USE pedidosjb_pedidos;

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
        - IFNULL(dp_descuento.monto_descuento, 0)
    ) AS monto_total,
    IFNULL(dp_ejecutado.monto_ejecutado, 0) AS monto_ejecutado,
    IFNULL(dp_programado.monto_programado, 0) AS monto_programado,
    IFNULL(dp_no_ejecutado.monto_no_ejecutado, 0) AS monto_no_ejecutado,
    GREATEST(
        (
            IFNULL(d.monto_total, 0)
            - IFNULL(dp_descuento.monto_descuento, 0)
        ) - IFNULL(dp_ejecutado.monto_ejecutado, 0),
        0
    ) AS saldo_cartera,
    DATEDIFF(CURDATE(), d.fecha_factura) AS dias_transcurridos,
    CASE
        WHEN DATEDIFF(CURDATE(), d.fecha_factura) <= 30 THEN
            GREATEST(
                (
                    IFNULL(d.monto_total, 0)
                    - IFNULL(dp_descuento.monto_descuento, 0)
                ) - IFNULL(dp_ejecutado.monto_ejecutado, 0),
                0
            )
        ELSE 0
    END AS saldo_0_30,
    CASE
        WHEN DATEDIFF(CURDATE(), d.fecha_factura) BETWEEN 31 AND 60 THEN
            GREATEST(
                (
                    IFNULL(d.monto_total, 0)
                    - IFNULL(dp_descuento.monto_descuento, 0)
                ) - IFNULL(dp_ejecutado.monto_ejecutado, 0),
                0
            )
        ELSE 0
    END AS saldo_31_60,
    CASE
        WHEN DATEDIFF(CURDATE(), d.fecha_factura) BETWEEN 61 AND 90 THEN
            GREATEST(
                (
                    IFNULL(d.monto_total, 0)
                    - IFNULL(dp_descuento.monto_descuento, 0)
                ) - IFNULL(dp_ejecutado.monto_ejecutado, 0),
                0
            )
        ELSE 0
    END AS saldo_61_90,
    CASE
        WHEN DATEDIFF(CURDATE(), d.fecha_factura) > 90 THEN
            GREATEST(
                (
                    IFNULL(d.monto_total, 0)
                    - IFNULL(dp_descuento.monto_descuento, 0)
                ) - IFNULL(dp_ejecutado.monto_ejecutado, 0),
                0
            )
        ELSE 0
    END AS saldo_91_mas
FROM pedidosjb_pedidos.despacho d
JOIN pedidosjb_pedidos.pedido p ON p.idpedido = d.idpedido
JOIN pedidosjb_pedidos.cliente c ON c.idcliente = p.idcliente
LEFT JOIN pedidosjb_seguridad.usuario u ON u.usuario = p.usuario_creacion
LEFT JOIN (
    SELECT
        dp.iddespacho,
        SUM(dp.monto) AS monto_descuento
    FROM pedidosjb_pedidos.despacho_pago dp
    LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
    WHERE UPPER(TRIM(dp.estado)) IN ('PROGRAMADO', 'EJECUTADO')
      AND UPPER(TRIM(IFNULL(tp.descripcion, ''))) = 'DESCUENTO'
    GROUP BY dp.iddespacho
) dp_descuento ON dp_descuento.iddespacho = d.iddespacho
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
            - IFNULL(dp_descuento.monto_descuento, 0)
        ) - IFNULL(dp_ejecutado.monto_ejecutado, 0),
        0
    ) > 0
  AND IFNULL(TRIM(d.numero_factura), '') <> '';
