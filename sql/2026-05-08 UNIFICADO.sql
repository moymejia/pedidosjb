INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(57, 9, 'Liquidacion de ingresos', 'liquidacion_de_ingresos', 'cargar_opcion', 25, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(276, 57, 'Opcion_liquidacion_de_ingresos', 'SI', NULL, NULL, NULL, 'ACTIVO');


ALTER TABLE pedidosjb_pedidos.cliente_anticipo 
DROP FOREIGN KEY fk_cliente_anticipo_forma_pago,
DROP INDEX idx_cliente_anticipo_idforma_pago,
DROP COLUMN idforma_pago;

INSERT INTO pedidosjb_pedidos.tipo_documento
(idtipo_documento, nombre, correlativo, estado, fecha_creacion, usuario_creacion, fecha_modificacion, usuario_modificacion)
VALUES(1, 'Recibo', 'NO', 'ACTIVO', '2026-05-04 16:36:52', 'admin', NULL, NULL);
INSERT INTO pedidosjb_pedidos.tipo_documento
(idtipo_documento, nombre, correlativo, estado, fecha_creacion, usuario_creacion, fecha_modificacion, usuario_modificacion)
VALUES(2, 'Recuperacion', 'NO', 'ACTIVO', '2026-05-04 16:36:52', 'admin', NULL, NULL);
INSERT INTO pedidosjb_pedidos.tipo_documento
(idtipo_documento, nombre, correlativo, estado, fecha_creacion, usuario_creacion, fecha_modificacion, usuario_modificacion)
VALUES(3, 'Provisional', 'NO', 'ACTIVO', '2026-05-04 16:36:52', 'admin', NULL, NULL);


use pedidosjb_pedidos;

CREATE OR REPLACE VIEW pedidosjb_pedidos.view_ventas_temporada AS
SELECT
    p.idpedido,
    p.nopedido,
    p.idcliente,
    CONCAT(c.codigo, ' - ', c.nombre) AS nombre_cliente,
    p.idmarca,
    m.nombre AS nombre_marca,
    p.idtemporada,
    p.fecha_creacion,
    p.fecha_desde,
    p.fecha_hasta,
    p.estado,
    COALESCE(SUM(pd.subtotal), 0) AS monto_total,
    COALESCE(SUM(pd.cantidad), 0) AS cantidad_pares,
    COUNT(DISTINCT pd.idproducto) AS cantidad_modelos
FROM pedido p
JOIN marca m ON m.idmarca = p.idmarca
LEFT JOIN cliente c ON c.idcliente = p.idcliente
LEFT JOIN pedido_detalle pd ON pd.idpedido = p.idpedido
GROUP BY p.idpedido;

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_pedido_comparativo` AS
SELECT
    p.idpedido AS idpedido,
    p.idcliente AS idcliente,
    c.nombre AS cliente_nombre,
    p.idmarca AS idmarca,
    m.nombre AS marca_nombre,
    p.idtemporada AS idtemporada,
    COALESCE(SUM(pd.cantidad), 0) AS total_pares,
    COALESCE(SUM(pd.subtotal), 0) AS monto_total,
    p.estado AS estado
FROM pedidosjb_pedidos.pedido p
LEFT JOIN pedidosjb_pedidos.cliente c ON c.idcliente = p.idcliente
LEFT JOIN pedidosjb_pedidos.marca m ON m.idmarca = p.idmarca
LEFT JOIN pedidosjb_pedidos.pedido_detalle pd ON pd.idpedido = p.idpedido
GROUP BY
    p.idpedido,
    p.idcliente,
    c.nombre,
    p.idmarca,
    m.nombre,
    p.idtemporada,
    p.estado;


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
    IFNULL(`d`.`monto_flete`, 0) AS `monto_flete`,
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
    `td`.`nombre` AS `tipo_documento`,
    `dp`.`estado` AS `estado_pago_individual`
from
    (((((`pedidosjb_pedidos`.`despacho` `d`
join `pedidosjb_pedidos`.`pedido` `p` on
    ((`d`.`idpedido` = `p`.`idpedido`)))
join `pedidosjb_pedidos`.`cliente` `c` on
    ((`p`.`idcliente` = `c`.`idcliente`)))
left join `pedidosjb_pedidos`.`despacho_pago` `dp` on
    ((`d`.`iddespacho` = `dp`.`iddespacho`)))
left join `pedidosjb_pedidos`.`tipo_pago` `tp` on
    ((`dp`.`idtipo_pago` = `tp`.`idtipo_pago`)))
left join `pedidosjb_pedidos`.`tipo_documento` `td` on
    ((`dp`.`idtipo_documento` = `td`.`idtipo_documento`)))
where
    (`d`.`fecha_factura` is not null);



