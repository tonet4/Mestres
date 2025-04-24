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
INSERT INTO `eventos_calendario` VALUES (19,2,12,2025,1,20,'Matematicas','-Examen sumas, restas, multipliccones','#9186ea'),(20,2,12,2025,1,21,'Lengua','-Analizar frases, sujeto, predicado,adjetivos','#e67e22'),(23,2,12,2025,5,25,'Ciencias','-Realizar un examen clinico','#1abc9c'),(25,2,12,2025,5,21,'Religión','Rezar a dios para ser rico','#c6b8cc'),(26,2,13,2025,1,30,'Informática','-Comprobación de la base de datos','#9b59b6'),(27,4,16,2025,2,36,'Tutoria','Hablar con Juan','#e67e22'),(28,4,16,2025,1,37,'Mates','Examen','#e74c3c'),(29,2,15,2025,1,38,'Prueba de examen','Examen de religión','#71948d');
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos_calendario_anual`
--

LOCK TABLES `eventos_calendario_anual` WRITE;
/*!40000 ALTER TABLE `eventos_calendario_anual` DISABLE KEYS */;
INSERT INTO `eventos_calendario_anual` VALUES (2,2,'2025-05-16','aaa','aaa','star','#e67e22'),(3,2,'2025-05-17','Prueba dos','sdede','users','#e67e22'),(4,2,'2025-05-15','Ocaa','qqqq','flag','#34495e'),(5,2,'2025-05-16','wwww','wwww','flag','#3498db'),(8,2,'2025-05-15','AAAA','','star','#e74c3c'),(10,2,'2025-04-18','Viernes Santo','Fiesta de Pascua','flag','#f1c40f'),(13,2,'2025-04-18','ddddd','dddd','star','#1abc9c'),(14,2,'2025-04-18','aaaaaaaaaaaaaaaaaa','eeeeeeeeee','book','#e67e22'),(15,2,'2025-04-18','qqqqqqqqqq','qqqqqqqqqqqqqqqqq','graduation-cap','#3498db'),(16,2,'2025-04-18','qqqqqqqqqqqqq','qqqqqqqqqqqqqqqq','calendar','#e74c3c'),(17,2,'2025-04-20','Cumple de Triana','Nos lo vamos a pasar genial','flag','#1abc9c');
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
INSERT INTO `eventos_fin_semana` VALUES (3,2,11,2025,'sabado','[{\"id\":1,\"text\":\"aawww\"},{\"id\":2,\"text\":\"errrr\"}]'),(4,2,11,2025,'domingo','[{\"id\":1,\"text\":\"ttttttttttt\"},{\"id\":2,\"text\":\"jjuu\"}]'),(5,2,12,2025,'sabado','[]'),(6,2,12,2025,'domingo','[{\"id\":1,\"text\":\"-Comunión\"},{\"id\":2,\"text\":\"-Comemos macarrones con tomate\"}]'),(7,2,14,2025,'sabado','[]'),(8,2,15,2025,'sabado','[]'),(9,2,15,2025,'domingo','[{\"id\":1,\"text\":\"Cumpleaños de triana\"}]');
/*!40000 ALTER TABLE `eventos_fin_semana` ENABLE KEYS */;
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
INSERT INTO `horas_calendario` VALUES (18,2,11,2025,'08:00 - 09:00',1),(19,2,11,2025,'09:00 - 09:30',2),(20,2,12,2025,'09:00 - 10:00',1),(21,2,12,2025,'10:01 - 11:00',2),(25,2,12,2025,'12:10 - 13:00',3),(30,2,13,2025,'07:00 - 08:00',1),(32,2,13,2025,'08:00 - 09:00',2),(35,2,13,2025,'11:00 - 12:00',3),(36,4,16,2025,'06:00 - 07:00',1),(37,4,16,2025,'08:00 - 09:00',2),(38,2,15,2025,'08:00 - 09:00',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notas`
--

LOCK TABLES `notas` WRITE;
/*!40000 ALTER TABLE `notas` DISABLE KEYS */;
INSERT INTO `notas` VALUES (1,2,'-Hola es mi primera nota','eliminado','2025-03-14 14:31:06','2025-03-26 20:26:39'),(2,2,'-Esta es mi segunda nota','eliminado','2025-03-18 22:28:43','2025-03-26 20:26:42'),(3,2,'-Hola esta es mi tercera nota','activo','2025-03-18 22:29:06','2025-04-19 17:25:20'),(4,2,'-Hola es mi cuarta nota','eliminado','2025-03-22 17:01:32','2025-04-19 17:40:56'),(5,2,'-Mañana intentar ir a correr con los del Parotet, espero que no sean más de 10K','eliminado','2025-03-22 23:05:09','2025-03-26 20:26:37'),(6,2,'-Holaaaa','activo','2025-03-25 21:38:50','2025-03-25 21:38:50'),(7,4,'-Recoger al niño a las 19:00','activo','2025-04-02 19:20:36','2025-04-02 19:20:36'),(8,2,'prueba','activo','2025-04-19 17:40:48','2025-04-19 17:40:48'),(9,2,'Pruebame','eliminado','2025-04-20 09:00:39','2025-04-20 09:00:56');
/*!40000 ALTER TABLE `notas` ENABLE KEYS */;
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
INSERT INTO `notas_semana` VALUES (3,2,11,2025,'[{\"id\":1,\"text\":\"aaa\"}]'),(4,2,13,2025,'[]'),(5,2,12,2025,'[{\"id\":1,\"text\":\"-Cumpleaños Oihane la semana que viene\"},{\"id\":2,\"text\":\"aa\"},{\"id\":3,\"text\":\"s\"}]'),(6,4,13,2025,'[{\"id\":1,\"text\":\"bailae\"}]');
/*!40000 ALTER TABLE `notas_semana` ENABLE KEYS */;
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
INSERT INTO `usuarios` VALUES (1,'Admin','Sistema','admin@mestres.com','$2y$10$YkH/bR3sNKMKUJP35xj9oOgkIKk13FkJPYzUKWAOVXCAA7KWJj2/C','administrador','2025-03-14 12:52:15',NULL,1),(2,'Antonio','Lorenzo','antonio@gmail.com','$2y$10$SR0z5o1xnLv5CfMaCY8IL.I131GKZfUDnPBWrm5Xy7C1bsIeyIb5e','profesor','2025-03-14 12:58:11','2025-04-20 11:32:08',1),(4,'Raul','Esteban','raul@gmail.com','$2y$10$VUf9E2qMiEH1qnp2alW0JOgwsMk7hJW.fpUlJqaMCKrWwYHnhX7ia','profesor','2025-04-02 19:18:47','2025-04-02 19:27:51',1),(5,'Manolo','Esteban','manolo@gmail.com','$2y$10$mKdkUH6pJBrUOgQphWAFneIMi9eMxJ/ROCTWCBGLpUA1dlmK3TJZW','profesor','2025-04-20 09:08:31','2025-04-20 09:08:43',1);
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

-- Dump completed on 2025-04-21  7:24:22
