-- phpMyAdmin SQL Dump
-- version 5.2.1deb1+deb12u1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 27-10-2025 a las 15:51:54
-- Versión del servidor: 10.11.14-MariaDB-0+deb12u2
-- Versión de PHP: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `rrhh-prac`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Adendas`
--

CREATE TABLE `Adendas` (
  `adenda_id` int(11) NOT NULL,
  `convenio_id` int(11) NOT NULL,
  `tipo_accion` varchar(50) DEFAULT NULL,
  `fecha_adenda` date DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `Adendas`
--

INSERT INTO `Adendas` (`adenda_id`, `convenio_id`, `tipo_accion`, `fecha_adenda`, `descripcion`) VALUES
(1, 4, 'CORTE', '2025-07-01', 'ADENDA DE SUSPENSION EN JULIO 2025 REINICIÓ EL 01 DE AGOSTO'),
(2, 37, 'AMPLIACION', '2025-10-01', 'ADENDA DE AMPLIACION DE: OCTUBRE 2025 A ABRIL 2026'),
(3, 38, 'AMPLIACION', '2025-10-01', 'ADENDA DE AMPLIACION DE: OCTUBRE 2025 A ABRIL 2026'),
(4, 40, 'CORTE', '2025-06-30', 'ADENDA DE SUSPENCION DEL 30 DE JUNIO AL 1 DE AGOSTO'),
(5, 43, 'CORTE', '2025-07-01', 'ADENDA DE SUSPENCION TODO JULIO REINICIO EL 01 DE AGOSTO'),
(6, 52, 'AMPLIACION', '2025-10-01', 'ADENDA DE AMPLIACION DE: OCTUBRE 2025 A ABRIL 2026');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Areas`
--

