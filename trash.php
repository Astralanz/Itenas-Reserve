<?php
session_start();
include 'koneksi.php';

// Proteksi admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Langsung sapu bersih data
$hapus = mysqli_query($conn, "DELETE FROM peminjaman WHERE status IN ('SELESAI', 'REJECTED', 'DITOLAK')");

if ($hapus) {
    echo "<script>alert('Semua riwayat lama berhasil dibersihkan!'); window.location.href='admin_antrian.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus data!'); window.location.href='admin_antrian.php';</script>";
}
?>