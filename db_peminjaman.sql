-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Jun 2026 pada 17.23
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
(2, 'Ruang Adu Nasib banget', 'Aula serbaguna untuk seminar atau acara'),
(11, '201jt', '1234'),
(12, 'dfsdfsdf', 'sdfsdf'),
(13, 'sdfsdfsdf', 'sdfsdfs'),
(14, 'sfsdfds', 'dfssdf');

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
(86, 10, 2, 'RAFLI MAULANA AHMAD', '152024078', 'Informatika', '2026-06-09', '13:00:00', '18:00:00', 'selesai', 0);

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
(5, 'rafli.maulana@mhs.itenas.ac.id', '152024078 RAFLI MAULANA AHMAD', 4, 'cek', '2026-05-24 21:47:18'),
(6, 'rafli.maulana@mhs.itenas.ac.id', '152024078 RAFLI MAULANA AHMAD', 1, 'P gelud?', '2026-05-24 23:31:06'),
(7, 'rafli.maulana@mhs.itenas.ac.id', '152024078 RAFLI MAULANA AHMAD', 5, 'sadasd', '2026-05-24 23:32:17'),
(8, 'rafli.maulana@mhs.itenas.ac.id', '152024078 RAFLI MAULANA AHMAD', 3, 'hahayy', '2026-05-25 00:45:39'),
(10, 'rafli.maulana@mhs.itenas.ac.id', '152024078 RAFLI MAULANA AHMAD', 3, 'Test', '2026-05-29 10:19:00'),
(11, 'rafli.maulana@mhs.itenas.ac.id', '152024078 RAFLI MAULANA AHMAD', 1, 'halo\r\n', '2026-05-29 13:43:33'),
(12, 'rafli.maulana@mhs.itenas.ac.id', 'Rafli Maulana', 5, 'Ulasan Sementara', '2026-05-29 21:21:00'),
(13, 'rafli.maulana@mhs.itenas.ac.id', 'Rafli Maulana', 5, 'Ulasan Sementara', '2026-05-29 21:23:07'),
(14, 'rafli.maulana@mhs.itenas.ac.id', 'Rafli Maulana', 5, 'Ulasan Sementara', '2026-05-29 21:23:40'),
(15, 'rafli.maulana@mhs.itenas.ac.id', '152024078 RAFLI MAULANA AHMAD', 1, 'gdfgfgd', '2026-05-29 22:01:06');

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
(10, 'rafli.maulana@mhs.itenas.ac.id', 'user', '6a278f0a359a2.jpg'),
(11, 'rmaulanaahmad45@gmail.com', 'admin', NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT untuk tabel `rating`
--
ALTER TABLE `rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