CREATE TABLE `Areas` (
  `area_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `Areas`
--

INSERT INTO `Areas` (`area_id`, `nombre`) VALUES
(40, 'ABASTECIMIENTOS'),
(39, 'CENFOTI'),
(12, 'CENTRO DE IDIOMAS'),
(38, 'CENTRO ESTOMATOLÓGICO LUIS VALLEJOS SANTONI'),
(25, 'DECANATO FACULTAD CIENCIAS DE LA SALUD'),
(37, 'DEFENSORIA UNIVERSITARIA'),
(29, 'DEPARTAMENTO ACADEMICO DE MEDICINA HUMANA'),
(11, 'DEPARTAMENTO ACADEMICO DE SISTEMAS'),
(41, 'DIRECCIÓN DE ADMINISTRACIÓN'),
(46, 'DIRECCION DE ADMISION'),
(30, 'DIRECCIÓN DE BIENESTAR UNIVERSITARIO'),
(28, 'DIRECCION DE COOPERACION NACIONAL E INTERNACIONAL'),
(43, 'DIRECCION DE RECURSOS HUMANOS'),
(24, 'DIRECCIÓN DE RESPONSABILIDAD SOCIAL Y EXTENSIÓN UNIVERSITARIA'),
(27, 'DIRECCION DE TECNOLOGIAS DE INFORMACIÓN'),
(4, 'ESCUELA DE POSGRADO'),
(20, 'ESCUELA PROF. DE AMBIENTAL'),
(19, 'ESCUELA PROF. DE INGENIERIA DE SISTEMAS'),
(16, 'ESCUELA PROF. DE INGENIERIA INDUSTRIAL'),
(32, 'ESCUELA PROFESIONAL DE DERECHO'),
(15, 'ESCUELA PROFESIONAL DE ECONOMIA'),
(42, 'ESCUELA PROFESIONAL DE PSICOLOGIA'),
(35, 'FACULTAD CIENCIAS ECONOMICAS,ADMINISTRATIVAS Y CONTABLES'),
(21, 'FACULTAD DE CIENCIAS DE LA SALUD - DECANATO'),
(5, 'FACULTAD DE CIENCIAS SOCIALES - DECANATO'),
(3, 'FACULTAD DE DERECHO Y CIENCIA POLITICA'),
(18, 'FACULTAD DE INGENIERIA - DECANATO'),
(14, 'FACULTAD DE INGENIERIA - UNIDAD DE INVESTIGACIÓN'),
(1, 'FACULTAD DERECHO Y CIENCIAS POLITICA'),
(7, 'FCEAC - UNIDAD DE INVESTIGACIÓN'),
(13, 'FCEAC . SECRETARIA ADMINISTRATIVA'),
(17, 'FCEAC. SECRETARIA ACADEMICA'),
(31, 'INSTITUTO CIENTIFICO'),
(23, 'LABORATORIO BLOOMBERG'),
(45, 'LABORATORIO DE INVESTIGACIÓN EN NEUROCIENCIAS'),
(36, 'MUSEO DE CIENCIA Y TECNOLOGÍA EL MUNDO DE LA EXPERIMENTACIÓN'),
(10, 'OFICINA DE ESTRUCTURA Y OBRAS'),
(26, 'OFICINA DE INFRAESTRUCTURA Y OBRAS'),
(44, 'OFICINA DE MARKETING, PROMOCION E IMAGEN INSTITUCIONAL'),
(33, 'RECTORADO'),
(2, 'SECRETARÍA DE INSTRUCCIÓN'),
(9, 'UNIDAD DE ALMACEN'),
(8, 'UNIDAD DE CONTABILIDAD'),
(6, 'UNIDAD DE PATRIMONIO'),
(34, 'VICERRECTORADO DE INVESTIGACIÓN - COORDINACION DE TRANSFERENCIA TECNOLÓGICA Y PATENTES'),
(22, 'VRIN - DIRECCION DE EMPRENDIMIENTO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Convenios`
--

CREATE TABLE `Convenios` (
  `convenio_id` int(11) NOT NULL,
  `practicante_id` int(11) NOT NULL,
  `proceso_id` int(11) DEFAULT NULL,
  `tipo_practica` varchar(50) NOT NULL,
  `estado_convenio` varchar(30) NOT NULL,
  `induccion_completada` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `Convenios`
--

INSERT INTO `Convenios` (`convenio_id`, `practicante_id`, `proceso_id`, `tipo_practica`, `estado_convenio`, `induccion_completada`) VALUES
(1, 1, 1, 'PREPROFESIONAL', 'Vigente', 1),
(2, 2, 2, 'PREPROFESIONAL', 'Vigente', 1),
(3, 3, 3, 'PREPROFESIONAL', 'Vigente', 1),
(4, 4, 4, 'PREPROFESIONAL', 'Vigente', 1),
(5, 5, 5, 'PREPROFESIONAL', 'Vigente', 1),
(6, 6, 6, 'PREPROFESIONAL', 'Vigente', 1),
(7, 7, 7, 'PREPROFESIONAL', 'Vigente', 1),
(8, 8, 8, 'PREPROFESIONAL', 'Vigente', 1),
(9, 9, 9, 'PREPROFESIONAL', 'Vigente', 1),
(10, 10, 10, 'PREPROFESIONAL', 'Vigente', 1),
(11, 11, 11, 'PREPROFESIONAL', 'Vigente', 1),
(12, 12, 12, 'PREPROFESIONAL', 'Vigente', 1),
(13, 13, 13, 'PREPROFESIONAL', 'Vigente', 1),
(14, 14, 14, 'PREPROFESIONAL', 'Vigente', 1),
(15, 15, 15, 'PREPROFESIONAL', 'Vigente', 1),
(16, 16, 16, 'PREPROFESIONAL', 'Vigente', 1),
(17, 17, 17, 'PREPROFESIONAL', 'Vigente', 1),
(18, 18, 18, 'PREPROFESIONAL', 'Vigente', 1),
(19, 19, 19, 'PREPROFESIONAL', 'Vigente', 1),
(20, 20, 20, 'PREPROFESIONAL', 'Vigente', 1),
(21, 21, 21, 'PREPROFESIONAL', 'Vigente', 1),
(22, 22, 22, 'PREPROFESIONAL', 'Vigente', 1),
(23, 23, 23, 'PREPROFESIONAL', 'Vigente', 1),
(24, 24, 24, 'PREPROFESIONAL', 'Vigente', 1),
(25, 25, 25, 'PREPROFESIONAL', 'Vigente', 1),
(26, 26, 26, 'PREPROFESIONAL', 'Vigente', 1),
(27, 27, 27, 'PREPROFESIONAL', 'Vigente', 1),
(28, 28, 28, 'PREPROFESIONAL', 'Vigente', 1),
(29, 29, 29, 'PREPROFESIONAL', 'Vigente', 1),
(30, 30, 30, 'PREPROFESIONAL', 'Vigente', 1),
(31, 31, 31, 'PREPROFESIONAL', 'Vigente', 1),
(32, 32, 32, 'PREPROFESIONAL', 'Vigente', 1),
(33, 33, 33, 'PREPROFESIONAL', 'Vigente', 1),
(34, 34, 34, 'PREPROFESIONAL', 'Vigente', 1),
(35, 35, 35, 'PREPROFESIONAL', 'Vigente', 1),
(36, 36, 36, 'PROFESIONAL', 'Finalizado', 1),
(37, 37, 37, 'PROFESIONAL', 'Vigente', 1),
(38, 38, 38, 'PROFESIONAL', 'Vigente', 1),
(39, 39, 39, 'PROFESIONAL', 'Vigente', 1),
(40, 40, 40, 'PROFESIONAL', 'Vigente', 1),
(41, 41, 41, 'PROFESIONAL', 'Vigente', 1),
(42, 42, 42, 'PROFESIONAL', 'Vigente', 1),
(43, 43, 43, 'PROFESIONAL', 'Vigente', 1),
(44, 44, 44, 'PROFESIONAL', 'Vigente', 1),
(45, 45, 45, 'PROFESIONAL', 'Vigente', 1),
(46, 46, 46, 'PROFESIONAL', 'Vigente', 1),
(47, 47, 47, 'PROFESIONAL', 'Vigente', 1),
(48, 48, 48, 'PROFESIONAL', 'Vigente', 1),
(49, 49, 49, 'PROFESIONAL', 'Vigente', 1),
(50, 50, 50, 'PROFESIONAL', 'Vigente', 1),
(51, 51, 51, 'PROFESIONAL', 'Vigente', 1),
(52, 52, 52, 'PROFESIONAL', 'Vigente', 1),
(53, 53, 53, 'PROFESIONAL', 'Vigente', 1),
(54, 54, 54, 'PROFESIONAL', 'Vigente', 1),
(55, 55, 55, 'PROFESIONAL', 'Vigente', 1),
(56, 56, 56, 'PROFESIONAL', 'Vigente', 1),
(57, 57, 57, 'PROFESIONAL', 'Vigente', 1),
(58, 49, 58, 'PROFESIONAL', 'Vigente', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Documentos`
--

CREATE TABLE `Documentos` (
  `documento_id` int(11) NOT NULL,
  `practicante_id` int(11) NOT NULL,
  `proceso_id` int(11) DEFAULT NULL,
  `convenio_id` int(11) DEFAULT NULL,
  `adenda_id` int(11) DEFAULT NULL,
  `tipo_documento` varchar(50) NOT NULL,
  `url_archivo` varchar(255) NOT NULL,
  `fecha_carga` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `Documentos`
--

INSERT INTO `Documentos` (`documento_id`, `practicante_id`, `proceso_id`, `convenio_id`, `adenda_id`, `tipo_documento`, `url_archivo`, `fecha_carga`) VALUES
(1, 59, 66, NULL, NULL, 'CARTA_PRESENTACION', 'uploads/documentos/59_CARTA_PRESENTACION_66.pdf', '2025-10-27 14:27:09'),
(2, 59, 66, NULL, NULL, 'DNI', 'uploads/documentos/59_DNI_66.pdf', '2025-10-27 14:27:09'),
(3, 59, 66, NULL, NULL, 'CV', 'uploads/documentos/59_CV_66.pdf', '2025-10-27 14:27:09'),
(4, 59, 66, NULL, NULL, 'DECLARACIONES', 'uploads/documentos/59_DECLARACIONES_66.pdf', '2025-10-27 14:27:09'),
(5, 59, 67, NULL, NULL, 'CARTA_PRESENTACION', 'uploads/documentos/59_CARTA_PRESENTACION_67.pdf', '2025-10-27 14:33:40'),
(6, 59, 67, NULL, NULL, 'DNI', 'uploads/documentos/59_DNI_67.pdf', '2025-10-27 14:33:40'),
(7, 59, 67, NULL, NULL, 'CV', 'uploads/documentos/59_CV_67.pdf', '2025-10-27 14:33:40'),
(8, 59, 67, NULL, NULL, 'DECLARACIONES', 'uploads/documentos/59_DECLARACIONES_67.pdf', '2025-10-27 14:33:40'),
(9, 59, 68, NULL, NULL, 'CARTA_PRESENTACION', 'uploads/documentos/59_CARTA_PRESENTACION_68.pdf', '2025-10-27 14:35:53'),
(10, 59, 68, NULL, NULL, 'DNI', 'uploads/documentos/59_DNI_68.pdf', '2025-10-27 14:35:53'),
(11, 59, 68, NULL, NULL, 'CV', 'uploads/documentos/59_CV_68.pdf', '2025-10-27 14:35:53'),
(12, 59, 68, NULL, NULL, 'DECLARACIONES', 'uploads/documentos/59_DECLARACIONES_68.pdf', '2025-10-27 14:35:53'),
(13, 59, 69, NULL, NULL, 'CARTA_PRESENTACION', 'uploads/documentos/59_CARTA_PRESENTACION_69.pdf', '2025-10-27 14:36:55'),
(14, 59, 69, NULL, NULL, 'DNI', 'uploads/documentos/59_DNI_69.pdf', '2025-10-27 14:36:55'),
(15, 59, 69, NULL, NULL, 'CV', 'uploads/documentos/59_CV_69.pdf', '2025-10-27 14:36:55'),
(16, 59, 69, NULL, NULL, 'DECLARACIONES', 'uploads/documentos/59_DECLARACIONES_69.pdf', '2025-10-27 14:36:55'),
(17, 59, 70, NULL, NULL, 'CARTA_PRESENTACION', 'uploads/documentos/59_CARTA_PRESENTACION_70.pdf', '2025-10-27 14:40:05'),
(18, 59, 70, NULL, NULL, 'DNI', 'uploads/documentos/59_DNI_70.pdf', '2025-10-27 14:40:05'),
(19, 59, 70, NULL, NULL, 'CV', 'uploads/documentos/59_CV_70.pdf', '2025-10-27 14:40:05'),
(20, 59, 70, NULL, NULL, 'DECLARACIONES', 'uploads/documentos/59_DECLARACIONES_70.pdf', '2025-10-27 14:40:05'),
(21, 60, 71, NULL, NULL, 'CARTA_PRESENTACION', 'uploads/documentos/60_CARTA_PRESENTACION_71.pdf', '2025-10-27 14:44:21'),
(22, 60, 71, NULL, NULL, 'DNI', 'uploads/documentos/60_DNI_71.pdf', '2025-10-27 14:44:21'),
(23, 60, 71, NULL, NULL, 'CV', 'uploads/documentos/60_CV_71.pdf', '2025-10-27 14:44:21'),
(24, 60, 71, NULL, NULL, 'DECLARACIONES', 'uploads/documentos/60_DECLARACIONES_71.pdf', '2025-10-27 14:44:21'),
(25, 61, 72, NULL, NULL, 'DNI', 'uploads/documentos/61_DNI_72.pdf', '2025-10-27 14:49:02'),
(26, 61, 72, NULL, NULL, 'CV', 'uploads/documentos/61_CV_72.pdf', '2025-10-27 14:49:02'),
(27, 61, 72, NULL, NULL, 'DECLARACIONES', 'uploads/documentos/61_DECLARACIONES_72.pdf', '2025-10-27 14:49:02'),
(28, 61, 73, NULL, NULL, 'DNI', 'uploads/documentos/61_DNI_73.pdf', '2025-10-27 14:53:25'),
(29, 61, 73, NULL, NULL, 'CV', 'uploads/documentos/61_CV_73.pdf', '2025-10-27 14:53:25'),
(30, 61, 73, NULL, NULL, 'DECLARACIONES', 'uploads/documentos/61_DECLARACIONES_73.pdf', '2025-10-27 14:53:25'),
(31, 61, 73, NULL, NULL, 'CONSOLIDADO', 'uploads/documentos/61_CONSOLIDADO_73.pdf', '2025-10-27 14:53:25'),
(32, 61, 74, NULL, NULL, 'CARTA_PRESENTACION', 'uploads/documentos/61_CARTA_PRESENTACION_74.pdf', '2025-10-27 15:08:24'),
(33, 61, 74, NULL, NULL, 'DNI', 'uploads/documentos/61_DNI_74.pdf', '2025-10-27 15:08:24'),
(34, 61, 74, NULL, NULL, 'CV', 'uploads/documentos/61_CV_74.pdf', '2025-10-27 15:08:24'),
(35, 61, 74, NULL, NULL, 'DECLARACIONES', 'uploads/documentos/61_DECLARACIONES_74.pdf', '2025-10-27 15:08:24'),
(36, 61, 74, NULL, NULL, 'CONSOLIDADO', 'uploads/documentos/61_CONSOLIDADO_74.pdf', '2025-10-27 15:08:24'),
(37, 62, 75, NULL, NULL, 'CARTA_PRESENTACION', 'uploads/documentos/62_CARTA_PRESENTACION_75.pdf', '2025-10-27 15:11:21'),
(38, 62, 75, NULL, NULL, 'DNI', 'uploads/documentos/62_DNI_75.pdf', '2025-10-27 15:11:21'),
(39, 62, 75, NULL, NULL, 'CV', 'uploads/documentos/62_CV_75.pdf', '2025-10-27 15:11:21'),
(40, 62, 75, NULL, NULL, 'DECLARACIONES', 'uploads/documentos/62_DECLARACIONES_75.pdf', '2025-10-27 15:11:21'),
(41, 62, 75, NULL, NULL, 'CONSOLIDADO', 'uploads/documentos/62_CONSOLIDADO_75.pdf', '2025-10-27 15:11:21'),
(42, 63, 76, NULL, NULL, 'DNI', 'uploads/documentos/63_DNI_76.pdf', '2025-10-27 15:29:35'),
(43, 63, 76, NULL, NULL, 'CV', 'uploads/documentos/63_CV_76.pdf', '2025-10-27 15:29:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `EscuelasProfesionales`
--

CREATE TABLE `EscuelasProfesionales` (
  `escuela_id` int(11) NOT NULL,
  `universidad_id` int(11) DEFAULT NULL,
  `nombre` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `EscuelasProfesionales`
--

INSERT INTO `EscuelasProfesionales` (`escuela_id`, `universidad_id`, `nombre`) VALUES
(1, 1, 'Derecho'),
(2, 1, 'Administración'),
(3, 1, 'Contabilidad'),
(4, 1, 'Economía'),
(5, 1, 'Ingeniería Civil'),
(6, 1, 'Ingeniería de Sistemas'),
(7, 1, 'Ingeniería Industrial'),
(8, 1, 'Marketing'),
(9, 1, 'Finanzas'),
(10, 1, 'Ingeniería Ambiental'),
(11, 1, 'Arquitectura'),
(12, 1, 'Psicología'),
(13, 1, 'Enfermería'),
(14, 1, 'Estomatología'),
(15, 1, 'Medicina Humana'),
(16, 1, 'Obstetricia'),
(17, 1, 'Tecnología Médica'),
(18, 1, 'Turismo'),
(19, 1, 'Educación'),
(20, 1, 'Administración de Negocios Internacionales');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Locales`
--

CREATE TABLE `Locales` (
  `local_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `Locales`
--

INSERT INTO `Locales` (`local_id`, `nombre`) VALUES
(1, 'Sede Principal Cusco');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `PeriodosConvenio`
--

CREATE TABLE `PeriodosConvenio` (
  `periodo_id` int(11) NOT NULL,
  `convenio_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `local_id` int(11) DEFAULT NULL,
  `area_id` int(11) DEFAULT NULL,
  `estado_periodo` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `PeriodosConvenio`
--

INSERT INTO `PeriodosConvenio` (`periodo_id`, `convenio_id`, `fecha_inicio`, `fecha_fin`, `local_id`, `area_id`, `estado_periodo`) VALUES
(1, 1, '2025-04-07', '2026-04-06', 1, 1, 'Activo'),
(2, 2, '2025-04-10', '2026-04-09', 1, 2, 'Activo'),
(3, 3, '2025-06-02', '2025-12-01', 1, 3, 'Activo'),
(4, 4, '2025-06-02', '2025-06-30', 1, 3, 'Finalizado'),
(5, 4, '2025-08-01', '2026-01-01', 1, 3, 'Activo'),
(6, 5, '2025-08-20', '2025-12-19', 1, 4, 'Activo'),
(7, 6, '2025-08-20', '2025-12-19', 1, 5, 'Activo'),
(8, 7, '2025-08-20', '2025-12-19', 1, 6, 'Activo'),
(9, 8, '2025-08-20', '2025-12-19', 1, 7, 'Activo'),
(10, 9, '2025-08-20', '2025-12-19', 1, 8, 'Activo'),
(11, 10, '2025-08-20', '2025-12-19', 1, 9, 'Activo'),
(12, 11, '2025-08-20', '2025-12-19', 1, 6, 'Activo'),
(13, 12, '2025-08-20', '2025-12-19', 1, 10, 'Activo'),
(14, 13, '2025-08-20', '2025-12-19', 1, 11, 'Activo'),
(15, 14, '2025-08-20', '2025-12-19', 1, 12, 'Activo'),
(16, 15, '2025-08-20', '2025-12-19', 1, 13, 'Activo'),
(17, 16, '2025-08-20', '2025-12-19', 1, 14, 'Activo'),
(18, 17, '2025-08-20', '2025-12-19', 1, 15, 'Activo'),
(19, 18, '2025-08-20', '2025-12-19', 1, 6, 'Activo'),
(20, 19, '2025-08-20', '2025-12-19', 1, 16, 'Activo'),
(21, 20, '2025-08-20', '2025-12-19', 1, 17, 'Activo'),
(22, 21, '2025-08-20', '2025-12-19', 1, 18, 'Activo'),
(23, 22, '2025-08-20', '2025-12-19', 1, 19, 'Activo'),
(24, 23, '2025-08-20', '2025-12-19', 1, 20, 'Activo'),
(25, 24, '2025-08-20', '2025-12-19', 1, 21, 'Activo'),
(26, 25, '2025-08-20', '2025-12-19', 1, 22, 'Activo'),
(27, 26, '2025-08-25', '2025-12-24', 1, 23, 'Activo'),
(28, 27, '2025-08-25', '2025-12-24', 1, 24, 'Activo'),
(29, 28, '2025-08-25', '2025-12-24', 1, 25, 'Activo'),
(30, 29, '2025-10-01', '2026-01-31', 1, 26, 'Activo'),
(31, 30, '2025-10-01', '2026-01-31', 1, 27, 'Activo'),
(32, 31, '2025-10-01', '2026-01-31', 1, 28, 'Activo'),
(33, 32, '2025-10-01', '2026-01-31', 1, 29, 'Activo'),
(34, 33, '2025-10-01', '2026-01-31', 1, 30, 'Activo'),
(35, 34, '2025-10-14', '2026-02-14', 1, 31, 'Activo'),
(36, 35, '2025-10-14', '2026-02-14', 1, 32, 'Activo'),
(37, 36, '2024-10-24', '2025-10-23', 1, 33, 'Finalizado'),
(38, 37, '2025-03-10', '2026-04-09', 1, 20, 'Activo'),
(39, 38, '2025-03-10', '2026-04-09', 1, 34, 'Activo'),
(40, 39, '2025-04-07', '2025-11-07', 1, 35, 'Activo'),
(41, 40, '2025-04-10', '2025-06-29', 1, 36, 'Finalizado'),
(42, 40, '2025-08-01', '2025-11-09', 1, 36, 'Activo'),
(43, 41, '2025-05-15', '2025-11-14', 1, 26, 'Activo'),
(44, 42, '2025-05-15', '2025-11-14', 1, 37, 'Activo'),
(45, 43, '2025-05-15', '2025-06-30', 1, 38, 'Finalizado'),
(46, 43, '2025-08-01', '2025-12-14', 1, 38, 'Activo'),
(47, 44, '2025-07-15', '2026-01-14', 1, 39, 'Activo'),
(48, 45, '2025-07-15', '2026-01-14', 1, 40, 'Activo'),
(49, 46, '2025-08-20', '2026-02-19', 1, 41, 'Activo'),
(50, 47, '2025-08-20', '2026-02-19', 1, 4, 'Activo'),
(51, 48, '2025-08-25', '2026-02-25', 1, 42, 'Activo'),
(52, 49, '2025-08-25', '2026-02-25', 1, 43, 'Activo'),
(53, 50, '2025-08-25', '2026-02-25', 1, 44, 'Activo'),
(54, 51, '2025-09-01', '2026-02-28', 1, 45, 'Activo'),
(55, 52, '2025-10-01', '2026-04-01', 1, 46, 'Activo'),
(56, 53, '2025-10-01', '2026-03-31', 1, 46, 'Activo'),
(57, 54, '2025-10-01', '2026-03-31', 1, 46, 'Activo'),
(58, 55, '2025-10-09', '2026-04-08', 1, 4, 'Activo'),
(59, 56, '2025-10-09', '2026-04-08', 1, 27, 'Activo'),
(60, 57, '2025-10-09', '2026-04-08', 1, 27, 'Activo'),
(61, 58, '2025-10-26', '2025-10-31', 1, 43, 'Finalizado'),
(62, 58, '2025-11-01', '2026-02-01', 1, 40, 'Futuro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Practicantes`
--

CREATE TABLE `Practicantes` (
  `practicante_id` int(11) NOT NULL,
  `dni` varchar(15) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `promedio_general` decimal(4,2) DEFAULT NULL,
  `estado_general` varchar(30) NOT NULL DEFAULT 'Candidato',
  `escuela_profesional_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `Practicantes`
--

INSERT INTO `Practicantes` (`practicante_id`, `dni`, `nombres`, `apellidos`, `fecha_nacimiento`, `email`, `telefono`, `promedio_general`, `estado_general`, `escuela_profesional_id`) VALUES
(1, '72230497', 'CARLOS ALBERTO', 'ACHAHUI PILCO', NULL, NULL, NULL, NULL, 'Activo', 1),
(2, '72948354', 'JAKE DANGHELO', 'GUTIERREZ ARDILES', NULL, NULL, NULL, NULL, 'Activo', 1),
(3, '72078205', 'JOEL FELICIANO', 'CARBAJAL PACHECO', NULL, NULL, NULL, NULL, 'Activo', 1),
(4, '72499917', 'ALEJANDRO RODRIGO', 'PILARES SUTTA', NULL, NULL, NULL, NULL, 'Activo', 1),
(5, '75258586', 'MARIANA HETAIRA', 'DAVALOS FLORES', NULL, NULL, NULL, NULL, 'Activo', 2),
(6, '70836383', 'FABIO OMAR', 'PALOMINO HUALLPA', NULL, NULL, NULL, NULL, 'Activo', 2),
(7, '72648910', 'GONZALO SEBASTIAN', 'GUTIERREZ VELARDE', NULL, NULL, NULL, NULL, 'Activo', 2),
(8, '70371906', 'GUSTAVO EDUARDO', 'VILLA FLORES', NULL, NULL, NULL, NULL, 'Activo', 2),
(9, '73378414', 'YAMIR RODRIGO', 'ARIZABAL LUNA', NULL, NULL, NULL, NULL, 'Activo', 3),
(10, '44359899', 'RUTH NAYDA', 'PORTILLA CALLAPIÑA', NULL, NULL, NULL, NULL, 'Activo', 3),
(11, '73084553', 'DIEGO DAVID', 'FERRO DURAND', NULL, NULL, NULL, NULL, 'Activo', 4),
(12, '72227539', 'NILSON JAMIL', 'CONDORI PEÑA', NULL, NULL, NULL, NULL, 'Activo', 5),
(13, '76314517', 'ABEL EDSON', 'ROJAS CORNEJO', NULL, NULL, NULL, NULL, 'Activo', 6),
(14, '77675435', 'MARK JOEL', 'SEGOVIA PALACIOS', NULL, NULL, NULL, NULL, 'Activo', 6),
(15, '70667512', 'CARLOS EDUARDO', 'CARDENAS LOAIZA', NULL, NULL, NULL, NULL, 'Activo', 6),
(16, '73051120', 'ANTHONY WILBERT', 'OSTERIANO CHAMPI', NULL, NULL, NULL, NULL, 'Activo', 6),
(17, '73194622', 'DIEGO ANDRES', 'TRESIERRA ZAMORA', NULL, NULL, NULL, NULL, 'Activo', 6),
(18, '74075588', 'YHOJAN', 'AGUILAR CCORIMANYA', NULL, NULL, NULL, NULL, 'Activo', 6),
(19, '76414624', 'ANTHONY JARETH', 'LA TORRE VELAZCO', NULL, NULL, NULL, NULL, 'Activo', 6),
(20, '76181119', 'KELVIN RONNY', 'QUISPE BRAVO', NULL, NULL, NULL, NULL, 'Activo', 6),
(21, '71078049', 'JOSE DAVID', 'SALAZAR BENAVENTE', NULL, NULL, NULL, NULL, 'Activo', 6),
(22, '75750127', 'JEAN PIERRO', 'TENAZOA TORRES', NULL, NULL, NULL, NULL, 'Activo', 6),
(23, '72369883', 'LUIS GERARDO', 'VILCA RAMOS', NULL, NULL, NULL, NULL, 'Activo', 6),
(24, '76405136', 'JHON ALEXIS', 'DIAZ CHURA', NULL, NULL, NULL, NULL, 'Activo', 6),
(25, '72906088', 'ESTEFANNY BEATRIZ', 'TUMPE BELLOTA', NULL, NULL, NULL, NULL, 'Activo', 8),
(26, '72718000', 'LUIS VICENTE', 'RIVERA VARGAS', NULL, NULL, NULL, NULL, 'Activo', 9),
(27, '71483342', 'DIEGO FERNANDO', 'CASTAÑEDA ANAYA', NULL, NULL, NULL, NULL, 'Activo', 10),
(28, '76913825', 'ALEXANDER JOSE', 'CHUCHULLO ROJAS', NULL, NULL, NULL, NULL, 'Activo', 7),
(29, '76398393', 'ANAMEY', 'AGUILAR CASTRO', NULL, NULL, NULL, NULL, 'Activo', 5),
(30, '73343819', 'WILBERT JUNIOR', 'CARDENAS ALEJO', NULL, NULL, NULL, NULL, 'Activo', 6),
(31, '73060349', 'ALDAIR JON', 'HUAMAN CACERES', NULL, NULL, NULL, NULL, 'Activo', 6),
(32, '72783940', 'STEFANO', 'MAR CHIARELLA', NULL, NULL, NULL, NULL, 'Activo', 6),
(33, '72743809', 'LUCIANA VALERIA', 'ZUÑIGA CARDENAS', NULL, NULL, NULL, NULL, 'Activo', 6),
(34, '71749332', 'YELANI KAOMI', 'SILVA PAZ', NULL, NULL, NULL, NULL, 'Activo', 7),
(35, '74148476', 'ZAYIN VICTOR', 'CURO MENDOZA', NULL, NULL, NULL, NULL, 'Activo', 6),
(36, '43008574', 'VICTOR RAUL', 'ZAVALETA MEZA', NULL, NULL, NULL, NULL, 'Cesado', 1),
(37, '70938202', 'MIKE MARCO', 'CONDORI CONTRERAS', NULL, NULL, NULL, NULL, 'Activo', 6),
(38, '70428743', 'BRESSIA PAOLY', 'QUISPE ARTEAGA', NULL, NULL, NULL, NULL, 'Activo', 2),
(39, '71559404', 'ANTHONY JACKSON', 'VALENCIA CASTILLO', NULL, NULL, NULL, NULL, 'Activo', 4),
(40, '70339815', 'KELMA ESTEFANNY', 'MENDOZA SEGOVIA', NULL, NULL, NULL, NULL, 'Activo', 6),
(41, '70910347', 'ANGIE JUNETH', 'HERNANDEZ MECHATE', NULL, NULL, NULL, NULL, 'Activo', 11),
(42, '72481448', 'JANINE ANGELICA', 'RODRIGUEZ ORMACHEA', NULL, NULL, NULL, NULL, 'Activo', 6),
(43, '75824283', 'JOSHET KEVIN', 'CCOYORE LLANO', NULL, NULL, NULL, NULL, 'Activo', 6),
(44, '71631974', 'YHERALD CESAR', 'CACERES MONTEJO', NULL, NULL, NULL, NULL, 'Activo', 6),
(45, '73195005', 'DANIELA', 'CASTRO VELARDE', NULL, NULL, NULL, NULL, 'Activo', 2),
(46, '73084555', 'RUDY EDGARDO', 'FERRO DURAND', NULL, NULL, NULL, NULL, 'Activo', 3),
(47, '70412295', 'KATHERINE MILAGROS', 'ALMANZA COLLAVINOS', NULL, NULL, NULL, NULL, 'Activo', 4),
(48, '72656365', 'ARNOLD', 'ALFARO TORRES', NULL, NULL, NULL, NULL, 'Activo', 6),
(49, '73029913', 'JAIRO FACUNDO', 'VILCHEZ CACERES', NULL, NULL, NULL, NULL, 'Activo', 6),
(50, '77020051', 'VICTOR HUGO', 'VALDEIGLESIAS HUAMAN', NULL, NULL, NULL, NULL, 'Activo', 8),
(51, '73029896', 'BETY JAHAIRA', 'GUZMAN PARO', NULL, NULL, NULL, NULL, 'Activo', 12),
(52, '72303061', 'MIGUEL ANGEL', 'VARGAS PANTOJA', NULL, NULL, NULL, NULL, 'Activo', 6),
(53, '42447634', 'EVELYN MERCEDES', 'ALARCON YAQUETTO', NULL, NULL, NULL, NULL, 'Activo', 6),
(54, '70921715', 'DIEGO DANIEL', 'LOZANO PACHECO', NULL, NULL, NULL, NULL, 'Activo', 7),
(55, '70445736', 'GONZALO JAIR', 'ALMANZA CUNO', NULL, NULL, NULL, NULL, 'Activo', 6),
(56, '72089385', 'SERGIO MARCELO', 'DURAND CASTRO', NULL, NULL, NULL, NULL, 'Activo', 6),
(57, '73710205', 'GEOVANNI DAYAN', 'VALLE ALFARO', NULL, NULL, NULL, NULL, 'Activo', 6),
(58, '75836534', 'Alessandro', 'Fernandez la Rosa', '2002-06-03', '019200655@gmail.com', '973269119', 20.00, 'Candidato', 6),
(59, '76472673', 'Juan', 'Fernandez la Rosa', '2001-07-03', 'juanq@gmail.com', '975462734', 16.00, 'Candidato', 19),
(60, '40114731', 'Juan', 'Quispe', '1970-02-03', 'gfebres@uandina.edu.pe', '973269119', 15.00, 'Candidato', 8),
(61, '23805271', 'juan', 'zorra', '1970-02-03', 'juan@gmail.com', '973268181', 13.00, 'Candidato', 16),
(62, '43481626', 'juan', 'zorrita', '1960-02-03', 'jzorr@gmail.com', '974367278', 16.00, 'Candidato', 10),
(63, '34534634', 'Alessandro super', 'gay homosexual', '2020-04-26', 'jairo@gmail.com', '999935738', 12.00, 'Candidato', 17);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ProcesosReclutamiento`
--

CREATE TABLE `ProcesosReclutamiento` (
  `proceso_id` int(11) NOT NULL,
  `practicante_id` int(11) NOT NULL,
  `fecha_postulacion` date DEFAULT NULL,
  `fecha_entrevista` date DEFAULT NULL,
  `puntuacion_final_entrevista` decimal(4,2) DEFAULT NULL,
  `estado_proceso` enum('En Evaluación','Aceptado','Rechazado','Pendiente') NOT NULL,
  `tipo_practica` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ProcesosReclutamiento`
--

INSERT INTO `ProcesosReclutamiento` (`proceso_id`, `practicante_id`, `fecha_postulacion`, `fecha_entrevista`, `puntuacion_final_entrevista`, `estado_proceso`, `tipo_practica`) VALUES
(1, 1, '2025-04-01', NULL, NULL, 'Aceptado', NULL),
(2, 2, '2025-04-01', NULL, NULL, 'Aceptado', NULL),
(3, 3, '2025-05-20', NULL, NULL, 'Aceptado', NULL),
(4, 4, '2025-05-20', NULL, NULL, 'Aceptado', NULL),
(5, 5, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(6, 6, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(7, 7, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(8, 8, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(9, 9, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(10, 10, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(11, 11, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(12, 12, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(13, 13, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(14, 14, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(15, 15, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(16, 16, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(17, 17, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(18, 18, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(19, 19, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(20, 20, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(21, 21, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(22, 22, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(23, 23, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(24, 24, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(25, 25, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(26, 26, '2025-08-15', NULL, NULL, 'Aceptado', NULL),
(27, 27, '2025-08-15', NULL, NULL, 'Aceptado', NULL),
(28, 28, '2025-08-15', NULL, NULL, 'Aceptado', NULL),
(29, 29, '2025-09-20', NULL, NULL, 'Aceptado', NULL),
(30, 30, '2025-09-20', NULL, NULL, 'Aceptado', NULL),
(31, 31, '2025-09-20', NULL, NULL, 'Aceptado', NULL),
(32, 32, '2025-09-20', NULL, NULL, 'Aceptado', NULL),
(33, 33, '2025-09-20', NULL, NULL, 'Aceptado', NULL),
(34, 34, '2025-10-05', NULL, NULL, 'Aceptado', NULL),
(35, 35, '2025-10-05', NULL, NULL, 'Aceptado', NULL),
(36, 36, '2024-10-15', NULL, NULL, 'Aceptado', NULL),
(37, 37, '2025-03-01', NULL, NULL, 'Aceptado', NULL),
(38, 38, '2025-03-01', NULL, NULL, 'Aceptado', NULL),
(39, 39, '2025-04-01', NULL, NULL, 'Aceptado', NULL),
(40, 40, '2025-04-01', NULL, NULL, 'Aceptado', NULL),
(41, 41, '2025-05-05', NULL, NULL, 'Aceptado', NULL),
(42, 42, '2025-05-05', NULL, NULL, 'Aceptado', NULL),
(43, 43, '2025-05-05', NULL, NULL, 'Aceptado', NULL),
(44, 44, '2025-07-05', NULL, NULL, 'Aceptado', NULL),
(45, 45, '2025-07-05', NULL, NULL, 'Aceptado', NULL),
(46, 46, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(47, 47, '2025-08-10', NULL, NULL, 'Aceptado', NULL),
(48, 48, '2025-08-15', NULL, NULL, 'Aceptado', NULL),
(49, 49, '2025-08-15', NULL, NULL, 'Aceptado', NULL),
(50, 50, '2025-08-15', NULL, NULL, 'Aceptado', NULL),
(51, 51, '2025-08-20', NULL, NULL, 'Aceptado', NULL),
(52, 52, '2025-09-20', NULL, NULL, 'Aceptado', NULL),
(53, 53, '2025-09-20', NULL, NULL, 'Aceptado', NULL),
(54, 54, '2025-09-20', NULL, NULL, 'Aceptado', NULL),
(55, 55, '2025-10-01', NULL, NULL, 'Aceptado', NULL),
(56, 56, '2025-10-01', NULL, NULL, 'Aceptado', NULL),
(57, 57, '2025-10-01', NULL, NULL, 'Aceptado', NULL),
(58, 49, '2025-10-24', NULL, 14.70, 'Aceptado', NULL),
(75, 62, '2025-10-07', NULL, NULL, 'En Evaluación', 'PREPROFESIONAL'),
(76, 63, '2025-10-27', NULL, NULL, 'En Evaluación', 'PREPROFESIONAL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ResultadosEntrevista`
--

CREATE TABLE `ResultadosEntrevista` (
  `resultado_id` int(11) NOT NULL,
  `proceso_id` int(11) NOT NULL,
  `campo_1_nombre` varchar(50) DEFAULT 'Criterio 1',
  `campo_1_nota` decimal(4,2) DEFAULT NULL,
  `campo_2_nombre` varchar(50) DEFAULT 'Criterio 2',
  `campo_2_nota` decimal(4,2) DEFAULT NULL,
  `campo_3_nombre` varchar(50) DEFAULT 'Criterio 3',
  `campo_3_nota` decimal(4,2) DEFAULT NULL,
  `campo_4_nombre` varchar(50) DEFAULT 'Criterio 4',
  `campo_4_nota` decimal(4,2) DEFAULT NULL,
  `campo_5_nombre` varchar(50) DEFAULT 'Criterio 5',
  `campo_5_nota` decimal(4,2) DEFAULT NULL,
  `campo_6_nombre` varchar(50) DEFAULT 'Criterio 6',
  `campo_6_nota` decimal(4,2) DEFAULT NULL,
  `campo_7_nombre` varchar(50) DEFAULT 'Criterio 7',
  `campo_7_nota` decimal(4,2) DEFAULT NULL,
  `campo_8_nombre` varchar(50) DEFAULT 'Criterio 8',
  `campo_8_nota` decimal(4,2) DEFAULT NULL,
  `campo_9_nombre` varchar(50) DEFAULT 'Criterio 9',
  `campo_9_nota` decimal(4,2) DEFAULT NULL,
  `campo_10_nombre` varchar(50) DEFAULT 'Criterio 10',
  `campo_10_nota` decimal(4,2) DEFAULT NULL,
  `comentarios_adicionales` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ResultadosEntrevista`
--

INSERT INTO `ResultadosEntrevista` (`resultado_id`, `proceso_id`, `campo_1_nombre`, `campo_1_nota`, `campo_2_nombre`, `campo_2_nota`, `campo_3_nombre`, `campo_3_nota`, `campo_4_nombre`, `campo_4_nota`, `campo_5_nombre`, `campo_5_nota`, `campo_6_nombre`, `campo_6_nota`, `campo_7_nombre`, `campo_7_nota`, `campo_8_nombre`, `campo_8_nota`, `campo_9_nombre`, `campo_9_nota`, `campo_10_nombre`, `campo_10_nota`, `comentarios_adicionales`) VALUES
(1, 1, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(2, 2, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(3, 3, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(4, 4, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(5, 5, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(6, 6, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(7, 7, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(8, 8, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(9, 9, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(10, 10, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(11, 11, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(12, 12, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(13, 13, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(14, 14, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(15, 15, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(16, 16, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(17, 17, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(18, 18, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(19, 19, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(20, 20, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(21, 21, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(22, 22, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(23, 23, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(24, 24, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(25, 25, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(26, 26, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(27, 27, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(28, 28, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(29, 29, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(30, 30, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(31, 31, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(32, 32, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(33, 33, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(34, 34, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(35, 35, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(36, 36, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(37, 37, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(38, 38, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(39, 39, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(40, 40, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(41, 41, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(42, 42, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(43, 43, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(44, 44, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(45, 45, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(46, 46, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(47, 47, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(48, 48, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(49, 49, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(50, 50, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(51, 51, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(52, 52, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(53, 53, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(54, 54, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(55, 55, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(56, 56, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(57, 57, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(58, 58, 'Criterio 1', 14.00, 'Criterio 2', 16.00, 'Criterio 3', 17.00, 'Criterio 4', 13.00, 'Criterio 5', 15.00, 'Criterio 6', 13.00, 'Criterio 7', 16.00, 'Criterio 8', 13.00, 'Criterio 9', 16.00, 'Criterio 10', 14.00, 'es muy capo'),
(59, 59, 'Criterio 1', 20.00, 'Criterio 2', 20.00, 'Criterio 3', 20.00, 'Criterio 4', 20.00, 'Criterio 5', 20.00, 'Criterio 6', 20.00, 'Criterio 7', 20.00, 'Criterio 8', 20.00, 'Criterio 9', 20.00, 'Criterio 10', 20.00, ''),
(60, 60, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(61, 61, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(62, 62, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(63, 63, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(64, 64, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(65, 65, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(66, 66, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(67, 67, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(68, 68, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(69, 69, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(70, 70, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(71, 71, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(72, 72, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(73, 73, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(74, 74, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(75, 75, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL),
(76, 76, 'Criterio 1', NULL, 'Criterio 2', NULL, 'Criterio 3', NULL, 'Criterio 4', NULL, 'Criterio 5', NULL, 'Criterio 6', NULL, 'Criterio 7', NULL, 'Criterio 8', NULL, 'Criterio 9', NULL, 'Criterio 10', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Universidades`
--

CREATE TABLE `Universidades` (
  `universidad_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `Universidades`
--

INSERT INTO `Universidades` (`universidad_id`, `nombre`) VALUES
(1, 'Universidad Andina del Cusco');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `Adendas`
--
ALTER TABLE `Adendas`
  ADD PRIMARY KEY (`adenda_id`),
  ADD KEY `convenio_id` (`convenio_id`);

--
-- Indices de la tabla `Areas`
--
ALTER TABLE `Areas`
  ADD PRIMARY KEY (`area_id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `Convenios`
--
ALTER TABLE `Convenios`
  ADD PRIMARY KEY (`convenio_id`),
  ADD UNIQUE KEY `proceso_id` (`proceso_id`),
  ADD KEY `practicante_id` (`practicante_id`);

--
-- Indices de la tabla `Documentos`
--
ALTER TABLE `Documentos`
  ADD PRIMARY KEY (`documento_id`),
  ADD KEY `practicante_id` (`practicante_id`),
  ADD KEY `convenio_id` (`convenio_id`),
  ADD KEY `adenda_id` (`adenda_id`),
  ADD KEY `proceso_id` (`proceso_id`);

--
-- Indices de la tabla `EscuelasProfesionales`
--
ALTER TABLE `EscuelasProfesionales`
  ADD PRIMARY KEY (`escuela_id`),
  ADD KEY `universidad_id` (`universidad_id`);

--
-- Indices de la tabla `Locales`
--
ALTER TABLE `Locales`
  ADD PRIMARY KEY (`local_id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `PeriodosConvenio`
--
ALTER TABLE `PeriodosConvenio`
  ADD PRIMARY KEY (`periodo_id`),
  ADD KEY `convenio_id` (`convenio_id`),
  ADD KEY `local_id` (`local_id`),
  ADD KEY `area_id` (`area_id`);

--
-- Indices de la tabla `Practicantes`
--
ALTER TABLE `Practicantes`
  ADD PRIMARY KEY (`practicante_id`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD KEY `escuela_profesional_id` (`escuela_profesional_id`);

--
-- Indices de la tabla `ProcesosReclutamiento`
--
ALTER TABLE `ProcesosReclutamiento`
  ADD PRIMARY KEY (`proceso_id`),
  ADD KEY `practicante_id` (`practicante_id`);

--
-- Indices de la tabla `ResultadosEntrevista`
--
ALTER TABLE `ResultadosEntrevista`
  ADD PRIMARY KEY (`resultado_id`),
  ADD KEY `proceso_id` (`proceso_id`);

--
-- Indices de la tabla `Universidades`
--
ALTER TABLE `Universidades`
  ADD PRIMARY KEY (`universidad_id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `Adendas`
--
ALTER TABLE `Adendas`
  MODIFY `adenda_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `Areas`
--
ALTER TABLE `Areas`
  MODIFY `area_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `Convenios`
--
ALTER TABLE `Convenios`
  MODIFY `convenio_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de la tabla `Documentos`
--
ALTER TABLE `Documentos`
  MODIFY `documento_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `EscuelasProfesionales`
--
ALTER TABLE `EscuelasProfesionales`
  MODIFY `escuela_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `Locales`
--
ALTER TABLE `Locales`
  MODIFY `local_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `PeriodosConvenio`
--
ALTER TABLE `PeriodosConvenio`
  MODIFY `periodo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT de la tabla `Practicantes`
--
ALTER TABLE `Practicantes`
  MODIFY `practicante_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de la tabla `ProcesosReclutamiento`
--
ALTER TABLE `ProcesosReclutamiento`
  MODIFY `proceso_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de la tabla `ResultadosEntrevista`
--
ALTER TABLE `ResultadosEntrevista`
  MODIFY `resultado_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de la tabla `Universidades`
--
ALTER TABLE `Universidades`
  MODIFY `universidad_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `Adendas`
--
ALTER TABLE `Adendas`
  ADD CONSTRAINT `Adendas_ibfk_1` FOREIGN KEY (`convenio_id`) REFERENCES `Convenios` (`convenio_id`);

--
-- Filtros para la tabla `Convenios`
--
ALTER TABLE `Convenios`
  ADD CONSTRAINT `Convenios_ibfk_1` FOREIGN KEY (`practicante_id`) REFERENCES `Practicantes` (`practicante_id`),
  ADD CONSTRAINT `Convenios_ibfk_2` FOREIGN KEY (`proceso_id`) REFERENCES `ProcesosReclutamiento` (`proceso_id`);

--
-- Filtros para la tabla `Documentos`
--
ALTER TABLE `Documentos`
  ADD CONSTRAINT `Documentos_ibfk_1` FOREIGN KEY (`practicante_id`) REFERENCES `Practicantes` (`practicante_id`),
  ADD CONSTRAINT `Documentos_ibfk_2` FOREIGN KEY (`convenio_id`) REFERENCES `Convenios` (`convenio_id`),
  ADD CONSTRAINT `Documentos_ibfk_3` FOREIGN KEY (`adenda_id`) REFERENCES `Adendas` (`adenda_id`),
  ADD CONSTRAINT `Documentos_ibfk_4` FOREIGN KEY (`proceso_id`) REFERENCES `ProcesosReclutamiento` (`proceso_id`);

--
-- Filtros para la tabla `EscuelasProfesionales`
--
ALTER TABLE `EscuelasProfesionales`
  ADD CONSTRAINT `EscuelasProfesionales_ibfk_1` FOREIGN KEY (`universidad_id`) REFERENCES `Universidades` (`universidad_id`);

--
-- Filtros para la tabla `PeriodosConvenio`
--
ALTER TABLE `PeriodosConvenio`
  ADD CONSTRAINT `PeriodosConvenio_ibfk_1` FOREIGN KEY (`convenio_id`) REFERENCES `Convenios` (`convenio_id`),
  ADD CONSTRAINT `PeriodosConvenio_ibfk_2` FOREIGN KEY (`local_id`) REFERENCES `Locales` (`local_id`),
  ADD CONSTRAINT `PeriodosConvenio_ibfk_3` FOREIGN KEY (`area_id`) REFERENCES `Areas` (`area_id`);

--
-- Filtros para la tabla `ProcesosReclutamiento`
--
ALTER TABLE `ProcesosReclutamiento`
  ADD CONSTRAINT `ProcesosReclutamiento_ibfk_1` FOREIGN KEY (`practicante_id`) REFERENCES `Practicantes` (`practicante_id`);

--
-- Filtros para la tabla `ResultadosEntrevista`
--
ALTER TABLE `ResultadosEntrevista`
  ADD CONSTRAINT `ResultadosEntrevista_ibfk_1` FOREIGN KEY (`proceso_id`) REFERENCES `ProcesosReclutamiento` (`proceso_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
