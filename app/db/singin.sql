-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: db
-- Tiempo de generación: 17-11-2022 a las 12:48:09
-- Versión del servidor: 8.0.29
-- Versión de PHP: 8.0.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `singin`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `employee`
--

CREATE TABLE `employee` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `dni` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `pass` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `employee`
--

INSERT INTO `employee` (`id`, `name`, `dni`, `email`, `pass`) VALUES
(1, 'NestorPons', '20249503G', 'nestorpons@gmail.com', 'c9aa66d423f6115fff0079cae118f5b9476966275844646e07db93a12982adc0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sings`
--

CREATE TABLE `sings` (
  `id` int NOT NULL,
  `id_employee` int NOT NULL,
  `date_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hash` varchar(100) NOT NULL,
  `action` tinyint(1) NOT NULL COMMENT '1 = singin | 0 = singout'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sings`
--
ALTER TABLE `sings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ID_EMP_TIM` (`id_employee`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `employee`
--
ALTER TABLE `employee`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `sings`
--
ALTER TABLE `sings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `sings`
--
ALTER TABLE `sings`
  ADD CONSTRAINT `ID_EMP_TIM` FOREIGN KEY (`id_employee`) REFERENCES `employee` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
