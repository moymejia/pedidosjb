INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES
(49, 7, 'Corte', 'corte', 'cargar_opcion', 18, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES
(235, 49, 'opcion_corte', 'SI', 'idconcepto', NULL, NULL, 'ACTIVO'),
(236, 49, 'crear_corte', 'NO', 'idconcepto', 'nombre', NULL, 'ACTIVO'),
(237, 49, 'modificar_corte', 'NO', 'idconcepto', 'nombre', NULL, 'ACTIVO'),
(238, 49, 'cambiar_estado_corte', 'NO', 'idconcepto', 'estado', NULL, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES
(1, 235, 'NO'),
(1, 236, 'NO'),
(1, 237, 'NO'),
(1, 238, 'NO');