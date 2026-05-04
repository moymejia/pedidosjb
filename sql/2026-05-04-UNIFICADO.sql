-- Script unificado
-- Fecha: 2026-05-04
-- Objetivo: ejecutar en un solo archivo los cambios de:
--   2026-04-27 estado de cuenta.sql
--   2026-04-27 Fix view_ventas_temporada.sql
--   2026-04-29 pantalla anticipos cliente.sql
--   2026-04-29 acciones cliente_anticipo_aplicacion.sql
--   2026-04-29 registro pagos despacho.sql
--   2026-04-29 agregar soporte anticipos despacho_pago.sql
--   2026-05-04 Ajuste vista ventas_temporada fechas.sql
--   2026-05-04 imagenes documento despacho_pago.sql
--
-- NOTA:
-- - No elimina los SQL anteriores.
-- - Está preparado para correrse una sola vez sin romper por objetos ya existentes.

SET @OLD_UNIQUE_CHECKS = @@UNIQUE_CHECKS;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET @OLD_SQL_MODE = @@SQL_MODE;

SET UNIQUE_CHECKS = 0;
SET FOREIGN_KEY_CHECKS = 0;

/* =====================================================================
   PEDIDOSJB_PEDIDOS - ESTRUCTURA Y CATALOGOS
   ===================================================================== */
USE pedidosjb_pedidos;

-- tipo_pago.signo
SET @sql = (
    SELECT IF(
        EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = 'pedidosjb_pedidos'
              AND table_name = 'tipo_pago'
              AND column_name = 'signo'
        ),
        'SELECT ''tipo_pago.signo ya existe''',
        'ALTER TABLE pedidosjb_pedidos.tipo_pago ADD COLUMN signo INT NOT NULL DEFAULT 1'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- tabla tipo_documento
CREATE TABLE IF NOT EXISTS pedidosjb_pedidos.tipo_documento (
    idtipo_documento INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    correlativo VARCHAR(50) NOT NULL,
    estado VARCHAR(25) NOT NULL DEFAULT 'ACTIVO',
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25)
        CHARACTER SET utf8mb4
        COLLATE utf8mb4_unicode_ci NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25)
        CHARACTER SET utf8mb4
        COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    PRIMARY KEY (idtipo_documento),
    UNIQUE KEY uq_tipo_documento_nombre (nombre),
    KEY idx_tipo_documento_usuario_creacion (usuario_creacion),
    KEY idx_tipo_documento_usuario_modificacion (usuario_modificacion),
    CONSTRAINT fk_tipo_documento_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_tipo_documento_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- despacho_pago.idtipo_documento
