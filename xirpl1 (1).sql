-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 09, 2026 at 06:02 PM
-- Server version: 8.0.44
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xirpl1`
--

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id` int NOT NULL,
  `nip` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id`, `nip`, `nama`, `jenis_kelamin`, `user_id`) VALUES
(2, '32456345', 'Jokooooo', 'L', 2),
(3, '766', 'koko', 'L', 255),
(4, '54223', 'Rereee', 'L', 3),
(5, '867556', 'Ruru', 'P', 4),
(6, '789789789', 'Jawaaaaaaaaaa', 'P', 256);

-- --------------------------------------------------------

--
-- Table structure for table `mapel`
--

CREATE TABLE `mapel` (
  `id` int NOT NULL,
  `kode_mapel` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama_mapel` varchar(100) NOT NULL,
  `guru_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mapel`
--

INSERT INTO `mapel` (`id`, `kode_mapel`, `nama_mapel`, `guru_id`) VALUES
(2, 'A0002', 'Inggris', 2),
(3, 'A0004', 'KKRPL', 3),
(4, 'A0005', 'Lengserkan Prabowo', 4),
(5, 'A0006', 'Lengserkan Prabowo', 5),
(6, 'BIRPL1', 'Bahasa Indonesia ', 5),
(7, 'A1290', 'Bahasa Jerman', 2);

-- --------------------------------------------------------

--
-- Table structure for table `mengajar`
--

CREATE TABLE `mengajar` (
  `id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `siswa_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nilai_rapor`
--

CREATE TABLE `nilai_rapor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `siswa_id` int NOT NULL,
  `mapel_id` int NOT NULL,
  `nilai_kkm` int NOT NULL,
  `nilai_pengetahuan` int NOT NULL,
  `nilai_keterampilan` int NOT NULL,
  `Keterangan` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int NOT NULL,
  `nis` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(10) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `users_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `nis`, `nama`, `kelas`, `jenis_kelamin`, `users_id`) VALUES
(1, '2456', 'wewe', 'x pplg 6', 'P', 6),
(4, '65789', 'uio', 'x pplg 6', 'L', 7),
(7, '878978789', 'BSIMILLAHH', 'xiii', 'L', 244),
(9, '35467', 'Jawajwajawa', 'xixixi', 'L', 246),
(10, '902', 'Patrick', 'XI RPL 1', 'L', 247),
(11, '90909090', 'Eureka Noor Royan Abdurrahman Akram', 'XI RPL 1', 'L', 252),
(12, '79499', 'Selamet Wowok', 'XI RPL 2', 'L', 253);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','guru','siswa') NOT NULL,
  `created_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'Jaka_Guru', 'wowo', 'admin', '2026-08-19 06:55:46'),
(2, 'Joko_Guru', 'wowo123', 'admin', '2026-08-19 06:58:29'),
(3, 'Rere_Guru', 'owowow', 'admin', '2026-08-19 06:59:39'),
(4, 'Ruru_Guru', 'owowow', 'admin', '2026-08-19 06:59:42'),
(6, 'Wewe_Siswa', 'owowow', 'siswa', '2026-08-26 06:22:49'),
(7, 'Uio_Siswa', 'owowow', 'siswa', '2026-08-26 06:24:57'),
(238, 'sdfsf', 'sfddsf', 'siswa', '2026-09-02 06:24:34'),
(239, 'AIOIOIOIO', 'UIruiu', 'siswa', '2026-09-02 06:25:28'),
(240, 'AIOIOIOIO', 'vb', 'siswa', '2026-09-02 06:26:01'),
(241, 'AIOIOIOIO', 'bbjkbmnbmnbmn', 'siswa', '2026-09-02 06:26:30'),
(244, 'bsimillahh_user', 'password123', 'siswa', '2026-09-29 00:08:24'),
(246, 'jawajwajawa_user', 'password123', 'siswa', '2026-09-29 00:08:24'),
(247, 'patrick_user', 'password123', 'siswa', '2026-09-29 00:08:24'),
(248, 'Cobadah', '$2y$10$aO6kyBRsC9J4n2fgRZ4aLulFp8VFYtEkPLSvMwmTNz0Ug9nlBhtiS', 'admin', '2026-09-02 06:47:28'),
(252, 'eureka_user', 'password123', 'siswa', '2026-09-29 00:08:24'),
(253, 'Asep2344', '$2y$10$nCGf5849Eg2DbSD2J1e.UORjzNV2rovTCxZcfdYTS4X0MtwMgZc2S', 'siswa', '2026-09-09 03:51:10'),
(254, 'Jawa', '$2y$10$8xTB.5sqTjvJR0h0DzVL/OmYvkOZD1jjHhUc7V1RSZv5X.YDKnnfC', 'guru', '2026-09-14 05:52:30'),
(255, 'Sumatera235', '$2y$10$EQfrmyV/2aF5bfV8bkyquuoOA7pZ/qHeJ3fm9GZjHVXhtpAOSHqwm', 'guru', '2026-09-14 05:55:47'),
(256, 'Akujawabarat34', '$2y$10$hNLU..g/P6HENTqkuMzniuOX9j6ipkpMvhAAk9SvJoZTwEQrLyQ.a', 'guru', '2026-09-14 05:57:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`,`nip`),
  ADD KEY `fkguruusers` (`user_id`);

--
-- Indexes for table `mapel`
--
ALTER TABLE `mapel`
  ADD PRIMARY KEY (`id`,`kode_mapel`),
  ADD KEY `fkidguru` (`guru_id`);

--
-- Indexes for table `mengajar`
--
ALTER TABLE `mengajar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `siswa_id` (`siswa_id`),
  ADD KEY `fkidmapel` (`mapel_id`);

--
-- Indexes for table `nilai_rapor`
--
ALTER TABLE `nilai_rapor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fkmapelidrapor` (`mapel_id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`,`nis`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `users_id_2` (`users_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `mapel`
--
ALTER TABLE `mapel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `mengajar`
--
ALTER TABLE `mengajar`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=257;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guru`
--
ALTER TABLE `guru`
  ADD CONSTRAINT `fkguruusers` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `mapel`
--
ALTER TABLE `mapel`
  ADD CONSTRAINT `fkidguru` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`);

--
-- Constraints for table `mengajar`
--
ALTER TABLE `mengajar`
  ADD CONSTRAINT `fkidmapel` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`),
  ADD CONSTRAINT `idsiswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`);

--
-- Constraints for table `nilai_rapor`
--
ALTER TABLE `nilai_rapor`
  ADD CONSTRAINT `fkmapelidrapor` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`);

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `iduser` FOREIGN KEY (`users_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
