-- Unificado: Liquidacion de ingresos y reporte de recibos
-- Fecha: 2026-10-05
-- Descripcion:
--   1) Actualiza view_estado_cuenta_despacho_detallado para exponer observaciones_anticipo.
--   2) Crea view_reporte_recibo_cheques para centralizar la logica del reporte.
--   3) Registra la opcion y accion de seguridad del reporte de recibos.

-- ============================================================
-- 1) Vista para liquidacion de ingresos
-- ============================================================

-- Ejecutar antes de utilizar la columna Inf. anticipo en la liquidacion de ingresos.
-- Conserva los campos existentes y agrega las observaciones del anticipo asociado al pago.
CREATE OR REPLACE
ALGORITHM = UNDEFINED
DEFINER = `root`@`%`
SQL SECURITY DEFINER
VIEW `pedidosjb_pedidos`.`view_estado_cuenta_despacho_detallado` AS
SELECT
    `d`.`iddespacho` AS `iddespacho`,
    `p`.`idcliente` AS `idcliente`,
    CONCAT(`c`.`codigo`, ' - ', `c`.`nombre`) AS `nombre_cliente`,
    `p`.`idtemporada` AS `idtemporada`,
    `d`.`numero_factura` AS `numero_factura`,
    `d`.`fecha_factura` AS `fecha_factura`,
    `d`.`monto_total` AS `monto_total`,
    IFNULL(`d`.`monto_flete`, 0) AS `monto_flete`,
    `dp`.`monto` AS `monto_pago`,
    IFNULL(`tp`.`signo`, 1) AS `signo_pago`,
    `dp`.`correlativo_documento` AS `correlativo_documento`,
    `dp`.`referencia_pago` AS `referencia_pago`,
    `dp`.`numero_recuperado` AS `numero_recuperado`,
    (`d`.`monto_total` - IFNULL((
        SELECT SUM(`dp1`.`monto` * IFNULL(`tp1`.`signo`, 1))
        FROM `pedidosjb_pedidos`.`despacho_pago` `dp1`
        LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp1` ON `tp1`.`idtipo_pago` = `dp1`.`idtipo_pago`
        WHERE (`dp1`.`iddespacho` = `d`.`iddespacho`) AND (`dp1`.`estado` = 'EJECUTADO')
    ), 0)) AS `saldo_pendiente`,
    (`d`.`fecha_factura` + INTERVAL `p`.`dias_credito` DAY) AS `fecha_vencimiento`,
    (TO_DAYS((`d`.`fecha_factura` + INTERVAL `p`.`dias_credito` DAY)) - TO_DAYS(CURDATE())) AS `proximidad`,
    (CASE
        WHEN ((TO_DAYS((`d`.`fecha_factura` + INTERVAL `p`.`dias_credito` DAY)) - TO_DAYS(CURDATE())) <= 0) THEN 'Vencido'
        WHEN ((TO_DAYS((`d`.`fecha_factura` + INTERVAL `p`.`dias_credito` DAY)) - TO_DAYS(CURDATE())) BETWEEN 1 AND 30) THEN 'A 30'
        WHEN ((TO_DAYS((`d`.`fecha_factura` + INTERVAL `p`.`dias_credito` DAY)) - TO_DAYS(CURDATE())) BETWEEN 31 AND 60) THEN 'A 60'
        WHEN ((TO_DAYS((`d`.`fecha_factura` + INTERVAL `p`.`dias_credito` DAY)) - TO_DAYS(CURDATE())) BETWEEN 61 AND 90) THEN 'A 90'
        ELSE '90 +'
    END) AS `estado`,
    (CASE
        WHEN (IFNULL((
            SELECT SUM(`dp1`.`monto` * IFNULL(`tp1`.`signo`, 1))
            FROM `pedidosjb_pedidos`.`despacho_pago` `dp1`
            LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp1` ON `tp1`.`idtipo_pago` = `dp1`.`idtipo_pago`
            WHERE (`dp1`.`iddespacho` = `d`.`iddespacho`) AND (`dp1`.`estado` = 'EJECUTADO')
        ), 0) = 0) THEN 'PENDIENTE'
        WHEN (IFNULL((
            SELECT SUM(`dp1`.`monto` * IFNULL(`tp1`.`signo`, 1))
            FROM `pedidosjb_pedidos`.`despacho_pago` `dp1`
            LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp1` ON `tp1`.`idtipo_pago` = `dp1`.`idtipo_pago`
            WHERE (`dp1`.`iddespacho` = `d`.`iddespacho`) AND (`dp1`.`estado` = 'EJECUTADO')
        ), 0) < `d`.`monto_total`) THEN 'PARCIAL'
        ELSE 'PAGADO'
    END) AS `estado_pago`,
    (CASE
        WHEN (UPPER(TRIM(IFNULL(`dp`.`estado`, ''))) = 'EJECUTADO') THEN IFNULL(`dp`.`fecha_ejecutado`, `dp`.`fecha`)
        ELSE `dp`.`fecha`
    END) AS `fecha_pago`,
    `tp`.`descripcion` AS `tipo_pago`,
    `td`.`nombre` AS `tipo_documento`,
    `dp`.`estado` AS `estado_pago_individual`,
    `c`.`codigo` AS `codigo_cliente`,
    `dp`.`banco` AS `banco`,
    (SELECT `ca`.`observaciones`
     FROM `pedidosjb_pedidos`.`cliente_anticipo` `ca`
     WHERE `ca`.`idcliente_anticipo` = `dp`.`idcliente_anticipo`) AS `observaciones_anticipo`
FROM
    (((((`pedidosjb_pedidos`.`despacho` `d`
JOIN `pedidosjb_pedidos`.`pedido` `p` ON
    ((`d`.`idpedido` = `p`.`idpedido`)))
JOIN `pedidosjb_pedidos`.`cliente` `c` ON
    ((`p`.`idcliente` = `c`.`idcliente`)))
LEFT JOIN `pedidosjb_pedidos`.`despacho_pago` `dp` ON
    ((`d`.`iddespacho` = `dp`.`iddespacho`)))
LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp` ON
    ((`dp`.`idtipo_pago` = `tp`.`idtipo_pago`)))
LEFT JOIN `pedidosjb_pedidos`.`tipo_documento` `td` ON
    ((`dp`.`idtipo_documento` = `td`.`idtipo_documento`)))
WHERE
    (`d`.`fecha_factura` IS NOT NULL);


-- ============================================================
-- 2) Vista para reporte de recibos
-- ============================================================

