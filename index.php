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

// Ambil email user
$email_user = $_SESSION['email'];

// --- PROSES UPLOAD FOTO PROFIL ---
if (isset($_POST['simpan_profil'])) {
    // Cek apakah ada file yang diupload dan tidak ada error
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === 0) {
        $nama_file = $_FILES['foto_profil']['name'];
        $tmp_file = $_FILES['foto_profil']['tmp_name'];
        
        // Ambil ekstensi file (misal: jpg, png)
        $ekstensi = pathinfo($nama_file, PATHINFO_EXTENSION);
        // Bikin nama file baru yang unik biar gak bentrok (contoh: 64a7b...jpg)
        $nama_file_baru = uniqid() . '.' . $ekstensi;
        $tujuan_upload = 'uploads/' . $nama_file_baru;

        // Pindahin file ke folder uploads
        if (move_uploaded_file($tmp_file, $tujuan_upload)) {
            // Update nama file di database
            mysqli_query($conn, "UPDATE users SET foto_profil = '$nama_file_baru' WHERE email = '$email_user'");
            echo "<script>alert('Foto profil berhasil diupdate, bosku!'); window.location.href='index.php';</script>";
            exit();
        } else {
            echo "<script>alert('Waduh, gagal mindahin foto ke folder uploads!');</script>";
        }
    } else {
        echo "<script>alert('Lu belum milih foto baru cuy!'); window.location.href='index.php';</script>";
    }
}

// --- AMBIL DATA USER (TERMASUK FOTO PROFIL) ---
$query_user = mysqli_query($conn, "SELECT foto_profil FROM users WHERE email = '$email_user'");
$data_user = mysqli_fetch_array($query_user);
$foto_profil_db = isset($data_user['foto_profil']) ? $data_user['foto_profil'] : '';

// Tentukan path foto (kalo kosong/file gak ada, pake foto default)
$path_foto = (!empty($foto_profil_db) && file_exists('uploads/' . $foto_profil_db)) 
             ? 'uploads/' . $foto_profil_db 
             : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';


// --- KODE PENARIK NAMA & NIM DARI GOOGLE SSO ---
$nama_google = isset($_SESSION['nama_google']) ? $_SESSION['nama_google'] : '';
$pecah_nama = explode(' ', $nama_google);

$nim_user = isset($pecah_nama[0]) && !empty($pecah_nama[0]) ? $pecah_nama[0] : 'Mahasiswa Itenas';
unset($pecah_nama[0]);
$nama_asli = implode(' ', $pecah_nama);

if (empty(trim($nama_asli))) {
    $nama_asli = explode('@', $email_user)[0];
}

// --- LOGIKA DETEKSI PRODI ---
$kode_prodi = substr($nim_user, 0, 2);
$prodi_user = "Prodi Tidak Diketahui";

switch ($kode_prodi) {
    case '11': $prodi_user = "Teknik Elektro"; break;
    case '12': $prodi_user = "Teknik Mesin"; break;
    case '13': $prodi_user = "Teknik Industri"; break;
    case '14': $prodi_user = "Teknik Kimia"; break;
    case '15': $prodi_user = "Informatika"; break;
    case '16': $prodi_user = "Sistem Informasi"; break;
    case '18': $prodi_user = "Teknik Industri (Karyawan)"; break;
    case '21': $prodi_user = "Teknik Sipil"; break;
    case '22': $prodi_user = "PWK"; break;
    case '23': $prodi_user = "Teknik Lingkungan"; break;
    case '24': $prodi_user = "Teknik Geodesi"; break;
    case '31': $prodi_user = "Desain Interior"; break;
    case '32': $prodi_user = "DKV"; break;
    case '33': $prodi_user = "Desain Produk"; break;
    case '41': $prodi_user = "Magister Teknik Industri"; break;
    case '42': $prodi_user = "Magister Teknik Sipil"; break;
    case '43': $prodi_user = "Magister PWK"; break;
}

// --- AMBIL DATA FASILITAS / ASET ---
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

$tanggal_sekarang = date('Y-m-d');
$jam_sekarang = date('H:i:s');

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

        /* --- SIDEBAR LEFT --- */
        .sidebar { width: 320px; background-color: #dce4f7; border-radius: 30px; padding: 30px 20px; display: flex; flex-direction: column; justify-content: space-between; }
        .profile-section { display: flex; align-items: center; gap: 15px; margin-bottom: 40px; padding-left: 10px; }
        
        .avatar-box {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            
            /* INI OBAT ANTI-GENCET FLEXBOX */
            flex-shrink: 0; 
            
            background-image: url('<?php echo $path_foto; ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            
            border: 3px solid white;
            cursor: pointer;
            transition: 0.2s;
            margin-top: -5px;    /* Bikin buletannya agak turun ke bawah */
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

    <div class="sidebar">
        <div>
            <div class="profile-section">
                <div class="avatar-box" onclick="bukaModalProfil()"></div>
                <div class="profile-info">
                    <h3>Hallo <?php echo htmlspecialchars($nama_asli); ?></h3>
                    <p>NIM : <?php echo htmlspecialchars($nim_user); ?></p>
                </div>
            </div>

            <ul class="menu-list">
                <li class="menu-item active"><a href="index.php">📅 Reserve</a></li>
                <li class="menu-item"><a href="riwayat.php">⏳ Waiting List</a></li>
                <li class="menu-item"><a href="rating.php">⭐ Rating and Feedback</a></li>
                <li class="menu-item"><a href="#">🎧 Customer Service</a></li>
            </ul>
        </div>

        <ul class="menu-list" style="flex-grow: 0;">
            <li class="menu-item"><a href="logout.php" style="color: #dc2626;">🚪 Logout</a></li>
        </ul>
    </div>

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

    <div class="modal-profil-overlay" id="modalProfilUser" onclick="tutupModalProfil(event)">
        <div class="modal-profil-box">
            
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="edit-avatar-container">
                    <label for="upload_profil">
                        <img src="<?php echo $path_foto; ?>" id="preview_avatar" class="edit-avatar-preview" alt="Profil">
                        <div class="edit-icon">✏️</div>
                    </label>
                    <input type="file" id="upload_profil" name="foto_profil" accept="image/*" onchange="previewImage(event)">
                </div>

                <h3 class="modal-profil-name">Hallo <?php echo htmlspecialchars($nama_asli); ?></h3>

                <div class="info-row">
                    <span class="info-label">NIM</span><span class="info-colon">:</span><span class="info-value"><?php echo htmlspecialchars($nim_user); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Prodi</span><span class="info-colon">:</span><span class="info-value"><?php echo htmlspecialchars($prodi_user); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span><span class="info-colon">:</span><span class="info-value"><?php echo htmlspecialchars($email_user); ?></span>
                </div>

                <div class="btn-simpan-wrapper">
                    <button type="submit" name="simpan_profil" class="btn-simpan">Simpan</button>
                </div>
            </form>

        </div>
    </div>

    <script>
        const modalProfil = document.getElementById('modalProfilUser');

        function bukaModalProfil() { modalProfil.style.display = 'flex'; }
        function tutupModalProfil(event) { if (event.target === modalProfil) { modalProfil.style.display = 'none'; } }

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('preview_avatar');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

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
                        setTimeout(() => { window.location.reload(); }, 1500); 
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