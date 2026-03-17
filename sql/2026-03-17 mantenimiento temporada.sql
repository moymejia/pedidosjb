INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(40, 7, 'Temporada', 'temporada', 'cargar_opcion', 9, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES
(194, 40, 'opcion_temporada', 'SI', 'idtemporada', 'fecha_inicio', '', 'ACTIVO'),
(195, 40, 'crear_temporada', 'NO', 'idtemporada', 'fecha_inicio', '', 'ACTIVO'),
(196, 40, 'modificar_temporada', 'NO', 'idtemporada', 'fecha_inicio', 'fecha_final', 'ACTIVO'),
(197, 40, 'cambiar_estado_temporada', 'NO', 'idtemporada', 'estado', '', 'ACTIVO');

INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES
(1, 194, 'NO'),
(1, 195, 'NO'),
(1, 196, 'NO'),
(1, 197, 'NO');
