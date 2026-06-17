-- Campo No. Recuperado para documentos de recuperacion
-- Fecha: 2026-06-16
-- EJECUTAR EN BASE DE DATOS

ALTER TABLE pedidosjb_pedidos.despacho_pago
ADD COLUMN numero_recuperado VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
AFTER correlativo_documento;

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
    dp.numero_recuperado,
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

CREATE OR REPLACE VIEW pedidosjb_pedidos.view_despacho_pago_recuperacion AS
SELECT
    dp.iddespacho_pago,
    dp.iddespacho_pago_recupera,
    dp.correlativo_documento,
    dp.numero_recuperado,
    dp.monto,
    dp.estado,
    UPPER(TRIM(IFNULL(td.nombre, ''))) AS tipo_documento,
    UPPER(TRIM(IFNULL(tp.descripcion, ''))) AS tipo_pago
FROM pedidosjb_pedidos.despacho_pago dp
LEFT JOIN pedidosjb_pedidos.tipo_documento td ON td.idtipo_documento = dp.idtipo_documento
LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago;

CREATE OR REPLACE VIEW pedidosjb_pedidos.view_estado_cuenta_despacho_detallado AS
SELECT
    d.iddespacho AS iddespacho,
    p.idcliente AS idcliente,
    CONCAT(c.codigo, ' - ', c.nombre) AS nombre_cliente,
    p.idtemporada AS idtemporada,
    d.numero_factura AS numero_factura,
    d.fecha_factura AS fecha_factura,
    d.monto_total AS monto_total,
    IFNULL(d.monto_flete, 0) AS monto_flete,
    dp.monto AS monto_pago,
    dp.correlativo_documento AS correlativo_documento,
    dp.numero_recuperado AS numero_recuperado,
    (
        d.monto_total - IFNULL(
            (
                SELECT SUM(dp1.monto)
                FROM pedidosjb_pedidos.despacho_pago dp1
                WHERE dp1.iddespacho = d.iddespacho
                  AND dp1.estado = 'EJECUTADO'
            ),
            0
        )
    ) AS saldo_pendiente,
    (d.fecha_factura + INTERVAL p.dias_credito DAY) AS fecha_vencimiento,
    (TO_DAYS((d.fecha_factura + INTERVAL p.dias_credito DAY)) - TO_DAYS(CURDATE())) AS proximidad,
    CASE
        WHEN (TO_DAYS((d.fecha_factura + INTERVAL p.dias_credito DAY)) - TO_DAYS(CURDATE())) <= 0 THEN 'Vencido'
        WHEN (TO_DAYS((d.fecha_factura + INTERVAL p.dias_credito DAY)) - TO_DAYS(CURDATE())) BETWEEN 1 AND 30 THEN 'A 30'
        WHEN (TO_DAYS((d.fecha_factura + INTERVAL p.dias_credito DAY)) - TO_DAYS(CURDATE())) BETWEEN 31 AND 60 THEN 'A 60'
        WHEN (TO_DAYS((d.fecha_factura + INTERVAL p.dias_credito DAY)) - TO_DAYS(CURDATE())) BETWEEN 61 AND 90 THEN 'A 90'
        ELSE '90 +'
    END AS estado,
    CASE
        WHEN IFNULL(
            (
                SELECT SUM(dp1.monto)
                FROM pedidosjb_pedidos.despacho_pago dp1
                WHERE dp1.iddespacho = d.iddespacho
                  AND dp1.estado = 'EJECUTADO'
            ),
            0
        ) = 0 THEN 'PENDIENTE'
        WHEN IFNULL(
            (
                SELECT SUM(dp1.monto)
                FROM pedidosjb_pedidos.despacho_pago dp1
                WHERE dp1.iddespacho = d.iddespacho
                  AND dp1.estado = 'EJECUTADO'
            ),
            0
        ) < d.monto_total THEN 'PARCIAL'
        ELSE 'PAGADO'
    END AS estado_pago,
    dp.fecha AS fecha_pago,
    tp.descripcion AS tipo_pago,
    td.nombre AS tipo_documento,
    dp.estado AS estado_pago_individual
FROM pedidosjb_pedidos.despacho d
JOIN pedidosjb_pedidos.pedido p ON d.idpedido = p.idpedido
JOIN pedidosjb_pedidos.cliente c ON p.idcliente = c.idcliente
LEFT JOIN pedidosjb_pedidos.despacho_pago dp ON d.iddespacho = dp.iddespacho
LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON dp.idtipo_pago = tp.idtipo_pago
LEFT JOIN pedidosjb_pedidos.tipo_documento td ON dp.idtipo_documento = td.idtipo_documento
WHERE d.fecha_factura IS NOT NULL;
