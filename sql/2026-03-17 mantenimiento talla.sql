INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(41, 7, 'Talla', 'talla', 'cargar_opcion', 10, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(198, 41, 'opcion_talla', 'SI', 'idtalla', NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(199, 41, 'crear_talla', 'NO', 'idtalla', 'numero', NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(200, 41, 'modificar_talla', 'NO', 'idtalla', 'numero', NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(201, 41, 'cambiar_estado_talla', 'NO', 'idtalla', 'estado', NULL, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 198, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 199, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 200, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 201, 'NO');