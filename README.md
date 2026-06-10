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

## PEMBAGIAN TUGAS
1. Pondasi awal/alur webnya termasuk backend : Rafli Maulana
2. Bikin UI awal sebelum ke Frontend : Raka Muhammad 80%
3. Bikin UI awal sebelum ke Frontend : Rafli Maulana 20%
4. Implementasi Frontend : Rafli Maulana dan Raka Muhammad
5. Fokus Implementasi lebih ke bagian reserve user/admin : Rafli Maulana
6. Fokus Implementasi lebih ke bagian Waiting List user/admin : Raka Muhammad
7. Fokus Implementasi lebih ke bagian rating user/admin : Rafli Maulana
8. Database : Rafli Maulana
9. Revisi customer service ganti ke wa di : Raka Muhammad
10. Google auth login user: Raka Muhammad
11. Google auth login admin / role admin : Rafli Maulana A
12. Revisi bagian admin "bagian alasan penolakan" : Raka Muhammad
13. Revisi bagian user "CEK" : Raka Muhammad
14. Nambahin otomatis deteksi prodi berdasarkan kode dari akun google itenas semisal '15" yg langsung terdeteksi INFORMATIKA : Rafli Maulana
