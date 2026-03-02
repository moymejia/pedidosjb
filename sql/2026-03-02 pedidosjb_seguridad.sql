CREATE DATABASE  IF NOT EXISTS `pedidosjb_seguridad` /*!40100 DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `pedidosjb_seguridad`;
-- MySQL dump 10.13  Distrib 8.0.40, for macos14 (arm64)
--
-- Host: localhost    Database: pedidosjb_seguridad
-- ------------------------------------------------------
-- Server version	8.4.3

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
-- Table structure for table `accion`
--

DROP TABLE IF EXISTS `accion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accion` (
  `idaccion` int NOT NULL AUTO_INCREMENT,
  `idopcion` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `indOpcion` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `referencia1` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referencia2` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referencia3` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idaccion`),
  KEY `opcion_accion_idx` (`idopcion`),
  CONSTRAINT `opcion_accion` FOREIGN KEY (`idopcion`) REFERENCES `opcion` (`idopcion`)
) ENGINE=InnoDB AUTO_INCREMENT=190 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accion`
--

LOCK TABLES `accion` WRITE;
/*!40000 ALTER TABLE `accion` DISABLE KEYS */;
INSERT INTO `accion` VALUES 
(1,1,'opcion usuarios','SI',NULL,NULL,NULL,'ACTIVO'),
(2,1,'Crear usuario','NO','usuario',NULL,NULL,'ACTIVO'),
(3,1,'Modificar usuario','NO','usuario',NULL,NULL,'ACTIVO'),
(4,1,'Cambiar clave','NO','usuario',NULL,NULL,'ACTIVO'),
(5,2,'opcion roles de usuario','SI',NULL,NULL,NULL,'ACTIVO'),
(6,2,'Agregar permiso','NO','rol',NULL,NULL,'ACTIVO'),
(7,2,'Modificar rol','NO','rol',NULL,NULL,'ACTIVO'),
(8,3,'opcion permisos por rol','SI',NULL,NULL,NULL,'ACTIVO'),
(9,3,'Cargar permisos por rol','NO','rol',NULL,NULL,'ACTIVO'),
(10,3,'Agregar permiso a rol','NO','rol','accion',NULL,'ACTIVO'),
(11,3,'Retirar permiso a rol','NO','rol','accion',NULL,'ACTIVO'),
(12,1,'Cambiar estado usuario','NO','usuario','estado nuevo',NULL,'ACTIVO'),
(13,2,'Cambiar estado rol','NO','rol',NULL,NULL,'ACTIVO'),
(14,3,'Agregar opcion a rol','NO','rol','accion',NULL,'ACTIVO'),
(15,3,'Retirar opcion a rol','NO','rol','accion',NULL,'ACTIVO'),
(16,4,'opcion Bitacora','SI',NULL,NULL,NULL,'ACTIVO'),
(17,4,'consultar Bitacora','NO','usuario','desde','hasta','ACTIVO'),
(18,5,'opcion configuracion','SI',NULL,NULL,NULL,'ACTIVO'),
(19,5,'Modificar configuracion','NO','clave',NULL,NULL,'ACTIVO'),
(20,6,'Opcion orden de menus','SI',NULL,NULL,NULL,'ACTIVO'),
(21,6,'Modificar Orden de menus','NO','menu',NULL,NULL,'ACTIVO');
/*!40000 ALTER TABLE `accion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `actividad_rol`
--

DROP TABLE IF EXISTS `actividad_rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `actividad_rol` (
  `idactividad` int NOT NULL,
  `idrol` int NOT NULL,
  PRIMARY KEY (`idactividad`,`idrol`) USING BTREE,
  KEY `idrol` (`idrol`) USING BTREE,
  CONSTRAINT `actividad_rol_ibfk_1` FOREIGN KEY (`idactividad`) REFERENCES `legans_ventas`.`actividad` (`idactividad`),
  CONSTRAINT `actividad_rol_ibfk_2` FOREIGN KEY (`idrol`) REFERENCES `rol` (`idrol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actividad_rol`
--

LOCK TABLES `actividad_rol` WRITE;
/*!40000 ALTER TABLE `actividad_rol` DISABLE KEYS */;
/*!40000 ALTER TABLE `actividad_rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth`
--

DROP TABLE IF EXISTS `auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth` (
  `idauth` int NOT NULL AUTO_INCREMENT,
  `idubicacion` int NOT NULL,
  `token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idauth`),
  KEY `fk_idubicacion` (`idubicacion`),
  CONSTRAINT `fk_idubicacion` FOREIGN KEY (`idubicacion`) REFERENCES `legans_inventario`.`ubicacion` (`idubicacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth`
--

LOCK TABLES `auth` WRITE;
/*!40000 ALTER TABLE `auth` DISABLE KEYS */;
/*!40000 ALTER TABLE `auth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bitacora`
--

DROP TABLE IF EXISTS `bitacora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bitacora` (
  `idbitacora` int NOT NULL AUTO_INCREMENT,
  `idaccion` int NOT NULL,
  `usuario` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fechahora` datetime NOT NULL,
  `referencia1` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referencia2` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referencia3` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idbitacora`),
  KEY `usuario_bitacora_idx` (`usuario`),
  KEY `accion_bitacora_idx` (`idaccion`),
  CONSTRAINT `accion_bitacora` FOREIGN KEY (`idaccion`) REFERENCES `accion` (`idaccion`),
  CONSTRAINT `usuario_bitacora` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`usuario`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--
--
-- Table structure for table `bitacora_usuario`
--

DROP TABLE IF EXISTS `bitacora_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bitacora_usuario` (
  `idbitacora_usuario` int NOT NULL AUTO_INCREMENT,
  `idtipo_bitacora` int NOT NULL,
  `usuario` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idbitacora_usuario`),
  KEY `bitacora_usuario` (`usuario`),
  KEY `tipobitacora_usuario` (`idtipo_bitacora`),
  CONSTRAINT `bitacora_usuario` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `tipobitacora_usuario` FOREIGN KEY (`idtipo_bitacora`) REFERENCES `tipo_bitacora` (`idtipo_bitacora`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora_usuario`
--

LOCK TABLES `bitacora_usuario` WRITE;
/*!40000 ALTER TABLE `bitacora_usuario` DISABLE KEYS */;
/*!40000 ALTER TABLE `bitacora_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracion`
--

DROP TABLE IF EXISTS `configuracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracion` (
  `clave` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `comentario` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion`
--

LOCK TABLES `configuracion` WRITE;
/*!40000 ALTER TABLE `configuracion` DISABLE KEYS */;
INSERT INTO `configuracion` VALUES ('ASDASD','Valor de prueba s','asdasd');
/*!40000 ALTER TABLE `configuracion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `error`
--

DROP TABLE IF EXISTS `error`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `error` (
  `iderror` int NOT NULL AUTO_INCREMENT,
  `idtipo_error` int NOT NULL,
  `usuario` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mensaje` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `datos` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `origen` varchar(5000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fechahora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`iderror`),
  KEY `usuario_error_idx` (`usuario`),
  KEY `tipo_error_idx` (`idtipo_error`),
  CONSTRAINT `tipo_error` FOREIGN KEY (`idtipo_error`) REFERENCES `tipo_error` (`idtipo_error`),
  CONSTRAINT `usuario_error` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=544 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `error`
--
--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu` (
  `idmenu` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icono` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orden` int DEFAULT NULL,
  `estado` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idmenu`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu`
--

LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` VALUES 
(1,'Seguridad','mdi mdi-key',2,'ACTIVO');
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opcion`
--

DROP TABLE IF EXISTS `opcion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opcion` (
  `idopcion` int NOT NULL AUTO_INCREMENT,
  `idmenu` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `funcion` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` int NOT NULL,
  `estado` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idopcion`),
  KEY `menu_opcion_idx` (`idmenu`),
  CONSTRAINT `menu_opcion` FOREIGN KEY (`idmenu`) REFERENCES `menu` (`idmenu`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opcion`
--

LOCK TABLES `opcion` WRITE;
/*!40000 ALTER TABLE `opcion` DISABLE KEYS */;
INSERT INTO `opcion` VALUES 
(1,1,'Usuarios','usuario','cargar_opcion',1,'ACTIVO'),
(2,1,'Roles de usuario','rol','cargar_opcion',2,'ACTIVO'),
(3,1,'Permisos por rol','rol_accion','cargar_opcion',3,'ACTIVO'),
(4,1,'Bitacora','bitacora','cargar_opcion',4,'ACTIVO'),
(5,1,'configuracion','configuracion','cargar_opcion',5,'ACTIVO'),
(6,1,'Menu','menu','cargar_opcion',6,'INACTIVO');
/*!40000 ALTER TABLE `opcion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plantilla_whatsapp`
--

DROP TABLE IF EXISTS `plantilla_whatsapp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plantilla_whatsapp` (
  `idplantilla` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `idioma` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `estado` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `idtelefono` bigint NOT NULL,
  `idaccion` int NOT NULL,
  PRIMARY KEY (`idplantilla`),
  KEY `idaccion_plantilla_fk` (`idaccion`),
  CONSTRAINT `idaccion_plantilla_fk` FOREIGN KEY (`idaccion`) REFERENCES `accion` (`idaccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plantilla_whatsapp`
--

LOCK TABLES `plantilla_whatsapp` WRITE;
/*!40000 ALTER TABLE `plantilla_whatsapp` DISABLE KEYS */;
/*!40000 ALTER TABLE `plantilla_whatsapp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plantilla_whatsapp_campo`
--

DROP TABLE IF EXISTS `plantilla_whatsapp_campo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plantilla_whatsapp_campo` (
  `idcampo` int NOT NULL AUTO_INCREMENT,
  `idplantilla` int NOT NULL,
  `nombre_campo` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci NOT NULL,
  `orden` int NOT NULL,
  PRIMARY KEY (`idcampo`),
  KEY `idplantilla_fk1` (`idplantilla`),
  CONSTRAINT `idplantilla_fk1` FOREIGN KEY (`idplantilla`) REFERENCES `plantilla_whatsapp` (`idplantilla`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plantilla_whatsapp_campo`
--

LOCK TABLES `plantilla_whatsapp_campo` WRITE;
/*!40000 ALTER TABLE `plantilla_whatsapp_campo` DISABLE KEYS */;
/*!40000 ALTER TABLE `plantilla_whatsapp_campo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol`
--

DROP TABLE IF EXISTS `rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol` (
  `idrol` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idrol`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol`
--

LOCK TABLES `rol` WRITE;
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
INSERT INTO `rol` VALUES (1,'Administrador','ACTIVO');
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol_accion`
--

DROP TABLE IF EXISTS `rol_accion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol_accion` (
  `idrol` int NOT NULL,
  `idaccion` int NOT NULL,
  `indFavorito` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idrol`,`idaccion`),
  KEY `rol_accion_idx` (`idaccion`),
  CONSTRAINT `accion_rol` FOREIGN KEY (`idrol`) REFERENCES `rol` (`idrol`),
  CONSTRAINT `rol_accion` FOREIGN KEY (`idaccion`) REFERENCES `accion` (`idaccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol_accion`
--

LOCK TABLES `rol_accion` WRITE;
/*!40000 ALTER TABLE `rol_accion` DISABLE KEYS */;
INSERT INTO `rol_accion` VALUES 
(1,1,'NO'),
(1,2,'NO'),
(1,3,'NO'),
(1,4,'NO'),
(1,5,'NO'),
(1,6,'NO'),
(1,7,'NO'),
(1,8,'NO'),
(1,9,'NO'),
(1,10,'NO'),
(1,11,'NO'),
(1,12,'NO'),
(1,13,'NO'),
(1,14,'NO'),
(1,15,'NO'),
(1,16,'NO'),
(1,17,'NO'),
(1,18,'NO'),
(1,19,'NO'),
(1,20,'NO'),
(1,21,'NO');
/*!40000 ALTER TABLE `rol_accion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_bitacora`
--

DROP TABLE IF EXISTS `tipo_bitacora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_bitacora` 
(
  `idtipo_bitacora` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar (100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idtipo_bitacora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_bitacora`
--

LOCK TABLES `tipo_bitacora` WRITE;
/*!40000 ALTER TABLE `tipo_bitacora` DISABLE KEYS */;
/*!40000 ALTER TABLE `tipo_bitacora` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_error`
--

DROP TABLE IF EXISTS `tipo_error`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_error` (
  `idtipo_error` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalles` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`idtipo_error`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_error`
--

LOCK TABLES `tipo_error` WRITE;
/*!40000 ALTER TABLE `tipo_error` DISABLE KEYS */;
INSERT INTO `tipo_error` VALUES (1,'login','sesion sin usuario registrado'),(2,'auth','usuario sin permiso para la accion soliciatada'),(3,'bd','error al ejecutar instruccion en la base de datos'),(4,'validation','validacion de proceso no superada');
/*!40000 ALTER TABLE `tipo_error` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `usuario` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `clave` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `idrol` int DEFAULT NULL,
  `color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pin` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`usuario`),
  KEY `usuario_rol_idx` (`idrol`),
  CONSTRAINT `usuario_rol` FOREIGN KEY (`idrol`) REFERENCES `rol` (`idrol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES ('admin','8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918','Administrador','admin@hotmail.com','ACTIVO',1,NULL,'0101'),('admin2','1c142b2d01aa34e9a36bde480645a57fd69e14155dacfab5a3f9257b77fdc8d8','Administrador 2','prueba@mailme.com','INACTIVO',1,'#000000','1234');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `view_bitacora`
--

DROP TABLE IF EXISTS `view_bitacora`;
/*!50001 DROP VIEW IF EXISTS `view_bitacora`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_bitacora` AS SELECT 
 1 AS `idbitacora`,
 1 AS `usuario`,
 1 AS `nombre_usuario`,
 1 AS `idopcion`,
 1 AS `opcion`,
 1 AS `idaccion`,
 1 AS `accion`,
 1 AS `fecha`,
 1 AS `hora`,
 1 AS `referencia_1`,
 1 AS `referencia_2`,
 1 AS `referencia_3`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_permisos`
--

DROP TABLE IF EXISTS `view_permisos`;
/*!50001 DROP VIEW IF EXISTS `view_permisos`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_permisos` AS SELECT 
 1 AS `idrol`,
 1 AS `rol`,
 1 AS `idmenu`,
 1 AS `menu`,
 1 AS `orden_menu`,
 1 AS `icono`,
 1 AS `idopcion`,
 1 AS `opcion`,
 1 AS `orden_opcion`,
 1 AS `idaccion`,
 1 AS `indOpcion`,
 1 AS `accion`*/;
