-- Vistas para pantalla de despacho
-- Fecha: 2026-04-14
-- EJECUTAR EN BASE DE DATOS
-- Elimina JOINs del código PHP de despacho.php

INSERT INTO pedidosjb_seguridad.opcion
(idopcion, idmenu, nombre, entity, funcion, orden, estado)
VALUES(51, 7, 'Despacho', 'despacho', 'cargar_opcion', 20, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES (51, 'Opcion despacho', 'SI', NULL, NULL, NULL, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES (51, 'Crear_despacho', 'NO', NULL, NULL, NULL, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES (51, 'Despachar_lineas', 'NO', NULL, NULL, NULL, 'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion
(idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES (51, 'Cerrar_despacho', 'NO', NULL, NULL, NULL, 'ACTIVO');



USE pedidosjb_pedidos;


CREATE OR REPLACE VIEW pedidosjb_pedidos.view_despacho_detalle AS
SELECT
    vpd.idpedido_detalle,
    vpd.idpedido,
    vpd.idproducto,
    vpd.idset_talla,
    vpd.set_talla,
    vpd.codigo,
    vpd.descripcion,
    vpd.color,
    vpd.marca,
    vpd.material,
    vpd.precio_venta,
    vpd.cantidad,
    vpd.subtotal,
    vpd.idtalla,
    vpd.talla,
    vpd.imagen,
    IFNULL(pd.cantidad_despachada, 0)                              AS cantidad_despachada,
    IFNULL(pd.cantidad_pendiente, IFNULL(vpd.cantidad, 0))         AS cantidad_pendiente,
    UPPER(TRIM(COALESCE(pd.estado, 'ACTIVO')))                     AS estado
FROM pedidosjb_pedidos.view_pedido_detalle vpd
LEFT JOIN pedidosjb_pedidos.pedido_detalle pd
    ON pd.idpedido_detalle = vpd.idpedido_detalle;


CREATE OR REPLACE VIEW pedidosjb_pedidos.view_despachos_proceso AS
SELECT
    d.iddespacho,
    d.idpedido,
    d.fecha,
    d.numero_factura,
    d.monto_total,
    d.estado,
    p.nopedido,
    p.cliente,
    p.temporada,
    p.marca,
    (
        SELECT COUNT(*)
        FROM pedidosjb_pedidos.pedido_detalle pd
        WHERE pd.idpedido = d.idpedido
          AND IFNULL(pd.cantidad_pendiente, 0) > 0
    ) AS lineas_pendientes
FROM pedidosjb_pedidos.despacho d
JOIN pedidosjb_pedidos.view_pedidos p ON p.idpedido = d.idpedido
WHERE d.estado IN ('ACTIVO', 'CERRADO')
  AND p.estado = 'CERRADO';


CREATE OR REPLACE VIEW pedidosjb_pedidos.view_despacho_lineas_estado AS
SELECT
    dd.iddespacho_detalle,
    dd.iddespacho,
    dd.idpedido_detalle,
    dd.cantidad,
    dd.precio_venta,
    dd.subtotal,
    dd.estado         AS estado_linea,
    d.estado          AS estado_despacho
FROM pedidosjb_pedidos.despacho_detalle dd
JOIN pedidosjb_pedidos.despacho d ON d.iddespacho = dd.iddespacho;
