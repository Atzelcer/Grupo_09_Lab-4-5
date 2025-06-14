-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-06-2025 a las 16:23:41
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bd_hotel`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fotografias`
--

CREATE TABLE `fotografias` (
  `id` int(11) NOT NULL,
  `habitacion_id` int(11) NOT NULL,
  `fotografia` varchar(255) NOT NULL,
  `orden` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fotografias`
--

INSERT INTO `fotografias` (`id`, `habitacion_id`, `fotografia`, `orden`) VALUES
(1, 1, 'habitacion1.avif', 1),
(2, 1, 'cama.avif', 2),
(3, 1, 'baño.avif', 3),
(4, 1, 'sala.jpg', 4),
(5, 5, 'habitacion5.avif', 1),
(6, 5, 'cama.avif', 2),
(7, 5, 'baño.avif', 3),
(8, 5, 'sala.jpg', 4),
(17, 7, 'habitacion7.jpg', 1),
(18, 7, 'cama.jpg', 2),
(19, 7, 'baño.jpg', 3),
(20, 7, 'sala.jpg', 4),
(21, 2, 'habitacion2.jpg', 1),
(22, 2, 'cama.jpg', 2),
(23, 2, 'baño.jpg', 3),
(24, 2, 'sala de estar.jpg', 4),
(25, 3, 'habitacion3.jpg', 1),
(26, 3, 'cama.jpg', 2),
(27, 3, 'baño.jpg', 3),
(28, 3, 'sala de estar.jpg', 4),
(29, 6, 'habitacion6.jpg', 1),
(30, 6, 'cama.jpg', 2),
(31, 6, 'baño.jpg', 3),
(32, 6, 'sala.jpg', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitaciones`
--

CREATE TABLE `habitaciones` (
  `id` int(11) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `piso` int(11) NOT NULL,
  `tipo_habitacion_id` int(11) NOT NULL,
  `estado` enum('disponible','ocupada','mantenimiento') DEFAULT 'disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitaciones`
--

INSERT INTO `habitaciones` (`id`, `numero`, `piso`, `tipo_habitacion_id`, `estado`) VALUES
(1, '101', 1, 1, 'disponible'),
(2, '102', 1, 2, 'disponible'),
(3, '201', 2, 3, 'disponible'),
(5, '23', 2, 1, 'ocupada'),
(6, '103', 1, 2, 'disponible'),
(7, '108', 3, 3, 'disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `habitacion_id` int(11) NOT NULL,
  `fecha_ingreso` date NOT NULL,
  `fecha_salida` date NOT NULL,
  `estado` enum('pendiente','confirmada','cancelada','completada') DEFAULT 'pendiente',
  `precio_total` decimal(8,2) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `usuario_id`, `habitacion_id`, `fecha_ingreso`, `fecha_salida`, `estado`, `precio_total`, `observaciones`, `created_at`) VALUES
(1, 1, 1, '2025-06-15', '2025-06-18', 'confirmada', 240.00, 'Reserva administrativa de prueba', '2025-06-10 11:48:18'),
(2, 2, 2, '2025-06-20', '2025-06-23', 'pendiente', 360.00, 'Primera reserva de Juan Pérez', '2025-06-10 11:48:18'),
(3, 1, 3, '2025-07-01', '2025-07-05', 'confirmada', 800.00, 'Reserva para evento especial', '2025-06-10 11:48:18'),
(4, 2, 5, '2025-06-10', '2025-06-12', 'cancelada', 160.00, 'Cancelada por cambio de planes', '2025-06-10 11:48:18'),
(5, 1, 2, '2025-06-01', '2025-06-03', 'completada', 240.00, 'Estancia completada satisfactoriamente', '2025-06-10 11:48:18'),
(6, 2, 3, '2025-08-15', '2025-08-20', 'pendiente', 1000.00, 'Reserva para vacaciones de verano', '2025-06-10 11:48:18'),
(7, 1, 5, '2025-07-10', '2025-07-12', 'confirmada', 160.00, 'Reserva de trabajo', '2025-06-10 11:48:18'),
(8, 2, 1, '2025-05-25', '2025-05-28', 'completada', 240.00, 'Cliente satisfecho con el servicio', '2025-06-10 11:48:18'),
(9, 2, 1, '2025-06-12', '2025-06-13', 'confirmada', 80.00, 'Muy God', '2025-06-11 03:17:41'),
(13, 2, 2, '2025-06-12', '2025-06-13', 'pendiente', 120.00, 'bueno god', '2025-06-11 09:11:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_habitacion`
--

CREATE TABLE `tipo_habitacion` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `superficie` decimal(5,2) NOT NULL,
  `nro_de_camas` int(11) NOT NULL,
  `precio_por_noche` decimal(8,2) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_habitacion`
--

INSERT INTO `tipo_habitacion` (`id`, `nombre`, `superficie`, `nro_de_camas`, `precio_por_noche`, `descripcion`) VALUES
(1, 'Simple', 20.00, 1, 80.00, 'Habitación individual con baño privado'),
(2, 'Doble', 30.00, 2, 120.00, 'Habitación para dos personas'),
(3, 'Suite', 50.00, 2, 200.00, 'Suite de lujo con sala');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `rol` enum('admin','usuario') DEFAULT 'usuario',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `correo`, `password`, `nombre`, `telefono`, `rol`, `created_at`) VALUES
(1, 'admin@hotel.com', '0192023a7bbd73250516f069df18b500', 'Administrador', '+59173463613', 'admin', '2025-06-10 10:57:58'),
(2, 'usuario@email.com', '6ad14ba9986e3615423dfca256d04e3f', 'Juan Pérez', '+59178863623', 'usuario', '2025-06-10 10:57:58'),
(3, 'vela123@gmail.com', 'a138a16980946363f6cd6e915241d764', 'Elmer Vela', '67652401', 'usuario', '2025-06-11 00:07:20'),
(4, 'causa123@gmail.com', '1bbf45e2940f2d24ca0949b58e77a457', 'pirlo', NULL, 'usuario', '2025-06-11 00:20:32');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `fotografias`
--
ALTER TABLE `fotografias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `habitacion_id` (`habitacion_id`);

--
-- Indices de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero` (`numero`),
  ADD KEY `tipo_habitacion_id` (`tipo_habitacion_id`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `habitacion_id` (`habitacion_id`);

--
-- Indices de la tabla `tipo_habitacion`
--
ALTER TABLE `tipo_habitacion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `fotografias`
--
ALTER TABLE `fotografias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `tipo_habitacion`
--
ALTER TABLE `tipo_habitacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `fotografias`
--
ALTER TABLE `fotografias`
  ADD CONSTRAINT `fotografias_ibfk_1` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD CONSTRAINT `habitaciones_ibfk_1` FOREIGN KEY (`tipo_habitacion_id`) REFERENCES `tipo_habitacion` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
