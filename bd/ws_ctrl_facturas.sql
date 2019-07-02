-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 02-07-2019 a las 14:35:03
-- Versión del servidor: 5.7.26-0ubuntu0.18.04.1
-- Versión de PHP: 7.1.25-1+ubuntu18.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `kalea2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ws_ctrl_facturas`
--

CREATE TABLE `ws_ctrl_facturas` (
  `id` int(60) NOT NULL,
  `no_transa_mov` varchar(20) COLLATE utf8_bin NOT NULL,
  `tipo_pago` int(2) NOT NULL COMMENT '1- Contado 2-Cuotas',
  `total` varchar(20) COLLATE utf8_bin NOT NULL,
  `n_cuotas` int(2) NOT NULL,
  `valor_cuotas` varchar(20) COLLATE utf8_bin NOT NULL,
  `n_referencia` varchar(20) COLLATE utf8_bin NOT NULL,
  `n_autorizacion` varchar(20) COLLATE utf8_bin NOT NULL,
  `nombre` varchar(30) COLLATE utf8_bin NOT NULL,
  `email` varchar(50) COLLATE utf8_bin NOT NULL,
  `envio_factura` int(1) NOT NULL COMMENT '0- Por Enviar 1-Enviado 2-Error al Enviar o al Generar',
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ws_ctrl_facturas`
--
ALTER TABLE `ws_ctrl_facturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_transa_mov` (`no_transa_mov`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ws_ctrl_facturas`
--
ALTER TABLE `ws_ctrl_facturas`
  MODIFY `id` int(60) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
