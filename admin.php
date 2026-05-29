<?php
session_start();
include 'koneksi.php';

// --- LOGIKA 1: ACC & TOLAK PEMINJAMAN (YANG LAMA) ---
if (isset($_GET['aksi']) && isset($_GET['id_pinjam'])) {
    $id_pinjam = $_GET['id_pinjam'];
    $aksi = $_GET['aksi'];

    if ($aksi == 'acc') {
        mysqli_query($conn, "UPDATE peminjaman SET status = 'approved' WHERE id = '$id_pinjam'");
    } elseif ($aksi == 'tolak') {
        mysqli_query($conn, "UPDATE peminjaman SET status = 'rejected' WHERE id = '$id_pinjam'");
    }
    header("Location: admin.php");
    exit();
}

// --- LOGIKA 2: TAMBAH ASET BARU ---
if (isset($_POST['tambah_aset'])) {
    $nama_aset = $_POST['nama_aset'];
    $deskripsi = $_POST['deskripsi'];
    
    $insert = mysqli_query($conn, "INSERT INTO aset (nama_aset, deskripsi) VALUES ('$nama_aset', '$deskripsi')");
    if ($insert) {
        echo "<script>alert('Fasilitas baru berhasil ditambahkan!'); window.location.href='admin.php';</script>";
    }
}

// --- LOGIKA 3: HAPUS ASET ---
if (isset($_GET['hapus_aset'])) {
    $id_aset = $_GET['hapus_aset'];
    
    // Catatan: Kalo aset dihapus, idealnya lu gak bisa ngehapus kalo asetnya lagi dipinjem
    // Tapi kita bikin force delete aja dulu buat testing
    $hapus = mysqli_query($conn, "DELETE FROM aset WHERE id = '$id_aset'");
    if ($hapus) {
        echo "<script>alert('Fasilitas berhasil dihapus!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Gagal hapus! Mungkin aset ini sedang ada di daftar peminjaman.'); window.location.href='admin.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Admin - Peminjaman Itenas</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 40px;">

    <h2>Dashboard Admin: Peminjaman Aset Itenas</h2>
    <a href="index.php">⬅️ Kembali ke Halaman User</a>
    <hr>

    <!-- BAGIAN 1: KELOLA ASET (TAMBAH & HAPUS) -->
    <div style="background-color: #e8f4f8; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
        <h3>🛠️ Kelola Fasilitas (Aset)</h3>
        
        <!-- Form Tambah Fasilitas -->
        <form method="POST" action="" style="margin-bottom: 20px;">
            <input type="text" name="nama_aset" placeholder="Nama Fasilitas (Cth: Proyektor)" required style="padding: 8px; width: 200px;">
            <input type="text" name="deskripsi" placeholder="Deskripsi Singkat" required style="padding: 8px; width: 300px;">
            <button type="submit" name="tambah_aset" style="background-color: blue; color: white; padding: 9px 15px; border: none; cursor: pointer;">+ Tambah Fasilitas</button>
        </form>

        <!-- Tabel Daftar Fasilitas -->
        <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; text-align: left; background: white;">
            <tr style="background-color: #ddd;">
                <th>No</th>
                <th>Nama Fasilitas</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>
            <?php
            $query_aset = mysqli_query($conn, "SELECT * FROM aset");
            $no_aset = 1;
            while($data_aset = mysqli_fetch_array($query_aset)) {
                echo "<tr>";
                echo "<td>" . $no_aset++ . "</td>";
                echo "<td><b>" . $data_aset['nama_aset'] . "</b></td>";
                echo "<td>" . $data_aset['deskripsi'] . "</td>";
                echo "<td>
                        <a href='admin.php?hapus_aset=" . $data_aset['id'] . "' onclick=\"return confirm('Yakin mau hapus fasilitas ini?');\" style='color: red; text-decoration: none; font-weight: bold;'>🗑️ Hapus</a>
                      </td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>

    <!-- BAGIAN 2: DAFTAR PERSETUJUAN PEMINJAMAN -->
    <h3>📝 Daftar Antrean Peminjaman</h3>
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; text-align: left;">
        <tr style="background-color: #333; color: white;">
            <th>No</th>     
            <th>Nama Mahasiswa</th>
            <th>Prodi</th>
            <th>Aset</th>
            <th>Jadwal Pinjam</th>
            <th>Status</th>
            <th>Persetujuan</th>
        </tr>

        <?php
        $query = mysqli_query($conn, "SELECT p.*, a.nama_aset FROM peminjaman p JOIN aset a ON p.aset_id = a.id ORDER BY p.id DESC");
        $no = 1;

        while($data = mysqli_fetch_array($query)) {
            echo "<tr>";
            echo "<td>" . $no++ . "</td>";
            echo "<td>" . $data['nama_peminjam'] . "<br><small>(" . $data['nim'] . ")</small></td>";
            echo "<td>" . $data['prodi'] . "</td>";
            echo "<td><b>" . $data['nama_aset'] . "</b></td>";
            echo "<td>" . $data['tanggal_pinjam'] . "<br>" . $data['jam_mulai'] . " - " . $data['jam_selesai'] . "</td>";
            
            if ($data['status'] == 'pending') { $warna_status = "orange"; } 
            elseif ($data['status'] == 'approved') { $warna_status = "green"; } 
            elseif ($data['status'] == 'rejected') { $warna_status = "red"; } 
            else { $warna_status = "blue"; }

            echo "<td style='color: $warna_status; font-weight: bold;'>" . strtoupper($data['status']) . "</td>";
            
            echo "<td>";
            if ($data['status'] == 'pending') {
                echo "<a href='admin.php?aksi=acc&id_pinjam=" . $data['id'] . "' style='background-color: green; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; margin-right: 5px;'>✅ ACC</a>";
                echo "<a href='admin.php?aksi=tolak&id_pinjam=" . $data['id'] . "' style='background-color: red; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;'>❌ TOLAK</a>";
            } else {
                echo "<i>Sudah diproses</i>";
            }
            echo "</td>";
            echo "</tr>";
        }
        ?>
    </table>

</body>
</html>