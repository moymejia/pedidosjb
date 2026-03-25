-- MySQL dump 10.13  Distrib 9.6.0, for macos15.7 (arm64)
--
-- Host: localhost    Database: pedidosjb_pedidos
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cliente`
--

DROP TABLE IF EXISTS `cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cliente` (
  `idcliente` int NOT NULL AUTO_INCREMENT,
  `nit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dias_credito` int NOT NULL DEFAULT '0',
  `monto_credito` decimal(14,2) NOT NULL DEFAULT '0.00',
  `observaciones` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `establecimiento` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `limite_credito` decimal(14,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`idcliente`),
  UNIQUE KEY `uq_cliente_nit` (`nit`),
  KEY `idx_cliente_usuario_creacion` (`usuario_creacion`),
  KEY `idx_cliente_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_cliente_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cliente_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cliente`
--

LOCK TABLES `cliente` WRITE;
/*!40000 ALTER TABLE `cliente` DISABLE KEYS */;
INSERT INTO `cliente` VALUES (1,'1','Rodrigo','si',3,0.00,'asdas','ACTIVO','2026-03-18 16:26:20','admin',NULL,'admin','123','si','1234',1.00),(3,'33','Cliente 1','Tecpan G.',3,0.00,'sisisi','ACTIVO','2026-03-25 17:02:06','admin',NULL,NULL,'451223','Establecimiento 1','12312313',12.00);
/*!40000 ALTER TABLE `cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cliente_anticipo`
--

DROP TABLE IF EXISTS `cliente_anticipo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cliente_anticipo` (
  `idcliente_anticipo` int NOT NULL AUTO_INCREMENT,
  `idcliente` int NOT NULL,
  `fecha` date NOT NULL,
  `idtipo_pago` int NOT NULL,
  `idforma_pago` int NOT NULL,
  `monto` decimal(14,2) NOT NULL,
  `saldo_disponible` decimal(14,2) NOT NULL,
  `referencia_pago` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idcliente_anticipo`),
  KEY `idx_cliente_anticipo_idcliente` (`idcliente`),
  KEY `idx_cliente_anticipo_idtipo_pago` (`idtipo_pago`),
  KEY `idx_cliente_anticipo_idforma_pago` (`idforma_pago`),
  KEY `idx_cliente_anticipo_usuario_creacion` (`usuario_creacion`),
  KEY `idx_cliente_anticipo_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_cliente_anticipo_cliente` FOREIGN KEY (`idcliente`) REFERENCES `cliente` (`idcliente`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cliente_anticipo_forma_pago` FOREIGN KEY (`idforma_pago`) REFERENCES `forma_pago` (`idforma_pago`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cliente_anticipo_tipo_pago` FOREIGN KEY (`idtipo_pago`) REFERENCES `tipo_pago` (`idtipo_pago`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cliente_anticipo_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cliente_anticipo_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cliente_anticipo`
--

LOCK TABLES `cliente_anticipo` WRITE;
/*!40000 ALTER TABLE `cliente_anticipo` DISABLE KEYS */;
/*!40000 ALTER TABLE `cliente_anticipo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cliente_anticipo_aplicacion`
--

DROP TABLE IF EXISTS `cliente_anticipo_aplicacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cliente_anticipo_aplicacion` (
  `idcliente_anticipo_aplicacion` int NOT NULL AUTO_INCREMENT,
  `idcliente_anticipo` int NOT NULL,
  `iddespacho` int NOT NULL,
  `fecha` date NOT NULL,
  `monto_aplicado` decimal(14,2) NOT NULL,
  `observaciones` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idcliente_anticipo_aplicacion`),
  KEY `idx_cliente_anticipo_aplicacion_idcliente_anticipo` (`idcliente_anticipo`),
  KEY `idx_cliente_anticipo_aplicacion_iddespacho` (`iddespacho`),
  KEY `idx_cliente_anticipo_aplicacion_usuario_creacion` (`usuario_creacion`),
  KEY `idx_cliente_anticipo_aplicacion_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_cliente_anticipo_aplicacion_anticipo` FOREIGN KEY (`idcliente_anticipo`) REFERENCES `cliente_anticipo` (`idcliente_anticipo`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cliente_anticipo_aplicacion_despacho` FOREIGN KEY (`iddespacho`) REFERENCES `despacho` (`iddespacho`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cliente_anticipo_aplicacion_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cliente_anticipo_aplicacion_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cliente_anticipo_aplicacion`
--

LOCK TABLES `cliente_anticipo_aplicacion` WRITE;
/*!40000 ALTER TABLE `cliente_anticipo_aplicacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `cliente_anticipo_aplicacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cliente_contacto`
--

DROP TABLE IF EXISTS `cliente_contacto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cliente_contacto` (
  `idcliente_contacto` int NOT NULL AUTO_INCREMENT,
  `idcliente` int NOT NULL,
  `idtipo_contacto` int NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idcliente_contacto`),
  KEY `idx_cliente_contacto_idcliente` (`idcliente`),
  KEY `idx_cliente_contacto_idtipo_contacto` (`idtipo_contacto`),
  KEY `idx_cliente_contacto_usuario_creacion` (`usuario_creacion`),
  KEY `idx_cliente_contacto_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_cliente_contacto_cliente` FOREIGN KEY (`idcliente`) REFERENCES `cliente` (`idcliente`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cliente_contacto_tipo_contacto` FOREIGN KEY (`idtipo_contacto`) REFERENCES `tipo_contacto` (`idtipo_contacto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cliente_contacto_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cliente_contacto_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cliente_contacto`
--

LOCK TABLES `cliente_contacto` WRITE;
/*!40000 ALTER TABLE `cliente_contacto` DISABLE KEYS */;
/*!40000 ALTER TABLE `cliente_contacto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `color`
--

DROP TABLE IF EXISTS `color`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `color` (
  `idcolor` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idcolor`),
  UNIQUE KEY `uq_color_nombre` (`nombre`),
  KEY `idx_color_usuario_creacion` (`usuario_creacion`),
  KEY `idx_color_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_color_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_color_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `color`
--

LOCK TABLES `color` WRITE;
/*!40000 ALTER TABLE `color` DISABLE KEYS */;
INSERT INTO `color` VALUES (5,'NEGRO','ACTIVO','2026-03-16 16:05:19','admin',NULL,NULL),(6,'PAJA','ACTIVO','2026-03-16 16:05:19','admin',NULL,NULL),(7,'GRIS CAFÉ','ACTIVO','2026-03-16 16:05:19','admin',NULL,NULL),(8,'GRIS','ACTIVO','2026-03-17 09:48:06','admin',NULL,NULL),(9,'CAFÉ','ACTIVO','2026-03-17 09:48:06','admin',NULL,NULL),(10,'AMARILLO','ACTIVO','2026-03-17 09:48:06','admin',NULL,NULL),(11,'NARANJA','ACTIVO','2026-03-17 09:48:06','admin',NULL,NULL),(12,'PLATA','ACTIVO','2026-03-17 09:48:06','admin',NULL,NULL),(13,'AZUL','ACTIVO','2026-03-17 09:48:06','admin',NULL,NULL),(14,'01063','ACTIVO','2026-03-18 12:18:16','admin',NULL,NULL),(15,'0107','ACTIVO','2026-03-18 14:27:26','admin',NULL,NULL),(16,'01071','ACTIVO','2026-03-18 14:27:26','admin',NULL,NULL),(17,'01073','ACTIVO','2026-03-18 14:27:26','admin',NULL,NULL),(18,'01074','ACTIVO','2026-03-18 14:27:26','admin',NULL,NULL),(19,'NAPA','ACTIVO','2026-03-18 15:07:59','admin',NULL,NULL),(20,'METALIZADO','ACTIVO','2026-03-18 15:13:59','admin',NULL,NULL),(21,'NAPA BRIGHT','ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(22,'NAPA CASUAL','ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(23,'NOBUCK','ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(24,'VERNIZ','ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(25,'','ACTIVO','2026-03-19 10:13:44','admin',NULL,NULL),(26,'VERDE','ACTIVO','2026-03-19 10:14:48','admin',NULL,NULL),(27,'NAPO','ACTIVO','2026-03-19 10:15:22','admin',NULL,NULL),(28,'GRENDAY','ACTIVO','2026-03-19 10:17:54','admin',NULL,NULL),(29,'ROJO','ACTIVO','2026-03-19 10:51:41','admin',NULL,NULL),(30,'CAMEL','ACTIVO','2026-03-19 11:40:51','admin',NULL,NULL),(31,'MIEL','ACTIVO','2026-03-19 11:51:33','admin',NULL,NULL),(32,'ORO','ACTIVO','2026-03-19 11:51:33','admin',NULL,NULL),(33,'BEIGE','ACTIVO','2026-03-19 11:51:33','admin',NULL,NULL),(34,'VINO','ACTIVO','2026-03-19 11:51:33','admin',NULL,NULL),(35,'VERDE MENTA','ACTIVO','2026-03-19 11:51:33','admin',NULL,NULL),(36,'LATE','ACTIVO','2026-03-19 11:51:33','admin',NULL,NULL),(37,'HUESO','ACTIVO','2026-03-19 11:51:33','admin',NULL,NULL),(38,'MARINO','ACTIVO','2026-03-19 11:51:33','admin',NULL,NULL);
/*!40000 ALTER TABLE `color` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `concepto`
--

DROP TABLE IF EXISTS `concepto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `concepto` (
  `idconcepto` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idconcepto`),
  UNIQUE KEY `uq_concepto_nombre` (`nombre`),
  KEY `idx_concepto_usuario_creacion` (`usuario_creacion`),
  KEY `idx_concepto_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_concepto_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_concepto_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `concepto`
--

LOCK TABLES `concepto` WRITE;
/*!40000 ALTER TABLE `concepto` DISABLE KEYS */;
INSERT INTO `concepto` VALUES (1,'','ACTIVO','2026-03-16 15:42:10','admin',NULL,NULL),(2,'TPU/PU','ACTIVO','2026-03-16 15:59:33','admin',NULL,NULL),(3,'CASCO INYECCION','ACTIVO','2026-03-17 09:09:39','admin',NULL,NULL),(4,'CASCO PEGADO','ACTIVO','2026-03-17 09:48:06','admin',NULL,NULL),(5,'CONCHA METATARSO','ACTIVO','2026-03-17 09:48:06','admin',NULL,NULL),(6,'ELASTOMERO','ACTIVO','2026-03-17 09:48:06','admin',NULL,NULL),(7,'METATARSO','ACTIVO','2026-03-17 09:48:06','admin',NULL,NULL),(8,'CONCEPTO SI','ACTIVO','2026-03-24 10:09:37','admin',NULL,'admin');
/*!40000 ALTER TABLE `concepto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `corte`
--

DROP TABLE IF EXISTS `corte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `corte` (
  `idcorte` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idcorte`),
  UNIQUE KEY `uq_corte_nombre` (`nombre`),
  KEY `idx_corte_usuario_creacion` (`usuario_creacion`),
  KEY `idx_corte_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_corte_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_corte_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `corte`
--

LOCK TABLES `corte` WRITE;
/*!40000 ALTER TABLE `corte` DISABLE KEYS */;
INSERT INTO `corte` VALUES (1,'','ACTIVO','2026-03-16 15:42:10','admin',NULL,NULL),(2,'HUNGARO','ACTIVO','2026-03-16 15:59:33','admin',NULL,NULL),(3,'NBK','ACTIVO','2026-03-16 15:59:33','admin',NULL,NULL),(4,'CRAZY','ACTIVO','2026-03-16 15:59:33','admin',NULL,NULL),(5,'TEXTIL','ACTIVO','2026-03-16 15:59:33','admin',NULL,NULL),(6,'MICROFIBRA','ACTIVO','2026-03-16 15:59:33','admin',NULL,NULL),(7,'HUNGARO/ NBK/ CRAZY','ACTIVO','2026-03-17 08:41:56','admin',NULL,NULL),(8,'Crazy NBK','ACTIVO','2026-03-17 09:48:06','admin',NULL,NULL),(9,'SINTETICO','ACTIVO','2026-03-17 09:48:06','admin',NULL,NULL),(10,'ULTRAPIEL','ACTIVO','2026-03-17 09:48:06','admin',NULL,NULL),(11,'CORTE SI','INACTIVO','2026-03-24 10:12:21','admin',NULL,'admin');
/*!40000 ALTER TABLE `corte` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `despacho`
--

DROP TABLE IF EXISTS `despacho`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `despacho` (
  `iddespacho` int NOT NULL AUTO_INCREMENT,
  `idpedido` int NOT NULL,
  `fecha` date NOT NULL,
  `fecha_factura` date DEFAULT NULL,
  `numero_factura` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto_flete` decimal(14,2) NOT NULL DEFAULT '0.00',
  `monto_otros` decimal(14,2) NOT NULL DEFAULT '0.00',
  `monto_subtotal` decimal(14,2) NOT NULL DEFAULT '0.00',
  `monto_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `saldo_pendiente` decimal(14,2) NOT NULL DEFAULT '0.00',
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`iddespacho`),
  UNIQUE KEY `uq_despacho_numero_factura` (`numero_factura`),
  KEY `idx_despacho_idpedido` (`idpedido`),
  KEY `idx_despacho_usuario_creacion` (`usuario_creacion`),
  KEY `idx_despacho_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_despacho_pedido` FOREIGN KEY (`idpedido`) REFERENCES `pedido` (`idpedido`) ON UPDATE CASCADE,
  CONSTRAINT `fk_despacho_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_despacho_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `despacho`
--

LOCK TABLES `despacho` WRITE;
/*!40000 ALTER TABLE `despacho` DISABLE KEYS */;
/*!40000 ALTER TABLE `despacho` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `despacho_detalle`
--

DROP TABLE IF EXISTS `despacho_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `despacho_detalle` (
  `iddespacho_detalle` int NOT NULL AUTO_INCREMENT,
  `iddespacho` int NOT NULL,
  `idpedido_detalle` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_venta` decimal(14,2) NOT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT '0.00',
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`iddespacho_detalle`),
  UNIQUE KEY `uq_despacho_detalle` (`iddespacho`,`idpedido_detalle`),
  KEY `idx_despacho_detalle_idpedido_detalle` (`idpedido_detalle`),
  KEY `idx_despacho_detalle_usuario_creacion` (`usuario_creacion`),
  KEY `idx_despacho_detalle_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_despacho_detalle_despacho` FOREIGN KEY (`iddespacho`) REFERENCES `despacho` (`iddespacho`) ON UPDATE CASCADE,
  CONSTRAINT `fk_despacho_detalle_pedido_detalle` FOREIGN KEY (`idpedido_detalle`) REFERENCES `pedido_detalle` (`idpedido_detalle`) ON UPDATE CASCADE,
  CONSTRAINT `fk_despacho_detalle_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_despacho_detalle_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `despacho_detalle`
--

LOCK TABLES `despacho_detalle` WRITE;
/*!40000 ALTER TABLE `despacho_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `despacho_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `despacho_pago`
--

DROP TABLE IF EXISTS `despacho_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `despacho_pago` (
  `iddespacho_pago` int NOT NULL AUTO_INCREMENT,
  `iddespacho` int NOT NULL,
  `fecha` date NOT NULL,
  `idtipo_pago` int NOT NULL,
  `idforma_pago` int NOT NULL,
  `monto` decimal(14,2) NOT NULL,
  `referencia_pago` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iddespacho_pago_recupera` int DEFAULT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`iddespacho_pago`),
  KEY `idx_despacho_pago_iddespacho` (`iddespacho`),
  KEY `idx_despacho_pago_idtipo_pago` (`idtipo_pago`),
  KEY `idx_despacho_pago_idforma_pago` (`idforma_pago`),
  KEY `idx_despacho_pago_recupera` (`iddespacho_pago_recupera`),
  KEY `idx_despacho_pago_usuario_creacion` (`usuario_creacion`),
  KEY `idx_despacho_pago_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_despacho_pago_despacho` FOREIGN KEY (`iddespacho`) REFERENCES `despacho` (`iddespacho`) ON UPDATE CASCADE,
  CONSTRAINT `fk_despacho_pago_forma_pago` FOREIGN KEY (`idforma_pago`) REFERENCES `forma_pago` (`idforma_pago`) ON UPDATE CASCADE,
  CONSTRAINT `fk_despacho_pago_recupera` FOREIGN KEY (`iddespacho_pago_recupera`) REFERENCES `despacho_pago` (`iddespacho_pago`) ON UPDATE CASCADE,
  CONSTRAINT `fk_despacho_pago_tipo_pago` FOREIGN KEY (`idtipo_pago`) REFERENCES `tipo_pago` (`idtipo_pago`) ON UPDATE CASCADE,
  CONSTRAINT `fk_despacho_pago_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_despacho_pago_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `despacho_pago`
--

LOCK TABLES `despacho_pago` WRITE;
/*!40000 ALTER TABLE `despacho_pago` DISABLE KEYS */;
/*!40000 ALTER TABLE `despacho_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forma_pago`
--

DROP TABLE IF EXISTS `forma_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forma_pago` (
  `idforma_pago` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idforma_pago`),
  UNIQUE KEY `uq_forma_pago_descripcion` (`descripcion`),
  KEY `idx_forma_pago_usuario_creacion` (`usuario_creacion`),
  KEY `idx_forma_pago_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_forma_pago_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_forma_pago_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forma_pago`
--

LOCK TABLES `forma_pago` WRITE;
/*!40000 ALTER TABLE `forma_pago` DISABLE KEYS */;
INSERT INTO `forma_pago` VALUES (1,'Forma 1','ACTIVO','2026-03-18 16:42:13','admin',NULL,'admin');
/*!40000 ALTER TABLE `forma_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marca`
--

DROP TABLE IF EXISTS `marca`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marca` (
  `idmarca` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idset_talla_preferido` int DEFAULT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idmarca`),
  UNIQUE KEY `uq_marca_nombre` (`nombre`),
  KEY `idx_marca_idset_talla_preferido` (`idset_talla_preferido`),
  KEY `idx_marca_usuario_creacion` (`usuario_creacion`),
  KEY `idx_marca_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_marca_set_talla_preferido` FOREIGN KEY (`idset_talla_preferido`) REFERENCES `set_talla` (`idset_talla`) ON UPDATE CASCADE,
  CONSTRAINT `fk_marca_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_marca_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marca`
--

LOCK TABLES `marca` WRITE;
/*!40000 ALTER TABLE `marca` DISABLE KEYS */;
INSERT INTO `marca` VALUES (2,'Caribu',1,'ACTIVO','2026-03-16 11:17:00','admin',NULL,NULL),(3,'Pampili',1,'ACTIVO','2026-03-18 12:16:45','admin',NULL,NULL),(4,'Via marte',18,'ACTIVO','2026-03-18 14:37:48','admin',NULL,'admin'),(5,'VillaPink',11,'ACTIVO','2026-03-19 09:54:45','admin',NULL,NULL),(6,'PEGADA',11,'ACTIVO','2026-03-19 10:49:24','admin',NULL,NULL),(7,'Sando',11,'ACTIVO','2026-03-19 11:30:48','admin',NULL,NULL);
/*!40000 ALTER TABLE `marca` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedido`
--

DROP TABLE IF EXISTS `pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedido` (
  `idpedido` int NOT NULL AUTO_INCREMENT,
  `idcliente` int NOT NULL,
  `idtemporada` int NOT NULL,
  `idmarca` int NOT NULL,
  `idset_talla` int NOT NULL,
  `observaciones_pedido` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descuento` decimal(14,2) NOT NULL DEFAULT '0.00',
  `dias_credito` int NOT NULL DEFAULT '0',
  `monto_subtotal` decimal(14,2) NOT NULL DEFAULT '0.00',
  `monto_descuento` decimal(14,2) NOT NULL DEFAULT '0.00',
  `monto_total` decimal(14,2) NOT NULL DEFAULT '0.00',
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_desde` date NOT NULL,
  `fecha_hasta` date NOT NULL,
  `total_pares` int NOT NULL DEFAULT '0',
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idtransporte` int DEFAULT NULL,
  PRIMARY KEY (`idpedido`),
  KEY `idx_pedido_idcliente` (`idcliente`),
  KEY `idx_pedido_idtemporada` (`idtemporada`),
  KEY `idx_pedido_idmarca` (`idmarca`),
  KEY `idx_pedido_idset_talla` (`idset_talla`),
  KEY `idx_pedido_usuario_creacion` (`usuario_creacion`),
  KEY `idx_pedido_usuario_modificacion` (`usuario_modificacion`),
  KEY `fk_pedido_transporte` (`idtransporte`),
  CONSTRAINT `fk_pedido_cliente` FOREIGN KEY (`idcliente`) REFERENCES `cliente` (`idcliente`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pedido_marca` FOREIGN KEY (`idmarca`) REFERENCES `marca` (`idmarca`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pedido_set_talla` FOREIGN KEY (`idset_talla`) REFERENCES `set_talla` (`idset_talla`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pedido_temporada` FOREIGN KEY (`idtemporada`) REFERENCES `temporada` (`idtemporada`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pedido_transporte` FOREIGN KEY (`idtransporte`) REFERENCES `transporte` (`idtransporte`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pedido_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pedido_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedido`
--

LOCK TABLES `pedido` WRITE;
/*!40000 ALTER TABLE `pedido` DISABLE KEYS */;
INSERT INTO `pedido` VALUES (1,1,1,2,11,'observaciones del pedido. ',0.00,0,24331.84,10.00,24321.84,'CERRADO','2026-03-25 08:27:34','admin','2026-03-25 08:36:09','admin','2026-04-01','2026-04-30',39,'mail@mail.com',1),(2,3,1,2,11,'k',0.00,0,0.00,1.00,0.00,'BORRADOR','2026-03-25 12:21:37','admin',NULL,NULL,'2026-03-01','2026-03-25',0,'prueba@mailme.com',1);
/*!40000 ALTER TABLE `pedido` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedido_detalle`
--

DROP TABLE IF EXISTS `pedido_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedido_detalle` (
  `idpedido_detalle` int NOT NULL AUTO_INCREMENT,
  `idpedido` int NOT NULL,
  `idproducto` int NOT NULL,
  `idtalla` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_lista` decimal(14,2) NOT NULL,
  `precio_venta` decimal(14,2) NOT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT '0.00',
  `cantidad_despachada` int NOT NULL DEFAULT '0',
  `cantidad_pendiente` int NOT NULL DEFAULT '0',
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idproducto_precio` int DEFAULT NULL,
  PRIMARY KEY (`idpedido_detalle`),
  KEY `idx_pedido_detalle_idpedido` (`idpedido`),
  KEY `idx_pedido_detalle_idproducto` (`idproducto`),
  KEY `idx_pedido_detalle_idtalla` (`idtalla`),
  KEY `idx_pedido_detalle_usuario_creacion` (`usuario_creacion`),
  KEY `idx_pedido_detalle_usuario_modificacion` (`usuario_modificacion`),
  KEY `fk_pedido_detalle_producto_precio` (`idproducto_precio`),
  CONSTRAINT `fk_pedido_detalle_pedido` FOREIGN KEY (`idpedido`) REFERENCES `pedido` (`idpedido`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pedido_detalle_producto` FOREIGN KEY (`idproducto`) REFERENCES `producto` (`idproducto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pedido_detalle_producto_precio` FOREIGN KEY (`idproducto_precio`) REFERENCES `producto_precio` (`idproducto_precio`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pedido_detalle_talla` FOREIGN KEY (`idtalla`) REFERENCES `talla` (`idtalla`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pedido_detalle_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pedido_detalle_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedido_detalle`
--

LOCK TABLES `pedido_detalle` WRITE;
/*!40000 ALTER TABLE `pedido_detalle` DISABLE KEYS */;
INSERT INTO `pedido_detalle` VALUES (1,1,1437,1,3,621.68,621.68,1865.04,0,3,'ACTIVO','2026-03-25 14:34:26','admin',NULL,NULL,'img/producto/1437_1434_7.jpg',1434),(2,1,1437,2,2,621.68,621.68,1243.36,0,2,'ACTIVO','2026-03-25 14:34:26','admin',NULL,NULL,'img/producto/1437_1434_7.jpg',1434),(3,1,1437,13,2,621.68,621.68,1243.36,0,2,'ACTIVO','2026-03-25 14:34:26','admin',NULL,NULL,'img/producto/1437_1434_7.jpg',1434),(4,1,1437,14,2,621.68,621.68,1243.36,0,2,'ACTIVO','2026-03-25 14:34:26','admin',NULL,NULL,'img/producto/1437_1434_7.jpg',1434),(5,1,1437,15,2,621.68,621.68,1243.36,0,2,'ACTIVO','2026-03-25 14:34:26','admin',NULL,NULL,'img/producto/1437_1434_7.jpg',1434),(6,1,1437,16,2,621.68,621.68,1243.36,0,2,'ACTIVO','2026-03-25 14:34:26','admin',NULL,NULL,'img/producto/1437_1434_7.jpg',1434),(7,1,1435,1,2,625.00,625.00,1250.00,0,2,'ACTIVO','2026-03-25 14:35:47','admin',NULL,NULL,'img/producto/1435_1432_5.jpg',1432),(8,1,1435,2,2,625.00,625.00,1250.00,0,2,'ACTIVO','2026-03-25 14:35:47','admin',NULL,NULL,'img/producto/1435_1432_5.jpg',1432),(9,1,1435,13,3,625.00,625.00,1875.00,0,3,'ACTIVO','2026-03-25 14:35:47','admin',NULL,NULL,'img/producto/1435_1432_5.jpg',1432),(10,1,1435,14,5,625.00,625.00,3125.00,0,5,'ACTIVO','2026-03-25 14:35:47','admin',NULL,NULL,'img/producto/1435_1432_5.jpg',1432),(11,1,1435,15,2,625.00,625.00,1250.00,0,2,'ACTIVO','2026-03-25 14:35:47','admin',NULL,NULL,'img/producto/1435_1432_5.jpg',1432),(12,1,1435,16,12,625.00,625.00,7500.00,0,12,'ACTIVO','2026-03-25 14:35:47','admin',NULL,NULL,'img/producto/1435_1432_5.jpg',1432),(25,2,1420,1,2,573.78,573.78,1147.56,0,2,'ACTIVO','2026-03-25 12:23:15','admin','2026-03-25 12:24:34','admin','img/producto/1420_1417_5.jpg',1417),(26,2,1420,2,2,573.78,573.78,1147.56,0,2,'ACTIVO','2026-03-25 12:23:15','admin','2026-03-25 12:24:34','admin','img/producto/1420_1417_5.jpg',1417),(27,2,1420,13,0,573.78,573.78,0.00,0,0,'ACTIVO','2026-03-25 12:23:15','admin','2026-03-25 12:24:34','admin','img/producto/1420_1417_5.jpg',1417),(28,2,1420,14,5,573.78,573.78,2868.90,0,5,'ACTIVO','2026-03-25 12:23:15','admin','2026-03-25 12:24:34','admin','img/producto/1420_1417_5.jpg',1417),(29,2,1420,15,0,573.78,573.78,0.00,0,0,'ACTIVO','2026-03-25 12:23:15','admin','2026-03-25 12:24:34','admin','img/producto/1420_1417_5.jpg',1417),(30,2,1420,16,0,573.78,573.78,0.00,0,0,'ACTIVO','2026-03-25 12:23:15','admin','2026-03-25 12:24:34','admin','img/producto/1420_1417_5.jpg',1417);
/*!40000 ALTER TABLE `pedido_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `producto`
--

DROP TABLE IF EXISTS `producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `producto` (
  `idproducto` int NOT NULL AUTO_INCREMENT,
  `idmarca` int NOT NULL,
  `idtemporada` int DEFAULT NULL,
  `linea` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modelo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idset_talla` int NOT NULL,
  `idcolor` int DEFAULT NULL,
  `idcorte` int DEFAULT NULL,
  `idtipo_suela` int DEFAULT NULL,
  `idconcepto` int DEFAULT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idproducto`),
  KEY `idx_producto_idmarca` (`idmarca`),
  KEY `idx_producto_idset_talla` (`idset_talla`),
  KEY `idx_producto_usuario_creacion` (`usuario_creacion`),
  KEY `idx_producto_usuario_modificacion` (`usuario_modificacion`),
  KEY `fk_idcolor_producto` (`idcolor`),
  KEY `fk_idcorte_producto` (`idcorte`),
  KEY `fk_idtipo_suela_producto` (`idtipo_suela`),
  KEY `fk_idconcepto_producto` (`idconcepto`),
  KEY `fk_idtemporada_producto` (`idtemporada`),
  CONSTRAINT `fk_idcolor_producto` FOREIGN KEY (`idcolor`) REFERENCES `color` (`idcolor`) ON UPDATE CASCADE,
  CONSTRAINT `fk_idconcepto_producto` FOREIGN KEY (`idconcepto`) REFERENCES `concepto` (`idconcepto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_idcorte_producto` FOREIGN KEY (`idcorte`) REFERENCES `corte` (`idcorte`) ON UPDATE CASCADE,
  CONSTRAINT `fk_idtemporada_producto` FOREIGN KEY (`idtemporada`) REFERENCES `temporada` (`idtemporada`) ON UPDATE CASCADE,
  CONSTRAINT `fk_idtipo_suela_producto` FOREIGN KEY (`idtipo_suela`) REFERENCES `tipo_suela` (`idtipo_suela`) ON UPDATE CASCADE,
  CONSTRAINT `fk_producto_marca` FOREIGN KEY (`idmarca`) REFERENCES `marca` (`idmarca`) ON UPDATE CASCADE,
  CONSTRAINT `fk_producto_set_talla` FOREIGN KEY (`idset_talla`) REFERENCES `set_talla` (`idset_talla`) ON UPDATE CASCADE,
  CONSTRAINT `fk_producto_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_producto_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1826 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producto`
--

LOCK TABLES `producto` WRITE;
/*!40000 ALTER TABLE `producto` DISABLE KEYS */;
INSERT INTO `producto` VALUES (1420,2,1,NULL,'316',11,5,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1421,2,1,NULL,'316',11,6,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1422,2,1,NULL,'316',11,7,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1423,2,1,NULL,'316',11,5,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1424,2,1,NULL,'316',11,6,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1425,2,1,NULL,'316',11,7,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1426,2,1,NULL,'316',11,5,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1427,2,1,NULL,'316',11,6,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1428,2,1,NULL,'316',11,7,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1429,2,1,NULL,'317',11,5,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1430,2,1,NULL,'317',11,6,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1431,2,1,NULL,'317',11,7,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1432,2,1,NULL,'317',11,5,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1433,2,1,NULL,'317',11,6,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1434,2,1,NULL,'317',11,7,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1435,2,1,NULL,'317',11,5,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1436,2,1,NULL,'317',11,6,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1437,2,1,NULL,'317',11,7,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1438,2,1,NULL,'317-C',11,5,5,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1439,2,1,NULL,'328',11,5,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1440,2,1,NULL,'328',11,6,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1441,2,1,NULL,'328',11,7,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1442,2,1,NULL,'328',11,5,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1443,2,1,NULL,'328',11,6,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1444,2,1,NULL,'328',11,7,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1445,2,1,NULL,'328',11,5,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1446,2,1,NULL,'328',11,6,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1447,2,1,NULL,'328',11,7,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1448,2,1,NULL,'353',11,5,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1449,2,1,NULL,'353',11,6,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1450,2,1,NULL,'353',11,7,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1451,2,1,NULL,'353',11,5,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1452,2,1,NULL,'353',11,6,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1453,2,1,NULL,'353',11,7,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1454,2,1,NULL,'353',11,5,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1455,2,1,NULL,'353',11,6,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1456,2,1,NULL,'353',11,7,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1457,2,1,NULL,'678',11,5,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1458,2,1,NULL,'678',11,6,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1459,2,1,NULL,'678',11,7,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1460,2,1,NULL,'678',11,5,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1461,2,1,NULL,'678',11,6,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1462,2,1,NULL,'678',11,7,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1463,2,1,NULL,'678',11,5,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1464,2,1,NULL,'678',11,6,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1465,2,1,NULL,'678',11,7,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1466,2,1,NULL,'710',11,5,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1467,2,1,NULL,'710',11,6,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1468,2,1,NULL,'710',11,7,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1469,2,1,NULL,'710',11,5,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1470,2,1,NULL,'710',11,6,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1471,2,1,NULL,'710',11,7,3,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1472,2,1,NULL,'710',11,5,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1473,2,1,NULL,'710',11,6,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1474,2,1,NULL,'710',11,7,4,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1475,2,1,NULL,'850',11,5,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1476,2,1,NULL,'851',11,5,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1477,2,1,NULL,'858',11,5,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1478,2,1,NULL,'954-I',11,5,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1479,2,1,NULL,'967-I',11,5,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1480,2,1,NULL,'967-C',11,5,2,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1481,2,1,NULL,'971',11,5,6,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1482,2,1,NULL,'972',11,5,6,4,3,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1483,2,1,NULL,'837',12,5,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1484,2,1,NULL,'837',12,6,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1485,2,1,NULL,'837',12,8,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1486,2,1,NULL,'837',12,9,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1487,2,1,NULL,'837',12,5,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1488,2,1,NULL,'837',12,6,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1489,2,1,NULL,'837',12,8,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1490,2,1,NULL,'837',12,9,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1491,2,1,NULL,'665',12,5,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1492,2,1,NULL,'665',12,6,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1493,2,1,NULL,'665',12,8,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1494,2,1,NULL,'665',12,9,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1495,2,1,NULL,'665',12,5,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1496,2,1,NULL,'665',12,6,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1497,2,1,NULL,'665',12,8,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1498,2,1,NULL,'665',12,9,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1499,2,1,NULL,'349',12,5,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1500,2,1,NULL,'349',12,6,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1501,2,1,NULL,'349',12,8,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1502,2,1,NULL,'349',12,9,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1503,2,1,NULL,'349',12,5,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1504,2,1,NULL,'349',12,6,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1505,2,1,NULL,'349',12,8,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1506,2,1,NULL,'349',12,9,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1507,2,1,NULL,'366',12,5,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1508,2,1,NULL,'366',12,6,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1509,2,1,NULL,'366',12,8,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1510,2,1,NULL,'366',12,9,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1511,2,1,NULL,'366',12,5,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1512,2,1,NULL,'366',12,6,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1513,2,1,NULL,'366',12,8,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1514,2,1,NULL,'366',12,9,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1515,2,1,NULL,'364',12,5,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1516,2,1,NULL,'364',12,6,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1517,2,1,NULL,'364',12,8,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1518,2,1,NULL,'364',12,9,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1519,2,1,NULL,'364',12,5,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1520,2,1,NULL,'364',12,6,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1521,2,1,NULL,'364',12,8,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1522,2,1,NULL,'364',12,9,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1523,2,1,NULL,'370',12,5,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1524,2,1,NULL,'370',12,6,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1525,2,1,NULL,'370',12,8,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1526,2,1,NULL,'370',12,9,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1527,2,1,NULL,'370',12,5,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1528,2,1,NULL,'370',12,6,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1529,2,1,NULL,'370',12,8,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1530,2,1,NULL,'370',12,9,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1531,2,1,NULL,'325',12,5,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1532,2,1,NULL,'325',12,10,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1533,2,1,NULL,'325',12,11,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1534,2,1,NULL,'325',12,12,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1535,2,1,NULL,'325',12,13,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1536,2,1,NULL,'340',12,5,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1537,2,1,NULL,'340',12,10,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1538,2,1,NULL,'340',12,11,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1539,2,1,NULL,'340',12,12,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1540,2,1,NULL,'340',12,13,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1541,2,1,NULL,'354',12,5,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1542,2,1,NULL,'354',12,10,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1543,2,1,NULL,'354',12,11,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1544,2,1,NULL,'354',12,12,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1545,2,1,NULL,'354',12,13,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1546,2,1,NULL,'359',12,5,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1547,2,1,NULL,'359',12,10,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1548,2,1,NULL,'359',12,11,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1549,2,1,NULL,'359',12,12,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1550,2,1,NULL,'359',12,13,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1551,2,1,NULL,'361',12,13,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1552,2,1,NULL,'361',12,8,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1553,2,1,NULL,'361',12,5,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1554,2,1,NULL,'744',12,5,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1555,2,1,NULL,'744',12,6,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1556,2,1,NULL,'744',12,8,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1557,2,1,NULL,'744',12,9,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1558,2,1,NULL,'744',12,5,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1559,2,1,NULL,'744',12,6,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1560,2,1,NULL,'744',12,8,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1561,2,1,NULL,'744',12,9,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1562,2,1,NULL,'856',12,5,2,4,5,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1563,2,1,NULL,'950',12,5,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1564,2,1,NULL,'950',12,6,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1565,2,1,NULL,'950',12,8,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1566,2,1,NULL,'950',12,9,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1567,2,1,NULL,'950',12,5,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1568,2,1,NULL,'950',12,6,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1569,2,1,NULL,'950',12,8,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1570,2,1,NULL,'950',12,9,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1571,2,1,NULL,'951',12,5,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1572,2,1,NULL,'951',12,6,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1573,2,1,NULL,'951',12,8,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1574,2,1,NULL,'951',12,9,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1575,2,1,NULL,'951',12,5,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1576,2,1,NULL,'951',12,6,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1577,2,1,NULL,'951',12,8,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1578,2,1,NULL,'951',12,9,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1579,2,1,NULL,'952',12,5,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1580,2,1,NULL,'952',12,10,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1581,2,1,NULL,'952',12,11,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1582,2,1,NULL,'952',12,12,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1583,2,1,NULL,'952',12,13,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1584,2,1,NULL,'955-E',12,5,2,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1585,2,1,NULL,'955-E',12,6,2,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1586,2,1,NULL,'955-E',12,8,2,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1587,2,1,NULL,'955-E',12,9,2,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1588,2,1,NULL,'955-E',12,5,8,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1589,2,1,NULL,'955-E',12,6,8,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1590,2,1,NULL,'955-E',12,8,8,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1591,2,1,NULL,'955-E',12,9,8,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1592,2,1,NULL,'955-P',12,5,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1593,2,1,NULL,'955-P',12,6,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1594,2,1,NULL,'955-P',12,8,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1595,2,1,NULL,'955-P',12,9,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1596,2,1,NULL,'955-P',12,5,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1597,2,1,NULL,'955-P',12,6,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1598,2,1,NULL,'955-P',12,8,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1599,2,1,NULL,'955-P',12,9,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1600,2,1,NULL,'955-H',12,5,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1601,2,1,NULL,'955-H',12,6,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1602,2,1,NULL,'955-H',12,8,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1603,2,1,NULL,'955-H',12,9,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1604,2,1,NULL,'955-H',12,5,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1605,2,1,NULL,'955-H',12,6,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1606,2,1,NULL,'955-H',12,8,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1607,2,1,NULL,'955-H',12,9,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1608,2,1,NULL,'956-E',12,5,2,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1609,2,1,NULL,'956-E',12,6,2,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1610,2,1,NULL,'956-E',12,8,2,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1611,2,1,NULL,'956-E',12,9,2,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1612,2,1,NULL,'956-E',12,5,8,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1613,2,1,NULL,'956-E',12,6,8,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1614,2,1,NULL,'956-E',12,8,8,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1615,2,1,NULL,'956-E',12,9,8,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1616,2,1,NULL,'956-P',12,5,2,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1617,2,1,NULL,'956-P',12,6,2,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1618,2,1,NULL,'956-P',12,8,2,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1619,2,1,NULL,'956-P',12,9,2,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1620,2,1,NULL,'956-P',12,5,8,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1621,2,1,NULL,'956-P',12,6,8,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1622,2,1,NULL,'956-P',12,8,8,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1623,2,1,NULL,'956-P',12,9,8,4,6,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1624,2,1,NULL,'956-H',12,5,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1625,2,1,NULL,'956-H',12,6,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1626,2,1,NULL,'956-H',12,8,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1627,2,1,NULL,'956-H',12,9,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1628,2,1,NULL,'956-H',12,5,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1629,2,1,NULL,'956-H',12,6,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1630,2,1,NULL,'956-H',12,8,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1631,2,1,NULL,'956-H',12,9,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1632,2,1,NULL,'962',12,5,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1633,2,1,NULL,'962',12,10,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1634,2,1,NULL,'962',12,11,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1635,2,1,NULL,'962',12,12,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1636,2,1,NULL,'962',12,13,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1637,2,1,NULL,'963',12,5,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1638,2,1,NULL,'963',12,10,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1639,2,1,NULL,'963',12,11,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1640,2,1,NULL,'963',12,12,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1641,2,1,NULL,'963',12,13,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1642,2,1,NULL,'970',12,5,10,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1643,2,1,NULL,'970-N',12,5,10,4,7,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1644,2,1,NULL,'974',12,5,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1645,2,1,NULL,'974',12,6,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1646,2,1,NULL,'974',12,8,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1647,2,1,NULL,'974',12,9,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1648,2,1,NULL,'974',12,5,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1649,2,1,NULL,'974',12,6,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1650,2,1,NULL,'974',12,8,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1651,2,1,NULL,'974',12,9,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1652,2,1,NULL,'975',12,5,9,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1653,2,1,NULL,'976',12,5,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1654,2,1,NULL,'976',12,6,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1655,2,1,NULL,'976',12,8,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1656,2,1,NULL,'976',12,9,2,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1657,2,1,NULL,'976',12,5,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1658,2,1,NULL,'976',12,6,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1659,2,1,NULL,'976',12,8,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1660,2,1,NULL,'976',12,9,8,4,4,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1683,3,1,'04 ANGEL','4',18,14,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:13','admin',NULL,NULL),(1684,3,1,'04 ANGEL','4',18,15,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:13','admin',NULL,NULL),(1685,3,1,'04 ANGEL','4',18,16,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:13','admin',NULL,NULL),(1686,3,1,'04 ANGEL','4',18,17,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:13','admin',NULL,NULL),(1687,3,1,'04 ANGEL','4',18,18,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:14','admin',NULL,NULL),(1688,4,1,NULL,'370-001-01',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1689,4,1,NULL,'370-001-03',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1690,4,1,NULL,'370-001-06',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1691,4,1,NULL,'370-001-12',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1692,4,1,NULL,'370-001- 19',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1693,4,1,NULL,'369-001-01',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1694,4,1,NULL,'369-001-02',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1695,4,1,NULL,'369-001-03',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1696,4,1,NULL,'369-001-04',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1697,4,1,NULL,'369-001-05',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1698,4,1,NULL,'369-001-06',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1699,4,1,NULL,'369-002-02',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1700,4,1,NULL,'369-002-03',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1701,4,1,NULL,'369-002-05',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1702,4,1,NULL,'369-002-07',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1703,4,1,NULL,'369-002-09',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1711,4,1,NULL,'369-002-08',11,22,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin','2026-03-18 16:12:05','admin'),(1712,4,1,NULL,'368-001-01',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1713,4,1,NULL,'368-001-02',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1714,4,1,NULL,'368-001-04',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1715,4,1,NULL,'368-001-07',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1716,4,1,NULL,'368-001- 14',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1717,4,1,NULL,'368-001-16',11,19,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1718,4,1,NULL,'347-001-01',11,21,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1719,4,1,NULL,'347-001-01',11,22,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1720,4,1,NULL,'347-001-01',11,23,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1721,4,1,NULL,'347-001-01',11,24,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1722,4,1,NULL,'347-002-01',11,21,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1723,4,1,NULL,'347-002-01',11,22,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1724,4,1,NULL,'347-002-01',11,23,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1725,4,1,NULL,'347-002-01',11,24,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1726,4,1,NULL,'347-005-01',11,21,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1727,4,1,NULL,'347-005-01',11,22,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1728,4,1,NULL,'347-005-01',11,23,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1729,4,1,NULL,'347-005-01',11,24,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1730,4,1,NULL,'347-007-01',11,21,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1731,4,1,NULL,'347-007-01',11,22,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1732,4,1,NULL,'347-007-01',11,23,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1733,4,1,NULL,'347-007-01',11,24,NULL,NULL,NULL,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1754,5,2,NULL,'6000',19,26,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1755,5,2,NULL,'6001',19,27,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1756,5,2,NULL,'6002',19,28,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1757,5,2,NULL,'6003',19,27,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1758,5,2,NULL,'6004',19,27,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1759,5,2,NULL,'6005',19,27,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1760,5,2,NULL,'6014',19,27,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1761,5,2,NULL,'6015',19,27,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1762,5,2,NULL,'6016',19,27,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1763,5,2,NULL,'6017',19,27,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1764,5,2,NULL,'6006',19,27,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1765,5,2,NULL,'6007',19,27,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1766,5,2,NULL,'6008',19,26,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1767,5,2,NULL,'6009',19,26,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1768,5,2,NULL,'6011',19,26,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1769,5,2,NULL,'6012',19,26,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1770,5,2,NULL,'6010',19,26,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1771,5,2,NULL,'6013',19,26,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1772,6,1,NULL,'110507',19,13,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1773,6,1,NULL,'110605',19,29,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1774,6,1,NULL,'110607',19,29,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1775,6,1,NULL,'110752',19,29,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1776,6,1,NULL,'110753',19,29,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1777,6,1,NULL,'110933',19,29,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1778,6,1,NULL,'110934',19,29,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1779,6,1,NULL,'111104',19,29,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1780,6,1,NULL,'111105',19,29,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1781,6,1,NULL,'111506',19,29,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1782,6,1,NULL,'111507',19,26,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1783,6,1,NULL,'111509',19,5,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1784,6,1,NULL,'111510',19,5,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1785,6,1,NULL,'111603',19,5,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1786,6,1,NULL,'111607',19,5,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1787,6,1,NULL,'111702',19,5,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1788,6,1,NULL,'111704',19,5,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1789,6,1,NULL,'111707',19,5,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1790,6,1,NULL,'111721',19,5,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1791,6,1,NULL,'111801',19,5,NULL,NULL,NULL,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1820,7,2,NULL,'1240',38,32,NULL,5,NULL,'ACTIVO','2026-03-19 11:57:53','admin','2026-03-19 11:59:13','admin'),(1821,7,2,NULL,'1241',38,32,NULL,5,NULL,'ACTIVO','2026-03-19 11:57:53','admin','2026-03-19 11:59:13','admin'),(1822,7,2,NULL,'1242',38,34,NULL,5,NULL,'ACTIVO','2026-03-19 11:57:53','admin','2026-03-19 11:59:13','admin'),(1823,7,2,NULL,'2710',38,35,NULL,6,NULL,'ACTIVO','2026-03-19 11:57:53','admin','2026-03-19 11:59:13','admin'),(1824,7,2,NULL,'2711',38,32,NULL,6,NULL,'ACTIVO','2026-03-19 11:57:53','admin','2026-03-19 11:59:13','admin'),(1825,7,2,NULL,'2712',39,38,NULL,6,NULL,'ACTIVO','2026-03-19 11:57:53','admin','2026-03-19 11:59:13','admin');
/*!40000 ALTER TABLE `producto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `producto_precio`
--

DROP TABLE IF EXISTS `producto_precio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `producto_precio` (
  `idproducto_precio` int NOT NULL AUTO_INCREMENT,
  `idproducto` int NOT NULL,
  `material` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio` decimal(14,2) NOT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idproducto_precio`),
  KEY `idx_producto_precio_idproducto` (`idproducto`),
  KEY `idx_producto_precio_usuario_creacion` (`usuario_creacion`),
  KEY `idx_producto_precio_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_producto_precio_producto` FOREIGN KEY (`idproducto`) REFERENCES `producto` (`idproducto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_producto_precio_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_producto_precio_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1822 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producto_precio`
--

LOCK TABLES `producto_precio` WRITE;
/*!40000 ALTER TABLE `producto_precio` DISABLE KEYS */;
INSERT INTO `producto_precio` VALUES (1417,1420,'PIEL 1',573.78,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1418,1421,'PIEL 1',573.78,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1419,1422,'PIEL 1',573.78,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1420,1423,'PIEL 1',573.78,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1421,1424,'PIEL 1',573.78,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1422,1425,'PIEL 1',573.78,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1423,1426,'PIEL 1',573.78,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1424,1427,'PIEL 1',573.78,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1425,1428,'PIEL 1',573.78,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1426,1429,'PIEL 1',621.68,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1427,1430,'PIEL 1',621.68,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1428,1431,'PIEL 1',621.68,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1429,1432,'PIEL 1',621.68,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1430,1433,'PIEL 1',621.68,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1431,1434,'PIEL 1',621.68,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1432,1435,'PIEL 1',621.68,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1433,1436,'PIEL 1',621.68,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1434,1437,'PIEL 1',621.68,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1435,1438,'PIEL 1',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1436,1439,'PIEL 1',557.50,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1437,1440,'PIEL 1',557.50,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1438,1441,'PIEL 1',557.50,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1439,1442,'PIEL 1',557.50,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1440,1443,'PIEL 1',557.50,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1441,1444,'PIEL 1',557.50,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1442,1445,'PIEL 1',557.50,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1443,1446,'PIEL 1',557.50,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1444,1447,'PIEL 1',557.50,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1445,1448,'PIEL 1',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1446,1449,'PIEL 1',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1447,1450,'PIEL 1',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1448,1451,'PIEL 1',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1449,1452,'PIEL 1',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1450,1453,'PIEL 1',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1451,1454,'PIEL 1',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1452,1455,'PIEL 1',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1453,1456,'PIEL 1',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1454,1457,'PIEL 1',615.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1455,1458,'PIEL 1',615.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1456,1459,'PIEL 1',615.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1457,1460,'PIEL 1',615.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1458,1461,'PIEL 1',615.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1459,1462,'PIEL 1',615.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1460,1463,'PIEL 1',615.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1461,1464,'PIEL 1',615.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1462,1465,'PIEL 1',615.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1463,1466,'PIEL 1',579.53,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1464,1467,'PIEL 1',579.53,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1465,1468,'PIEL 1',579.53,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1466,1469,'PIEL 1',579.53,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1467,1470,'PIEL 1',579.53,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1468,1471,'PIEL 1',579.53,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1469,1472,'PIEL 1',579.53,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1470,1473,'PIEL 1',579.53,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1471,1474,'PIEL 1',579.53,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1472,1475,'PIEL 1',627.42,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1473,1476,'PIEL 1',592.94,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1474,1477,'PIEL 1',632.21,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1475,1478,'PIEL 1',574.74,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1476,1479,'PIEL 1',588.69,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1477,1480,'PIEL 1',571.02,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1478,1481,'PIEL 1',465.00,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1479,1482,'PIEL 1',474.30,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1480,1483,'PIEL 2',510.56,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1481,1484,'PIEL 2',510.56,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1482,1485,'PIEL 2',510.56,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1483,1486,'PIEL 2',510.56,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1484,1487,'PIEL 2',510.56,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1485,1488,'PIEL 2',510.56,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1486,1489,'PIEL 2',510.56,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1487,1490,'PIEL 2',510.56,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1488,1491,'PIEL 2',529.72,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1489,1492,'PIEL 2',529.72,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1490,1493,'PIEL 2',529.72,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1491,1494,'PIEL 2',529.72,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1492,1495,'PIEL 2',529.72,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1493,1496,'PIEL 2',529.72,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1494,1497,'PIEL 2',529.72,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1495,1498,'PIEL 2',529.72,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1496,1499,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1497,1500,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1498,1501,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1499,1502,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1500,1503,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1501,1504,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1502,1505,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1503,1506,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1504,1507,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1505,1508,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1506,1509,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1507,1510,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1508,1511,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1509,1512,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1510,1513,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1511,1514,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1512,1515,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1513,1516,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1514,1517,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1515,1518,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1516,1519,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1517,1520,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1518,1521,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1519,1522,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1520,1523,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1521,1524,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1522,1525,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1523,1526,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1524,1527,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1525,1528,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1526,1529,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1527,1530,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1528,1531,'PIEL 2',455.00,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1529,1532,'PIEL 2',455.00,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1530,1533,'PIEL 2',455.00,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1531,1534,'PIEL 2',455.00,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1532,1535,'PIEL 2',455.00,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1533,1536,'PIEL 2',491.40,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1534,1537,'PIEL 2',491.40,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1535,1538,'PIEL 2',491.40,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1536,1539,'PIEL 2',491.40,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1537,1540,'PIEL 2',491.40,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1538,1541,'PIEL 2',483.74,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1539,1542,'PIEL 2',483.74,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1540,1543,'PIEL 2',483.74,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1541,1544,'PIEL 2',483.74,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1542,1545,'PIEL 2',483.74,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1543,1546,'PIEL 2',459.79,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1544,1547,'PIEL 2',459.79,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1545,1548,'PIEL 2',459.79,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1546,1549,'PIEL 2',459.79,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1547,1550,'PIEL 2',459.79,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1548,1551,'PIEL 2',469.37,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1549,1552,'PIEL 2',469.37,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1550,1553,'PIEL 2',469.37,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1551,1554,'PIEL 2',517.27,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1552,1555,'PIEL 2',517.27,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1553,1556,'PIEL 2',517.27,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1554,1557,'PIEL 2',517.27,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1555,1558,'PIEL 2',517.27,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1556,1559,'PIEL 2',517.27,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1557,1560,'PIEL 2',517.27,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1558,1561,'PIEL 2',517.27,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1559,1562,'PIEL 2',526.85,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1560,1563,'PIEL 2',478.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1561,1564,'PIEL 2',478.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1562,1565,'PIEL 2',478.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1563,1566,'PIEL 2',478.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1564,1567,'PIEL 2',478.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1565,1568,'PIEL 2',478.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1566,1569,'PIEL 2',478.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1567,1570,'PIEL 2',478.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1568,1571,'PIEL 2',536.42,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1569,1572,'PIEL 2',536.42,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1570,1573,'PIEL 2',536.42,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1571,1574,'PIEL 2',536.42,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1572,1575,'PIEL 2',536.42,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1573,1576,'PIEL 2',536.42,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1574,1577,'PIEL 2',536.42,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1575,1578,'PIEL 2',536.42,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1576,1579,'PIEL 2',500.98,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1577,1580,'PIEL 2',500.98,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1578,1581,'PIEL 2',500.98,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1579,1582,'PIEL 2',500.98,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1580,1583,'PIEL 2',500.98,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1581,1584,'PIEL 2',565.16,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1582,1585,'PIEL 2',565.16,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1583,1586,'PIEL 2',565.16,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1584,1587,'PIEL 2',565.16,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1585,1588,'PIEL 2',565.16,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1586,1589,'PIEL 2',565.16,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1587,1590,'PIEL 2',565.16,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1588,1591,'PIEL 2',565.16,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1589,1592,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1590,1593,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1591,1594,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1592,1595,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1593,1596,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1594,1597,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1595,1598,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1596,1599,'PIEL 2',555.58,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1597,1600,'PIEL 2',546.00,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1598,1601,'PIEL 2',546.00,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1599,1602,'PIEL 2',546.00,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1600,1603,'PIEL 2',546.00,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1601,1604,'PIEL 2',546.00,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1602,1605,'PIEL 2',546.00,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1603,1606,'PIEL 2',546.00,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1604,1607,'PIEL 2',546.00,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1605,1608,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1606,1609,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1607,1610,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1608,1611,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1609,1612,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1610,1613,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1611,1614,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1612,1615,'PIEL 2',569.95,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1613,1616,'PIEL 2',532.59,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1614,1617,'PIEL 2',532.59,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1615,1618,'PIEL 2',532.59,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1616,1619,'PIEL 2',532.59,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1617,1620,'PIEL 2',532.59,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1618,1621,'PIEL 2',532.59,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1619,1622,'PIEL 2',532.59,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1620,1623,'PIEL 2',532.59,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1621,1624,'PIEL 2',524.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1622,1625,'PIEL 2',524.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1623,1626,'PIEL 2',524.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1624,1627,'PIEL 2',524.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1625,1628,'PIEL 2',524.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1626,1629,'PIEL 2',524.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1627,1630,'PIEL 2',524.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1628,1631,'PIEL 2',524.93,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1629,1632,'PIEL 2',492.36,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1630,1633,'PIEL 2',492.36,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1631,1634,'PIEL 2',492.36,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1632,1635,'PIEL 2',492.36,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1633,1636,'PIEL 2',492.36,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1634,1637,'PIEL 2',472.44,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1635,1638,'PIEL 2',472.44,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1636,1639,'PIEL 2',472.44,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1637,1640,'PIEL 2',472.44,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1638,1641,'PIEL 2',472.44,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1639,1642,'PIEL 2',427.80,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1640,1643,'PIEL 2',469.65,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1641,1644,'PIEL 2',520.80,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1642,1645,'PIEL 2',520.80,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1643,1646,'PIEL 2',520.80,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1644,1647,'PIEL 2',520.80,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1645,1648,'PIEL 2',520.80,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1646,1649,'PIEL 2',520.80,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1647,1650,'PIEL 2',520.80,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1648,1651,'PIEL 2',520.80,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1649,1652,'PIEL 2',449.19,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1650,1653,'PIEL 2',530.10,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1651,1654,'PIEL 2',530.10,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1652,1655,'PIEL 2',530.10,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1653,1656,'PIEL 2',530.10,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1654,1657,'PIEL 2',530.10,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1655,1658,'PIEL 2',530.10,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1656,1659,'PIEL 2',530.10,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1657,1660,'PIEL 2',530.10,'ACTIVO','2026-03-18 11:07:29','admin',NULL,NULL),(1679,1683,'SINTETICO',154.48,'ACTIVO','2026-03-18 15:09:13','admin',NULL,NULL),(1680,1684,'SINTETICO',155.43,'ACTIVO','2026-03-18 15:09:13','admin',NULL,NULL),(1681,1685,'SINTETICO',159.23,'ACTIVO','2026-03-18 15:09:13','admin',NULL,NULL),(1682,1686,'SINTETICO',156.22,'ACTIVO','2026-03-18 15:09:14','admin',NULL,NULL),(1683,1687,'SINTETICO',156.06,'ACTIVO','2026-03-18 15:09:14','admin',NULL,NULL),(1684,1688,NULL,287.62,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1685,1689,NULL,287.62,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1686,1690,NULL,287.62,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1687,1691,NULL,287.62,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1688,1692,NULL,287.62,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1689,1693,NULL,346.53,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1690,1694,NULL,346.53,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1691,1695,NULL,346.53,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1692,1696,NULL,346.53,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1693,1697,NULL,346.53,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1694,1698,NULL,346.53,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1695,1699,NULL,346.53,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1696,1700,NULL,346.53,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1697,1701,NULL,346.53,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1698,1702,NULL,346.53,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1699,1703,NULL,346.53,'ACTIVO','2026-03-18 15:09:35','admin',NULL,NULL),(1707,1711,NULL,340.00,'ACTIVO','2026-03-18 15:15:31','admin',NULL,'admin'),(1708,1712,NULL,287.62,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1709,1713,NULL,287.62,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1710,1714,NULL,287.62,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1711,1715,NULL,287.62,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1712,1716,NULL,287.62,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1713,1717,NULL,287.62,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1714,1718,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1715,1719,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1716,1720,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1717,1721,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1718,1722,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1719,1723,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1720,1724,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1721,1725,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1722,1726,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1723,1727,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1724,1728,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1725,1729,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1726,1730,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1727,1731,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1728,1732,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1729,1733,NULL,250.80,'ACTIVO','2026-03-18 15:15:31','admin',NULL,NULL),(1750,1754,NULL,218.98,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1751,1755,NULL,186.15,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1752,1756,NULL,185.85,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1753,1757,NULL,194.66,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1754,1758,NULL,199.22,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1755,1759,NULL,220.35,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1756,1760,NULL,172.47,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1757,1761,NULL,175.51,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1758,1762,NULL,181.59,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1759,1763,NULL,173.23,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1760,1764,NULL,201.50,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1761,1765,NULL,192.84,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1762,1766,NULL,198.46,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1763,1767,NULL,201.50,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1764,1768,NULL,203.93,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1765,1769,NULL,192.38,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1766,1770,NULL,265.94,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1767,1771,NULL,314.72,'ACTIVO','2026-03-19 10:13:44','admin','2026-03-19 10:17:54','admin'),(1768,1772,NULL,339.57,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1769,1773,NULL,351.94,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1770,1774,NULL,334.07,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1771,1775,NULL,391.80,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1772,1776,NULL,413.79,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1773,1777,NULL,339.57,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1774,1778,NULL,351.94,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1775,1779,NULL,349.19,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1776,1780,NULL,357.44,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1777,1781,NULL,305.21,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1778,1782,NULL,325.82,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1779,1783,NULL,285.96,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1780,1784,NULL,307.96,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1781,1785,NULL,346.44,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1782,1786,NULL,336.82,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1783,1787,NULL,356.06,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1784,1788,NULL,393.17,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1785,1789,NULL,382.18,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1786,1790,NULL,395.92,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1787,1791,NULL,367.06,'ACTIVO','2026-03-19 10:51:41','admin','2026-03-19 10:57:16','admin'),(1816,1820,NULL,214.00,'ACTIVO','2026-03-19 11:57:53','admin','2026-03-19 11:59:13','admin'),(1817,1821,NULL,214.00,'ACTIVO','2026-03-19 11:57:53','admin','2026-03-19 11:59:13','admin'),(1818,1822,NULL,246.00,'ACTIVO','2026-03-19 11:57:53','admin','2026-03-19 11:59:13','admin'),(1819,1823,NULL,217.00,'ACTIVO','2026-03-19 11:57:53','admin','2026-03-19 11:59:13','admin'),(1820,1824,NULL,217.00,'ACTIVO','2026-03-19 11:57:53','admin','2026-03-19 11:59:13','admin'),(1821,1825,NULL,217.00,'ACTIVO','2026-03-19 11:57:53','admin','2026-03-19 11:59:13','admin');
/*!40000 ALTER TABLE `producto_precio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `set_talla`
--

DROP TABLE IF EXISTS `set_talla`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `set_talla` (
  `idset_talla` int NOT NULL AUTO_INCREMENT,
  `grupo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idset_talla`),
  UNIQUE KEY `uq_set_talla_grupo` (`grupo`),
  KEY `idx_set_talla_usuario_creacion` (`usuario_creacion`),
  KEY `idx_set_talla_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_set_talla_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_set_talla_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `set_talla`
--

LOCK TABLES `set_talla` WRITE;
/*!40000 ALTER TABLE `set_talla` DISABLE KEYS */;
INSERT INTO `set_talla` VALUES (1,'1','4','ACTIVO','2026-03-16 11:16:57','admin',NULL,NULL),(2,'2','3','ACTIVO','2026-03-17 09:47:33','admin',NULL,NULL),(3,'3','2','ACTIVO','2026-03-18 11:01:07','admin',NULL,NULL),(4,'4','1','ACTIVO','2026-03-18 11:01:07','admin',NULL,NULL),(11,'24-29',NULL,'ACTIVO','2026-03-18 11:05:20','admin',NULL,NULL),(12,'22-25',NULL,'ACTIVO','2026-03-18 11:05:21','admin',NULL,NULL),(18,'16-22',NULL,'ACTIVO','2026-03-18 12:21:49','admin',NULL,NULL),(19,'24-31',NULL,'ACTIVO','2026-03-19 10:08:59','admin',NULL,NULL),(38,'33-40',NULL,'ACTIVO','2026-03-19 11:57:53','admin',NULL,NULL),(39,'32-25','32-35','ACTIVO','2026-03-19 11:59:13','admin',NULL,'admin'),(40,'30-31','30-31','ACTIVO','2026-03-23 12:37:05','admin',NULL,'admin'),(41,'25-31',NULL,'ACTIVO','2026-03-25 17:03:57','admin',NULL,NULL),(42,'25-27',NULL,'ACTIVO','2026-03-25 17:04:24','admin',NULL,NULL);
/*!40000 ALTER TABLE `set_talla` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `set_talla_detalle`
--

DROP TABLE IF EXISTS `set_talla_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `set_talla_detalle` (
  `idset_talla_detalle` int NOT NULL AUTO_INCREMENT,
  `idset_talla` int NOT NULL,
  `idtalla` int NOT NULL,
  `orden` int NOT NULL DEFAULT '0',
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idset_talla_detalle`),
  UNIQUE KEY `uq_set_talla_detalle` (`idset_talla`,`idtalla`),
  KEY `idx_set_talla_detalle_idtalla` (`idtalla`),
  KEY `idx_set_talla_detalle_usuario_creacion` (`usuario_creacion`),
  KEY `idx_set_talla_detalle_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_set_talla_detalle_set_talla` FOREIGN KEY (`idset_talla`) REFERENCES `set_talla` (`idset_talla`) ON UPDATE CASCADE,
  CONSTRAINT `fk_set_talla_detalle_talla` FOREIGN KEY (`idtalla`) REFERENCES `talla` (`idtalla`) ON UPDATE CASCADE,
  CONSTRAINT `fk_set_talla_detalle_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_set_talla_detalle_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=92 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `set_talla_detalle`
--

LOCK TABLES `set_talla_detalle` WRITE;
/*!40000 ALTER TABLE `set_talla_detalle` DISABLE KEYS */;
INSERT INTO `set_talla_detalle` VALUES (10,11,16,0,'ACTIVO','2026-03-18 11:05:20','admin',NULL,NULL),(11,11,1,0,'ACTIVO','2026-03-18 11:05:20','admin',NULL,NULL),(12,11,13,0,'ACTIVO','2026-03-18 11:05:20','admin',NULL,NULL),(13,11,14,0,'ACTIVO','2026-03-18 11:05:20','admin',NULL,NULL),(14,11,15,0,'ACTIVO','2026-03-18 11:05:20','admin',NULL,NULL),(15,11,2,0,'ACTIVO','2026-03-18 11:05:21','admin',NULL,NULL),(16,12,19,0,'ACTIVO','2026-03-18 11:05:21','admin',NULL,NULL),(17,12,18,0,'ACTIVO','2026-03-18 11:05:21','admin',NULL,NULL),(18,12,16,0,'ACTIVO','2026-03-18 11:05:21','admin',NULL,NULL),(19,12,1,0,'ACTIVO','2026-03-18 11:05:21','admin',NULL,NULL),(20,18,20,0,'ACTIVO','2026-03-18 12:21:49','admin',NULL,NULL),(21,18,21,0,'ACTIVO','2026-03-18 12:21:49','admin',NULL,NULL),(22,18,22,0,'ACTIVO','2026-03-18 12:21:49','admin',NULL,NULL),(23,18,23,0,'ACTIVO','2026-03-18 12:21:49','admin',NULL,NULL),(24,18,24,0,'ACTIVO','2026-03-18 12:21:49','admin',NULL,NULL),(25,18,25,0,'ACTIVO','2026-03-18 12:21:49','admin',NULL,NULL),(26,18,19,0,'ACTIVO','2026-03-18 12:21:49','admin',NULL,NULL),(27,19,16,0,'ACTIVO','2026-03-19 10:08:59','admin',NULL,NULL),(28,19,1,0,'ACTIVO','2026-03-19 10:08:59','admin',NULL,NULL),(29,19,13,0,'ACTIVO','2026-03-19 10:08:59','admin',NULL,NULL),(30,19,14,0,'ACTIVO','2026-03-19 10:08:59','admin',NULL,NULL),(31,19,15,0,'ACTIVO','2026-03-19 10:08:59','admin',NULL,NULL),(32,19,2,0,'ACTIVO','2026-03-19 10:08:59','admin',NULL,NULL),(33,19,26,0,'ACTIVO','2026-03-19 10:08:59','admin',NULL,NULL),(34,19,27,0,'ACTIVO','2026-03-19 10:08:59','admin',NULL,NULL),(77,38,29,0,'ACTIVO','2026-03-19 11:57:53','admin',NULL,NULL),(78,38,30,0,'ACTIVO','2026-03-19 11:57:53','admin',NULL,NULL),(79,38,31,0,'ACTIVO','2026-03-19 11:57:53','admin',NULL,NULL),(80,38,32,0,'ACTIVO','2026-03-19 11:57:53','admin',NULL,NULL),(81,38,33,0,'ACTIVO','2026-03-19 11:57:53','admin',NULL,NULL),(82,38,34,0,'ACTIVO','2026-03-19 11:57:53','admin',NULL,NULL),(83,38,35,0,'ACTIVO','2026-03-19 11:57:53','admin',NULL,NULL),(84,38,36,0,'ACTIVO','2026-03-19 11:57:53','admin',NULL,NULL),(86,40,2,0,'ACTIVO','2026-03-23 12:37:13','admin',NULL,NULL),(87,40,1,0,'ACTIVO','2026-03-25 15:50:27','admin',NULL,NULL),(88,40,13,0,'ACTIVO','2026-03-25 15:50:28','admin',NULL,NULL),(89,40,15,0,'ACTIVO','2026-03-25 15:50:29','admin',NULL,NULL),(90,40,14,0,'ACTIVO','2026-03-25 15:50:30','admin',NULL,NULL),(91,40,16,0,'ACTIVO','2026-03-25 15:50:31','admin',NULL,NULL);
/*!40000 ALTER TABLE `set_talla_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `talla`
--

DROP TABLE IF EXISTS `talla`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `talla` (
  `idtalla` int NOT NULL AUTO_INCREMENT,
  `numero` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idtalla`),
  UNIQUE KEY `uq_talla_numero` (`numero`),
  KEY `idx_talla_usuario_creacion` (`usuario_creacion`),
  KEY `idx_talla_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_talla_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_talla_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `talla`
--

LOCK TABLES `talla` WRITE;
/*!40000 ALTER TABLE `talla` DISABLE KEYS */;
INSERT INTO `talla` VALUES (1,'42','ACTIVO','2026-03-16 12:05:40','admin',NULL,'admin'),(2,'29','ACTIVO','2026-03-16 12:05:40','admin',NULL,NULL),(13,'26','ACTIVO','2026-03-16 12:05:40','admin',NULL,NULL),(14,'27','ACTIVO','2026-03-16 12:05:40','admin',NULL,NULL),(15,'28','ACTIVO','2026-03-16 12:05:40','admin',NULL,NULL),(16,'24','ACTIVO','2026-03-18 10:59:50','admin',NULL,NULL),(18,'23','ACTIVO','2026-03-16 12:05:40','admin',NULL,NULL),(19,'22','ACTIVO','2026-03-16 12:05:40','admin',NULL,NULL),(20,'16','ACTIVO','2026-03-16 12:05:40','admin',NULL,NULL),(21,'17','ACTIVO','2026-03-16 12:05:40','admin',NULL,NULL),(22,'18','ACTIVO','2026-03-16 12:05:40','admin',NULL,NULL),(23,'19','ACTIVO','2026-03-16 12:05:40','admin',NULL,NULL),(24,'20','ACTIVO','2026-03-16 12:05:40','admin',NULL,NULL),(25,'21','ACTIVO','2026-03-16 12:05:40','admin',NULL,NULL),(26,'30','ACTIVO','2026-03-18 16:32:39','admin',NULL,NULL),(27,'31','ACTIVO','2026-03-18 16:32:39','admin',NULL,NULL),(28,'32','ACTIVO','2026-03-18 16:32:39','admin',NULL,NULL),(29,'33','ACTIVO','2026-03-18 16:32:39','admin',NULL,NULL),(30,'34','ACTIVO','2026-03-18 16:32:39','admin',NULL,NULL),(31,'35','ACTIVO','2026-03-18 16:32:39','admin',NULL,NULL),(32,'36','ACTIVO','2026-03-18 16:32:39','admin',NULL,NULL),(33,'37','ACTIVO','2026-03-18 16:32:39','admin',NULL,NULL),(34,'38','ACTIVO','2026-03-18 16:32:39','admin',NULL,NULL),(35,'39','ACTIVO','2026-03-18 16:32:39','admin',NULL,NULL),(36,'40','ACTIVO','2026-03-18 16:32:39','admin',NULL,NULL),(37,'41','ACTIVO','2026-03-18 16:32:39','admin',NULL,NULL);
/*!40000 ALTER TABLE `talla` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `temporada`
--

DROP TABLE IF EXISTS `temporada`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `temporada` (
  `idtemporada` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idtemporada`),
  UNIQUE KEY `uq_temporada_nombre` (`nombre`),
  KEY `idx_temporada_usuario_creacion` (`usuario_creacion`),
  KEY `idx_temporada_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_temporada_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_temporada_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temporada`
--

LOCK TABLES `temporada` WRITE;
/*!40000 ALTER TABLE `temporada` DISABLE KEYS */;
INSERT INTO `temporada` VALUES (1,'Temporada 1','2026-01-01','2026-12-31','ACTIVO','2026-03-16 11:18:33','admin',NULL,NULL),(2,'Temporada 2','2026-03-01','2026-03-22','ACTIVO','2026-03-18 16:31:03','admin',NULL,'admin'),(3,'asdasd','2026-03-14',NULL,'ACTIVO','2026-03-18 16:32:01','admin',NULL,NULL);
/*!40000 ALTER TABLE `temporada` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_contacto`
--

DROP TABLE IF EXISTS `tipo_contacto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_contacto` (
  `idtipo_contacto` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idtipo_contacto`),
  UNIQUE KEY `uq_tipo_contacto_descripcion` (`descripcion`),
  KEY `idx_tipo_contacto_usuario_creacion` (`usuario_creacion`),
  KEY `idx_tipo_contacto_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_tipo_contacto_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_tipo_contacto_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_contacto`
--

LOCK TABLES `tipo_contacto` WRITE;
/*!40000 ALTER TABLE `tipo_contacto` DISABLE KEYS */;
INSERT INTO `tipo_contacto` VALUES (1,'Tipo de contacto 1','ACTIVO','2026-03-18 16:30:25','admin',NULL,'admin');
/*!40000 ALTER TABLE `tipo_contacto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_pago`
--

DROP TABLE IF EXISTS `tipo_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_pago` (
  `idtipo_pago` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idtipo_pago`),
  UNIQUE KEY `uq_tipo_pago_descripcion` (`descripcion`),
  KEY `idx_tipo_pago_usuario_creacion` (`usuario_creacion`),
  KEY `idx_tipo_pago_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_tipo_pago_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_tipo_pago_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_pago`
--

LOCK TABLES `tipo_pago` WRITE;
/*!40000 ALTER TABLE `tipo_pago` DISABLE KEYS */;
INSERT INTO `tipo_pago` VALUES (1,'Tipo pago 1','ACTIVO','2026-03-18 16:40:14','admin',NULL,'admin');
/*!40000 ALTER TABLE `tipo_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_suela`
--

DROP TABLE IF EXISTS `tipo_suela`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_suela` (
  `idtipo_suela` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idtipo_suela`),
  UNIQUE KEY `uq_suela_nombre` (`nombre`),
  KEY `idx_suela_usuario_creacion` (`usuario_creacion`),
  KEY `idx_suela_usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `fk_suela_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_suela_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_suela`
--

LOCK TABLES `tipo_suela` WRITE;
/*!40000 ALTER TABLE `tipo_suela` DISABLE KEYS */;
INSERT INTO `tipo_suela` VALUES (1,'','ACTIVO','2026-03-16 15:39:55','admin',NULL,NULL),(2,'NEGRO, PAJA, GRIS CAFÉ','ACTIVO','2026-03-16 15:59:33','admin',NULL,NULL),(3,'NEGRO','ACTIVO','2026-03-16 15:59:33','admin',NULL,NULL),(4,'TPU/PU','ACTIVO','2026-03-17 09:09:39','admin',NULL,NULL),(5,'MIRANDA','ACTIVO','2026-03-19 11:40:51','admin',NULL,NULL),(6,'BARRY','ACTIVO','2026-03-19 11:51:33','admin',NULL,NULL),(7,'TIPOSUELA1','ACTIVO','2026-03-24 10:13:22','admin',NULL,'admin');
/*!40000 ALTER TABLE `tipo_suela` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transporte`
--

DROP TABLE IF EXISTS `transporte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transporte` (
  `idtransporte` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVO',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_creacion` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `usuario_modificacion` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idtransporte`),
  KEY `usuario_creacion` (`usuario_creacion`),
  KEY `usuario_modificacion` (`usuario_modificacion`),
  CONSTRAINT `transporte_ibfk_1` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `transporte_ibfk_2` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transporte`
--

LOCK TABLES `transporte` WRITE;
/*!40000 ALTER TABLE `transporte` DISABLE KEYS */;
INSERT INTO `transporte` VALUES (1,'Trans','ACTIVO','2026-03-23 12:41:41','admin','2026-03-23 12:41:45','admin');
/*!40000 ALTER TABLE `transporte` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `view_cliente_contacto`
--

DROP TABLE IF EXISTS `view_cliente_contacto`;
/*!50001 DROP VIEW IF EXISTS `view_cliente_contacto`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_cliente_contacto` AS SELECT 
 1 AS `idcliente_contacto`,
 1 AS `idcliente`,
 1 AS `nombre_contacto`,
 1 AS `telefono_contacto`,
 1 AS `correo_contacto`,
 1 AS `estado_contacto`,
 1 AS `observaciones_contacto`,
 1 AS `tipo_contacto`,
 1 AS `idtipo_contacto`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_marca_set_talla`
--

DROP TABLE IF EXISTS `view_marca_set_talla`;
/*!50001 DROP VIEW IF EXISTS `view_marca_set_talla`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_marca_set_talla` AS SELECT 
 1 AS `idmarca`,
 1 AS `nombre`,
 1 AS `estado`,
 1 AS `idset_talla_preferido`,
 1 AS `idset_talla`,
 1 AS `grupo`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_pedido_detalle`
--

DROP TABLE IF EXISTS `view_pedido_detalle`;
/*!50001 DROP VIEW IF EXISTS `view_pedido_detalle`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_pedido_detalle` AS SELECT 
 1 AS `idpedido_detalle`,
 1 AS `idpedido`,
 1 AS `imagen`,
 1 AS `idproducto`,
 1 AS `idproducto_precio`,
 1 AS `codigo`,
 1 AS `descripcion`,
 1 AS `idcolor`,
 1 AS `color`,
 1 AS `marca`,
 1 AS `material`,
 1 AS `precio_venta`,
 1 AS `cantidad`,
 1 AS `subtotal`,
 1 AS `idtalla`,
 1 AS `talla`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_pedidos`
--

DROP TABLE IF EXISTS `view_pedidos`;
/*!50001 DROP VIEW IF EXISTS `view_pedidos`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_pedidos` AS SELECT 
 1 AS `idpedido`,
 1 AS `idcliente`,
 1 AS `idtemporada`,
 1 AS `fecha_creacion`,
 1 AS `idmarca`,
 1 AS `idset_talla`,
 1 AS `idtransporte`,
 1 AS `email`,
 1 AS `monto_descuento`,
 1 AS `cliente`,
 1 AS `telefono`,
 1 AS `direccion`,
 1 AS `nit`,
 1 AS `establecimiento`,
 1 AS `dias_credito`,
 1 AS `temporada`,
 1 AS `marca`,
 1 AS `set_talla`,
 1 AS `transporte`,
 1 AS `estado`,
 1 AS `fecha_desde`,
 1 AS `fecha_hasta`,
 1 AS `observaciones_pedido`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_producto_modelo`
--

DROP TABLE IF EXISTS `view_producto_modelo`;
/*!50001 DROP VIEW IF EXISTS `view_producto_modelo`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_producto_modelo` AS SELECT 
 1 AS `idproducto`,
 1 AS `modelo`,
 1 AS `linea`,
 1 AS `idset_talla`,
 1 AS `idcolor`,
 1 AS `color`,
 1 AS `idmarca`,
 1 AS `marca`,
 1 AS `idproducto_precio`,
 1 AS `material`,
 1 AS `precio`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_set_talla_detalle`
--

DROP TABLE IF EXISTS `view_set_talla_detalle`;
/*!50001 DROP VIEW IF EXISTS `view_set_talla_detalle`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_set_talla_detalle` AS SELECT 
 1 AS `idset_talla`,
 1 AS `descripcion`,
 1 AS `idtalla`,
 1 AS `talla`,
 1 AS `orden`,
 1 AS `numero`*/;
SET character_set_client = @saved_cs_client;

--
-- Dumping routines for database 'pedidosjb_pedidos'
--
--
-- WARNING: can't read the INFORMATION_SCHEMA.libraries table. It's most probably an old server 8.4.3.
--

--
-- Final view structure for view `view_cliente_contacto`
--

/*!50001 DROP VIEW IF EXISTS `view_cliente_contacto`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_cliente_contacto` AS select `cc`.`idcliente_contacto` AS `idcliente_contacto`,`cc`.`idcliente` AS `idcliente`,`cc`.`nombre` AS `nombre_contacto`,`cc`.`telefono` AS `telefono_contacto`,`cc`.`correo` AS `correo_contacto`,`cc`.`estado` AS `estado_contacto`,`cc`.`observaciones` AS `observaciones_contacto`,`tc`.`descripcion` AS `tipo_contacto`,`tc`.`idtipo_contacto` AS `idtipo_contacto` from (`cliente_contacto` `cc` join `tipo_contacto` `tc` on((`tc`.`idtipo_contacto` = `cc`.`idtipo_contacto`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_marca_set_talla`
--

/*!50001 DROP VIEW IF EXISTS `view_marca_set_talla`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_marca_set_talla` AS select `m`.`idmarca` AS `idmarca`,`m`.`nombre` AS `nombre`,`m`.`estado` AS `estado`,`m`.`idset_talla_preferido` AS `idset_talla_preferido`,`st`.`idset_talla` AS `idset_talla`,`st`.`grupo` AS `grupo` from (`marca` `m` join `set_talla` `st` on((`m`.`idset_talla_preferido` = `st`.`idset_talla`))) order by `m`.`idmarca` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_pedido_detalle`
--

/*!50001 DROP VIEW IF EXISTS `view_pedido_detalle`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_pedido_detalle` AS select `pd`.`idpedido_detalle` AS `idpedido_detalle`,`pd`.`idpedido` AS `idpedido`,`pd`.`imagen` AS `imagen`,`pd`.`idproducto` AS `idproducto`,`pd`.`idproducto_precio` AS `idproducto_precio`,`p`.`modelo` AS `codigo`,`p`.`linea` AS `descripcion`,`c`.`idcolor` AS `idcolor`,`c`.`nombre` AS `color`,`m`.`nombre` AS `marca`,`pp`.`material` AS `material`,`pd`.`precio_venta` AS `precio_venta`,`pd`.`cantidad` AS `cantidad`,`pd`.`subtotal` AS `subtotal`,`t`.`idtalla` AS `idtalla`,`t`.`numero` AS `talla` from (((((`pedido_detalle` `pd` join `producto` `p` on((`pd`.`idproducto` = `p`.`idproducto`))) join `color` `c` on((`p`.`idcolor` = `c`.`idcolor`))) join `producto_precio` `pp` on((`pp`.`idproducto_precio` = `pd`.`idproducto_precio`))) join `talla` `t` on((`pd`.`idtalla` = `t`.`idtalla`))) join `marca` `m` on((`p`.`idmarca` = `m`.`idmarca`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_pedidos`
--

/*!50001 DROP VIEW IF EXISTS `view_pedidos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_pedidos` AS select `p`.`idpedido` AS `idpedido`,`p`.`idcliente` AS `idcliente`,`p`.`idtemporada` AS `idtemporada`,`p`.`fecha_creacion` AS `fecha_creacion`,`p`.`idmarca` AS `idmarca`,`p`.`idset_talla` AS `idset_talla`,`p`.`idtransporte` AS `idtransporte`,`p`.`email` AS `email`,`p`.`monto_descuento` AS `monto_descuento`,`c`.`nombre` AS `cliente`,`c`.`telefono` AS `telefono`,`c`.`direccion` AS `direccion`,`c`.`nit` AS `nit`,`c`.`establecimiento` AS `establecimiento`,`c`.`dias_credito` AS `dias_credito`,`t`.`nombre` AS `temporada`,`m`.`nombre` AS `marca`,`st`.`descripcion` AS `set_talla`,`tr`.`nombre` AS `transporte`,`p`.`estado` AS `estado`,`p`.`fecha_desde` AS `fecha_desde`,`p`.`fecha_hasta` AS `fecha_hasta`,`p`.`observaciones_pedido` AS `observaciones_pedido` from (((((`pedido` `p` join `cliente` `c` on((`p`.`idcliente` = `c`.`idcliente`))) join `temporada` `t` on((`p`.`idtemporada` = `t`.`idtemporada`))) join `marca` `m` on((`p`.`idmarca` = `m`.`idmarca`))) join `set_talla` `st` on((`p`.`idset_talla` = `st`.`idset_talla`))) left join `transporte` `tr` on((`p`.`idtransporte` = `tr`.`idtransporte`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_producto_modelo`
--

/*!50001 DROP VIEW IF EXISTS `view_producto_modelo`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_producto_modelo` AS select `p`.`idproducto` AS `idproducto`,`p`.`modelo` AS `modelo`,`p`.`linea` AS `linea`,`p`.`idset_talla` AS `idset_talla`,`c`.`idcolor` AS `idcolor`,`c`.`nombre` AS `color`,`p`.`idmarca` AS `idmarca`,`m`.`nombre` AS `marca`,`pp`.`idproducto_precio` AS `idproducto_precio`,`pp`.`material` AS `material`,`pp`.`precio` AS `precio` from (((`producto` `p` join `marca` `m` on((`m`.`idmarca` = `p`.`idmarca`))) join `color` `c` on((`p`.`idcolor` = `c`.`idcolor`))) left join `producto_precio` `pp` on(((`pp`.`idproducto` = `p`.`idproducto`) and (`pp`.`estado` = 'ACTIVO')))) where (`p`.`estado` = 'ACTIVO') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_set_talla_detalle`
--

/*!50001 DROP VIEW IF EXISTS `view_set_talla_detalle`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_set_talla_detalle` AS select `st`.`idset_talla` AS `idset_talla`,`st`.`descripcion` AS `descripcion`,`t`.`idtalla` AS `idtalla`,`t`.`numero` AS `talla`,`std`.`orden` AS `orden`,`t`.`numero` AS `numero` from ((`set_talla` `st` join `set_talla_detalle` `std` on((`std`.`idset_talla` = `st`.`idset_talla`))) join `talla` `t` on((`t`.`idtalla` = `std`.`idtalla`))) where ((`st`.`estado` = 'ACTIVO') and (`std`.`estado` = 'ACTIVO')) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-25 12:42:55
