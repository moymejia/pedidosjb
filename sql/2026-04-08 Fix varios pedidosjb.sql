
ALTER TABLE cliente DROP INDEX uq_cliente_nit;

ALTER TABLE talla
MODIFY COLUMN numero DECIMAL(4,1) NOT NULL;


CREATE OR REPLACE VIEW view_producto_modelo AS
SELECT
    p.idproducto,
    p.modelo,
    p.linea,
    p.idset_talla,
    c.idcolor,
    c.nombre AS color,
    p.idmarca,
    m.nombre AS marca,
    pp.idproducto_precio,
    pp.material,
    pp.precio,
    pp.estado AS estado_material
FROM producto p
JOIN marca m ON m.idmarca = p.idmarca
JOIN color c ON p.idcolor = c.idcolor
JOIN producto_precio pp 
    ON pp.idproducto = p.idproducto
    AND pp.estado = 'ACTIVO' 
WHERE p.estado = 'ACTIVO';