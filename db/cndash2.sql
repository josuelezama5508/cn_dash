-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-07-2025 a las 16:16:49
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cndash`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `block_dispo`
--

CREATE TABLE `block_dispo` (
  `id_d_b` int(11) NOT NULL,
  `clave_empresa` varchar(50) NOT NULL,
  `fecha_block` varchar(50) NOT NULL,
  `horarios` text NOT NULL,
  `motivo_cierre` text NOT NULL,
  `mensaje` text NOT NULL,
  `fk_usuario` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `product_code` varchar(16) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_email` varchar(100) NOT NULL,
  `client_phone` varchar(100) DEFAULT NULL,
  `hotel_name` varchar(100) DEFAULT NULL,
  `hotel_room_number` varchar(100) DEFAULT NULL,
  `booking_date` varchar(100) NOT NULL,
  `booking_time` varchar(100) NOT NULL,
  `channel_id` int(11) NOT NULL,
  `rep_id` int(11) DEFAULT NULL,
  `lang_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `active` enum('0','1') NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `channel`
--

CREATE TABLE `channel` (
  `id_channel` int(11) NOT NULL,
  `nombre` varchar(45) DEFAULT NULL,
  `tipo` enum('Propio','E-Comerce','Agencia-Convencional','Bahia','Calle','Agencia/Marina-Hotel','OTRO') DEFAULT 'Agencia-Convencional',
  `telefono` varchar(15) DEFAULT NULL,
  `activo` int(11) DEFAULT 1,
  `ResellerID` varchar(15) DEFAULT NULL,
  `subCanal` enum('directa','indirecta') DEFAULT 'indirecta',
  `datestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `channels`
--

