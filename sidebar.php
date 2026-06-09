<?php
// Ambil email user (karena file ini di-include, session_start() udah dipanggil di file utama)
$email_user = $_SESSION['email'];
$current_url = basename($_SERVER['PHP_SELF']); // Deteksi halaman aktif

// --- PROSES UPLOAD FOTO PROFIL ---
if (isset($_POST['simpan_profil'])) {
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === 0) {
        $nama_file = $_FILES['foto_profil']['name'];
        $tmp_file = $_FILES['foto_profil']['tmp_name'];
        $ekstensi = pathinfo($nama_file, PATHINFO_EXTENSION);
        $nama_file_baru = uniqid() . '.' . $ekstensi;
        $tujuan_upload = 'uploads/' . $nama_file_baru;

        if (move_uploaded_file($tmp_file, $tujuan_upload)) {
            mysqli_query($conn, "UPDATE users SET foto_profil = '$nama_file_baru' WHERE email = '$email_user'");
            echo "<script>alert('Foto profil berhasil diupdate!'); window.location.href='$current_url';</script>";
            exit();
        } else {
            echo "<script>alert('Waduh, gagal mindahin foto ke folder uploads!');</script>";
        }
    } else {
        echo "<script>alert('Lu belum milih foto baru cuy!'); window.location.href='$current_url';</script>";
    }
}

// --- AMBIL DATA USER (TERMASUK FOTO PROFIL) ---
$query_user = mysqli_query($conn, "SELECT foto_profil FROM users WHERE email = '$email_user'");
$data_user = mysqli_fetch_array($query_user);
$foto_profil_db = isset($data_user['foto_profil']) ? $data_user['foto_profil'] : '';
$path_foto = (!empty($foto_profil_db) && file_exists('uploads/' . $foto_profil_db)) ? 'uploads/' . $foto_profil_db : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';

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
?>

<style>
/* Tombol WhatsApp Melayang */
.floating-whatsapp {
    position: fixed;
    right: 28px;
    bottom: 28px;
    width: 58px;
    height: 58px;
    background: #25D366;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 24px rgba(0,0,0,0.18);
    z-index: 9999;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.floating-whatsapp:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 26px rgba(0,0,0,0.24);
}
.floating-whatsapp svg {
    width: 28px;
    height: 28px;
    fill: #ffffff;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.25));
}

/* Pastikan avatar di sidebar bisa diklik dan ada ukurannya */
.avatar-box {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-size: cover;
    background-position: center;
    cursor: pointer;
    transition: opacity 0.3s ease;
}
.avatar-box:hover {
    opacity: 0.8;
}

/* Base style untuk modal overlay agar tidak berantakan */
.modal-profil-overlay {
    display: none; /* Sembunyikan saat pertama load */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 10000;
    justify-content: center;
    align-items: center;
}
</style>

<div class="sidebar">
    <div>
        <div class="profile-section">
            <div class="avatar-box" style="background-image: url('<?php echo $path_foto; ?>');" onclick="bukaModalProfil()"></div>
            <div class="profile-info">
                <h3>Hallo <?php echo htmlspecialchars($nama_asli); ?></h3>
                <p>NIM : <?php echo htmlspecialchars($nim_user); ?></p>
            </div>
        </div>

        <ul class="menu-list">
            <li class="menu-item <?php echo $current_url == 'index.php' ? 'active' : ''; ?>"><a href="index.php">📅 Reserve</a></li>
            <li class="menu-item <?php echo $current_url == 'riwayat.php' ? 'active' : ''; ?>"><a href="riwayat.php">⏳ Waiting List</a></li>
            <li class="menu-item <?php echo $current_url == 'rating.php' ? 'active' : ''; ?>"><a href="rating.php">⭐ Rating and Feedback</a></li>
        </ul>
    </div>

    <ul class="menu-list" style="flex-grow: 0;">
        <li class="menu-item"><a href="logout.php" style="color: #dc2626;">🚪 Logout</a></li>
    </ul>
</div>

