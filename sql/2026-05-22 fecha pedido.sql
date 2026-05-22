-- 2026-05-22
-- Agrega fecha_pedido y migra pedidos existentes tomando fecha_creacion

ALTER TABLE pedido
ADD COLUMN fecha_pedido DATE;

UPDATE pedido
SET fecha_pedido = DATE(fecha_creacion)
WHERE fecha_pedido IS NULL OR fecha_pedido = '0000-00-00';

ALTER TABLE pedido
MODIFY COLUMN fecha_pedido DATE NOT NULL;

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_pedidos` AS
select
	`p`.`idpedido` AS `idpedido`,
	`p`.`idcliente` AS `idcliente`,
	`p`.`idtemporada` AS `idtemporada`,
	`p`.`nopedido` AS `nopedido`,
	`p`.`fecha_creacion` AS `fecha_creacion`,
	`p`.`fecha_pedido` AS `fecha_pedido`,
	`p`.`idmarca` AS `idmarca`,
	`p`.`idtransporte` AS `idtransporte`,
	`p`.`email` AS `email`,
	`p`.`monto_descuento` AS `monto_descuento`,
	concat(`c`.`codigo`, ' - ', `c`.`nombre`) AS `cliente`,
	`c`.`telefono` AS `telefono`,
	`c`.`direccion` AS `direccion`,
	`c`.`nit` AS `nit`,
	`c`.`establecimiento` AS `establecimiento`,
	`c`.`dias_credito` AS `dias_credito`,
	`t`.`nombre` AS `temporada`,
	`m`.`nombre` AS `marca`,
	`m`.`descripcion` AS `descripcion_marca`,
	`tr`.`nombre` AS `transporte`,
	`p`.`estado` AS `estado`,
	`p`.`fecha_desde` AS `fecha_desde`,
	`p`.`fecha_hasta` AS `fecha_hasta`,
	`p`.`observaciones_pedido` AS `observaciones_pedido`
from
	((((`pedidosjb_pedidos`.`pedido` `p`
join `pedidosjb_pedidos`.`cliente` `c` on
	((`p`.`idcliente` = `c`.`idcliente`)))
join `pedidosjb_pedidos`.`temporada` `t` on
	((`p`.`idtemporada` = `t`.`idtemporada`)))
join `pedidosjb_pedidos`.`marca` `m` on
	((`p`.`idmarca` = `m`.`idmarca`)))
left join `pedidosjb_pedidos`.`transporte` `tr` on
	((`p`.`idtransporte` = `tr`.`idtransporte`)));
