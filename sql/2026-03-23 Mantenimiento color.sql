INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(47, 7, 'Color', 'color', 'cargar_opcion', 16, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES
(227, 47, 'opcion_color', 'SI', 'idcolor', NULL, NULL, 'ACTIVO'),
(228, 47, 'crear_color', 'NO', 'idcolor', 'nombre', NULL, 'ACTIVO'),
(229, 47, 'modificar_color', 'NO', 'idcolor', 'nombre', NULL, 'ACTIVO'),
(230, 47, 'cambiar_estado_color', 'NO', 'idcolor', 'estado', NULL, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES
(1, 227, 'NO'),
(1, 228, 'NO'),
(1, 229, 'NO'),
(1, 230, 'NO');