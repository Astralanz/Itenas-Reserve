<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'koneksi.php';

// Menyesuaikan dengan parameter kiriman dari halaman utama (?id_aset=)
if (!isset($_GET['id_aset'])) {
    die("<script>alert('Error: Pilih aset dulu dari halaman utama!'); window.location.href='index.php';</script>");
}

$id_aset = $_GET['id_aset'];
$query_aset = mysqli_query($conn, "SELECT nama_aset FROM aset WHERE id = '$id_aset'");
$data_aset = mysqli_fetch_array($query_aset);

// --- LOGIKA TARIK NAMA, NIM, PRODI OTOMATIS ---
$email_user = $_SESSION['email'];
$nama_google = isset($_SESSION['nama_google']) ? $_SESSION['nama_google'] : '';
$pecah_nama = explode(' ', $nama_google);

$nim_user = isset($pecah_nama[0]) && !empty($pecah_nama[0]) ? $pecah_nama[0] : 'Mahasiswa Itenas';
unset($pecah_nama[0]);
$nama_asli = implode(' ', $pecah_nama);

if (empty(trim($nama_asli))) {
    $nama_asli = explode('@', $email_user)[0];
}

$kode_prodi = substr($nim_user, 0, 2);
$prodi_user = "Prodi Tidak Diketahui";
$list_prodi = [
    '11'=>'Teknik Elektro', '12'=>'Teknik Mesin', '13'=>'Teknik Industri', '14'=>'Teknik Kimia', 
    '15'=>'Informatika', '16'=>'Sistem Informasi', '18'=>'Teknik Industri (Karyawan)', 
    '21'=>'Teknik Sipil', '22'=>'PWK', '23'=>'Teknik Lingkungan', '24'=>'Teknik Geodesi', 
    '31'=>'Desain Interior', '32'=>'DKV', '33'=>'Desain Produk', 
    '41'=>'Magister Teknik Industri', '42'=>'Magister Teknik Sipil', '43'=>'Magister PWK'
];
if(isset($list_prodi[$kode_prodi])) $prodi_user = $list_prodi[$kode_prodi];

// --- LOGIKA UNTUK PEMBATASAN TANGGAL (MAKSIMAL 3 HARI) ---
$hari_ini = date('Y-m-d');
$batas_tanggal = date('Y-m-d', strtotime('+2 days')); // Hari ini, Besok, Lusa

