-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-10-2025 a las 00:50:44
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
-- Base de datos: `adira`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_gasto`
--

CREATE TABLE `categorias_gasto` (
  `ID_Categoria` int(11) NOT NULL,
  `NombreCategoria` varchar(150) NOT NULL,
  `Descripcion` varchar(400) DEFAULT NULL,
  `CostoEstimado` decimal(14,2) DEFAULT 0.00,
  `UnidadMedida` varchar(50) DEFAULT 'unidad',
  `PrecioUnitarioReferencia` decimal(14,4) DEFAULT 0.0000,
  `Moneda` varchar(10) DEFAULT 'ARS',
  `EsRecurrente` tinyint(1) DEFAULT 0,
  `FechaCreacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias_gasto`
--

INSERT INTO `categorias_gasto` (`ID_Categoria`, `NombreCategoria`, `Descripcion`, `CostoEstimado`, `UnidadMedida`, `PrecioUnitarioReferencia`, `Moneda`, `EsRecurrente`, `FechaCreacion`) VALUES
(1, 'Pago de Empleados (Nómina)', 'Sueldos y cargas sociales del personal', 200000.00, 'mes', 200000.0000, 'ARS', 1, '2025-10-25 20:41:25'),
(2, 'Insumos: Uniformes', 'Uniformes para personal operativo', 15000.00, 'kit', 1500.0000, 'ARS', 0, '2025-10-25 20:41:25'),
(3, 'Insumos: Equipamiento (Handies/Micros)', 'Equipamiento de comunicación y audio', 80000.00, 'unidad', 80000.0000, 'ARS', 0, '2025-10-25 20:41:25'),
(4, 'Insumos: Alimentos/Agua', 'Agua y viandas para el personal', 5000.00, 'mes', 5000.0000, 'ARS', 1, '2025-10-25 20:41:25'),
(5, 'Viajes y Viáticos', 'Viáticos por traslados y hospedajes', 30000.00, 'viaje', 3000.0000, 'ARS', 0, '2025-10-25 20:41:25'),
(6, 'Impuestos y Monotributo', 'Cargas e impuestos fiscales', 25000.00, 'mes', 25000.0000, 'ARS', 1, '2025-10-25 20:41:25'),
(7, 'Otros', 'Gastos varios no categorizados', 10000.00, 'unidad', 10000.0000, 'ARS', 0, '2025-10-25 20:41:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `ID_Empleado` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido` varchar(100) NOT NULL,
  `DNI` varchar(20) NOT NULL,
  `Puesto` varchar(100) DEFAULT NULL,
  `TipoContrato` varchar(50) DEFAULT 'Tiempo Completo',
  `FechaIngreso` date DEFAULT NULL,
  `SalarioBase` decimal(14,2) DEFAULT 0.00,
  `Moneda` varchar(10) DEFAULT 'ARS',
  `Activo` tinyint(1) DEFAULT 1,
  `FechaCreacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`ID_Empleado`, `Nombre`, `Apellido`, `DNI`, `Puesto`, `TipoContrato`, `FechaIngreso`, `SalarioBase`, `Moneda`, `Activo`, `FechaCreacion`) VALUES
(1, 'Carlos', 'Pérez', '32145896', 'Vigilador', 'Tiempo Completo', '2020-05-10', 180000.00, 'ARS', 1, '2025-10-25 20:44:56'),
(2, 'Lucía', 'Gómez', '29547123', 'Supervisora', 'Tiempo Completo', '2019-11-01', 250000.00, 'ARS', 1, '2025-10-25 20:44:56'),
(3, 'Julián', 'Rodríguez', '40125689', 'Chofer', 'Eventual', '2023-03-15', 120000.00, 'ARS', 1, '2025-10-25 20:44:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados_eventos`
--

