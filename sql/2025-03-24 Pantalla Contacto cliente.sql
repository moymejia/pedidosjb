
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(34, 7, 'Crear contacto cliente', 'NO', NULL, NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(35, 7, 'Modificar contacto cliente', 'NO', NULL, NULL, NULL, 'ACTIVO');
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(36, 7, 'Cambiar de estado contacto cliente', 'NO', NULL, NULL, NULL, 'ACTIVO');

CREATE VIEW view_cliente_contacto AS
SELECT 
    cc.idcliente_contacto,
    cc.idcliente,
    cc.nombre        AS nombre_contacto,
    cc.telefono      AS telefono_contacto,
    cc.correo        AS correo_contacto,
    cc.estado        AS estado_contacto,
    cc.observaciones AS observaciones_contacto,
    tc.descripcion   AS tipo_contacto,
    tc.idtipo_contacto
FROM cliente_contacto cc
INNER JOIN tipo_contacto tc 
    ON tc.idtipo_contacto = cc.idtipo_contacto;