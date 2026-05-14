CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_despacho_pago_resumen` AS
SELECT
    `d`.`iddespacho` AS `iddespacho`,
    `d`.`idpedido` AS `idpedido`,
    `d`.`numero_factura` AS `numero_factura`,
    `vp`.`nopedido` AS `nopedido`,
    `vp`.`idcliente` AS `idcliente`,
    `vp`.`cliente` AS `cliente`,
    `d`.`fecha` AS `fecha`,
    `d`.`monto_total` AS `monto_despacho`,

    IFNULL(
        (
            SELECT SUM(`dp`.`monto` * IFNULL(`tp`.`signo`,0))
            FROM `pedidosjb_pedidos`.`despacho_pago` `dp`
            LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp`
                ON `tp`.`idtipo_pago` = `dp`.`idtipo_pago`
            WHERE `dp`.`iddespacho` = `d`.`iddespacho`
            AND UPPER(TRIM(`dp`.`estado`)) = 'EJECUTADO'
        ),0
    ) AS `total_pagado_ejecutado`,

    IFNULL(
        (
            SELECT SUM(`dp`.`monto` * IFNULL(`tp`.`signo`,0))
            FROM `pedidosjb_pedidos`.`despacho_pago` `dp`
            LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp`
                ON `tp`.`idtipo_pago` = `dp`.`idtipo_pago`
            WHERE `dp`.`iddespacho` = `d`.`iddespacho`
            AND UPPER(TRIM(`dp`.`estado`)) = 'PROGRAMADO'
        ),0
    ) AS `total_programado_neto`,

    (
        `d`.`monto_total`
        -
        IFNULL(
            (
                SELECT SUM(`dp`.`monto` * IFNULL(`tp`.`signo`,0))
                FROM `pedidosjb_pedidos`.`despacho_pago` `dp`
                LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp`
                    ON `tp`.`idtipo_pago` = `dp`.`idtipo_pago`
                WHERE `dp`.`iddespacho` = `d`.`iddespacho`
                AND UPPER(TRIM(`dp`.`estado`)) = 'EJECUTADO'
            ),0
        )
    ) AS `saldo_pendiente`

FROM `pedidosjb_pedidos`.`despacho` `d`
LEFT JOIN `pedidosjb_pedidos`.`view_pedidos` `vp`
    ON `vp`.`idpedido` = `d`.`idpedido`

WHERE `d`.`estado` IN ('ACTIVO', 'CERRADO');