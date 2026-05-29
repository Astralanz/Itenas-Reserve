<?php
session_start();
include 'koneksi.php';

// Atur timezone
date_default_timezone_set('Asia/Jakarta');

// Proteksi halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data user yang sedang login untuk keperluan input ke database
$email_user = $_SESSION['email'];
$nama_user_login = explode('@', $email_user)[0]; 
$nama_display = !empty($_SESSION['nama_google']) ? $_SESSION['nama_google'] : $nama_user_login;

// --- LOGIKA HAPUS RATING ---
if (isset($_POST['hapus_rating'])) {
    $id_hapus = (int)$_POST['id_rating'];
    mysqli_query($conn, "DELETE FROM rating WHERE id = '$id_hapus' AND email_user = '$email_user'");
    echo "<script>alert('Review berhasil dihapus!'); window.location.href='rating.php';</script>";
}

// --- LOGIKA SIMPAN / UPDATE RATING ---
if (isset($_POST['kirim_rating'])) {
    $rating_angka = (int) $_POST['rating_value'];
    $komentar = mysqli_real_escape_string($conn, $_POST['komentar']);
    $id_edit = isset($_POST['id_edit']) ? (int)$_POST['id_edit'] : 0;

    if ($rating_angka > 0 && !empty($komentar)) {
        if ($id_edit > 0) {
            // PROSES UPDATE
            $query_update = "UPDATE rating SET rating = '$rating_angka', komentar = '$komentar' WHERE id = '$id_edit' AND email_user = '$email_user'";
            mysqli_query($conn, $query_update);
            echo "<script>alert('Review lu berhasil diupdate!'); window.location.href='rating.php';</script>";
        } else {
            // PROSES INSERT BARU
            $query_insert = "INSERT INTO rating (email_user, nama_user, rating, komentar) 
                             VALUES ('$email_user', '$nama_display', '$rating_angka', '$komentar')";
            mysqli_query($conn, $query_insert);
            echo "<script>alert('Review lu berhasil dikirim! Thanks cuy!'); window.location.href='rating.php';</script>";
        }
    } else {
        echo "<script>alert('Pilih bintangnya dan isi komentarnya dulu dong!');</script>";
    }
}

