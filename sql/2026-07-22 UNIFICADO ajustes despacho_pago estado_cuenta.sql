-- 2026-07-02 UNIFICADO
-- Incluye:
-- 1) fecha_ejecutado en despacho_pago
-- 2) indice unico para referencia_pago
-- 3) tipos de pago Correcion +/-
-- 4) views de estado de cuenta con signo, referencia y numero recuperado

USE pedidosjb_pedidos;

-- 1) Agregar fecha real de ejecucion si no existe
ALTER TABLE despacho_pago
    ADD COLUMN fecha_ejecutado DATE NULL AFTER estado;

-- 3) Tipos de pago para ajustes (segun solicitud)
INSERT INTO pedidosjb_pedidos.tipo_pago
(idtipo_pago, descripcion, estado, fecha_creacion, usuario_creacion, fecha_modificacion, usuario_modificacion, signo)
VALUES(13, 'Correcion +', 'PROTEGIDO', '2026-07-01 16:16:43', 'admin', NULL, NULL, 1)
ON DUPLICATE KEY UPDATE
descripcion = VALUES(descripcion),
estado = VALUES(estado),
signo = VALUES(signo),
usuario_modificacion = 'admin',
fecha_modificacion = NOW();

INSERT INTO pedidosjb_pedidos.tipo_pago
(idtipo_pago, descripcion, estado, fecha_creacion, usuario_creacion, fecha_modificacion, usuario_modificacion, signo)
VALUES(14, 'Correcion -', 'PROTEGIDO', '2026-07-01 16:16:43', 'admin', NULL, NULL, -1)
ON DUPLICATE KEY UPDATE
descripcion = VALUES(descripcion),
estado = VALUES(estado),
signo = VALUES(signo),
usuario_modificacion = 'admin',
fecha_modificacion = NOW();

-- 4) View base de detalle de pagos (sin joins en entidad)
CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_despacho_pago_detalle` AS
SELECT
    `dp`.`iddespacho_pago` AS `iddespacho_pago`,
    `dp`.`iddespacho` AS `iddespacho`,
    (CASE
        WHEN (UPPER(TRIM(IFNULL(`dp`.`estado`, ''))) = 'EJECUTADO') THEN IFNULL(`dp`.`fecha_ejecutado`, `dp`.`fecha`)
        ELSE `dp`.`fecha`
    END) AS `fecha`,
    `dp`.`estado` AS `estado`,
    `tp`.`signo` AS `signo`,
    `dp`.`monto` AS `monto`,
    (IFNULL(`tp`.`signo`, 0) * `dp`.`monto`) AS `monto_aplicado`,
    `dp`.`correlativo_documento` AS `correlativo_documento`,
    `dp`.`numero_recuperado` AS `numero_recuperado`,
    `dp`.`banco` AS `banco`,
    `dp`.`referencia_pago` AS `referencia_pago`,
    `dp`.`observaciones` AS `observaciones`,
    `dp`.`imagen` AS `imagen`,
    `dp`.`usuario_creacion` AS `usuario_creacion`,
    `tp`.`descripcion` AS `tipo_pago`,
    `td`.`nombre` AS `tipo_documento`,
    `fp`.`descripcion` AS `forma_pago`,
    `dp`.`idcliente_anticipo` AS `idcliente_anticipo`,
    `ca`.`saldo_disponible` AS `anticipo_saldo`
FROM
    ((((`pedidosjb_pedidos`.`despacho_pago` `dp`
LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp` ON
    ((`tp`.`idtipo_pago` = `dp`.`idtipo_pago`)))
LEFT JOIN `pedidosjb_pedidos`.`forma_pago` `fp` ON
    ((`fp`.`idforma_pago` = `dp`.`idforma_pago`)))
LEFT JOIN `pedidosjb_pedidos`.`tipo_documento` `td` ON
    ((`td`.`idtipo_documento` = `dp`.`idtipo_documento`)))
LEFT JOIN `pedidosjb_pedidos`.`cliente_anticipo` `ca` ON
    ((`ca`.`idcliente_anticipo` = `dp`.`idcliente_anticipo`)));