CREATE TABLE `channels` (
  `channel_id` int(11) NOT NULL,
  `channel_name` text NOT NULL,
  `channel_type` enum('propio','e-commerce','agencia-convencional','bahia','calle','agencia/marina-hotel','otro') NOT NULL DEFAULT 'agencia-convencional',
  `channel_phone` varchar(15) DEFAULT 'N/A',
  `sub_channel` enum('directa','indirecta') NOT NULL DEFAULT 'indirecta',
  `active` enum('0','1') NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `companies`
--

CREATE TABLE `companies` (
  `company_id` int(11) NOT NULL,
  `company_name` text NOT NULL,
  `primary_color` varchar(100) NOT NULL DEFAULT '#345A98',
  `secondary_color` varchar(100) NOT NULL DEFAULT '#000',
  `company_code` varchar(50) NOT NULL,
  `disponibilidad_api` enum('0','1') NOT NULL DEFAULT '1',
  `productos` text DEFAULT NULL,
  `dias_dispo` text NOT NULL DEFAULT 'Mon|Tue|Wed|Thu|Fri|Sat|Sun',
  `transportation` int(11) NOT NULL DEFAULT 0,
  `company_logo` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `social_networks` text DEFAULT NULL,
  `phones` text DEFAULT NULL,
  `active` enum('0','1') NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `companies`
--

INSERT INTO `companies` (`company_id`, `company_name`, `primary_color`, `secondary_color`, `company_code`, `disponibilidad_api`, `productos`, `dias_dispo`, `transportation`, `company_logo`, `website`, `social_networks`, `phones`, `active`, `timestamp`) VALUES
(17, 'Empresa de Prueba', '#345a98', '#000', 'UNBGD', '1', '[{\"codigoproducto\":\"PRODUCTODEPRUEBA\",\"bd\":\"products\"},{\"codigoproducto\":\"SNORKELNIGHT\",\"bd\":\"products\"}]', 'Mon|Tue|Wed|Thu|Fri|Sat|Sun', 0, 'http://localhost/cn_dash/uploads/images/UNBGD.jpg', NULL, NULL, NULL, '1', '2025-07-08 16:41:27'),
(18, 'Parasail', '#d804e7', '#000', 'MFMFT', '1', '[{\"codigoproducto\":\"PARASAILCANCUN\",\"bd\":\"products\"}]', 'Mon|Tue|Wed|Thu|Fri|Sat|Sun', 0, 'http://localhost/cn_dash/uploads/images/MFMFT.png', NULL, NULL, NULL, '1', '2025-07-10 14:23:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `currency_codes`
--

CREATE TABLE `currency_codes` (
  `currency_id` int(11) NOT NULL,
  `denomination` varchar(3) NOT NULL,
  `active` enum('0','1') NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `currency_codes`
--

INSERT INTO `currency_codes` (`currency_id`, `denomination`, `active`, `timestamp`) VALUES
(1, 'USD', '1', '2025-05-29 17:12:05'),
(2, 'MXN', '1', '2025-05-30 15:58:32'),
(3, 'GBP', '0', '2025-05-30 15:58:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `disponibilidad`
--

CREATE TABLE `disponibilidad` (
  `id_dispo` int(11) NOT NULL,
  `clave_empresa` varchar(50) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `horario` varchar(20) NOT NULL,
  `h_match` text NOT NULL,
  `cupo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `disponibilidad`
--

INSERT INTO `disponibilidad` (`id_dispo`, `clave_empresa`, `status`, `horario`, `h_match`, `cupo`) VALUES
(1, 'PYRGA', 0, '09:00 AM', '08:30 AM,09:00 AM', 20),
(2, 'PYRGA', 0, '09:00 AM', '08:30 AM,09:00 AM', 1),
(3, 'PYRGA', 0, '07:00 AM', '6:30 AM', 35),
(4, 'PYRGA', 0, '12:00 AM', '', 1),
(5, 'PYRGA', 0, '12:30 PM', '12:00 PM', 1),
(6, 'PYRGA', 0, '12:30 PM', '12:00 PM,12:30 PM', 1),
(7, 'PYRGA', 1, '11:00 AM', '', 20),
(8, 'PYRGA', 1, '01:00 PM', '12:30 PM,1:00 PM', 20),
(9, 'EXUCH', 0, '11:00 AM', '10:30 AM,11:00 AM', 20),
(10, 'MFMFT', 1, '07:00 AM', '', 10),
(11, 'MFMFT', 1, '09:00 AM', '', 10),
(12, 'MFMFT', 1, '02:30 PM', '', 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id` int(11) NOT NULL,
  `nombre` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `primario` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `secundario` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `clave_empresa` varchar(50) DEFAULT NULL,
  `disponibilidad_api` int(11) NOT NULL DEFAULT 0,
  `productos` longtext NOT NULL,
  `p_leader` varchar(150) NOT NULL DEFAULT '',
  `dias_dispo` varchar(100) DEFAULT 'ALL',
  `transporte` tinyint(1) NOT NULL DEFAULT 1,
  `imagen` varchar(100) NOT NULL,
  `site` varchar(100) NOT NULL,
  `social_networks` text DEFAULT NULL,
  `phones` text DEFAULT NULL,
  `statusD` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `history`
--

CREATE TABLE `history` (
  `history_id` int(11) NOT NULL,
  `module` varchar(150) NOT NULL,
  `row_id` int(11) NOT NULL,
  `action` varchar(150) NOT NULL,
  `details` varchar(150) NOT NULL,
  `user_id` int(11) NOT NULL,
  `old_data` text NOT NULL,
  `new_data` text NOT NULL,
  `active` enum('0','1','','') NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `history`
--

INSERT INTO `history` (`history_id`, `module`, `row_id`, `action`, `details`, `user_id`, `old_data`, `new_data`, `active`, `timestamp`) VALUES
(1, 'products', 4, 'create', 'Nuevo producto creado.', 1, '[]', '{\"product_id\":\"4\",\"product_name\":\"Producto de atv\",\"company_id\":\"1\",\"price_wetsuit\":\"3\",\"price_adult\":\"3\",\"price_child\":\"3\",\"price_rider\":\"3\",\"price_photo\":\"3\",\"product_code\":\"PRUEBAPRODUCTO\",\"description\":\"\",\"currency_id\":\"1\",\"productdefine\":\"tour\",\"booklink\":null,\"show_dash\":\"0\",\"show_web\":\"0\",\"lang_id\":\"2\",\"productforpromo\":\"0\",\"location_description\":null,\"location_url\":null,\"location_image\":null,\"harbor\":null,\"active\":\"1\",\"timestamp\":\"2025-07-02 14:30:43\",\"id\":\"4\"}', '1', '2025-07-02 19:30:43'),
(2, 'products', 7, 'create', 'Nuevo producto creado.', 1, '[]', '{\"product_id\":\"7\",\"product_name\":\"Producto de atv\",\"company_id\":\"1\",\"price_wetsuit\":\"3\",\"price_adult\":\"3\",\"price_child\":\"3\",\"price_rider\":\"3\",\"price_photo\":\"3\",\"product_code\":\"PRUEBAPRODUCTO\",\"description\":\"\",\"currency_id\":\"1\",\"productdefine\":\"tour\",\"booklink\":null,\"show_dash\":\"0\",\"show_web\":\"0\",\"lang_id\":\"2\",\"productforpromo\":\"0\",\"location_description\":null,\"location_url\":null,\"location_image\":null,\"harbor\":null,\"active\":\"1\",\"timestamp\":\"2025-07-02 14:30:43\",\"id\":\"7\"}', '1', '2025-07-02 19:30:43'),
(3, 'products', 5, 'create', 'Nuevo producto creado.', 1, '[]', '{\"product_id\":\"5\",\"product_name\":\"Producto de atv\",\"company_id\":\"1\",\"price_wetsuit\":\"3\",\"price_adult\":\"3\",\"price_child\":\"3\",\"price_rider\":\"3\",\"price_photo\":\"3\",\"product_code\":\"PRUEBAPRODUCTO\",\"description\":\"\",\"currency_id\":\"1\",\"productdefine\":\"tour\",\"booklink\":null,\"show_dash\":\"0\",\"show_web\":\"0\",\"lang_id\":\"2\",\"productforpromo\":\"0\",\"location_description\":null,\"location_url\":null,\"location_image\":null,\"harbor\":null,\"active\":\"1\",\"timestamp\":\"2025-07-02 14:30:43\",\"id\":\"5\"}', '1', '2025-07-02 19:30:43'),
(4, 'products', 6, 'create', 'Nuevo producto creado.', 1, '[]', '{\"product_id\":\"6\",\"product_name\":\"Producto de atv\",\"company_id\":\"1\",\"price_wetsuit\":\"3\",\"price_adult\":\"3\",\"price_child\":\"3\",\"price_rider\":\"3\",\"price_photo\":\"3\",\"product_code\":\"PRUEBAPRODUCTO\",\"description\":\"\",\"currency_id\":\"1\",\"productdefine\":\"tour\",\"booklink\":null,\"show_dash\":\"0\",\"show_web\":\"0\",\"lang_id\":\"2\",\"productforpromo\":\"0\",\"location_description\":null,\"location_url\":null,\"location_image\":null,\"harbor\":null,\"active\":\"1\",\"timestamp\":\"2025-07-02 14:30:43\",\"id\":\"6\"}', '1', '2025-07-02 19:30:43'),
(5, 'products', 8, 'create', 'Nuevo producto creado.', 1, '[]', '{\"product_id\":\"8\",\"product_name\":\"Producto cuatrimoto\",\"company_id\":\"1\",\"price_wetsuit\":\"3\",\"price_adult\":\"3\",\"price_child\":\"3\",\"price_rider\":\"3\",\"price_photo\":\"3\",\"product_code\":\"PRUEBAPRODUCTO\",\"description\":\"\",\"currency_id\":\"1\",\"productdefine\":\"tour\",\"booklink\":null,\"show_dash\":\"0\",\"show_web\":\"0\",\"lang_id\":\"2\",\"productforpromo\":\"0\",\"location_description\":null,\"location_url\":null,\"location_image\":null,\"harbor\":null,\"active\":\"1\",\"timestamp\":\"2025-07-02 16:00:19\",\"id\":\"8\"}', '1', '2025-07-02 21:00:19'),
(6, 'tags', 1, 'create', 'Nuevo tag creado.', 1, '[]', '{\"tag_id\":\"1\",\"tag_index\":\"Adult\",\"tag_name\":\"{\\\"en\\\":\\\"Adult\\\",\\\"es\\\":\\\"Adulto\\\"}\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:52:51\",\"id\":\"1\"}', '1', '2025-07-02 22:52:51'),
(7, 'tags', 2, 'create', 'Nuevo tag creado.', 1, '[]', '{\"tag_id\":\"2\",\"tag_index\":\"Child\",\"tag_name\":\"{\\\"en\\\":\\\"Kids (6-11) years\\\",\\\"es\\\":\\\"Ni\\\\u00f1os de (6-11) a\\\\u00f1os\\\"}\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:53:10\",\"id\":\"2\"}', '1', '2025-07-02 22:53:10'),
(8, 'tags', 3, 'create', 'Nuevo tag creado.', 1, '[]', '{\"tag_id\":\"3\",\"tag_index\":\"1-2\",\"tag_name\":\"{\\\"en\\\":\\\"Full HD Photo and Video Package For 1-2 People\\\",\\\"es\\\":\\\"Paquete de Foto y Video Full HD Para 1-2 Personas\\\"}\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:54:07\",\"id\":\"3\"}', '1', '2025-07-02 22:54:07'),
(9, 'item_product', 1, 'create', 'Nuevo tag creado.', 1, '[]', '{\"itemproduct_id\":\"1\",\"tag_id\":\"1\",\"price_id\":\"1\",\"productcode\":\"PRODUCTODEPRUEBA\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:54:17\",\"id\":\"1\"}', '1', '2025-07-02 22:54:17'),
(10, 'item_product', 2, 'create', 'Nuevo tag creado.', 1, '[]', '{\"itemproduct_id\":\"2\",\"tag_id\":\"2\",\"price_id\":\"1\",\"productcode\":\"PRODUCTODEPRUEBA\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:54:17\",\"id\":\"2\"}', '1', '2025-07-02 22:54:17'),
(11, 'item_product', 3, 'create', 'Nuevo tag creado.', 1, '[]', '{\"itemproduct_id\":\"3\",\"tag_id\":\"3\",\"price_id\":\"1\",\"productcode\":\"PRODUCTODEPRUEBA\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:54:17\",\"id\":\"3\"}', '1', '2025-07-02 22:54:17'),
(12, 'item_product', 3, 'update', 'Tipo del tag actualizado.', 1, '{\"itemproduct_id\":\"3\",\"tag_id\":\"3\",\"price_id\":\"1\",\"productcode\":\"PRODUCTODEPRUEBA\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:54:17\",\"id\":\"3\"}', '{\"itemproduct_id\":\"3\",\"tag_id\":\"3\",\"price_id\":\"1\",\"productcode\":\"PRODUCTODEPRUEBA\",\"producttag_type\":\"addon\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:54:17\",\"id\":\"3\"}', '1', '2025-07-02 22:54:21'),
(13, 'item_product', 3, 'update', 'Clase del tag actualizado.', 1, '{\"itemproduct_id\":\"3\",\"tag_id\":\"3\",\"price_id\":\"1\",\"productcode\":\"PRODUCTODEPRUEBA\",\"producttag_type\":\"addon\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:54:17\",\"id\":\"3\"}', '{\"itemproduct_id\":\"3\",\"tag_id\":\"3\",\"price_id\":\"1\",\"productcode\":\"PRODUCTODEPRUEBA\",\"producttag_type\":\"addon\",\"producttag_class\":\"checkbox\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:54:17\",\"id\":\"3\"}', '1', '2025-07-02 22:54:24'),
(14, 'item_product', 1, 'update', 'Precio del tag actualizado.', 1, '{\"itemproduct_id\":\"1\",\"tag_id\":\"1\",\"price_id\":\"1\",\"productcode\":\"PRODUCTODEPRUEBA\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:54:17\",\"id\":\"1\"}', '{\"itemproduct_id\":\"1\",\"tag_id\":\"1\",\"price_id\":\"3\",\"productcode\":\"PRODUCTODEPRUEBA\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:54:17\",\"id\":\"1\"}', '1', '2025-07-02 22:54:31'),
(15, 'item_product', 2, 'update', 'Precio del tag actualizado.', 1, '{\"itemproduct_id\":\"2\",\"tag_id\":\"2\",\"price_id\":\"1\",\"productcode\":\"PRODUCTODEPRUEBA\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:54:17\",\"id\":\"2\"}', '{\"itemproduct_id\":\"2\",\"tag_id\":\"2\",\"price_id\":\"3\",\"productcode\":\"PRODUCTODEPRUEBA\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:54:17\",\"id\":\"2\"}', '1', '2025-07-02 22:54:32'),
(16, 'item_product', 3, 'update', 'Precio del tag actualizado.', 1, '{\"itemproduct_id\":\"3\",\"tag_id\":\"3\",\"price_id\":\"1\",\"productcode\":\"PRODUCTODEPRUEBA\",\"producttag_type\":\"addon\",\"producttag_class\":\"checkbox\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:54:17\",\"id\":\"3\"}', '{\"itemproduct_id\":\"3\",\"tag_id\":\"3\",\"price_id\":\"2\",\"productcode\":\"PRODUCTODEPRUEBA\",\"producttag_type\":\"addon\",\"producttag_class\":\"checkbox\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-02 17:54:17\",\"id\":\"3\"}', '1', '2025-07-02 22:54:33'),
(17, 'item_product', 4, 'create', 'Nuevo tag creado.', 1, '[]', '{\"itemproduct_id\":\"4\",\"tag_id\":\"1\",\"price_id\":\"1\",\"productcode\":\"HQAWEEERZXCGHKXO\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-07 09:15:39\",\"id\":\"4\"}', '1', '2025-07-07 14:15:39'),
(18, 'item_product', 5, 'create', 'Nuevo tag creado.', 1, '[]', '{\"itemproduct_id\":\"5\",\"tag_id\":\"2\",\"price_id\":\"1\",\"productcode\":\"HQAWEEERZXCGHKXO\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-07 09:15:39\",\"id\":\"5\"}', '1', '2025-07-07 14:15:39'),
(19, 'item_product', 6, 'create', 'Nuevo tag creado.', 1, '[]', '{\"itemproduct_id\":\"6\",\"tag_id\":\"1\",\"price_id\":\"1\",\"productcode\":\"PARASAILCANCUN\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-10 09:31:40\",\"id\":\"6\"}', '1', '2025-07-10 14:31:40'),
(20, 'item_product', 7, 'create', 'Nuevo tag creado.', 1, '[]', '{\"itemproduct_id\":\"7\",\"tag_id\":\"2\",\"price_id\":\"1\",\"productcode\":\"PARASAILCANCUN\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-10 09:31:40\",\"id\":\"7\"}', '1', '2025-07-10 14:31:40'),
(21, 'item_product', 6, 'update', 'Precio del tag actualizado.', 1, '{\"itemproduct_id\":\"6\",\"tag_id\":\"1\",\"price_id\":\"1\",\"productcode\":\"PARASAILCANCUN\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-10 09:31:40\",\"id\":\"6\"}', '{\"itemproduct_id\":\"6\",\"tag_id\":\"1\",\"price_id\":\"2\",\"productcode\":\"PARASAILCANCUN\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-10 09:31:40\",\"id\":\"6\"}', '1', '2025-07-10 14:32:16'),
(22, 'item_product', 7, 'update', 'Precio del tag actualizado.', 1, '{\"itemproduct_id\":\"7\",\"tag_id\":\"2\",\"price_id\":\"1\",\"productcode\":\"PARASAILCANCUN\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-10 09:31:40\",\"id\":\"7\"}', '{\"itemproduct_id\":\"7\",\"tag_id\":\"2\",\"price_id\":\"2\",\"productcode\":\"PARASAILCANCUN\",\"producttag_type\":\"tour\",\"producttag_class\":\"number\",\"groupby\":\"\",\"value_min\":\"0\",\"value_max\":\"50\",\"config_for\":\"0\",\"position\":\"0\",\"active\":\"1\",\"timestamp\":\"2025-07-10 09:31:40\",\"id\":\"7\"}', '1', '2025-07-10 14:32:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item_product`
--

CREATE TABLE `item_product` (
  `itemproduct_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `price_id` int(11) NOT NULL,
  `productcode` varchar(16) NOT NULL,
  `producttag_type` enum('tour','addon','extraquestion','store') NOT NULL DEFAULT 'tour',
  `producttag_class` enum('number','checkbox') NOT NULL DEFAULT 'number',
  `groupby` varchar(150) NOT NULL,
  `value_min` int(11) NOT NULL DEFAULT 0,
  `value_max` int(11) NOT NULL DEFAULT 50,
  `config_for` int(11) NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  `active` enum('0','1') NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `item_product`
--

INSERT INTO `item_product` (`itemproduct_id`, `tag_id`, `price_id`, `productcode`, `producttag_type`, `producttag_class`, `groupby`, `value_min`, `value_max`, `config_for`, `position`, `active`, `timestamp`) VALUES
(1, 1, 3, 'PRODUCTODEPRUEBA', 'tour', 'number', '', 0, 50, 0, 0, '1', '2025-07-02 22:54:17'),
(2, 2, 3, 'PRODUCTODEPRUEBA', 'tour', 'number', '', 0, 50, 0, 0, '1', '2025-07-02 22:54:17'),
(3, 3, 2, 'PRODUCTODEPRUEBA', 'addon', 'checkbox', '', 0, 50, 0, 0, '1', '2025-07-02 22:54:17'),
(4, 1, 1, 'HQAWEEERZXCGHKXO', 'tour', 'number', '', 0, 50, 0, 0, '1', '2025-07-07 14:15:39'),
(5, 2, 1, 'HQAWEEERZXCGHKXO', 'tour', 'number', '', 0, 50, 0, 0, '1', '2025-07-07 14:15:39'),
(6, 1, 2, 'PARASAILCANCUN', 'tour', 'number', '', 0, 50, 0, 0, '1', '2025-07-10 14:31:40'),
(7, 2, 2, 'PARASAILCANCUN', 'tour', 'number', '', 0, 50, 0, 0, '1', '2025-07-10 14:31:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `language_codes`
--

CREATE TABLE `language_codes` (
  `lang_id` int(11) NOT NULL,
  `code` varchar(2) NOT NULL,
  `language` varchar(11) NOT NULL,
  `active` enum('0','1') NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `language_codes`
--

INSERT INTO `language_codes` (`lang_id`, `code`, `language`, `active`, `timestamp`) VALUES
(1, 'en', 'Inglés', '1', '2025-05-29 17:11:10'),
(2, 'es', 'Español', '1', '2025-05-30 16:24:32'),
(3, 'pt', 'Portugés', '1', '2025-05-30 16:28:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prices`
--

CREATE TABLE `prices` (
  `price_id` int(11) NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `active` enum('0','1') NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prices`
--

INSERT INTO `prices` (`price_id`, `price`, `active`, `timestamp`) VALUES
(1, 0.00, '1', '2025-05-29 18:01:22'),
(2, 75.00, '1', '2025-05-30 20:36:35'),
(3, 139.00, '1', '2025-06-03 17:48:48'),
(4, 510.00, '0', '2025-06-05 18:57:38'),
(5, 220.00, '0', '2025-06-05 18:57:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` text NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT 1,
  `price_wetsuit` int(11) DEFAULT 1,
  `price_adult` int(11) NOT NULL DEFAULT 1,
  `price_child` int(11) DEFAULT 1,
  `price_rider` int(11) DEFAULT 1,
  `price_photo` int(11) DEFAULT 1,
  `product_code` varchar(16) NOT NULL,
  `description` text DEFAULT NULL,
  `currency_id` int(11) NOT NULL DEFAULT 1,
  `productdefine` enum('tour','store','test','season') NOT NULL DEFAULT 'tour',
  `booklink` varchar(150) DEFAULT NULL,
  `show_dash` enum('0','1') NOT NULL DEFAULT '0',
  `show_web` enum('0','1') NOT NULL DEFAULT '0',
  `lang_id` int(11) NOT NULL,
  `productforpromo` enum('0','1') NOT NULL DEFAULT '0',
  `location_description` text DEFAULT NULL,
  `location_url` text DEFAULT NULL,
  `location_image` text DEFAULT NULL,
  `harbor` text DEFAULT NULL,
  `active` enum('0','1') NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `company_id`, `price_wetsuit`, `price_adult`, `price_child`, `price_rider`, `price_photo`, `product_code`, `description`, `currency_id`, `productdefine`, `booklink`, `show_dash`, `show_web`, `lang_id`, `productforpromo`, `location_description`, `location_url`, `location_image`, `harbor`, `active`, `timestamp`) VALUES
(1, 'New test product', 1, 3, 3, 3, 3, 3, 'PRODUCTODEPRUEBA', '', 1, 'tour', NULL, '1', '0', 1, '0', NULL, NULL, NULL, NULL, '1', '2025-07-07 20:32:56'),
(2, 'Producto cuatrimoto', 1, 1, 1, 1, 1, 1, 'SNORKELNIGHT', '', 1, 'tour', NULL, '1', '0', 1, '0', NULL, NULL, NULL, NULL, '1', '2025-07-09 14:56:12'),
(3, 'Parasail Cancun', 1, 1, 3, 1, 1, 1, 'PARASAILCANCUN', '', 1, 'tour', NULL, '1', '0', 1, '0', NULL, NULL, NULL, NULL, '1', '2025-07-10 14:27:24'),
(4, 'Parasail Cancun Es', 1, 1, 3, 1, 1, 1, 'PARASAILCANCUN', '', 1, 'tour', NULL, '0', '0', 2, '0', NULL, NULL, NULL, NULL, '1', '2025-07-10 14:43:07'),
(5, 'Parasail Cancun', 1, 1, 3, 1, 1, 1, 'PARASAILCANCUN', '', 1, 'tour', NULL, '0', '0', 1, '0', NULL, NULL, NULL, NULL, '1', '2025-07-10 14:43:26'),
(6, 'Producto atv', 1, 3, 3, 3, 3, 3, 'PRODUCTATV', '', 1, 'tour', NULL, '0', '0', 1, '0', NULL, NULL, NULL, NULL, '1', '2025-07-10 14:55:08'),
(7, 'Producto cuatrimotos', 1, 3, 3, 3, 3, 3, 'PRODUCTATV', '', 1, 'tour', NULL, '0', '0', 2, '0', NULL, NULL, NULL, NULL, '1', '2025-07-10 14:55:08'),
(8, 'New test product', 1, 1, 1, 1, 1, 1, 'SWIMANDFLY', '', 1, 'tour', NULL, '0', '0', 1, '0', NULL, NULL, NULL, NULL, '1', '2025-07-11 14:25:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promo_code`
--

CREATE TABLE `promo_code` (
  `code_id` int(11) NOT NULL,
  `code_begdate` date NOT NULL,
  `code_expdate` date NOT NULL,
  `promocode` varchar(45) NOT NULL,
  `number` int(11) NOT NULL,
  `product_id` int(11) NOT NULL DEFAULT 9999,
  `discount` int(11) NOT NULL,
  `active` enum('0','1') NOT NULL DEFAULT '1',
  `campo2` varchar(45) DEFAULT NULL,
  `numpersonas` int(11) DEFAULT NULL,
  `campaign` varchar(230) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rep`
--

CREATE TABLE `rep` (
  `idrep` int(11) NOT NULL,
  `nombre` varchar(45) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `email` varchar(60) DEFAULT NULL,
  `idcanal` int(11) DEFAULT NULL,
  `comision` int(11) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reps`
--

CREATE TABLE `reps` (
  `rep_id` int(11) NOT NULL,
  `channel_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tags`
--

CREATE TABLE `tags` (
  `tag_id` int(11) NOT NULL,
  `tag_index` varchar(150) NOT NULL,
  `tag_name` text NOT NULL,
  `active` enum('0','1') NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tags`
--

INSERT INTO `tags` (`tag_id`, `tag_index`, `tag_name`, `active`, `timestamp`) VALUES
(1, 'Adult', '{\"en\":\"Adult\",\"es\":\"Adulto\"}', '1', '2025-07-02 22:52:51'),
(2, 'Child', '{\"en\":\"Kids (6-11) years\",\"es\":\"Ni\\u00f1os de (6-11) a\\u00f1os\"}', '1', '2025-07-02 22:53:10'),
(3, '1-2', '{\"en\":\"Full HD Photo and Video Package For 1-2 People\",\"es\":\"Paquete de Foto y Video Full HD Para 1-2 Personas\"}', '1', '2025-07-02 22:54:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(250) NOT NULL,
  `user_lastname` varchar(250) DEFAULT NULL,
  `email` varchar(250) NOT NULL,
  `username` varchar(250) NOT NULL,
  `password` varchar(250) NOT NULL,
  `level` int(11) DEFAULT NULL,
  `active` enum('0','1') NOT NULL DEFAULT '1',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `user_lastname`, `email`, `username`, `password`, `level`, `active`, `timestamp`) VALUES
(1, 'admin', 'admin', 'sistemas@admin.com', 'adminuno', 'admin', NULL, '1', '2025-06-04 19:22:56');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `block_dispo`
--
ALTER TABLE `block_dispo`
  ADD PRIMARY KEY (`id_d_b`);

--
-- Indices de la tabla `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `channel_id` (`channel_id`),
  ADD KEY `rep_id` (`rep_id`),
  ADD KEY `lang_id` (`lang_id`);

--
-- Indices de la tabla `channel`
--
ALTER TABLE `channel`
  ADD PRIMARY KEY (`id_channel`),
  ADD KEY `nombre` (`nombre`,`ResellerID`);

--
-- Indices de la tabla `channels`
--
ALTER TABLE `channels`
  ADD PRIMARY KEY (`channel_id`);

--
-- Indices de la tabla `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`company_id`);

--
-- Indices de la tabla `currency_codes`
--
ALTER TABLE `currency_codes`
  ADD PRIMARY KEY (`currency_id`);

--
-- Indices de la tabla `disponibilidad`
--
ALTER TABLE `disponibilidad`
  ADD PRIMARY KEY (`id_dispo`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`history_id`);

--
-- Indices de la tabla `item_product`
--
ALTER TABLE `item_product`
  ADD PRIMARY KEY (`itemproduct_id`);

--
-- Indices de la tabla `language_codes`
--
ALTER TABLE `language_codes`
  ADD PRIMARY KEY (`lang_id`);

--
-- Indices de la tabla `prices`
--
ALTER TABLE `prices`
  ADD PRIMARY KEY (`price_id`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indices de la tabla `promo_code`
--
ALTER TABLE `promo_code`
  ADD PRIMARY KEY (`code_id`);

--
-- Indices de la tabla `rep`
--
ALTER TABLE `rep`
  ADD PRIMARY KEY (`idrep`);

--
-- Indices de la tabla `reps`
--
ALTER TABLE `reps`
  ADD PRIMARY KEY (`rep_id`);

--
-- Indices de la tabla `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`tag_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `block_dispo`
--
ALTER TABLE `block_dispo`
  MODIFY `id_d_b` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `channel`
--
ALTER TABLE `channel`
  MODIFY `id_channel` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `channels`
--
ALTER TABLE `channels`
  MODIFY `channel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `currency_codes`
--
ALTER TABLE `currency_codes`
  MODIFY `currency_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `disponibilidad`
--
ALTER TABLE `disponibilidad`
  MODIFY `id_dispo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `history`
--
ALTER TABLE `history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `item_product`
--
ALTER TABLE `item_product`
  MODIFY `itemproduct_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `language_codes`
--
ALTER TABLE `language_codes`
  MODIFY `lang_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `prices`
--
ALTER TABLE `prices`
  MODIFY `price_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `promo_code`
--
ALTER TABLE `promo_code`
  MODIFY `code_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rep`
--
ALTER TABLE `rep`
  MODIFY `idrep` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reps`
--
ALTER TABLE `reps`
  MODIFY `rep_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `tags`
--
ALTER TABLE `tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`channel_id`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`rep_id`) REFERENCES `reps` (`rep_id`),
  ADD CONSTRAINT `bookings_ibfk_4` FOREIGN KEY (`lang_id`) REFERENCES `language_codes` (`lang_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