<a href="https://api.whatsapp.com/send?phone=6285189920482&text=Halo%20Admin%20Itenas%20Reserve,%20Saya%20ingin%20bertanya%20mengenai%20peminjaman%20aset" class="floating-whatsapp" target="_blank" rel="noopener noreferrer" aria-label="Chat WhatsApp Customer Service">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.52 3.48A11.76 11.76 0 0 0 12 0C5.37 0 0 5.37 0 12c0 2.08.55 4.13 1.6 5.95L0 24l6.32-1.64A11.94 11.94 0 0 0 12 24c6.63 0 12-5.37 12-12 0-3.22-1.25-6.25-3.48-8.52zm-8.15 18.16c-1.95 0-3.85-.52-5.53-1.5l-.4-.24-3.75.98.99-3.65-.26-.38A9.76 9.76 0 0 1 2.25 12c0-5.36 4.36-9.75 9.75-9.75 2.6 0 5.05 1.01 6.88 2.84 1.82 1.83 2.84 4.28 2.84 6.87 0 5.39-4.4 9.75-9.77 9.75zm5.35-7.03c-.29-.15-1.71-.85-1.98-.95-.27-.1-.47-.15-.67.15-.19.29-.75.95-.92 1.15-.17.19-.34.21-.63.07-.29-.15-1.22-.45-2.33-1.44-.86-.77-1.44-1.71-1.61-2-.17-.29-.02-.45.13-.6.13-.13.29-.34.43-.51.14-.17.19-.3.29-.5.1-.19.05-.36-.02-.51-.07-.15-.67-1.62-.92-2.22-.24-.58-.48-.5-.67-.51-.17-.01-.36-.01-.55-.01-.19 0-.5.07-.76.36-.27.29-1.05 1.03-1.05 2.51s1.08 2.9 1.23 3.1c.15.19 2.12 3.24 5.14 4.54.72.31 1.28.5 1.72.64.72.23 1.38.2 1.9.12.58-.09 1.71-.7 1.95-1.37.24-.67.24-1.25.17-1.37-.07-.12-.27-.19-.56-.34z"/></svg>
</a>

<div class="modal-profil-overlay" id="modalProfilUser" onclick="tutupModalProfil(event)">
    <div class="modal-profil-box">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="edit-avatar-container">
                <label for="upload_profil" style="cursor: pointer;">
                    <img src="<?php echo $path_foto; ?>" id="preview_avatar" class="edit-avatar-preview" alt="Profil">
                    <div class="edit-icon">✏️</div>
                </label>
                <input type="file" id="upload_profil" name="foto_profil" accept="image/*" onchange="previewImage(event)" style="display: none;">
            </div>
            <h3 class="modal-profil-name">Hallo <?php echo htmlspecialchars($nama_asli); ?></h3>
            <div class="info-row"><span class="info-label">NIM</span><span class="info-colon">:</span><span class="info-value"><?php echo htmlspecialchars($nim_user); ?></span></div>
            <div class="info-row"><span class="info-label">Prodi</span><span class="info-colon">:</span><span class="info-value"><?php echo htmlspecialchars($prodi_user); ?></span></div>
            <div class="info-row"><span class="info-label">Email</span><span class="info-colon">:</span><span class="info-value"><?php echo htmlspecialchars($email_user); ?></span></div>
            <br>
            <div class="btn-simpan-wrapper">
                <button type="submit" name="simpan_profil" class="btn-simpan">Simpan Profil</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Buka Modal
    function bukaModalProfil() { 
        document.getElementById('modalProfilUser').style.display = 'flex'; 
    }
    
    // Tutup Modal (Bila klik area gelap)
    function tutupModalProfil(event) { 
        const modal = document.getElementById('modalProfilUser');
        if (event.target === modal) { 
            modal.style.display = 'none'; 
        } 
    }
    
    // Preview gambar saat diunggah
    function previewImage(event) {
        if (event.target.files && event.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e){ 
                document.getElementById('preview_avatar').src = e.target.result; 
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>