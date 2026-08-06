USE `pedidosjb_seguridad`;

CREATE TABLE IF NOT EXISTS `datatable_configuracion` (
  `idtabla` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `creado_por` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `actualizado_por` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idtabla`),
  KEY `idx_datatable_configuracion_creado_por` (`creado_por`),
  KEY `idx_datatable_configuracion_actualizado_por` (`actualizado_por`),
  CONSTRAINT `fk_datatable_configuracion_creado_por` FOREIGN KEY (`creado_por`) REFERENCES `usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_datatable_configuracion_actualizado_por` FOREIGN KEY (`actualizado_por`) REFERENCES `usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `datatable_configuracion_detalle` (
  `idtabla_detalle` int NOT NULL AUTO_INCREMENT,
  `idtabla` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parametro` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `creado_por` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `actualizado_por` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idtabla_detalle`),
  UNIQUE KEY `uk_datatable_configuracion_detalle_parametro` (`idtabla`, `parametro`),
  KEY `idx_datatable_configuracion_detalle_creado_por` (`creado_por`),
  KEY `idx_datatable_configuracion_detalle_actualizado_por` (`actualizado_por`),
  CONSTRAINT `fk_datatable_configuracion_detalle_encabezado` FOREIGN KEY (`idtabla`) REFERENCES `datatable_configuracion` (`idtabla`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_datatable_configuracion_detalle_creado_por` FOREIGN KEY (`creado_por`) REFERENCES `usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_datatable_configuracion_detalle_actualizado_por` FOREIGN KEY (`actualizado_por`) REFERENCES `usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `datatable_configuracion_detalle_valor` (
  `idtabla_detalle_valor` int NOT NULL AUTO_INCREMENT,
  `idtabla_detalle` int NOT NULL,
  `clave` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` int NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `creado_por` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `actualizado_en` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `actualizado_por` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idtabla_detalle_valor`),
  UNIQUE KEY `uk_datatable_configuracion_detalle_valor_orden` (`idtabla_detalle`, `orden`),
  KEY `idx_datatable_configuracion_detalle_valor_creado_por` (`creado_por`),
  KEY `idx_datatable_configuracion_detalle_valor_actualizado_por` (`actualizado_por`),
  CONSTRAINT `fk_datatable_configuracion_detalle_valor_detalle` FOREIGN KEY (`idtabla_detalle`) REFERENCES `datatable_configuracion_detalle` (`idtabla_detalle`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_datatable_configuracion_detalle_valor_creado_por` FOREIGN KEY (`creado_por`) REFERENCES `usuario` (`usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_datatable_configuracion_detalle_valor_actualizado_por` FOREIGN KEY (`actualizado_por`) REFERENCES `usuario` (`usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE OR REPLACE VIEW `view_datatable_configuracion` AS
SELECT
  `c`.`idtabla`,
  `d`.`idtabla_detalle`,
  `d`.`parametro`,
  `v`.`clave`,
  CASE
    WHEN `v`.`idtabla_detalle_valor` IS NULL THEN `d`.`valor`
    ELSE `v`.`valor`
  END AS `valor`,
  `v`.`orden`
FROM `datatable_configuracion` `c`
LEFT JOIN `datatable_configuracion_detalle` `d` ON `d`.`idtabla` = `c`.`idtabla`
LEFT JOIN `datatable_configuracion_detalle_valor` `v` ON `v`.`idtabla_detalle` = `d`.`idtabla_detalle`;

SET @idmenu_configuracion_datatables = (
    SELECT idmenu FROM menu WHERE nombre = 'Seguridad' LIMIT 1
);

INSERT INTO opcion (idmenu, nombre, entity, funcion, orden, estado)
SELECT @idmenu_configuracion_datatables,
       'Configuración DataTables',
       'datatable_configuracion',
       'cargar_opcion',
       COALESCE((SELECT MAX(o.orden) + 1 FROM opcion o WHERE o.idmenu = @idmenu_configuracion_datatables), 1),
       'ACTIVO'
FROM dual
WHERE @idmenu_configuracion_datatables IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM opcion WHERE entity = 'datatable_configuracion');

SET @idopcion_configuracion_datatables = (
    SELECT idopcion FROM opcion WHERE entity = 'datatable_configuracion' LIMIT 1
);

INSERT INTO accion (idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
SELECT @idopcion_configuracion_datatables,
       'opcion_configuracion_datatables',
       'SI',
       'idtabla',
       NULL,
       NULL,
       'ACTIVO'
FROM dual
WHERE @idopcion_configuracion_datatables IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM accion WHERE nombre = 'opcion_configuracion_datatables');

INSERT INTO accion (idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
SELECT @idopcion_configuracion_datatables,
       'modificar_configuracion_datatables',
       'NO',
       'idtabla',
       'parametro',
       'cambio',
       'ACTIVO'
FROM dual
WHERE @idopcion_configuracion_datatables IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM accion WHERE nombre = 'modificar_configuracion_datatables');

INSERT INTO accion (idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
SELECT @idopcion_configuracion_datatables,
       'actualizar_vista',
       'NO',
       'idtabla',
       NULL,
       NULL,
       'ACTIVO'
FROM dual
WHERE @idopcion_configuracion_datatables IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM accion WHERE nombre = 'actualizar_vista');

INSERT INTO accion (idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
SELECT @idopcion_configuracion_datatables,
       'compartir_vista',
       'NO',
       'idtabla',
       'nombre_estado',
       NULL,
       'ACTIVO'
FROM dual
WHERE @idopcion_configuracion_datatables IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM accion WHERE nombre = 'compartir_vista');

SET @idrol_admin_configuracion_datatables = (
    SELECT idrol FROM usuario WHERE usuario = 'admin' LIMIT 1
);

INSERT INTO rol_accion (idrol, idaccion, indFavorito)
SELECT @idrol_admin_configuracion_datatables, a.idaccion, 'NO'
FROM accion a
WHERE a.nombre IN (
    'opcion_configuracion_datatables',
    'modificar_configuracion_datatables',
    'actualizar_vista',
    'compartir_vista'
)
  AND @idrol_admin_configuracion_datatables IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM rol_accion ra
      WHERE ra.idrol = @idrol_admin_configuracion_datatables
        AND ra.idaccion = a.idaccion
  );
