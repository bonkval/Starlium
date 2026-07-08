-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 08, 2026 at 02:39 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `adidas_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `action` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `user_name`, `action`, `timestamp`) VALUES
(1, 1, 'Main Admin', 'Admin Logged In', '2026-07-02 13:34:14'),
(2, 1, 'Main Admin', 'Admin Logged In', '2026-07-02 15:36:51');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `stock`) VALUES
(1, 'Ultraboost Light', 'Running', 180.00, 50),
(2, 'Superstar Classic', 'Originals', 100.00, 30),
(3, 'Stan Smith', 'Originals', 95.00, 40),
(4, 'Dame 8', 'Basketball', 120.00, 15),
(5, 'Don Issue 4', 'Basketball', 110.00, 20),
(6, 'Samba Classic Black', 'Originals', 90.00, 25),
(7, 'Gazelle Vintage White', 'Originals', 100.00, 20),
(8, 'Campus 00s Grey', 'Originals', 110.00, 15),
(9, 'Adizero Adios Pro 3', 'Running', 250.00, 10),
(10, 'Pureboost 23', 'Running', 140.00, 35),
(11, 'Solarboost 5', 'Running', 160.00, 18),
(12, 'AE 1 (Anthony Edwards)', 'Basketball', 120.00, 12),
(13, 'Harden Volume 8', 'Basketball', 160.00, 14),
(14, 'Trae Young 3', 'Basketball', 140.00, 22),
(15, 'Forum Low Classic', 'Originals', 100.00, 28);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(50) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'buyer',
  `status` varchar(20) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `address`, `phone`, `role`, `status`) VALUES
(1, 'Main Admin', 'admin@adidas.com', 'admin123', 'Headquarters', '12345678', 'admin', 'active'),
(2, 'Cedrick Rafael Vales', 'cedrickvales1111@gmail.com', 'password123', '18 J Legaspi St.', '09655236422', 'buyer', 'active'),
(3, 'Joseph Benedict Bondoc', 'josephbenedictbondoc@gmail.com', 'password123', 'Caloocan', '09626941570', 'buyer', 'active'),
(4, 'Ced Vales', 'cdvales@fit.edu.ph', 'password123', '18 J Legaspi St.', '09655236422', 'admin', 'active'),
(5, 'Jan Rainnier Odarbe', 'jan.rainnier13@gmail.com', 'password123', 'Bulacan', '09655236422', 'buyer', 'active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
