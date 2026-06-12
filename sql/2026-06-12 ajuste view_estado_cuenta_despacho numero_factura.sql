-- 2026-06-12
-- Ajuste: incluir numero_factura en view_estado_cuenta_despacho para mostrar No. despacho/factura en estado de cuenta resumido.

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
    IFNULL(SUM((CASE WHEN (`dp`.`estado` = 'EJECUTADO') THEN `dp`.`monto` ELSE 0 END)), 0) AS `monto_total_pagado`,
    IFNULL(SUM((CASE WHEN (`dp`.`estado` = 'PROGRAMADO') THEN `dp`.`monto` ELSE 0 END)), 0) AS `monto_programado`,
    (`d`.`monto_total` - IFNULL(SUM((CASE WHEN (`dp`.`estado` = 'EJECUTADO') THEN `dp`.`monto` ELSE 0 END)), 0)) AS `saldo_pendiente`,
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
    (((`pedidosjb_pedidos`.`despacho` `d`
JOIN `pedidosjb_pedidos`.`pedido` `p` ON
    ((`d`.`idpedido` = `p`.`idpedido`)))
JOIN `pedidosjb_pedidos`.`cliente` `c` ON
    ((`p`.`idcliente` = `c`.`idcliente`)))
LEFT JOIN `pedidosjb_pedidos`.`despacho_pago` `dp` ON
    ((`d`.`iddespacho` = `dp`.`iddespacho`)))
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
