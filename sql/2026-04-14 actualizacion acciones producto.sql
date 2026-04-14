UPDATE pedidosjb_seguridad.accion
SET nombre = 'crear_producto_carga_productos'
WHERE idaccion = 24;

UPDATE pedidosjb_seguridad.accion
SET nombre = 'eliminar_productos_borrador_carga_productos'
WHERE idaccion = 26;

UPDATE pedidosjb_seguridad.accion
SET nombre = 'activar_productos_carga_productos'
WHERE idaccion = 27;

UPDATE pedidosjb_seguridad.accion
SET nombre = 'modificar_producto_carga_productos'
WHERE idaccion = 28;

UPDATE pedidosjb_seguridad.accion
SET nombre = 'crear_producto_mantenimiento'
WHERE idaccion = 49;

UPDATE pedidosjb_seguridad.accion
SET nombre = 'modificar_producto_mantenimiento'
WHERE idaccion = 50;

UPDATE pedidosjb_seguridad.accion
SET nombre = 'cambiar_estado_mantenimiento'
WHERE idaccion = 51;

UPDATE pedidosjb_seguridad.accion
SET nombre = 'crear_producto_precio_mantenimiento'
WHERE idaccion = 52;

UPDATE pedidosjb_seguridad.accion
SET nombre = 'modificar_producto_precio_mantenimiento'
WHERE idaccion = 53;

DELETE FROM pedidosjb_seguridad.rol_accion
WHERE idaccion IN (218, 219);

DELETE FROM pedidosjb_seguridad.accion
WHERE idaccion IN (218, 219);
