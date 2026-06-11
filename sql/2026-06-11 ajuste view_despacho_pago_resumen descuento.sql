USE pedidosjb_pedidos;

-- Ajuste: el DESCUENTO no debe afectar total_pagado_ejecutado ni total_programado_neto.
-- Debe disminuir el monto base del despacho y, por lo tanto, el saldo_pendiente.

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
    (
        IFNULL(`d`.`monto_total`, 0)
        - IFNULL((
            SELECT SUM(`dp_desc`.`monto`)
            FROM `pedidosjb_pedidos`.`despacho_pago` `dp_desc`
            LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp_desc`
                ON `tp_desc`.`idtipo_pago` = `dp_desc`.`idtipo_pago`
            WHERE `dp_desc`.`iddespacho` = `d`.`iddespacho`
              AND UPPER(TRIM(`dp_desc`.`estado`)) IN ('PROGRAMADO', 'EJECUTADO')
              AND UPPER(TRIM(IFNULL(`tp_desc`.`descripcion`, ''))) = 'DESCUENTO'
        ), 0)
    ) AS `monto_despacho`,
    IFNULL((
        SELECT SUM(`dp_ej`.`monto` * IFNULL(`tp_ej`.`signo`, 0))
        FROM `pedidosjb_pedidos`.`despacho_pago` `dp_ej`
        LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp_ej`
            ON `tp_ej`.`idtipo_pago` = `dp_ej`.`idtipo_pago`
                WHERE `dp_ej`.`iddespacho` = `d`.`iddespacho`
                    AND UPPER(TRIM(`dp_ej`.`estado`)) = 'EJECUTADO'
                    AND UPPER(TRIM(IFNULL(`tp_ej`.`descripcion`, ''))) NOT IN ('DESCUENTO', 'DEVOLUCION', 'DEVOLUCIÓN')
    ), 0) AS `total_pagado_ejecutado`,
    IFNULL((
        SELECT SUM(`dp_pr`.`monto` * IFNULL(`tp_pr`.`signo`, 0))
        FROM `pedidosjb_pedidos`.`despacho_pago` `dp_pr`
        LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp_pr`
            ON `tp_pr`.`idtipo_pago` = `dp_pr`.`idtipo_pago`
                WHERE `dp_pr`.`iddespacho` = `d`.`iddespacho`
                    AND UPPER(TRIM(`dp_pr`.`estado`)) = 'PROGRAMADO'
                    AND UPPER(TRIM(IFNULL(`tp_pr`.`descripcion`, ''))) NOT IN ('DESCUENTO', 'DEVOLUCION', 'DEVOLUCIÓN')
    ), 0) AS `total_programado_neto`,
    (
        (
            IFNULL(`d`.`monto_total`, 0)
            - IFNULL((
                SELECT SUM(`dp_desc2`.`monto`)
                FROM `pedidosjb_pedidos`.`despacho_pago` `dp_desc2`
                LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp_desc2`
                    ON `tp_desc2`.`idtipo_pago` = `dp_desc2`.`idtipo_pago`
                WHERE `dp_desc2`.`iddespacho` = `d`.`iddespacho`
                  AND UPPER(TRIM(`dp_desc2`.`estado`)) IN ('PROGRAMADO', 'EJECUTADO')
                  AND UPPER(TRIM(IFNULL(`tp_desc2`.`descripcion`, ''))) = 'DESCUENTO'
            ), 0)
        )
        - IFNULL((
            SELECT SUM(`dp_sal`.`monto` * IFNULL(`tp_sal`.`signo`, 0))
            FROM `pedidosjb_pedidos`.`despacho_pago` `dp_sal`
            LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp_sal`
                ON `tp_sal`.`idtipo_pago` = `dp_sal`.`idtipo_pago`
                        WHERE `dp_sal`.`iddespacho` = `d`.`iddespacho`
                            AND UPPER(TRIM(`dp_sal`.`estado`)) = 'EJECUTADO'
                            AND UPPER(TRIM(IFNULL(`tp_sal`.`descripcion`, ''))) NOT IN ('DESCUENTO', 'DEVOLUCION', 'DEVOLUCIÓN')
        ), 0)
    ) AS `saldo_pendiente`
FROM `pedidosjb_pedidos`.`despacho` `d`
LEFT JOIN `pedidosjb_pedidos`.`view_pedidos` `vp`
    ON `vp`.`idpedido` = `d`.`idpedido`
WHERE `d`.`estado` IN ('ACTIVO', 'CERRADO');
