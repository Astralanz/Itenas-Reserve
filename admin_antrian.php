<?php
session_start();
include 'koneksi.php';

// Atur timezone
date_default_timezone_set('Asia/Jakarta');

// Proteksi halaman admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$reasons_file = __DIR__ . '/reject_reasons.json';
$reject_reasons = [];
if (file_exists($reasons_file)) {
    $contents = file_get_contents($reasons_file);
    $reject_reasons = json_decode($contents, true) ?: [];
}

// =========================================================================
// --- LOGIKA AJAX (BEKERJA DI BELAKANG LAYAR TANPA LOADING) ---
// =========================================================================

// 1. AJAX Hapus Riwayat & ACC
if (isset($_GET['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_GET['ajax_action'];

    if ($action == 'hapus_riwayat') {
        $hapus = mysqli_query($conn, "DELETE FROM peminjaman WHERE status IN ('SELESAI', 'REJECTED', 'DITOLAK')");
        echo json_encode(['status' => $hapus ? 'success' : 'error', 'pesan' => $hapus ? 'Semua riwayat usang berhasil dibakar habis!' : 'Gagal menghapus riwayat!']);
        exit();
    }

    if ($action == 'acc' && isset($_GET['id_pinjam'])) {
        $id_pinjam = mysqli_real_escape_string($conn, $_GET['id_pinjam']);
        mysqli_query($conn, "UPDATE peminjaman SET status = 'APPROVED' WHERE id = '$id_pinjam'");
        echo json_encode(['status' => 'success', 'pesan' => 'Permohonan berhasil di-ACC!']);
        exit();
    }
}

// 2. AJAX Tolak Permohonan (Menerima input alasan dari SweetAlert)
if (isset($_POST['ajax_action']) && $_POST['ajax_action'] == 'tolak') {
    header('Content-Type: application/json');
    $id_pinjam = mysqli_real_escape_string($conn, $_POST['id_pinjam']);
    $alasan_tolak = trim($_POST['alasan_tolak']);
    
    mysqli_query($conn, "UPDATE peminjaman SET status = 'REJECTED' WHERE id = '$id_pinjam'");
    $reject_reasons[$id_pinjam] = $alasan_tolak;
    file_put_contents($reasons_file, json_encode($reject_reasons, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    echo json_encode(['status' => 'success', 'pesan' => 'Permohonan telah DITOLAK beserta alasannya.']);
    exit();
}
// =========================================================================
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Antrian Permohonan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { 
            background: linear-gradient(to bottom right, #fff0f5, #ffffff); 
            height: 100vh; display: flex; padding: 20px; gap: 20px; overflow: hidden;
        }
        
        /* --- SIDEBAR --- */
        .sidebar-container { width: 280px; display: flex; flex-direction: column; gap: 15px; }
        
        .sidebar-header { 
            background-color: #ff1a73; border-radius: 20px; padding: 25px 20px; 
            color: white; box-shadow: 0 10px 20px rgba(255, 26, 115, 0.2);
        }
        .sidebar-header h2 { font-size: 22px; font-weight: 800; line-height: 1.2; text-transform: uppercase; }
        
        .sidebar-menu { 
            background-color: #ff1a73; border-radius: 20px; padding: 30px 15px; 
            flex: 1; color: white; box-shadow: 0 10px 20px rgba(255, 26, 115, 0.2);
            display: flex; flex-direction: column; justify-content: space-between;
            overflow-y: auto;
        }
        .menu-list { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .menu-item a { 
            display: flex; align-items: center; gap: 15px; padding: 12px 20px; 
            color: white; text-decoration: none; font-weight: 600; font-size: 15px; 
            border-radius: 15px; transition: 0.3s;
        }
        
        .menu-item.active a { background-color: white; color: black; }
        .menu-item:not(.active) a:hover { background-color: rgba(255, 255, 255, 0.1); }
        
        /* --- MAIN CONTENT --- */
        .main-content { flex: 1; display: flex; flex-direction: column; padding: 10px 20px; overflow: hidden; }
        
        /* HEADER & TOMBOL HAPUS */
        .header-action { 
            display: flex; justify-content: center; align-items: center; 
            margin-bottom: 30px; position: relative; width: 100%;
        }
        .page-title { 
            color: #ff1a73; font-size: 32px; font-weight: 800; text-transform: capitalize; 
            letter-spacing: 1px; margin: 0; text-align: center;
        }
        .btn-hapus-all {
            position: absolute; right: 10px;
            background-color: #ff1a73; color: white; padding: 10px 20px; border-radius: 12px;
            border: none; cursor: pointer;
            text-decoration: none; font-weight: 600; font-size: 14px; box-shadow: 0 4px 10px rgba(255, 77, 77, 0.3);
            transition: 0.2s; display: flex; align-items: center; gap: 8px; font-family: 'Poppins', sans-serif;
        }
        .btn-hapus-all:hover { background-color: #ff1a73; transform: translateY(-2px); }

        /* --- STYLING TABEL ANTRIAN --- */
        .table-container { flex: 1; overflow-y: auto; padding-right: 10px; }
        
        table { width: 100%; border-collapse: separate; border-spacing: 0 15px; text-align: center; margin-top: -15px; }
        thead { position: sticky; top: 0; z-index: 10; }
        th { background-color: #ff1a73; color: white; font-weight: 600; font-size: 14px; padding: 18px 10px; }
        th:first-child { border-top-left-radius: 15px; border-bottom-left-radius: 15px; }
        th:last-child { border-top-right-radius: 15px; border-bottom-right-radius: 15px; }
        
        td { background-color: #f4f7fb; padding: 15px 10px; font-size: 14px; color: #333; vertical-align: middle; }
        td:first-child { border-top-left-radius: 15px; border-bottom-left-radius: 15px; font-weight: 600; }
        td:last-child { border-top-right-radius: 15px; border-bottom-right-radius: 15px; }

        .col-mahasiswa { text-align: left; line-height: 1.4; }
        .col-jadwal { font-size: 13px; line-height: 1.4; color: #555; }
        .col-status { font-weight: 700; text-transform: uppercase; font-size: 13px; }
        
        .action-buttons { display: flex; flex-direction: column; gap: 8px; align-items: center; justify-content: center; }
        .btn-acc { background-color: #7dd3fc; color: white; border: none; padding: 6px 20px; border-radius: 20px; font-weight: 700; font-size: 12px; cursor: pointer; text-decoration: none; width: 80px; background: #6ede6e; transition: 0.2s; font-family: 'Poppins', sans-serif;}
        .btn-acc:hover { background: #5bc95b; }
        .btn-tolak { background-color: #ff1a73; color: white; border: none; padding: 6px 20px; border-radius: 20px; font-weight: 700; font-size: 12px; cursor: pointer; text-decoration: none; width: 80px; transition: 0.2s; font-family: 'Poppins', sans-serif;}
        .btn-tolak:hover { background: #e6005c; }
        
        .sudah-diproses { font-style: italic; color: #777; font-size: 13px; }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #dcb3c3; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #ff1a73; }
    </style>
</head>
<body>

    <div class="sidebar-container">
        <div class="sidebar-header">
            <h2>ADMIN<br>CONTROL</h2>
            <p style="font-size: 14px; font-weight: 500; margin-top: 5px; opacity: 0.9;">ITENAS RESERVE</p>
        </div>
        
        <div class="sidebar-menu">
            <ul class="menu-list">
                <li class="menu-item">
                    <a href="admin_aset.php">🏢 Aset Kampus</a>
                </li>
                <li class="menu-item active">
                    <a href="admin_antrian.php">👥 Antrian</a>
                </li>
                <li class="menu-item">
                    <a href="admin_rating.php">⭐ Rating dari user</a>
                </li>
            </ul>

            <ul class="menu-list" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 15px;">
                <li class="menu-item">
                    <a href="logout.php" onclick="return confirm('Yakin ingin keluar dari panel admin?');" style="color: #ffe6e6;">
                        🚪 Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="header-action">
            <h1 class="page-title">Antrian Permohonan</h1>
            <button onclick="hapusRiwayat()" class="btn-hapus-all">
                🗑️ Bersihkan Riwayat
            </button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th style="text-align: left;">Mahasiswa</th>
                        <th>NIM</th>
                        <th>PRODI</th>
                        <th>ASET</th>
                        <th>Jadwal Reservasi</th>
                        <th>STATUS</th>
                        <th>ALASAN</th>
                        <th>PERSETUJUAN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = mysqli_query($conn, "SELECT p.*, a.nama_aset FROM peminjaman p JOIN aset a ON p.aset_id = a.id ORDER BY p.id DESC");
                    $no = 1;

                    if (mysqli_num_rows($query) > 0) {
                        while($data = mysqli_fetch_array($query)) {
                            
                            $status_db = strtoupper($data['status']);
                            $waktu_sekarang = time(); 
                            
                            $string_mulai = $data['tanggal_pinjam'] . ' ' . $data['jam_mulai'];
                            $string_selesai = $data['tanggal_pinjam'] . ' ' . $data['jam_selesai'];
                            
                            $timestamp_mulai = strtotime($string_mulai);
                            $timestamp_selesai = strtotime($string_selesai);

                            if (($status_db == 'APPROVED' || $status_db == 'ACC') && $waktu_sekarang > $timestamp_selesai) {
                                mysqli_query($conn, "UPDATE peminjaman SET status = 'SELESAI' WHERE id = '".$data['id']."'");
                                $status_db = 'SELESAI'; 
                            }

                            $is_billing_aktif = ($waktu_sekarang >= $timestamp_mulai && $waktu_sekarang <= $timestamp_selesai);

                            $status_text = "";
                            $status_color = "";

                            if ($status_db == 'PENDING') {
                                $status_text = "PENDING";
                                $status_color = "#ffb800";
                            } elseif ($status_db == 'REJECTED' || $status_db == 'DITOLAK') {
                                $status_text = "DITOLAK";
                                $status_color = "#ff1a73";
                            } elseif ($status_db == 'SELESAI') {
                                $status_text = "SELESAI";
                                $status_color = "#4a6fdc";
                            } elseif ($status_db == 'APPROVED' || $status_db == 'ACC') {
                                if ($is_billing_aktif) {
                                    $status_text = "SEDANG DIGUNAKAN";
                                    $status_color = "#6ede6e";
                                } else {
                                    $status_text = "DISETUJUI";
                                    $status_color = "#6ede6e";
                                }
                            } else {
                                $status_text = $status_db;
                                $status_color = "#333";
                            }
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td class="col-mahasiswa"><?php echo htmlspecialchars($data['nama_peminjam']); ?></td>
                        <td><?php echo htmlspecialchars($data['nim']); ?></td>
                        <td><?php echo htmlspecialchars($data['prodi']); ?></td>
                        <td><?php echo htmlspecialchars($data['nama_aset']); ?></td>
                        <td class="col-jadwal">
                            <?php echo $data['tanggal_pinjam']; ?><br>
                            <i><?php echo date('H:i', strtotime($data['jam_mulai'])); ?> - <?php echo date('H:i', strtotime($data['jam_selesai'])); ?></i>
                        </td>
                        <td class="col-status" style="color: <?php echo $status_color; ?>;">
                            <?php echo $status_text; ?>
                        </td>
                        <td style="font-size:13px; color:#444; line-height:1.4; text-align:left;">
                            <?php if ($status_db == 'REJECTED') {
                                echo htmlspecialchars($reject_reasons[$data['id']] ?? 'Tidak ada alasan tertulis.');
                            } else {
                                echo '-';
                            } ?>
                        </td>
                        <td>
                            <?php if ($status_db == 'PENDING') { ?>
                                <div class="action-buttons">
                                    <button onclick="accPermohonan(<?php echo $data['id']; ?>)" class="btn-acc">ACC</button>
                                    <button onclick="tolakPermohonan(<?php echo $data['id']; ?>, '<?php echo addslashes(htmlspecialchars($data['nama_peminjam'])); ?>', '<?php echo addslashes(htmlspecialchars($data['nama_aset'])); ?>')" class="btn-tolak">TOLAK</button>
                                </div>
                            <?php } else { ?>
                                <span class="sudah-diproses">Sudah diproses</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='9' style='padding: 30px; color:#888; font-style:italic;'>Belum ada antrian permohonan peminjaman.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // 1. Fungsi ACC
        function accPermohonan(id) {
            Swal.fire({
                title: 'Setujui Permohonan?',
                text: "Pastikan jadwal tidak bentrok ya bos!",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6ede6e',
                cancelButtonColor: '#888',
                confirmButtonText: 'Ya, ACC!',
                cancelButtonText: 'Batal',
                heightAuto: false
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`admin_antrian.php?ajax_action=acc&id_pinjam=${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if(data.status === 'success') {
                            Swal.fire({
                                title: 'Berhasil!', 
                                text: data.pesan, 
                                icon: 'success',
                                heightAuto: false
                            }).then(() => location.reload());
                        }
                    });
                }
            });
        }

        // 2. Fungsi Tolak (Dengan Form Input Langsung di Popup)
        function tolakPermohonan(id, nama, aset) {
            Swal.fire({
                title: 'Tolak Permohonan',
                html: `Silakan isi alasan penolakan untuk <b>${aset}</b> oleh <b>${nama}</b>.`,
                input: 'textarea',
                inputPlaceholder: 'Contoh: Waktu tidak tersedia, fasilitas sedang perbaikan...',
                inputAttributes: {
                    'aria-label': 'Tulis alasan penolakan'
                },
                showCancelButton: true,
                confirmButtonColor: '#ff1a73',
                cancelButtonColor: '#888',
                confirmButtonText: 'Kirim Penolakan',
                cancelButtonText: 'Batal',
                heightAuto: false,
                inputValidator: (value) => {
                    if (!value) {
                        return 'Alasan penolakan wajib diisi bos!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('ajax_action', 'tolak');
                    formData.append('id_pinjam', id);
                    formData.append('alasan_tolak', result.value);

                    fetch('admin_antrian.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.status === 'success') {
                            Swal.fire({
                                title: 'Ditolak!', 
                                text: data.pesan, 
                                icon: 'success',
                                heightAuto: false
                            }).then(() => location.reload());
                        }
                    });
                }
            });
        }

        // 3. Fungsi Hapus Riwayat
        function hapusRiwayat() {
            Swal.fire({
                title: 'Yakin bersihin riwayat?',
                text: "Data yang udah Selesai & Ditolak bakal dihapus permanen lho bos!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff4d4d',
                cancelButtonColor: '#888',
                confirmButtonText: 'Ya, Sapu Bersih!',
                cancelButtonText: 'Batal',
                heightAuto: false
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('admin_antrian.php?ajax_action=hapus_riwayat')
                    .then(res => res.json())
                    .then(data => {
                        if(data.status === 'success') {
                            Swal.fire({
                                title: 'Mantap!', 
                                text: data.pesan, 
                                icon: 'success',
                                heightAuto: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                title: 'Gagal!', 
                                text: data.pesan, 
                                icon: 'error',
                                heightAuto: false
                            });
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>