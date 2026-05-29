<?php
$host = "localhost";
$user = "root"; 
$pass = "";     
$db   = "db_peminjaman";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Yaaah koneksi gagal: " . mysqli_connect_error());
}
?>