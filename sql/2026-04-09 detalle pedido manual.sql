USE pedidosjb_pedidos;

ALTER TABLE pedido_detalle
    MODIFY COLUMN idproducto_precio INT NULL,
    ADD COLUMN color_texto VARCHAR(100) NULL AFTER idproducto_precio,
    ADD COLUMN material_texto VARCHAR(100) NULL AFTER color_texto;


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
    COALESCE(NULLIF(pd.color_texto, ''), c.nombre) AS color,
    COALESCE(m.nombre, marca_pedido.nombre) AS marca,
    NULLIF(pd.material_texto, '') AS material,
    pd.precio_venta,
    pd.cantidad,
    pd.subtotal,
    t.idtalla,
    t.numero AS talla
FROM pedidosjb_pedidos.pedido_detalle pd
LEFT JOIN pedidosjb_pedidos.producto p
    ON pd.idproducto = p.idproducto
LEFT JOIN pedidosjb_pedidos.color c
    ON p.idcolor = c.idcolor
JOIN pedidosjb_pedidos.talla t
    ON pd.idtalla = t.idtalla
LEFT JOIN pedidosjb_pedidos.marca m
    ON p.idmarca = m.idmarca
LEFT JOIN pedidosjb_pedidos.pedido pe
    ON pd.idpedido = pe.idpedido
LEFT JOIN pedidosjb_pedidos.marca marca_pedido
    ON pe.idmarca = marca_pedido.idmarca
LEFT JOIN pedidosjb_pedidos.set_talla st
    ON pd.idset_talla = st.idset_talla;