CREATE OR REPLACE
ALGORITHM = UNDEFINED
DEFINER = `root`@`%`
SQL SECURITY DEFINER
VIEW `pedidosjb_pedidos`.`view_reporte_recibo_cheques` AS
SELECT
    `idcliente` AS `idcliente`,
    IFNULL(`referencia_pago`, '') AS `cheque_no`,
    TRIM(IFNULL(`banco`, '')) AS `banco`,
    `fecha_pago` AS `fecha_cheque_raw`,
    IFNULL(`correlativo_documento`, '') AS `recibo_caja`,
    IFNULL(`tipo_documento`, '') AS `tipo_recibo`,
    IFNULL(`estado_pago_individual`, '') AS `estado_cheque`,
    IFNULL(`monto_pago`, 0) AS `valor_cheque`
FROM `pedidosjb_pedidos`.`view_estado_cuenta_despacho_detallado`
WHERE `fecha_pago` IS NOT NULL
    AND UPPER(TRIM(IFNULL(`tipo_pago`, ''))) LIKE '%CHEQUE%'
    AND UPPER(TRIM(IFNULL(`estado_pago_individual`, ''))) IN ('EJECUTADO', 'PROGRAMADO')
    AND UPPER(TRIM(IFNULL(`tipo_documento`, ''))) NOT LIKE 'RECUPER%';


-- ============================================================
-- 3) Opcion Reporte de recibos
-- ============================================================

-- Reporte: Reporte de recibos
-- Fecha: 2026-10-05
-- Descripcion:
--   Registra opcion y accion de seguridad para el reporte de recibos con cheques.

USE pedidosjb_seguridad;

SET @idopcion_reporte_recibo_cheques = (
    SELECT idopcion
    FROM opcion
    WHERE entity = 'reporte_recibo_cheques'
      AND funcion = 'cargar_opcion'
    LIMIT 1
);

SET @idopcion_reporte_recibo_cheques = IFNULL(
    @idopcion_reporte_recibo_cheques,
    (SELECT IFNULL(MAX(idopcion), 0) + 1 FROM opcion)
);

INSERT INTO opcion (idopcion, idmenu, nombre, entity, funcion, orden, estado)
SELECT
    @idopcion_reporte_recibo_cheques,
    9,
    'Reporte de recibos',
    'reporte_recibo_cheques',
    'cargar_opcion',
    27,
    'ACTIVO'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM opcion
    WHERE entity = 'reporte_recibo_cheques'
      AND funcion = 'cargar_opcion'
);

SET @idopcion_reporte_recibo_cheques = (
    SELECT idopcion
    FROM opcion
    WHERE entity = 'reporte_recibo_cheques'
      AND funcion = 'cargar_opcion'
    LIMIT 1
);

SET @idaccion_reporte_recibo_cheques = (
    SELECT idaccion
    FROM accion
    WHERE idopcion = @idopcion_reporte_recibo_cheques
      AND nombre = 'Opcion_reporte_recibo_cheques'
    LIMIT 1
);

SET @idaccion_reporte_recibo_cheques = IFNULL(
    @idaccion_reporte_recibo_cheques,
    (SELECT IFNULL(MAX(idaccion), 0) + 1 FROM accion)
);

INSERT INTO accion (idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
SELECT
    @idaccion_reporte_recibo_cheques,
    @idopcion_reporte_recibo_cheques,
    'Opcion_reporte_recibo_cheques',
    'SI',
    NULL,
    NULL,
    NULL,
    'ACTIVO'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM accion
    WHERE idopcion = @idopcion_reporte_recibo_cheques
      AND nombre = 'Opcion_reporte_recibo_cheques'
);

INSERT INTO rol_accion (idrol, idaccion, indFavorito)
SELECT 1, @idaccion_reporte_recibo_cheques, 'NO'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM rol_accion
    WHERE idrol = 1
      AND idaccion = @idaccion_reporte_recibo_cheques
);
