USE pedidosjb_pedidos;

ALTER TABLE cliente_anticipo
ADD COLUMN banco VARCHAR(100) NULL AFTER saldo_disponible;

CREATE OR REPLACE VIEW view_cliente_anticipo AS
SELECT
    ca.idcliente_anticipo,
    ca.idcliente,
    ca.fecha,
    ca.idtipo_pago,
    ca.monto,
    ca.saldo_disponible,
    ca.banco,
    ca.referencia_pago,
    ca.observaciones,
    ca.estado,
    ca.fecha_creacion,
    ca.usuario_creacion,
    ca.fecha_modificacion,
    ca.usuario_modificacion,
    CONCAT(c.codigo, ' - ', c.nombre) AS cliente,
    tp.descripcion AS tipo_pago
FROM cliente_anticipo ca
LEFT JOIN cliente c ON c.idcliente = ca.idcliente
LEFT JOIN tipo_pago tp ON tp.idtipo_pago = ca.idtipo_pago;