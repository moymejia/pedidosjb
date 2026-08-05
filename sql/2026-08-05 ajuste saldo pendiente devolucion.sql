USE pedidosjb_pedidos;

-- Ajuste: la DEVOLUCION debe disminuir el saldo pendiente del despacho.
-- Se trata como reduccion del monto base junto con DESCUENTO, sin inflar
-- los totales de pagos ejecutados/programados.

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
            SELECT SUM(`dp_aj`.`monto`)
            FROM `pedidosjb_pedidos`.`despacho_pago` `dp_aj`
            LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp_aj`
                ON `tp_aj`.`idtipo_pago` = `dp_aj`.`idtipo_pago`
            WHERE `dp_aj`.`iddespacho` = `d`.`iddespacho`
              AND UPPER(TRIM(`dp_aj`.`estado`)) IN ('PROGRAMADO', 'EJECUTADO')
              AND UPPER(TRIM(IFNULL(`tp_aj`.`descripcion`, ''))) IN ('DESCUENTO', 'DEVOLUCION', 'DEVOLUCIÓN')
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
                SELECT SUM(`dp_aj2`.`monto`)
                FROM `pedidosjb_pedidos`.`despacho_pago` `dp_aj2`
                LEFT JOIN `pedidosjb_pedidos`.`tipo_pago` `tp_aj2`
                    ON `tp_aj2`.`idtipo_pago` = `dp_aj2`.`idtipo_pago`
                WHERE `dp_aj2`.`iddespacho` = `d`.`iddespacho`
                  AND UPPER(TRIM(`dp_aj2`.`estado`)) IN ('PROGRAMADO', 'EJECUTADO')
                  AND UPPER(TRIM(IFNULL(`tp_aj2`.`descripcion`, ''))) IN ('DESCUENTO', 'DEVOLUCION', 'DEVOLUCIÓN')
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

CREATE OR REPLACE VIEW pedidosjb_pedidos.view_cartera_de_clientes AS
SELECT
    d.iddespacho,
    p.idpedido,
    p.idcliente,
    c.codigo AS codigo_cliente,
    c.nombre AS nombre_cliente,
    p.usuario_creacion AS usuario_vendedor,
    IFNULL(u.nombre, p.usuario_creacion) AS nombre_vendedor,
    d.numero_factura,
    d.fecha_factura,
    (
        IFNULL(d.monto_total, 0)
        - IFNULL(dp_ajuste.monto_ajuste, 0)
    ) AS monto_total,
    IFNULL(dp_ejecutado.monto_ejecutado, 0) AS monto_ejecutado,
    IFNULL(dp_programado.monto_programado, 0) AS monto_programado,
    IFNULL(dp_no_ejecutado.monto_no_ejecutado, 0) AS monto_no_ejecutado,
    GREATEST(
        (
            IFNULL(d.monto_total, 0)
            - IFNULL(dp_ajuste.monto_ajuste, 0)
        ) - IFNULL(dp_ejecutado.monto_ejecutado, 0),
        0
    ) AS saldo_cartera,
    DATEDIFF(CURDATE(), d.fecha_factura) AS dias_transcurridos,
    CASE
        WHEN DATEDIFF(CURDATE(), d.fecha_factura) <= 30 THEN
            GREATEST(((IFNULL(d.monto_total, 0) - IFNULL(dp_ajuste.monto_ajuste, 0)) - IFNULL(dp_ejecutado.monto_ejecutado, 0)), 0)
        ELSE 0
    END AS saldo_0_30,
    CASE
        WHEN DATEDIFF(CURDATE(), d.fecha_factura) BETWEEN 31 AND 60 THEN
            GREATEST(((IFNULL(d.monto_total, 0) - IFNULL(dp_ajuste.monto_ajuste, 0)) - IFNULL(dp_ejecutado.monto_ejecutado, 0)), 0)
        ELSE 0
    END AS saldo_31_60,
    CASE
        WHEN DATEDIFF(CURDATE(), d.fecha_factura) BETWEEN 61 AND 90 THEN
            GREATEST(((IFNULL(d.monto_total, 0) - IFNULL(dp_ajuste.monto_ajuste, 0)) - IFNULL(dp_ejecutado.monto_ejecutado, 0)), 0)
        ELSE 0
    END AS saldo_61_90,
    CASE
        WHEN DATEDIFF(CURDATE(), d.fecha_factura) > 90 THEN
            GREATEST(((IFNULL(d.monto_total, 0) - IFNULL(dp_ajuste.monto_ajuste, 0)) - IFNULL(dp_ejecutado.monto_ejecutado, 0)), 0)
        ELSE 0
    END AS saldo_91_mas
FROM pedidosjb_pedidos.despacho d
JOIN pedidosjb_pedidos.pedido p ON p.idpedido = d.idpedido
JOIN pedidosjb_pedidos.cliente c ON c.idcliente = p.idcliente
LEFT JOIN pedidosjb_seguridad.usuario u ON u.usuario = p.usuario_creacion
LEFT JOIN (
    SELECT
        dp.iddespacho,
        SUM(dp.monto) AS monto_ajuste
    FROM pedidosjb_pedidos.despacho_pago dp
    LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
    WHERE UPPER(TRIM(dp.estado)) IN ('PROGRAMADO', 'EJECUTADO')
      AND UPPER(TRIM(IFNULL(tp.descripcion, ''))) IN ('DESCUENTO', 'DEVOLUCION', 'DEVOLUCIÓN')
    GROUP BY dp.iddespacho
) dp_ajuste ON dp_ajuste.iddespacho = d.iddespacho
LEFT JOIN (
    SELECT
        dp.iddespacho,
        SUM(dp.monto * IFNULL(tp.signo, 0)) AS monto_ejecutado
    FROM pedidosjb_pedidos.despacho_pago dp
    LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
    WHERE UPPER(TRIM(dp.estado)) = 'EJECUTADO'
      AND UPPER(TRIM(IFNULL(tp.descripcion, ''))) NOT IN ('DESCUENTO', 'DEVOLUCION', 'DEVOLUCIÓN')
    GROUP BY dp.iddespacho
) dp_ejecutado ON dp_ejecutado.iddespacho = d.iddespacho
LEFT JOIN (
    SELECT
        dp.iddespacho,
        SUM(dp.monto * IFNULL(tp.signo, 0)) AS monto_programado
    FROM pedidosjb_pedidos.despacho_pago dp
    LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
    WHERE UPPER(TRIM(dp.estado)) = 'PROGRAMADO'
      AND UPPER(TRIM(IFNULL(tp.descripcion, ''))) NOT IN ('DESCUENTO', 'DEVOLUCION', 'DEVOLUCIÓN')
    GROUP BY dp.iddespacho
) dp_programado ON dp_programado.iddespacho = d.iddespacho
LEFT JOIN (
    SELECT
        dp.iddespacho,
        SUM(dp.monto * IFNULL(tp.signo, 0)) AS monto_no_ejecutado
    FROM pedidosjb_pedidos.despacho_pago dp
    LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
    WHERE UPPER(TRIM(dp.estado)) = 'PROGRAMADO'
      AND UPPER(TRIM(IFNULL(tp.descripcion, ''))) NOT IN ('DESCUENTO', 'DEVOLUCION', 'DEVOLUCIÓN')
    GROUP BY dp.iddespacho
) dp_no_ejecutado ON dp_no_ejecutado.iddespacho = d.iddespacho
WHERE d.fecha_factura IS NOT NULL
  AND GREATEST(
        (
            IFNULL(d.monto_total, 0)
            - IFNULL(dp_ajuste.monto_ajuste, 0)
        ) - IFNULL(dp_ejecutado.monto_ejecutado, 0),
        0
    ) > 0
  AND IFNULL(TRIM(d.numero_factura), '') <> '';
