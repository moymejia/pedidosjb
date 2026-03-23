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

ALTER TABLE pedido
ADD COLUMN email VARCHAR(150) NULL;

CREATE TABLE transporte (
  idtransporte INT NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  estado VARCHAR(25) NOT NULL DEFAULT 'ACTIVO',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  usuario_creacion VARCHAR(25) NOT NULL,
  fecha_modificacion DATETIME DEFAULT NULL,
  usuario_modificacion VARCHAR(25) DEFAULT NULL,
  PRIMARY KEY (idtransporte)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE color (
  idcolor INT NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(50) NOT NULL,
  estado VARCHAR(25) NOT NULL,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  usuario_creacion VARCHAR(25) NOT NULL,
  fecha_modificacion DATETIME DEFAULT NULL,
  usuario_modificacion VARCHAR(25) DEFAULT NULL,
  PRIMARY KEY (idcolor),
  UNIQUE KEY uq_color_nombre (nombre),
  KEY idx_color_usuario_creacion (usuario_creacion),
  KEY idx_color_usuario_modificacion (usuario_modificacion),
  CONSTRAINT fk_color_usuario_creacion 
    FOREIGN KEY (usuario_creacion) 
    REFERENCES pedidosjb_seguridad.usuario (usuario)
    ON DELETE RESTRICT 
    ON UPDATE CASCADE,
  CONSTRAINT fk_color_usuario_modificacion 
    FOREIGN KEY (usuario_modificacion) 
    REFERENCES pedidosjb_seguridad.usuario (usuario)
    ON DELETE RESTRICT 
    ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

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

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_pedidos` AS
select
    `p`.`idpedido` AS `idpedido`,
    `p`.`idcliente` AS `idcliente`,
    `p`.`idtemporada` AS `idtemporada`,
    `p`.`idmarca` AS `idmarca`,
    `p`.`idset_talla` AS `idset_talla`,
    `p`.`idtransporte` AS `idtransporte`,
    `p`.`email` AS `email`,
    `p`.`monto_descuento` AS `monto_descuento`,
    `c`.`nombre` AS `cliente`,
    `t`.`nombre` AS `temporada`,
    `m`.`nombre` AS `marca`,
    `st`.`descripcion` AS `set_talla`,
    `tr`.`nombre` AS `transporte`,
    `p`.`estado` AS `estado`,
    `p`.`fecha_desde` AS `fecha_desde`,
    `p`.`fecha_hasta` AS `fecha_hasta`,
    `p`.`observaciones_pedido` AS `observaciones_pedido`
from
    (((((`pedidosjb_pedidos`.`pedido` `p`
join `pedidosjb_pedidos`.`cliente` `c` on
    ((`p`.`idcliente` = `c`.`idcliente`)))
join `pedidosjb_pedidos`.`temporada` `t` on
    ((`p`.`idtemporada` = `t`.`idtemporada`)))
join `pedidosjb_pedidos`.`marca` `m` on
    ((`p`.`idmarca` = `m`.`idmarca`)))
join `pedidosjb_pedidos`.`set_talla` `st` on
    ((`p`.`idset_talla` = `st`.`idset_talla`)))
left join `pedidosjb_pedidos`.`transporte` `tr` on
    ((`p`.`idtransporte` = `tr`.`idtransporte`)));


