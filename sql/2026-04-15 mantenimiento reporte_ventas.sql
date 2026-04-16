INSERT INTO pedidosjb_seguridad.menu
(idmenu, nombre, icono, orden, estado)
VALUES(9, 'Reporte de ventas', 'mdi mdi-file-chart', 5, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(52, 9, 'Ventas por temporada', 'ventas_temporada', 'cargar_opcion', 20, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(247, 52, 'opcion_ventas_temporada', 'SI', 'idpedido', 'idmarca', 'idtemporada', 'ACTIVO');

INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 247, 'NO');

-- pedidosjb_pedidos.view_ventas_temporada source

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_ventas_temporada` AS
select
    `p`.`nopedido` AS `nopedido`,
    `p`.`idpedido` AS `idpedido`,
    `p`.`idcliente` AS `idcliente`,
    `p`.`monto_total` AS `monto_total`,
    concat(`c`.`codigo`, ' - ', `c`.`nombre`) AS `nombre_cliente`,
    `p`.`idmarca` AS `idmarca`,
    `m`.`nombre` AS `nombre_marca`,
    `p`.`idtemporada` AS `idtemporada`,
    `p`.`total_pares` AS `cantidad_pares`,
    count(distinct `pr`.`modelo`) AS `cantidad_modelos`,
    `p`.`estado` AS `estado`
from
    ((((`pedidosjb_pedidos`.`pedido` `p`
join `pedidosjb_pedidos`.`marca` `m` on
    ((`m`.`idmarca` = `p`.`idmarca`)))
left join `pedidosjb_pedidos`.`cliente` `c` on
    ((`c`.`idcliente` = `p`.`idcliente`)))
left join `pedidosjb_pedidos`.`pedido_detalle` `pd` on
    ((`pd`.`idpedido` = `p`.`idpedido`)))
left join `pedidosjb_pedidos`.`producto` `pr` on
    ((`pr`.`idproducto` = `pd`.`idproducto`)))
group by
    `p`.`idpedido`,
    `p`.`idcliente`,
    `c`.`nombre`,
    `p`.`idmarca`,
    `m`.`nombre`,
    `p`.`idtemporada`,
    `p`.`total_pares`,
    `p`.`estado`;