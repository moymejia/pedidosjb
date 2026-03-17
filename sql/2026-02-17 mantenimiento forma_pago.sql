INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(43, 7, 'Forma de pago', 'forma_pago', 'cargar_opcion', 12, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES
(206, 43, 'opcion_forma_pago', 'SI', 'idforma_pago', NULL, NULL, 'ACTIVO'),
(207, 43, 'crear_forma_pago', 'NO', 'idforma_pago', 'descripcion', NULL, 'ACTIVO'),
(208, 43, 'modificar_forma_pago', 'NO', 'idforma_pago', 'descripcion', NULL, 'ACTIVO'),
(209, 43, 'cambiar_estado_forma_pago', 'NO', 'idforma_pago', 'estado', NULL, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 206, 'NO'),
VALUES(1, 207, 'NO'),
VALUES(1, 208, 'NO'),
VALUES(1, 209, 'NO');