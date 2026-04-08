ALTER TABLE pedidosjb_pedidos.producto_precio
DROP COLUMN material,
ADD COLUMN idset_talla int NOT NULL,
ADD CONSTRAINT fk_producto_precio_set_talla FOREIGN KEY (idset_talla) REFERENCES pedidosjb_pedidos.set_talla(idset_talla);