CREATE TABLE `empleados_eventos` (
  `ID_EmpleadoEvento` int(11) NOT NULL,
  `ID_Empleado` int(11) NOT NULL,
  `ID_Evento` int(11) NOT NULL,
  `RolEnEvento` varchar(100) DEFAULT NULL,
  `FechaAsignacion` date DEFAULT curdate(),
  `HorasAsignadas` decimal(6,2) DEFAULT 0.00,
  `Observaciones` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados_eventos`
--

INSERT INTO `empleados_eventos` (`ID_EmpleadoEvento`, `ID_Empleado`, `ID_Evento`, `RolEnEvento`, `FechaAsignacion`, `HorasAsignadas`, `Observaciones`) VALUES
(1, 1, 1, 'Guardia Nocturno', '2025-10-25', 160.00, 'Turno noche en planta industrial'),
(2, 2, 1, 'Supervisora General', '2025-10-25', 180.00, 'Encargada del personal'),
(3, 3, 1, 'Chofer Traslado', '2025-10-25', 60.00, 'Traslado de personal y materiales');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `ID_Evento` int(11) NOT NULL,
  `Localidad` varchar(120) NOT NULL,
  `Contratista` varchar(200) NOT NULL,
  `NombreEvento` varchar(250) DEFAULT NULL,
  `Modalidad` varchar(50) DEFAULT NULL,
  `Establecimiento` varchar(200) DEFAULT NULL,
  `FechaInicio` datetime DEFAULT NULL,
  `FechaFin` datetime DEFAULT NULL,
  `MontoCobrarEstimado` decimal(14,2) DEFAULT NULL,
  `Moneda` varchar(10) DEFAULT 'ARS',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`ID_Evento`, `Localidad`, `Contratista`, `NombreEvento`, `Modalidad`, `Establecimiento`, `FechaInicio`, `FechaFin`, `MontoCobrarEstimado`, `Moneda`, `CreatedAt`) VALUES
(1, 'Buenos Aires', 'SegurCorp S.A.', 'Vigilancia Planta Industrial C', 'Fijo', 'Fábrica C, Dock Sud', '2025-10-01 08:00:00', '2025-10-31 20:00:00', 350000.00, 'ARS', '2025-10-25 20:41:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `ID_Gasto` int(11) NOT NULL,
  `IdentificadorUnico` varchar(100) DEFAULT NULL,
  `Fecha` date NOT NULL,
  `Cantidad` decimal(12,4) DEFAULT 1.0000,
  `PrecioUnitario` decimal(14,4) DEFAULT 0.0000,
  `Monto` decimal(16,2) GENERATED ALWAYS AS (round(`Cantidad` * `PrecioUnitario`,2)) STORED,
  `Descripcion` varchar(500) DEFAULT NULL,
  `ID_Categoria` int(11) DEFAULT NULL,
  `ID_Evento` int(11) DEFAULT NULL,
  `Proveedor` varchar(200) DEFAULT NULL,
  `Comprobante` varchar(500) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos`
--

INSERT INTO `gastos` (`ID_Gasto`, `IdentificadorUnico`, `Fecha`, `Cantidad`, `PrecioUnitario`, `Descripcion`, `ID_Categoria`, `ID_Evento`, `Proveedor`, `Comprobante`, `CreatedAt`) VALUES
(1, 'G-2025-0001', '2025-10-01', 1.0000, 200000.0000, 'Pago de sueldos mes Octubre', 1, 1, 'Banco Nación', 'recibo_2025_10.pdf', '2025-10-25 20:41:26'),
(2, 'G-2025-0002', '2025-10-05', 10.0000, 1500.0000, 'Compra de 10 uniformes', 2, 1, 'Uniformes del Sur', 'factura_345.pdf', '2025-10-25 20:41:26'),
(3, 'G-2025-0003', '2025-10-06', 2.0000, 40000.0000, 'Compra de 2 handies Motorola', 3, 1, 'ElectroCom', 'fact_678.pdf', '2025-10-25 20:41:26');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_costoslaborales_evento`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_costoslaborales_evento` (
`ID_Evento` int(11)
,`NombreEvento` varchar(250)
,`Localidad` varchar(120)
,`TotalEmpleados` bigint(21)
,`CostoMensualEstimado` decimal(36,2)
,`TotalHorasAsignadas` decimal(28,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_gastos_detalle`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_gastos_detalle` (
`ID_Gasto` int(11)
,`IdentificadorUnico` varchar(100)
,`Fecha` date
,`Cantidad` decimal(12,4)
,`PrecioUnitario` decimal(14,4)
,`Monto` decimal(16,2)
,`DescripcionGasto` varchar(500)
,`Categoria` varchar(150)
,`CostoEstimado` decimal(14,2)
,`PrecioUnitarioReferencia` decimal(14,4)
,`NombreEvento` varchar(250)
,`Localidad` varchar(120)
,`Contratista` varchar(200)
,`MontoCobrarEstimado` decimal(14,2)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_costoslaborales_evento`
--
DROP TABLE IF EXISTS `vw_costoslaborales_evento`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_costoslaborales_evento`  AS SELECT `ev`.`ID_Evento` AS `ID_Evento`, `ev`.`NombreEvento` AS `NombreEvento`, `ev`.`Localidad` AS `Localidad`, count(`ee`.`ID_Empleado`) AS `TotalEmpleados`, sum(`emp`.`SalarioBase`) AS `CostoMensualEstimado`, sum(`ee`.`HorasAsignadas`) AS `TotalHorasAsignadas` FROM ((`empleados_eventos` `ee` join `empleados` `emp` on(`ee`.`ID_Empleado` = `emp`.`ID_Empleado`)) join `eventos` `ev` on(`ee`.`ID_Evento` = `ev`.`ID_Evento`)) GROUP BY `ev`.`ID_Evento`, `ev`.`NombreEvento`, `ev`.`Localidad` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_gastos_detalle`
--
DROP TABLE IF EXISTS `vw_gastos_detalle`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_gastos_detalle`  AS SELECT `g`.`ID_Gasto` AS `ID_Gasto`, `g`.`IdentificadorUnico` AS `IdentificadorUnico`, `g`.`Fecha` AS `Fecha`, `g`.`Cantidad` AS `Cantidad`, `g`.`PrecioUnitario` AS `PrecioUnitario`, `g`.`Monto` AS `Monto`, `g`.`Descripcion` AS `DescripcionGasto`, `c`.`NombreCategoria` AS `Categoria`, `c`.`CostoEstimado` AS `CostoEstimado`, `c`.`PrecioUnitarioReferencia` AS `PrecioUnitarioReferencia`, `e`.`NombreEvento` AS `NombreEvento`, `e`.`Localidad` AS `Localidad`, `e`.`Contratista` AS `Contratista`, `e`.`MontoCobrarEstimado` AS `MontoCobrarEstimado` FROM ((`gastos` `g` left join `categorias_gasto` `c` on(`g`.`ID_Categoria` = `c`.`ID_Categoria`)) left join `eventos` `e` on(`g`.`ID_Evento` = `e`.`ID_Evento`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias_gasto`
--
ALTER TABLE `categorias_gasto`
  ADD PRIMARY KEY (`ID_Categoria`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`ID_Empleado`),
  ADD UNIQUE KEY `DNI` (`DNI`);

--
-- Indices de la tabla `empleados_eventos`
--
ALTER TABLE `empleados_eventos`
  ADD PRIMARY KEY (`ID_EmpleadoEvento`),
  ADD KEY `ID_Empleado` (`ID_Empleado`),
  ADD KEY `ID_Evento` (`ID_Evento`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`ID_Evento`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`ID_Gasto`),
  ADD UNIQUE KEY `IdentificadorUnico` (`IdentificadorUnico`),
  ADD KEY `ID_Categoria` (`ID_Categoria`),
  ADD KEY `fk_gastos_evento` (`ID_Evento`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias_gasto`
--
ALTER TABLE `categorias_gasto`
  MODIFY `ID_Categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `ID_Empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `empleados_eventos`
--
ALTER TABLE `empleados_eventos`
  MODIFY `ID_EmpleadoEvento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `ID_Evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `ID_Gasto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `empleados_eventos`
--
ALTER TABLE `empleados_eventos`
  ADD CONSTRAINT `empleados_eventos_ibfk_1` FOREIGN KEY (`ID_Empleado`) REFERENCES `empleados` (`ID_Empleado`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `empleados_eventos_ibfk_2` FOREIGN KEY (`ID_Evento`) REFERENCES `eventos` (`ID_Evento`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD CONSTRAINT `fk_gastos_evento` FOREIGN KEY (`ID_Evento`) REFERENCES `eventos` (`ID_Evento`) ON DELETE CASCADE,
  ADD CONSTRAINT `gastos_ibfk_1` FOREIGN KEY (`ID_Categoria`) REFERENCES `categorias_gasto` (`ID_Categoria`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `gastos_ibfk_2` FOREIGN KEY (`ID_Evento`) REFERENCES `eventos` (`ID_Evento`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
