-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 01, 2026 at 06:25 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pustani_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `user_id`, `title`, `category`, `content`, `image`, `status`, `created_at`, `updated_at`) VALUES
(2, 1, 'Smart Farming Berbasis IoT', 'TEKNOLOGI', 'Teknologi pemantauan kelembapan tanah otomatis menggunakan sensor IoT sangat membantu petani dalam menghemat air dan meningkatkan hasil panen secara signifikan...', 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?q=80&w=1470&auto=format&fit=crop', 'published', '2025-12-28 04:49:22', '2025-12-28 16:01:17'),
(3, 1, 'Hidroponik Lahan Sempit', 'BUDIDAYA', 'Memanfaatkan pipa paralon dan sistem NFT (Nutrient Film Technique) adalah solusi terbaik untuk bercocok tanam di lahan perkotaan yang sempit...', 'https://images.unsplash.com/photo-1558449028-b53a39d100fc?q=80&w=1374&auto=format&fit=crop', 'published', '2025-12-28 04:49:22', '2025-12-28 15:37:32'),
(4, 1, 'Pupuk Cair Alami Dapur', 'ORGANIK', 'Jangan buang sampah dapur Anda! Sisa sayuran dan kulit buah bisa diolah menjadi Eco-Enzyme yang sangat subur untuk tanaman...', 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?q=80&w=1374&auto=format&fit=crop', 'published', '2025-12-28 04:49:22', '2025-12-28 15:37:32'),
(5, 1, 'Harmoni Ternak dan Tanaman', 'TERPADU', 'Sistem pertanian terpadu (Integrated Farming System) menggabungkan peternakan sapi dengan pertanian jagung untuk siklus energi yang efisien...', 'https://images.unsplash.com/photo-1516467508483-a7212febe31a?q=80&w=1473&auto=format&fit=crop', 'published', '2025-12-28 04:49:22', '2025-12-28 15:37:32'),
(6, 1, 'Budidaya Jamur Tiram', 'BISNIS', 'Peluang bisnis jamur tiram sangat menjanjikan dengan modal yang relatif kecil. Kuncinya ada pada menjaga kelembapan kumbung...', 'https://images.unsplash.com/photo-1591261730799-ee4e6c2d16d7?q=80&w=1470&auto=format&fit=crop', 'published', '2025-12-28 04:49:22', '2025-12-28 15:37:32'),
(7, 1, 'Potensi Ekspor Buah Tropis', 'EKSPOR', 'Permintaan pasar global terhadap buah manggis dan salak dari Indonesia terus meningkat. Ini adalah standar kualitas yang dibutuhkan...', 'https://images.unsplash.com/photo-1528825871115-3581a5387919?q=80&w=1430&auto=format&fit=crop', 'published', '2025-12-28 04:49:22', '2025-12-28 15:37:32'),
(18, 8, 'contoh', 'Hama & Penyakit', 'asas', '695609f25f905.png', 'published', '2026-01-01 05:45:22', '2026-01-01 05:45:40'),
(19, 8, 'apaaja', 'Hama & Penyakit', 'apaaja', '69560b8a2a8c8.png', 'draft', '2026-01-01 05:52:10', '2026-01-01 05:52:10'),
(20, 8, 'apaaja', 'Teknologi', 'apaaja', '69560d2daf938.png', 'draft', '2026-01-01 05:59:09', '2026-01-01 05:59:09');

-- --------------------------------------------------------

--
-- Table structure for table `article_comments`
--

CREATE TABLE `article_comments` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `article_id` int NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `article_comments`
--

INSERT INTO `article_comments` (`id`, `user_id`, `article_id`, `comment`, `created_at`) VALUES
(1, 1, 2, 'waw keren banger bilker', '2025-12-31 10:18:18'),
(2, 4, 2, 'butut', '2025-12-31 12:54:32');

-- --------------------------------------------------------

--
-- Table structure for table `article_ratings`
--

CREATE TABLE `article_ratings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `article_id` int NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `article_ratings`
--

INSERT INTO `article_ratings` (`id`, `user_id`, `article_id`, `rating`, `created_at`) VALUES
(1, 1, 2, 5, '2025-12-31 10:18:17'),
(2, 4, 2, 1, '2025-12-31 12:54:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','expert') DEFAULT 'user',
  `status_ahli` enum('none','pending','rejected') DEFAULT 'none',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Status verifikasi expert: 0=belum, 1=sudah',
  `is_banned` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `foto_profil` varchar(255) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text,
  `keahlian` varchar(255) DEFAULT NULL,
  `bio` text,
  `dokumen_pendukung` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `password`, `role`, `status_ahli`, `is_verified`, `is_banned`, `created_at`, `foto_profil`, `no_hp`, `alamat`, `keahlian`, `bio`, `dokumen_pendukung`) VALUES
(1, 'billywicaksono.999@gmail.com', 'bilker29', '$2y$10$9yTvDhxAQWUl.ZVVYzP5HuElKPP4i8SZVg.ESAfTRXu/RjBaKCLb6', 'user', 'none', 0, 0, '2025-12-28 02:24:29', '695503ce8a679.jpg', '089510631734', 'Billy Wicaksono', 'Petani Cangkul', 'Hai perkenalkan saya ahli tani', NULL),
(2, 'gispi.desu@gmail.com', 'Ghryshvi', '$2y$10$lceSYjeUd0U2QLjwEVbSL.ul81T8bz/Itrsiyra6o74vckL73zzhG', 'user', 'none', 0, 0, '2025-12-28 11:26:02', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'admin@admin.com', 'admin', '$2y$10$by4TfnOez4J0CIWaLi43MuL2QILNFDGzj4.XwKDM1SigjPQ0XeLPm', 'admin', 'rejected', 0, 0, '2025-12-28 12:58:33', NULL, '', '', 'Pakar', 'apaaja', 'CV_1767242164_4.webp'),
(7, 'billywicaksono@gmail.com', 'bilker', '$2y$10$4pmB7v9R3NVCADTCtVVRHuStXZFiAvWwyjNxYosLNfpdjWwLWKwjK', 'user', 'none', 0, 0, '2025-12-31 08:44:02', NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'ghrysh@gmail.com', 'halo', '$2y$10$Rq80KODtyytDi2xszwIQge.VGFpHmzjQzYhqcGKuX9b6Y.1C1eT7m', 'expert', 'none', 1, 0, '2025-12-31 12:49:51', NULL, NULL, NULL, 'Pakar', 'apaaja', 'CV_1767244701_8.webp');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `article_comments`
--
ALTER TABLE `article_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `article_ratings`
--
ALTER TABLE `article_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_rating` (`user_id`,`article_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `article_comments`
--
ALTER TABLE `article_comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `article_ratings`
--
ALTER TABLE `article_ratings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
