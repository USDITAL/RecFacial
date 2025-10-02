-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-04-2025 a las 18:57:06
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
-- Base de datos: `recfacial`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tajuste`
--

CREATE TABLE `tajuste` (
  `ID_AJUSTE` int(11) NOT NULL,
  `NOM_AJUSTE` varchar(20) DEFAULT NULL,
  `VALOR_AJUSTE` varchar(20) DEFAULT NULL,
  `TIPO_AJUSTE` varchar(5) DEFAULT NULL,
  `DESC_AJUSTE` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tajuste`
--

INSERT INTO `tajuste` (`ID_AJUSTE`, `NOM_AJUSTE`, `VALOR_AJUSTE`, `TIPO_AJUSTE`, `DESC_AJUSTE`) VALUES
(1, 'MaxLoginRq', '6', 'int', 'Número máximo de intentos de acceso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbio`
--

CREATE TABLE `tbio` (
  `COD_BIO` int(11) NOT NULL,
  `COD_TIPO_BIO` int(11) DEFAULT NULL,
  `DATO_BIO` text DEFAULT NULL,
  `COD_EMPLEADO` int(11) DEFAULT NULL,
  `FEC_ALTA` datetime DEFAULT NULL,
  `NOM_USUARIO_ALTA` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbio`
--

INSERT INTO `tbio` (`COD_BIO`, `COD_TIPO_BIO`, `DATO_BIO`, `COD_EMPLEADO`, `FEC_ALTA`, `NOM_USUARIO_ALTA`) VALUES
(14, 1, 'bio_encriptado', 1, '2025-04-03 13:45:41', 'Admon');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `templeado`
--

CREATE TABLE `templeado` (
  `COD_EMPLEADO` int(11) NOT NULL,
  `COD_USUARIO` int(11) DEFAULT NULL,
  `NOM_EMPLEADO` varchar(20) DEFAULT NULL,
  `APE1_EMPLEADO` varchar(20) DEFAULT NULL,
  `APE2_EMPLEADO` varchar(20) DEFAULT NULL,
  `CONTACTO_EMPLEADO` varchar(20) DEFAULT NULL,
  `FEC_ALTA` datetime DEFAULT NULL,
  `NOM_USUARIO_ALTA` varchar(20) DEFAULT NULL,
  `FEC_BAJA` datetime DEFAULT NULL,
  `NOM_USUARIO_BAJA` varchar(20) DEFAULT NULL,
  `FOTO` varchar(30) DEFAULT NULL,
  `HORARIO` varchar(30) DEFAULT NULL,
  `FLEX` tinyint(1) DEFAULT NULL,
  `MAX_HORA_DIA` int(2) DEFAULT NULL,
  `BOLSA_HORAS` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `templeado`
--

INSERT INTO `templeado` (`COD_EMPLEADO`, `COD_USUARIO`, `NOM_EMPLEADO`, `APE1_EMPLEADO`, `APE2_EMPLEADO`, `CONTACTO_EMPLEADO`, `FEC_ALTA`, `NOM_USUARIO_ALTA`, `FEC_BAJA`, `NOM_USUARIO_BAJA`, `FOTO`, `HORARIO`, `FLEX`, `MAX_HORA_DIA`, `BOLSA_HORAS`) VALUES
(1, 2, 'David', 'Martín', 'Prados', 'David@gmail.es', '2025-03-20 11:32:41', 'admon', NULL, NULL, 'emp_0001_da_ma_pr.jpg', '8h a 19h', 1, 6, 1),
(3, 1, 'Juan', 'Perez', 'Gomez', 'juanpg@local.com', '2025-03-20 11:34:57', 'admon', NULL, NULL, 'emp_0002_ju_pe_go.jpg', '8h a 16h', 0, 8, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tincidencia`
--

CREATE TABLE `tincidencia` (
  `ID` int(10) NOT NULL COMMENT 'ID de la incidencia',
  `FECHA_REV` date DEFAULT NULL COMMENT 'Fecha de la solicitud',
  `FECHA_INC` date DEFAULT NULL COMMENT 'Fecha sobre la que trata la incidencia',
  `COMENTARIO` text DEFAULT NULL COMMENT 'Comentario del empleado',
  `PRIORIDAD` int(1) DEFAULT NULL COMMENT 'Prioridad dada por el empelado',
  `COD_EMPLEADO` int(2) DEFAULT NULL COMMENT 'Código del empleado',
  `RESUELTA` tinyint(1) DEFAULT NULL COMMENT 'Estado de la incidencia',
  `COD_USUARIO` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tincidencia`
--

INSERT INTO `tincidencia` (`ID`, `FECHA_REV`, `FECHA_INC`, `COMENTARIO`, `PRIORIDAD`, `COD_EMPLEADO`, `RESUELTA`, `COD_USUARIO`) VALUES
(1, '2025-03-27', '2025-03-24', 'ddd', 1, 1, 1, 2),
(2, '2025-03-27', '2025-03-27', 'Horas Extras', 3, 1, 0, NULL),
(26, '2025-03-28', '2025-03-28', 'Mios', 1, 1, 0, NULL),
(27, '2025-03-28', '2025-03-20', 'Está mal', 2, 1, 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tmarcaje`
--

CREATE TABLE `tmarcaje` (
  `COD_MARCAJE` bigint(20) NOT NULL,
  `COD_TIPO_MARCAJE` int(11) DEFAULT NULL,
  `COD_EMPLEADO` int(11) DEFAULT NULL,
  `COD_BIO` int(11) DEFAULT NULL,
  `DES_FOTO` varchar(30) DEFAULT NULL,
  `COD_TIPO_ACCESO` int(11) DEFAULT NULL,
  `FEC_MARCAJE` datetime DEFAULT NULL,
  `FEC_GRABACION` datetime DEFAULT NULL,
  `IND_INCIDENCIA` tinyint(1) DEFAULT NULL,
  `IND_PENDIENTE` tinyint(1) DEFAULT NULL,
  `DES_OBSERVACIONES` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tmarcaje`
--

INSERT INTO `tmarcaje` (`COD_MARCAJE`, `COD_TIPO_MARCAJE`, `COD_EMPLEADO`, `COD_BIO`, `DES_FOTO`, `COD_TIPO_ACCESO`, `FEC_MARCAJE`, `FEC_GRABACION`, `IND_INCIDENCIA`, `IND_PENDIENTE`, `DES_OBSERVACIONES`) VALUES
(1, 1, 1, NULL, 'foto', 1, '2025-03-20 11:48:24', '2025-03-20 11:48:24', 0, 1, 'observaciones'),
(24, 1, 1, NULL, 'empleado_1_1742819095.jpg', 1, '2025-03-24 12:24:55', '2025-03-24 13:24:55', 0, 0, ''),
(25, 2, 1, NULL, 'empleado_1_1742819161.jpg', 1, '2025-03-24 12:26:01', '2025-03-24 13:26:01', 0, 0, ''),
(26, 1, 1, NULL, 'empleado_1_1742829918.jpg', 1, '2025-03-24 15:25:18', '2025-03-24 16:25:18', 0, 0, ''),
(27, 2, 1, NULL, 'empleado_1_1742830016.jpg', 1, '2025-03-24 15:26:56', '2025-03-24 16:26:56', 0, 0, ''),
(28, 1, 1, NULL, 'empleado_1_1742832595.jpg', 1, '2025-03-24 16:09:55', '2025-03-24 17:09:55', 0, 0, ''),
(29, 2, 1, NULL, 'empleado_1_1742837544.jpg', 1, '2025-03-24 17:32:24', '2025-03-24 18:32:24', 0, 0, ''),
(30, 1, 1, NULL, 'empleado_1_1742889590.jpg', 1, '2025-03-25 07:59:50', '2025-03-25 08:59:50', 0, 0, ''),
(31, 2, 1, NULL, 'empleado_1_1742891899.jpg', 1, '2025-03-25 08:38:19', '2025-03-25 09:38:19', 0, 0, ''),
(32, 1, 1, NULL, 'empleado_1_1742900460.jpg', 1, '2025-03-25 11:01:00', '2025-03-25 12:01:00', 0, 0, ''),
(33, 2, 1, NULL, 'empleado_1_1742916423.jpg', 1, '2025-03-25 15:27:02', '2025-03-25 16:27:03', 0, 0, ''),
(34, 1, 1, NULL, 'empleado_1_1742819095.jpg', 1, '2025-03-21 08:04:05', '2025-03-21 08:04:05', 0, 0, ''),
(35, 2, 1, NULL, 'empleado_1_1742819161.jpg', 1, '2025-03-21 14:06:01', '2025-03-21 14:06:01', 0, 0, ''),
(36, 1, 1, NULL, 'empleado_1_1742819095.jpg', 1, '2025-03-21 16:06:01', '2025-03-21 16:06:01', 0, 0, ''),
(37, 2, 1, NULL, 'empleado_1_1742819161.jpg', 1, '2025-03-21 18:16:22', '2025-03-21 18:16:22', 0, 0, ''),
(38, 1, 1, NULL, 'empleado_1_1742819095.jpg', 1, '2025-03-24 08:00:55', '2025-03-24 08:00:55', 0, 0, ''),
(39, 2, 1, NULL, 'empleado_1_1742819161.jpg', 1, '2025-03-24 12:15:01', '2025-03-24 12:05:12', 0, 0, ''),
(40, 1, 1, NULL, 'empleado_1_1742819095.jpg', 1, '2025-03-20 08:02:00', '2025-03-20 08:02:00', 0, 0, ''),
(41, 2, 1, NULL, 'empleado_1_1742819161.jpg', 1, '2025-03-20 14:03:03', '2025-03-20 14:03:03', 0, 0, ''),
(42, 1, 1, NULL, 'empleado_1_1742819095.jpg', 1, '2025-03-20 16:10:25', '2025-03-20 16:10:25', 0, 0, ''),
(43, 2, 1, NULL, 'empleado_1_1742819161.jpg', 1, '2025-03-20 18:07:35', '2025-03-20 18:07:35', 0, 0, ''),
(44, 1, 1, NULL, 'empleado_1_1742819095.jpg', 1, '2025-03-19 08:07:01', '2025-03-19 08:07:01', 0, 0, ''),
(45, 2, 1, NULL, 'empleado_1_1742819161.jpg', 1, '2025-03-19 16:12:01', '2025-03-19 16:12:01', 0, 0, ''),
(46, 1, 1, NULL, 'empleado_1_1742919496.jpg', 1, '2025-03-25 17:18:16', '2025-03-25 17:18:16', 0, 0, ''),
(47, 2, 1, NULL, 'empleado_1_1742819161.jpg', 1, '2025-03-25 18:59:01', '2025-03-25 18:59:01', 0, 0, ''),
(48, 1, 1, NULL, 'empleado_1_1742981114.jpg', 1, '2025-03-26 10:25:13', '2025-03-26 10:25:14', 0, 0, ''),
(49, 2, 1, NULL, 'empleado_1_1742981762.jpg', 1, '2025-03-26 10:36:02', '2025-03-26 10:36:02', 0, 0, ''),
(50, 1, 1, NULL, 'empleado_1_1742982244.jpg', 1, '2025-03-26 10:44:04', '2025-03-26 10:44:04', 0, 0, ''),
(51, 1, 1, NULL, 'empleado_1_1742819095.jpg', 99, '2025-03-17 08:00:00', '2025-03-17 08:00:00', 0, 0, ''),
(52, 2, 1, NULL, 'empleado_1_1742819095.jpg', 99, '2025-03-17 14:00:00', '2025-03-17 14:00:00', 0, 0, ''),
(53, 2, 1, NULL, 'empleado_1_1743011885.jpg', 1, '2025-03-26 18:58:05', '2025-03-26 18:58:05', 0, 0, ''),
(54, 1, 1, NULL, 'empleado_1_1743060817.jpg', 1, '2025-03-27 08:33:37', '2025-03-27 08:33:37', 0, 0, ''),
(55, 2, 1, NULL, 'empleado_1_1743080252.jpg', 1, '2025-03-27 13:59:32', '2025-03-27 13:57:32', 0, 0, ''),
(56, 1, 1, NULL, 'empleado_1_1743146363.jpg', 1, '2025-03-28 08:19:23', '2025-03-28 08:19:23', 0, 0, ''),
(57, 2, 1, NULL, 'empleado_1_1743148134.jpg', 1, '2025-03-28 08:52:54', '2025-03-28 08:48:54', 0, 0, ''),
(59, 1, 1, NULL, 'empleado_1_1743402990.jpg', 1, '2025-03-31 08:36:30', '2025-03-31 08:36:30', 0, 0, ''),
(60, 2, 1, NULL, 'empleado_1_1743431511.jpg', 1, '2025-03-31 16:31:51', '2025-03-31 16:31:51', 0, 0, ''),
(61, 1, 1, NULL, 'empleado_1_1743488616.jpg', 1, '2025-04-01 08:23:36', '2025-04-01 08:23:37', 0, 0, ''),
(62, 2, 1, NULL, 'empleado_1_1743577305.jpg', 1, '2025-04-01 19:01:45', '2025-04-02 09:01:45', 0, 0, ''),
(63, 1, 1, NULL, 'empleado_1_1743577374.jpg', 1, '2025-04-02 08:02:54', '2025-04-02 09:02:54', 0, 0, ''),
(64, 2, 1, NULL, 'empleado_1_1743660474.jpg', 1, '2025-04-03 08:07:54', '2025-04-03 08:07:54', 0, 0, ''),
(65, 1, 1, NULL, 'empleado_1_1743680355.jpg', 1, '2025-04-03 09:39:15', '2025-04-03 13:39:15', 0, 0, ''),
(66, 2, 1, 14, 'empleado_1_1743680780.jpg', 1, '2025-04-03 19:46:19', '2025-04-03 13:46:20', 0, 0, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trol`
--

CREATE TABLE `trol` (
  `COD_ROL` int(11) NOT NULL,
  `NOM_ROL` varchar(20) DEFAULT NULL,
  `DES_ROL` varchar(100) DEFAULT NULL,
  `FEC_ALTA` datetime DEFAULT NULL,
  `NOM_USUARIO_ALTA` varchar(20) DEFAULT NULL,
  `FEC_BAJA` datetime DEFAULT NULL,
  `NOM_USUARIO_BAJA` varchar(20) DEFAULT NULL,
  `PRIVILEGIOS` text DEFAULT NULL COMMENT 'Array de privilegios'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `trol`
--

INSERT INTO `trol` (`COD_ROL`, `NOM_ROL`, `DES_ROL`, `FEC_ALTA`, `NOM_USUARIO_ALTA`, `FEC_BAJA`, `NOM_USUARIO_BAJA`, `PRIVILEGIOS`) VALUES
(1, 'Conserje', 'Acceso a la aplicación de reconocimiento facial', '2025-03-20 11:40:27', 'Admon', NULL, NULL, 'O:17:\"Clases\\Privilegio\":20:{s:27:\"\0Clases\\Privilegio\0empCrear\";b:0;s:31:\"\0Clases\\Privilegio\0empModificar\";b:0;s:26:\"\0Clases\\Privilegio\0empBaja\";b:0;s:27:\"\0Clases\\Privilegio\0usrCrear\";b:0;s:31:\"\0Clases\\Privilegio\0usrModificar\";b:0;s:26:\"\0Clases\\Privilegio\0usrBaja\";b:0;s:33:\"\0Clases\\Privilegio\0usrGenerarPass\";b:0;s:33:\"\0Clases\\Privilegio\0marCrearPropio\";b:0;s:37:\"\0Clases\\Privilegio\0marConsultarPropio\";b:0;s:27:\"\0Clases\\Privilegio\0marCrear\";b:0;s:31:\"\0Clases\\Privilegio\0marModificar\";b:0;s:30:\"\0Clases\\Privilegio\0marEliminar\";b:0;s:31:\"\0Clases\\Privilegio\0marConsultar\";b:0;s:26:\"\0Clases\\Privilegio\0marAuth\";b:0;s:27:\"\0Clases\\Privilegio\0bioCrear\";b:0;s:30:\"\0Clases\\Privilegio\0bioEliminar\";b:0;s:27:\"\0Clases\\Privilegio\0rolCrear\";b:0;s:31:\"\0Clases\\Privilegio\0rolModificar\";b:0;s:30:\"\0Clases\\Privilegio\0rolEliminar\";b:0;s:35:\"\0Clases\\Privilegio\0ajustesModificar\";b:0;}'),
(2, 'Admin', 'Acceso al portal de administración', '2025-03-20 11:48:41', 'Admon', NULL, NULL, 'O:17:\"Clases\\Privilegio\":20:{s:27:\"\0Clases\\Privilegio\0empCrear\";b:0;s:31:\"\0Clases\\Privilegio\0empModificar\";b:0;s:26:\"\0Clases\\Privilegio\0empBaja\";b:0;s:27:\"\0Clases\\Privilegio\0usrCrear\";b:0;s:31:\"\0Clases\\Privilegio\0usrModificar\";b:0;s:26:\"\0Clases\\Privilegio\0usrBaja\";b:0;s:33:\"\0Clases\\Privilegio\0usrGenerarPass\";b:0;s:33:\"\0Clases\\Privilegio\0marCrearPropio\";b:0;s:37:\"\0Clases\\Privilegio\0marConsultarPropio\";b:0;s:27:\"\0Clases\\Privilegio\0marCrear\";b:0;s:31:\"\0Clases\\Privilegio\0marModificar\";b:0;s:30:\"\0Clases\\Privilegio\0marEliminar\";b:0;s:31:\"\0Clases\\Privilegio\0marConsultar\";b:0;s:26:\"\0Clases\\Privilegio\0marAuth\";b:0;s:27:\"\0Clases\\Privilegio\0bioCrear\";b:0;s:30:\"\0Clases\\Privilegio\0bioEliminar\";b:0;s:27:\"\0Clases\\Privilegio\0rolCrear\";b:0;s:31:\"\0Clases\\Privilegio\0rolModificar\";b:0;s:30:\"\0Clases\\Privilegio\0rolEliminar\";b:0;s:35:\"\0Clases\\Privilegio\0ajustesModificar\";b:0;}'),
(3, 'Empleado', 'Acceso al portal de empleado', '2025-03-20 11:48:41', 'Admon', NULL, NULL, 'O:17:\"Clases\\Privilegio\":20:{s:27:\"\0Clases\\Privilegio\0empCrear\";b:0;s:31:\"\0Clases\\Privilegio\0empModificar\";b:0;s:26:\"\0Clases\\Privilegio\0empBaja\";b:0;s:27:\"\0Clases\\Privilegio\0usrCrear\";b:0;s:31:\"\0Clases\\Privilegio\0usrModificar\";b:0;s:26:\"\0Clases\\Privilegio\0usrBaja\";b:0;s:33:\"\0Clases\\Privilegio\0usrGenerarPass\";b:0;s:33:\"\0Clases\\Privilegio\0marCrearPropio\";b:0;s:37:\"\0Clases\\Privilegio\0marConsultarPropio\";b:0;s:27:\"\0Clases\\Privilegio\0marCrear\";b:0;s:31:\"\0Clases\\Privilegio\0marModificar\";b:0;s:30:\"\0Clases\\Privilegio\0marEliminar\";b:0;s:31:\"\0Clases\\Privilegio\0marConsultar\";b:0;s:26:\"\0Clases\\Privilegio\0marAuth\";b:0;s:27:\"\0Clases\\Privilegio\0bioCrear\";b:0;s:30:\"\0Clases\\Privilegio\0bioEliminar\";b:0;s:27:\"\0Clases\\Privilegio\0rolCrear\";b:0;s:31:\"\0Clases\\Privilegio\0rolModificar\";b:0;s:30:\"\0Clases\\Privilegio\0rolEliminar\";b:0;s:35:\"\0Clases\\Privilegio\0ajustesModificar\";b:0;}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ttbio`
--

CREATE TABLE `ttbio` (
  `COD_TIPO_BIO` int(11) NOT NULL,
  `DES_TIPO_BIO` varchar(20) DEFAULT NULL,
  `FEC_ALTA` datetime DEFAULT NULL,
  `NOM_USUARIO_ALTA` varchar(20) DEFAULT NULL,
  `FEC_BAJA` datetime DEFAULT NULL,
  `NOM_USUARIO_BAJA` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ttbio`
--

INSERT INTO `ttbio` (`COD_TIPO_BIO`, `DES_TIPO_BIO`, `FEC_ALTA`, `NOM_USUARIO_ALTA`, `FEC_BAJA`, `NOM_USUARIO_BAJA`) VALUES
(1, 'Facial', '2025-03-16 11:39:02', 'Admon', NULL, NULL),
(2, 'RFID', '2025-03-16 11:39:02', 'Admon', NULL, NULL),
(3, 'Teclado', '2025-03-20 11:54:22', 'Admon', NULL, NULL),
(7, 'Keypad', '2025-03-20 12:16:02', 'Admon', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ttipoacceso`
--

CREATE TABLE `ttipoacceso` (
  `COD_TIPO_ACCESO` int(11) NOT NULL,
  `DES_TIPO_ACCESO` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ttipoacceso`
--

INSERT INTO `ttipoacceso` (`COD_TIPO_ACCESO`, `DES_TIPO_ACCESO`) VALUES
(1, 'RecFacial'),
(2, 'RFID'),
(99, 'AUSENCIA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ttransacciones`
--

CREATE TABLE `ttransacciones` (
  `COD_TRANSACCION` bigint(20) NOT NULL,
  `TIP_TRANS` varchar(5) DEFAULT NULL,
  `DESC_TRANS` varchar(30) DEFAULT NULL,
  `COD_OBJ` int(11) DEFAULT NULL,
  `NOM_OBJ` varchar(20) DEFAULT NULL,
  `COD_USUARIO` int(11) DEFAULT NULL,
  `FEC_SIS` datetime DEFAULT NULL,
  `HOR_SIS` datetime DEFAULT NULL,
  `IP_USUARIO` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ttransacciones`
--

INSERT INTO `ttransacciones` (`COD_TRANSACCION`, `TIP_TRANS`, `DESC_TRANS`, `COD_OBJ`, `NOM_OBJ`, `COD_USUARIO`, `FEC_SIS`, `HOR_SIS`, `IP_USUARIO`) VALUES
(6, 'mod_u', 'Modificación del usuario Admon', 1, 'tUsuario', 1, '2025-03-20 13:30:43', NULL, '127.0.0.1'),
(7, 'mod_u', 'Modificación del usuario Admon', 1, 'tUsuario', 1, '2025-03-20 13:31:02', NULL, '127.0.0.1'),
(8, 'mod_u', 'Modificación del usuario Admon', 1, 'tUsuario', 1, '2025-03-20 13:31:17', NULL, '127.0.0.1'),
(9, 'mod_u', 'Modificación del usuario Admon', 1, 'tUsuario', 1, '2025-03-20 13:36:53', NULL, '127.0.0.1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tusuario`
--

CREATE TABLE `tusuario` (
  `COD_USUARIO` int(11) NOT NULL,
  `NOM_LOGIN` varchar(20) DEFAULT NULL,
  `DES_CONTRASENA` varchar(100) DEFAULT NULL,
  `DES_CORREO` varchar(20) DEFAULT NULL,
  `FEC_ALTA` datetime DEFAULT NULL,
  `NOM_USUARIO_ALTA` varchar(20) DEFAULT NULL,
  `FEC_BAJA` datetime DEFAULT NULL,
  `NOM_USUARIO_BAJA` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tusuario`
--

INSERT INTO `tusuario` (`COD_USUARIO`, `NOM_LOGIN`, `DES_CONTRASENA`, `DES_CORREO`, `FEC_ALTA`, `NOM_USUARIO_ALTA`, `FEC_BAJA`, `NOM_USUARIO_BAJA`) VALUES
(1, 'Admon', '$2y$10$wmG1sV.DKtBGmElbfJvdNezoKWvene1rOui8jJU48e01USIybXdVO', 'benito@sefue.com', '2025-03-20 12:32:57', 'Admon', NULL, NULL),
(2, 'David', '$2y$10$wmG1sV.DKtBGmElbfJvdNezoKWvene1rOui8jJU48e01USIybXdVO', 'david@david.com', '2025-03-25 09:39:30', 'Admon', NULL, NULL),
(5, 'Raquel', '$2y$10$lDgy5yfHJEJQjYK6l3yvBOluqR7HfzFcxT2vT2qTOivf8lElI9ROG', 'davidraquelisis@gmail.com', '2025-04-03 08:24:46', 'David', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tusuariorol`
--

CREATE TABLE `tusuariorol` (
  `COD_USUARIO` int(11) DEFAULT NULL,
  `COD_ROL` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tusuariorol`
--

INSERT INTO `tusuariorol` (`COD_USUARIO`, `COD_ROL`) VALUES
(1, 1),
(2, 2),
(2, 3);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tajuste`
--
ALTER TABLE `tajuste`
  ADD PRIMARY KEY (`ID_AJUSTE`);

--
-- Indices de la tabla `tbio`
--
ALTER TABLE `tbio`
  ADD PRIMARY KEY (`COD_BIO`),
  ADD KEY `COD_EMPLEADO` (`COD_EMPLEADO`),
  ADD KEY `COD_TIPO_BIO` (`COD_TIPO_BIO`);

--
-- Indices de la tabla `templeado`
--
ALTER TABLE `templeado`
  ADD PRIMARY KEY (`COD_EMPLEADO`),
  ADD UNIQUE KEY `COD_USUARIO` (`COD_USUARIO`);

--
-- Indices de la tabla `tincidencia`
--
ALTER TABLE `tincidencia`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `tmarcaje`
--
ALTER TABLE `tmarcaje`
  ADD PRIMARY KEY (`COD_MARCAJE`),
  ADD KEY `COD_EMPLEADO` (`COD_EMPLEADO`),
  ADD KEY `COD_TIPO_ACCESO` (`COD_TIPO_ACCESO`),
  ADD KEY `tmarcaje_ibfk_2` (`COD_BIO`);

--
-- Indices de la tabla `trol`
--
ALTER TABLE `trol`
  ADD PRIMARY KEY (`COD_ROL`);

--
-- Indices de la tabla `ttbio`
--
ALTER TABLE `ttbio`
  ADD PRIMARY KEY (`COD_TIPO_BIO`);

--
-- Indices de la tabla `ttipoacceso`
--
ALTER TABLE `ttipoacceso`
  ADD PRIMARY KEY (`COD_TIPO_ACCESO`);

--
-- Indices de la tabla `ttransacciones`
--
ALTER TABLE `ttransacciones`
  ADD PRIMARY KEY (`COD_TRANSACCION`),
  ADD KEY `COD_USUARIO` (`COD_USUARIO`);

--
-- Indices de la tabla `tusuario`
--
ALTER TABLE `tusuario`
  ADD PRIMARY KEY (`COD_USUARIO`);

--
-- Indices de la tabla `tusuariorol`
--
ALTER TABLE `tusuariorol`
  ADD UNIQUE KEY `uk_usuario_rol` (`COD_USUARIO`,`COD_ROL`),
  ADD KEY `COD_ROL` (`COD_ROL`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tajuste`
--
ALTER TABLE `tajuste`
  MODIFY `ID_AJUSTE` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `tbio`
--
ALTER TABLE `tbio`
  MODIFY `COD_BIO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `templeado`
--
ALTER TABLE `templeado`
  MODIFY `COD_EMPLEADO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tincidencia`
--
ALTER TABLE `tincidencia`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID de la incidencia', AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `tmarcaje`
--
ALTER TABLE `tmarcaje`
  MODIFY `COD_MARCAJE` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT de la tabla `trol`
--
ALTER TABLE `trol`
  MODIFY `COD_ROL` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `ttbio`
--
ALTER TABLE `ttbio`
  MODIFY `COD_TIPO_BIO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `ttipoacceso`
--
ALTER TABLE `ttipoacceso`
  MODIFY `COD_TIPO_ACCESO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT de la tabla `ttransacciones`
--
ALTER TABLE `ttransacciones`
  MODIFY `COD_TRANSACCION` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `tusuario`
--
ALTER TABLE `tusuario`
  MODIFY `COD_USUARIO` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tbio`
--
ALTER TABLE `tbio`
  ADD CONSTRAINT `tbio_ibfk_1` FOREIGN KEY (`COD_EMPLEADO`) REFERENCES `templeado` (`COD_EMPLEADO`),
  ADD CONSTRAINT `tbio_ibfk_2` FOREIGN KEY (`COD_TIPO_BIO`) REFERENCES `ttbio` (`COD_TIPO_BIO`);

--
-- Filtros para la tabla `templeado`
--
ALTER TABLE `templeado`
  ADD CONSTRAINT `templeado_ibfk_1` FOREIGN KEY (`COD_USUARIO`) REFERENCES `tusuario` (`COD_USUARIO`);

--
-- Filtros para la tabla `tmarcaje`
--
ALTER TABLE `tmarcaje`
  ADD CONSTRAINT `tmarcaje_ibfk_1` FOREIGN KEY (`COD_EMPLEADO`) REFERENCES `templeado` (`COD_EMPLEADO`),
  ADD CONSTRAINT `tmarcaje_ibfk_2` FOREIGN KEY (`COD_BIO`) REFERENCES `tbio` (`COD_BIO`) ON DELETE SET NULL,
  ADD CONSTRAINT `tmarcaje_ibfk_3` FOREIGN KEY (`COD_TIPO_ACCESO`) REFERENCES `ttipoacceso` (`COD_TIPO_ACCESO`);

--
-- Filtros para la tabla `ttransacciones`
--
ALTER TABLE `ttransacciones`
  ADD CONSTRAINT `ttransacciones_ibfk_1` FOREIGN KEY (`COD_USUARIO`) REFERENCES `tusuario` (`COD_USUARIO`);

--
-- Filtros para la tabla `tusuariorol`
--
ALTER TABLE `tusuariorol`
  ADD CONSTRAINT `tusuariorol_ibfk_1` FOREIGN KEY (`COD_USUARIO`) REFERENCES `tusuario` (`COD_USUARIO`),
  ADD CONSTRAINT `tusuariorol_ibfk_2` FOREIGN KEY (`COD_ROL`) REFERENCES `trol` (`COD_ROL`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
