INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(42, 7, 'Tipo de pago', 'tipo_pago', 'cargar_opcion', 11, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(202, 42, 'opcion_tipo_pago', 'SI', 'idtipo_pago', NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(203, 42, 'crear_tipo_pago', 'NO', 'idtipo_pago', 'descripcion', NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(204, 42, 'modificar_tipo_pago', 'NO', 'idtipo_pago', 'descrpcion', NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(205, 42, 'cambiar_estado_tipo_pago', 'NO', 'idtipo_pago', 'estado', NULL, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 202, 'SI');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 203, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 204, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 205, 'NO');