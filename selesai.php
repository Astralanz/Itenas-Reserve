<?php
include 'koneksi.php';

if (isset($_GET['id_pinjam'])) {
    $id_pinjam = $_GET['id_pinjam'];
    
    // Ubah status dari approved menjadi selesai
    $update = mysqli_query($conn, "UPDATE peminjaman SET status = 'selesai' WHERE id = '$id_pinjam'");

    if ($update) {
        echo "<script>
                alert('Peminjaman telah diselesaikan lebih awal. Terima kasih!');
                window.location.href='index.php';
              </script>";
    }
}
?>