

USE pedidosjb_pedidos;

-- 1) Tipo pago: ahora define el signo del movimiento
ALTER TABLE tipo_pago
    ADD COLUMN  signo int NOT NULL;


-- 2) Catalogo de tipo de documento
CREATE TABLE tipo_documento (
    idtipo_documento INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    correlativo VARCHAR(50) NOT NULL,
    estado VARCHAR(25) NOT NULL DEFAULT 'ACTIVO',
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_creacion VARCHAR(25) 
        CHARACTER SET utf8mb4 
        COLLATE utf8mb4_unicode_ci NOT NULL,
    fecha_modificacion DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    usuario_modificacion VARCHAR(25) 
        CHARACTER SET utf8mb4 
        COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    PRIMARY KEY (idtipo_documento),
    UNIQUE KEY uq_tipo_documento_nombre (nombre),
    KEY idx_tipo_documento_usuario_creacion (usuario_creacion),
    KEY idx_tipo_documento_usuario_modificacion (usuario_modificacion),
    CONSTRAINT fk_tipo_documento_usuario_creacion 
        FOREIGN KEY (usuario_creacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_tipo_documento_usuario_modificacion 
        FOREIGN KEY (usuario_modificacion)
        REFERENCES pedidosjb_seguridad.usuario (usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT

) ENGINE=InnoDB 
DEFAULT CHARSET=utf8mb4 
COLLATE=utf8mb4_unicode_ci;

-- 4) Ajustes de tabla despacho_pago
ALTER TABLE despacho_pago
    ADD COLUMN  idtipo_documento INT NULL AFTER idtipo_pago,
    ADD COLUMN  correlativo_documento VARCHAR(50) NULL AFTER monto,
    ADD COLUMN  banco VARCHAR(100) NULL AFTER correlativo_documento,
    MODIFY COLUMN estado VARCHAR(25) NOT NULL;


ALTER TABLE despacho_pago
    MODIFY COLUMN idtipo_documento INT NOT NULL,
    MODIFY COLUMN correlativo_documento VARCHAR(50) NOT NULL;
-- 5) Vistas
CREATE OR REPLACE VIEW pedidosjb_pedidos.view_despacho_pago_detalle AS
SELECT
    dp.iddespacho_pago,
    dp.iddespacho,
    dp.fecha,
    dp.estado,
    tp.signo,
    dp.monto,
    (IFNULL(tp.signo, 0) * dp.monto) AS monto_aplicado,
    dp.correlativo_documento,
    dp.banco,
    dp.referencia_pago,
    dp.observaciones,
    dp.usuario_creacion,
    tp.descripcion AS tipo_pago,
    td.nombre AS tipo_documento,
    fp.descripcion AS forma_pago
FROM pedidosjb_pedidos.despacho_pago dp
LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
LEFT JOIN pedidosjb_pedidos.forma_pago fp ON fp.idforma_pago = dp.idforma_pago
LEFT JOIN pedidosjb_pedidos.tipo_documento td ON td.idtipo_documento = dp.idtipo_documento;

-- Vista de saldo por tipo de pago por despacho
CREATE OR REPLACE VIEW pedidosjb_pedidos.view_despacho_pago_tipo_pago AS
SELECT
    vdpd.iddespacho,
    IFNULL(NULLIF(TRIM(vdpd.tipo_pago), ''), 'SIN TIPO') AS tipo_pago,
    SUM(vdpd.monto_aplicado) AS total_neto
FROM pedidosjb_pedidos.view_despacho_pago_detalle vdpd
WHERE UPPER(TRIM(vdpd.estado)) IN ('PROGRAMADO', 'EJECUTADO')
GROUP BY
    vdpd.iddespacho,
    IFNULL(NULLIF(TRIM(vdpd.tipo_pago), ''), 'SIN TIPO');


