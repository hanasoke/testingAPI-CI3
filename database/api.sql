-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2025 at 10:55 AM
-- Server version: 10.4.14-MariaDB
-- PHP Version: 7.2.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `api`
--

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `color` varchar(50) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `transmission` varchar(100) NOT NULL,
  `seat` int(11) NOT NULL,
  `machine` int(11) NOT NULL,
  `power` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `manufacture` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `name`, `color`, `brand`, `transmission`, `seat`, `machine`, `power`, `price`, `stock`, `manufacture`) VALUES
(1, 'Suzuki Ertiga', 'White', 'Suzuki', 'Manual', 4, 450, 4000, 560000, 50, '28/05/2024'),
(2, 'Mobilio', 'Dark', 'Honda', 'Matic', 3, 460, 900, 30000000, 500, '12/03/2021');

-- --------------------------------------------------------

--
-- Table structure for table `cpus`
--

CREATE TABLE `cpus` (
  `cpu_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `core` int(11) NOT NULL,
  `thread` int(11) NOT NULL,
  `serie` varchar(100) NOT NULL,
  `memory` varchar(100) NOT NULL,
  `manufacturing_node` int(11) NOT NULL,
  `integrated_graphic` varchar(100) NOT NULL,
  `boost_clock` float NOT NULL,
  `total_cache` int(11) NOT NULL,
  `price` varchar(100) NOT NULL,
  `video` text NOT NULL,
  `video_format` varchar(20) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cpus`
--

INSERT INTO `cpus` (`cpu_id`, `name`, `brand`, `core`, `thread`, `serie`, `memory`, `manufacturing_node`, `integrated_graphic`, `boost_clock`, `total_cache`, `price`, `video`, `video_format`, `created_date`, `updated_date`) VALUES
(1, 'Core i9-13900K', 'Intel', 24, 32, '13th Gen', 'DDR5', 10, 'Intel HD 770', 5.8, 36, '11500000', 'cpu_video_1749795241.mp4', 'mp4', '2025-06-12 14:36:31', '2025-06-13 13:14:00'),
(3, 'Core i9-14900K', 'Intel', 24, 32, 'Core i9-14900K', 'DDR5 5600 MT/s, DDR4 3200 MT/s', 10, 'Intel UHD Graphics 770', 6, 36, '10700000', 'cpu_video_1749795335.mp4', 'mp4', '2025-06-12 15:14:02', '2025-06-13 13:15:35'),
(4, 'AMD Ryzen 7950X', 'AMD', 16, 32, 'Ryzen 9 7000 Series', 'DDR5-5200', 5, 'AMD Radeon Graphics', 5.6, 36, '10300500', 'cpu_video_1749795342.mp4', 'mp4', '2025-06-12 15:19:11', '2025-06-13 13:15:41'),
(5, 'Intel Core i7-14700K', 'Intel', 20, 28, 'Core i7', 'DDR5-5600 MT/s, DDR4 3200 MT/s', 10, 'Intel UHD Graphics 770', 5.6, 33, '9552700', 'cpu_video_1749795346.mp4', 'mp4', '2025-06-12 15:30:40', '2025-06-13 13:15:46'),
(6, 'AMD Ryzen 7 7700X', 'AMD', 8, 16, 'Ryzen 7 7000 Series', 'DDR5-5200', 5, 'AMD Radeon Graphics', 5.4, 32, '8552000', 'cpu_video_1749795351.mp4', 'mp4', '2025-06-12 15:36:50', '2025-06-13 13:15:51'),
(7, 'AMD Ryzen 5 7600X', 'AMD', 6, 12, 'Ryzen 5 7000 Series', 'DDR5', 5, 'AMD Radeon Graphics', 5.3, 32, '6523000', 'cpu_video_1749801469.mp4', 'mp4', '2025-06-12 15:52:33', '2025-06-13 14:57:48'),
(9, 'AMD Ryzen 5 7600B', 'AMD', 6, 12, 'Ryzen 5 7000 Series', 'DDR5', 5, 'AMD Radeon Graphics', 5.3, 32, '6523000', 'cpu_video_1749795181.mp4', 'mp4', '2025-06-13 11:20:28', '2025-06-13 13:13:01');

-- --------------------------------------------------------

--
-- Table structure for table `motorcycles`
--

CREATE TABLE `motorcycles` (
  `id_motor` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `color` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `machine` int(11) NOT NULL,
  `volume` float NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `motorcycles`
--

INSERT INTO `motorcycles` (`id_motor`, `name`, `brand`, `color`, `type`, `machine`, `volume`, `created_date`, `updated_date`) VALUES
(9, 'NMax 32', 'Yamaha', 'Black & Silver', 'Matic', 3400, 303.12, '2025-06-11 16:40:23', NULL),
(10, 'Beat Magic', 'Honda', 'Red', 'Matic', 420, 402.24, '2025-06-11 16:41:43', '2025-06-11 17:06:50');

-- --------------------------------------------------------

--
-- Table structure for table `post_category`
--

CREATE TABLE `post_category` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_date` datetime NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `updated_date` datetime DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `post_category`
--

INSERT INTO `post_category` (`category_id`, `name`, `slug`, `created_date`, `created_by`, `updated_date`, `updated_by`) VALUES
(1, 'Saitama', 'munich', '2025-06-12 13:36:20', 'hanas', '2025-06-12 13:53:39', 'hanas'),
(2, 'Jaz Idjez', 'Bologna', '2025-06-12 13:54:18', 'bang jay', '2025-06-12 13:57:18', 'kluivert'),
(4, 'Andrea Pirlo', 'Juventus', '2025-06-12 13:55:08', 'kompany', NULL, NULL),
(5, 'Ter Teran', 'Napoli', '2025-06-12 14:19:42', 'nampol', NULL, NULL),
(6, 'Kevin De Bruyne', 'KDB', '2025-06-12 14:21:56', 'nampol', '2025-06-12 14:22:44', 'Pep Guardiola');

-- --------------------------------------------------------

--
-- Table structure for table `psus`
--

CREATE TABLE `psus` (
  `psu_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `series` varchar(100) NOT NULL,
  `models` varchar(100) NOT NULL,
  `power` varchar(100) NOT NULL,
  `license` varchar(100) NOT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `psus`
--

INSERT INTO `psus` (`psu_id`, `name`, `type`, `series`, `models`, `power`, `license`, `created_date`, `updated_date`) VALUES
(3, 'Thermaltake Toughpower', 'Full Modul', 'Toughpower GF1', 'PS-TPD-0750FNFAGU-1', '750', '684a5e99a42fc.txt', '2025-06-12 11:59:05', NULL),
(4, 'FSP HV PRO', 'ATX', 'HV PRO', 'HV500', '550', '684a6fab730c7.txt', '2025-06-12 13:11:55', NULL),
(5, 'Imperior Gaming', 'ATX', 'Gaming', 'P500', '550', '684a6fdd5a07e.txt', '2025-06-12 13:12:45', NULL),
(6, 'AeroCool Advanced Technologies', 'ATX', 'LUX RGB', 'Modular 8 Plus Bronze', '650', '684a700a5eca4.txt', '2025-06-12 13:13:30', NULL),
(7, 'Armaggedon', 'ARM 45', 'vOLTRON Bronze', '235FX', '300', '684a70491a3cb.txt', '2025-06-12 13:14:33', NULL),
(8, 'Cooler Master', 'ATX', 'MWE Bronze', 'BRONZE - V2', '550', '684a709122ae9.txt', '2025-06-12 13:15:45', NULL),
(9, 'MSI', 'ATX', 'MPG', 'A750GF', '750', '684a70b045d41.txt', '2025-06-12 13:16:16', NULL),
(10, '1STPLAYER', 'ATXM', 'DK Full Modular', 'CV550', '500', '684a70fa7d50e.txt', '2025-06-12 13:17:30', NULL),
(11, 'Aerocool', 'AER0', 'Mirage Gold', '650', '650', '684a7126aedbf.txt', '2025-06-12 13:18:14', NULL),
(12, 'GAMEMAX', 'ATX', 'VP RGB', 'VP-600-RGB', '600', '684a7168ce2a6.txt', '2025-06-12 13:19:20', NULL),
(13, 'Corsair', 'ATX', 'CX', 'CX650', '650', '684a7185e2668.txt', '2025-06-12 13:19:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `age`, `phone`, `address`) VALUES
(1, 'Hanas', 'hanasoke@gmail.com', 26, '085819536158', 'Jl.Sirojul Munir No.12'),
(3, 'saitama', 'saitama@gmail.com', 25, '085819536258', 'Jl.Sutarman No.54'),
(4, 'rudifer', 'rudeifer@gmail.com', 20, '085328146157', 'Jl.Elang Sakti No.20'),
(5, 'Paris Sains Germain', 'paris@gmail.com', 23, '085829546958', 'Jl.Elang Sakti No.20');

-- --------------------------------------------------------

--
-- Table structure for table `vga_cards`
--

CREATE TABLE `vga_cards` (
  `id_card` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `price` varchar(100) NOT NULL,
  `photo` text NOT NULL,
  `release_date` varchar(100) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `vga_cards`
--

INSERT INTO `vga_cards` (`id_card`, `name`, `brand`, `price`, `photo`, `release_date`, `created_date`, `updated_date`) VALUES
(5, 'RTX 2070', 'Nvidia', '4900000', '68494dd9dfb91.jpg', 'Q1 2021', '2025-06-11 16:32:44', '2025-06-11 16:35:21'),
(6, 'GTX 1650', 'Nvidia', '2550000', '68494df7aeb0a.jpg', 'Q2 2019', '2025-06-11 16:35:51', NULL),
(7, 'GeForce RTX 4090', 'Nvidia', '25983750', '684a3d9dc9d69.jpg', 'October 12,2022', '2025-06-12 09:38:21', NULL),
(8, 'GeForce RTX 4080 SUPER', 'Nvidia', '16233750', '684a3df13be9b.jpg', 'January 31,2024', '2025-06-12 09:39:45', NULL),
(9, 'Radeon FX 7900', 'Radeon', '16233750', '684a3e73d71f6.jpg', 'January 31,2024', '2025-06-12 09:41:55', '2025-06-12 10:16:32'),
(10, 'GeForce RTX 4070 Ti SUPER', 'Nvidia', '12983750', '684a3f09edcd7.jpg', 'January 31,2024', '2025-06-12 09:44:25', '2025-06-12 10:15:27'),
(11, 'Radeon RX 7800 XT', 'Radeon', '8108750', '684a4073cda81.jpg', 'September 6, 2023', '2025-06-12 09:50:27', '2025-06-12 10:14:10'),
(12, 'GeForce RTX 4070 Ti', 'Nvidia', '8500000', '684a45bc25c60.jpg', 'May 24, 2023', '2025-06-12 10:07:44', '2025-06-12 10:13:00'),
(13, 'Radeon RX 7700 XT', 'Radeon', '7296250', '684a486cbc8fd.jpg', 'September 6, 2023', '2025-06-12 10:24:28', NULL),
(14, 'GeForce RTX 4060', 'Nvidia', '4858750', '684a49f7a243f.jpg', 'June 29, 2023', '2025-06-12 10:31:03', NULL),
(15, 'Arc A770', 'Intel', '5346250', '684a4a46ec273.jpg', 'October 12, 2022', '2025-06-12 10:32:22', NULL),
(16, 'GeForce RTX 3060', 'Nvidia', '5346250', '684a4b20c52d8.jpg', 'February 25, 2021', '2025-06-12 10:36:00', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cpus`
--
ALTER TABLE `cpus`
  ADD PRIMARY KEY (`cpu_id`);

--
-- Indexes for table `motorcycles`
--
ALTER TABLE `motorcycles`
  ADD PRIMARY KEY (`id_motor`);

--
-- Indexes for table `post_category`
--
ALTER TABLE `post_category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `psus`
--
ALTER TABLE `psus`
  ADD PRIMARY KEY (`psu_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vga_cards`
--
ALTER TABLE `vga_cards`
  ADD PRIMARY KEY (`id_card`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cpus`
--
ALTER TABLE `cpus`
  MODIFY `cpu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `motorcycles`
--
ALTER TABLE `motorcycles`
  MODIFY `id_motor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `post_category`
--
ALTER TABLE `post_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `psus`
--
ALTER TABLE `psus`
  MODIFY `psu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vga_cards`
--
ALTER TABLE `vga_cards`
  MODIFY `id_card` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
