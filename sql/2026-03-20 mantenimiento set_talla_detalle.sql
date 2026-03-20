INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(45, 7, 'Set de tallas', 'set_talla', 'cargar_opcion', 14, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(214, 45, 'opcion_set_talla', 'SI', NULL, NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(215, 45, 'crear_set_talla', 'NO', NULL, NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(216, 45, 'modificar_set_talla', 'NO', NULL, NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(217, 45, 'cambiar estado_set_talla', 'NO', NULL, NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(218, 45, 'agregar_talla', 'NO', NULL, NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(219, 45, 'retirar_talla', 'NO', NULL, NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(220, 45, 'cargar_talla', 'NO', 'idset_talla_detalle', 'idset_talla', 'id_talla', 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(221, 45, 'agregar_talla', 'NO', 'idset_talla_detalle', 'idset_talla', 'id_talla', 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(222, 45, 'retirar_talla', 'NO', 'idset_talla_detalle', 'idset_talla', 'id_talla', 'ACTIVO');


INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 214, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 215, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 216, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 217, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 218, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 219, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 220, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 221, 'NO');
INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 222, 'NO');

-- pedidosjb_pedidos.view_set_talla_detalle source

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_set_talla_detalle` AS
select
    `st`.`idset_talla` AS `idset_talla`,
    `st`.`idtalla` AS `idtalla`,
    `t`.`numero` AS `numero`
from
    (`pedidosjb_pedidos`.`set_talla_detalle` `st`
join `pedidosjb_pedidos`.`talla` `t` on
    ((`st`.`idtalla` = `t`.`idtalla`)));