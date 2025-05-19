-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: mestres
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alumnos`
--

DROP TABLE IF EXISTS `alumnos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alumnos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(150) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT 'default.png',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `alumnos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumnos`
--

LOCK TABLES `alumnos` WRITE;
/*!40000 ALTER TABLE `alumnos` DISABLE KEYS */;
INSERT INTO `alumnos` VALUES (1,2,'Antonio','Lorenzo','1988-01-11','antonio@gmail.com','617051534','Avenida Rambleta 4','',NULL,'2025-05-09 17:30:49','2025-05-11 09:25:30',1),(2,2,'Mercedes','Gaita López','1989-12-12','merce@gmail.com','645051534','Avenida Rambleta 4','Es muy buena tocando la flauta',NULL,'2025-05-09 17:32:12','2025-05-11 09:25:22',1),(3,2,'Romero','Lukaku','1999-08-15','romero@gmail','617051534','Avenida Rambleta 4','',NULL,'2025-05-09 17:42:30','2025-05-11 09:25:37',1),(4,2,'Manolo','Comes Kobal',NULL,'aaa@gmail.com','654789456','','',NULL,'2025-05-10 15:38:24','2025-05-11 09:25:17',1),(5,2,'Antonio','Lorenzo','2025-05-23','antonio.esteban.lorenzo.88@gmail.com','617051534','Avenida Rambleta 4','','alumno_1747157466_682381da6d0d4.jpeg','2025-05-13 19:31:06','2025-05-13 19:31:06',1),(6,2,'Rodrigo','Moreno Romeu','1988-02-12','Usuario1@gmail.com','654789654','Callle palo, 4','','alumno_1747157625_68238279a0955.jpg','2025-05-13 19:33:45','2025-05-13 19:33:45',1);
/*!40000 ALTER TABLE `alumnos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alumnos_grupos`
--

DROP TABLE IF EXISTS `alumnos_grupos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alumnos_grupos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alumno_id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `fecha_asignacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `alumno_grupo` (`alumno_id`,`grupo_id`),
  KEY `grupo_id` (`grupo_id`),
  CONSTRAINT `alumnos_grupos_ibfk_1` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alumnos_grupos_ibfk_2` FOREIGN KEY (`grupo_id`) REFERENCES `grupos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumnos_grupos`
--

LOCK TABLES `alumnos_grupos` WRITE;
/*!40000 ALTER TABLE `alumnos_grupos` DISABLE KEYS */;
/*!40000 ALTER TABLE `alumnos_grupos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asignaturas`
--

DROP TABLE IF EXISTS `asignaturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asignaturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `color` varchar(20) DEFAULT '#3498db',
  `icono` varchar(50) DEFAULT 'book',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `asignaturas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asignaturas`
--

LOCK TABLES `asignaturas` WRITE;
/*!40000 ALTER TABLE `asignaturas` DISABLE KEYS */;
INSERT INTO `asignaturas` VALUES (1,2,'Programación','','#89fb90','laptop-code','2025-05-09 18:05:08','2025-05-09 20:41:08',1),(2,2,'Bases de Datos','','#afd5ee','globe','2025-05-09 18:06:11','2025-05-09 18:06:11',1),(3,2,'Diseño','Creamos nuevas páginas con gusto','#e6b333','book','2025-05-09 18:06:59','2025-05-09 18:07:30',1);
/*!40000 ALTER TABLE `asignaturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asistencias`
--

DROP TABLE IF EXISTS `asistencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alumno_id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `estado` enum('presente','ausente','retraso','justificado') NOT NULL DEFAULT 'presente',
  `observaciones` text DEFAULT NULL,
  `registrado_por` int(11) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `alumno_id` (`alumno_id`),
  KEY `asignatura_id` (`asignatura_id`),
  KEY `registrado_por` (`registrado_por`),
  CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asistencias_ibfk_2` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asistencias_ibfk_3` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencias`
--

LOCK TABLES `asistencias` WRITE;
/*!40000 ALTER TABLE `asistencias` DISABLE KEYS */;
INSERT INTO `asistencias` VALUES (1,1,1,'2025-05-09 18:59:25','presente',NULL,2,'2025-05-09 18:59:25'),(2,3,1,'2025-05-09 18:59:25','presente',NULL,2,'2025-05-09 18:59:25'),(3,1,1,'2025-05-10 19:00:30','retraso',NULL,2,'2025-05-09 19:00:30'),(4,3,1,'2025-05-10 19:00:30','presente',NULL,2,'2025-05-09 19:00:30'),(5,1,3,'2025-05-09 20:40:00','ausente',NULL,2,'2025-05-09 20:40:00'),(6,3,3,'2025-05-09 20:40:00','ausente',NULL,2,'2025-05-09 20:40:00'),(7,1,1,'2025-05-12 20:41:39','retraso',NULL,2,'2025-05-09 20:41:39'),(8,3,1,'2025-05-12 20:41:39','ausente',NULL,2,'2025-05-09 20:41:39'),(9,1,2,'2025-05-10 15:13:20','ausente',NULL,2,'2025-05-10 15:13:20'),(10,3,2,'2025-05-10 15:13:20','presente',NULL,2,'2025-05-10 15:13:20'),(11,1,2,'2025-05-12 15:15:24','ausente','Hoy es día 12/05',2,'2025-05-10 15:15:24'),(12,3,2,'2025-05-12 15:15:24','presente',NULL,2,'2025-05-10 15:15:24'),(13,4,1,'2025-05-30 17:00:43','ausente',NULL,2,'2025-05-11 17:00:43'),(14,1,1,'2025-05-30 17:00:43','presente',NULL,2,'2025-05-11 17:00:43'),(15,4,1,'2025-05-28 17:05:08','presente',NULL,2,'2025-05-11 17:05:08'),(16,1,1,'2025-05-28 17:05:08','ausente',NULL,2,'2025-05-11 17:05:08'),(17,4,3,'2025-05-13 21:42:57','ausente',NULL,2,'2025-05-11 21:42:57'),(18,1,3,'2025-05-13 21:42:57','presente',NULL,2,'2025-05-11 21:42:57'),(19,4,3,'2025-05-13 21:43:21','presente',NULL,2,'2025-05-11 21:43:21'),(20,1,3,'2025-05-13 21:43:21','presente',NULL,2,'2025-05-11 21:43:21'),(21,2,1,'2025-05-11 21:45:44','presente',NULL,2,'2025-05-11 21:45:44'),(22,3,1,'2025-05-11 21:45:44','presente',NULL,2,'2025-05-11 21:45:44'),(23,2,3,'2025-05-11 21:47:50','presente',NULL,2,'2025-05-11 21:47:50'),(24,3,3,'2025-05-11 21:47:50','presente',NULL,2,'2025-05-11 21:47:50'),(25,2,3,'2025-05-11 21:49:18','ausente',NULL,2,'2025-05-11 21:49:18'),(26,3,3,'2025-05-11 21:49:18','presente',NULL,2,'2025-05-11 21:49:18'),(27,4,3,'2025-05-11 21:49:53','ausente',NULL,2,'2025-05-11 21:49:53'),(28,1,3,'2025-05-11 21:49:53','presente',NULL,2,'2025-05-11 21:49:53'),(29,2,1,'2025-05-11 21:51:40','ausente',NULL,2,'2025-05-11 21:51:40'),(30,3,1,'2025-05-11 21:51:40','presente',NULL,2,'2025-05-11 21:51:41');
/*!40000 ALTER TABLE `asistencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calificaciones`
--

DROP TABLE IF EXISTS `calificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calificaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alumno_id` int(11) NOT NULL,
  `evaluacion_id` int(11) NOT NULL,
  `valor` decimal(5,2) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `alumno_evaluacion` (`alumno_id`,`evaluacion_id`),
  KEY `alumno_id` (`alumno_id`),
  KEY `evaluacion_id` (`evaluacion_id`),
  CONSTRAINT `calificaciones_ibfk_1` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `calificaciones_ibfk_2` FOREIGN KEY (`evaluacion_id`) REFERENCES `evaluaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `check_valor_range` CHECK (`valor` >= 0 and `valor` <= 10)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calificaciones`
--

LOCK TABLES `calificaciones` WRITE;
/*!40000 ALTER TABLE `calificaciones` DISABLE KEYS */;
INSERT INTO `calificaciones` VALUES (24,4,6,5.00,NULL,'2025-05-11 09:27:11'),(25,1,6,7.00,NULL,'2025-05-11 09:27:11'),(26,2,7,9.00,NULL,'2025-05-11 09:56:58'),(27,2,8,6.50,NULL,'2025-05-11 09:56:58'),(28,2,9,7.40,NULL,'2025-05-11 09:56:58'),(29,2,10,4.50,NULL,'2025-05-11 09:56:58'),(30,2,11,10.00,NULL,'2025-05-11 09:56:58'),(31,3,7,9.00,NULL,'2025-05-11 09:56:58'),(32,3,8,7.00,NULL,'2025-05-11 09:56:58'),(33,3,9,9.00,NULL,'2025-05-11 09:56:58'),(34,3,10,7.00,NULL,'2025-05-11 09:56:58'),(35,3,11,10.00,NULL,'2025-05-11 09:56:58');
/*!40000 ALTER TABLE `calificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evaluaciones`
--

DROP TABLE IF EXISTS `evaluaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evaluaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `periodo_id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date NOT NULL,
  `porcentaje` decimal(5,2) DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `asignatura_id` (`asignatura_id`),
  KEY `periodo_id` (`periodo_id`),
  KEY `grupo_id` (`grupo_id`),
  CONSTRAINT `evaluaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluaciones_ibfk_2` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluaciones_ibfk_3` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_evaluacion` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluaciones_ibfk_4` FOREIGN KEY (`grupo_id`) REFERENCES `grupos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluaciones`
--

LOCK TABLES `evaluaciones` WRITE;
/*!40000 ALTER TABLE `evaluaciones` DISABLE KEYS */;
INSERT INTO `evaluaciones` VALUES (6,2,1,1,3,'Examen tema 1','','2025-05-11',20.00,1),(7,2,1,1,4,'Ejercicios Tema1','','2025-05-13',5.00,1),(8,2,1,1,4,'Examen Tema 1','','2025-05-16',30.00,1),(9,2,1,1,4,'Ejercicios Tema 2','','2025-05-21',10.00,1),(10,2,1,1,4,'Examen Tema 2','','2025-05-11',40.00,1),(11,2,1,1,4,'Actitud','','2025-05-11',15.00,1);
/*!40000 ALTER TABLE `evaluaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventos_calendario`
--

DROP TABLE IF EXISTS `eventos_calendario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eventos_calendario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `semana_numero` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `dia_semana` tinyint(4) NOT NULL COMMENT '1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes',
  `hora_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `color` varchar(20) DEFAULT '#3498db',
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `hora_id` (`hora_id`),
  CONSTRAINT `eventos_calendario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `eventos_calendario_ibfk_2` FOREIGN KEY (`hora_id`) REFERENCES `horas_calendario` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos_calendario`
--

LOCK TABLES `eventos_calendario` WRITE;
/*!40000 ALTER TABLE `eventos_calendario` DISABLE KEYS */;
/*!40000 ALTER TABLE `eventos_calendario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventos_calendario_anual`
--

DROP TABLE IF EXISTS `eventos_calendario_anual`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eventos_calendario_anual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(50) DEFAULT 'calendar',
  `color` varchar(20) DEFAULT '#3498db',
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `eventos_calendario_anual_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos_calendario_anual`
--

LOCK TABLES `eventos_calendario_anual` WRITE;
/*!40000 ALTER TABLE `eventos_calendario_anual` DISABLE KEYS */;
/*!40000 ALTER TABLE `eventos_calendario_anual` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventos_fin_semana`
--

DROP TABLE IF EXISTS `eventos_fin_semana`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eventos_fin_semana` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `semana_numero` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `dia` enum('sabado','domingo') NOT NULL,
  `contenido` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`,`semana_numero`,`anio`,`dia`),
  CONSTRAINT `eventos_fin_semana_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos_fin_semana`
--

LOCK TABLES `eventos_fin_semana` WRITE;
/*!40000 ALTER TABLE `eventos_fin_semana` DISABLE KEYS */;
/*!40000 ALTER TABLE `eventos_fin_semana` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupos`
--

DROP TABLE IF EXISTS `grupos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `curso_academico` varchar(20) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `grupos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupos`
--

LOCK TABLES `grupos` WRITE;
/*!40000 ALTER TABLE `grupos` DISABLE KEYS */;
INSERT INTO `grupos` VALUES (3,2,'1 DAW','','2024-2025','2025-05-11 09:24:54','2025-05-14 18:31:48',0),(4,2,'2 DAW','','2024-2025','2025-05-11 09:25:01','2025-05-13 19:05:55',0),(5,2,'1 ESO','','2024-2025','2025-05-13 18:40:33','2025-05-13 18:40:54',0),(6,2,'2 ESO','','2024-2025','2025-05-13 18:40:42','2025-05-13 18:40:50',0),(7,2,'1 ESO','','2024-2025','2025-05-13 18:52:39','2025-05-13 19:06:13',0),(8,2,'2 DAW','','2024-2025','2025-05-13 18:52:44','2025-05-13 18:52:52',0),(9,2,'2ESO','','','2025-05-13 18:52:59','2025-05-13 19:05:58',0),(10,2,'3 ESO','','2024-2025','2025-05-13 18:53:08','2025-05-13 19:06:06',0),(11,2,'4 ESO','Acaban el instituto','2024-2025','2025-05-13 18:53:28','2025-05-13 19:06:04',0),(12,2,'1 ESO','','','2025-05-13 18:53:43','2025-05-13 19:06:08',0),(13,2,'1 ESO','','','2025-05-13 18:53:47','2025-05-13 19:05:51',0),(14,2,'1 ESO','','','2025-05-13 18:53:52','2025-05-13 18:54:01',0),(15,2,'1 DAW','','2024-2025','2025-05-14 18:33:44','2025-05-14 18:33:44',1);
/*!40000 ALTER TABLE `grupos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupos_asignaturas`
--

DROP TABLE IF EXISTS `grupos_asignaturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupos_asignaturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `grupo_id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grupo_asignatura` (`grupo_id`,`asignatura_id`),
  KEY `asignatura_id` (`asignatura_id`),
  CONSTRAINT `grupos_asignaturas_ibfk_1` FOREIGN KEY (`grupo_id`) REFERENCES `grupos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grupos_asignaturas_ibfk_2` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupos_asignaturas`
--

LOCK TABLES `grupos_asignaturas` WRITE;
/*!40000 ALTER TABLE `grupos_asignaturas` DISABLE KEYS */;
INSERT INTO `grupos_asignaturas` VALUES (10,3,1),(6,3,2),(8,3,3),(11,4,1),(7,4,2),(9,4,3);
/*!40000 ALTER TABLE `grupos_asignaturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horas_calendario`
--

DROP TABLE IF EXISTS `horas_calendario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `horas_calendario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `semana_numero` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `hora` varchar(13) DEFAULT NULL,
  `orden` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`,`semana_numero`,`anio`,`orden`),
  CONSTRAINT `horas_calendario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horas_calendario`
--

LOCK TABLES `horas_calendario` WRITE;
/*!40000 ALTER TABLE `horas_calendario` DISABLE KEYS */;
/*!40000 ALTER TABLE `horas_calendario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notas`
--

DROP TABLE IF EXISTS `notas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `texto` text NOT NULL,
  `estado` enum('activo','eliminado') DEFAULT 'activo',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `notas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notas`
--

LOCK TABLES `notas` WRITE;
/*!40000 ALTER TABLE `notas` DISABLE KEYS */;
INSERT INTO `notas` VALUES (10,2,'xssssssssss','activo','2025-05-14 18:25:58','2025-05-14 18:25:58');
/*!40000 ALTER TABLE `notas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notas_finales`
--

DROP TABLE IF EXISTS `notas_finales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notas_finales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alumno_id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  `periodo_id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `valor_final` decimal(5,2) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_calculo` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `alumno_asignatura_periodo_grupo` (`alumno_id`,`asignatura_id`,`periodo_id`,`grupo_id`),
  KEY `alumno_id` (`alumno_id`),
  KEY `asignatura_id` (`asignatura_id`),
  KEY `periodo_id` (`periodo_id`),
  KEY `grupo_id` (`grupo_id`),
  CONSTRAINT `notas_finales_ibfk_1` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notas_finales_ibfk_2` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notas_finales_ibfk_3` FOREIGN KEY (`periodo_id`) REFERENCES `periodos_evaluacion` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notas_finales_ibfk_4` FOREIGN KEY (`grupo_id`) REFERENCES `grupos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notas_finales`
--

LOCK TABLES `notas_finales` WRITE;
/*!40000 ALTER TABLE `notas_finales` DISABLE KEYS */;
INSERT INTO `notas_finales` VALUES (8,4,1,1,3,5.00,NULL,'2025-05-11 09:27:11'),(9,1,1,1,3,7.00,NULL,'2025-05-11 09:27:11'),(10,2,1,1,4,6.44,NULL,'2025-05-11 21:32:37'),(11,3,1,1,4,7.75,NULL,'2025-05-11 21:32:37');
/*!40000 ALTER TABLE `notas_finales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notas_semana`
--

DROP TABLE IF EXISTS `notas_semana`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notas_semana` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `semana_numero` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `contenido` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_id` (`usuario_id`,`semana_numero`,`anio`),
  CONSTRAINT `notas_semana_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notas_semana`
--

LOCK TABLES `notas_semana` WRITE;
/*!40000 ALTER TABLE `notas_semana` DISABLE KEYS */;
/*!40000 ALTER TABLE `notas_semana` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periodos_evaluacion`
--

DROP TABLE IF EXISTS `periodos_evaluacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `periodos_evaluacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `periodos_evaluacion_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periodos_evaluacion`
--

LOCK TABLES `periodos_evaluacion` WRITE;
/*!40000 ALTER TABLE `periodos_evaluacion` DISABLE KEYS */;
INSERT INTO `periodos_evaluacion` VALUES (1,2,'Primer trimestre','2025-05-10','2025-06-10','Primera Evaluación global',1),(2,2,'Segundo Trimestre','2025-06-02','2025-06-26','Segunda Evaluación global',1);
/*!40000 ALTER TABLE `periodos_evaluacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reuniones`
--

DROP TABLE IF EXISTS `reuniones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reuniones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time DEFAULT NULL,
  `contenido` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `reuniones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reuniones`
--

LOCK TABLES `reuniones` WRITE;
/*!40000 ALTER TABLE `reuniones` DISABLE KEYS */;
/*!40000 ALTER TABLE `reuniones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_asignaturas`
--

DROP TABLE IF EXISTS `usuario_asignaturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario_asignaturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `asignatura_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_asignatura` (`usuario_id`,`asignatura_id`),
  KEY `asignatura_id` (`asignatura_id`),
  CONSTRAINT `usuario_asignaturas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `usuario_asignaturas_ibfk_2` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_asignaturas`
--

LOCK TABLES `usuario_asignaturas` WRITE;
/*!40000 ALTER TABLE `usuario_asignaturas` DISABLE KEYS */;
/*!40000 ALTER TABLE `usuario_asignaturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('administrador','profesor') NOT NULL DEFAULT 'profesor',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `ultima_conexion` datetime DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Admin','Sistema','admin@mestres.com','$2y$10$YkH/bR3sNKMKUJP35xj9oOgkIKk13FkJPYzUKWAOVXCAA7KWJj2/C','administrador','2025-03-14 12:52:15',NULL,1),(2,'Antonio','Lorenzo','antonio@gmail.com','$2y$10$SR0z5o1xnLv5CfMaCY8IL.I131GKZfUDnPBWrm5Xy7C1bsIeyIb5e','profesor','2025-03-14 12:58:11','2025-05-14 18:09:27',1),(4,'Raul','Esteban','raul@gmail.com','$2y$10$VUf9E2qMiEH1qnp2alW0JOgwsMk7hJW.fpUlJqaMCKrWwYHnhX7ia','profesor','2025-04-02 19:18:47','2025-04-02 19:27:51',1),(5,'Jesus','Esteban','jesus@gmal.com','$2y$10$lRYjfKCNvI8RmiT9DmDDm.OXi1U1H1hweoPGThZysRuh26Z3s29te','profesor','2025-05-09 18:02:12','2025-05-11 22:00:58',1);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-14 18:58:56
