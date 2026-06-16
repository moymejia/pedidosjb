-- Permiso para editar encabezado de despacho
-- Fecha: 2026-06-16
-- EJECUTAR EN BASE DE DATOS pedidosjb_seguridad

INSERT INTO pedidosjb_seguridad.accion (idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
SELECT o.idopcion, 'Modificar_despacho', 'NO', 'iddespacho', 'numero_factura', 'usuario_modificacion', 'ACTIVO'
FROM pedidosjb_seguridad.opcion o
WHERE o.entity = 'despacho'
  AND o.funcion = 'cargar_opcion'
  AND NOT EXISTS (
      SELECT 1
      FROM pedidosjb_seguridad.accion a
      WHERE a.idopcion = o.idopcion
        AND a.nombre = 'Modificar_despacho'
  );

INSERT INTO pedidosjb_seguridad.rol_accion (idrol, idaccion, nombre, indFavorito)
SELECT 1, a.idaccion, a.nombre, 'NO'
FROM pedidosjb_seguridad.accion a
JOIN pedidosjb_seguridad.opcion o ON o.idopcion = a.idopcion
WHERE o.entity = 'despacho'
  AND o.funcion = 'cargar_opcion'
  AND a.nombre = 'Modificar_despacho'
  AND NOT EXISTS (
      SELECT 1
      FROM pedidosjb_seguridad.rol_accion r
      WHERE r.idrol = 1
        AND r.idaccion = a.idaccion
  );
