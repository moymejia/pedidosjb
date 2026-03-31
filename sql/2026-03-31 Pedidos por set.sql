use pedidosjb_pedidos

ALTER TABLE pedido DROP FOREIGN KEY fk_pedido_set_talla;
ALTER TABLE pedido DROP INDEX idx_pedido_idset_talla;
ALTER TABLE pedido DROP COLUMN idset_talla;

ALTER TABLE pedido_detalle ADD COLUMN idset_talla INT NOT NULL;
ALTER TABLE pedido_detalle ADD INDEX idx_pedido_detalle_idset_talla (idset_talla);

ALTER TABLE pedido_detalle
ADD CONSTRAINT fk_pedido_detalle_set_talla
FOREIGN KEY (idset_talla)
REFERENCES set_talla(idset_talla)
ON UPDATE CASCADE;


CREATE OR REPLACE VIEW pedidosjb_pedidos.view_pedidos AS
SELECT
    p.idpedido,
    p.idcliente,
    p.idtemporada,
    p.nopedido,
    p.fecha_creacion,
    p.idmarca,
    p.idtransporte,
    p.email,
    p.monto_descuento,
    CONCAT(c.codigo, ' - ', c.nombre) AS cliente,
    c.telefono,
    c.direccion,
    c.nit,
    c.establecimiento,
    c.dias_credito,
    t.nombre AS temporada,
    m.nombre AS marca,
    tr.nombre AS transporte,
    p.estado,
    p.fecha_desde,
    p.fecha_hasta,
    p.observaciones_pedido
FROM pedidosjb_pedidos.pedido p
JOIN pedidosjb_pedidos.cliente c ON p.idcliente = c.idcliente
JOIN pedidosjb_pedidos.temporada t ON p.idtemporada = t.idtemporada
JOIN pedidosjb_pedidos.marca m ON p.idmarca = m.idmarca
LEFT JOIN pedidosjb_pedidos.transporte tr ON p.idtransporte = tr.idtransporte;


CREATE OR REPLACE VIEW pedidosjb_pedidos.view_pedido_detalle AS
SELECT
    pd.idpedido_detalle,
    pd.idpedido,
    pd.imagen,
    pd.idproducto,
    pd.idproducto_precio,
    pd.idset_talla,
    st.grupo,
    st.descripcion AS set_descripcion,
    CONCAT(IFNULL(st.grupo,''), ' - ', IFNULL(st.descripcion,'')) AS set_talla,
    p.modelo AS codigo,
    p.linea AS descripcion,
    c.idcolor,
    c.nombre AS color,
    m.nombre AS marca,
    pp.material,
    pd.precio_venta,
    pd.cantidad,
    pd.subtotal,
    t.idtalla,
    t.numero AS talla
FROM pedidosjb_pedidos.pedido_detalle pd
JOIN pedidosjb_pedidos.producto p ON pd.idproducto = p.idproducto
JOIN pedidosjb_pedidos.color c ON p.idcolor = c.idcolor
JOIN pedidosjb_pedidos.producto_precio pp ON pp.idproducto_precio = pd.idproducto_precio
JOIN pedidosjb_pedidos.talla t ON pd.idtalla = t.idtalla
JOIN pedidosjb_pedidos.marca m ON p.idmarca = m.idmarca
LEFT JOIN pedidosjb_pedidos.set_talla st ON pd.idset_talla = st.idset_talla;