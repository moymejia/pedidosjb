USE pedidosjb_seguridad;

ALTER TABLE usuario
ADD COLUMN correlativo_usuario VARCHAR(25) NOT NULL;

UPDATE usuario
SET correlativo_usuario = 'JB'
WHERE correlativo_usuario IS NULL OR TRIM(correlativo_usuario) = '';


INSERT INTO pedidosjb_pedidos.temporada
(idtemporada, nombre, fecha_inicio, fecha_fin, estado, fecha_creacion, usuario_creacion, fecha_modificacion, usuario_modificacion)
VALUES(100, 'DESPACHO INMEDIATO', '0000-00-00', '0000-00-00', 'PROTEGIDO', '2026-04-13 16:47:52', 'admin', NULL, NULL);

CREATE OR REPLACE VIEW pedidosjb_pedidos.view_producto_modelo AS
SELECT
    p.idproducto,
    p.modelo,
    p.linea,
    p.idmarca,
    m.nombre AS marca,
    p.idtemporada
FROM pedidosjb_pedidos.producto p
JOIN pedidosjb_pedidos.marca m 
    ON m.idmarca = p.idmarca
WHERE p.estado = 'ACTIVO';