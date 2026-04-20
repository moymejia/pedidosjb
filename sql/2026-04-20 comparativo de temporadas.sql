INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(53, 9, 'Comparativo por temporadas', 'comparativo_temporadas', 'cargar_opcion', 20, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(249, 53, 'opcion_comparativo_temporadas', 'SI', NULL, NULL, NULL, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, nombre, indFavorito)
VALUES(1, 249, NULL, 'NO');

-- pedidosjb_pedidos.view_pedido_comparativo source

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_pedido_comparativo` AS
select
    `p`.`idpedido` AS `idpedido`,
    `p`.`idcliente` AS `idcliente`,
    `c`.`nombre` AS `cliente_nombre`,
    `p`.`idmarca` AS `idmarca`,
    `m`.`nombre` AS `marca_nombre`,
    `p`.`idtemporada` AS `idtemporada`,
    `p`.`total_pares` AS `total_pares`,
    `p`.`monto_total` AS `monto_total`,
    `p`.`estado` AS `estado`
from
    ((`pedidosjb_pedidos`.`pedido` `p`
left join `pedidosjb_pedidos`.`cliente` `c` on
    ((`c`.`idcliente` = `p`.`idcliente`)))
left join `pedidosjb_pedidos`.`marca` `m` on
    ((`m`.`idmarca` = `p`.`idmarca`)));