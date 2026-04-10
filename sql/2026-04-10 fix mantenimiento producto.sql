ALTER TABLE pedidosjb_pedidos.producto_precio
ADD COLUMN idset_talla INT NOT NULL,
ADD CONSTRAINT fk_producto_precio_set_talla
FOREIGN KEY (idset_talla)
REFERENCES pedidosjb_pedidos.set_talla (idset_talla)
ON UPDATE CASCADE;

-- pedidosjb_pedidos.view_producto_precio source

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_producto_precio` AS
select
    `pp`.`idproducto_precio` AS `idproducto_precio`,
    `pp`.`idproducto` AS `idproducto`,
    `p`.`modelo` AS `modelo`,
    `pp`.`material` AS `material`,
    `pp`.`idset_talla` AS `idset_talla`,
    `pp`.`precio` AS `precio`,
    `pp`.`estado` AS `estado`,
    `pp`.`fecha_creacion` AS `fecha_creacion`,
    `pp`.`usuario_creacion` AS `usuario_creacion`,
    `pp`.`fecha_modificacion` AS `fecha_modificacion`,
    `pp`.`usuario_modificacion` AS `usuario_modificacion`,
    (case
        when ((`st`.`descripcion` is null)
        or (`st`.`descripcion` = '')) then `st`.`grupo`
        else concat(`st`.`grupo`, ' - ', `st`.`descripcion`)
    end) AS `set_talla`
from
    ((`pedidosjb_pedidos`.`producto_precio` `pp`
join `pedidosjb_pedidos`.`producto` `p` on
    ((`pp`.`idproducto` = `p`.`idproducto`)))
join `pedidosjb_pedidos`.`set_talla` `st` on
    ((`pp`.`idset_talla` = `st`.`idset_talla`)));


-- pedidosjb_pedidos.view_producto source

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_producto` AS
select
    `p`.`idproducto` AS `idproducto`,
    `p`.`idmarca` AS `idmarca`,
    `m`.`nombre` AS `marca`,
    `p`.`idtemporada` AS `idtemporada`,
    `t`.`nombre` AS `temporada`,
    `p`.`linea` AS `linea`,
    `p`.`modelo` AS `modelo`,
    `p`.`idcolor` AS `idcolor`,
    `c`.`nombre` AS `color`,
    `p`.`idcorte` AS `idcorte`,
    `co`.`nombre` AS `corte`,
    `p`.`idtipo_suela` AS `idtipo_suela`,
    `ts`.`nombre` AS `tipo_suela`,
    `p`.`idconcepto` AS `idconcepto`,
    `cp`.`nombre` AS `concepto`,
    `p`.`estado` AS `estado`,
    `p`.`fecha_creacion` AS `fecha_creacion`,
    `p`.`usuario_creacion` AS `usuario_creacion`,
    `p`.`fecha_modificacion` AS `fecha_modificacion`,
    `p`.`usuario_modificacion` AS `usuario_modificacion`,
    group_concat(concat(`st`.`grupo`, ': Q.', format(`pp`.`precio`, 2)) order by `st`.`grupo` ASC separator '; ') AS `precios`
from
    ((((((((`pedidosjb_pedidos`.`producto` `p`
join `pedidosjb_pedidos`.`marca` `m` on
    ((`p`.`idmarca` = `m`.`idmarca`)))
left join `pedidosjb_pedidos`.`temporada` `t` on
    ((`p`.`idtemporada` = `t`.`idtemporada`)))
left join `pedidosjb_pedidos`.`color` `c` on
    ((`p`.`idcolor` = `c`.`idcolor`)))
left join `pedidosjb_pedidos`.`corte` `co` on
    ((`p`.`idcorte` = `co`.`idcorte`)))
left join `pedidosjb_pedidos`.`tipo_suela` `ts` on
    ((`p`.`idtipo_suela` = `ts`.`idtipo_suela`)))
left join `pedidosjb_pedidos`.`concepto` `cp` on
    ((`p`.`idconcepto` = `cp`.`idconcepto`)))
left join `pedidosjb_pedidos`.`producto_precio` `pp` on
    ((`p`.`idproducto` = `pp`.`idproducto`)))
left join `pedidosjb_pedidos`.`set_talla` `st` on
    ((`pp`.`idset_talla` = `st`.`idset_talla`)))
group by
    `p`.`idproducto`;


CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW pedidosjb_pedidos.view_producto_modelo AS
select
    p.idproducto AS idproducto,
    p.modelo AS modelo,
    p.linea AS linea,
    pp.idset_talla AS idset_talla,
    p.idmarca AS idmarca,
    m.nombre AS marca,
    pp.idproducto_precio AS idproducto_precio,
    pp.precio AS precio,
    pp.idset_talla AS idset_talla_precio
from
    ((pedidosjb_pedidos.producto p
join pedidosjb_pedidos.marca m on
    ((m.idmarca = p.idmarca)))
join pedidosjb_pedidos.producto_precio pp on
    (((pp.idproducto = p.idproducto)
        and (pp.estado = 'ACTIVO'))))
where
    (p.estado = 'ACTIVO');