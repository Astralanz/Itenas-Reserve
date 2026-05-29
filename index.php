<?php
session_start();
include 'koneksi.php';

// =========================================================================
// --- API ENDPOINT UNTUK AJAX MODAL CEK JADWAL (Ditaruh paling atas) ---
// =========================================================================
if (isset($_GET['ajax_cek_jadwal'])) {
    $id_aset = mysqli_real_escape_string($conn, $_GET['id_aset']);
    $tanggal = mysqli_real_escape_string($conn, $_GET['tanggal']);
    
    $query = "SELECT peminjaman.*, users.foto_profil 
              FROM peminjaman 
              LEFT JOIN users ON peminjaman.user_id = users.id 
              WHERE peminjaman.aset_id = '$id_aset' 
              AND peminjaman.tanggal_pinjam = '$tanggal' 
              AND peminjaman.status IN ('APPROVED', 'ACC') 
              ORDER BY peminjaman.jam_mulai ASC";
              
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $waktu_sekarang = time();
        while ($row = mysqli_fetch_array($result)) {
            $foto = (!empty($row['foto_profil']) && file_exists('uploads/' . $row['foto_profil'])) 
                    ? 'uploads/' . $row['foto_profil'] 
                    : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
            
            $jam_mulai = date('H:i', strtotime($row['jam_mulai']));
            $jam_selesai = date('H:i', strtotime($row['jam_selesai']));
            
            // Tambahkan waktu mulai full biar bisa dicek di JS
            $waktu_mulai_full = $row['tanggal_pinjam'] . ' ' . $row['jam_mulai'];
            $waktu_selesai_full = $row['tanggal_pinjam'] . ' ' . $row['jam_selesai'];
            
            echo '
            <div class="jadwal-item">
                <div class="jadwal-item-avatar" style="background-image: url(\''.$foto.'\');"></div>
                <div class="jadwal-info">
                    <h4>'.htmlspecialchars($row['nama_peminjam']).' - '.htmlspecialchars($row['prodi']).'</h4>
                    <p>Waktu : '.$jam_mulai.' - '.$jam_selesai.'</p>
                    <p class="sisa-waktu"><span class="countdown" data-start="'.$waktu_mulai_full.'" data-end="'.$waktu_selesai_full.'">Loading...</span></p>
                </div>
            </div>';
        }
    } else {
        echo '<div class="jadwal-empty">Hari Ini Kosong nih, aman buat di-booking!</div>';
    }
    exit(); 
}
// =========================================================================

date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$email_user = $_SESSION['email'];
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

// LOGIKA PEMBATAS TANGGAL (HARI INI SAMPAI LUSA)
$tanggal_sekarang = date('Y-m-d');
$batas_tanggal = date('Y-m-d', strtotime('+2 days')); 
$jam_sekarang = date('H:i:s');

