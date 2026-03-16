INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(39, 7, 'Tipo de Contacto', 'tipo_contacto', 'cargar_opcion', 8, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(190, 39, 'opcion_tipo_contacto', 'SI', 'idtipo_contacto', NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(191, 39, 'crea_tipo_contacto', 'NO', 'idtipo_contacto', NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(192, 39, 'modificar_tipo_contacto', 'NO', 'idtipo_contacto', NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(193, 39, 'cambiar_estado_tipo_contacto', 'NO', 'idtipo_contacto', NULL, NULL, 'ACTIVO');


INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 190, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 191, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 192, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 193, 'NO');