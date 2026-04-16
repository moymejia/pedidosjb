ALTER TABLE pedidosjb_pedidos.marca
DROP FOREIGN KEY fk_marca_set_talla_preferido;

Alter table pedidosjb_pedidos.marca
drop column idset_talla_preferido;


ALTER TABLE pedidosjb_pedidos.producto
DROP FOREIGN KEY fk_producto_set_talla;

ALTER TABLE pedidosjb_pedidos.producto
DROP INDEX idx_producto_idset_talla;

ALTER TABLE pedidosjb_pedidos.producto
DROP COLUMN idset_talla;

REPLACE INTO pedidosjb_pedidos.temporada
(idtemporada, nombre, fecha_inicio, fecha_fin, estado, fecha_creacion, usuario_creacion, fecha_modificacion, usuario_modificacion)
VALUES
(100, 'DESPACHO INMEDIATO', '0001-01-01', '0001-01-01', 'PROTEGIDO', '2026-04-13 16:47:52', 'admin', NULL, NULL);

CREATE OR REPLACE VIEW pedidosjb_pedidos.view_producto_precio AS
SELECT
    pp.idproducto_precio,
    pp.idproducto,
    p.modelo,
    p.idmarca,  
    pp.idset_talla,
    pp.precio,
    pp.estado,
    pp.fecha_creacion,
    pp.usuario_creacion,
    pp.fecha_modificacion,
    pp.usuario_modificacion,
    (CASE
        WHEN (st.descripcion IS NULL OR st.descripcion = '')
        THEN st.grupo
        ELSE CONCAT(st.grupo, ' - ', st.descripcion)
    END) AS set_talla
FROM pedidosjb_pedidos.producto_precio pp
JOIN pedidosjb_pedidos.producto p 
    ON pp.idproducto = p.idproducto
JOIN pedidosjb_pedidos.set_talla st 
    ON pp.idset_talla = st.idset_talla;