SET @sql = (
    SELECT IF(
        EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = 'pedidosjb_pedidos'
              AND table_name = 'despacho_pago'
              AND column_name = 'idtipo_documento'
        ),
        'SELECT ''despacho_pago.idtipo_documento ya existe''',
        'ALTER TABLE pedidosjb_pedidos.despacho_pago ADD COLUMN idtipo_documento INT NULL AFTER idtipo_pago'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- despacho_pago.correlativo_documento
SET @sql = (
    SELECT IF(
        EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = 'pedidosjb_pedidos'
              AND table_name = 'despacho_pago'
              AND column_name = 'correlativo_documento'
        ),
        'SELECT ''despacho_pago.correlativo_documento ya existe''',
        'ALTER TABLE pedidosjb_pedidos.despacho_pago ADD COLUMN correlativo_documento VARCHAR(50) NULL AFTER monto'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- despacho_pago.banco
SET @sql = (
    SELECT IF(
        EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = 'pedidosjb_pedidos'
              AND table_name = 'despacho_pago'
              AND column_name = 'banco'
        ),
        'SELECT ''despacho_pago.banco ya existe''',
        'ALTER TABLE pedidosjb_pedidos.despacho_pago ADD COLUMN banco VARCHAR(100) NULL AFTER correlativo_documento'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- despacho_pago.idcliente_anticipo
SET @sql = (
    SELECT IF(
        EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = 'pedidosjb_pedidos'
              AND table_name = 'despacho_pago'
              AND column_name = 'idcliente_anticipo'
        ),
        'SELECT ''despacho_pago.idcliente_anticipo ya existe''',
        'ALTER TABLE pedidosjb_pedidos.despacho_pago ADD COLUMN idcliente_anticipo INT NULL AFTER idtipo_pago'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- despacho_pago.imagen
SET @sql = (
    SELECT IF(
        EXISTS (
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = 'pedidosjb_pedidos'
              AND table_name = 'despacho_pago'
              AND column_name = 'imagen'
        ),
        'SELECT ''despacho_pago.imagen ya existe''',
        'ALTER TABLE pedidosjb_pedidos.despacho_pago ADD COLUMN imagen VARCHAR(255) NULL AFTER observaciones'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- indice despacho_pago.idcliente_anticipo
SET @sql = (
    SELECT IF(
        EXISTS (
            SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = 'pedidosjb_pedidos'
              AND table_name = 'despacho_pago'
              AND index_name = 'idx_despacho_pago_idcliente_anticipo'
        ),
        'SELECT ''indice idx_despacho_pago_idcliente_anticipo ya existe''',
        'ALTER TABLE pedidosjb_pedidos.despacho_pago ADD KEY idx_despacho_pago_idcliente_anticipo (idcliente_anticipo)'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- FK despacho_pago -> cliente_anticipo
SET @sql = (
    SELECT IF(
        EXISTS (
            SELECT 1
            FROM information_schema.table_constraints
            WHERE constraint_schema = 'pedidosjb_pedidos'
              AND table_name = 'despacho_pago'
              AND constraint_name = 'fk_despacho_pago_cliente_anticipo'
              AND constraint_type = 'FOREIGN KEY'
        ),
        'SELECT ''fk_despacho_pago_cliente_anticipo ya existe''',
        'ALTER TABLE pedidosjb_pedidos.despacho_pago ADD CONSTRAINT fk_despacho_pago_cliente_anticipo FOREIGN KEY (idcliente_anticipo) REFERENCES pedidosjb_pedidos.cliente_anticipo (idcliente_anticipo) ON UPDATE CASCADE ON DELETE RESTRICT'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Tipo pago ANTICIPO
INSERT INTO pedidosjb_pedidos.tipo_pago
(idtipo_pago, descripcion, estado, fecha_creacion, usuario_creacion, fecha_modificacion, usuario_modificacion, signo)
VALUES (10, 'ANTICIPO', 'PROTEGIDO', '2026-04-29 14:20:29', 'admin', NULL, NULL, 1)
ON DUPLICATE KEY UPDATE
    descripcion = VALUES(descripcion),
    estado = VALUES(estado),
    signo = VALUES(signo),
    usuario_modificacion = 'admin',
    fecha_modificacion = NOW();

-- Asegurar tipo de estado
ALTER TABLE pedidosjb_pedidos.despacho_pago
    MODIFY COLUMN estado VARCHAR(25) NOT NULL;

-- Hacer NOT NULL solo cuando no hay nulos (evita fallo al cargar)
SET @nulls_doc = (
    SELECT COUNT(1)
    FROM pedidosjb_pedidos.despacho_pago
    WHERE idtipo_documento IS NULL
);
SET @sql = IF(
    @nulls_doc = 0,
    'ALTER TABLE pedidosjb_pedidos.despacho_pago MODIFY COLUMN idtipo_documento INT NOT NULL',
    'SELECT ''Se omite NOT NULL en idtipo_documento: existen registros nulos'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @nulls_corr = (
    SELECT COUNT(1)
    FROM pedidosjb_pedidos.despacho_pago
    WHERE correlativo_documento IS NULL
);
SET @sql = IF(
    @nulls_corr = 0,
    'ALTER TABLE pedidosjb_pedidos.despacho_pago MODIFY COLUMN correlativo_documento VARCHAR(50) NOT NULL',
    'SELECT ''Se omite NOT NULL en correlativo_documento: existen registros nulos'''
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

/* =====================================================================
   PEDIDOSJB_PEDIDOS - VISTAS
   ===================================================================== */

-- Vista cliente anticipo
CREATE OR REPLACE VIEW pedidosjb_pedidos.view_cliente_anticipo AS
SELECT
    ca.idcliente_anticipo,
    ca.idcliente,
    ca.fecha,
    ca.idtipo_pago,
    ca.monto,
    ca.saldo_disponible,
    ca.referencia_pago,
    ca.observaciones,
    ca.estado,
    ca.fecha_creacion,
    ca.usuario_creacion,
    ca.fecha_modificacion,
    ca.usuario_modificacion,
    CONCAT(c.codigo, ' - ', c.nombre) AS cliente,
    tp.descripcion AS tipo_pago
FROM pedidosjb_pedidos.cliente_anticipo ca
LEFT JOIN pedidosjb_pedidos.cliente c ON c.idcliente = ca.idcliente
LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = ca.idtipo_pago;

-- Vista ventas por temporada (version final con fechas)
CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW pedidosjb_pedidos.view_ventas_temporada AS
SELECT
    p.nopedido AS nopedido,
    p.idpedido AS idpedido,
    p.idcliente AS idcliente,
    COALESCE(SUM(pd.subtotal), 0) AS monto_total,
    CONCAT(c.codigo, ' - ', c.nombre) AS nombre_cliente,
    p.idmarca AS idmarca,
    m.nombre AS nombre_marca,
    p.idtemporada AS idtemporada,
    p.fecha_creacion AS fecha_creacion,
    p.fecha_desde AS fecha_desde,
    p.fecha_hasta AS fecha_hasta,
    COALESCE(SUM(pd.cantidad), 0) AS cantidad_pares,
    COUNT(DISTINCT pd.idproducto) AS cantidad_modelos,
    p.estado AS estado
FROM pedidosjb_pedidos.pedido p
JOIN pedidosjb_pedidos.marca m ON m.idmarca = p.idmarca
LEFT JOIN pedidosjb_pedidos.cliente c ON c.idcliente = p.idcliente
LEFT JOIN pedidosjb_pedidos.pedido_detalle pd ON pd.idpedido = p.idpedido
GROUP BY
    p.nopedido,
    p.idpedido,
    p.idcliente,
    c.codigo,
    c.nombre,
    p.idmarca,
    m.nombre,
    p.idtemporada,
    p.fecha_creacion,
    p.fecha_desde,
    p.fecha_hasta,
    p.estado;

-- Vista estado de cuenta resumen
CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW pedidosjb_pedidos.view_estado_cuenta_despacho AS
SELECT
    d.iddespacho AS iddespacho,
    p.idcliente AS idcliente,
    CONCAT(c.codigo, ' - ', c.nombre) AS nombre_cliente,
    p.idtemporada AS idtemporada,
    d.fecha_factura AS fecha_factura,
    d.monto_total AS monto_total,
    IFNULL(SUM((CASE WHEN (dp.estado = 'EJECUTADO') THEN dp.monto ELSE 0 END)), 0) AS monto_total_pagado,
    IFNULL(SUM((CASE WHEN (dp.estado = 'PROGRAMADO') THEN dp.monto ELSE 0 END)), 0) AS monto_programado,
    (d.monto_total - IFNULL(SUM((CASE WHEN (dp.estado = 'EJECUTADO') THEN dp.monto ELSE 0 END)), 0)) AS saldo_pendiente,
    (d.fecha_factura + INTERVAL p.dias_credito DAY) AS fecha_vencimiento,
    (TO_DAYS((d.fecha_factura + INTERVAL p.dias_credito DAY)) - TO_DAYS(CURDATE())) AS proximidad,
    (CASE
        WHEN ((TO_DAYS((d.fecha_factura + INTERVAL p.dias_credito DAY)) - TO_DAYS(CURDATE())) <= 0) THEN 'Vencido'
        WHEN ((TO_DAYS((d.fecha_factura + INTERVAL p.dias_credito DAY)) - TO_DAYS(CURDATE())) BETWEEN 1 AND 30) THEN 'A 30'
        WHEN ((TO_DAYS((d.fecha_factura + INTERVAL p.dias_credito DAY)) - TO_DAYS(CURDATE())) BETWEEN 31 AND 60) THEN 'A 60'
        WHEN ((TO_DAYS((d.fecha_factura + INTERVAL p.dias_credito DAY)) - TO_DAYS(CURDATE())) BETWEEN 61 AND 90) THEN 'A 90'
        ELSE '90 +'
    END) AS estado
FROM pedidosjb_pedidos.despacho d
JOIN pedidosjb_pedidos.pedido p ON d.idpedido = p.idpedido
JOIN pedidosjb_pedidos.cliente c ON p.idcliente = c.idcliente
LEFT JOIN pedidosjb_pedidos.despacho_pago dp ON d.iddespacho = dp.iddespacho
WHERE d.fecha_factura IS NOT NULL
GROUP BY
    d.iddespacho,
    p.idcliente,
    c.nombre,
    p.idtemporada,
    d.fecha_factura,
    d.monto_total,
    p.dias_credito;

-- Vista estado de cuenta detallado
CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW pedidosjb_pedidos.view_estado_cuenta_despacho_detallado AS
SELECT
    d.iddespacho AS iddespacho,
    p.idcliente AS idcliente,
    CONCAT(c.codigo, ' - ', c.nombre) AS nombre_cliente,
    p.idtemporada AS idtemporada,
    d.numero_factura AS numero_factura,
    d.fecha_factura AS fecha_factura,
    d.monto_total AS monto_total,
    dp.monto AS monto_pago,
    (
        d.monto_total - IFNULL((
            SELECT SUM(dp1.monto)
            FROM pedidosjb_pedidos.despacho_pago dp1
            WHERE dp1.iddespacho = d.iddespacho
              AND dp1.estado = 'EJECUTADO'
        ), 0)
    ) AS saldo_pendiente,
    (d.fecha_factura + INTERVAL p.dias_credito DAY) AS fecha_vencimiento,
    (TO_DAYS((d.fecha_factura + INTERVAL p.dias_credito DAY)) - TO_DAYS(CURDATE())) AS proximidad,
    (CASE
        WHEN ((TO_DAYS((d.fecha_factura + INTERVAL p.dias_credito DAY)) - TO_DAYS(CURDATE())) <= 0) THEN 'Vencido'
        WHEN ((TO_DAYS((d.fecha_factura + INTERVAL p.dias_credito DAY)) - TO_DAYS(CURDATE())) BETWEEN 1 AND 30) THEN 'A 30'
        WHEN ((TO_DAYS((d.fecha_factura + INTERVAL p.dias_credito DAY)) - TO_DAYS(CURDATE())) BETWEEN 31 AND 60) THEN 'A 60'
        WHEN ((TO_DAYS((d.fecha_factura + INTERVAL p.dias_credito DAY)) - TO_DAYS(CURDATE())) BETWEEN 61 AND 90) THEN 'A 90'
        ELSE '90 +'
    END) AS estado,
    (CASE
        WHEN (
            IFNULL((
                SELECT SUM(dp1.monto)
                FROM pedidosjb_pedidos.despacho_pago dp1
                WHERE dp1.iddespacho = d.iddespacho
                  AND dp1.estado = 'EJECUTADO'
            ), 0) = 0
        ) THEN 'PENDIENTE'
        WHEN (
            IFNULL((
                SELECT SUM(dp1.monto)
                FROM pedidosjb_pedidos.despacho_pago dp1
                WHERE dp1.iddespacho = d.iddespacho
                  AND dp1.estado = 'EJECUTADO'
            ), 0) < d.monto_total
        ) THEN 'PARCIAL'
        ELSE 'PAGADO'
    END) AS estado_pago,
    dp.fecha AS fecha_pago,
    tp.descripcion AS tipo_pago,
    dp.estado AS estado_pago_individual
FROM pedidosjb_pedidos.despacho d
JOIN pedidosjb_pedidos.pedido p ON d.idpedido = p.idpedido
JOIN pedidosjb_pedidos.cliente c ON p.idcliente = c.idcliente
LEFT JOIN pedidosjb_pedidos.despacho_pago dp ON d.iddespacho = dp.iddespacho
LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON dp.idtipo_pago = tp.idtipo_pago
WHERE d.fecha_factura IS NOT NULL;

-- Vista despacho pago detalle (version final con anticipo + imagen)
CREATE OR REPLACE VIEW pedidosjb_pedidos.view_despacho_pago_detalle AS
SELECT
    dp.iddespacho_pago,
    dp.iddespacho,
    dp.fecha,
    dp.estado,
    tp.signo,
    dp.monto,
    (IFNULL(tp.signo, 0) * dp.monto) AS monto_aplicado,
    dp.correlativo_documento,
    dp.banco,
    dp.referencia_pago,
    dp.observaciones,
    dp.imagen,
    dp.usuario_creacion,
    tp.descripcion AS tipo_pago,
    td.nombre AS tipo_documento,
    fp.descripcion AS forma_pago,
    dp.idcliente_anticipo,
    ca.saldo_disponible AS anticipo_saldo
FROM pedidosjb_pedidos.despacho_pago dp
LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
LEFT JOIN pedidosjb_pedidos.forma_pago fp ON fp.idforma_pago = dp.idforma_pago
LEFT JOIN pedidosjb_pedidos.tipo_documento td ON td.idtipo_documento = dp.idtipo_documento
LEFT JOIN pedidosjb_pedidos.cliente_anticipo ca ON ca.idcliente_anticipo = dp.idcliente_anticipo;

-- Vista despacho pago por tipo de pago
CREATE OR REPLACE VIEW pedidosjb_pedidos.view_despacho_pago_tipo_pago AS
SELECT
    vdpd.iddespacho,
    IFNULL(NULLIF(TRIM(vdpd.tipo_pago), ''), 'SIN TIPO') AS tipo_pago,
    SUM(vdpd.monto_aplicado) AS total_neto
FROM pedidosjb_pedidos.view_despacho_pago_detalle vdpd
WHERE UPPER(TRIM(vdpd.estado)) IN ('PROGRAMADO', 'EJECUTADO')
GROUP BY
    vdpd.iddespacho,
    IFNULL(NULLIF(TRIM(vdpd.tipo_pago), ''), 'SIN TIPO');

-- Vista despacho pago por tipo de documento
CREATE OR REPLACE VIEW pedidosjb_pedidos.view_despacho_pago_tipo_documento AS
SELECT
    vdpd.iddespacho,
    IFNULL(NULLIF(TRIM(vdpd.tipo_documento), ''), 'SIN TIPO') AS tipo_documento,
    SUM(vdpd.monto_aplicado) AS total_neto
FROM pedidosjb_pedidos.view_despacho_pago_detalle vdpd
WHERE UPPER(TRIM(vdpd.estado)) IN ('PROGRAMADO', 'EJECUTADO')
GROUP BY
    vdpd.iddespacho,
    IFNULL(NULLIF(TRIM(vdpd.tipo_documento), ''), 'SIN TIPO');

-- Vista despacho pago resumen
CREATE OR REPLACE VIEW pedidosjb_pedidos.view_despacho_pago_resumen AS
SELECT
    d.iddespacho,
    d.idpedido,
    vp.nopedido,
    vp.idcliente,
    vp.cliente,
    d.fecha,
    d.monto_total AS monto_despacho,
    IFNULL((
        SELECT SUM(dp.monto * IFNULL(tp.signo, 0))
        FROM pedidosjb_pedidos.despacho_pago dp
        LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
        WHERE dp.iddespacho = d.iddespacho
          AND UPPER(TRIM(dp.estado)) = 'EJECUTADO'
    ), 0) AS total_pagado_ejecutado,
    IFNULL((
        SELECT SUM(dp.monto * IFNULL(tp.signo, 0))
        FROM pedidosjb_pedidos.despacho_pago dp
        LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
        WHERE dp.iddespacho = d.iddespacho
          AND UPPER(TRIM(dp.estado)) = 'PROGRAMADO'
    ), 0) AS total_programado_neto,
    (
        d.monto_total - IFNULL((
            SELECT SUM(dp.monto * IFNULL(tp.signo, 0))
            FROM pedidosjb_pedidos.despacho_pago dp
            LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
            WHERE dp.iddespacho = d.iddespacho
              AND UPPER(TRIM(dp.estado)) = 'EJECUTADO'
        ), 0)
    ) AS saldo_pendiente
FROM pedidosjb_pedidos.despacho d
LEFT JOIN pedidosjb_pedidos.view_pedidos vp ON vp.idpedido = d.idpedido
WHERE d.estado IN ('ACTIVO', 'CERRADO');

/* =====================================================================
   PEDIDOSJB_SEGURIDAD - OPCIONES, ACCIONES Y ROLES
   ===================================================================== */
USE pedidosjb_seguridad;

-- Opcion: Anticipos de cliente
INSERT INTO pedidosjb_seguridad.opcion (idmenu, nombre, entity, funcion, orden, estado)
SELECT 7, 'Anticipos de cliente', 'cliente_anticipo', 'cargar_opcion', 22, 'ACTIVO'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM pedidosjb_seguridad.opcion
    WHERE entity = 'cliente_anticipo'
);

-- Opcion: Registro pagos despacho
INSERT INTO pedidosjb_seguridad.opcion (idmenu, nombre, entity, funcion, orden, estado)
SELECT 7, 'Registro pagos despacho', 'despacho_pago', 'cargar_opcion', 21, 'ACTIVO'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM pedidosjb_seguridad.opcion
    WHERE entity = 'despacho_pago'
);

-- Opcion: Estado de cuenta
INSERT INTO pedidosjb_seguridad.opcion (idmenu, nombre, entity, funcion, orden, estado)
SELECT 9, 'Estado de cuenta', 'despacho', 'cargar_estado_de_cuenta', 21, 'ACTIVO'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM pedidosjb_seguridad.opcion
    WHERE entity = 'despacho'
      AND funcion = 'cargar_estado_de_cuenta'
);

-- Ajuste de opcion existente de estado de cuenta
UPDATE pedidosjb_seguridad.opcion
SET idmenu = 9,
    nombre = 'Estado de cuenta',
    funcion = 'cargar_estado_de_cuenta',
    orden = 21,
    estado = 'ACTIVO'
WHERE entity = 'despacho'
  AND funcion = 'cargar_estado_de_cuenta';

-- Acciones de cliente_anticipo
INSERT INTO pedidosjb_seguridad.accion (idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
SELECT
    o.idopcion,
    a.nombre,
    a.indOpcion,
    a.referencia1,
    a.referencia2,
    a.referencia3,
    'ACTIVO'
FROM pedidosjb_seguridad.opcion o
JOIN (
    SELECT 'Opcion_cliente_anticipo' AS nombre, 'SI' AS indOpcion, 'idcliente_anticipo' AS referencia1, NULL AS referencia2, NULL AS referencia3
    UNION ALL SELECT 'Consultar_cliente_anticipo', 'NO', 'idcliente_anticipo', NULL, NULL
    UNION ALL SELECT 'Crear_cliente_anticipo', 'NO', 'idcliente_anticipo', 'idcliente', 'monto'
    UNION ALL SELECT 'Modificar_cliente_anticipo', 'NO', 'idcliente_anticipo', 'idcliente', 'monto'
    UNION ALL SELECT 'Cambiar_estado_cliente_anticipo', 'NO', 'idcliente_anticipo', 'estado', NULL
    UNION ALL SELECT 'Crear_cliente_anticipo_aplicacion', 'NO', 'idcliente_anticipo_aplicacion', 'idcliente_anticipo', 'monto_aplicado'
    UNION ALL SELECT 'Actualizar_cliente_anticipo_aplicacion', 'NO', 'idcliente_anticipo_aplicacion', 'idcliente_anticipo', 'monto_aplicado'
    UNION ALL SELECT 'Cancelar_cliente_anticipo_aplicacion', 'NO', 'idcliente_anticipo_aplicacion', 'estado', NULL
    UNION ALL SELECT 'Consultar_cliente_anticipo_aplicacion', 'NO', 'idcliente_anticipo_aplicacion', NULL, NULL
) a ON 1 = 1
WHERE o.entity = 'cliente_anticipo'
  AND NOT EXISTS (
      SELECT 1
      FROM pedidosjb_seguridad.accion x
      WHERE x.idopcion = o.idopcion
        AND x.nombre = a.nombre
  );

-- Acciones de despacho_pago
INSERT INTO pedidosjb_seguridad.accion (idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
SELECT
    o.idopcion,
    a.nombre,
    a.indOpcion,
    a.referencia1,
    a.referencia2,
    a.referencia3,
    'ACTIVO'
FROM pedidosjb_seguridad.opcion o
JOIN (
    SELECT 'Opcion_despacho_pago' AS nombre, 'SI' AS indOpcion, 'iddespacho' AS referencia1, NULL AS referencia2, NULL AS referencia3
    UNION ALL SELECT 'Consultar_despacho_pago', 'NO', 'iddespacho_pago', NULL, NULL
    UNION ALL SELECT 'Crear_despacho_pago', 'NO', 'iddespacho_pago', 'iddespacho', 'monto'
    UNION ALL SELECT 'Modificar_despacho_pago', 'NO', 'iddespacho_pago', 'iddespacho', 'monto'
    UNION ALL SELECT 'Eliminar_despacho_pago', 'NO', 'iddespacho_pago', 'iddespacho', 'estado'
    UNION ALL SELECT 'Ejecutar_despacho_pago', 'NO', 'iddespacho_pago', 'iddespacho', 'estado'
    UNION ALL SELECT 'Imprimir_despacho_pago', 'NO', 'iddespacho_pago', 'correlativo_documento', 'tipo_documento'
) a ON 1 = 1
WHERE o.entity = 'despacho_pago'
  AND NOT EXISTS (
      SELECT 1
      FROM pedidosjb_seguridad.accion x
      WHERE x.idopcion = o.idopcion
        AND x.nombre = a.nombre
  );

-- Accion de opcion estado de cuenta
INSERT INTO pedidosjb_seguridad.accion (idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
SELECT
    o.idopcion,
    'opcion_estado_de_cuenta',
    'SI',
    NULL,
    NULL,
    NULL,
    'ACTIVO'
FROM pedidosjb_seguridad.opcion o
WHERE o.entity = 'despacho'
  AND o.funcion = 'cargar_estado_de_cuenta'
  AND NOT EXISTS (
      SELECT 1
      FROM pedidosjb_seguridad.accion a
      WHERE a.idopcion = o.idopcion
        AND a.nombre = 'opcion_estado_de_cuenta'
  );

-- Asignacion de acciones al rol 1
INSERT INTO pedidosjb_seguridad.rol_accion (idrol, idaccion, indFavorito)
SELECT
    1,
    a.idaccion,
    CASE
        WHEN a.nombre IN ('Opcion_cliente_anticipo') THEN 'SI'
        ELSE 'NO'
    END AS indFavorito
FROM pedidosjb_seguridad.accion a
JOIN pedidosjb_seguridad.opcion o ON o.idopcion = a.idopcion
WHERE o.entity IN ('cliente_anticipo', 'despacho_pago')
  AND NOT EXISTS (
      SELECT 1
      FROM pedidosjb_seguridad.rol_accion r
      WHERE r.idrol = 1
        AND r.idaccion = a.idaccion
  );

INSERT INTO pedidosjb_seguridad.rol_accion (idrol, idaccion, indFavorito)
SELECT 1, a.idaccion, 'NO'
FROM pedidosjb_seguridad.accion a
JOIN pedidosjb_seguridad.opcion o ON o.idopcion = a.idopcion
WHERE o.entity = 'despacho'
  AND o.funcion = 'cargar_estado_de_cuenta'
  AND a.nombre = 'opcion_estado_de_cuenta'
  AND NOT EXISTS (
      SELECT 1
      FROM pedidosjb_seguridad.rol_accion r
      WHERE r.idrol = 1
        AND r.idaccion = a.idaccion
  );

SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS = @OLD_UNIQUE_CHECKS;
SET SQL_MODE = @OLD_SQL_MODE;
