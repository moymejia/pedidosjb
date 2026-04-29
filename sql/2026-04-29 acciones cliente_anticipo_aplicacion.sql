-- Registro de acciones para cliente_anticipo_aplicacion
-- Fecha: 2026-04-29

USE pedidosjb_seguridad;

-- Insertar acciones asociadas a la opcion de cliente_anticipo
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
    SELECT 'Crear_cliente_anticipo_aplicacion'      AS nombre, 'NO' AS indOpcion, 'idcliente_anticipo_aplicacion' AS referencia1, 'idcliente_anticipo' AS referencia2, 'monto_aplicado' AS referencia3
    UNION ALL SELECT 'Actualizar_cliente_anticipo_aplicacion', 'NO', 'idcliente_anticipo_aplicacion', 'idcliente_anticipo', 'monto_aplicado'
    UNION ALL SELECT 'Cancelar_cliente_anticipo_aplicacion',   'NO', 'idcliente_anticipo_aplicacion', 'estado', NULL
    UNION ALL SELECT 'Consultar_cliente_anticipo_aplicacion',  'NO', 'idcliente_anticipo_aplicacion', NULL, NULL
) a
WHERE o.entity = 'cliente_anticipo'
AND NOT EXISTS (
    SELECT 1
    FROM accion x
    WHERE x.idopcion = o.idopcion
    AND x.nombre = a.nombre
);

-- Asignar acciones al rol 1 (administrador)
INSERT INTO rol_accion (idrol, idaccion, indFavorito)
SELECT 1, a.idaccion, 'NO'
FROM accion a
JOIN opcion o ON o.idopcion = a.idopcion
WHERE o.entity = 'cliente_anticipo'
AND a.nombre IN (
    'Crear_cliente_anticipo_aplicacion',
    'Actualizar_cliente_anticipo_aplicacion',
    'Cancelar_cliente_anticipo_aplicacion',
    'Consultar_cliente_anticipo_aplicacion'
)
AND NOT EXISTS (
    SELECT 1
    FROM rol_accion r
    WHERE r.idrol = 1
    AND r.idaccion = a.idaccion
);
