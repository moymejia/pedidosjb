INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES
(50, 7, 'Tipo de suela', 'tipo_suela', 'cargar_opcion', 19, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES
(239, 50, 'opcion_tipo_suela', 'SI', 'idtipo_suela', NULL, NULL, 'ACTIVO'),
(240, 50, 'crear_tipo_suela', 'NO', 'idtipo_suela', 'nombre', NULL, 'ACTIVO'),
(241, 50, 'modificar_tipo_suela', 'NO', 'idtipo_suela', 'nombre', NULL, 'ACTIVO'),
(242, 50, 'cambiar_estado_tipo_suela', 'NO', 'idtipo_suela', 'estado', NULL, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES
(1, 239, 'NO'),
(1, 240, 'NO'),
(1, 241, 'NO'),
(1, 242, 'NO');