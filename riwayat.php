<?php
session_start();
include 'koneksi.php';

// Atur timezone biar sinkron sama jam laptop/WIB
date_default_timezone_set('Asia/Jakarta');

// Proteksi halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Variabel ini disiapin di sini biar sidebar.php bisa langsung pake
$email_user = $_SESSION['email'];

// --- LOGIKA TOMBOL BATALKAN ---
if (isset($_POST['batalkan_peminjaman'])) {
    $id_peminjaman = mysqli_real_escape_string($conn, $_POST['id_peminjaman']);
    mysqli_query($conn, "DELETE FROM peminjaman WHERE id = '$id_peminjaman'");
    echo "<script>alert('Peminjaman berhasil dibatalkan!'); window.location.href='riwayat.php';</script>";
}

// --- LOGIKA TOMBOL SELESAIKAN ---
if (isset($_POST['selesaikan_peminjaman'])) {
    $id_peminjaman = mysqli_real_escape_string($conn, $_POST['id_peminjaman']);
    mysqli_query($conn, "UPDATE peminjaman SET status = 'SELESAI' WHERE id = '$id_peminjaman'");
    echo "<script>alert('Fasilitas telah selesai digunakan lebih awal!'); window.location.href='riwayat.php';</script>";
}

// QUERY UTAMA
$query_waiting = mysqli_query($conn, "SELECT peminjaman.*, aset.nama_aset 
                                      FROM peminjaman 
                                      JOIN aset ON peminjaman.aset_id = aset.id 
                                      ORDER BY peminjaman.id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting List - Itenas Reserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { background-color: #eef2fc; height: 100vh; display: flex; overflow: hidden; padding: 20px; }
        
        /* --- SIDEBAR LEFT (Layout Dasar) --- */
        .sidebar { width: 320px; background-color: #dce4f7; border-radius: 30px; padding: 30px 20px; display: flex; flex-direction: column; justify-content: space-between; }
        .profile-section { display: flex; align-items: center; gap: 15px; margin-bottom: 40px; padding-left: 10px; }
        
        /* CLASS AVATAR SAMA KAYA INDEX (Aman dari error) */
        .avatar-box { 
            width: 60px; 
            height: 60px; 
            border-radius: 50%; 
            flex-shrink: 0; 
            background-size: cover; 
            background-position: center; 
            background-repeat: no-repeat; 
            border: 3px solid white; 
            cursor: pointer; 
            transition: 0.2s; 
            margin-top: -5px; 
        }
        .avatar-box:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(74, 111, 220, 0.2); }
        .profile-info h3 { font-size: 16px; font-weight: 700; color: #1a1a1a; text-transform: capitalize; }
        .profile-info p { font-size: 12px; color: #666; }
        .menu-list { list-style: none; display: flex; flex-direction: column; gap: 12px; flex-grow: 1; }
        .menu-item a { display: flex; align-items: center; gap: 15px; padding: 15px 25px; color: #555; text-decoration: none; font-weight: 500; font-size: 14px; border-radius: 20px; transition: 0.3s; }
        .menu-item.active a { background-color: #4a6fdc; color: white; box-shadow: 0 10px 20px rgba(74, 111, 220, 0.3); }
        .menu-item:not(.active) a:hover { background-color: rgba(255, 255, 255, 0.5); color: #000; }
        
        /* --- MAIN KONTEN RIGHT --- */
        .main-content { flex: 1; padding: 10px 40px; display: flex; flex-direction: column; overflow-y: auto; }
        .main-header { text-align: center; font-size: 32px; font-weight: 700; color: #4a6fdc; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 40px; text-shadow: 0 4px 10px rgba(74, 111, 220, 0.1); }
        
        /* --- WAITING LIST TABLE CSS --- */
        .waiting-table-container { width: 100%; display: flex; flex-direction: column; gap: 15px; }
        .table-header-row { display: grid; grid-template-columns: 0.5fr 1.5fr 2fr 1.5fr 1.2fr; padding: 15px 30px; background-color: #dce4f7; border-radius: 20px; color: #555; font-weight: 600; font-size: 14px; text-align: left; align-items: center; }
        .table-data-row { display: grid; grid-template-columns: 0.5fr 1.5fr 2fr 1.5fr 1.2fr; padding: 20px 30px; background-color: white; border-radius: 20px; box-shadow: 0 8px 20px rgba(0,0,0,0.02); align-items: center; font-size: 14px; color: #333; }
        .col-schedule { font-size: 13px; color: #555; line-height: 1.4; }
        .col-status { font-weight: 500; font-style: italic; }
        .status-pending { color: #555; }
        .status-approved { color: #16a34a; font-weight: 600; }
        .status-rejected { color: #dc2626; }
        .btn-action { border: none; padding: 10px 0; border-radius: 15px; font-size: 13px; font-weight: 600; color: white; cursor: pointer; width: 120px; text-align: center; display: block; transition: 0.2s; }
        .btn-red { background-color: #e50000; box-shadow: 0 4px 12px rgba(229, 0, 0, 0.2); }
        .btn-red:hover { background-color: #b80000; }
        .btn-orange { background-color: #ff9100; box-shadow: 0 4px 12px rgba(255, 145, 0, 0.2); }
        .btn-orange:hover { background-color: #d47800; }

        /* --- STYLING MODAL PROFIL USER --- */
        .modal-profil-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(3px); }
        .modal-profil-box { background: white; width: 450px; padding: 40px; border-radius: 20px; box-shadow: 0 15px 30px rgba(0,0,0,0.15); text-align: center; position: relative; }
        .edit-avatar-container { position: relative; width: 120px; height: 120px; margin: 0 auto 20px; }
        .edit-avatar-preview { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 4px solid #4a6fdc; cursor: pointer; }
        .edit-icon { position: absolute; bottom: 0; right: 0; background: white; width: 35px; height: 35px; border-radius: 50%; display: flex; justify-content: center; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); cursor: pointer; color: #4a6fdc; font-size: 16px; }
        #upload_profil { display: none; }
        .modal-profil-name { font-size: 18px; font-weight: 800; color: #000; margin-bottom: 25px; text-transform: uppercase;}
        .info-row { display: flex; text-align: left; margin-bottom: 15px; font-size: 15px; color: #555; }
        .info-label { width: 80px; }
        .info-colon { width: 20px; text-align: center; }
        .info-value { flex: 1; font-weight: 500; color: #333; }
        .btn-simpan-wrapper { display: inline-block; padding: 2px; border-radius: 20px; background: linear-gradient(to right, #4169e1, #ffa3ff); margin-top: 15px; }
        .btn-simpan { background: white; border: none; padding: 8px 30px; border-radius: 18px; color: #a855f7; font-weight: 600; cursor: pointer; transition: 0.2s; font-size: 14px; }
        .btn-simpan:hover { background: #a855f7; color: white; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h1 class="main-header">Waiting List</h1>

        <div class="waiting-table-container">
            <div class="table-header-row">
                <div>No</div>
                <div>Nama Aset</div>
                <div>Jadwal Pinjam</div>
                <div>Status</div>
                <div>Aksi</div>
            </div>

            <?php 
            if (mysqli_num_rows($query_waiting) > 0) {
                $no = 1;
                while ($row = mysqli_fetch_array($query_waiting)) {
                    
                    $status_db = strtoupper($row['status']);
                    $waktu_sekarang = time(); 
                    
                    $string_mulai = $row['tanggal_pinjam'] . ' ' . $row['jam_mulai'];
                    $string_selesai = $row['tanggal_pinjam'] . ' ' . $row['jam_selesai'];
                    
                    $timestamp_mulai = strtotime($string_mulai);
                    $timestamp_selesai = strtotime($string_selesai);

                    if ($timestamp_mulai > $timestamp_selesai) {
                        $timestamp_selesai += 86400; 
                    }

                    if (($status_db == 'APPROVED' || $status_db == 'ACC') && $waktu_sekarang > $timestamp_selesai) {
                        mysqli_query($conn, "UPDATE peminjaman SET status = 'SELESAI' WHERE id = '".$row['id']."'");
                        $status_db = 'SELESAI'; 
                    }

                    $is_billing_aktif = ($waktu_sekarang >= $timestamp_mulai && $waktu_sekarang <= $timestamp_selesai);

                    $status_text = "";
                    $status_class = "";

                    if ($status_db == 'PENDING') {
                        $status_text = "Menunggu Persetujuan";
                        $status_class = "status-pending";
                    } elseif ($status_db == 'APPROVED' || $status_db == 'ACC') {
                        $status_text = "Pengajuan Di ACC";
                        $status_class = "status-approved";
                    } elseif ($status_db == 'REJECTED') {
                        $status_text = "Pengajuan Di Tolak";
                        $status_class = "status-rejected";
                    } elseif ($status_db == 'SELESAI') {
                        $status_text = "Selesai";
                        $status_class = "status-pending"; 
                    } else {
                        $status_text = $row['status'];
                    }
            ?>
                    <div class="table-data-row">
                        <div><?php echo $no++; ?></div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($row['nama_aset']); ?></div>
                        <div class="col-schedule">
                            <?php echo $row['tanggal_pinjam']; ?><br>
                            <?php echo date('H:i', strtotime($row['jam_mulai'])); ?> - <?php echo date('H:i', strtotime($row['jam_selesai'])); ?>
                        </div>
                        <div class="col-status <?php echo $status_class; ?>">
                            <?php echo $status_text; ?>
                        </div>
                        <div>
                            <?php if ($status_db == 'PENDING') { ?>
                                <form action="" method="POST">
                                    <input type="hidden" name="id_peminjaman" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="batalkan_peminjaman" class="btn-action btn-red">Batalkan</button>
                                </form>
                            <?php } elseif (($status_db == 'APPROVED' || $status_db == 'ACC') && $is_billing_aktif) { ?>
                                <form action="" method="POST">
                                    <input type="hidden" name="id_peminjaman" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="selesaikan_peminjaman" class="btn-action btn-orange">Selesaikan</button>
                                </form>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </div>
                    </div>
            <?php 
                }
            } else {
                echo "<p style='text-align: center; color: #666; margin-top: 20px;'>Belum ada data pengajuan peminjaman.</p>";
            }
            ?>
        </div>
    </div>

</body>
</html>