SET character_set_client = @saved_cs_client;

--
-- Dumping events for database 'pedidosjb_seguridad'
--

--
-- Dumping routines for database 'pedidosjb_seguridad'
--

--
-- Final view structure for view `view_bitacora`
--

/*!50001 DROP VIEW IF EXISTS `view_bitacora`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_bitacora` AS select `b`.`idbitacora` AS `idbitacora`,`b`.`usuario` AS `usuario`,`u`.`nombre` AS `nombre_usuario`,`o`.`idopcion` AS `idopcion`,`o`.`nombre` AS `opcion`,`a`.`idaccion` AS `idaccion`,`a`.`nombre` AS `accion`,cast(`b`.`fechahora` as date) AS `fecha`,cast(`b`.`fechahora` as time) AS `hora`,if((`a`.`referencia1` is not null),concat(`a`.`referencia1`,': ',`b`.`referencia1`),'') AS `referencia_1`,if((`a`.`referencia2` is not null),concat(`a`.`referencia2`,': ',`b`.`referencia2`),'') AS `referencia_2`,if((`a`.`referencia3` is not null),concat(`a`.`referencia3`,': ',`b`.`referencia3`),'') AS `referencia_3` from (((`bitacora` `b` join `usuario` `u` on((`b`.`usuario` = `u`.`usuario`))) join `accion` `a` on((`b`.`idaccion` = `a`.`idaccion`))) join `opcion` `o` on((`a`.`idopcion` = `o`.`idopcion`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_permisos`
--

/*!50001 DROP VIEW IF EXISTS `view_permisos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_permisos` AS select `r`.`idrol` AS `idrol`,`r`.`nombre` AS `rol`,`o`.`idmenu` AS `idmenu`,`m`.`nombre` AS `menu`,`m`.`orden` AS `orden_menu`,`m`.`icono` AS `icono`,`a`.`idopcion` AS `idopcion`,`o`.`nombre` AS `opcion`,`o`.`orden` AS `orden_opcion`,`ra`.`idaccion` AS `idaccion`,`a`.`indOpcion` AS `indOpcion`,`a`.`nombre` AS `accion` from ((((`rol` `r` join `rol_accion` `ra` on((`r`.`idrol` = `ra`.`idrol`))) join `accion` `a` on((`ra`.`idaccion` = `a`.`idaccion`))) join `opcion` `o` on((`a`.`idopcion` = `o`.`idopcion`))) join `menu` `m` on((`o`.`idmenu` = `m`.`idmenu`))) where (`o`.`estado` = 'ACTIVO') */;
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

-- Dump completed on 2026-03-02 16:02:53
