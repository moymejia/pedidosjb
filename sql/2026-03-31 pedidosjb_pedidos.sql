CREATE OR REPLACE VIEW pedidosjb_pedidos.view_producto AS
SELECT 
    p.idproducto,
    p.idmarca,
    m.nombre as marca,
    p.idtemporada,
    t.nombre as temporada,
    p.linea,
    p.modelo,
    p.idset_talla,
    st.grupo AS set_talla,
    p.idcolor,
    c.nombre AS color,
    p.idcorte,
    co.nombre as corte,
    p.idtipo_suela,
    ts.nombre as tipo_suela,
    p.idconcepto,
    cp.nombre as concepto,
    p.estado,
    p.fecha_creacion,
    p.usuario_creacion,
    p.fecha_modificacion,
    p.usuario_modificacion
FROM pedidosjb_pedidos.producto p
INNER JOIN pedidosjb_pedidos.marca m 
    ON p.idmarca = m.idmarca
LEFT JOIN pedidosjb_pedidos.temporada t 
    ON p.idtemporada = t.idtemporada
INNER JOIN pedidosjb_pedidos.set_talla st 
    ON p.idset_talla = st.idset_talla
LEFT JOIN pedidosjb_pedidos.color c 
    ON p.idcolor = c.idcolor
LEFT JOIN pedidosjb_pedidos.corte co 
    ON p.idcorte = co.idcorte
LEFT JOIN pedidosjb_pedidos.tipo_suela ts 
    ON p.idtipo_suela = ts.idtipo_suela
LEFT JOIN pedidosjb_pedidos.concepto cp 
    ON p.idconcepto = cp.idconcepto;

CREATE OR REPLACE VIEW pedidosjb_pedidos.view_producto_precio AS
SELECT 
    pp.idproducto_precio,
    pp.idproducto,
    p.modelo,
    pp.material,
    pp.precio,
    pp.estado,
    pp.fecha_creacion,
    pp.usuario_creacion,
    pp.fecha_modificacion,
    pp.usuario_modificacion
FROM pedidosjb_pedidos.producto_precio pp
INNER JOIN pedidosjb_pedidos.producto p 
    ON pp.idproducto = p.idproducto;