<?php
session_start();
include 'koneksi.php';

// Atur timezone
date_default_timezone_set('Asia/Jakarta');

// Proteksi halaman admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// --- LOGIKA 1: TAMBAH ASET BARU ---
if (isset($_POST['tambah_aset'])) {
    $nama_aset = mysqli_real_escape_string($conn, $_POST['nama_aset']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']); 
    
    $insert = mysqli_query($conn, "INSERT INTO aset (nama_aset, deskripsi) VALUES ('$nama_aset', '$deskripsi')");
    if ($insert) {
        echo "<script>alert('Fasilitas baru berhasil ditambahkan!'); window.location.href='admin_aset.php';</script>";
    }
}

// --- LOGIKA 2: UPDATE/EDIT ASET ---
if (isset($_POST['edit_aset'])) {
    $id_aset = mysqli_real_escape_string($conn, $_POST['id_aset']);
    $nama_aset = mysqli_real_escape_string($conn, $_POST['nama_aset']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']); 
    
    $update = mysqli_query($conn, "UPDATE aset SET nama_aset = '$nama_aset', deskripsi = '$deskripsi' WHERE id = '$id_aset'");
    if ($update) {
        echo "<script>alert('Fasilitas berhasil diupdate!'); window.location.href='admin_aset.php';</script>";
    } else {
        echo "<script>alert('Gagal mengupdate fasilitas!'); window.location.href='admin_aset.php';</script>";
    }
}

// --- LOGIKA 3: HAPUS ASET ---
if (isset($_GET['hapus_aset'])) {
    $id_aset = mysqli_real_escape_string($conn, $_GET['hapus_aset']);
    
    $hapus = mysqli_query($conn, "DELETE FROM aset WHERE id = '$id_aset'");
    if ($hapus) {
        echo "<script>alert('Fasilitas berhasil dihapus!'); window.location.href='admin_aset.php';</script>";
    } else {
        echo "<script>alert('Gagal hapus! Mungkin aset ini sedang ada di daftar peminjaman.'); window.location.href='admin_aset.php';</script>";
    }
}

// --- LOGIKA 4: PENCARIAN (SEARCH) ---
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query_aset = mysqli_query($conn, "SELECT * FROM aset WHERE nama_aset LIKE '%$search%' ORDER BY id DESC");
} else {
    $query_aset = mysqli_query($conn, "SELECT * FROM aset ORDER BY id ASC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Aset Kampus</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }
        body { 
            background: linear-gradient(to bottom right, #fff0f5, #ffffff); 
            height: 100vh; display: flex; padding: 20px; gap: 20px; overflow: hidden;
        }
        
        /* --- SIDEBAR --- */
        .sidebar-container { width: 280px; display: flex; flex-direction: column; gap: 15px; }
        
        .sidebar-header { 
            background-color: #ff1a73; border-radius: 20px; padding: 25px 20px; 
            color: white; box-shadow: 0 10px 20px rgba(255, 26, 115, 0.2);
        }
        .sidebar-header h2 { font-size: 22px; font-weight: 800; line-height: 1.2; text-transform: uppercase; }
        
        .sidebar-menu { 
            background-color: #ff1a73; border-radius: 20px; padding: 30px 15px; 
            flex: 1; color: white; box-shadow: 0 10px 20px rgba(255, 26, 115, 0.2);
            overflow-y: auto;
        }
        .menu-list { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .menu-item a { 
            display: flex; align-items: center; gap: 15px; padding: 12px 20px; 
            color: white; text-decoration: none; font-weight: 600; font-size: 15px; 
            border-radius: 15px; transition: 0.3s;
        }
        .menu-item.active a { background-color: white; color: black; }
        .menu-item:not(.active) a:hover { background-color: rgba(255, 255, 255, 0.1); }
        
        .sub-menu { margin-left: 45px; margin-top: 5px; display: flex; flex-direction: column; gap: 8px; }
        .sub-menu a { color: white; text-decoration: none; font-size: 13px; font-style: italic; opacity: 0.9; }
        .sub-menu a:hover { opacity: 1; text-decoration: underline; }

        /* --- MAIN CONTENT --- */
        .main-content { flex: 1; display: flex; flex-direction: column; padding: 10px 20px; overflow: hidden; }
        
        .page-title { text-align: center; color: #ff1a73; font-size: 32px; font-weight: 800; text-transform: uppercase; margin-bottom: 30px; letter-spacing: 1px; }

        .action-bar { display: flex; gap: 20px; margin-bottom: 25px; }
        
        .search-form { flex: 1; display: flex; }
        .search-input { 
            width: 100%; border: 2px solid #ff1a73; border-radius: 15px; 
            padding: 12px 25px; font-size: 15px; outline: none; color: #333;
        }
        .search-input::placeholder { color: #aaa; font-style: italic; }
        
        .btn-tambah { 
            background-color: #ff1a73; color: white; border: none; border-radius: 15px; 
            padding: 0 30px; font-size: 15px; font-weight: 600; cursor: pointer; 
            transition: 0.2s; box-shadow: 0 5px 15px rgba(255, 26, 115, 0.3); display: flex; align-items: center; gap: 8px;
        }
        .btn-tambah:hover { background-color: #e6005c; transform: translateY(-2px); }

        /* --- LIST ASET --- */
        .aset-container { flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; padding-right: 10px; }
        
        .aset-row { 
            display: flex; align-items: center; justify-content: space-between; 
            background-color: #f4f7fb; padding: 15px 30px; border-radius: 15px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }
        
        .aset-info { display: flex; align-items: center; gap: 30px; }
        .aset-no { font-size: 16px; color: #555; font-weight: 500; width: 20px; }
        .aset-nama { font-size: 15px; color: #222; font-weight: 500; }
        
        .aset-actions { display: flex; gap: 15px; }
        .btn-action { 
            border: none; padding: 8px 25px; border-radius: 20px; 
            font-size: 12px; font-weight: 700; color: white; cursor: pointer; 
            text-transform: uppercase; text-decoration: none; transition: 0.2s;
        }
        .btn-edit { background-color: #ffb800; }
        .btn-edit:hover { background-color: #e6a600; }
        .btn-hapus { background-color: #ff1a73; }
        .btn-hapus:hover { background-color: #e6005c; }

        /* --- MODAL (POPUP) --- */
        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;
        }
        .modal-box {
            background: white; width: 400px; padding: 30px; border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2); position: relative;
        }
        .modal-title { font-size: 20px; font-weight: 700; margin-bottom: 20px; text-align: center; }
        .title-tambah { color: #ff1a73; }
        .title-edit { color: #ffb800; }

        .close-modal { position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: #888; }
        .close-modal:hover { color: #ff1a73; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; color: #555; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group textarea {
            width: 100%; border: 1.5px solid #ddd; border-radius: 10px; padding: 10px 15px; font-size: 14px; outline: none;
        }
        .form-group input:focus, .form-group textarea:focus { border-color: #ff1a73; }
        
        .btn-submit-modal {
            width: 100%; color: white; border: none; padding: 12px;
            border-radius: 10px; font-weight: 600; font-size: 15px; cursor: pointer; margin-top: 10px; transition: 0.2s;
        }
        .btn-tambah-submit { background: #ff1a73; }
        .btn-tambah-submit:hover { background: #e6005c; }
        .btn-edit-submit { background: #ffb800; }
        .btn-edit-submit:hover { background: #e6a600; }
        
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #dcb3c3; border-radius: 10px; }
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
                <li class="menu-item active">
                    <a href="admin_aset.php">🏢 Aset Kampus</a>
                </li>
                <li class="menu-item">
                    <a href="admin_antrian.php">👥 Antrian</a>
                </li>
                <li class="menu-item">
                    <a href="admin_rating.php">⭐ Rating dari user</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <h1 class="page-title">ASET KAMPUS</h1>

        <div class="action-bar">
            <form action="" method="GET" class="search-form">
                <input type="text" name="search" class="search-input" placeholder="Cari Aset..." value="<?php echo htmlspecialchars($search); ?>">
            </form>
            <button class="btn-tambah" onclick="bukaModalTambah()">+ Tambahkan Aset</button>
        </div>

        <div class="aset-container">
            <?php 
            if (mysqli_num_rows($query_aset) > 0) {
                $no = 1;
                while($data = mysqli_fetch_array($query_aset)) {
            ?>
                <div class="aset-row">
                    <div class="aset-info">
                        <span class="aset-no"><?php echo $no++; ?></span>
                        <span class="aset-nama"><?php echo htmlspecialchars($data['nama_aset']); ?></span>
                    </div>
                    <div class="aset-actions">
                        <button class="btn-action btn-edit" onclick="bukaModalEdit('<?php echo $data['id']; ?>', '<?php echo addslashes(htmlspecialchars($data['nama_aset'])); ?>', '<?php echo addslashes(htmlspecialchars($data['deskripsi'])); ?>')">EDIT</button>
                        <a href="admin_aset.php?hapus_aset=<?php echo $data['id']; ?>" class="btn-action btn-hapus" onclick="return confirm('Yakin mau hapus fasilitas ini?');">HAPUS</a>
                    </div>
                </div>
            <?php 
                }
            } else {
                echo "<p style='text-align:center; color:#888; font-style:italic;'>Aset tidak ditemukan atau data masih kosong.</p>";
            }
            ?>
        </div>
    </div>

    <div class="modal-overlay" id="modalTambah">
        <div class="modal-box">
            <span class="close-modal" onclick="tutupModalTambah()">&times;</span>
            <h3 class="modal-title title-tambah">Tambah Aset Baru</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Nama Aset / Fasilitas</label>
                    <input type="text" name="nama_aset" placeholder="Contoh: Lapangan Basket" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi Detail</label>
                    <textarea name="deskripsi" rows="3" placeholder="Contoh: Lapangan basket outdoor dekat GSG..." required></textarea>
                </div>
                <button type="submit" name="tambah_aset" class="btn-submit-modal btn-tambah-submit">Simpan Aset</button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalEdit">
        <div class="modal-box">
            <span class="close-modal" onclick="tutupModalEdit()">&times;</span>
            <h3 class="modal-title title-edit">Edit Aset Kampus</h3>
            <form method="POST" action="">
                <input type="hidden" name="id_aset" id="edit_id_aset">
                
                <div class="form-group">
                    <label>Nama Aset / Fasilitas</label>
                    <input type="text" name="nama_aset" id="edit_nama_aset" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi Detail</label>
                    <textarea name="deskripsi" id="edit_deskripsi" rows="3" required></textarea>
                </div>
                <button type="submit" name="edit_aset" class="btn-submit-modal btn-edit-submit">Update Aset</button>
            </form>
        </div>
    </div>

    <script>
        const modalTambah = document.getElementById('modalTambah');
        const modalEdit = document.getElementById('modalEdit');

        // Fungsi Buka Tutup Modal Tambah
        function bukaModalTambah() { modalTambah.style.display = 'flex'; }
        function tutupModalTambah() { modalTambah.style.display = 'none'; }

        // Fungsi Buka Tutup Modal Edit (Plus masukin data yg mau diedit ke form)
        function bukaModalEdit(id, nama, deskripsi) {
            document.getElementById('edit_id_aset').value = id;
            document.getElementById('edit_nama_aset').value = nama;
            document.getElementById('edit_deskripsi').value = deskripsi;
            modalEdit.style.display = 'flex';
        }
        function tutupModalEdit() { modalEdit.style.display = 'none'; }

        // Tutup modal kalau klik area gelap di luarnya
        window.onclick = function(event) {
            if (event.target == modalTambah) { modalTambah.style.display = 'none'; }
            if (event.target == modalEdit) { modalEdit.style.display = 'none'; }
        }
    </script>
</body>
</html>