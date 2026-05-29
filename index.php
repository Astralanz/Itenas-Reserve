<?php
session_start();
include 'koneksi.php';

// Set zona waktu
date_default_timezone_set('Asia/Jakarta');

// Proteksi halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Variabel ini disiapin di sini biar sidebar.php bisa langsung pake
$email_user = $_SESSION['email'];

// --- AMBIL DATA FASILITAS / ASET ---
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

$tanggal_sekarang = date('Y-m-d');
$jam_sekarang = date('H:i:s');

// Query buat nampilin list aset
$query_string = "SELECT aset.*, 
                (SELECT nama_peminjam FROM peminjaman WHERE peminjaman.aset_id = aset.id AND peminjaman.status IN ('APPROVED', 'ACC') AND peminjaman.tanggal_pinjam = '$tanggal_sekarang' AND peminjaman.jam_selesai > '$jam_sekarang' ORDER BY peminjaman.id DESC LIMIT 1) as peminjam,
                (SELECT prodi FROM peminjaman WHERE peminjaman.aset_id = aset.id AND peminjaman.status IN ('APPROVED', 'ACC') AND peminjaman.tanggal_pinjam = '$tanggal_sekarang' AND peminjaman.jam_selesai > '$jam_sekarang' ORDER BY peminjaman.id DESC LIMIT 1) as prodi_peminjam,
                (SELECT CONCAT(peminjaman.tanggal_pinjam, ' ', peminjaman.jam_selesai) FROM peminjaman WHERE peminjaman.aset_id = aset.id AND peminjaman.status IN ('APPROVED', 'ACC') AND peminjaman.tanggal_pinjam = '$tanggal_sekarang' AND peminjaman.jam_selesai > '$jam_sekarang' ORDER BY peminjaman.id DESC LIMIT 1) as waktu_selesai,
                (SELECT jam_mulai FROM peminjaman WHERE peminjaman.aset_id = aset.id AND peminjaman.status IN ('APPROVED', 'ACC') AND peminjaman.tanggal_pinjam = '$tanggal_sekarang' AND peminjaman.jam_selesai > '$jam_sekarang' ORDER BY peminjaman.id DESC LIMIT 1) as jam_mulai_peminjam,
                (SELECT jam_selesai FROM peminjaman WHERE peminjaman.aset_id = aset.id AND peminjaman.status IN ('APPROVED', 'ACC') AND peminjaman.tanggal_pinjam = '$tanggal_sekarang' AND peminjaman.jam_selesai > '$jam_sekarang' ORDER BY peminjaman.id DESC LIMIT 1) as jam_selesai_peminjam
                FROM aset";

if (!empty($search)) {
    $query_string .= " WHERE nama_aset LIKE '%$search%'";
}

