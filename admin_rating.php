<?php
session_start();
include 'koneksi.php';

// Atur timezone
date_default_timezone_set('Asia/Jakarta');

// Proteksi halaman admin + Cek Role (Biar mhs ga bisa tembus)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- AMBIL DATA RATING DARI DATABASE ---
$query_rating = mysqli_query($conn, "SELECT * FROM rating ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Rating dari User</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { 
            background: linear-gradient(to bottom right, #fff0f5, #ffffff); 
            height: 100vh; display: flex; padding: 20px; gap: 20px; overflow: hidden;
        }
        
        /* --- SIDEBAR --- */
        .sidebar-container { width: 280px; display: flex; flex-direction: column; gap: 15px; flex-shrink: 0; }
        
        .sidebar-header { 
            background-color: #ff1a73; border-radius: 20px; padding: 25px 20px; 
            color: white; box-shadow: 0 10px 20px rgba(255, 26, 115, 0.2);
        }
        .sidebar-header h2 { font-size: 22px; font-weight: 800; line-height: 1.2; text-transform: uppercase; }
        
        .sidebar-menu { 
            background-color: #ff1a73; border-radius: 20px; padding: 30px 15px; 
            flex: 1; color: white; box-shadow: 0 10px 20px rgba(255, 26, 115, 0.2);
            display: flex; flex-direction: column; justify-content: space-between; /* Menjaga posisi logout tetap di bawah */
            overflow-y: auto;
        }
        .menu-list { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .menu-item a { 
            display: flex; align-items: center; gap: 15px; padding: 12px 20px; 
            color: white; text-decoration: none; font-weight: 600; font-size: 15px; 
            border-radius: 15px; transition: 0.3s;
        }
        
        /* Menu Aktif dipindah ke Rating */
        .menu-item.active a { background-color: white; color: black; }
        .menu-item:not(.active) a:hover { background-color: rgba(255, 255, 255, 0.1); }
        
        .sub-menu { margin-left: 45px; margin-top: 5px; display: flex; flex-direction: column; gap: 8px; }
        .sub-menu a { color: white; text-decoration: none; font-size: 13px; font-style: italic; opacity: 0.9; }
        .sub-menu a:hover { opacity: 1; text-decoration: underline; }

        /* --- MAIN CONTENT --- */
        .main-content { flex: 1; display: flex; flex-direction: column; padding: 10px 20px; overflow: hidden; }
        .page-title { text-align: center; color: #ff1a73; font-size: 32px; font-weight: 800; margin-bottom: 30px; letter-spacing: 1px; }

        /* --- LIST REVIEW ORANG-ORANG --- */
        .rating-container { display: flex; flex-direction: column; flex: 1; overflow: hidden; }
        
        .reviews-list { 
            flex: 1; overflow-y: auto; background-color: #eef2fc; /* Warna background soft kayak di desain */
            padding: 25px 35px; border-radius: 25px; display: flex; flex-direction: column; 
        }
        
        .review-item { 
            display: flex; gap: 20px; padding-bottom: 20px; padding-top: 20px; 
            border-bottom: 1.5px solid #ff99c2; /* Garis pemisah warna pink soft */
        }
        .review-item:first-child { padding-top: 0; }
        .review-item:last-child { border-bottom: none; padding-bottom: 0; }
        
        .review-avatar { 
            width: 55px; height: 55px; border-radius: 50%; 
            background: url('https://cdn-icons-png.flaticon.com/512/3135/3135715.png') center/cover; 
            border: 3px solid #4a6fdc; flex-shrink: 0; 
        }
        
        .review-content { flex: 1; display: flex; flex-direction: column; }
        
        /* Nama user warna Magenta sesuai desain */
        .review-content h4 { font-size: 15px; font-weight: 700; color: #ff1a73; margin-bottom: 2px; }
        .review-content p { font-size: 14px; color: #555; line-height: 1.5; margin-bottom: 5px; flex-grow: 1; }
        
        /* KELOMPOK BAWAH REVIEW (Bintang & Tanggal) */
        .review-bottom-bar { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 5px; }
        
        /* Bintang warna Magenta */
        .stars-display { color: #ff1a73; font-size: 17px; letter-spacing: 2px; margin-bottom: 2px; }
        .stars-display .empty { color: #f0a8c4; } /* Warna bintang kosong disesuaikan biar blend sama pink */
        
        /* Styling Tanggal */
        .review-date { font-size: 13px; color: #777; font-style: italic; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #ff99c2; border-radius: 10px; }
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
                <li class="menu-item">
                    <a href="admin_antrian.php">👥 Antrian</a>
                </li>
                <li class="menu-item active">
                    <a href="admin_rating.php">⭐ Rating dari user</a>
                </li>
            </ul>

            <ul class="menu-list" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 15px;">
                <li class="menu-item">
                    <a href="logout.php" onclick="return confirm('Yakin ingin keluar dari panel admin?');" style="color: #ffe6e6;">
                        🚪 Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <h1 class="page-title">Rating dari user</h1>

        <div class="rating-container">
            <div class="reviews-list">
                <?php 
                if (mysqli_num_rows($query_rating) > 0) {
                    while ($row = mysqli_fetch_array($query_rating)) {
                        // Format tanggal jadi dd-mm-yyyy sesuai desain lu
                        $tanggal_format = date('d-m-Y', strtotime($row['tanggal']));
                ?>
                <div class="review-item">
                    <div class="review-avatar"></div>
                    <div class="review-content">
                        <h4><?php echo htmlspecialchars($row['nama_user']); ?></h4>
                        <p><?php echo nl2br(htmlspecialchars($row['komentar'])); ?></p>
                        
                        <div class="review-bottom-bar">
                            <div class="stars-display">
                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                    <?php if ($i <= $row['rating']) : ?>
                                        ★
                                    <?php else : ?>
                                        <span class="empty">☆</span>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            
                            <div class="review-date">
                                <?php echo $tanggal_format; ?>
                            </div>
                        </div>

                    </div>
                </div>
                <?php 
                    }
                } else {
                    echo "<p style='text-align: center; color: #888; margin-top: 20px; font-style: italic;'>Belum ada rating dari user.</p>";
                }
                ?>
            </div>
        </div>
    </div>

</body>
</html>