// --- AMBIL DATA RATING DARI DATABASE (UDAH PAKE JOIN) ---
$query_rating = mysqli_query($conn, "SELECT rating.*, users.foto_profil FROM rating LEFT JOIN users ON rating.email_user = users.email ORDER BY rating.id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rating & Feedback - Itenas Reserve</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { background-color: #eef2fc; height: 100vh; display: flex; overflow: hidden; padding: 20px; }
        
        /* --- SIDEBAR LEFT (Layout Dasarnya Saja) --- */
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

        /* --- MAIN CONTENT --- */
        .main-content { flex: 1; padding: 10px 40px; display: flex; flex-direction: column; overflow-y: hidden; position: relative; }
        .main-header { text-align: center; font-size: 32px; font-weight: 700; color: #4a6fdc; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 25px; text-shadow: 0 4px 10px rgba(74, 111, 220, 0.1); }

        /* --- RATING BOX CONTAINER --- */
        .rating-container { display: flex; flex-direction: column; flex: 1; overflow: hidden; gap: 20px; }
        
        /* LIST REVIEW ORANG-ORANG */
        .reviews-list { flex: 1; overflow-y: auto; background-color: #dce4f7; padding: 25px 35px; border-radius: 25px; display: flex; flex-direction: column; }
        .review-item { display: flex; gap: 20px; padding-bottom: 20px; padding-top: 20px; border-bottom: 1.5px solid #bac7e6; }
        .review-item:first-child { padding-top: 0; }
        .review-item:last-child { border-bottom: none; padding-bottom: 0; }
        
        /* CSS AVATAR REVIEW */
        .review-avatar { width: 55px; height: 55px; border-radius: 50%; background-size: cover; background-position: center; border: 3px solid #4a6fdc; flex-shrink: 0; }
        
        .review-content { flex: 1; display: flex; flex-direction: column; }
        .review-content h4 { font-size: 15px; font-weight: 700; color: #333; margin-bottom: 2px; }
        .review-content p { font-size: 14px; color: #555; line-height: 1.5; margin-bottom: 5px; flex-grow: 1; }
        
        /* KELOMPOK BAWAH REVIEW */
        .review-bottom-bar { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 5px; }
        .stars-display { color: #ffb800; font-size: 17px; letter-spacing: 2px; margin-bottom: 2px; }
        .stars-display .empty { color: #ccc; }
        .review-right-side { display: flex; flex-direction: column; align-items: flex-end; position: relative; gap: 5px; }
        .review-date { font-size: 13px; color: #777; font-style: italic; }

        /* --- STYLING DROPDOWN TITIK TIGA --- */
        .kebab-container { position: relative; }
        .dot-menu { font-size: 26px; color: #555; cursor: pointer; line-height: 0.8; padding: 0 5px; user-select: none; position: relative; top: -60px;}
        .dot-menu:hover { color: #000; }
        .dropdown-menu { display: none; position: absolute; right: 25px; top: -60px; background: white; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); overflow: hidden; z-index: 10; min-width: 100px; border: 1px solid #eee; }
        .dropdown-menu.show { display: block; }
        .dropdown-item { display: block; width: 100%; background: none; border: none; padding: 10px 15px; font-size: 15px; color: #555; font-style: italic; cursor: pointer; transition: 0.2s; font-family: 'Poppins', sans-serif; text-align: center; }
        .dropdown-item:hover { background-color: #f8f9fa; color: #4a6fdc; }
        .dropdown-divider { height: 3px; background: linear-gradient(to right, #4169e1, #ffa3ff); }

        /* --- INPUT FORM BAWAH --- */
        .input-section { background-color: #ffffff; padding: 20px 30px; border-radius: 25px; border: 1.5px solid #d4b3ff; display: flex; gap: 20px; align-items: flex-start; box-shadow: 0 10px 20px rgba(0,0,0,0.02); }
        .input-avatar { width: 55px; height: 55px; border-radius: 50%; background-size: cover; background-position: center; border: 3px solid #4a6fdc; flex-shrink: 0; }
        .input-form { flex: 1; display: flex; flex-direction: column; gap: 8px; margin-top: 5px; }
        .input-form h4 { font-size: 15px; font-weight: 700; color: #333; margin: 0; }
        textarea { width: 100%; background: transparent; border: none; outline: none; resize: none; font-size: 14px; color: #555; font-family: 'Poppins', sans-serif; height: 40px; }
        textarea::placeholder { color: #aaa; font-style: italic; }
        .input-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 5px; }
        .interactive-stars { font-size: 22px; color: #ccc; cursor: pointer; user-select: none; letter-spacing: 2px; }
        .interactive-stars span { transition: 0.2s; }
        .interactive-stars span:hover, .interactive-stars span.active { color: #ffb800; }
        .btn-kirim { background-color: #4a6fdc; color: white; border: none; padding: 10px 35px; border-radius: 25px; font-weight: 600; font-size: 13px; cursor: pointer; transition: 0.2s; }
        .btn-kirim:hover { background-color: #3558b8; }
        
        /* --- KEMBALIKAN CSS MODAL YANG HILANG (INI OBAT ANTI SPIDERMAN RAKSASA) --- */
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
        <h1 class="main-header">Rating & Feedback</h1>

        <div class="rating-container">
            
            <div class="reviews-list">
                <?php 
                if (mysqli_num_rows($query_rating) > 0) {
                    while ($row = mysqli_fetch_array($query_rating)) {
                        $tanggal_format = date('d-m-Y', strtotime($row['tanggal']));
                ?>
                <div class="review-item">
                    <?php 
                        // LOGIKA FOTO PROFIL UNTUK SETIAP REVIEW
                        $foto_review = $row['foto_profil'];
                        $path_foto_review = (!empty($foto_review) && file_exists('uploads/' . $foto_review)) 
                                            ? 'uploads/' . $foto_review 
                                            : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
                    ?>
                    <div class="review-avatar" style="background-image: url('<?php echo $path_foto_review; ?>');"></div>
                    
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
                            
                            <div class="review-right-side">
                                <?php if ($row['email_user'] == $email_user) { ?>
                                <div class="kebab-container">
                                    <span class="dot-menu" onclick="toggleDropdown(event, <?php echo $row['id']; ?>)">&#8942;</span>
                                    
                                    <div class="dropdown-menu" id="dropdown-<?php echo $row['id']; ?>">
                                        <form action="" method="POST" style="margin:0;">
                                            <input type="hidden" name="id_rating" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="hapus_rating" class="dropdown-item" onclick="return confirm('Yakin mau hapus komentar ini?')">Hapus</button>
                                        </form>
                                        <div class="dropdown-divider"></div>
                                        <button type="button" class="dropdown-item" onclick="editReview(<?php echo $row['id']; ?>, `<?php echo addslashes(htmlspecialchars($row['komentar'])); ?>`, <?php echo $row['rating']; ?>)">Edit</button>
                                    </div>
                                </div>
                                <?php } ?>

                                <div class="review-date"><?php echo $tanggal_format; ?></div>
                            </div>
                        </div>

                    </div>
                </div>
                <?php 
                    }
                } else {
                    echo "<p style='text-align: center; color: #888; margin-top: 20px; font-style: italic;'>Belum ada feedback dari mahasiswa lain. Jadilah yang pertama!</p>";
                }
                ?>
            </div>

            <div class="input-section">
                <div class="input-avatar" style="background-image: url('<?php echo $path_foto; ?>');"></div>
                <form action="" method="POST" class="input-form">
                    <h4><?php echo htmlspecialchars($nama_display); ?></h4>
                    <textarea name="komentar" id="input_komentar" placeholder="Ketik Sesuatu disini..." required></textarea>
                    
                    <div class="input-footer">
                        <div class="interactive-stars" id="star-container">
                            <span data-val="1">☆</span>
                            <span data-val="2">☆</span>
                            <span data-val="3">☆</span>
                            <span data-val="4">☆</span>
                            <span data-val="5">☆</span>
                        </div>
                        
                        <input type="hidden" name="id_edit" id="id_edit" value="0">
                        <input type="hidden" name="rating_value" id="rating_value" value="0">
                        <button type="submit" name="kirim_rating" id="btn_submit" class="btn-kirim">Kirim</button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        // --- LOGIKA BINTANG INTERAKTIF ---
        const stars = document.querySelectorAll('#star-container span');
        const ratingInput = document.getElementById('rating_value');

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-val');
                ratingInput.value = value; 
                
                stars.forEach(s => {
                    if (s.getAttribute('data-val') <= value) {
                        s.innerHTML = '★';
                        s.classList.add('active');
                        s.style.color = '#ffb800';
                    } else {
                        s.innerHTML = '☆';
                        s.classList.remove('active');
                        s.style.color = '#ccc';
                    }
                });
            });

            star.addEventListener('mouseover', function() {
                const value = this.getAttribute('data-val');
                stars.forEach(s => {
                    if (s.getAttribute('data-val') <= value) {
                        s.style.color = '#ffb800';
                    }
                });
            });

            star.addEventListener('mouseout', function() {
                stars.forEach(s => {
                    if (!s.classList.contains('active')) {
                        s.style.color = '#ccc';
                    }
                });
            });
        });

        // --- LOGIKA MENU DROPDOWN TITIK TIGA ---
        function toggleDropdown(event, id) {
            event.stopPropagation();
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if(menu.id !== 'dropdown-'+id) menu.classList.remove('show');
            });
            document.getElementById('dropdown-'+id).classList.toggle('show');
        }

        window.onclick = function(event) {
            if (!event.target.matches('.dot-menu')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        }

        // --- LOGIKA EDIT REVIEW ---
        function editReview(id, komentar, rating) {
            document.getElementById('dropdown-'+id).classList.remove('show');
            
            document.getElementById('id_edit').value = id;
            document.getElementById('input_komentar').value = komentar;
            document.getElementById('rating_value').value = rating;
            
            document.getElementById('btn_submit').innerHTML = "Update";
            
            stars.forEach(s => {
                if (s.getAttribute('data-val') <= rating) {
                    s.innerHTML = '★';
                    s.classList.add('active');
                    s.style.color = '#ffb800';
                } else {
                    s.innerHTML = '☆';
                    s.classList.remove('active');
                    s.style.color = 'rgb(204, 204, 204)';
                }
            });

            document.querySelector('.input-section').scrollIntoView({ behavior: 'smooth' });
            document.getElementById('input_komentar').focus();
        }
    </script>
</body>
</html>