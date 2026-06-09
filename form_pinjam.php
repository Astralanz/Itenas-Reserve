<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'koneksi.php';

// Menyesuaikan dengan parameter kiriman dari halaman utama (?id_aset=)
if (!isset($_GET['id_aset']) || empty($_GET['id_aset'])) {
    die("<script>alert('Error: Pilih aset dulu dari halaman utama!'); window.location.href='index.php';</script>");
}

$id_aset = mysqli_real_escape_string($conn, $_GET['id_aset']);
$query_aset = mysqli_query($conn, "SELECT nama_aset FROM aset WHERE id = '$id_aset'");

// Pengaman tambahan kalau id aset nggak ada di database
if (mysqli_num_rows($query_aset) == 0) {
    die("<script>alert('Error: Aset tidak ditemukan atau sudah dihapus!'); window.location.href='index.php';</script>");
}

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
date_default_timezone_set('Asia/Jakarta');
$hari_ini = date('Y-m-d');
$batas_tanggal = date('Y-m-d', strtotime('+2 days')); // Hari ini, Besok, Lusa
$jam_sekarang_server = date('H:i'); // Ambil jam server saat ini

// =========================================================================
// --- LOGIKA AJAX SUBMIT BOOKING (BEKERJA DI BELAKANG LAYAR) ---
// =========================================================================
if (isset($_POST['ajax_submit'])) {
    header('Content-Type: application/json');
    
    $nama     = mysqli_real_escape_string($conn, $nama_asli);
    $nim      = mysqli_real_escape_string($conn, $nim_user);
    $prodi    = mysqli_real_escape_string($conn, $prodi_user);
    
    $tanggal  = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $mulai    = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
    $selesai  = mysqli_real_escape_string($conn, $_POST['jam_selesai']);
    $user_id  = $_SESSION['user_id']; 

    // 1. Validasi Tanggal
    if ($tanggal < $hari_ini || $tanggal > $batas_tanggal) {
        echo json_encode(['status' => 'error', 'pesan' => 'Maaf, booking hanya bisa untuk hari ini sampai 2 hari ke depan!']);
        exit();
    }

    // 2. Validasi Jam Operasional (06:00 - 18:00)
    if ($mulai < '06:00' || $selesai > '18:00') {
        echo json_encode(['status' => 'error', 'pesan' => 'Maaf bos, jam operasional cuma dari jam 06:00 sampai 18:00!']);
        exit();
    }
    
    // 3. Validasi Logika Jam Terbalik
    if ($mulai >= $selesai) {
        echo json_encode(['status' => 'error', 'pesan' => 'Jam mulai gak boleh melebihi atau sama dengan jam selesai!']);
        exit();
    }

    // 4. Validasi Jam Udah Lewat (PERTahanan Backend)
    if ($tanggal == $hari_ini && $mulai <= $jam_sekarang_server) {
        echo json_encode(['status' => 'error', 'pesan' => 'Waktu booking invalid! Jam yang lu pilih udah lewat bos.']);
        exit();
    }

    $query_insert = "INSERT INTO peminjaman (user_id, aset_id, nama_peminjam, nim, prodi, tanggal_pinjam, jam_mulai, jam_selesai, status) 
                     VALUES ('$user_id', '$id_aset', '$nama', '$nim', '$prodi', '$tanggal', '$mulai', '$selesai', 'pending')";
    
    if (mysqli_query($conn, $query_insert)) {
        echo json_encode(['status' => 'success', 'pesan' => '🎉 Pengajuan berhasil! Menunggu persetujuan Admin.']);
    } else {
        echo json_encode(['status' => 'error', 'pesan' => 'Wah gagal nyimpen data: ' . mysqli_error($conn)]);
    }
    exit();
}
// =========================================================================
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking - Itenas Reserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        
        /* Dropdown options styling for disabled */
        select option:disabled { color: #aaa; font-style: italic; }

        .row-inputs { display: flex; gap: 15px; }
        .row-inputs .input-group { flex: 1; }
        .action-buttons { display: flex; align-items: center; justify-content: space-between; margin-top: 30px; gap: 15px; }
        .btn-submit-wrapper { flex: 1.5; padding: 2px; background: linear-gradient(to right, #4169e1, #ffa3ff); border-radius: 35px; }
        .btn-submit { width: 100%; background-color: white; border: none; border-radius: 33px; padding: 12px 0; font-size: 14px; font-weight: 600; color: #4a6fdc; cursor: pointer; transition: 0.2s; }
        .btn-submit:hover { background-color: #4a6fdc; color: white; }
        .btn-submit:disabled { background-color: #e9ecef; color: #888; cursor: not-allowed; } 
        .btn-cancel { flex: 1; text-align: center; padding: 12px 0; border-radius: 35px; border: 1px solid #dc2626; color: #dc2626; text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.3s; }
        .btn-cancel:hover { background-color: #dc2626; color: white; }
        .note-text { font-size: 11px; color: #e50000; font-style: italic; margin-top: 5px; padding-left: 5px; display: block; }
    </style>
</head>
<body>

    <div class="form-container">
        <h2>Pinjam: <?php echo htmlspecialchars($data_aset['nama_aset']); ?></h2>
        <p class="subtitle">Isi dulu ya</p>

        <form id="formBooking" action="" method="POST">
            
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
                <input type="date" name="tanggal" class="input-control" id="inputTanggal"
                       min="<?php echo $hari_ini; ?>" 
                       max="<?php echo $batas_tanggal; ?>" 
                       value="<?php echo $hari_ini; ?>" required>
                <span class="note-text">*Maksimal booking untuk 3 hari (hari ini sampai lusa)</span>
            </div>

            <div class="row-inputs">
                <div class="input-group">
                    <label>Jam Mulai</label>
                    <select name="jam_mulai" class="input-control" id="selectJamMulai" required>
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

                <div class="input-group">
                    <label>Jam Selesai</label>
                    <select name="jam_selesai" class="input-control" id="selectJamSelesai" required>
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
                    <button type="submit" class="btn-submit" id="btnSubmitAJAX">Ajukan Pinjaman</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        const tanggalInput = document.getElementById('inputTanggal');
        const jamMulai = document.getElementById('selectJamMulai');
        const jamSelesai = document.getElementById('selectJamSelesai');
        const formBooking = document.getElementById('formBooking');
        const btnSubmit = document.getElementById('btnSubmitAJAX');
        
        // Patokan tanggal hari ini dari server PHP biar gak ngaco
        const hariIniServer = "<?php echo $hari_ini; ?>";

        // --- FUNGSI MENGUNCI JAM YANG SUDAH LEWAT ---
        function updateJamKadaluarsa() {
            const selectedDate = tanggalInput.value;
            const isToday = (selectedDate === hariIniServer);

            // Bikin objek waktu dari laptop user untuk kelancaran UI secara Real-Time
            const now = new Date();
            const jam = String(now.getHours()).padStart(2, '0');
            const menit = String(now.getMinutes()).padStart(2, '0');
            const waktuSekarang = `${jam}:${menit}`;

            [jamMulai, jamSelesai].forEach(selectBox => {
                Array.from(selectBox.options).forEach(option => {
                    if (option.value === "") return; // Skip opsi default (--:--)

                    // Kalau milih tanggal HARI INI dan jamnya lebih kecil dari jam sekarang = DISABLE
                    if (isToday && option.value <= waktuSekarang) {
                        option.disabled = true;
                        option.style.backgroundColor = "#e9ecef"; // warna abu-abu
                    } else {
                        // Kalau besok/lusa, bebasin semua jamnya
                        option.disabled = false;
                        option.style.backgroundColor = "white";
                    }
                });

                // Kalau tiba-tiba opsi yang udah terlanjur diklik jadi 'disabled' (misal nunggu kelamaan), reset isinya
                if (selectBox.options[selectBox.selectedIndex] && selectBox.options[selectBox.selectedIndex].disabled) {
                    selectBox.value = "";
                }
            });
        }

        // Panggil saat tanggal diubah dan saat pertama kali halaman diload
        tanggalInput.addEventListener('change', updateJamKadaluarsa);
        updateJamKadaluarsa();

        // --- LOGIKA Pengecekan Jam Mulai vs Jam Selesai ---
        function cekLogikaJam() {
            if (jamMulai.value && jamSelesai.value) {
                if (jamMulai.value >= jamSelesai.value) {
                    Swal.fire({
                        title: 'Oops!',
                        text: 'Jam mulai gak boleh lebih besar atau sama dengan jam selesai, bosku!',
                        icon: 'warning',
                        confirmButtonColor: '#ff1a73'
                    });
                    jamSelesai.value = ""; 
                }
            }
        }

        jamMulai.addEventListener('change', cekLogikaJam);
        jamSelesai.addEventListener('change', cekLogikaJam);

        // --- EKSEKUSI AJAX PAS FORM DIKIRIM ---
        formBooking.addEventListener('submit', function(e) {
            e.preventDefault(); 

            const formData = new FormData(this);
            formData.append('ajax_submit', '1'); 

            const originalText = btnSubmit.innerHTML;
            btnSubmit.innerHTML = 'Mengirim Data...';
            btnSubmit.disabled = true;

            fetch(`form_pinjam.php?id_aset=<?php echo $id_aset; ?>`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;

                if(data.status === 'success') {
                    Swal.fire({
                        title: 'Mantap!',
                        text: data.pesan,
                        icon: 'success',
                        confirmButtonColor: '#4a6fdc'
                    }).then(() => {
                        window.location.href = 'index.php';
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: data.pesan,
                        icon: 'error',
                        confirmButtonColor: '#ff1a73'
                    });
                }
            })
            .catch(error => {
                btnSubmit.innerHTML = originalText;
                btnSubmit.disabled = false;
                Swal.fire('Error!', 'Jaringan lu lagi bapuk kayaknya bos, coba lagi!', 'error');
            });
        });
    </script>

</body>
</html>