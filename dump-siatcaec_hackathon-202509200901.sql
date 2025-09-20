/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.3-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: siatcae.com    Database: siatcaec_hackathon
-- ------------------------------------------------------
-- Server version	10.6.20-MariaDB-cll-lve

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `taxistas`
--

DROP TABLE IF EXISTS `taxistas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `taxistas` (
  `nombre` varchar(30) NOT NULL,
  `apellido` varchar(40) NOT NULL,
  `curp` varchar(18) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `codigo_postal` varchar(10) NOT NULL,
  `wallet` varchar(80) DEFAULT NULL,
  `edad` int(2) DEFAULT NULL,
  `placa` varchar(15) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxistas`
--

LOCK TABLES `taxistas` WRITE;
/*!40000 ALTER TABLE `taxistas` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `taxistas` VALUES
('Diego Eduardo','Montoya Delgado','MODD070115HASNLGA2','Jose Lopez Portillo','20206','$ilp.interledger-test.dev/taxista_diego',NULL,'',1),
('Diego','Rodríguez','RODR800101HDFLGL01','Calle Principal 123','12345','https://ilp.interledger-test.dev/taxista_diego',28,'ABC123',2);
/*!40000 ALTER TABLE `taxistas` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `transacciones`
--

DROP TABLE IF EXISTS `transacciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transacciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` varchar(50) NOT NULL,
  `conductor_id` varchar(50) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','completada','fallida') DEFAULT 'completada',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transacciones`
--

LOCK TABLES `transacciones` WRITE;
/*!40000 ALTER TABLE `transacciones` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `transacciones` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `nombre` varchar(30) NOT NULL,
  `apellido` varchar(40) NOT NULL,
  `curp` varchar(18) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `codigo_postal` varchar(10) NOT NULL,
  `wallet` varchar(80) DEFAULT NULL,
  `edad` int(2) DEFAULT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `usuarios` VALUES
('Diego Eduardo','Montoya','VAHJ070326MASZRSA0','Jose Lopez Portillo','20206','pasajero_diego',18,'4495545165','$2y$10$CHLIw313JijKc0uFhRzO.uJLfTThNgTJLXU5hgz0glvJBfQj6xAXy','diegoeduardm7@gmail.com',1);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `viajes`
--

DROP TABLE IF EXISTS `viajes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `viajes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_curp` int(11) NOT NULL,
  `taxista_curp` int(11) NOT NULL,
  `origen` varchar(255) NOT NULL,
  `destino` varchar(255) NOT NULL,
  `distancia` decimal(5,2) NOT NULL,
  `costo` decimal(10,2) NOT NULL,
  `estado` enum('solicitado','en_progreso','completado','cancelado') DEFAULT 'solicitado',
  `fecha_viaje` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_curp` (`usuario_curp`),
  KEY `taxista_curp` (`taxista_curp`),
  CONSTRAINT `viajes_ibfk_1` FOREIGN KEY (`usuario_curp`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `viajes_ibfk_2` FOREIGN KEY (`taxista_curp`) REFERENCES `taxistas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `viajes`
--

LOCK TABLES `viajes` WRITE;
/*!40000 ALTER TABLE `viajes` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `viajes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Dumping routines for database 'siatcaec_hackathon'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-09-20  9:01:38
