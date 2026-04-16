ALTER TABLE pedidosjb_seguridad.bitacora MODIFY idaccion INT(11) NULL;

ALTER TABLE pedidosjb_seguridad.bitacora ADD accion VARCHAR(100) NULL AFTER idaccion;

ALTER TABLE pedidosjb_seguridad.accion ADD INDEX nombre_accion_idx (nombre);


CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_seguridad`.`view_bitacora` AS
SELECT
    b.idbitacora AS idbitacora,
    b.usuario AS usuario,
    u.nombre AS nombre_usuario,
    o.idopcion AS idopcion,
    o.nombre AS opcion,
    IF(b.idaccion IS NULL, NULL, a.idaccion) AS idaccion,
    IF(b.idaccion IS NULL, b.accion, a.nombre) AS accion,
    CAST(b.fechahora AS DATE) AS fecha,
    CAST(b.fechahora AS TIME) AS hora,
    IF(b.idaccion IS NULL, b.referencia1,
        IF(a.referencia1 IS NOT NULL, CONCAT(a.referencia1, ': ', b.referencia1), '')
    ) AS referencia_1,
    IF(b.idaccion IS NULL, b.referencia2,
        IF(a.referencia2 IS NOT NULL, CONCAT(a.referencia2, ': ', b.referencia2), '')
    ) AS referencia_2,
    IF(b.idaccion IS NULL, b.referencia3,
        IF(a.referencia3 IS NOT NULL, CONCAT(a.referencia3, ': ', b.referencia3), '')
    ) AS referencia_3
FROM pedidosjb_seguridad.bitacora b
JOIN pedidosjb_seguridad.usuario u 
    ON b.usuario = u.usuario
LEFT JOIN pedidosjb_seguridad.accion a 
    ON (b.idaccion = a.idaccion OR (b.idaccion IS NULL AND b.accion = a.nombre))
LEFT JOIN pedidosjb_seguridad.opcion o 
    ON a.idopcion = o.idopcion;

    