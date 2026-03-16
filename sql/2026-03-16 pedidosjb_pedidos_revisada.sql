CREATE SCHEMA IF NOT EXISTS pedidosjb_pedidos
DEFAULT CHARACTER SET utf8mb4
DEFAULT COLLATE utf8mb4_unicode_ci;

USE pedidosjb_pedidos;

SET NAMES utf8mb4;

-- =========================================================
-- TABLAS CATALOGO
-- =========================================================

CREATE TABLE cliente (
    idcliente INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    codigo varchar(20) NOT NULL,
    direccion varchar(400),
    establecimiento varchar(500),
    telefono varchar(60),
    nit VARCHAR(20) NOT NULL,
    limite_credito DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    dias_credito INT NOT NULL DEFAULT 0,
    observaciones VARCHAR(500) DEFAULT NULL,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idcliente),
    UNIQUE KEY uq_cliente_nit (nit),

    KEY idx_cliente_usuario_creacion (usuario_creacion),
    KEY idx_cliente_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_cliente_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_cliente_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE tipo_contacto (
    idtipo_contacto INT NOT NULL AUTO_INCREMENT,
    descripcion VARCHAR(100) NOT NULL,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idtipo_contacto),
    UNIQUE KEY uq_tipo_contacto_descripcion (descripcion),

    KEY idx_tipo_contacto_usuario_creacion (usuario_creacion),
    KEY idx_tipo_contacto_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_tipo_contacto_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_tipo_contacto_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE cliente_contacto (
    idcliente_contacto INT NOT NULL AUTO_INCREMENT,
    idcliente INT NOT NULL,
    idtipo_contacto INT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    telefono VARCHAR(25) DEFAULT NULL,
    correo VARCHAR(200) DEFAULT NULL,
    observaciones VARCHAR(250) DEFAULT NULL,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idcliente_contacto),

    KEY idx_cliente_contacto_idcliente (idcliente),
    KEY idx_cliente_contacto_idtipo_contacto (idtipo_contacto),
    KEY idx_cliente_contacto_usuario_creacion (usuario_creacion),
    KEY idx_cliente_contacto_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_cliente_contacto_cliente
        FOREIGN KEY (idcliente)
        REFERENCES cliente (idcliente)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_cliente_contacto_tipo_contacto
        FOREIGN KEY (idtipo_contacto)
        REFERENCES tipo_contacto (idtipo_contacto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_cliente_contacto_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_cliente_contacto_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE temporada (
    idtemporada INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE DEFAULT NULL,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idtemporada),
    UNIQUE KEY uq_temporada_nombre (nombre),

    KEY idx_temporada_usuario_creacion (usuario_creacion),
    KEY idx_temporada_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_temporada_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_temporada_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE talla (
    idtalla INT NOT NULL AUTO_INCREMENT,
    numero VARCHAR(10) NOT NULL,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idtalla),
    UNIQUE KEY uq_talla_numero (numero),

    KEY idx_talla_usuario_creacion (usuario_creacion),
    KEY idx_talla_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_talla_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_talla_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE set_talla (
    idset_talla INT NOT NULL AUTO_INCREMENT,
    grupo VARCHAR(50) NOT NULL,
    descripcion VARCHAR(100) DEFAULT NULL,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idset_talla),
    UNIQUE KEY uq_set_talla_grupo (grupo),

    KEY idx_set_talla_usuario_creacion (usuario_creacion),
    KEY idx_set_talla_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_set_talla_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_set_talla_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE set_talla_detalle (
    idset_talla_detalle INT NOT NULL AUTO_INCREMENT,
    idset_talla INT NOT NULL,
    idtalla INT NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idset_talla_detalle),
    UNIQUE KEY uq_set_talla_detalle (idset_talla, idtalla),

    KEY idx_set_talla_detalle_idtalla (idtalla),
    KEY idx_set_talla_detalle_usuario_creacion (usuario_creacion),
    KEY idx_set_talla_detalle_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_set_talla_detalle_set_talla
        FOREIGN KEY (idset_talla)
        REFERENCES set_talla (idset_talla)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_set_talla_detalle_talla
        FOREIGN KEY (idtalla)
        REFERENCES talla (idtalla)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_set_talla_detalle_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_set_talla_detalle_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE marca (
    idmarca INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    idset_talla_preferido INT DEFAULT NULL,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idmarca),
    UNIQUE KEY uq_marca_nombre (nombre),

    KEY idx_marca_idset_talla_preferido (idset_talla_preferido),
    KEY idx_marca_usuario_creacion (usuario_creacion),
    KEY idx_marca_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_marca_set_talla_preferido
        FOREIGN KEY (idset_talla_preferido)
        REFERENCES set_talla (idset_talla)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_marca_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_marca_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE tipo_pago (
    idtipo_pago INT NOT NULL AUTO_INCREMENT,
    descripcion VARCHAR(100) NOT NULL,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idtipo_pago),
    UNIQUE KEY uq_tipo_pago_descripcion (descripcion),

    KEY idx_tipo_pago_usuario_creacion (usuario_creacion),
    KEY idx_tipo_pago_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_tipo_pago_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_tipo_pago_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE forma_pago (
    idforma_pago INT NOT NULL AUTO_INCREMENT,
    descripcion VARCHAR(100) NOT NULL,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idforma_pago),
    UNIQUE KEY uq_forma_pago_descripcion (descripcion),

    KEY idx_forma_pago_usuario_creacion (usuario_creacion),
    KEY idx_forma_pago_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_forma_pago_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_forma_pago_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- =========================================================
-- PRODUCTOS
-- =========================================================

CREATE TABLE producto (
    idproducto INT NOT NULL AUTO_INCREMENT,
    idmarca INT NOT NULL,
    linea VARCHAR(100) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    color VARCHAR(100) NOT NULL,
    idset_talla INT NOT NULL,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idproducto),

    KEY idx_producto_idmarca (idmarca),
    KEY idx_producto_idset_talla (idset_talla),
    KEY idx_producto_usuario_creacion (usuario_creacion),
    KEY idx_producto_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_producto_marca
        FOREIGN KEY (idmarca)
        REFERENCES marca (idmarca)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_producto_set_talla
        FOREIGN KEY (idset_talla)
        REFERENCES set_talla (idset_talla)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_producto_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_producto_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE producto_precio (
    idproducto_precio INT NOT NULL AUTO_INCREMENT,
    idproducto INT NOT NULL,
    material VARCHAR(100) NOT NULL,
    precio DECIMAL(14,2) NOT NULL,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idproducto_precio),

    KEY idx_producto_precio_idproducto (idproducto),
    KEY idx_producto_precio_usuario_creacion (usuario_creacion),
    KEY idx_producto_precio_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_producto_precio_producto
        FOREIGN KEY (idproducto)
        REFERENCES producto (idproducto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_producto_precio_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_producto_precio_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- =========================================================
-- PEDIDOS
-- =========================================================

CREATE TABLE pedido (
    idpedido INT NOT NULL AUTO_INCREMENT,
    idcliente INT NOT NULL,
    idtemporada INT NOT NULL,
    idmarca INT NOT NULL,
    idset_talla INT NOT NULL,
    fecha_pedido DATE NOT NULL,
    observaciones_pedido VARCHAR(500) DEFAULT NULL,
    descuento DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    dias_credito INT NOT NULL DEFAULT 0,
    monto_subtotal DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    monto_descuento DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    monto_total DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idpedido),

    KEY idx_pedido_idcliente (idcliente),
    KEY idx_pedido_idtemporada (idtemporada),
    KEY idx_pedido_idmarca (idmarca),
    KEY idx_pedido_idset_talla (idset_talla),
    KEY idx_pedido_usuario_creacion (usuario_creacion),
    KEY idx_pedido_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_pedido_cliente
        FOREIGN KEY (idcliente)
        REFERENCES cliente (idcliente)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_pedido_temporada
        FOREIGN KEY (idtemporada)
        REFERENCES temporada (idtemporada)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_pedido_marca
        FOREIGN KEY (idmarca)
        REFERENCES marca (idmarca)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_pedido_set_talla
        FOREIGN KEY (idset_talla)
        REFERENCES set_talla (idset_talla)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_pedido_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_pedido_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE pedido_detalle (
    idpedido_detalle INT NOT NULL AUTO_INCREMENT,
    idpedido INT NOT NULL,
    idproducto INT NOT NULL,
    idtalla INT NOT NULL,
    cantidad INT NOT NULL,
    precio_lista DECIMAL(14,2) NOT NULL,
    precio_venta DECIMAL(14,2) NOT NULL,
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    cantidad_despachada INT NOT NULL DEFAULT 0,
    cantidad_pendiente INT NOT NULL DEFAULT 0,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idpedido_detalle),

    KEY idx_pedido_detalle_idpedido (idpedido),
    KEY idx_pedido_detalle_idproducto (idproducto),
    KEY idx_pedido_detalle_idtalla (idtalla),
    KEY idx_pedido_detalle_usuario_creacion (usuario_creacion),
    KEY idx_pedido_detalle_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_pedido_detalle_pedido
        FOREIGN KEY (idpedido)
        REFERENCES pedido (idpedido)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_pedido_detalle_producto
        FOREIGN KEY (idproducto)
        REFERENCES producto (idproducto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_pedido_detalle_talla
        FOREIGN KEY (idtalla)
        REFERENCES talla (idtalla)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_pedido_detalle_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_pedido_detalle_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- =========================================================
-- DESPACHOS
-- =========================================================

CREATE TABLE despacho (
    iddespacho INT NOT NULL AUTO_INCREMENT,
    idpedido INT NOT NULL,
    fecha DATE NOT NULL,
    fecha_factura DATE DEFAULT NULL,
    numero_factura VARCHAR(50) DEFAULT NULL,
    observaciones VARCHAR(500) DEFAULT NULL,
    monto_flete DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    monto_otros DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    monto_subtotal DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    monto_total DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    saldo_pendiente DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (iddespacho),
    UNIQUE KEY uq_despacho_numero_factura (numero_factura),

    KEY idx_despacho_idpedido (idpedido),
    KEY idx_despacho_usuario_creacion (usuario_creacion),
    KEY idx_despacho_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_despacho_pedido
        FOREIGN KEY (idpedido)
        REFERENCES pedido (idpedido)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_despacho_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_despacho_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE despacho_detalle (
    iddespacho_detalle INT NOT NULL AUTO_INCREMENT,
    iddespacho INT NOT NULL,
    idpedido_detalle INT NOT NULL,
    cantidad INT NOT NULL,
    precio_venta DECIMAL(14,2) NOT NULL,
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (iddespacho_detalle),
    UNIQUE KEY uq_despacho_detalle (iddespacho, idpedido_detalle),

    KEY idx_despacho_detalle_idpedido_detalle (idpedido_detalle),
    KEY idx_despacho_detalle_usuario_creacion (usuario_creacion),
    KEY idx_despacho_detalle_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_despacho_detalle_despacho
        FOREIGN KEY (iddespacho)
        REFERENCES despacho (iddespacho)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_despacho_detalle_pedido_detalle
        FOREIGN KEY (idpedido_detalle)
        REFERENCES pedido_detalle (idpedido_detalle)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_despacho_detalle_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_despacho_detalle_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- =========================================================
-- PAGOS DE DESPACHO / FACTURA
-- =========================================================

CREATE TABLE despacho_pago (
    iddespacho_pago INT NOT NULL AUTO_INCREMENT,
    iddespacho INT NOT NULL,
    fecha DATE NOT NULL,
    idtipo_pago INT NOT NULL,
    idforma_pago INT NOT NULL,
    monto DECIMAL(14,2) NOT NULL,
    referencia_pago VARCHAR(100) DEFAULT NULL,
    observaciones VARCHAR(500) DEFAULT NULL,
    iddespacho_pago_recupera INT DEFAULT NULL,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (iddespacho_pago),

    KEY idx_despacho_pago_iddespacho (iddespacho),
    KEY idx_despacho_pago_idtipo_pago (idtipo_pago),
    KEY idx_despacho_pago_idforma_pago (idforma_pago),
    KEY idx_despacho_pago_recupera (iddespacho_pago_recupera),
    KEY idx_despacho_pago_usuario_creacion (usuario_creacion),
    KEY idx_despacho_pago_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_despacho_pago_despacho
        FOREIGN KEY (iddespacho)
        REFERENCES despacho (iddespacho)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_despacho_pago_tipo_pago
        FOREIGN KEY (idtipo_pago)
        REFERENCES tipo_pago (idtipo_pago)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_despacho_pago_forma_pago
        FOREIGN KEY (idforma_pago)
        REFERENCES forma_pago (idforma_pago)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_despacho_pago_recupera
        FOREIGN KEY (iddespacho_pago_recupera)
        REFERENCES despacho_pago (iddespacho_pago)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_despacho_pago_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_despacho_pago_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


-- =========================================================
-- ANTICIPOS DE CLIENTE
-- =========================================================

CREATE TABLE cliente_anticipo (
    idcliente_anticipo INT NOT NULL AUTO_INCREMENT,
    idcliente INT NOT NULL,
    fecha DATE NOT NULL,
    idtipo_pago INT NOT NULL,
    idforma_pago INT NOT NULL,
    monto DECIMAL(14,2) NOT NULL,
    saldo_disponible DECIMAL(14,2) NOT NULL,
    referencia_pago VARCHAR(100) DEFAULT NULL,
    observaciones VARCHAR(500) DEFAULT NULL,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idcliente_anticipo),

    KEY idx_cliente_anticipo_idcliente (idcliente),
    KEY idx_cliente_anticipo_idtipo_pago (idtipo_pago),
    KEY idx_cliente_anticipo_idforma_pago (idforma_pago),
    KEY idx_cliente_anticipo_usuario_creacion (usuario_creacion),
    KEY idx_cliente_anticipo_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_cliente_anticipo_cliente
        FOREIGN KEY (idcliente)
        REFERENCES cliente (idcliente)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_cliente_anticipo_tipo_pago
        FOREIGN KEY (idtipo_pago)
        REFERENCES tipo_pago (idtipo_pago)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_cliente_anticipo_forma_pago
        FOREIGN KEY (idforma_pago)
        REFERENCES forma_pago (idforma_pago)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_cliente_anticipo_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_cliente_anticipo_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE cliente_anticipo_aplicacion (
    idcliente_anticipo_aplicacion INT NOT NULL AUTO_INCREMENT,
    idcliente_anticipo INT NOT NULL,
    iddespacho INT NOT NULL,
    fecha DATE NOT NULL,
    monto_aplicado DECIMAL(14,2) NOT NULL,
    observaciones VARCHAR(500) DEFAULT NULL,
    estado VARCHAR(25) NOT NULL,

    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) DEFAULT NULL,

    PRIMARY KEY (idcliente_anticipo_aplicacion),

    KEY idx_cliente_anticipo_aplicacion_idcliente_anticipo (idcliente_anticipo),
    KEY idx_cliente_anticipo_aplicacion_iddespacho (iddespacho),
    KEY idx_cliente_anticipo_aplicacion_usuario_creacion (usuario_creacion),
    KEY idx_cliente_anticipo_aplicacion_usuario_modificacion (usuario_modificacion),

    CONSTRAINT fk_cliente_anticipo_aplicacion_anticipo
        FOREIGN KEY (idcliente_anticipo)
        REFERENCES cliente_anticipo (idcliente_anticipo)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_cliente_anticipo_aplicacion_despacho
        FOREIGN KEY (iddespacho)
        REFERENCES despacho (iddespacho)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_cliente_anticipo_aplicacion_usuario_creacion
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_cliente_anticipo_aplicacion_usuario_modificacion
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;