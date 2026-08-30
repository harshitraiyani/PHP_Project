-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 30, 2026 at 12:50 PM
-- Server version: 8.0.46
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vehicle_service_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `vehicle_id` int NOT NULL,
  `service_type_id` int NOT NULL,
  `booking_date` date NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `extra_charge` decimal(10,2) NOT NULL DEFAULT '0.00',
  `extra_charge_note` varchar(255) DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed','Cancelled') DEFAULT 'Pending',
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `vehicle_id`, `service_type_id`, `booking_date`, `price`, `extra_charge`, `extra_charge_note`, `status`, `notes`, `created_at`) VALUES
(1, 4, 1, 3, '2026-08-12', 8140.00, 7000.00, 'change headlight lamp', 'Completed', 'change headlight lamp', '2026-08-11 12:39:16'),
(4, 2, 8, 3, '2026-08-13', 5940.00, 40000.00, 'Installed 4 MRF Company Tyre (10000 * 4)', 'Completed', 'Change Tyre', '2026-08-12 13:22:48'),
(5, 7, 9, 3, '2026-08-25', 6160.00, 0.00, '', 'Completed', '', '2026-08-20 07:16:18');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`) VALUES
(26, 'Audi'),
(24, 'BMW'),
(23, 'BYD'),
(14, 'Chevrolet'),
(17, 'Citroen'),
(18, 'Datsun'),
(15, 'Fiat'),
(20, 'Force Motors'),
(13, 'Ford'),
(22, 'Hindustan Motors'),
(5, 'Honda'),
(2, 'Hyundai'),
(19, 'Isuzu'),
(29, 'Jaguar'),
(16, 'Jeep'),
(7, 'Kia'),
(28, 'Land Rover'),
(30, 'Lexus'),
(4, 'Mahindra'),
(1, 'Maruti Suzuki'),
(25, 'Mercedes-Benz'),
(8, 'MG'),
(31, 'Mini'),
(12, 'Nissan'),
(32, 'Porsche'),
(21, 'Premier'),
(11, 'Renault'),
(9, 'Skoda'),
(3, 'Tata'),
(6, 'Toyota'),
(10, 'Volkswagen'),
(27, 'Volvo');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int NOT NULL,
  `booking_id` int NOT NULL,
  `invoice_no` varchar(30) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `payment_status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `booking_id`, `invoice_no`, `amount`, `tax`, `total`, `payment_status`, `created_at`) VALUES
(1, 1, 'INV-20260812-2607', 15140.00, 2725.20, 17865.20, 'Paid', '2026-08-12 03:25:31'),
(4, 4, 'INV-20260812-7153', 45940.00, 8269.20, 54209.20, 'Paid', '2026-08-12 13:35:28'),
(5, 5, 'INV-20260820-8510', 6160.00, 1108.80, 7268.80, 'Paid', '2026-08-20 07:17:07');

-- --------------------------------------------------------

--
-- Table structure for table `models`
--

