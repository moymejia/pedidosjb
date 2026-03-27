CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_set_talla_detalle` AS
select
    `st`.`idset_talla` AS `idset_talla`,
    `st`.`grupo` AS `descripcion`,
    `t`.`idtalla` AS `idtalla`,
    `t`.`numero` AS `talla`,
    `std`.`orden` AS `orden`,
    `t`.`numero` AS `numero`
from
    ((`pedidosjb_pedidos`.`set_talla` `st`
join `pedidosjb_pedidos`.`set_talla_detalle` `std` on
    ((`std`.`idset_talla` = `st`.`idset_talla`)))
join `pedidosjb_pedidos`.`talla` `t` on
    ((`t`.`idtalla` = `std`.`idtalla`)))
where
    ((`st`.`estado` = 'ACTIVO')
        and (`std`.`estado` = 'ACTIVO'));