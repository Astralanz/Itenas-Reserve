<?php
session_start();
include 'koneksi.php';

// Atur timezone
date_default_timezone_set('Asia/Jakarta');

// Proteksi halaman admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$show_reject_form = false;
$reject_data = null;

// --- LOGIKA ACC & TOLAK PEMINJAMAN ---
$reasons_file = __DIR__ . '/reject_reasons.json';
$reject_reasons = [];
if (file_exists($reasons_file)) {
    $contents = file_get_contents($reasons_file);
    $reject_reasons = json_decode($contents, true) ?: [];
}

if (isset($_POST['tolak_submit'])) {
    $id_pinjam = mysqli_real_escape_string($conn, $_POST['id_pinjam']);
    $alasan_tolak = trim($_POST['alasan_tolak']);
    mysqli_query($conn, "UPDATE peminjaman SET status = 'REJECTED' WHERE id = '$id_pinjam'");
    $reject_reasons[$id_pinjam] = $alasan_tolak;
    file_put_contents($reasons_file, json_encode($reject_reasons, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "<script>alert('Permohonan telah DITOLAK dengan alasan!'); window.location.href='admin_antrian.php';</script>";
    exit();
} elseif (isset($_GET['aksi']) && isset($_GET['id_pinjam'])) {
    $id_pinjam = mysqli_real_escape_string($conn, $_GET['id_pinjam']);
    $aksi = $_GET['aksi'];

    if ($aksi == 'acc') {
        mysqli_query($conn, "UPDATE peminjaman SET status = 'APPROVED' WHERE id = '$id_pinjam'");
        echo "<script>alert('Permohonan berhasil di-ACC!'); window.location.href='admin_antrian.php';</script>";
        exit();
    } elseif ($aksi == 'tolak') {
        $show_reject_form = true;
        $result = mysqli_query($conn, "SELECT p.*, a.nama_aset FROM peminjaman p JOIN aset a ON p.aset_id = a.id WHERE p.id = '$id_pinjam' LIMIT 1");
        $reject_data = mysqli_fetch_assoc($result);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Antrian Permohonan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
            overflow-y: auto;
        }
        .menu-list { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .menu-item a { 
            display: flex; align-items: center; gap: 15px; padding: 12px 20px; 
            color: white; text-decoration: none; font-weight: 600; font-size: 15px; 
            border-radius: 15px; transition: 0.3s;
        }
        
        /* Menu Aktif dipindah ke Antrian */
        .menu-item.active a { background-color: white; color: black; }
        .menu-item:not(.active) a:hover { background-color: rgba(255, 255, 255, 0.1); }
        
        .sub-menu { margin-left: 45px; margin-top: 5px; display: flex; flex-direction: column; gap: 8px; }
        .sub-menu a { color: white; text-decoration: none; font-size: 13px; font-style: italic; opacity: 0.9; }
        .sub-menu a:hover { opacity: 1; text-decoration: underline; }

        /* --- MAIN CONTENT --- */
        .main-content { flex: 1; display: flex; flex-direction: column; padding: 10px 20px; overflow: hidden; }
        .page-title { text-align: center; color: #ff1a73; font-size: 32px; font-weight: 800; text-transform: capitalize; margin-bottom: 30px; letter-spacing: 1px; }

        /* --- STYLING TABEL ANTRIAN --- */
        .table-container { 
            flex: 1; overflow-y: auto; padding-right: 10px; 
        }
        
        table { 
            width: 100%; border-collapse: separate; border-spacing: 0 15px; 
            text-align: center; margin-top: -15px;
        }
        
        thead { position: sticky; top: 0; z-index: 10; }
        
        th { 
            background-color: #ff1a73; color: white; font-weight: 600; 
            font-size: 14px; padding: 18px 10px; 
        }
        th:first-child { border-top-left-radius: 15px; border-bottom-left-radius: 15px; }
        th:last-child { border-top-right-radius: 15px; border-bottom-right-radius: 15px; }
        
        td { 
            background-color: #f4f7fb; padding: 15px 10px; 
            font-size: 14px; color: #333; vertical-align: middle;
        }
        td:first-child { border-top-left-radius: 15px; border-bottom-left-radius: 15px; font-weight: 600; }
        td:last-child { border-top-right-radius: 15px; border-bottom-right-radius: 15px; }

        /* Detail Kolom */
        .col-mahasiswa { text-align: left; line-height: 1.4; }
        .col-jadwal { font-size: 13px; line-height: 1.4; color: #555; }
        .col-status { font-weight: 700; text-transform: uppercase; font-size: 13px; }
        
        /* Tombol Aksi */
        .action-buttons { display: flex; flex-direction: column; gap: 8px; align-items: center; justify-content: center; }
        .btn-acc { background-color: #7dd3fc; color: white; border: none; padding: 6px 20px; border-radius: 20px; font-weight: 700; font-size: 12px; cursor: pointer; text-decoration: none; width: 80px; background: #6ede6e; transition: 0.2s;}
        .btn-acc:hover { background: #5bc95b; }
        .btn-tolak { background-color: #ff1a73; color: white; border: none; padding: 6px 20px; border-radius: 20px; font-weight: 700; font-size: 12px; cursor: pointer; text-decoration: none; width: 80px; transition: 0.2s;}
        .btn-tolak:hover { background: #e6005c; }
        
        .reject-form { background: white; padding: 25px; border-radius: 20px; border: 1px solid rgba(255, 26, 115, 0.15); margin-bottom: 25px; box-shadow: 0 10px 25px rgba(255, 26, 115, 0.08); }
        .reject-form h3 { margin-bottom: 15px; color: #ff1a73; font-size: 20px; }
        .reject-form textarea { width: 100%; min-height: 120px; border-radius: 15px; border: 1px solid #ddd; padding: 15px; resize: vertical; font-size: 14px; font-family: 'Poppins', sans-serif; margin-bottom: 15px; }
        .reject-form textarea:focus { outline: none; border-color: #ff1a73; box-shadow: 0 0 0 4px rgba(255, 26, 115, 0.08); }
        .reject-form .btn-submit { background-color: #ff1a73; color: white; border: none; padding: 12px 25px; border-radius: 18px; font-weight: 700; cursor: pointer; margin-right: 12px; }
        .reject-form .btn-cancel { display: inline-block; color: #ff1a73; text-decoration: none; font-weight: 700; padding: 12px 25px; border-radius: 18px; border: 1px solid #ff1a73; }
        .reject-form .btn-submit:hover { background: #e6005c; }
        .reject-form .btn-cancel:hover { background: rgba(255, 26, 115, 0.08); }
        
        .sudah-diproses { font-style: italic; color: #777; font-size: 13px; }

        /* Custom Scrollbar */
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
        </div>
    </div>

    <div class="main-content">
        <h1 class="page-title">Antrian Permohonan</h1>

        <?php if ($show_reject_form && $reject_data) { ?>
            <div class="reject-form">
                <h3>Tolak Permohonan</h3>
                <p style="margin-bottom: 15px; color: #444;">Silakan isi alasan penolakan untuk permohonan <strong><?php echo htmlspecialchars($reject_data['nama_aset']); ?></strong> oleh <strong><?php echo htmlspecialchars($reject_data['nama_peminjam']); ?></strong>.</p>
                <form method="POST">
                    <input type="hidden" name="id_pinjam" value="<?php echo $reject_data['id']; ?>">
                    <textarea name="alasan_tolak" placeholder="Contoh: Waktu tidak tersedia, fasilitas sedang dalam perbaikan, atau peserta melebihi kapasitas" required><?php echo htmlspecialchars($reject_reasons[$reject_data['id']] ?? ''); ?></textarea>
                    <button type="submit" name="tolak_submit" class="btn-submit">Kirim Penolakan</button>
                    <a href="admin_antrian.php" class="btn-cancel">Batal</a>
                </form>
            </div>
        <?php } ?>

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
                    // Ambil data peminjaman digabung sama data aset
                    $query = mysqli_query($conn, "SELECT p.*, a.nama_aset FROM peminjaman p JOIN aset a ON p.aset_id = a.id ORDER BY p.id DESC");
                    $no = 1;

                    if (mysqli_num_rows($query) > 0) {
                        while($data = mysqli_fetch_array($query)) {
                            
                            // Logika Realtime Status
                            $status_db = strtoupper($data['status']);
                            $waktu_sekarang = time(); 
                            
                            $string_mulai = $data['tanggal_pinjam'] . ' ' . $data['jam_mulai'];
                            $string_selesai = $data['tanggal_pinjam'] . ' ' . $data['jam_selesai'];
                            
                            $timestamp_mulai = strtotime($string_mulai);
                            $timestamp_selesai = strtotime($string_selesai);

                            // Auto-update SELESAI di database kalo jamnya udh lewat
                            if (($status_db == 'APPROVED' || $status_db == 'ACC') && $waktu_sekarang > $timestamp_selesai) {
                                mysqli_query($conn, "UPDATE peminjaman SET status = 'SELESAI' WHERE id = '".$data['id']."'");
                                $status_db = 'SELESAI'; 
                            }

                            $is_billing_aktif = ($waktu_sekarang >= $timestamp_mulai && $waktu_sekarang <= $timestamp_selesai);

                            // Pewarnaan Status UI
                            $status_text = "";
                            $status_color = "";

                            if ($status_db == 'PENDING') {
                                $status_text = "PENDING";
                                $status_color = "#ffb800"; // Kuning/Orange
                            } elseif ($status_db == 'REJECTED' || $status_db == 'TOLAK') {
                                $status_text = "DITOLAK";
                                $status_color = "#ff1a73"; // Merah/Pink
                            } elseif ($status_db == 'SELESAI') {
                                $status_text = "Selesai";
                                $status_color = "#4a6fdc"; // Biru
                            } elseif ($status_db == 'APPROVED' || $status_db == 'ACC') {
                                if ($is_billing_aktif) {
                                    $status_text = "SEDANG DIGUNAKAN";
                                    $status_color = "#6ede6e"; // Hijau
                                } else {
                                    $status_text = "DISETUJUI";
                                    $status_color = "#6ede6e"; // Hijau
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
                                    <a href="admin_antrian.php?aksi=acc&id_pinjam=<?php echo $data['id']; ?>" onclick="return confirm('ACC permohonan ini?')" class="btn-acc">ACC</a>
                                    <a href="admin_antrian.php?aksi=tolak&id_pinjam=<?php echo $data['id']; ?>" onclick="return confirm('Tolak permohonan ini?')" class="btn-tolak">TOLAK</a>
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

</body>
</html>