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