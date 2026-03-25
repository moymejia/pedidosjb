-- =========================
-- SEGURIDAD
-- =========================

INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(9, 2, 'Pedido', 'pedido', 'cargar_opcion', 7, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES
(40, 9, 'Opcion pedido', 'SI', NULL, NULL, NULL, 'ACTIVO'),
(41, 9, 'Crear pedido', 'NO', NULL, NULL, NULL, 'ACTIVO'),
(42, 9, 'Eliminar pedido', 'NO', NULL, NULL, NULL, 'ACTIVO'),
(43, 9, 'Cerrar Pedido', 'NO', NULL, NULL, NULL, 'ACTIVO'),
(44, 9, 'Crear detalle pedido', 'NO', NULL, NULL, NULL, 'ACTIVO'),
(45, 9, 'Eliminar detalle pedido', 'NO', NULL, NULL, NULL, 'ACTIVO'),
(46, 9, 'Modificar detalle pedido', 'NO', NULL, NULL, NULL, 'ACTIVO'),
(47, 9, 'Imprimir pedido', 'NO', NULL, NULL, NULL, 'ACTIVO');

-- =========================
-- USAR DB
-- =========================

USE pedidosjb_pedidos;

-- =========================
-- CREAR TABLAS NUEVAS (ANTES DE FKs)
-- =========================

CREATE TABLE transporte (
    idtransporte INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    estado VARCHAR(25) NOT NULL DEFAULT 'ACTIVO',
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,
    PRIMARY KEY (idtransporte),
    FOREIGN KEY (usuario_creacion) REFERENCES pedidosjb_seguridad.usuario(usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (usuario_modificacion) REFERENCES pedidosjb_seguridad.usuario(usuario) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE color (
    idcolor INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    estado VARCHAR(25) NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
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

-- =========================
-- ALTERS SIN FK
-- =========================

ALTER TABLE pedido 
DROP COLUMN fecha_pedido,
ADD COLUMN fecha_desde DATE NOT NULL, 
ADD COLUMN fecha_hasta DATE NOT NULL,
ADD COLUMN total_pares INT NOT NULL DEFAULT 0,
ADD COLUMN email VARCHAR(150) NULL,
ADD COLUMN idtransporte INT;

ALTER TABLE pedido_detalle
ADD COLUMN imagen VARCHAR(255) DEFAULT NULL,
ADD COLUMN idproducto_precio INT;

-- =========================
-- FOREIGN KEYS
-- =========================

ALTER TABLE pedido_detalle
DROP FOREIGN KEY fk_pedido_detalle_pedido;

ALTER TABLE pedido_detalle
ADD CONSTRAINT fk_pedido_detalle_pedido
FOREIGN KEY (idpedido)
REFERENCES pedido(idpedido)
ON DELETE CASCADE
ON UPDATE CASCADE;

ALTER TABLE pedido_detalle
ADD CONSTRAINT fk_pedido_detalle_producto_precio
FOREIGN KEY (idproducto_precio)
REFERENCES producto_precio(idproducto_precio)
ON UPDATE CASCADE
ON DELETE RESTRICT;

ALTER TABLE pedido
ADD CONSTRAINT fk_pedido_transporte
FOREIGN KEY (idtransporte)
REFERENCES transporte(idtransporte)
ON UPDATE CASCADE
ON DELETE SET NULL;

-- =========================
-- VISTAS (AL FINAL)
-- =========================

CREATE OR REPLACE VIEW pedidosjb_pedidos.view_producto_modelo AS
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

CREATE OR REPLACE VIEW pedidosjb_pedidos.view_pedidos AS
SELECT 
    p.idpedido,
    p.idcliente,
    p.idtemporada,
    p.fecha_creacion,
    p.idmarca,
    p.idset_talla,
    p.idtransporte,
    p.email,
    p.monto_descuento,
    c.nombre AS cliente,
    c.telefono,
    c.direccion,
    c.nit,
    c.establecimiento,
    c.dias_credito,
    t.nombre AS temporada,
    m.nombre AS marca,
    st.descripcion AS set_talla,
    tr.nombre AS transporte,
    p.estado,
    p.fecha_desde,
    p.fecha_hasta,
    p.observaciones_pedido
FROM pedido p
INNER JOIN cliente c ON p.idcliente = c.idcliente
INNER JOIN temporada t ON p.idtemporada = t.idtemporada
INNER JOIN marca m ON p.idmarca = m.idmarca
INNER JOIN set_talla st ON p.idset_talla = st.idset_talla
LEFT JOIN transporte tr ON p.idtransporte = tr.idtransporte;