-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 29, 2025 lúc 06:29 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `shop_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `image` varchar(100) NOT NULL,
  `is_custom` tinyint(1) DEFAULT 0 COMMENT 'Is custom flower arrangement',
  `custom_data` text DEFAULT NULL COMMENT 'JSON data for custom arrangement'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `pid`, `name`, `price`, `quantity`, `image`, `is_custom`, `custom_data`) VALUES
(9, 3, 0, 'Bó hoa tự thiết kế', 125000, 1, 'custom_bouquet.png', 1, '[{\"id\":\"1\",\"name\":\"Hoa H\\u1ed3ng \\u0110\\u1ecf\",\"price\":15000,\"quantity\":2,\"emoji\":\"\\ud83c\\udf39\",\"type\":\"flower\"},{\"id\":\"20\",\"name\":\"L\\u00e1 Monstera\",\"price\":15000,\"quantity\":2,\"emoji\":\"\\ud83c\\udf3f\",\"type\":\"filler\"},{\"id\":\"42\",\"name\":\"Thi\\u1ec7p Ch\\u00fac M\\u1eebng\",\"price\":15000,\"quantity\":2,\"emoji\":\"\\ud83d\\udc8c\",\"type\":\"accessory\"},{\"id\":\"31\",\"name\":\"Gi\\u1ea5y H\\u00e0n Qu\\u1ed1c\",\"price\":35000,\"quantity\":1,\"emoji\":\"\\ud83c\\udf81\",\"type\":\"wrap\"}]'),
(10, 3, 0, 'Bó hoa tự thiết kế', 435000, 1, 'custom_bouquet.png', 1, '[{\"id\":\"4\",\"name\":\"Hoa H\\u1ed3ng V\\u00e0ng\",\"price\":18000,\"quantity\":5,\"emoji\":\"\\ud83d\\udc9b\",\"type\":\"flower\"},{\"id\":\"6\",\"name\":\"Hoa Tulip\",\"price\":30000,\"quantity\":2,\"emoji\":\"\\ud83c\\udf37\",\"type\":\"flower\"},{\"id\":\"7\",\"name\":\"Hoa Lily\",\"price\":35000,\"quantity\":3,\"emoji\":\"\\ud83c\\udf3a\",\"type\":\"flower\"},{\"id\":\"22\",\"name\":\"C\\u00e0nh Eucalyptus\",\"price\":20000,\"quantity\":4,\"emoji\":\"\\ud83c\\udf43\",\"type\":\"filler\"},{\"id\":\"35\",\"name\":\"Gi\\u1ecf M\\u00e2y\",\"price\":100000,\"quantity\":1,\"emoji\":\"\\ud83e\\uddfa\",\"type\":\"wrap\"}]');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `last_message_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chat_conversations`
--

INSERT INTO `chat_conversations` (`id`, `user_id`, `user_name`, `status`, `last_message_at`, `created_at`) VALUES
(1, 3, 'Ngoc Sinh', 'open', '2025-12-14 06:32:18', '2025-12-14 04:28:00'),
(2, 12, 'Huy Nguyên', 'open', '2025-12-26 04:42:01', '2025-12-26 03:49:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_name` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `conversation_id`, `sender_type`, `sender_id`, `sender_name`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'user', 3, 'Ngoc Sinh', 'chào shoptooi muốn mua hoa tặng bạn gái', 1, '2025-12-14 04:28:00'),
(2, 1, 'user', 3, 'Ngoc Sinh', 'chào bạn tôi muốn mua hoa', 1, '2025-12-14 04:35:19'),
(3, 1, 'user', 3, 'Ngoc Sinh', 'ggffc', 1, '2025-12-14 05:25:49'),
(4, 1, 'user', 3, 'Ngoc Sinh', 'vjkjb', 1, '2025-12-14 05:35:59'),
(5, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:46:07'),
(6, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:46:18'),
(7, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:46:29'),
(8, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:46:40'),
(9, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:46:50'),
(10, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:47:01'),
(11, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:47:12'),
(12, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:47:22'),
(13, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:47:32'),
(14, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:47:42'),
(15, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:47:52'),
(16, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:48:04'),
(17, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:48:15'),
(18, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:48:25'),
(19, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:48:36'),
(20, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:48:47'),
(21, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:48:59'),
(22, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:49:09'),
(23, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:49:20'),
(24, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:49:32'),
(25, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:49:42'),
(26, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:49:54'),
(27, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:50:04'),
(28, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:50:15'),
(29, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:50:26'),
(30, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:50:37'),
(31, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:50:49'),
(32, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:50:59'),
(33, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:51:10'),
(34, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:51:22'),
(35, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:51:33'),
(36, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:51:44'),
(37, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:51:54'),
(38, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:52:05'),
(39, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:52:16'),
(40, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:52:28'),
(41, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:52:39'),
(42, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:52:50'),
(43, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:53:01'),
(44, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:53:11'),
(45, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:53:23'),
(46, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:53:33'),
(47, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:53:44'),
(48, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:53:55'),
(49, 1, 'admin', 1, 'Admin', 'chào bạn', 1, '2025-12-14 05:54:06'),
(50, 1, 'user', 3, 'Ngoc Sinh', 'hiii', 1, '2025-12-14 06:31:50'),
(51, 1, 'admin', 1, 'Admin', 'hi lại', 1, '2025-12-14 06:32:05'),
(52, 1, 'user', 3, 'Ngoc Sinh', 'oke bạn', 1, '2025-12-14 06:32:13'),
(53, 1, 'admin', 1, 'Admin', 'ok bạn', 1, '2025-12-14 06:32:18'),
(54, 2, 'user', 12, 'Huy Nguyên', 'chào shop', 1, '2025-12-26 03:49:23'),
(55, 2, 'admin', 1, 'Admin', ' chào bạn', 1, '2025-12-26 03:49:48'),
(56, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:50:04'),
(57, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:50:20'),
(58, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:50:36'),
(59, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:50:52'),
(60, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:51:08'),
(61, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:51:24'),
(62, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:51:40'),
(63, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:51:56'),
(64, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:52:12'),
(65, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:52:28'),
(66, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:52:44'),
(67, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:53:00'),
(68, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:53:16'),
(69, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:53:32'),
(70, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:53:48'),
(71, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:54:04'),
(72, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:54:20'),
(73, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:54:36'),
(74, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:54:52'),
(75, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:55:08'),
(76, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:55:24'),
(77, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:55:40'),
(78, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:55:56'),
(79, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:56:12'),
(80, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:56:28'),
(81, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:56:44'),
(82, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:57:00'),
(83, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:57:16'),
(84, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:57:32'),
(85, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:57:48'),
(86, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:58:04'),
(87, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:58:20'),
(88, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:58:36'),
(89, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:58:52'),
(90, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:59:08'),
(91, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:59:24'),
(92, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:59:40'),
(93, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 03:59:56'),
(94, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:00:12'),
(95, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:00:28'),
(96, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:00:44'),
(97, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:00:59'),
(98, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:01:15'),
(99, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:01:31'),
(100, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:01:47'),
(101, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:02:03'),
(102, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:02:19'),
(103, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:02:35'),
(104, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:02:51'),
(105, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:03:07'),
(106, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:03:23'),
(107, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:03:39'),
(108, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:03:55'),
(109, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:04:11'),
(110, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:04:27'),
(111, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:04:43'),
(112, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:04:58'),
(113, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:05:14'),
(114, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:05:30'),
(115, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:05:46'),
(116, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:06:02'),
(117, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:06:18'),
(118, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:06:34'),
(119, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:06:50'),
(120, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:07:06'),
(121, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:07:22'),
(122, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:07:38'),
(123, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:07:53'),
(124, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:08:09'),
(125, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:08:25'),
(126, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:08:41'),
(127, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:08:57'),
(128, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:09:13'),
(129, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:09:29'),
(130, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:09:45'),
(131, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:10:01'),
(132, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:10:17'),
(133, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:10:33'),
(134, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:10:49'),
(135, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:11:05'),
(136, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:11:21'),
(137, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:11:37'),
(138, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:11:53'),
(139, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:12:09'),
(140, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:12:25'),
(141, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:12:41'),
(142, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:12:57'),
(143, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:13:13'),
(144, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:13:29'),
(145, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:13:45'),
(146, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:14:01'),
(147, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:14:17'),
(148, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:14:33'),
(149, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:14:49'),
(150, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:15:05'),
(151, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:15:21'),
(152, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:15:37'),
(153, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:15:53'),
(154, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:16:09'),
(155, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:16:25'),
(156, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:16:41'),
(157, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:16:57'),
(158, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:17:13'),
(159, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:17:29'),
(160, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:17:45'),
(161, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:18:01'),
(162, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:18:17'),
(163, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:18:33'),
(164, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:18:49'),
(165, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:19:05'),
(166, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:19:21'),
(167, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:19:37'),
(168, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:19:53'),
(169, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:20:09'),
(170, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:20:25'),
(171, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:20:41'),
(172, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:20:57'),
(173, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:21:13'),
(174, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:21:29'),
(175, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:21:45'),
(176, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:22:01'),
(177, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:22:17'),
(178, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:22:33'),
(179, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:22:49'),
(180, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:23:05'),
(181, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:23:21'),
(182, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:23:37'),
(183, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:23:53'),
(184, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:24:09'),
(185, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:24:25'),
(186, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:24:41'),
(187, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:24:57'),
(188, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:25:13'),
(189, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:25:29'),
(190, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:25:45'),
(191, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:26:01'),
(192, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:26:17'),
(193, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:26:33'),
(194, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:26:49'),
(195, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:27:05'),
(196, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:27:21'),
(197, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:27:37'),
(198, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:27:53'),
(199, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:28:09'),
(200, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:28:25'),
(201, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:28:41'),
(202, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:28:57'),
(203, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:29:13'),
(204, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:29:29'),
(205, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:29:45'),
(206, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:30:01'),
(207, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:30:17'),
(208, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:30:33'),
(209, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:30:49'),
(210, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:31:05'),
(211, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:31:21'),
(212, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:31:37'),
(213, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:31:53'),
(214, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:32:09'),
(215, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:32:25'),
(216, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:32:41'),
(217, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:32:57'),
(218, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:33:13'),
(219, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:33:29'),
(220, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:33:45'),
(221, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:34:01'),
(222, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:34:17'),
(223, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:34:33'),
(224, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:34:49'),
(225, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:35:05'),
(226, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:35:21'),
(227, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:35:37'),
(228, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:35:53'),
(229, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:36:09'),
(230, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:36:25'),
(231, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:36:41'),
(232, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:36:57'),
(233, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:37:13'),
(234, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:37:29'),
(235, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:37:45'),
(236, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:38:01'),
(237, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:38:17'),
(238, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:38:33'),
(239, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:38:49'),
(240, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:39:05'),
(241, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:39:21'),
(242, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:39:37'),
(243, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:39:53'),
(244, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:40:09'),
(245, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:40:25'),
(246, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:40:41'),
(247, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:40:57'),
(248, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:41:13'),
(249, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:41:29'),
(250, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:41:45'),
(251, 2, 'admin', 1, 'Admin', ' chào bạn', 0, '2025-12-26 04:42:01');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_type`, `discount_value`, `min_order`, `max_discount`, `usage_limit`, `used_count`, `start_date`, `end_date`, `is_active`, `created_at`) VALUES
(1, 'WELCOME10', 'percentage', 10.00, 100000.00, 50000.00, 100, 0, '2025-12-14 00:37:06', '2026-01-13 00:37:06', 1, '2025-12-13 17:37:06'),
(2, 'SUMMER50K', 'fixed', 50000.00, 200000.00, NULL, 50, 0, '2025-12-14 00:37:06', '2026-02-12 00:37:06', 1, '2025-12-13 17:37:06'),
(3, 'FLASH20', 'percentage', 20.00, 300000.00, 100000.00, 30, 0, '2025-12-14 00:37:06', '2025-12-21 00:37:06', 1, '2025-12-13 17:37:06');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupon_usage`
--

CREATE TABLE `coupon_usage` (
  `id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `status` enum('sent','failed') DEFAULT 'sent',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `flower_elements`
--

CREATE TABLE `flower_elements` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL,
  `category` enum('main','filler','green','accessory','wrap','vase') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `flower_elements`
--

INSERT INTO `flower_elements` (`id`, `name`, `price`, `image`, `category`, `is_active`, `created_at`) VALUES
(1, 'Hoa Hồng Đỏ', 15000, 'rose_red.png', 'main', 1, '2025-12-26 07:17:23'),
(2, 'Hoa Hồng Hồng', 15000, 'rose_pink.png', 'main', 1, '2025-12-26 07:17:23'),
(3, 'Hoa Hồng Trắng', 15000, 'rose_white.png', 'main', 1, '2025-12-26 07:17:23'),
(4, 'Hoa Hướng Dương', 20000, 'sunflower.png', 'main', 1, '2025-12-26 07:17:23'),
(5, 'Hoa Tulip Đỏ', 25000, 'tulip_red.png', 'main', 1, '2025-12-26 07:17:23'),
(6, 'Hoa Tulip Vàng', 25000, 'tulip_yellow.png', 'main', 1, '2025-12-26 07:17:23'),
(7, 'Hoa Lily Trắng', 30000, 'lily_white.png', 'main', 1, '2025-12-26 07:17:23'),
(8, 'Hoa Cẩm Chướng', 12000, 'carnation.png', 'main', 1, '2025-12-26 07:17:23'),
(9, 'Hoa Cúc Trắng', 10000, 'daisy.png', 'main', 1, '2025-12-26 07:17:23'),
(10, 'Hoa Lan Hồ Điệp', 50000, 'orchid.png', 'main', 1, '2025-12-26 07:17:23'),
(11, 'Baby Breath', 8000, 'baby_breath.png', 'filler', 1, '2025-12-26 07:17:23'),
(12, 'Lá Monstera', 10000, 'monstera.png', 'green', 1, '2025-12-26 07:17:23'),
(13, 'Lá Dương Xỉ', 5000, 'fern.png', 'green', 1, '2025-12-26 07:17:23'),
(14, 'Cành Eucalyptus', 12000, 'eucalyptus.png', 'green', 1, '2025-12-26 07:17:23'),
(15, 'Nơ Đỏ', 10000, 'ribbon_red.png', 'accessory', 1, '2025-12-26 07:17:23'),
(16, 'Nơ Hồng', 10000, 'ribbon_pink.png', 'accessory', 1, '2025-12-26 07:17:23'),
(17, 'Giấy Gói Kraft', 15000, 'wrap_kraft.png', 'wrap', 1, '2025-12-26 07:17:23'),
(18, 'Giấy Gói Trắng', 15000, 'wrap_white.png', 'wrap', 1, '2025-12-26 07:17:23');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `game_scores`
--

CREATE TABLE `game_scores` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_type` varchar(50) NOT NULL,
  `score` int(11) DEFAULT 0,
  `best_score` int(11) DEFAULT 0,
  `played_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `garden_points`
--

CREATE TABLE `garden_points` (
  `user_id` int(11) NOT NULL,
  `points` int(11) DEFAULT 0,
  `total_harvested` int(11) DEFAULT 0,
  `longest_streak` int(11) DEFAULT 0,
  `last_action_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `garden_points`
--

INSERT INTO `garden_points` (`user_id`, `points`, `total_harvested`, `longest_streak`, `last_action_date`) VALUES
(3, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory_history`
--

CREATE TABLE `inventory_history` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `change_type` enum('restock','sale','adjustment','return') NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `quantity_before` int(11) NOT NULL,
  `quantity_after` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `inventory_history`
--

INSERT INTO `inventory_history` (`id`, `product_id`, `change_type`, `quantity_change`, `quantity_before`, `quantity_after`, `order_id`, `admin_id`, `notes`, `created_at`) VALUES
(1, 5, 'sale', -4, 25, 21, 1, NULL, 'Stock reduced due to order #1', '2025-12-14 08:37:59'),
(2, 5, 'sale', -3, 21, 18, 2, NULL, 'Stock reduced due to order #2', '2025-12-14 08:45:54'),
(3, 8, 'sale', -1, 50, 49, 28, NULL, 'Stock reduced due to order #28', '2025-12-26 03:35:43');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `message` varchar(500) NOT NULL,
  `admin_reply` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `email` varchar(100) NOT NULL,
  `method` varchar(50) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'cod',
  `payment_transaction_id` varchar(255) DEFAULT NULL,
  `address` varchar(500) NOT NULL,
  `total_products` varchar(1000) NOT NULL,
  `total_price` int(11) NOT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `placed_on` varchar(50) NOT NULL,
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending',
  `delivery_status` varchar(50) NOT NULL DEFAULT 'Đang xử lý',
  `delivery_lat` float DEFAULT NULL,
  `delivery_lng` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `name`, `number`, `email`, `method`, `payment_method`, `payment_transaction_id`, `address`, `total_products`, `total_price`, `coupon_code`, `discount_amount`, `placed_on`, `payment_status`, `delivery_status`, `delivery_lat`, `delivery_lng`) VALUES
(1, 3, 'Nguyễn Ngọc Sinh', '0355610260', 'ngocsinh6905@gmail.com', 'momo', 'momo', NULL, 'Số nhà 12321, fdfsfsd, sdfsdf, fsfdsfds - 3123213', 'Sen hồng (4) ', 1520000, NULL, 0.00, '14-12-2025', 'pending', 'ðang giao', 14.055, 109.045),
(2, 3, 'Nguyễn Ngọc Sinh', '0355610260', 'ngocsinh6905@gmail.com', 'momo', 'momo', NULL, 'Số nhà qqqq, 2132, qqq, qqq - 12323', 'Sen hồng (3) ', 1140000, NULL, 0.00, '14-12-2025', 'pending', '??ang x??? l??', 14.055, 109.045),
(3, 3, 'Nguyễn Ngọc Sinh', '0355610260', 'ngocsinh6905@gmail.com', 'momo', 'momo', NULL, 'Số nhà 12321, Huỳnh Thị Hai, Quận 12, Việt Nam - 1200000', 'Hoa Sen (2) ', 600000, NULL, 0.00, '25-12-2025', 'pending', '??ang x??? l??', 14.055, 109.045),
(16, 3, 'Nguyễn Ngọc Sinh', '0355610260', 'ngocsinh6005@gmail.com', 'momo', 'cod', NULL, 'Số 45 Nguyễn Huệ, Quận 1, TP.HCM', 'Hoa Hồng (2), Hoa Sen (1)', 850000, NULL, 0.00, '2025-12-20 10:30:00', 'completed', 'Đã giao', 14.055, 109.045),
(17, 4, 'Trần Văn Minh', '0901234567', 'minhtran@gmail.com', 'cash on delivery', 'cod', NULL, 'Số 123 Lê Lợi, Quận 3, TP.HCM', 'Bó hoa cưới trắng (1)', 1200000, NULL, 0.00, '2025-12-18 14:20:00', 'completed', 'Đã giao', 14.055, 109.045),
(18, 5, 'Lê Thị Hương', '0912345678', 'huongle@gmail.com', 'bank', 'cod', NULL, 'Số 78 Trần Hưng Đạo, Quận 5, TP.HCM', 'Hoa tulip Vàng (3), Hoa Sen (2)', 1500000, NULL, 0.00, '2025-12-15 09:15:00', 'completed', 'Đã giao', 14.055, 109.045),
(19, 6, 'Phạm Đức Anh', '0923456789', 'ducanhpham@gmail.com', 'momo', 'cod', NULL, 'Số 200 Điện Biên Phủ, Quận Bình Thạnh, TP.HCM', 'Hoa cầm tay cô dâu (1)', 980000, NULL, 0.00, '2025-12-10 16:45:00', 'completed', 'Đã giao', 14.055, 109.045),
(20, 7, 'Nguyễn Thị Mai', '0934567890', 'mainguyen@gmail.com', 'cash on delivery', 'cod', NULL, 'Số 55 Võ Văn Tần, Quận 3, TP.HCM', 'Bó hoa hồng đỏ (2)', 760000, NULL, 0.00, '2025-12-25 08:30:00', 'pending', 'Đang giao', 14.055, 109.045),
(21, 8, 'Hoàng Văn Tùng', '0945678901', 'tunghoang@gmail.com', 'momo', 'cod', NULL, 'Số 89 Nguyễn Thị Minh Khai, Quận 1, TP.HCM', 'Hoa trang trí tiệc cưới (1), Nến thơm lavender (2)', 2100000, NULL, 0.00, '2025-12-25 11:20:00', 'completed', 'Đang giao', 14.055, 109.045),
(22, 9, 'Vũ Thị Lan', '0956789012', 'lanvu@gmail.com', 'bank', 'cod', NULL, 'Số 150 Cách Mạng Tháng 8, Quận 10, TP.HCM', 'Sen hồng (5)', 1250000, NULL, 0.00, '2025-12-24 15:00:00', 'completed', 'Đang giao', 14.055, 109.045),
(23, 10, 'Đặng Minh Tuấn', '0967890123', 'tuandang@gmail.com', 'cash on delivery', 'cod', NULL, 'Số 33 Pasteur, Quận 1, TP.HCM', 'Hoa Hồng (3), Hoa tulip Vàng (2)', 1100000, NULL, 0.00, '2025-12-26 07:45:00', 'pending', 'Đang xử lý', 14.055, 109.045),
(24, 11, 'Bùi Thị Ngọc', '0978901234', 'ngocbui@gmail.com', 'momo', 'cod', NULL, 'Số 45 Nguyễn Huệ, Quận 1, TP.HCM', 'Mẫu đơn (2)', 680000, NULL, 0.00, '2025-12-26 08:15:00', 'pending', 'Đang xử lý', 14.055, 109.045),
(25, 3, 'Nguyễn Ngọc Sinh', '0355610260', 'ngocsinh6005@gmail.com', 'bank', 'cod', NULL, 'Số 123 Lê Lợi, Quận 3, TP.HCM', 'Hoa Sen (4)', 920000, NULL, 0.00, '2025-12-26 09:00:00', 'pending', 'Đang xử lý', 14.055, 109.045),
(26, 4, 'Trần Văn Minh', '0901234567', 'minhtran@gmail.com', 'cash on delivery', 'cod', NULL, 'Số 78 Trần Hưng Đạo, Quận 5, TP.HCM', 'Bó hoa cưới trắng (1)', 1200000, NULL, 0.00, '2025-12-22 10:00:00', 'pending', 'Đã hủy', 14.055, 109.045),
(27, 5, 'Lê Thị Hương', '0912345678', 'huongle@gmail.com', 'momo', 'cod', NULL, 'Số 55 Võ Văn Tần, Quận 3, TP.HCM', 'Hoa cầm tay cô dâu (2)', 1960000, NULL, 0.00, '2025-12-19 14:30:00', 'pending', 'Đã hủy', 14.055, 109.045),
(28, 12, 'Huy Nguyên', '0355610206', 'nguyen@gmail.com', 'cod', 'cod', NULL, 'Số nhà 12, Huỳnh Thị Hai, Quận 12, Việt Nam - 122', 'Hoa cầm tay cô dâu (1) ', 1200000, NULL, 0.00, '26-12-2025', 'pending', 'Đang xử lý', 14.055, 109.045),
(29, 3, 'Nguyễn Ngọc Sinh', '0922123123', 'ngocsinh6905@gmail.com', 'cod', 'cod', NULL, 'Số nhà 12, Huỳnh Thị Hai, Quận 12, Việt Nam - 123123', 'Bó hoa tự thiết kế (1) ', 65000, NULL, 0.00, '26-12-2025', 'pending', 'Đang xử lý', 14.055, 109.045),
(30, 3, 'Nguyễn Ngọc Sinh', '0912282192', 'ngocsinh6905@gmail.com', 'momo', 'momo', NULL, 'Số nhà 12, Huỳnh Thị Hai, Quận 12, Việt Nam - 12323', 'Bó hoa tự thiết kế (1) ', 198000, NULL, 0.00, '26-12-2025', 'pending', 'Đang xử lý', 14.055, 109.045);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 5, 4, 380000),
(2, 2, 5, 3, 380000),
(3, 3, 0, 2, 300000),
(4, 28, 8, 1, 1200000),
(5, 29, 0, 1, 65000),
(6, 30, 0, 1, 198000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_reviews`
--

CREATE TABLE `order_reviews` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `gateway` varchar(50) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `request_data` text DEFAULT NULL,
  `response_data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `payment_transactions`
--

INSERT INTO `payment_transactions` (`id`, `order_id`, `gateway`, `transaction_id`, `amount`, `status`, `request_data`, `response_data`, `created_at`, `updated_at`) VALUES
(1, 1, 'momo', 'ORD1_1765701479', 1520000.00, 'pending', '{\"partnerCode\":\"MOMOBKUN20180529\",\"partnerName\":\"Flower Store\",\"storeId\":\"FlowerStore\",\"requestId\":\"1765701479\",\"amount\":\"1520000\",\"orderId\":\"ORD1_1765701479\",\"orderInfo\":\"Thanh to\\u00e1n \\u0111\\u01a1n h\\u00e0ng #1 - Sen h\\u1ed3ng (4) \",\"redirectUrl\":\"http:\\/\\/localhost\\/flower-shop\\/payment_return.php?gateway=momo\",\"ipnUrl\":\"http:\\/\\/localhost\\/flower-shop\\/payment_ipn.php?gateway=momo\",\"lang\":\"vi\",\"extraData\":\"\",\"requestType\":\"payWithATM\",\"signature\":\"9b63d72df0487dedea37924f2d2e190141fba4351dbd7264baac1eb2f37132b7\"}', NULL, '2025-12-14 08:37:59', NULL),
(2, 2, 'momo', 'ORD2_1765701954', 1140000.00, 'pending', '{\"partnerCode\":\"MOMOBKUN20180529\",\"partnerName\":\"Flower Store\",\"storeId\":\"FlowerStore\",\"requestId\":\"1765701954\",\"amount\":\"1140000\",\"orderId\":\"ORD2_1765701954\",\"orderInfo\":\"Thanh to\\u00e1n \\u0111\\u01a1n h\\u00e0ng #2 - Sen h\\u1ed3ng (3) \",\"redirectUrl\":\"http:\\/\\/localhost\\/flower-shop\\/payment_return.php?gateway=momo\",\"ipnUrl\":\"http:\\/\\/localhost\\/flower-shop\\/payment_ipn.php?gateway=momo\",\"lang\":\"vi\",\"extraData\":\"\",\"requestType\":\"payWithATM\",\"signature\":\"908b5735aa5e29eb1ea343d5858e763f255b778a83597e6582a9daf4dd4cf344\"}', NULL, '2025-12-14 08:45:54', NULL),
(3, 3, 'momo', 'ORD3_1766695737', 600000.00, 'pending', '{\"partnerCode\":\"MOMOBKUN20180529\",\"partnerName\":\"Flower Store\",\"storeId\":\"FlowerStore\",\"requestId\":\"1766695737\",\"amount\":\"600000\",\"orderId\":\"ORD3_1766695737\",\"orderInfo\":\"Thanh to\\u00e1n \\u0111\\u01a1n h\\u00e0ng #3 - Hoa Sen (2) \",\"redirectUrl\":\"http:\\/\\/localhost\\/flower-shop\\/pages\\/payment_return.php?gateway=momo\",\"ipnUrl\":\"http:\\/\\/localhost\\/flower-shop\\/pages\\/payment_ipn.php?gateway=momo\",\"lang\":\"vi\",\"extraData\":\"\",\"requestType\":\"payWithATM\",\"signature\":\"8eed83a19992270635ca70b85b4185f4aad5932a06d8dc90dc09195cc1139712\"}', NULL, '2025-12-25 20:48:58', NULL),
(4, 28, 'cod', 'COD28_1766720143', 1200000.00, 'pending', NULL, NULL, '2025-12-26 03:35:43', NULL),
(5, 29, 'cod', 'COD29_1766735026', 65000.00, 'pending', NULL, NULL, '2025-12-26 07:43:46', NULL),
(6, 30, 'momo', 'ORD30_1766735086', 198000.00, 'pending', '{\"partnerCode\":\"MOMOBKUN20180529\",\"partnerName\":\"Flower Store\",\"storeId\":\"FlowerStore\",\"requestId\":\"1766735086\",\"amount\":\"198000\",\"orderId\":\"ORD30_1766735086\",\"orderInfo\":\"Thanh to\\u00e1n \\u0111\\u01a1n h\\u00e0ng #30 - B\\u00f3 hoa t\\u1ef1 thi\\u1ebft k\\u1ebf (1) \",\"redirectUrl\":\"http:\\/\\/localhost\\/flower-shop\\/pages\\/payment_return.php?gateway=momo\",\"ipnUrl\":\"http:\\/\\/localhost\\/flower-shop\\/pages\\/payment_ipn.php?gateway=momo\",\"lang\":\"vi\",\"extraData\":\"\",\"requestType\":\"payWithATM\",\"signature\":\"1161e0c555b0396bf38d793377d77dabed4f234242a6ea1b05438b1bab976024\"}', NULL, '2025-12-26 07:44:46', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `popular_searches`
-- (See below for the actual view)
--
CREATE TABLE `popular_searches` (
`search_query` varchar(255)
,`search_count` bigint(21)
,`last_searched` timestamp
);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `details` varchar(500) NOT NULL,
  `price` int(11) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `low_stock_threshold` int(11) NOT NULL DEFAULT 10,
  `stock_status` enum('in_stock','low_stock','out_of_stock') DEFAULT 'in_stock',
  `is_available` tinyint(1) DEFAULT 1,
  `last_stock_update` timestamp NULL DEFAULT NULL,
  `image` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `details`, `price`, `stock_quantity`, `low_stock_threshold`, `stock_status`, `is_available`, `last_stock_update`, `image`, `category`) VALUES
(1, 'Hoa Sen', 'Hoa sen – biểu tượng của sự thuần khiết, thanh tao', 300000, 50, 10, 'in_stock', 1, NULL, 'hoasen.png', 'ngay-le'),
(2, 'Mộng Mơ', 'Giỏ hoa pastel nhẹ nhàng với hoa cát tường, hoa hồng', 350000, 30, 10, 'in_stock', 1, NULL, 'mongmo.png', 'sinh-nhat'),
(3, 'Nến thơm lavender', 'Nến thơm hương lavender giúp thư giãn', 250000, 100, 20, 'in_stock', 1, NULL, 'nenlavender.jpg', 'qua-tang'),
(4, 'Hoa Hồng', 'Hoa hồng đỏ – biểu tượng của tình yêu nồng nàn', 500000, 40, 10, 'in_stock', 1, NULL, 'hoahongdo.png', 'ngay-le'),
(5, 'Sen hồng', 'Biểu tượng của sự thanh cao, rực rỡ', 380000, 18, 10, 'in_stock', 1, '2025-12-14 08:45:54', 'sen.jpg', 'sinh-nhat'),
(6, 'Hoa tulip Vàng', 'Biểu tượng của niềm vui và lời chúc khởi đầu hạnh phúc', 450000, 20, 5, 'in_stock', 1, NULL, 'yellow_tulipa.jpg', 'dam-cuoi'),
(7, 'Bó hoa cưới trắng', 'Bó hoa cưới tinh khôi với hoa hồng trắng và baby breath', 850000, 50, 10, 'in_stock', 1, NULL, 'white_bouquet.jpg', 'dam-cuoi'),
(8, 'Hoa cầm tay cô dâu', 'Hoa cầm tay sang trọng với hoa mẫu đơn và hoa lan', 1200000, 49, 10, 'in_stock', 1, '2025-12-26 03:35:43', 'damcuoi1.png', 'dam-cuoi'),
(9, 'Hoa trang trí tiệc cưới', 'Bình hoa để bàn tiệc cưới phong cách châu Âu', 650000, 50, 10, 'in_stock', 1, NULL, 'wedding3.jpg', 'dam-cuoi'),
(10, 'Bó hoa hồng đỏ', 'Bó 20 hoa hồng đỏ Ecuador nhập khẩu', 750000, 50, 10, 'in_stock', 1, NULL, 'hoahongdo.png', 'sinh-nhat'),
(11, 'Giỏ hoa mix màu', 'Giỏ hoa nhiều màu sắc tươi vui cho ngày sinh nhật', 450000, 50, 10, 'in_stock', 1, NULL, 'tonghop.jpg', 'sinh-nhat'),
(12, 'Hộp hoa hồng sáp', 'Hộp hoa hồng sáp thơm lâu, quà tặng ý nghĩa', 350000, 50, 10, 'in_stock', 1, NULL, 'pink_roses.jpg', 'sinh-nhat'),
(13, 'Bó hoa hướng dương', 'Bó hoa hướng dương rực rỡ - biểu tượng của niềm vui', 280000, 50, 10, 'in_stock', 1, NULL, 'sunflower.jpg', 'sinh-nhat'),
(14, 'Hoa Valentine', 'Bó hoa hồng đỏ 99 bông - tình yêu vĩnh cửu', 2500000, 50, 10, 'in_stock', 1, NULL, 'red_tulipa.jpg', 'ngay-le'),
(15, 'Hoa 8/3', 'Bó hoa tulip hồng tặng mẹ, tặng vợ ngày 8/3', 550000, 50, 10, 'in_stock', 1, NULL, 'pink_bouquet.jpg', 'ngay-le'),
(16, 'Hoa 20/10', 'Giỏ hoa tone hồng pastel ngày Phụ nữ Việt Nam', 480000, 50, 10, 'in_stock', 1, NULL, 'pink_queen_rose.jpg', 'ngay-le'),
(17, 'Hoa Tết', 'Chậu hoa mai vàng rực rỡ đón xuân', 1500000, 50, 10, 'in_stock', 1, NULL, 'ngayle1.png', 'ngay-le'),
(18, 'Hộp quà chocolate hoa', 'Hộp quà gồm hoa hồng và chocolate Ferrero', 680000, 50, 10, 'in_stock', 1, NULL, 'quatang1.jpg', 'qua-tang'),
(19, 'Gấu bông kèm hoa', 'Gấu bông dễ thương kèm bó hoa nhỏ xinh', 420000, 50, 10, 'in_stock', 1, NULL, 'gift2.jpg', 'qua-tang'),
(20, 'Set quà spa thư giãn', 'Hộp quà gồm nến thơm, muối tắm và hoa khô', 550000, 50, 10, 'in_stock', 1, NULL, 'gift3.jpg', 'qua-tang'),
(21, 'Lẵng hoa khai trương', 'Lẵng hoa to đẹp chúc mừng khai trương', 1200000, 50, 10, 'in_stock', 1, NULL, 'opening.jpg', 'qua-tang'),
(22, 'Hoa ly trắng', 'Bó hoa ly trắng tinh khiết, hương thơm nhẹ nhàng', 320000, 50, 10, 'in_stock', 1, NULL, 'lily.jpg', 'ngay-le'),
(23, 'Hoa cẩm chướng', 'Bó hoa cẩm chướng - biểu tượng của tình mẫu tử', 250000, 50, 10, 'in_stock', 1, NULL, 'carnation.jpg', 'sinh-nhat'),
(24, 'Hoa lan hồ điệp', 'Chậu lan hồ điệp sang trọng làm quà tặng', 980000, 50, 10, 'in_stock', 1, NULL, 'orchid.jpg', 'qua-tang'),
(25, 'Bó hoa cúc họa mi', 'Bó hoa cúc họa mi trong trẻo, thanh khiết', 180000, 50, 10, 'in_stock', 1, NULL, 'daisy.jpg', 'sinh-nhat');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_compare`
--

CREATE TABLE `product_compare` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_views`
--

CREATE TABLE `product_views` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_reply` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_name`, `rating`, `comment`, `image`, `created_at`, `admin_reply`) VALUES
(11, 1, 'Trần Văn Minh', 5, 'Hoa rất đẹp và tươi, giao hàng nhanh. Sẽ ủng hộ shop dài dài!', NULL, '2025-12-20 09:00:00', NULL),
(12, 4, 'Lê Thị Hương', 5, 'Bó hoa hồng rất đẹp, bạn gái rất thích. Cảm ơn shop!', NULL, '2025-12-18 12:15:00', NULL),
(13, 6, 'Phạm Đức Anh', 4, 'Hoa tulip vàng đẹp lắm, chỉ có điều giao hàng hơi chậm một chút.', NULL, '2025-12-16 03:50:00', NULL),
(14, 7, 'Nguyễn Thị Mai', 5, 'Bó hoa cưới tuyệt vời! Đúng như mong đợi. Highly recommend!', NULL, '2025-12-11 13:30:00', NULL),
(15, 10, 'Hoàng Văn Tùng', 4, 'Hoa hồng đỏ rất tươi, đóng gói cẩn thận. Giá hợp lý.', NULL, '2025-12-25 07:30:00', NULL),
(16, 5, 'Vũ Thị Lan', 5, 'Sen hồng đẹp quá! Để bàn làm việc rất sang. Shop nhiệt tình!', NULL, '2025-12-23 05:00:00', NULL),
(17, 9, 'Đặng Minh Tuấn', 4, 'Hoa trang trí tiệc cưới rất đẹp, tư vấn nhiệt tình.', NULL, '2025-12-24 10:15:00', NULL),
(18, 8, 'Bùi Thị Ngọc', 5, 'Hoa cầm tay cô dâu xinh quá! Cảm ơn shop đã hỗ trợ nhanh chóng!', NULL, '2025-12-22 02:45:00', NULL),
(19, 1, 'Nguyễn Thị Mai', 4, 'Hoa sen đẹp, thơm nhẹ nhàng. Rất hài lòng với dịch vụ.', NULL, '2025-12-19 06:50:00', NULL),
(20, 4, 'Vũ Thị Lan', 5, 'Mua hoa hồng tặng mẹ, mẹ rất thích. Sẽ quay lại!', NULL, '2025-12-17 10:30:00', NULL),
(21, 2, 'Trần Thu Hà', 5, 'Hoa mẫu đơn đẹp xuất sắc! Màu sắc tươi tắn, cánh hoa dày dặn.', NULL, '2025-12-15 02:30:00', NULL),
(22, 3, 'Lý Minh Châu', 4, 'Nến thơm lavender rất dễ chịu, mùi hương nhẹ nhàng thư giãn.', NULL, '2025-12-14 08:00:00', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `search_history`
--

CREATE TABLE `search_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `search_query` varchar(255) NOT NULL,
  `results_count` int(11) DEFAULT 0,
  `clicked_product_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `stock_alerts`
--

CREATE TABLE `stock_alerts` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `alert_type` enum('low_stock','out_of_stock') NOT NULL,
  `current_quantity` int(11) NOT NULL,
  `threshold` int(11) NOT NULL,
  `is_resolved` tinyint(1) DEFAULT 0,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` varchar(20) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `user_type`) VALUES
(1, 'Admin', 'admin@gmail.com', '$2y$10$joE.clQu0sXFgBFVqqp0reGq5cTNtzYoch7WeZF1AakJ5pDwlTJ56', 'admin'),
(2, 'Khách Hàng Test', 'user01@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
(3, 'Ngoc Sinh', 'ngocsinh@gmail.com', '$2y$10$mV56ev9d/qUwulUD.iQdrekgyq2EgDVV2G/uCVlZp0T1W3xyE320S', 'user'),
(4, 'Trần Văn Minh', 'minhtran@gmail.com', '$2y$10$abcdefghijklmnopqrstuv', 'user'),
(5, 'Lê Thị Hương', 'huongle@gmail.com', '$2y$10$abcdefghijklmnopqrstuv', 'user'),
(6, 'Phạm Đức Anh', 'ducanhpham@gmail.com', '$2y$10$abcdefghijklmnopqrstuv', 'user'),
(7, 'Nguyễn Thị Mai', 'mainguyen@gmail.com', '$2y$10$abcdefghijklmnopqrstuv', 'user'),
(8, 'Hoàng Văn Tùng', 'tunghoang@gmail.com', '$2y$10$abcdefghijklmnopqrstuv', 'user'),
(9, 'Vũ Thị Lan', 'lanvu@gmail.com', '$2y$10$abcdefghijklmnopqrstuv', 'user'),
(10, 'Đặng Minh Tuấn', 'tuandang@gmail.com', '$2y$10$abcdefghijklmnopqrstuv', 'user'),
(11, 'Bùi Thị Ngọc', 'ngocbui@gmail.com', '$2y$10$abcdefghijklmnopqrstuv', 'user'),
(12, 'Huy Nguyên', 'nguyen@gmail.com', '$2y$12$jt2c8PGCgvslzKDxcQI3c.HKmqVtdM9jXp1KIyHgVNBG2ZxHdg1Ge', 'user'),
(14, 'mai no', 'no@gmail.com', '$2y$12$/FURxTYJ3xkZO2DF2k6pw.Z8S1xt2ScrJ0nLIAQjHK3zfhFJo3rUO', 'user'),
(17, 'Huy Nguyên 1', 'nguyen1@gmail.com', '$2y$12$dYYRYt.zBNy6ICwagw0SweFgyeCZA0CU0d43oECyxATXLSTEaBQCG', 'user');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_vouchers`
--

CREATE TABLE `user_vouchers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `collected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `user_vouchers`
--

INSERT INTO `user_vouchers` (`id`, `user_id`, `voucher_id`, `is_used`, `collected_at`, `used_at`) VALUES
(1, 3, 8, 0, '2025-12-29 13:03:32', NULL),
(2, 3, 4, 0, '2025-12-29 13:03:56', NULL),
(3, 3, 3, 0, '2025-12-29 14:54:36', NULL),
(4, 3, 7, 0, '2025-12-29 17:07:01', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `virtual_garden`
--

CREATE TABLE `virtual_garden` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `slot_index` int(11) NOT NULL,
  `flower_type` varchar(50) DEFAULT NULL,
  `planted_at` timestamp NULL DEFAULT NULL,
  `last_watered` timestamp NULL DEFAULT NULL,
  `growth_stage` int(11) DEFAULT 0,
  `is_dead` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `virtual_garden`
--

INSERT INTO `virtual_garden` (`id`, `user_id`, `slot_index`, `flower_type`, `planted_at`, `last_watered`, `growth_stage`, `is_dead`) VALUES
(1, 3, 0, 'tulip', '2025-12-26 08:52:51', '2025-12-26 08:52:51', 0, 1),
(2, 3, 1, 'sunflower', '2025-12-26 08:52:53', '2025-12-26 08:52:53', 0, 1),
(3, 3, 2, 'hibiscus', '2025-12-26 08:52:56', '2025-12-26 08:52:56', 0, 1),
(4, 3, 3, 'rose', '2025-12-26 08:52:58', '2025-12-26 08:52:58', 0, 1),
(5, 3, 4, 'tulip', '2025-12-26 08:53:00', '2025-12-26 08:53:00', 0, 1),
(6, 3, 5, 'hibiscus', '2025-12-26 08:53:03', '2025-12-26 08:53:03', 0, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percent','fixed') DEFAULT 'percent',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_value` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `user_limit` int(11) DEFAULT 1,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `vouchers`
--

INSERT INTO `vouchers` (`id`, `code`, `name`, `description`, `discount_type`, `discount_value`, `min_order_value`, `max_discount`, `usage_limit`, `used_count`, `user_limit`, `start_date`, `end_date`, `is_active`, `created_at`) VALUES
(1, 'WELCOME10', 'Chào mừng khách mới', 'Giảm 10% cho đơn hàng đầu tiên', 'percent', 10.00, 100000.00, 50000.00, 1000, 0, 1, '2025-12-01 00:00:00', '2026-12-29 18:25:06', 1, '2025-12-29 11:55:06'),
(2, 'FLOWER20', 'Siêu Sale 20%', 'Giảm 20% tối đa 100k cho mọi đơn', 'percent', 20.00, 200000.00, 100000.00, 500, 0, 3, '2025-12-01 00:00:00', '2026-06-29 18:25:06', 1, '2025-12-29 11:55:06'),
(3, 'SALE50K', 'Giảm ngay 50K', 'Giảm trực tiếp 50.000đ cho đơn từ 300k', 'fixed', 50000.00, 300000.00, NULL, 200, 0, 2, '2025-12-01 00:00:00', '2026-03-29 18:25:06', 1, '2025-12-29 11:55:06'),
(4, 'FREESHIP', 'Miễn phí ship', 'Giảm 30.000đ phí vận chuyển', 'fixed', 30000.00, 150000.00, NULL, NULL, 0, 5, '2025-12-01 00:00:00', '2026-12-29 18:25:06', 1, '2025-12-29 11:55:06'),
(5, 'FREESHIP50', 'Freeship đơn lớn', 'Miễn phí ship cho đơn từ 500k', 'fixed', 50000.00, 500000.00, NULL, 100, 0, 3, '2025-12-01 00:00:00', '2026-06-29 18:25:06', 1, '2025-12-29 11:55:06'),
(6, 'HOT30', 'Deal Siêu Hot 30%', 'Giảm 30% tối đa 200k - Limited!', 'percent', 30.00, 400000.00, 200000.00, 50, 0, 1, '2025-12-01 00:00:00', '2026-01-29 18:25:06', 1, '2025-12-29 11:55:06'),
(7, 'MEGA40', 'Mega Sale 40%', 'Giảm 40% tối đa 300k - VIP Only', 'percent', 40.00, 600000.00, 300000.00, 20, 0, 1, '2025-12-01 00:00:00', '2026-01-12 18:25:06', 1, '2025-12-29 11:55:06'),
(8, 'NEW100K', 'Giảm 100K đơn lớn', 'Giảm 100.000đ cho đơn từ 700k', 'fixed', 100000.00, 700000.00, NULL, 100, 0, 2, '2025-12-01 00:00:00', '2026-02-28 18:25:06', 1, '2025-12-29 11:55:06'),
(9, 'GARDEN5', 'Từ Vườn Hoa Ảo', 'Mã từ game vườn hoa - Giảm 5%', 'percent', 5.00, 0.00, 30000.00, NULL, 0, 10, '2025-12-01 00:00:00', '2026-12-29 18:25:06', 1, '2025-12-29 11:55:06'),
(10, 'GARDEN10', 'Từ Vườn Hoa Ảo', 'Mã từ game vườn hoa - Giảm 10%', 'percent', 10.00, 100000.00, 50000.00, NULL, 0, 10, '2025-12-01 00:00:00', '2026-12-29 18:25:06', 1, '2025-12-29 11:55:06'),
(11, 'GARDEN15', 'Từ Vườn Hoa Ảo', 'Mã từ game vườn hoa - Giảm 15%', 'percent', 15.00, 200000.00, 80000.00, NULL, 0, 10, '2025-12-01 00:00:00', '2026-12-29 18:25:06', 1, '2025-12-29 11:55:06'),
(12, 'GARDEN25', 'Từ Vườn Hoa Ảo VIP', 'Mã từ game vườn hoa - Giảm 25%', 'percent', 25.00, 300000.00, 150000.00, NULL, 0, 5, '2025-12-01 00:00:00', '2026-12-29 18:25:06', 1, '2025-12-29 11:55:06'),
(13, 'LASTCHANCE', 'Cơ hội cuối', 'Giảm 15% - Sắp hết hạn!', 'percent', 15.00, 150000.00, 75000.00, 30, 0, 2, '2025-12-01 00:00:00', '2026-01-01 18:25:06', 1, '2025-12-29 11:55:06'),
(14, 'SUMMER25', 'Summer Sale', 'Giảm 25% mùa hè tươi mát', 'percent', 25.00, 250000.00, 125000.00, 100, 0, 2, '2025-12-01 00:00:00', '2026-03-29 18:25:06', 1, '2025-12-29 11:55:06'),
(15, 'BIRTHDAY', 'Happy Birthday', 'Giảm đặc biệt ngày sinh nhật', 'percent', 20.00, 0.00, 100000.00, NULL, 0, 1, '2025-12-01 00:00:00', '2026-12-29 18:25:06', 1, '2025-12-29 11:55:06');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `voucher_usage`
--

CREATE TABLE `voucher_usage` (
  `id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `popular_searches`
--
DROP TABLE IF EXISTS `popular_searches`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `popular_searches`  AS SELECT `search_history`.`search_query` AS `search_query`, count(0) AS `search_count`, max(`search_history`.`created_at`) AS `last_searched` FROM `search_history` WHERE `search_history`.`created_at` >= current_timestamp() - interval 30 day GROUP BY `search_history`.`search_query` ORDER BY count(0) DESC LIMIT 0, 10 ;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Chỉ mục cho bảng `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Chỉ mục cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversation_id` (`conversation_id`),
  ADD KEY `idx_sender_type` (`sender_type`),
  ADD KEY `idx_is_read` (`is_read`);

--
-- Chỉ mục cho bảng `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipient` (`recipient`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Chỉ mục cho bảng `flower_elements`
--
ALTER TABLE `flower_elements`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `game_scores`
--
ALTER TABLE `game_scores`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `garden_points`
--
ALTER TABLE `garden_points`
  ADD PRIMARY KEY (`user_id`);

--
-- Chỉ mục cho bảng `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_change_type` (`change_type`),
  ADD KEY `order_id` (`order_id`);

--
-- Chỉ mục cho bảng `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_payment_method` (`payment_method`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Chỉ mục cho bảng `order_reviews`
--
ALTER TABLE `order_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_transaction_id` (`transaction_id`),
  ADD KEY `idx_status` (`status`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_stock_status` (`stock_status`),
  ADD KEY `idx_is_available` (`is_available`);
ALTER TABLE `products` ADD FULLTEXT KEY `search_index` (`name`,`details`);

--
-- Chỉ mục cho bảng `product_compare`
--
ALTER TABLE `product_compare`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Chỉ mục cho bảng `product_views`
--
ALTER TABLE `product_views`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Chỉ mục cho bảng `search_history`
--
ALTER TABLE `search_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_search_query` (`search_query`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Chỉ mục cho bảng `stock_alerts`
--
ALTER TABLE `stock_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_is_resolved` (`is_resolved`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_user_type` (`user_type`);

--
-- Chỉ mục cho bảng `user_vouchers`
--
ALTER TABLE `user_vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_voucher_unique` (`user_id`,`voucher_id`);

--
-- Chỉ mục cho bảng `virtual_garden`
--
ALTER TABLE `virtual_garden`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_slot` (`user_id`,`slot_index`);

--
-- Chỉ mục cho bảng `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Chỉ mục cho bảng `voucher_usage`
--
ALTER TABLE `voucher_usage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_voucher` (`voucher_id`,`user_id`,`order_id`);

--
-- Chỉ mục cho bảng `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `chat_conversations`
--
ALTER TABLE `chat_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=252;

--
-- AUTO_INCREMENT cho bảng `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `coupon_usage`
--
ALTER TABLE `coupon_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `flower_elements`
--
ALTER TABLE `flower_elements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `game_scores`
--
ALTER TABLE `game_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `inventory_history`
--
ALTER TABLE `inventory_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `order_reviews`
--
ALTER TABLE `order_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `product_compare`
--
ALTER TABLE `product_compare`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `product_views`
--
ALTER TABLE `product_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `search_history`
--
ALTER TABLE `search_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `stock_alerts`
--
ALTER TABLE `stock_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT cho bảng `user_vouchers`
--
ALTER TABLE `user_vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `virtual_garden`
--
ALTER TABLE `virtual_garden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `voucher_usage`
--
ALTER TABLE `voucher_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD CONSTRAINT `inventory_history_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_history_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `order_reviews`
--
ALTER TABLE `order_reviews`
  ADD CONSTRAINT `order_reviews_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `stock_alerts`
--
ALTER TABLE `stock_alerts`
  ADD CONSTRAINT `stock_alerts_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
