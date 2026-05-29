<?php
session_start();
session_destroy(); // Ini perintah buat ngehancurin Session
echo "<script>
        alert('Anda telah berhasil logout!');
        window.location.href='login.php';
      </script>";
?>