INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(48, 7, 'Concepto', 'concepto', 'cargar_opcion', 17, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES
(231, 47, 'opcion_concepto', 'SI', 'idconcepto', NULL, NULL, 'ACTIVO'),
(232, 47, 'crear_concepto', 'NO', 'idconcepto', 'nombre', NULL, 'ACTIVO'),
(233, 47, 'modificar_concepto', 'NO', 'idconcepto', 'nombre', NULL, 'ACTIVO'),
(234, 47, 'cambiar_estado_concepto', 'NO', 'idconcepto', 'estado', NULL, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES
(1, 231, 'NO'),
(1, 232, 'NO'),
(1, 233, 'NO'),
(1, 234, 'NO');