CREATE OR REPLACE VIEW pedidosjb_pedidos.view_despacho_pago_tipo_documento AS
SELECT
    vdpd.iddespacho,
    IFNULL(NULLIF(TRIM(vdpd.tipo_documento), ''), 'SIN TIPO') AS tipo_documento,
    SUM(vdpd.monto_aplicado) AS total_neto
FROM pedidosjb_pedidos.view_despacho_pago_detalle vdpd
WHERE UPPER(TRIM(vdpd.estado)) IN ('PROGRAMADO', 'EJECUTADO')
GROUP BY
    vdpd.iddespacho,
    IFNULL(NULLIF(TRIM(vdpd.tipo_documento), ''), 'SIN TIPO');

CREATE OR REPLACE VIEW pedidosjb_pedidos.view_despacho_pago_resumen AS
SELECT
    d.iddespacho,
    d.idpedido,
    vp.nopedido,
    vp.idcliente,
    vp.cliente,
    d.fecha,
    d.monto_total AS monto_despacho,
    IFNULL((
        SELECT SUM(dp.monto * IFNULL(tp.signo, 0))
        FROM pedidosjb_pedidos.despacho_pago dp
        LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
        WHERE dp.iddespacho = d.iddespacho
          AND UPPER(TRIM(dp.estado)) = 'EJECUTADO'
    ), 0) AS total_pagado_ejecutado,
    IFNULL((
        SELECT SUM(dp.monto * IFNULL(tp.signo, 0))
        FROM pedidosjb_pedidos.despacho_pago dp
        LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
        WHERE dp.iddespacho = d.iddespacho
          AND UPPER(TRIM(dp.estado)) = 'PROGRAMADO'
    ), 0) AS total_programado_neto,
    (d.monto_total - IFNULL((
        SELECT SUM(dp.monto * IFNULL(tp.signo, 0))
        FROM pedidosjb_pedidos.despacho_pago dp
        LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
        WHERE dp.iddespacho = d.iddespacho
          AND UPPER(TRIM(dp.estado)) = 'EJECUTADO'
    ), 0)) AS saldo_pendiente
FROM pedidosjb_pedidos.despacho d
LEFT JOIN pedidosjb_pedidos.view_pedidos vp ON vp.idpedido = d.idpedido
WHERE d.estado IN ('ACTIVO', 'CERRADO');


USE pedidosjb_seguridad;

INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
SELECT 54, 7, 'Registro pagos despacho', 'despacho_pago', 'cargar_opcion', 21, 'ACTIVO'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM pedidosjb_seguridad.opcion
    WHERE idopcion = 54
       OR entity = 'despacho_pago'
);

INSERT INTO accion (idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
SELECT
    o.idopcion,
    a.nombre,
    a.indOpcion,
    a.referencia1,
    a.referencia2,
    a.referencia3,
    'ACTIVO'
FROM opcion o
JOIN (
    SELECT 'Opcion_despacho_pago' AS nombre, 'SI' AS indOpcion, 'iddespacho' AS referencia1, NULL AS referencia2, NULL AS referencia3
    UNION ALL SELECT 'Consultar_despacho_pago', 'NO', 'iddespacho_pago', NULL, NULL
    UNION ALL SELECT 'Crear_despacho_pago', 'NO', 'iddespacho_pago', 'iddespacho', 'monto'
    UNION ALL SELECT 'Modificar_despacho_pago', 'NO', 'iddespacho_pago', 'iddespacho', 'monto'
    UNION ALL SELECT 'Eliminar_despacho_pago', 'NO', 'iddespacho_pago', 'iddespacho', 'estado'
    UNION ALL SELECT 'Ejecutar_despacho_pago', 'NO', 'iddespacho_pago', 'iddespacho', 'estado'
    UNION ALL SELECT 'Imprimir_despacho_pago', 'NO', 'iddespacho_pago', 'correlativo_documento', 'tipo_documento'
) a ON 1 = 1
WHERE o.entity = 'despacho_pago'
  AND NOT EXISTS (
        SELECT 1
        FROM accion x
        WHERE x.idopcion = o.idopcion
          AND x.nombre = a.nombre
    );
