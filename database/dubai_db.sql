-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-09-2026 a las 23:06:41
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
-- Base de datos: `dubai_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `id` int(10) UNSIGNED NOT NULL,
  `venta_id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_venta`
--

INSERT INTO `detalle_venta` (`id`, `venta_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 9, 1, 280.00, 280.00),
(2, 1, 12, 2, 6.00, 12.00),
(3, 1, 14, 1, 15.00, 15.00),
(4, 2, 1, 6, 12.00, 72.00),
(5, 2, 4, 2, 25.00, 50.00),
(6, 3, 11, 1, 650.00, 650.00),
(7, 3, 12, 4, 6.00, 24.00),
(8, 4, 2, 5, 10.00, 50.00),
(9, 4, 3, 2, 14.00, 28.00),
(10, 5, 10, 1, 190.00, 190.00),
(11, 5, 13, 2, 5.00, 10.00),
(12, 5, 15, 1, 18.00, 18.00),
(13, 6, 5, 3, 22.00, 66.00),
(14, 6, 6, 2, 24.00, 48.00),
(15, 7, 9, 1, 280.00, 280.00),
(16, 7, 4, 2, 25.00, 50.00),
(17, 7, 14, 2, 15.00, 30.00),
(18, 8, 1, 4, 12.00, 48.00),
(19, 8, 7, 3, 28.00, 84.00),
(20, 8, 12, 2, 6.00, 12.00),
(21, 9, 3, 2, 14.00, 28.00),
(22, 9, 4, 2, 25.00, 50.00),
(23, 10, 1, 3, 12.00, 36.00),
(24, 10, 5, 2, 22.00, 44.00),
(25, 11, 10, 1, 190.00, 190.00),
(26, 11, 12, 3, 6.00, 18.00),
(27, 11, 15, 1, 18.00, 18.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entradas`
--

CREATE TABLE `entradas` (
  `id` int(10) UNSIGNED NOT NULL,
  `evento_id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `tipo` enum('general','vip') NOT NULL DEFAULT 'general',
  `cliente_nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `estado` enum('vendida','usada','anulada') NOT NULL DEFAULT 'vendida',
  `fecha_venta` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `entradas`
--

INSERT INTO `entradas` (`id`, `evento_id`, `usuario_id`, `tipo`, `cliente_nombre`, `precio`, `codigo`, `estado`, `fecha_venta`) VALUES
(1, 1, 2, 'general', 'Carlos Quispe', 40.00, 'DUB-0001', 'usada', '2026-09-23 16:05:51'),
(2, 1, 2, 'vip', 'Lucía Mendoza', 80.00, 'DUB-0002', 'usada', '2026-09-23 16:05:51'),
(3, 1, 2, 'general', 'Jorge Huamán', 40.00, 'DUB-0003', 'anulada', '2026-09-22 16:05:51'),
(4, 2, 2, 'general', 'Marco Rojas', 50.00, 'DUB-0004', 'vendida', '2026-09-29 16:05:51'),
(5, 2, 2, 'general', 'Ana Paredes', 50.00, 'DUB-0005', 'vendida', '2026-09-29 16:05:51'),
(6, 2, 2, 'vip', 'Diego Salas', 100.00, 'DUB-0006', 'vendida', '2026-09-30 16:05:51'),
(7, 2, 2, 'vip', 'Valeria Torres', 100.00, 'DUB-0007', 'usada', '2026-09-30 16:05:51'),
(8, 2, 1, 'general', 'Pedro Flores', 50.00, 'DUB-0008', 'vendida', '2026-09-30 16:05:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `dj_artista` varchar(100) NOT NULL,
  `precio_entrada` decimal(10,2) NOT NULL DEFAULT 0.00,
  `aforo` int(10) UNSIGNED NOT NULL,
  `estado` enum('programado','en_curso','cerrado') NOT NULL DEFAULT 'programado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `nombre`, `descripcion`, `fecha`, `hora_inicio`, `dj_artista`, `precio_entrada`, `aforo`, `estado`) VALUES
(1, 'Noche Dorada', 'Apertura de temporada con música house y ambiente exclusivo.', '2026-09-23', '22:00:00', 'DJ Marco Vega', 40.00, 300, 'cerrado'),
(2, 'Dubai Electro Night', 'La noche electrónica más elegante de la ciudad.', '2026-09-30', '22:00:00', 'DJ Alexa Noir', 50.00, 400, 'en_curso'),
(3, 'Gala Champagne & House', 'Noche de gala con champagne, house y show en vivo.', '2026-10-06', '21:00:00', 'DJ Sebastián Ruiz', 60.00, 350, 'programado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `id` int(10) UNSIGNED NOT NULL,
  `numero` int(10) UNSIGNED NOT NULL,
  `zona` enum('general','vip') NOT NULL DEFAULT 'general',
  `capacidad` int(10) UNSIGNED NOT NULL,
  `consumo_minimo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('libre','reservada','ocupada') NOT NULL DEFAULT 'libre'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id`, `numero`, `zona`, `capacidad`, `consumo_minimo`, `estado`) VALUES
(1, 1, 'general', 4, 150.00, 'libre'),
(2, 2, 'general', 4, 150.00, 'libre'),
(3, 3, 'general', 6, 200.00, 'ocupada'),
(4, 4, 'general', 6, 200.00, 'libre'),
(5, 5, 'general', 4, 150.00, 'reservada'),
(6, 6, 'general', 8, 250.00, 'libre'),
(7, 7, 'general', 4, 150.00, 'libre'),
(8, 8, 'general', 6, 200.00, 'libre'),
(9, 9, 'vip', 8, 500.00, 'reservada'),
(10, 10, 'vip', 8, 500.00, 'libre'),
(11, 11, 'vip', 10, 800.00, 'libre'),
(12, 12, 'vip', 12, 1000.00, 'libre');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `categoria` enum('cerveza','coctel','licor','botella','gaseosa','snack') NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) NOT NULL DEFAULT 0,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `categoria`, `precio`, `stock`, `stock_minimo`, `estado`) VALUES
(1, 'Cusqueña Dorada 620 ml', 'cerveza', 12.00, 120, 24, 'activo'),
(2, 'Pilsen Callao 620 ml', 'cerveza', 10.00, 100, 24, 'activo'),
(3, 'Corona 355 ml', 'cerveza', 14.00, 80, 20, 'activo'),
(4, 'Pisco Sour', 'coctel', 25.00, 50, 10, 'activo'),
(5, 'Chilcano de Pisco', 'coctel', 22.00, 50, 10, 'activo'),
(6, 'Mojito', 'coctel', 24.00, 6, 10, 'activo'),
(7, 'Whisky Red Label (trago)', 'licor', 28.00, 60, 10, 'activo'),
(8, 'Vodka Absolut (trago)', 'licor', 26.00, 45, 10, 'activo'),
(9, 'Botella Whisky Black Label', 'botella', 280.00, 12, 3, 'activo'),
(10, 'Botella Vodka Absolut', 'botella', 190.00, 15, 3, 'activo'),
(11, 'Botella Champagne Moët Chandon', 'botella', 650.00, 6, 2, 'activo'),
(12, 'Coca-Cola 500 ml', 'gaseosa', 6.00, 150, 30, 'activo'),
(13, 'Agua mineral 500 ml', 'gaseosa', 5.00, 100, 20, 'activo'),
(14, 'Papas fritas', 'snack', 15.00, 40, 10, 'activo'),
(15, 'Nachos con queso', 'snack', 18.00, 3, 8, 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(10) UNSIGNED NOT NULL,
  `evento_id` int(10) UNSIGNED NOT NULL,
  `mesa_id` int(10) UNSIGNED NOT NULL,
  `cliente_nombre` varchar(100) NOT NULL,
  `cliente_telefono` varchar(20) NOT NULL,
  `cantidad_personas` int(10) UNSIGNED NOT NULL,
  `garantia` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('activa','cancelada','cumplida') NOT NULL DEFAULT 'activa',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `evento_id`, `mesa_id`, `cliente_nombre`, `cliente_telefono`, `cantidad_personas`, `garantia`, `estado`, `created_at`) VALUES
(1, 2, 5, 'Familia Gutiérrez', '987654321', 4, 100.00, 'activa', '2026-09-28 16:05:51'),
(2, 2, 9, 'Grupo Ramírez', '912345678', 8, 300.00, 'activa', '2026-09-29 16:05:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('administrador','cajero','mesero') NOT NULL DEFAULT 'mesero',
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `email`, `password`, `rol`, `estado`, `created_at`) VALUES
(1, 'Administrador General', 'admin', 'admin@dubai.local', '$2y$10$ImQ.29a7omrcKTuEgGusIu.4u3NKcQ2EJMxlNtknnA5omyvxIegnq', 'administrador', 'activo', '2026-09-30 16:05:50'),
(2, 'Carla Cajera', 'cajero', 'cajero@dubai.local', '$2y$10$6kY/dE8IM3l3EsBRHhLPp.oS/vyHXpTlkisPiCBeu5XstVtDFWWFq', 'cajero', 'activo', '2026-09-30 16:05:50'),
(3, 'Mario Mesero', 'mesero', 'mesero@dubai.local', '$2y$10$VwmVH87VhV6cATYKyMQ1d.CLvD10YIaUOy8vLWiV88OH.YijR.Rjm', 'mesero', 'activo', '2026-09-30 16:05:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(10) UNSIGNED NOT NULL,
  `mesa_id` int(10) UNSIGNED DEFAULT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `evento_id` int(10) UNSIGNED DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `metodo_pago` enum('efectivo','tarjeta','yape','plin') DEFAULT NULL,
  `estado` enum('abierta','pagada','anulada') NOT NULL DEFAULT 'abierta',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `mesa_id`, `usuario_id`, `evento_id`, `total`, `metodo_pago`, `estado`, `created_at`) VALUES
(1, 4, 3, 1, 307.00, 'efectivo', 'pagada', '2026-09-23 16:05:51'),
(2, NULL, 2, 1, 122.00, 'yape', 'pagada', '2026-09-23 16:05:51'),
(3, 10, 3, 1, 674.00, 'tarjeta', 'pagada', '2026-09-23 16:05:51'),
(4, NULL, 2, NULL, 78.00, 'efectivo', 'pagada', '2026-09-25 16:05:51'),
(5, 2, 3, NULL, 218.00, 'plin', 'pagada', '2026-09-26 16:05:51'),
(6, NULL, 2, NULL, 114.00, 'tarjeta', 'pagada', '2026-09-27 16:05:51'),
(7, 6, 3, NULL, 360.00, 'yape', 'pagada', '2026-09-28 16:05:51'),
(8, NULL, 2, 2, 144.00, 'efectivo', 'pagada', '2026-09-29 16:05:51'),
(9, 3, 3, 2, 78.00, NULL, 'abierta', '2026-09-30 16:05:51'),
(10, NULL, 2, 2, 80.00, 'efectivo', 'pagada', '2026-09-30 16:05:51'),
(11, 1, 3, 2, 226.00, 'tarjeta', 'pagada', '2026-09-30 16:05:51');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_detalle_venta` (`venta_id`),
  ADD KEY `idx_detalle_producto` (`producto_id`);

--
-- Indices de la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_entradas_codigo` (`codigo`),
  ADD KEY `idx_entradas_evento` (`evento_id`),
  ADD KEY `idx_entradas_usuario` (`usuario_id`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_eventos_fecha` (`fecha`);

--
-- Indices de la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_mesas_numero` (`numero`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_productos_categoria` (`categoria`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reservas_evento` (`evento_id`),
  ADD KEY `idx_reservas_mesa` (`mesa_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuarios_usuario` (`usuario`),
  ADD UNIQUE KEY `uq_usuarios_email` (`email`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ventas_mesa` (`mesa_id`),
  ADD KEY `idx_ventas_usuario` (`usuario_id`),
  ADD KEY `idx_ventas_evento` (`evento_id`),
  ADD KEY `idx_ventas_fecha` (`created_at`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `entradas`
--
ALTER TABLE `entradas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `fk_detalle_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD CONSTRAINT `fk_entradas_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`),
  ADD CONSTRAINT `fk_entradas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `fk_reservas_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`),
  ADD CONSTRAINT `fk_reservas_mesa` FOREIGN KEY (`mesa_id`) REFERENCES `mesas` (`id`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `fk_ventas_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`),
  ADD CONSTRAINT `fk_ventas_mesa` FOREIGN KEY (`mesa_id`) REFERENCES `mesas` (`id`),
  ADD CONSTRAINT `fk_ventas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
