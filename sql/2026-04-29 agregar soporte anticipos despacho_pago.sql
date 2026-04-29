-- Agregar soporte para pagos con anticipos en despacho_pago
-- Fecha: 2026-04-29
-- Descripción: Permite registrar pagos de despacho usando anticipos del cliente

USE pedidosjb_pedidos;

-- 1) Agregar columna idcliente_anticipo a despacho_pago
ALTER TABLE despacho_pago
    ADD COLUMN idcliente_anticipo INT NULL AFTER idtipo_pago;

-- 2) Agregar constraint para referencia a cliente_anticipo
ALTER TABLE despacho_pago
    ADD CONSTRAINT fk_despacho_pago_cliente_anticipo 
        FOREIGN KEY (idcliente_anticipo)
        REFERENCES pedidosjb_pedidos.cliente_anticipo (idcliente_anticipo)
        ON UPDATE CASCADE
        ON DELETE RESTRICT;

-- 3) Agregar índice para búsquedas rápidas
ALTER TABLE despacho_pago
    ADD KEY idx_despacho_pago_idcliente_anticipo (idcliente_anticipo);

-- 4) Actualizar vistas para incluir información del anticipo
DROP VIEW IF EXISTS pedidosjb_pedidos.view_despacho_pago_detalle;

INSERT INTO pedidosjb_pedidos.tipo_pago
(idtipo_pago, descripcion, estado, fecha_creacion, usuario_creacion, fecha_modificacion, usuario_modificacion, signo)
VALUES(10, 'ANTICIPO', 'PROTEGIDO', '2026-04-29 14:20:29', 'admin', NULL, NULL, 1);

CREATE VIEW pedidosjb_pedidos.view_despacho_pago_detalle AS
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