-- 5) View para validaciones de recuperacion
CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_despacho_pago_recuperacion` AS
SELECT
    `dp`.`iddespacho_pago` AS `iddespacho_pago`,
    `dp`.`iddespacho_pago_recupera` AS `iddespacho_pago_recupera`,
    `dp`.`referencia_pago` AS `referencia_pago`,
    `dp`.`correlativo_documento` AS `correlativo_documento`,
    `dp`.`numero_recuperado` AS `numero_recuperado`,
    `dp`.`monto` AS `monto`,
    UPPER(TRIM(IFNULL(`dp`.`estado`, ''))) AS `estado`,
    UPPER(TRIM(IFNULL(`td`.`nombre`, ''))) AS `tipo_documento`,
    UPPER(TRIM(IFNULL(`tp`.`descripcion`, ''))) AS `tipo_pago`
FROM
    ((`pedidosjb_pedidos`.`despacho_pago` `dp`
LEFT JOIN `pedidosjb_pedidos`.`tipo_documento` `td` ON
    ((`td`.`idtipo_documento` = `dp`.`idtipo_documento`)))
LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp` ON
    ((`tp`.`idtipo_pago` = `dp`.`idtipo_pago`)));

-- 6) Estado de cuenta resumido
CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_estado_cuenta_despacho` AS
SELECT
    `d`.`iddespacho` AS `iddespacho`,
    `d`.`numero_factura` AS `numero_factura`,
    `p`.`idcliente` AS `idcliente`,
    CONCAT(`c`.`codigo`, ' - ', `c`.`nombre`) AS `nombre_cliente`,
    `p`.`idtemporada` AS `idtemporada`,
    `d`.`fecha_factura` AS `fecha_factura`,
    `d`.`monto_total` AS `monto_total`,
    IFNULL(SUM((CASE WHEN (`dp`.`estado` = 'EJECUTADO') THEN (`dp`.`monto` * IFNULL(`tp`.`signo`, 1)) ELSE 0 END)), 0) AS `monto_total_pagado`,
    IFNULL(SUM((CASE WHEN (`dp`.`estado` = 'PROGRAMADO') THEN (`dp`.`monto` * IFNULL(`tp`.`signo`, 1)) ELSE 0 END)), 0) AS `monto_programado`,
    (`d`.`monto_total` - IFNULL(SUM((CASE WHEN (`dp`.`estado` = 'EJECUTADO') THEN (`dp`.`monto` * IFNULL(`tp`.`signo`, 1)) ELSE 0 END)), 0)) AS `saldo_pendiente`,
    (`d`.`fecha_factura` + INTERVAL `p`.`dias_credito` DAY) AS `fecha_vencimiento`,
    (TO_DAYS((`d`.`fecha_factura` + INTERVAL `p`.`dias_credito` DAY)) - TO_DAYS(CURDATE())) AS `proximidad`,
    (CASE
        WHEN ((TO_DAYS((`d`.`fecha_factura` + INTERVAL `p`.`dias_credito` DAY)) - TO_DAYS(CURDATE())) <= 0) THEN 'Vencido'
        WHEN ((TO_DAYS((`d`.`fecha_factura` + INTERVAL `p`.`dias_credito` DAY)) - TO_DAYS(CURDATE())) BETWEEN 1 AND 30) THEN 'A 30'
        WHEN ((TO_DAYS((`d`.`fecha_factura` + INTERVAL `p`.`dias_credito` DAY)) - TO_DAYS(CURDATE())) BETWEEN 31 AND 60) THEN 'A 60'
        WHEN ((TO_DAYS((`d`.`fecha_factura` + INTERVAL `p`.`dias_credito` DAY)) - TO_DAYS(CURDATE())) BETWEEN 61 AND 90) THEN 'A 90'
        ELSE '90 +'
    END) AS `estado`
FROM
    ((((`pedidosjb_pedidos`.`despacho` `d`
JOIN `pedidosjb_pedidos`.`pedido` `p` ON
    ((`d`.`idpedido` = `p`.`idpedido`)))
JOIN `pedidosjb_pedidos`.`cliente` `c` ON
    ((`p`.`idcliente` = `c`.`idcliente`)))
LEFT JOIN `pedidosjb_pedidos`.`despacho_pago` `dp` ON
    ((`d`.`iddespacho` = `dp`.`iddespacho`)))
LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp` ON
    ((`dp`.`idtipo_pago` = `tp`.`idtipo_pago`)))
WHERE
    (`d`.`fecha_factura` IS NOT NULL)
GROUP BY
    `d`.`iddespacho`,
    `d`.`numero_factura`,
    `p`.`idcliente`,
    `c`.`codigo`,
    `c`.`nombre`,
    `p`.`idtemporada`,
    `d`.`fecha_factura`,
    `d`.`monto_total`,
    `p`.`dias_credito`;

-- 7) Estado de cuenta detallado
CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_estado_cuenta_despacho_detallado` AS
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
    `dp`.`estado` AS `estado_pago_individual`
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
