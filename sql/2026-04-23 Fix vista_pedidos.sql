CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_producto_precio` AS
select
    pp.idproducto_precio,
    pp.idproducto,
    p.modelo,
    p.idmarca,
    p.idtemporada,
    pp.idset_talla,
    pp.precio,
    pp.estado,
    pp.fecha_creacion,
    pp.usuario_creacion,
    pp.fecha_modificacion,
    pp.usuario_modificacion,
    (case
        when ((st.descripcion is null) or (st.descripcion = '')) then st.grupo
        else concat(st.grupo, ' - ', st.descripcion)
    end) AS set_talla
from pedidosjb_pedidos.producto_precio pp
join pedidosjb_pedidos.producto p 
    on pp.idproducto = p.idproducto
join pedidosjb_pedidos.set_talla st 
    on pp.idset_talla = st.idset_talla;