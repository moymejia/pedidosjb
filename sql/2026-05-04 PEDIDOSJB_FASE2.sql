INSERT INTO pedidosjb_seguridad.accion
(idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(53, 'opcion_comparativo_temporadas', 'SI', NULL, NULL, NULL, 'ACTIVO');

ALTER TABLE pedidosjb_pedidos.cliente_anticipo 
DROP FOREIGN KEY fk_cliente_anticipo_forma_pago,
DROP INDEX idx_cliente_anticipo_idforma_pago,
DROP COLUMN idforma_pago;

INSERT INTO pedidosjb_pedidos.tipo_documento
(idtipo_documento, nombre, correlativo, estado, fecha_creacion, usuario_creacion, fecha_modificacion, usuario_modificacion)
VALUES(1, 'Recibo', 'NO', 'ACTIVO', '2026-05-04 16:36:52', 'admin', NULL, NULL);
INSERT INTO pedidosjb_pedidos.tipo_documento
(idtipo_documento, nombre, correlativo, estado, fecha_creacion, usuario_creacion, fecha_modificacion, usuario_modificacion)
VALUES(2, 'Recuperacion', 'NO', 'ACTIVO', '2026-05-04 16:36:52', 'admin', NULL, NULL);
INSERT INTO pedidosjb_pedidos.tipo_documento
(idtipo_documento, nombre, correlativo, estado, fecha_creacion, usuario_creacion, fecha_modificacion, usuario_modificacion)
VALUES(3, 'Provisional', 'NO', 'ACTIVO', '2026-05-04 16:36:52', 'admin', NULL, NULL);


use pedidosjb_pedidos;

CREATE OR REPLACE VIEW view_ventas_temporada AS
SELECT
    p.idpedido,
    p.nopedido,
    p.idcliente,
    CONCAT(c.codigo, ' - ', c.nombre) AS nombre_cliente,
    p.idmarca,
    m.nombre AS nombre_marca,
    p.idtemporada,
    p.fecha_creacion,
    p.fecha_desde,
    p.fecha_hasta,
    p.estado,
    COALESCE(SUM(pd.subtotal), 0) AS monto_total,
    COALESCE(SUM(pd.cantidad), 0) AS cantidad_pares,
    COUNT(DISTINCT pd.idproducto) AS cantidad_modelos
FROM pedido p
JOIN marca m ON m.idmarca = p.idmarca
LEFT JOIN cliente c ON c.idcliente = p.idcliente
LEFT JOIN pedido_detalle pd ON pd.idpedido = p.idpedido
GROUP BY p.idpedido;