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