-- Pantalla de anticipos de cliente
-- Fecha: 2026-04-28

USE pedidosjb_pedidos;

CREATE OR REPLACE VIEW view_cliente_anticipo AS
SELECT
    ca.idcliente_anticipo,
    ca.idcliente,
    ca.fecha,
    ca.idtipo_pago,
    ca.monto,
    ca.saldo_disponible,
    ca.referencia_pago,
    ca.observaciones,
    ca.estado,
    ca.fecha_creacion,
    ca.usuario_creacion,
    ca.fecha_modificacion,
    ca.usuario_modificacion,
    CONCAT(c.codigo, ' - ', c.nombre) AS cliente,
    tp.descripcion AS tipo_pago
FROM cliente_anticipo ca
LEFT JOIN cliente c ON c.idcliente = ca.idcliente
LEFT JOIN tipo_pago tp ON tp.idtipo_pago = ca.idtipo_pago;

USE pedidosjb_seguridad;

INSERT INTO opcion (idmenu, nombre, entity, funcion, orden, estado)
SELECT 7, 'Anticipos de cliente', 'cliente_anticipo', 'cargar_opcion', 22, 'ACTIVO'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM opcion
    WHERE entity = 'cliente_anticipo'
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
FROM opcion o,
(
    SELECT 'Opcion_cliente_anticipo' AS nombre, 'SI' AS indOpcion, 'idcliente_anticipo' AS referencia1, NULL AS referencia2, NULL AS referencia3
    UNION ALL SELECT 'Consultar_cliente_anticipo', 'NO', 'idcliente_anticipo', NULL, NULL
    UNION ALL SELECT 'Crear_cliente_anticipo', 'NO', 'idcliente_anticipo', 'idcliente', 'monto'
    UNION ALL SELECT 'Modificar_cliente_anticipo', 'NO', 'idcliente_anticipo', 'idcliente', 'monto'
    UNION ALL SELECT 'Cambiar_estado_cliente_anticipo', 'NO', 'idcliente_anticipo', 'estado', NULL
) a
WHERE o.entity = 'cliente_anticipo'
AND NOT EXISTS (
    SELECT 1
    FROM accion x
    WHERE x.idopcion = o.idopcion
    AND x.nombre = a.nombre
);

INSERT INTO rol_accion (idrol, idaccion, indFavorito)
SELECT 1, a.idaccion,
    CASE
        WHEN a.nombre = 'Opcion_cliente_anticipo' THEN 'SI'
        ELSE 'NO'
    END AS indFavorito
FROM accion a, opcion o
WHERE o.entity = 'cliente_anticipo'
AND o.idopcion = a.idopcion
AND NOT EXISTS (
    SELECT 1
    FROM rol_accion r
    WHERE r.idrol = 1
    AND r.idaccion = a.idaccion
);