$ambil_fasilitas = mysqli_query($conn, $query_string);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Itenas Reserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { background-color: #eef2fc; height: 100vh; display: flex; overflow: hidden; padding: 20px; }

        /* --- SIDEBAR LEFT (Layout Dasarnya Saja) --- */
        .sidebar { width: 320px; background-color: #dce4f7; border-radius: 30px; padding: 30px 20px; display: flex; flex-direction: column; justify-content: space-between; }
        .profile-section { display: flex; align-items: center; gap: 15px; margin-bottom: 40px; padding-left: 10px; }
        
        .avatar-box {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            flex-shrink: 0; 
            
            /* KODE PHP BACKGROUND DIHAPUS DARI SINI BIAR GAK MELEDAK KAYA TADI */
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
        .main-header { text-align: center; font-size: 32px; font-weight: 700; color: #4a6fdc; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 25px; text-shadow: 0 4px 10px rgba(74, 111, 220, 0.1); }
        .search-container { width: 100%; margin-bottom: 30px; padding: 2px; background: linear-gradient(to right, #4169e1, #ffa3ff); border-radius: 30px; }
        .search-box { width: 100%; background-color: white; border: none; border-radius: 28px; padding: 14px 25px; font-size: 14px; outline: none; }
        .card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; padding-bottom: 20px; }
        .facility-card { background-color: white; border-radius: 25px; padding: 25px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between; min-height: 190px; border: 3px solid transparent; transition: 0.3s; }
        .facility-card.is-booked { border-color: #a855f7; box-shadow: 0 10px 30px rgba(168, 85, 247, 0.15); }
        .card-body h3 { font-size: 18px; font-weight: 700; color: #000; margin-bottom: 8px; }
        .card-body p { font-size: 13px; color: #555; line-height: 1.6; }
        .time-counter { color: #4a6fdc; font-weight: 600; font-style: italic; }
        .card-footer { display: flex; justify-content: flex-end; margin-top: 15px; }
        .btn-book-wrapper { padding: 2px; background: linear-gradient(to right, #4169e1, #ffa3ff); border-radius: 20px; width: 110px; }
        .btn-book { width: 100%; background-color: white; border: none; border-radius: 18px; padding: 6px 0; font-size: 13px; font-weight: 600; color: #4a6fdc; cursor: pointer; text-align: center; display: block; text-decoration: none; transition: 0.2s; }
        .btn-book:hover { background-color: #4a6fdc; color: white; }

        /* --- CSS MODAL PROFIL (Buat jaga-jaga kalo Sidebar dipanggil) --- */
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
        <h1 class="main-header">Itenas Reserve</h1>

        <form action="" method="GET" class="search-container">
            <input type="text" name="search" class="search-box" placeholder="Cari Sesuatu nih?..." value="<?php echo htmlspecialchars($search); ?>">
        </form>

        <div class="card-grid">
            <?php 
            if (mysqli_num_rows($ambil_fasilitas) > 0) {
                while ($data = mysqli_fetch_array($ambil_fasilitas)) {
                    $is_booked = !empty($data['peminjam']);
            ?>
                    <div class="facility-card <?php echo $is_booked ? 'is-booked' : ''; ?>">
                        <div class="card-body">
                            <h3><?php echo htmlspecialchars($data['nama_aset']); ?></h3>
                            
                            <?php if ($is_booked) { ?>
                                <p style="margin-bottom: 4px;"><strong><?php echo htmlspecialchars($data['peminjam']); ?></strong> - <?php echo htmlspecialchars($data['prodi_peminjam']); ?></p>
                                <p style="margin-bottom: 4px;">Waktu : <?php echo date('H:i', strtotime($data['jam_mulai_peminjam'])); ?> - <?php echo date('H:i', strtotime($data['jam_selesai_peminjam'])); ?></p>
                                <p class="time-counter">Sisa Waktu : <span class="countdown" data-end="<?php echo $data['waktu_selesai']; ?>">Loading...</span></p>
                            <?php } else { ?>
                                <p>Hari Ini Kosong nih</p> 
                            <?php } ?>
                        </div>

                        <div class="card-footer">
                            <div class="btn-book-wrapper">
                                <a href="form_pinjam.php?id_aset=<?php echo $data['id']; ?>" class="btn-book">Book</a>
                            </div>
                        </div>
                    </div>
            <?php 
                }
            } else {
                echo "<p style='grid-column: 1/-1; text-align: center; color: #666;'>Fasilitas tidak ditemukan.</p>";
            }
            ?>
        </div>
    </div>

    <script>
        function startCountdowns() {
            const timers = document.querySelectorAll('.countdown');
            if(timers.length === 0) return; 
            
            setInterval(() => {
                timers.forEach(timer => {
                    const endTimeStr = timer.getAttribute('data-end');
                    if (!endTimeStr) return;

                    const endTime = new Date(endTimeStr.replace(/-/g, "/")).getTime();
                    const now = new Date().getTime();
                    const selisih = endTime - now;

                    if (selisih <= 0) {
                        timer.innerHTML = "Waktu Habis";
                        setTimeout(() => {
                            window.location.reload(); 
                        }, 1500); 
                        return;
                    }

                    const jam = Math.floor((selisih / (1000 * 60 * 60)));
                    const menit = Math.floor((selisih % (1000 * 60 * 60)) / (1000 * 60));
                    const detik = Math.floor((selisih % (1000 * 60)) / 1000);

                    timer.innerHTML = `${jam}j ${menit}m ${detik}d`;
                });
            }, 1000);
        }
        window.onload = startCountdowns;
    </script>
</body>
</html>