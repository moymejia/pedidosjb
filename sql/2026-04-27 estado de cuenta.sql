INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(54, 9, 'Estado de cuenta', 'despacho', 'cargar_estado_de_cuenta', 21, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(250, 54, 'opcion_estado_de_cuenta', 'SI', NULL, NULL, NULL, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, nombre, indFavorito)
VALUES(1, 250, NULL, 'NO');

-- pedidosjb_pedidos.view_estado_cuenta_despacho source

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_estado_cuenta_despacho` AS
select
    `d`.`iddespacho` AS `iddespacho`,
    `p`.`idcliente` AS `idcliente`,
    concat(`c`.`codigo`, ' - ', `c`.`nombre`) AS `nombre_cliente`,
    `p`.`idtemporada` AS `idtemporada`,
    `d`.`fecha_factura` AS `fecha_factura`,
    `d`.`monto_total` AS `monto_total`,
    ifnull(sum((case when (`dp`.`estado` = 'EJECUTADO') then `dp`.`monto` else 0 end)), 0) AS `monto_total_pagado`,
    ifnull(sum((case when (`dp`.`estado` = 'PROGRAMADO') then `dp`.`monto` else 0 end)), 0) AS `monto_programado`,
    (`d`.`monto_total` - ifnull(sum((case when (`dp`.`estado` = 'EJECUTADO') then `dp`.`monto` else 0 end)), 0)) AS `saldo_pendiente`,
    (`d`.`fecha_factura` + interval `p`.`dias_credito` day) AS `fecha_vencimiento`,
    (to_days((`d`.`fecha_factura` + interval `p`.`dias_credito` day)) - to_days(curdate())) AS `proximidad`,
    (case
        when ((to_days((`d`.`fecha_factura` + interval `p`.`dias_credito` day)) - to_days(curdate())) <= 0) then 'Vencido'
        when ((to_days((`d`.`fecha_factura` + interval `p`.`dias_credito` day)) - to_days(curdate())) between 1 and 30) then 'A 30'
        when ((to_days((`d`.`fecha_factura` + interval `p`.`dias_credito` day)) - to_days(curdate())) between 31 and 60) then 'A 60'
        when ((to_days((`d`.`fecha_factura` + interval `p`.`dias_credito` day)) - to_days(curdate())) between 61 and 90) then 'A 90'
        else '90 +'
    end) AS `estado`
from
    (((`pedidosjb_pedidos`.`despacho` `d`
join `pedidosjb_pedidos`.`pedido` `p` on
    ((`d`.`idpedido` = `p`.`idpedido`)))
join `pedidosjb_pedidos`.`cliente` `c` on
    ((`p`.`idcliente` = `c`.`idcliente`)))
left join `pedidosjb_pedidos`.`despacho_pago` `dp` on
    ((`d`.`iddespacho` = `dp`.`iddespacho`)))
where
    (`d`.`fecha_factura` is not null)
group by
    `d`.`iddespacho`,
    `p`.`idcliente`,
    `c`.`nombre`,
    `p`.`idtemporada`,
    `d`.`fecha_factura`,
    `d`.`monto_total`,
    `p`.`dias_credito`;


    -- pedidosjb_pedidos.view_estado_cuenta_despacho_detallado source

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_estado_cuenta_despacho_detallado` AS
select
    `d`.`iddespacho` AS `iddespacho`,
    `p`.`idcliente` AS `idcliente`,
    concat(`c`.`codigo`, ' - ', `c`.`nombre`) AS `nombre_cliente`,
    `p`.`idtemporada` AS `idtemporada`,
    `d`.`numero_factura` AS `numero_factura`,
    `d`.`fecha_factura` AS `fecha_factura`,
    `d`.`monto_total` AS `monto_total`,
    `dp`.`monto` AS `monto_pago`,
    (`d`.`monto_total` - ifnull((select sum(`dp1`.`monto`) from `pedidosjb_pedidos`.`despacho_pago` `dp1` where ((`dp1`.`iddespacho` = `d`.`iddespacho`) and (`dp1`.`estado` = 'EJECUTADO'))), 0)) AS `saldo_pendiente`,
    (`d`.`fecha_factura` + interval `p`.`dias_credito` day) AS `fecha_vencimiento`,
    (to_days((`d`.`fecha_factura` + interval `p`.`dias_credito` day)) - to_days(curdate())) AS `proximidad`,
    (case
        when ((to_days((`d`.`fecha_factura` + interval `p`.`dias_credito` day)) - to_days(curdate())) <= 0) then 'Vencido'
        when ((to_days((`d`.`fecha_factura` + interval `p`.`dias_credito` day)) - to_days(curdate())) between 1 and 30) then 'A 30'
        when ((to_days((`d`.`fecha_factura` + interval `p`.`dias_credito` day)) - to_days(curdate())) between 31 and 60) then 'A 60'
        when ((to_days((`d`.`fecha_factura` + interval `p`.`dias_credito` day)) - to_days(curdate())) between 61 and 90) then 'A 90'
        else '90 +'
    end) AS `estado`,
    (case
        when (ifnull((select sum(`dp1`.`monto`) from `pedidosjb_pedidos`.`despacho_pago` `dp1` where ((`dp1`.`iddespacho` = `d`.`iddespacho`) and (`dp1`.`estado` = 'EJECUTADO'))), 0) = 0) then 'PENDIENTE'
        when (ifnull((select sum(`dp1`.`monto`) from `pedidosjb_pedidos`.`despacho_pago` `dp1` where ((`dp1`.`iddespacho` = `d`.`iddespacho`) and (`dp1`.`estado` = 'EJECUTADO'))), 0) < `d`.`monto_total`) then 'PARCIAL'
        else 'PAGADO'
    end) AS `estado_pago`,
    `dp`.`fecha` AS `fecha_pago`,
    `tp`.`descripcion` AS `tipo_pago`,
    `dp`.`estado` AS `estado_pago_individual`
from
    ((((`pedidosjb_pedidos`.`despacho` `d`
join `pedidosjb_pedidos`.`pedido` `p` on
    ((`d`.`idpedido` = `p`.`idpedido`)))
join `pedidosjb_pedidos`.`cliente` `c` on
    ((`p`.`idcliente` = `c`.`idcliente`)))
left join `pedidosjb_pedidos`.`despacho_pago` `dp` on
    ((`d`.`iddespacho` = `dp`.`iddespacho`)))
left join `pedidosjb_pedidos`.`tipo_pago` `tp` on
    ((`dp`.`idtipo_pago` = `tp`.`idtipo_pago`)))
where
    (`d`.`fecha_factura` is not null);