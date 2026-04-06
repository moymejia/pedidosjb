CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_producto_modelo` AS
SELECT
    p.idproducto,
    p.modelo,
    p.linea,
    p.idset_talla,
    c.idcolor,
    c.nombre AS color,
    p.idmarca,
    m.nombre AS marca,
    pp.idproducto_precio,
    pp.material,
    pp.precio,
    pp.estado AS estado_material
FROM pedidosjb_pedidos.producto p
JOIN pedidosjb_pedidos.marca m 
    ON m.idmarca = p.idmarca
JOIN pedidosjb_pedidos.color c 
    ON p.idcolor = c.idcolor
LEFT JOIN pedidosjb_pedidos.producto_precio pp 
    ON pp.idproducto = p.idproducto
WHERE p.estado = 'ACTIVO';