if (isset($_POST['submit'])) {
    $nama     = mysqli_real_escape_string($conn, $nama_asli);
    $nim      = mysqli_real_escape_string($conn, $nim_user);
    $prodi    = mysqli_real_escape_string($conn, $prodi_user);
    
    $tanggal  = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $mulai    = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
    $selesai  = mysqli_real_escape_string($conn, $_POST['jam_selesai']);
    $user_id  = $_SESSION['user_id']; 

    // 1. Validasi Tanggal Server
    if ($tanggal < $hari_ini || $tanggal > $batas_tanggal) {
        echo "<script>alert('❌ Maaf, booking hanya bisa untuk hari ini sampai 2 hari ke depan!'); window.history.back();</script>";
        exit();
    }

    // 2. Validasi Jam Server (06:00 - 18:00)
    if ($mulai < '06:00' || $selesai > '18:00') {
        echo "<script>alert('❌ Maaf bos, jam operasional cuma dari jam 06:00 sampai 18:00!'); window.history.back();</script>";
        exit();
    }
    
    // 3. Validasi Logika Jam
    if ($mulai >= $selesai) {
        echo "<script>alert('❌ Jam mulai gak boleh melebihi atau sama dengan jam selesai!'); window.history.back();</script>";
        exit();
    }

    $query_insert = "INSERT INTO peminjaman (user_id, aset_id, nama_peminjam, nim, prodi, tanggal_pinjam, jam_mulai, jam_selesai, status) 
                     VALUES ('$user_id', '$id_aset', '$nama', '$nim', '$prodi', '$tanggal', '$mulai', '$selesai', 'pending')";
    
    if (mysqli_query($conn, $query_insert)) {
        echo "<script>
                alert('🎉 Pengajuan berhasil! Status: Menunggu persetujuan Admin.');
                window.location.href='index.php';
              </script>";
    } else {
        echo "Wah gagal nyimpen data: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking - Itenas Reserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { background-color: #4a6fdc; height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .form-container { background-color: #fcf9f2; width: 550px; padding: 40px; border-radius: 35px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); }
        h2 { font-size: 24px; font-weight: 700; color: #1a1a1a; margin-bottom: 5px; text-align: center; }
        .subtitle { font-size: 13px; color: #666; margin-bottom: 30px; text-align: center; }
        .input-group { margin-bottom: 18px; }
        .input-group label { display: block; font-size: 13px; font-weight: 600; color: #333; margin-bottom: 6px; padding-left: 5px; }
        .input-control { width: 100%; padding: 12px 20px; border-radius: 35px; border: 1px solid #ccc; outline: none; font-size: 14px; transition: 0.3s; background-color: white; }
        .input-control:focus { border-color: #4a6fdc; box-shadow: 0 0 10px rgba(74, 111, 220, 0.1); }
        .input-readonly { background-color: #e9ecef; color: #666; cursor: not-allowed; border-color: #ddd; }
        .row-inputs { display: flex; gap: 15px; }
        .row-inputs .input-group { flex: 1; }
        .action-buttons { display: flex; align-items: center; justify-content: space-between; margin-top: 30px; gap: 15px; }
        .btn-submit-wrapper { flex: 1.5; padding: 2px; background: linear-gradient(to right, #4169e1, #ffa3ff); border-radius: 35px; }
        .btn-submit { width: 100%; background-color: white; border: none; border-radius: 33px; padding: 12px 0; font-size: 14px; font-weight: 600; color: #4a6fdc; cursor: pointer; transition: 0.2s; }
        .btn-submit:hover { background-color: #4a6fdc; color: white; }
        .btn-cancel { flex: 1; text-align: center; padding: 12px 0; border-radius: 35px; border: 1px solid #dc2626; color: #dc2626; text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.3s; }
        .btn-cancel:hover { background-color: #dc2626; color: white; }
        .note-text { font-size: 11px; color: #e50000; font-style: italic; margin-top: 5px; padding-left: 5px; display: block; }
    </style>
</head>
<body>

    <div class="form-container">
        <h2>Pinjam: <?php echo htmlspecialchars($data_aset['nama_aset']); ?></h2>
        <p class="subtitle">Isi dulu ya</p>

        <form action="" method="POST">
            
            <div class="input-group">
                <label>Nama Lengkap</label>
                <input type="text" class="input-control input-readonly" value="<?php echo htmlspecialchars($nama_asli); ?>" readonly>
            </div>
            
            <div class="row-inputs">
                <div class="input-group">
                    <label>NIM</label>
                    <input type="text" class="input-control input-readonly" value="<?php echo htmlspecialchars($nim_user); ?>" readonly>
                </div>

                <div class="input-group">
                    <label>Program Studi</label>
                    <input type="text" class="input-control input-readonly" value="<?php echo htmlspecialchars($prodi_user); ?>" readonly>
                </div>
            </div>

            <div class="input-group">
                <label>Tanggal Pinjam</label>
                <input type="date" name="tanggal" class="input-control"
                       min="<?php echo $hari_ini; ?>" 
                       max="<?php echo $batas_tanggal; ?>" 
                       value="<?php echo $hari_ini; ?>" required>
                <span class="note-text">*Maksimal booking untuk 3 hari (hari ini sampai lusa)</span>
            </div>

            <div class="row-inputs">
                <div class="input-group">
                    <label>Jam Mulai</label>
                    <select name="jam_mulai" class="input-control" required>
                        <option value="" disabled selected>--:--</option>
                        <?php
                        for ($h = 6; $h <= 18; $h++) {
                            for ($m = 0; $m < 60; $m += 30) { // Interval 30 menit, ganti ke += 15 kalau mau per 15 menit
                                if ($h == 18 && $m > 0) break;
                                $time = sprintf('%02d:%02d', $h, $m);
                                echo "<option value='$time'>$time</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="input-group">
                    <label>Jam Selesai</label>
                    <select name="jam_selesai" class="input-control" required>
                        <option value="" disabled selected>--:--</option>
                        <?php
                        for ($h = 6; $h <= 18; $h++) {
                            for ($m = 0; $m < 60; $m += 30) { 
                                if ($h == 18 && $m > 0) break;
                                $time = sprintf('%02d:%02d', $h, $m);
                                echo "<option value='$time'>$time</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <span class="note-text" style="margin-top: -10px; margin-bottom: 10px;">*Format 24 Jam. Operasional: 06:00 - 18:00 WIB</span>

            <div class="action-buttons">
                <a href="index.php" class="btn-cancel">Batal</a>
                <div class="btn-submit-wrapper">
                    <button type="submit" name="submit" class="btn-submit">Ajukan Pinjaman</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        const jamMulai = document.querySelector('select[name="jam_mulai"]');
        const jamSelesai = document.querySelector('select[name="jam_selesai"]');

        function cekLogikaJam() {
            if (jamMulai.value && jamSelesai.value) {
                if (jamMulai.value >= jamSelesai.value) {
                    alert("❌ Jam mulai gak boleh lebih besar atau sama dengan jam selesai, bosku!");
                    jamSelesai.value = ""; // Reset paksa isi pilihan jam selesai
                }
            }
        }

        jamMulai.addEventListener('change', cekLogikaJam);
        jamSelesai.addEventListener('change', cekLogikaJam);
    </script>

</body>
</html>