INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(46, 7, 'Transporte', 'transporte', 'cargar_opcion', 15, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES
(223, 46, 'opcion_transporte', 'SI', 'idtransporte', NULL, NULL, 'ACTIVO'),
(224, 46, 'crear_transporte', 'NO', 'idtransporte', NULL, NULL, 'ACTIVO'),
(225, 46, 'modificar_transporte', 'NO', 'idtransporte', 'nombre', NULL, 'ACTIVO'),
(226, 46, 'cambiar_estado_transporte', 'NO', 'idtransporte', 'estado', NULL, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES
(1, 223, 'NO'),
(1, 224, 'NO'),
(1, 225, 'NO'),
(1, 226, 'NO');