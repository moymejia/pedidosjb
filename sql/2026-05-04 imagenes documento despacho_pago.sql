USE pedidosjb_pedidos;

ALTER TABLE despacho_pago
    ADD COLUMN imagen VARCHAR(255) NULL AFTER observaciones;

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
