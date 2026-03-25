
ALTER TABLE pedidosjb_pedidos.producto
DROP COLUMN color,
ADD COLUMN idtemporada int DEFAULT NULL AFTER idmarca,
ADD COLUMN idcolor int after idset_talla,
ADD COLUMN idcorte int after idcolor,
ADD COLUMN idtipo_suela int after idcorte,
ADD COLUMN idconcepto int after idtipo_suela,
ADD CONSTRAINT fk_idtemporada_producto FOREIGN KEY (`idtemporada`) REFERENCES temporada(`idtemporada`) ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_idcolor_producto FOREIGN KEY (`idcolor`) REFERENCES color(`idcolor`) ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_idcorte_producto FOREIGN KEY (`idcorte`) REFERENCES corte(`idcorte`) ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_idtipo_suela_producto FOREIGN KEY (`idtipo_suela`) REFERENCES tipo_suela(`idtipo_suela`) ON DELETE RESTRICT ON UPDATE CASCADE,
ADD CONSTRAINT fk_idconcepto_producto FOREIGN KEY (`idconcepto`) REFERENCES concepto(`idconcepto`) ON DELETE RESTRICT ON UPDATE CASCADE;


ALTER TABLE pedidosjb_pedidos.producto
MODIFY COLUMN linea VARCHAR(100) DEFAULT NULL;

ALTER TABLE pedidosjb_pedidos.producto_precio
MODIFY COLUMN material VARCHAR(50) DEFAULT NULL;

CREATE OR REPLACE VIEW view_set_talla_detalle AS
SELECT 
    std.idset_talla_detalle,
    std.idset_talla,
    std.idtalla,
    t.numero,
    std.orden,
    std.estado,
    std.fecha_creacion,
    std.usuario_creacion,
    std.fecha_modificacion,
    std.usuario_modificacion
FROM pedidosjb_pedidos.set_talla_detalle std
INNER JOIN pedidosjb_pedidos.talla t ON std.idtalla = t.idtalla;


INSERT INTO pedidosjb_pedidos.marca (nombre,idset_talla_preferido,estado,fecha_creacion,usuario_creacion,fecha_modificacion,usuario_modificacion) VALUES
	 ('Caribu',1,'ACTIVO','2026-03-16 11:17:00','admin',NULL,NULL),
	 ('Pampili',1,'ACTIVO','2026-03-18 12:16:45','admin',NULL,NULL),
	 ('Via marte',18,'ACTIVO','2026-03-18 14:37:48','admin',NULL,'admin'),
	 ('VillaPink',11,'ACTIVO','2026-03-19 09:54:45','admin',NULL,NULL),
	 ('PEGADA',11,'ACTIVO','2026-03-19 10:49:24','admin',NULL,NULL),
	 ('Sando',11,'ACTIVO','2026-03-19 11:30:48','admin',NULL,NULL);

