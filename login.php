<?php
session_start();
include 'koneksi.php';

// Kalo udah login, lempar ke halaman masing-masing sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_aset.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

// Logika Login pake Google Token GSI
if (isset($_POST['credential'])) {
    $jwt = $_POST['credential'];
    $token_parts = explode('.', $jwt);
    $payload = json_decode(base64_decode($token_parts[1]), true);
    
    $email = $payload['email'];
    $nama_google = $payload['name']; // TANGKAP NAMA GOOGLE DISINI

    // 1. CARI EMAILNYA DI DATABASE DULU
    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($cek_user) > 0) {
        $data_user = mysqli_fetch_array($cek_user);
        
        // JIKA DIA ADMIN: Langsung lempar ke admin_aset.php
        if ($data_user['role'] == 'admin') {
            $_SESSION['user_id'] = $data_user['id'];
            $_SESSION['email']   = $data_user['email'];
            $_SESSION['role']    = 'admin';
            $_SESSION['nama_google'] = $nama_google; 
            
            echo "<script>alert('Login Admin Berhasil! Selamat Datang Admin.'); window.location.href='admin_aset.php';</script>";
            exit();
        } 
        // JIKA DIA USER BIASA: Pastikan domainnya @mhs.itenas.ac.id dan lempar ke index.php
        else {
            if (strpos($email, '@mhs.itenas.ac.id') !== false) {
                $_SESSION['user_id'] = $data_user['id'];
                $_SESSION['email']   = $data_user['email'];
                $_SESSION['role']    = $data_user['role'];
                $_SESSION['nama_google'] = $nama_google;
                
                echo "<script>alert('Login Berhasil, Selamat Datang Mahasiswa Itenas!'); window.location.href='index.php';</script>";
                exit();
            } else {
                echo "<script>alert('❌ Akses Ditolak! Wajib menggunakan email @mhs.itenas.ac.id'); window.location.href='login.php';</script>";
                exit();
            }
        }
    } 
    // 2. JIKA EMAIL BELUM TERDAFTAR DI DATABASE (Pendaftar Baru)
    else {
        // Karena admin harus didaftarkan manual, berarti pendaftar baru otomatis adalah Mahasiswa
        if (strpos($email, '@mhs.itenas.ac.id') !== false) {
            mysqli_query($conn, "INSERT INTO users (email, role) VALUES ('$email', 'user')");
            
            $_SESSION['user_id'] = mysqli_insert_id($conn);
            $_SESSION['email']   = $email;
            $_SESSION['role']    = 'user';
            $_SESSION['nama_google'] = $nama_google;
            
            echo "<script>alert('Login Berhasil, Selamat Datang Mahasiswa Itenas!'); window.location.href='index.php';</script>";
            exit();
        } else {
            echo "<script>alert('❌ Akses Ditolak! Akun tidak terdaftar atau wajib menggunakan email @mhs.itenas.ac.id'); window.location.href='login.php';</script>";
            exit();
        }
    }
}

// Hitung total fasilitas realtime untuk di card kaca
$hitung_aset = mysqli_query($conn, "SELECT COUNT(id) as total FROM aset");
$jumlah_aset = mysqli_fetch_array($hitung_aset);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Itenas Reserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background-color: #4a6fdc; 
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .main-container {
            background-color: #fcf9f2; 
            width: 1500px; 
            height: 700px; 
            border-radius: 35px;
            display: flex;
            padding: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }
        
        .left-side {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        .left-side h2 {
            font-size: 24px;
            font-weight: 700;
            color: #000;
            margin-bottom: 30px;
            position: relative; 
            top: -70px; 
            left: -15px; 
        }
        .avatar-img {   
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 40px;
            object-fit: cover;
            position: relative; 
            top: -70px; 
            left: -15px; 
        }

        .google-btn-wrapper {
            width: max-content;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            top: -30px;
            left: -15px;

            padding: 3px;
            background: linear-gradient(to right, #4169e1, #ffa3ff);
            border-radius: 35px;
            z-index: 1;
        }
        
        .google-btn-wrapper::after {
            content: '';
            position: absolute;
            top: 3px; 
            left: 3px;
            right: 3px;
            bottom: 3px;
            background-color: #fcf9f2; 
            border-radius: 35px;
            z-index: -1;
        }

        .login-hint {
            font-size: 11px;
            color: #666;
            margin-top: 15px;
            position: relative;
            top: -30px; 
            left: -15px; 
        }

        .right-side {
            flex: 1.8;
            border-radius: 35px;
            background: linear-gradient(to bottom, rgba(100, 130, 200, 0.4), rgba(70, 100, 180, 0.6)), url('https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80') center/cover;
            position: relative;
            padding: 40px;
            color: white;
            overflow: hidden;
        }
        .right-side h1 {
            font-size: 36px;
            margin: 0;
            font-weight: 700;
            line-height: 1.2;
        }
        .right-side p {
            font-size: 14px;
            margin-top: 5px;
            opacity: 0.9;
        }

        .glass-card {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 20px 30px;
            border-radius: 35px;
            color: #333;
            border: 1px solid rgba(255, 255, 255, 0.5);
            min-width: 220px;
        }
        .glass-card p {
            margin: 0;
            font-size: 12px;
            font-weight: 600;
            color: #333;
            line-height: 1.4;
        }
        .glass-card h2 {
            margin: 10px 0 0 0;
            font-size: 42px;
            font-weight: 700;
            color: #000;
        }
    </style>
</head>
<body>

    <div class="main-container">
        
        <div class="left-side">
            <h2>Hallo Mahasiswa!</h2>
            
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Avatar" class="avatar-img">
            
            <div class="google-btn-wrapper">
                <div id="g_id_onload"
                     data-client_id="877360129817-q5hmfbr5aoveag1pksldgqtba983g7fk.apps.googleusercontent.com" 
                     data-context="signin"
                     data-ux_mode="popup"
                     data-login_uri="http://localhost/itenas_minjem/login.php"
                     data-auto_prompt="false">
                </div>
                
               <div class="g_id_signin"
                     data-type="standard"
                     data-shape="pill"
                     data-theme="none" data-text="signin_with"
                     data-size="large"
                     data-logo_alignment="left"
                     data-width="260">
                </div>
            </div>
            
            <p class="login-hint">Login dengan akun Itenas yaaa!</p>
        </div>

        <div class="right-side">
            <h1>Itenas Reserve</h1>
            <p>Website peminjaman fasilitas<br>Itenas</p>

            <div class="glass-card">
                <p>Jumlah fasilitas yang<br>bisa di pinjam</p>
                <h2><?php echo $jumlah_aset['total']; ?></h2>
            </div>
        </div>

    </div>

</body>
</html>