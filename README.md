# Itenas Reserve 🏢

Sistem reservasi fasilitas kampus Itenas berbasis web yang dibangun untuk memudahkan mahasiswa melakukan peminjaman aset (seperti Lapangan Basket, Aula, dll) secara *real-time* dan terorganisir.

## Fitur Utama

### Sisi Mahasiswa (User)
* **Booking Real-Time:** Ajukan peminjaman aset dengan validasi jam operasional (06:00 - 18:00).
* **Cek Jadwal AJAX:** Fitur cek ketersediaan fasilitas secara instan tanpa perlu *loading* halaman.
* **Live Countdown:** Timer hitung mundur saat fasilitas sedang digunakan.
* **Rating & Feedback:** Berikan ulasan bintang dan komentar setelah selesai meminjam.
* **Riwayat Peminjaman:** Lacak status pengajuan (Pending, Approved, Selesai, Ditolak).

### Sisi Admin
* **Dashboard Antrian:** Kelola permohonan peminjaman dengan fitur ACC/Tolak.
* **Alasan Penolakan Kustom:** Sistem manajemen alasan penolakan berbasis JSON.
* **Manajemen Aset:** Tambah, edit, dan hapus fasilitas kampus.
* **Auto-Update Status:** Sistem cerdas yang otomatis mengubah status menjadi "SELESAI" saat waktu peminjaman berakhir.
* **Sapu Bersih Riwayat:** Fitur untuk membersihkan data peminjaman yang sudah usang dari *database*.

## Teknologi yang Digunakan

* **Front-End:** HTML5, CSS3 (Flexbox/Grid), Vanilla JavaScript (AJAX/Fetch API).
* **Back-End:** PHP 8 (Procedural).
* **Database:** MySQL.
* **Libraries:** [SweetAlert2](https://sweetalert2.github.io/) (Notifikasi modern).
* **Environment:** XAMPP (Apache & MariaDB).
* **Version Control:** Git & GitHub.

## Cara Instalasi

1. **Clone Repository:**
   ```bash
   git clone [https://github.com/username-lu/itenas-reserve.git](https://github.com/username-lu/itenas-reserve.git)
