-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: eventos_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `eventos_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `eventos_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `eventos_db`;

--
-- Table structure for table `compras`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compras` (
  `id_compra` int(11) NOT NULL AUTO_INCREMENT,
  `data_compra` datetime NOT NULL DEFAULT current_timestamp(),
  `quantidade` int(11) NOT NULL CHECK (`quantidade` > 0),
  `valor_total` decimal(10,2) NOT NULL CHECK (`valor_total` >= 0),
  `id_participante` int(11) NOT NULL,
  `id_ingresso` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_compra`),
  KEY `fk_compra_participante` (`id_participante`),
  KEY `fk_compra_ingresso` (`id_ingresso`),
  CONSTRAINT `fk_compra_ingresso` FOREIGN KEY (`id_ingresso`) REFERENCES `ingressos` (`id_ingresso`) ON UPDATE CASCADE,
  CONSTRAINT `fk_compra_participante` FOREIGN KEY (`id_participante`) REFERENCES `participantes` (`id_participante`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras`
--

LOCK TABLES `compras` WRITE;
/*!40000 ALTER TABLE `compras` DISABLE KEYS */;
INSERT INTO `compras` VALUES (1,'2026-08-20 10:00:00',2,160.00,1,1,'2026-08-24 11:04:03'),(2,'2026-08-21 14:30:00',1,40.00,2,2,'2026-08-24 11:04:03'),(3,'2026-08-22 09:15:00',3,360.00,3,3,'2026-08-24 11:04:03');
/*!40000 ALTER TABLE `compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventos`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eventos` (
  `id_evento` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_evento` datetime NOT NULL,
  `status` enum('planejado','confirmado','cancelado','realizado') NOT NULL DEFAULT 'planejado',
  `id_local` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_evento`),
  UNIQUE KEY `uq_evento_titulo_data` (`titulo`,`data_evento`),
  KEY `fk_evento_local` (`id_local`),
  CONSTRAINT `fk_evento_local` FOREIGN KEY (`id_local`) REFERENCES `locais` (`id_local`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos`
--

LOCK TABLES `eventos` WRITE;
/*!40000 ALTER TABLE `eventos` DISABLE KEYS */;
INSERT INTO `eventos` VALUES (1,'Semana de Tecnologia 2026','Palestras e workshops de PAW e BdD','2026-09-15 08:00:00','confirmado',1,'2026-08-24 11:04:03'),(2,'Festival de Música Local','Shows de bandas regionais','2026-10-20 19:00:00','planejado',2,'2026-08-24 11:04:03'),(3,'Expo Inovação','Feira de startups e projetos estudantis','2026-11-05 09:00:00','planejado',3,'2026-08-24 11:04:03');
/*!40000 ALTER TABLE `eventos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingressos`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ingressos` (
  `id_ingresso` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(80) NOT NULL,
  `preco` decimal(10,2) NOT NULL CHECK (`preco` >= 0),
  `quantidade_total` int(11) NOT NULL CHECK (`quantidade_total` > 0),
  `quantidade_disponivel` int(11) NOT NULL CHECK (`quantidade_disponivel` >= 0),
  `id_evento` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_ingresso`),
  UNIQUE KEY `uq_ingresso_tipo_evento` (`tipo`,`id_evento`),
  KEY `fk_ingresso_evento` (`id_evento`),
  CONSTRAINT `fk_ingresso_evento` FOREIGN KEY (`id_evento`) REFERENCES `eventos` (`id_evento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `CONSTRAINT_1` CHECK (`quantidade_disponivel` <= `quantidade_total`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingressos`
--

LOCK TABLES `ingressos` WRITE;
/*!40000 ALTER TABLE `ingressos` DISABLE KEYS */;
INSERT INTO `ingressos` VALUES (1,'Inteira',80.00,200,200,1,'2026-08-24 11:04:03'),(2,'Meia-entrada',40.00,100,100,1,'2026-08-24 11:04:03'),(3,'Pista',120.00,300,300,2,'2026-08-24 11:04:03'),(4,'VIP',250.00,50,50,3,'2026-08-24 11:04:03'),(5,'Estudante',30.00,400,400,3,'2026-08-24 11:04:03');
/*!40000 ALTER TABLE `ingressos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locais`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locais` (
  `id_local` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) NOT NULL,
  `endereco` varchar(255) NOT NULL,
  `capacidade` int(11) NOT NULL CHECK (`capacidade` > 0),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_local`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locais`
--

LOCK TABLES `locais` WRITE;
/*!40000 ALTER TABLE `locais` DISABLE KEYS */;
INSERT INTO `locais` VALUES (1,'Centro de Convenções Helio','Av. Principal 1000, Centro',500,'2026-08-24 11:04:03'),(2,'Teatro Municipal','Rua das Artes 55, Centro',300,'2026-08-24 11:04:03'),(3,'Arena Tech','Rod. BR 101 km 12, Distrito Industrial',1200,'2026-08-24 11:04:03');
/*!40000 ALTER TABLE `locais` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participantes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `participantes` (
  `id_participante` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `perfil` enum('administrador','comum') NOT NULL DEFAULT 'comum',
  `senha` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_participante`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participantes`
--

LOCK TABLES `participantes` WRITE;
/*!40000 ALTER TABLE `participantes` DISABLE KEYS */;
INSERT INTO `participantes` VALUES (1,'Ana Souza','ana@email.com','111.222.333-44','(47) 99911-2233','administrador','$2y$10$3JjEJUodOjcvm4817aU7keVywDzdTrx46UcE7FFTZ8vEUWNeNeU3K','2026-08-24 11:04:03'),(2,'Bruno Lima','bruno@email.com','222.333.444-55','(47) 98822-3344','comum','$2y$10$3JjEJUodOjcvm4817aU7keVywDzdTrx46UcE7FFTZ8vEUWNeNeU3K','2026-08-24 11:04:03'),(3,'Carla Mendes','carla@email.com','333.444.555-66','(47) 97733-4455','comum','$2y$10$3JjEJUodOjcvm4817aU7keVywDzdTrx46UcE7FFTZ8vEUWNeNeU3K','2026-08-24 11:04:03');
/*!40000 ALTER TABLE `participantes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-13 19:58:28