$query_string = "SELECT aset.*,
                (SELECT nama_peminjam FROM peminjaman WHERE peminjaman.aset_id = aset.id AND peminjaman.status IN ('APPROVED', 'ACC') AND peminjaman.tanggal_pinjam = '$tanggal_sekarang' AND '$jam_sekarang' BETWEEN peminjaman.jam_mulai AND peminjaman.jam_selesai LIMIT 1) as peminjam,
                (SELECT prodi FROM peminjaman WHERE peminjaman.aset_id = aset.id AND peminjaman.status IN ('APPROVED', 'ACC') AND peminjaman.tanggal_pinjam = '$tanggal_sekarang' AND '$jam_sekarang' BETWEEN peminjaman.jam_mulai AND peminjaman.jam_selesai LIMIT 1) as prodi_peminjam,
                (SELECT CONCAT(peminjaman.tanggal_pinjam, ' ', peminjaman.jam_selesai) FROM peminjaman WHERE peminjaman.aset_id = aset.id AND peminjaman.status IN ('APPROVED', 'ACC') AND peminjaman.tanggal_pinjam = '$tanggal_sekarang' AND '$jam_sekarang' BETWEEN peminjaman.jam_mulai AND peminjaman.jam_selesai LIMIT 1) as waktu_selesai,
                (SELECT jam_mulai FROM peminjaman WHERE peminjaman.aset_id = aset.id AND peminjaman.status IN ('APPROVED', 'ACC') AND peminjaman.tanggal_pinjam = '$tanggal_sekarang' AND '$jam_sekarang' BETWEEN peminjaman.jam_mulai AND peminjaman.jam_selesai LIMIT 1) as jam_mulai_peminjam,
                (SELECT jam_selesai FROM peminjaman WHERE peminjaman.aset_id = aset.id AND peminjaman.status IN ('APPROVED', 'ACC') AND peminjaman.tanggal_pinjam = '$tanggal_sekarang' AND '$jam_sekarang' BETWEEN peminjaman.jam_mulai AND peminjaman.jam_selesai LIMIT 1) as jam_selesai_peminjam
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

        /* --- SIDEBAR LEFT --- */
        .sidebar { width: 320px; background-color: #dce4f7; border-radius: 30px; padding: 30px 20px; display: flex; flex-direction: column; justify-content: space-between; }
        .profile-section { display: flex; align-items: center; gap: 15px; margin-bottom: 40px; padding-left: 10px; }
        .avatar-box { width: 60px; height: 60px; border-radius: 50%; flex-shrink: 0; background-size: cover; background-position: center; background-repeat: no-repeat; border: 3px solid white; cursor: pointer; transition: 0.2s; margin-top: -5px; }
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
        .facility-card { background-color: rgba(255,255,255,0.9); border-radius: 25px; padding: 25px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between; min-height: 190px; border: 3px solid transparent; transition: 0.3s; backdrop-filter: blur(5px); }
        .facility-card.is-booked { border-color: #a855f7; box-shadow: 0 10px 30px rgba(168, 85, 247, 0.15); }
        .card-body h3 { font-size: 18px; font-weight: 700; color: #000; margin-bottom: 8px; }
        .card-body p { font-size: 13px; color: #555; line-height: 1.6; }
        .time-counter { color: #4a6fdc; font-weight: 600; font-style: italic; }
        
        .card-footer { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-top: 15px; }
        .btn-cek { 
            background-color: #7cd95b; 
            color: white; 
            border: none; 
            border-radius: 20px;
            width: 110px;        
            padding: 8px 0;      
            font-size: 13px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: 0.2s; 
            text-align: center;
        }
        .btn-cek:hover { background-color: #6bc242; box-shadow: 0 5px 15px rgba(124, 217, 91, 0.3); }
        .btn-book-wrapper { padding: 2px; background: linear-gradient(to right, #4169e1, #ffa3ff); border-radius: 20px; width: 110px; }
        .btn-book { width: 100%; background-color: white; border: none; border-radius: 18px; padding: 6px 0; font-size: 13px; font-weight: 600; color: #4a6fdc; cursor: pointer; text-align: center; display: block; text-decoration: none; transition: 0.2s; }
        .btn-book:hover { background-color: #4a6fdc; color: white; }

        /* --- STYLING MODAL CEK JADWAL --- */
        .modal-cek-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 2000; justify-content: center; align-items: center; backdrop-filter: blur(3px); }
        .modal-cek-box { background: #f4f7ff; width: 450px; max-height: 80vh; border-radius: 20px; padding: 25px; display: flex; flex-direction: column; position: relative; box-shadow: 0 15px 30px rgba(0,0,0,0.2); }
        .date-picker-wrapper { width: 100%; margin-bottom: 20px; }
        .date-picker-input { width: 100%; background-color: #4a6fdc; color: white; padding: 12px; border-radius: 12px; border: none; text-align: center; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: 14px; cursor: pointer; outline: none; transition: 0.2s; box-shadow: 0 5px 15px rgba(74, 111, 220, 0.2); }
        .date-picker-input:hover { background-color: #3b5bcc; }
        .date-picker-input::-webkit-calendar-picker-indicator { filter: invert(1); cursor: pointer; }
        .jadwal-list { flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; padding-right: 5px;}
        .jadwal-item { background: #dce4f7; padding: 15px; border-radius: 15px; display: flex; gap: 15px; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .jadwal-item-avatar { width: 45px; height: 45px; border-radius: 50%; background-color: white; border: 2px solid #4a6fdc; background-size: cover; background-position: center; flex-shrink: 0; }
        .jadwal-info { flex: 1; }
        .jadwal-info h4 { font-size: 14px; margin: 0 0 2px 0; color: #333; font-weight: 700; }
        .jadwal-info p { font-size: 12px; margin: 0; color: #555; }
        .sisa-waktu { color: #4a6fdc !important; font-style: italic; font-weight: 600; margin-top: 2px !important;}
        .jadwal-empty { text-align: center; color: #666; font-size: 14px; margin-top: 40px; margin-bottom: 40px; font-weight: 500;}

        /* --- STYLING MODAL PROFIL --- */
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
                            
                            <?php if ($is_booked) { 
                                $waktu_mulai_full_dashboard = $tanggal_sekarang . ' ' . $data['jam_mulai_peminjam'];
                            ?>
                                <p style="margin-bottom: 4px;"><strong><?php echo htmlspecialchars($data['peminjam']); ?></strong> - <?php echo htmlspecialchars($data['prodi_peminjam']); ?></p>
                                <p style="margin-bottom: 4px;">Waktu : <?php echo date('H:i', strtotime($data['jam_mulai_peminjam'])); ?> - <?php echo date('H:i', strtotime($data['jam_selesai_peminjam'])); ?></p>
                                <p class="time-counter"><span class="countdown" data-start="<?php echo $waktu_mulai_full_dashboard; ?>" data-end="<?php echo $data['waktu_selesai']; ?>">Loading...</span></p>
                            <?php } else { ?>
                                <p>Hari Ini Kosong nih</p> 
                            <?php } ?>
                        </div>

                        <div class="card-footer">
                            <button type="button" class="btn-cek" onclick="bukaModalCek(<?php echo $data['id']; ?>)">Cek</button>
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

    <div class="modal-cek-overlay" id="modalCekJadwal" onclick="tutupModalCek(event)">
        <div class="modal-cek-box">
            <div class="date-picker-wrapper">
                <input type="date" id="inputTanggalCek" class="date-picker-input" onchange="fetchJadwal()" title="Pilih Tanggal" min="<?php echo $tanggal_sekarang; ?>" max="<?php echo $batas_tanggal; ?>">
                <input type="hidden" id="hiddenIdAsetCek">
            </div>
            
            <div class="jadwal-list" id="jadwalListContainer">
                <div class="jadwal-empty">Pilih tanggal dulu ya!</div>
            </div>
        </div>
    </div>

    <script>
        const modalCek = document.getElementById('modalCekJadwal');
        const inputTanggalCek = document.getElementById('inputTanggalCek');
        const hiddenIdAsetCek = document.getElementById('hiddenIdAsetCek');
        const jadwalListContainer = document.getElementById('jadwalListContainer');

        function bukaModalCek(idAset) {
            hiddenIdAsetCek.value = idAset;
            inputTanggalCek.value = ""; 
            jadwalListContainer.innerHTML = '<div class="jadwal-empty">Pilih tanggal dulu ya!</div>';
            modalCek.style.display = 'flex';
        }

        function tutupModalCek(event) {
            if (event.target === modalCek) {
                modalCek.style.display = 'none';
            }
        }

        function fetchJadwal() {
            const tanggal = inputTanggalCek.value;
            const idAset = hiddenIdAsetCek.value;
            
            if (!tanggal) return;

            jadwalListContainer.innerHTML = '<div class="jadwal-empty">Loading data...</div>';

            fetch(`index.php?ajax_cek_jadwal=1&id_aset=${idAset}&tanggal=${tanggal}`)
                .then(response => response.text())
                .then(html => {
                    jadwalListContainer.innerHTML = html;
                })
                .catch(err => {
                    jadwalListContainer.innerHTML = '<div class="jadwal-empty" style="color:red;">Gagal narik data jaringan lu bapuk!</div>';
                });
        }

        setInterval(() => {
            const timers = document.querySelectorAll('.countdown');
            if(timers.length === 0) return; 

            timers.forEach(timer => {
                const startTimeStr = timer.getAttribute('data-start');
                const endTimeStr = timer.getAttribute('data-end');
                
                // Pastikan kedua data atribut tersedia
                if (!startTimeStr || !endTimeStr) return;

                // Format replace ini untuk mengatasi issue parsing tanggal di beberapa browser (terutama Safari)
                const startTime = new Date(startTimeStr.replace(/-/g, "/")).getTime();
                const endTime = new Date(endTimeStr.replace(/-/g, "/")).getTime();
                const now = new Date().getTime();

                // 1. Jika belum waktunya mulai (sekarang masih di bawah jam mulai)
                if (now < startTime) {
                    timer.innerHTML = "Waktu : Sedang Antri";
                } 
                // 2. Jika sudah masuk waktunya mulai, dan belum lewat jam selesai
                else if (now >= startTime && now <= endTime) {
                    const selisih = endTime - now;
                    const jam = Math.floor((selisih / (1000 * 60 * 60)));
                    const menit = Math.floor((selisih % (1000 * 60 * 60)) / (1000 * 60));
                    const detik = Math.floor((selisih % (1000 * 60)) / 1000);

                    timer.innerHTML = `Sisa Waktu : ${jam}j ${menit}m ${detik}d`;
                } 
                // 3. Jika waktunya sudah kelewat
                else {
                    timer.innerHTML = "Waktu Habis / Selesai";
                }
            });
        }, 1000);

    </script>
</body>
</html>