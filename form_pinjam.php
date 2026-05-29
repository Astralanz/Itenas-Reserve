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

// LOGIKA UNTUK PEMBATASAN TANGGAL (HARI INI & BESOK)
$hari_ini = date('Y-m-d');
$besok = date('Y-m-d', strtotime('+1 day'));

if (isset($_POST['submit'])) {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $nim      = mysqli_real_escape_string($conn, $_POST['nim']);
    $prodi    = mysqli_real_escape_string($conn, $_POST['prodi']);
    $tanggal  = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $mulai    = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
    $selesai  = mysqli_real_escape_string($conn, $_POST['jam_selesai']);
    $user_id  = $_SESSION['user_id']; 

    // Validasi server-side
    if ($tanggal < $hari_ini || $tanggal > $besok) {
        echo "<script>alert('❌ Maaf, booking hanya diperbolehkan untuk hari ini atau besok!'); window.history.back();</script>";
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
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #4a6fdc; /* Warna biru luar biar match sama Login page */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .form-container {
            background-color: #fcf9f2; /* Warna krem putih tulang */
            width: 550px;
            padding: 40px;
            border-radius: 35px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }

        h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 5px;
            text-align: center;
        }

        .subtitle {
            font-size: 13px;
            color: #666;
            margin-bottom: 30px;
            text-align: center;
        }

        .input-group {
            margin-bottom: 18px;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
            padding-left: 5px;
        }

        .input-control {
            width: 100%;
            padding: 12px 20px;
            border-radius: 35px;
            border: 1px solid #ccc;
            outline: none;
            font-size: 14px;
            transition: 0.3s;
            background-color: white;
        }

        .input-control:focus {
            border-color: #4a6fdc;
            box-shadow: 0 0 10px rgba(74, 111, 220, 0.1);
        }

        .row-inputs {
            display: flex;
            gap: 15px;
        }

        .row-inputs .input-group {
            flex: 1;
        }

        .action-buttons {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 30px;
            gap: 15px;
        }

        /* Wrapper Gradient Border untuk tombol submit */
        .btn-submit-wrapper {
            flex: 1.5;
            padding: 2px;
            background: linear-gradient(to right, #4169e1, #ffa3ff);
            border-radius: 35px;
        }

        .btn-submit {
            width: 100%;
            background-color: white;
            border: none;
            border-radius: 33px;
            padding: 12px 0;
            font-size: 14px;
            font-weight: 600;
            color: #4a6fdc;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-submit:hover {
            background-color: #4a6fdc;
            color: white;
        }

        .btn-cancel {
            flex: 1;
            text-align: center;
            padding: 12px 0;
            border-radius: 35px;
            border: 1px solid #dc2626;
            color: #dc2626;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-cancel:hover {
            background-color: #dc2626;
            color: white;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <h2>Pinjam: <?php echo htmlspecialchars($data_aset['nama_aset']); ?></h2>
        <p class="subtitle">Silakan lengkapi data peminjaman di bawah ini</p>

        <form action="" method="POST">
            <div class="input-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" class="input-control" placeholder="Masukkan nama lengkap lu" required>
            </div>
            
            <div class="input-group">
                <label>NIM</label>
                <input type="text" name="nim" class="input-control" placeholder="Contoh: 152024000" required>
            </div>

            <div class="input-group">
                <label>Program Studi</label>
                <input type="text" name="prodi" class="input-control" placeholder="Contoh: Informatika" required>
            </div>

            <div class="input-group">
                <label>Tanggal Pinjam</label>
                <input type="date" name="tanggal" class="input-control"
                       min="<?php echo $hari_ini; ?>" 
                       max="<?php echo $besok; ?>" 
                       value="<?php echo $hari_ini; ?>" required>
            </div>

            <div class="row-inputs">
                <div class="input-group">
                    <label>Jam Mulai</label>
                    <input type="time" name="jam_mulai" class="input-control" required>
                </div>

                <div class="input-group">
                    <label>Jam Selesai</label>
                    <input type="time" name="jam_selesai" class="input-control" required>
                </div>
            </div>

            <div class="action-buttons">
                <a href="index.php" class="btn-cancel">Batal</a>
                
                <div class="btn-submit-wrapper">
                    <button type="submit" name="submit" class="btn-submit">Ajukan Pinjaman</button>
                </div>
            </div>
        </form>
    </div>

</body>
</html>