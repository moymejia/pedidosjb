
CREATE TABLE pedidosjb_pedidos.`corte` (
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
    CONSTRAINT `fk_corte_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_corte_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pedidosjb_pedidos.`tipo_suela` (
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
    CONSTRAINT `fk_suela_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_suela_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE pedidosjb_pedidos.`concepto` (
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
    CONSTRAINT `fk_concepto_usuario_creacion` FOREIGN KEY (`usuario_creacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_concepto_usuario_modificacion` FOREIGN KEY (`usuario_modificacion`) REFERENCES `pedidosjb_seguridad`.`usuario` (`usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;