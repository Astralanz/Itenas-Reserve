-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 29 Bulan Mei 2026 pada 04.49
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_peminjaman`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `aset`
--

CREATE TABLE `aset` (
  `id` int(11) NOT NULL,
  `nama_aset` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `aset`
--

INSERT INTO `aset` (`id`, `nama_aset`, `deskripsi`) VALUES
(1, 'Lapangan Basket', 'Lapangan basket outdoor utama kampus'),
(2, 'Ruang Aula', 'Aula serbaguna untuk seminar atau acara'),
(3, 'ahayyy', 'Yang BU merapat'),
(6, 'afdad', 'asdad');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `aset_id` int(11) DEFAULT NULL,
  `nama_peminjam` varchar(100) DEFAULT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `prodi` varchar(50) DEFAULT NULL,
  `tanggal_pinjam` date DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `status` enum('pending','approved','rejected','selesai') DEFAULT 'pending',
  `is_read` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `user_id`, `aset_id`, `nama_peminjam`, `nim`, `prodi`, `tanggal_pinjam`, `jam_mulai`, `jam_selesai`, `status`, `is_read`) VALUES
(6, 1, 1, 'Astranjay', '152024078', 'Informatika', '2026-05-14', '13:15:00', '17:00:00', 'rejected', 0),
(7, 1, 2, 'nihao', 'afasd', 'asdadas', '2026-05-20', '12:31:00', '12:31:00', 'selesai', 0),
(8, 1, 1, 'afassafas', '1213213', 'asdadasd', '2026-05-15', '08:05:00', '09:05:00', 'selesai', 0),
(9, 1, 2, 'aasdiadsh', '234234asdad', 'asdasdad', '2026-05-15', '10:22:00', '11:22:00', 'selesai', 0),
(10, 1, 1, 'Astralanzs', 'afasd', 'Informatika', '2026-05-15', '20:06:00', '21:07:00', 'selesai', 0),
(12, 3, 1, 'asdasd', 'asdasdaasda', 'asdadasd', '2026-05-15', '20:23:00', '20:29:00', 'selesai', 0),
(13, 6, 1, 'Astranjay', '1213213', 'Informatika', '2026-05-15', '23:19:00', '15:19:00', 'selesai', 1),
(14, 6, 2, 'Astralanzs', 'afasd', 'Informatika', '2026-05-15', '23:26:00', '23:32:00', 'selesai', 0),
(15, 6, 1, 'Astranjay', 'afasd', 'Informatika', '2026-05-16', '00:06:00', '02:06:00', 'selesai', 0),
(16, 6, 3, 'Astranjay', '1213213', 'Informatika', '2026-05-15', '00:07:00', '00:39:00', 'selesai', 0),
(17, 6, 2, 'Astranjay', '1213213', 'asdadas', '2026-05-15', '04:12:00', '03:14:00', 'selesai', 0),
(18, 6, 2, 'nihao', '1213213', 'Informatika', '2026-05-16', '15:19:00', '11:15:00', 'selesai', 0),
(19, 7, 1, 'dadsad', 'asdasd', 'asdasd', '2026-05-21', '18:29:00', '18:33:00', 'selesai', 0),
(20, 10, 1, 'Astranjay', '1213213', 'Informatika', '2026-05-21', '22:16:00', '15:18:00', 'selesai', 0),
(21, 10, 1, 'Astranjay', '1213213', 'Informatika', '2026-05-21', '22:17:00', '14:21:00', 'selesai', 0),
(22, 10, 1, 'Astranjay', '1213213', 'Informatika', '2026-05-21', '22:20:00', '14:25:00', 'selesai', 0),
(23, 10, 1, 'Astranjay', 'afasd', 'asdadas', '2026-05-21', '22:24:00', '22:29:00', 'selesai', 0),
(24, 10, 2, 'Astralanzs', '1213213', 'asdadas', '2026-05-21', '22:27:00', '22:33:00', 'selesai', 0),
(27, 10, 3, 'Astralanzs', 'afasd', 'Informatika', '2026-05-21', '22:40:00', '23:40:00', 'selesai', 0),
(29, 10, 1, 'Astralanzs', '1213213', 'Informatika', '2026-05-21', '23:00:00', '13:00:00', 'selesai', 0),
(30, 10, 2, 'Astranjay', '1213213', 'Informatika', '2026-05-21', '23:01:00', '23:41:00', 'selesai', 0),
(31, 10, 1, 'Astranjay', 'afasd', 'asdadas', '2026-05-21', '23:06:00', '23:08:00', 'selesai', 0),
(33, 10, 1, 'Astranjay', '1213213', 'Informatika', '2026-05-21', '23:17:00', '23:19:00', 'selesai', 0),
(34, 10, 2, 'Astralanzs', '1213213', 'asdasd', '2026-05-21', '23:18:00', '23:24:00', 'rejected', 0),
(35, 10, 1, 'nihao', '234234asdad', 'Informatika', '2026-05-21', '23:44:00', '23:47:00', 'selesai', 0),
(37, 10, 2, 'nihao', '1213213', 'Informatika', '2026-05-21', '23:46:00', '23:52:00', 'selesai', 0),
(38, 10, 1, 'dfsdf', 'fsdfs', 'sfdsf', '2026-05-24', '22:35:00', '22:38:00', 'rejected', 0),
(39, 10, 1, 'dfsf', 'sfsdf', 'sdfsdf', '2026-05-24', '22:42:00', '22:48:00', 'selesai', 0),
(40, 10, 1, 'sdfsdf', 'sdfdsf', 'sdfsdfsd', '2026-05-24', '23:26:00', '23:30:00', 'selesai', 0),
(41, 10, 1, 'asasd', 'asdasd', 'asdasd', '2026-05-24', '23:27:00', '23:33:00', 'rejected', 0),
(42, 10, 1, 'rwgrwrgwgrw', '4rtwrgr', 'gdgegege', '2026-05-25', '13:32:00', '13:57:00', 'selesai', 0),
(43, 10, 1, 'asfasdfawf', 'sdfasdfadf', 'asdfasdfsaddf', '2026-05-25', '16:27:00', '16:30:00', 'selesai', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `rating`
--

CREATE TABLE `rating` (
  `id` int(11) NOT NULL,
  `email_user` varchar(100) NOT NULL,
  `nama_user` varchar(100) NOT NULL,
  `rating` int(11) NOT NULL,
  `komentar` text NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `rating`
--

INSERT INTO `rating` (`id`, `email_user`, `nama_user`, `rating`, `komentar`, `tanggal`) VALUES
(3, 'rafli.maulana@mhs.itenas.ac.id', '152024078 RAFLI MAULANA AHMAD', 4, 'test 11123', '2026-05-24 21:38:02'),
(5, 'rafli.maulana@mhs.itenas.ac.id', '152024078 RAFLI MAULANA AHMAD', 4, 'cek', '2026-05-24 21:47:18'),
(6, 'rafli.maulana@mhs.itenas.ac.id', '152024078 RAFLI MAULANA AHMAD', 1, 'P gelud?', '2026-05-24 23:31:06'),
(7, 'rafli.maulana@mhs.itenas.ac.id', '152024078 RAFLI MAULANA AHMAD', 5, 'sadasd', '2026-05-24 23:32:17'),
(8, 'rafli.maulana@mhs.itenas.ac.id', '152024078 RAFLI MAULANA AHMAD', 3, 'hahayy', '2026-05-25 00:45:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `foto_profil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `email`, `role`, `foto_profil`) VALUES
(1, 'user1@itenas.ac.id', 'user', NULL),
(2, 'asep@itenas.ac.id', 'user', NULL),
(3, 'mhs_baru@itenas.ac.id', 'user', NULL),
(4, 'adads@itenas.ac.id', 'user', NULL),
(5, 'dsff@itenas.ac.id', 'user', NULL),
(6, 'asdas@itenas.ac.id', 'user', NULL),
(7, 'astra@itenas.ac.id', 'user', NULL),
(8, 'rafli.maulana@itenas.ac.id', 'user', NULL),
(9, 'astra@mhs.itenas.ac.id', 'user', NULL),
(10, 'rafli.maulana@mhs.itenas.ac.id', 'user', '6a132ff92cce0.webp');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `aset`
--
ALTER TABLE `aset`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `aset_id` (`aset_id`);

--
-- Indeks untuk tabel `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `aset`
--
ALTER TABLE `aset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT untuk tabel `rating`
--
ALTER TABLE `rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`aset_id`) REFERENCES `aset` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