CREATE TABLE `models` (
  `id` int NOT NULL,
  `brand_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `segment` varchar(20) NOT NULL DEFAULT 'Hatchback'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `models`
--

INSERT INTO `models` (`id`, `brand_id`, `name`, `segment`) VALUES
(1, 1, 'Swift', 'Hatchback'),
(2, 1, 'Baleno', 'Hatchback'),
(3, 1, 'WagonR', 'Hatchback'),
(4, 1, 'Dzire', 'Sedan'),
(5, 2, 'i20', 'Hatchback'),
(6, 2, 'Creta', 'SUV'),
(7, 2, 'Venue', 'SUV'),
(8, 2, 'Verna', 'Sedan'),
(9, 3, 'Nexon', 'SUV'),
(10, 3, 'Tiago', 'Hatchback'),
(11, 3, 'Harrier', 'SUV'),
(12, 3, 'Punch', 'SUV'),
(13, 4, 'XUV700', 'SUV'),
(14, 4, 'Scorpio', 'SUV'),
(15, 4, 'Thar', 'SUV'),
(16, 5, 'City', 'Sedan'),
(17, 5, 'Amaze', 'Sedan'),
(18, 5, 'Elevate', 'SUV'),
(19, 6, 'Innova', 'SUV'),
(20, 6, 'Fortuner', 'SUV'),
(21, 6, 'Glanza', 'Hatchback'),
(22, 1, '800', 'Hatchback'),
(23, 1, 'Omni', 'Hatchback'),
(24, 1, 'Zen', 'Hatchback'),
(25, 1, 'Esteem', 'Sedan'),
(26, 1, 'Alto', 'Hatchback'),
(27, 1, 'Alto K10', 'Hatchback'),
(28, 1, 'S-Presso', 'Hatchback'),
(29, 1, 'Celerio', 'Hatchback'),
(30, 1, 'Eeco', 'Hatchback'),
(31, 1, 'Ignis', 'Hatchback'),
(32, 1, 'Fronx', 'Hatchback'),
(33, 1, 'Ciaz', 'Sedan'),
(34, 1, 'Ertiga', 'SUV'),
(35, 1, 'XL6', 'SUV'),
(36, 1, 'Brezza', 'SUV'),
(37, 1, 'Grand Vitara', 'SUV'),
(38, 1, 'Jimny', 'SUV'),
(39, 1, 'Invicto', 'SUV'),
(40, 2, 'Santro', 'Hatchback'),
(41, 2, 'Eon', 'Hatchback'),
(42, 2, 'i10', 'Hatchback'),
(43, 2, 'Grand i10 Nios', 'Hatchback'),
(44, 2, 'Aura', 'Sedan'),
(45, 2, 'Elantra', 'Sedan'),
(46, 2, 'Exter', 'SUV'),
(47, 2, 'Alcazar', 'SUV'),
(48, 2, 'Tucson', 'SUV'),
(49, 2, 'Santa Fe', 'SUV'),
(50, 2, 'Ioniq 5', 'Luxury'),
(51, 3, 'Nano', 'Hatchback'),
(52, 3, 'Indica', 'Hatchback'),
(53, 3, 'Indigo', 'Sedan'),
(54, 3, 'Sumo', 'SUV'),
(55, 3, 'Hexa', 'SUV'),
(56, 3, 'Altroz', 'Hatchback'),
(57, 3, 'Tigor', 'Sedan'),
(58, 3, 'Curvv', 'SUV'),
(59, 3, 'Safari', 'SUV'),
(60, 4, 'Xylo', 'SUV'),
(61, 4, 'KUV100', 'Hatchback'),
(62, 4, 'Marazzo', 'SUV'),
(63, 4, 'Bolero', 'SUV'),
(64, 4, 'Bolero Neo', 'SUV'),
(65, 4, 'Scorpio Classic', 'SUV'),
(66, 4, 'Scorpio-N', 'SUV'),
(67, 4, 'XUV300', 'SUV'),
(68, 4, 'XUV400', 'SUV'),
(69, 4, 'Thar Roxx', 'SUV'),
(70, 5, 'Brio', 'Hatchback'),
(71, 5, 'Jazz', 'Hatchback'),
(72, 5, 'Civic', 'Sedan'),
(73, 5, 'WR-V', 'SUV'),
(74, 5, 'BR-V', 'SUV'),
(75, 6, 'Qualis', 'SUV'),
(76, 6, 'Etios', 'Sedan'),
(77, 6, 'Corolla Altis', 'Sedan'),
(78, 6, 'Urban Cruiser Taisor', 'SUV'),
(79, 6, 'Urban Cruiser Hyryder', 'SUV'),
(80, 6, 'Innova Crysta', 'SUV'),
(81, 6, 'Innova HyCross', 'SUV'),
(82, 6, 'Fortuner Legender', 'Luxury'),
(83, 6, 'Camry', 'Luxury'),
(84, 6, 'Land Cruiser', 'Luxury'),
(85, 7, 'Seltos', 'SUV'),
(86, 7, 'Sonet', 'SUV'),
(87, 7, 'Carens', 'SUV'),
(88, 7, 'Carnival', 'Luxury'),
(89, 7, 'EV6', 'Luxury'),
(90, 8, 'Hector', 'SUV'),
(91, 8, 'Astor', 'SUV'),
(92, 8, 'ZS EV', 'SUV'),
(93, 8, 'Comet EV', 'Hatchback'),
(94, 8, 'Gloster', 'Luxury'),
(95, 9, 'Rapid', 'Sedan'),
(96, 9, 'Octavia', 'Luxury'),
(97, 9, 'Superb', 'Luxury'),
(98, 9, 'Kushaq', 'SUV'),
(99, 9, 'Slavia', 'Sedan'),
(100, 9, 'Kodiaq', 'Luxury'),
(101, 10, 'Polo', 'Hatchback'),
(102, 10, 'Vento', 'Sedan'),
(103, 10, 'Ameo', 'Sedan'),
(104, 10, 'Taigun', 'SUV'),
(105, 10, 'Virtus', 'Sedan'),
(106, 10, 'Tiguan', 'Luxury'),
(107, 11, 'Kwid', 'Hatchback'),
(108, 11, 'Scala', 'Sedan'),
(109, 11, 'Fluence', 'Sedan'),
(110, 11, 'Duster', 'SUV'),
(111, 11, 'Captur', 'SUV'),
(112, 11, 'Triber', 'SUV'),
(113, 11, 'Kiger', 'SUV'),
(114, 12, 'Micra', 'Hatchback'),
(115, 12, 'Sunny', 'Sedan'),
(116, 12, 'Terrano', 'SUV'),
(117, 12, 'Kicks', 'SUV'),
(118, 12, 'Magnite', 'SUV'),
(119, 13, 'Ikon', 'Sedan'),
(120, 13, 'Fiesta', 'Sedan'),
(121, 13, 'Figo', 'Hatchback'),
(122, 13, 'Aspire', 'Sedan'),
(123, 13, 'EcoSport', 'SUV'),
(124, 13, 'Endeavour', 'Luxury'),
(125, 14, 'Spark', 'Hatchback'),
(126, 14, 'Beat', 'Hatchback'),
(127, 14, 'Sail', 'Sedan'),
(128, 14, 'Cruze', 'Sedan'),
(129, 14, 'Tavera', 'SUV'),
(130, 14, 'Enjoy', 'SUV'),
(131, 15, 'Palio', 'Hatchback'),
(132, 15, 'Punto', 'Hatchback'),
(133, 15, 'Linea', 'Sedan'),
(134, 16, 'Compass', 'SUV'),
(135, 16, 'Meridian', 'Luxury'),
(136, 16, 'Wrangler', 'Luxury'),
(137, 17, 'C3', 'Hatchback'),
(138, 17, 'eC3', 'Hatchback'),
(139, 17, 'C3 Aircross', 'SUV'),
(140, 17, 'Basalt', 'Sedan'),
(141, 18, 'GO', 'Hatchback'),
(142, 18, 'GO+', 'Hatchback'),
(143, 18, 'redi-GO', 'Hatchback'),
(144, 19, 'D-Max', 'SUV'),
(145, 19, 'MU-X', 'Luxury'),
(146, 20, 'Gurkha', 'SUV'),
(147, 20, 'Trax', 'SUV'),
(148, 21, 'Padmini', 'Hatchback'),
(149, 22, 'Ambassador', 'Sedan'),
(150, 23, 'e6', 'SUV'),
(151, 23, 'Atto 3', 'Luxury'),
(152, 23, 'Seal', 'Luxury'),
(153, 24, '2 Series', 'Luxury'),
(154, 24, '3 Series', 'Luxury'),
(155, 24, '5 Series', 'Luxury'),
(156, 24, '7 Series', 'Luxury'),
(157, 24, 'X1', 'Luxury'),
(158, 24, 'X3', 'Luxury'),
(159, 24, 'X5', 'Luxury'),
(160, 25, 'A-Class Limousine', 'Luxury'),
(161, 25, 'C-Class', 'Luxury'),
(162, 25, 'E-Class', 'Luxury'),
(163, 25, 'S-Class', 'Luxury'),
(164, 25, 'GLA', 'Luxury'),
(165, 25, 'GLC', 'Luxury'),
(166, 25, 'GLE', 'Luxury'),
(167, 26, 'A4', 'Luxury'),
(168, 26, 'A6', 'Luxury'),
(169, 26, 'Q3', 'Luxury'),
(170, 26, 'Q5', 'Luxury'),
(171, 26, 'Q7', 'Luxury'),
(172, 27, 'S90', 'Luxury'),
(173, 27, 'XC40', 'Luxury'),
(174, 27, 'XC60', 'Luxury'),
(175, 27, 'XC90', 'Luxury'),
(176, 28, 'Discovery Sport', 'Luxury'),
(177, 28, 'Range Rover Evoque', 'Luxury'),
(178, 28, 'Range Rover Velar', 'Luxury'),
(179, 28, 'Range Rover Sport', 'Luxury'),
(180, 28, 'Range Rover', 'Luxury'),
(181, 29, 'XF', 'Luxury'),
(182, 29, 'F-Pace', 'Luxury'),
(183, 30, 'NX', 'Luxury'),
(184, 30, 'ES', 'Luxury'),
(185, 30, 'RX', 'Luxury'),
(186, 31, 'Cooper', 'Luxury'),
(187, 31, 'Countryman', 'Luxury'),
(188, 32, 'Macan', 'Luxury'),
(189, 32, 'Cayenne', 'Luxury');

-- --------------------------------------------------------

--
-- Table structure for table `model_pricing`
--

CREATE TABLE `model_pricing` (
  `id` int NOT NULL,
  `model_id` int NOT NULL,
  `base_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `model_pricing`
--

INSERT INTO `model_pricing` (`id`, `model_id`, `base_price`) VALUES
(1, 1, 1500.00),
(2, 2, 1700.00),
(3, 3, 1400.00),
(4, 4, 1600.00),
(5, 5, 1800.00),
(6, 6, 2200.00),
(7, 7, 2000.00),
(8, 8, 1900.00),
(9, 9, 2100.00),
(10, 10, 1600.00),
(11, 11, 2500.00),
(12, 12, 1500.00),
(13, 13, 2800.00),
(14, 14, 2600.00),
(15, 15, 2700.00),
(16, 16, 2000.00),
(17, 17, 1700.00),
(18, 18, 2100.00),
(19, 19, 2600.00),
(20, 20, 3000.00),
(21, 21, 1900.00),
(22, 22, 1300.00),
(23, 23, 1500.00),
(24, 24, 1500.00),
(25, 25, 1600.00),
(26, 26, 1600.00),
(27, 27, 1700.00),
(28, 28, 1700.00),
(29, 29, 1800.00),
(30, 30, 1600.00),
(31, 31, 1900.00),
(32, 32, 2200.00),
(33, 33, 2100.00),
(34, 34, 2000.00),
(35, 35, 2200.00),
(36, 36, 2000.00),
(37, 37, 2300.00),
(38, 38, 2200.00),
(39, 39, 2600.00),
(40, 40, 1700.00),
(41, 41, 1600.00),
(42, 42, 1700.00),
(43, 43, 1800.00),
(44, 44, 1900.00),
(45, 45, 2400.00),
(46, 46, 1800.00),
(47, 47, 2500.00),
(48, 48, 2800.00),
(49, 49, 3000.00),
(50, 50, 3600.00),
(51, 51, 1300.00),
(52, 52, 1500.00),
(53, 53, 1600.00),
(54, 54, 1800.00),
(55, 55, 2400.00),
(56, 56, 2000.00),
(57, 57, 1900.00),
(58, 58, 2300.00),
(59, 59, 2700.00),
(60, 60, 2000.00),
(61, 61, 1700.00),
(62, 62, 2200.00),
(63, 63, 2000.00),
(64, 64, 2000.00),
(65, 65, 2200.00),
(66, 66, 2500.00),
(67, 67, 2000.00),
(68, 68, 2200.00),
(69, 69, 2500.00),
(70, 70, 1700.00),
(71, 71, 1900.00),
(72, 72, 2600.00),
(73, 73, 2100.00),
(74, 74, 2200.00),
(75, 75, 2000.00),
(76, 76, 1800.00),
(77, 77, 2300.00),
(78, 78, 2000.00),
(79, 79, 2400.00),
(80, 80, 2800.00),
(81, 81, 3000.00),
(82, 82, 3700.00),
(83, 83, 3400.00),
(84, 84, 4400.00),
(85, 85, 2300.00),
(86, 86, 1900.00),
(87, 87, 2200.00),
(88, 88, 3200.00),
(89, 89, 3800.00),
(90, 90, 2500.00),
(91, 91, 2200.00),
(92, 92, 2600.00),
(93, 93, 1800.00),
(94, 94, 3200.00),
(95, 95, 2000.00),
(96, 96, 3000.00),
(97, 97, 3400.00),
(98, 98, 2300.00),
(99, 99, 2200.00),
(100, 100, 3500.00),
(101, 101, 2000.00),
(102, 102, 2100.00),
(103, 103, 1900.00),
(104, 104, 2300.00),
(105, 105, 2200.00),
(106, 106, 3200.00),
(107, 107, 1600.00),
(108, 108, 1900.00),
(109, 109, 2200.00),
(110, 110, 2100.00),
(111, 111, 2300.00),
(112, 112, 1900.00),
(113, 113, 1900.00),
(114, 114, 1700.00),
(115, 115, 1800.00),
(116, 116, 2100.00),
(117, 117, 2200.00),
(118, 118, 1900.00),
(119, 119, 1700.00),
(120, 120, 2000.00),
(121, 121, 1800.00),
(122, 122, 1900.00),
(123, 123, 2100.00),
(124, 124, 3100.00),
(125, 125, 1600.00),
(126, 126, 1700.00),
(127, 127, 1800.00),
(128, 128, 2300.00),
(129, 129, 2000.00),
(130, 130, 1900.00),
(131, 131, 1600.00),
(132, 132, 1700.00),
(133, 133, 1900.00),
(134, 134, 2700.00),
(135, 135, 3100.00),
(136, 136, 3800.00),
(137, 137, 1800.00),
(138, 138, 2000.00),
(139, 139, 2100.00),
(140, 140, 2200.00),
(141, 141, 1600.00),
(142, 142, 1700.00),
(143, 143, 1560.00),
(144, 144, 2600.00),
(145, 145, 3200.00),
(146, 146, 2300.00),
(147, 147, 1800.00),
(148, 148, 1400.00),
(149, 149, 1700.00),
(150, 150, 2800.00),
(151, 151, 3400.00),
(152, 152, 4000.00),
(153, 153, 3200.00),
(154, 154, 3800.00),
(155, 155, 4600.00),
(156, 156, 6000.00),
(157, 157, 3600.00),
(158, 158, 4200.00),
(159, 159, 5200.00),
(160, 160, 3400.00),
(161, 161, 4000.00),
(162, 162, 4800.00),
(163, 163, 6400.00),
(164, 164, 3800.00),
(165, 165, 4400.00),
(166, 166, 5400.00),
(167, 167, 3900.00),
(168, 168, 4700.00),
(169, 169, 3700.00),
(170, 170, 4300.00),
(171, 171, 5200.00),
(172, 172, 4200.00),
(173, 173, 3800.00),
(174, 174, 4400.00),
(175, 175, 5400.00),
(176, 176, 4400.00),
(177, 177, 4600.00),
(178, 178, 5200.00),
(179, 179, 6200.00),
(180, 180, 7200.00),
(181, 181, 4200.00),
(182, 182, 4800.00),
(183, 183, 4200.00),
(184, 184, 4400.00),
(185, 185, 5000.00),
(186, 186, 3800.00),
(187, 187, 4200.00),
(188, 188, 5800.00),
(189, 189, 6600.00);

-- --------------------------------------------------------

--
-- Table structure for table `segment_pricing`
--

CREATE TABLE `segment_pricing` (
  `segment` varchar(20) NOT NULL,
  `battery_price` decimal(10,2) NOT NULL,
  `tyre_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `segment_pricing`
--

INSERT INTO `segment_pricing` (`segment`, `battery_price`, `tyre_price`) VALUES
('Hatchback', 3200.00, 3500.00),
('Luxury', 9500.00, 10000.00),
('Sedan', 4200.00, 4500.00),
('SUV', 5800.00, 7000.00);

-- --------------------------------------------------------

--
-- Table structure for table `service_types`
--

CREATE TABLE `service_types` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `base_multiplier` decimal(4,2) DEFAULT '1.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `service_types`
--

INSERT INTO `service_types` (`id`, `name`, `description`, `base_multiplier`) VALUES
(1, 'Basic Service', 'Oil change, filter check, general inspection', 1.00),
(2, 'Standard Service', 'Basic + brake check, wheel alignment', 1.50),
(3, 'Premium Service', 'Standard + AC service, deep cleaning, full diagnostics', 2.20),
(4, 'Denting & Painting', 'Body repair and paint work', 3.00),
(5, 'Battery Replacement', 'Battery health check & full replacement', 0.90),
(6, 'Tyre Replacement (per tyre)', 'Single tyre replacement — select quantity at booking', 0.35);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `created_at`) VALUES
(2, 'Harshit Raiyani', 'harshit72@gmail.com', '9316679643', '$2y$10$uETvm6Pd1V7y5m97AXOWI.E4PPG6CC6vl5DlShiT1MCqcIj5L0G/e', 'customer', '2026-08-11 11:40:18'),
(3, 'Admin', 'admin@example.com', '9999999999', '$2b$10$rlpIJQkS4nj2a6kVb6kviOmCUnPnlF9Yt69LBwTEJ0raAT5v41NY.', 'admin', '2026-08-11 11:42:07'),
(4, 'Raj Patel', 'raj@gmail.com', '1234567890', '$2y$10$OVjr/LsXZKVFzkmRW/kDRukX6TdMKZiKCXFEZJ/mgmUuLbRF2FVK2', 'customer', '2026-08-11 12:35:22'),
(5, 'shubham', 'shubham@gmail.com', '8320150145', '$2y$10$GEk2N6lTNzngKEzG958oQ./QwABBLa2o9rj9BE0SD/WSyhlYhYl2i', 'customer', '2026-08-11 14:13:36'),
(7, 'Gadhiya Rudra', 'rudra@gmail.com', '7984819377', '$2y$10$yUYqUHRIxlywYuJsz2RxQeCQbHhQWlm/Zm2rso1mrcx5q5v7KDKEO', 'customer', '2026-08-20 07:14:15');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `brand_id` int NOT NULL,
  `model_id` int NOT NULL,
  `registration_no` varchar(20) NOT NULL,
  `year` year DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `user_id`, `brand_id`, `model_id`, `registration_no`, `year`, `created_at`) VALUES
(1, 4, 6, 82, 'GJ03RP0007', '2026', '2026-08-11 12:36:08'),
(2, 4, 2, 8, 'GJ03KP0007', '2015', '2026-08-11 12:36:35'),
(3, 5, 1, 4, 'GJ03SP6818', '2014', '2026-08-11 14:15:54'),
(7, 2, 4, 66, 'GJ03PJ7700', '2026', '2026-08-12 13:15:18'),
(8, 2, 16, 134, 'GJ03KP0072', '2022', '2026-08-12 13:17:26'),
(9, 7, 6, 80, 'GJ36AA2000', '2026', '2026-08-20 07:15:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `service_type_id` (`service_type_id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`);

--
-- Indexes for table `models`
--
ALTER TABLE `models`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brand_id` (`brand_id`);

--
-- Indexes for table `model_pricing`
--
ALTER TABLE `model_pricing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `model_id` (`model_id`);

--
-- Indexes for table `segment_pricing`
--
ALTER TABLE `segment_pricing`
  ADD PRIMARY KEY (`segment`);

--
-- Indexes for table `service_types`
--
ALTER TABLE `service_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `model_id` (`model_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `models`
--
ALTER TABLE `models`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT for table `model_pricing`
--
ALTER TABLE `model_pricing`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT for table `service_types`
--
ALTER TABLE `service_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`service_type_id`) REFERENCES `service_types` (`id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `models`
--
ALTER TABLE `models`
  ADD CONSTRAINT `models_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_pricing`
--
ALTER TABLE `model_pricing`
  ADD CONSTRAINT `model_pricing_ibfk_1` FOREIGN KEY (`model_id`) REFERENCES `models` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehicles_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`),
  ADD CONSTRAINT `vehicles_ibfk_3` FOREIGN KEY (`model_id`) REFERENCES `models` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
