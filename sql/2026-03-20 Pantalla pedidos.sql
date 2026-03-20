INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(8, 2, 'Pedido', 'pedido', 'cargar_opcion', 7, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(26, 8, 'Opcion pedido', 'SI', NULL, NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(27, 8, 'Crear pedido', 'NO', NULL, NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(28, 8, 'Eliminar pedido', 'NO', NULL, NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(29, 8, 'Cerrar Pedido', 'NO', NULL, NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(30, 8, 'Crear detalle pedido', 'NO', NULL, NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(31, 8, 'Eliminar detalle pedido', 'NO', NULL, NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(32, 8, 'Modificar detalle pedido', 'NO', NULL, NULL, NULL, 'ACTIVO');

use pedidosjb_pedidos;

alter table pedidosjb_pedidos.pedido 
drop column fecha_pedido,
add column fecha_desde date not null, 
add column fecha_hasta date not null;

ALTER TABLE pedido_detalle
ADD COLUMN imagen VARCHAR(255) DEFAULT NULL;

ALTER TABLE pedido_detalle
DROP FOREIGN KEY fk_pedido_detalle_pedido;

ALTER TABLE pedido_detalle
ADD CONSTRAINT fk_pedido_detalle_pedido
FOREIGN KEY (idpedido)
REFERENCES pedido(idpedido)
ON DELETE CASCADE
ON UPDATE CASCADE;

ALTER TABLE pedido
ADD COLUMN total_pares INT NOT NULL DEFAULT 0;


CREATE OR REPLACE VIEW pedidosjb_pedidos.view_producto_modelo AS
SELECT 
    p.idproducto,
    p.modelo,
    p.imagen,
    p.linea,
    c.idcolor,
    c.nombre AS color,
    p.idmarca,
    m.nombre AS marca,
    pp.idproducto_precio,
    pp.material,
    pp.precio
FROM producto p
INNER JOIN marca m 
    ON m.idmarca = p.idmarca
INNER JOIN color c
    ON p.idcolor = c.idcolor
LEFT JOIN producto_precio pp 
    ON pp.idproducto = p.idproducto 
    AND pp.estado = 'ACTIVO'
WHERE p.estado = 'ACTIVO';


CREATE OR REPLACE VIEW pedidosjb_pedidos.view_pedidos AS
SELECT 
    p.idpedido,
    p.idcliente,
    p.idtemporada,
    p.idmarca,
    p.idset_talla,
    c.nombre AS cliente,
    t.nombre AS temporada,
    m.nombre AS marca,
    st.descripcion AS set_talla,
    p.estado,
    p.fecha_desde,
    p.fecha_hasta,
    p.observaciones_pedido
FROM pedido p
INNER JOIN cliente c ON p.idcliente = c.idcliente
INNER JOIN temporada t ON p.idtemporada = t.idtemporada
INNER JOIN marca m ON p.idmarca = m.idmarca
INNER JOIN set_talla st ON p.idset_talla = st.idset_talla;

CREATE OR REPLACE VIEW view_set_talla_detalle AS
SELECT 
    st.idset_talla,
    st.descripcion,
    t.idtalla,
    t.numero AS talla,
    std.orden
FROM set_talla st
INNER JOIN set_talla_detalle std 
    ON std.idset_talla = st.idset_talla
INNER JOIN talla t 
    ON t.idtalla = std.idtalla
WHERE st.estado = 'ACTIVO'
AND std.estado = 'ACTIVO';

CREATE OR REPLACE VIEW pedidosjb_pedidos.view_pedido_detalle AS
SELECT
    pd.idpedido_detalle,
    pd.idpedido,
    pd.imagen,
    pd.idproducto,
    pd.idproducto_precio,
    p.modelo AS codigo,
    p.linea AS descripcion,
    c.idcolor,
    c.nombre AS color,
    m.nombre AS marca,
    pp.material,
    pd.precio_venta,
    pd.cantidad,
    pd.subtotal,
    t.idtalla,
    t.numero AS talla
FROM pedido_detalle pd
INNER JOIN producto p 
    ON pd.idproducto = p.idproducto
INNER JOIN color c
    ON p.idcolor = c.idcolor
INNER JOIN producto_precio pp 
    ON pp.idproducto_precio = pd.idproducto_precio
INNER JOIN talla t 
    ON pd.idtalla = t.idtalla
INNER JOIN marca m 
    ON p.idmarca = m.idmarca;


