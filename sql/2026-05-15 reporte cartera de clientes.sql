-- Reporte: Cartera de clientes
-- Fecha: 2026-05-14
-- Descripcion:
--   1) Registra opcion y accion de seguridad para la nueva pantalla.
--   2) Crea vista view_cartera_de_clientes para consumirla desde PHP sin JOIN en codigo.

USE pedidosjb_seguridad;

SET @idopcion_cartera = (
    SELECT idopcion
    FROM opcion
    WHERE entity = 'cartera_de_clientes'
    LIMIT 1
);

SET @idopcion_cartera = IFNULL(
    @idopcion_cartera,
    (SELECT IFNULL(MAX(idopcion), 0) + 1 FROM opcion)
);

INSERT INTO opcion (idopcion, idmenu, nombre, entity, funcion, orden, estado)
SELECT
    @idopcion_cartera,
    9,
    'Cartera de clientes',
    'cartera_de_clientes',
    'cargar_opcion',
    26,
    'ACTIVO'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM opcion
    WHERE entity = 'cartera_de_clientes'
);

SET @idopcion_cartera = (
    SELECT idopcion
    FROM opcion
    WHERE entity = 'cartera_de_clientes'
    LIMIT 1
);

SET @idaccion_cartera = (
    SELECT idaccion
    FROM accion
    WHERE idopcion = @idopcion_cartera
      AND nombre = 'Opcion_cartera_de_clientes'
    LIMIT 1
);

SET @idaccion_cartera = IFNULL(
    @idaccion_cartera,
    (SELECT IFNULL(MAX(idaccion), 0) + 1 FROM accion)
);

INSERT INTO accion (idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
SELECT
    @idaccion_cartera,
    @idopcion_cartera,
    'Opcion_cartera_de_clientes',
    'SI',
    NULL,
    NULL,
    NULL,
    'ACTIVO'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM accion
    WHERE idopcion = @idopcion_cartera
      AND nombre = 'Opcion_cartera_de_clientes'
);

INSERT INTO rol_accion (idrol, idaccion, indFavorito)
SELECT 1, @idaccion_cartera, 'NO'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1
    FROM rol_accion
    WHERE idrol = 1
      AND idaccion = @idaccion_cartera
);

USE pedidosjb_pedidos;

CREATE OR REPLACE VIEW pedidosjb_pedidos.view_cartera_de_clientes AS
SELECT
    d.iddespacho,
    p.idpedido,
    p.idcliente,
    c.codigo AS codigo_cliente,
    c.nombre AS nombre_cliente,
    p.usuario_creacion AS usuario_vendedor,
    IFNULL(u.nombre, p.usuario_creacion) AS nombre_vendedor,
    d.numero_factura,
    d.fecha_factura,
    d.monto_total,
    IFNULL(dp_ejecutado.monto_ejecutado, 0) AS monto_ejecutado,
    IFNULL(dp_programado.monto_programado, 0) AS monto_programado,
    IFNULL(dp_no_ejecutado.monto_no_ejecutado, 0) AS monto_no_ejecutado,
    GREATEST(
        d.monto_total - IFNULL(dp_ejecutado.monto_ejecutado, 0),
        0
    ) AS saldo_cartera,
    DATEDIFF(CURDATE(), d.fecha_factura) AS dias_transcurridos,
    CASE
        WHEN DATEDIFF(CURDATE(), d.fecha_factura) <= 30 THEN
            GREATEST(d.monto_total - IFNULL(dp_ejecutado.monto_ejecutado, 0), 0)
        ELSE 0
    END AS saldo_0_30,
    CASE
        WHEN DATEDIFF(CURDATE(), d.fecha_factura) BETWEEN 31 AND 60 THEN
            GREATEST(d.monto_total - IFNULL(dp_ejecutado.monto_ejecutado, 0), 0)
        ELSE 0
    END AS saldo_31_60,
    CASE
        WHEN DATEDIFF(CURDATE(), d.fecha_factura) BETWEEN 61 AND 90 THEN
            GREATEST(d.monto_total - IFNULL(dp_ejecutado.monto_ejecutado, 0), 0)
        ELSE 0
    END AS saldo_61_90,
    CASE
        WHEN DATEDIFF(CURDATE(), d.fecha_factura) > 90 THEN
            GREATEST(d.monto_total - IFNULL(dp_ejecutado.monto_ejecutado, 0), 0)
        ELSE 0
    END AS saldo_91_mas
FROM pedidosjb_pedidos.despacho d
JOIN pedidosjb_pedidos.pedido p ON p.idpedido = d.idpedido
JOIN pedidosjb_pedidos.cliente c ON c.idcliente = p.idcliente
LEFT JOIN pedidosjb_seguridad.usuario u ON u.usuario = p.usuario_creacion
LEFT JOIN (
    SELECT
        dp.iddespacho,
        SUM(dp.monto * IFNULL(tp.signo, 0)) AS monto_ejecutado
    FROM pedidosjb_pedidos.despacho_pago dp
    LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
    WHERE UPPER(TRIM(dp.estado)) = 'EJECUTADO'
    GROUP BY dp.iddespacho
) dp_ejecutado ON dp_ejecutado.iddespacho = d.iddespacho
LEFT JOIN (
    SELECT
        dp.iddespacho,
        SUM(dp.monto * IFNULL(tp.signo, 0)) AS monto_programado
    FROM pedidosjb_pedidos.despacho_pago dp
    LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
    WHERE UPPER(TRIM(dp.estado)) = 'PROGRAMADO'
    GROUP BY dp.iddespacho
) dp_programado ON dp_programado.iddespacho = d.iddespacho
LEFT JOIN (
    SELECT
        dp.iddespacho,
        SUM(dp.monto * IFNULL(tp.signo, 0)) AS monto_no_ejecutado
    FROM pedidosjb_pedidos.despacho_pago dp
    LEFT JOIN pedidosjb_pedidos.tipo_pago tp ON tp.idtipo_pago = dp.idtipo_pago
    WHERE UPPER(TRIM(dp.estado)) <> 'EJECUTADO'
    GROUP BY dp.iddespacho
) dp_no_ejecutado ON dp_no_ejecutado.iddespacho = d.iddespacho
WHERE d.fecha_factura IS NOT NULL
    AND GREATEST(
                d.monto_total - IFNULL(dp_ejecutado.monto_ejecutado, 0),
                0
            ) > 0
  AND IFNULL(TRIM(d.numero_factura), '') <> '';



UPDATE pedidosjb_pedidos.pedido
SET idcliente=41, idtemporada=105, idmarca=4, observaciones_pedido='10% de Descuento - Algunas imágenes son solo referencia del estilo', descuento=0.00, dias_credito=0, monto_subtotal=158181.66, monto_descuento=10.00, monto_total=142363.49, estado='CERRADO', fecha_creacion='2026-04-26 12:26:46', usuario_creacion='jbran', fecha_modificacion='2026-04-26 14:56:18', usuario_modificacion='jbran', fecha_desde='2026-07-01', fecha_hasta='2026-08-31', total_pares=24, email='', idtransporte=2, nopedido='JB-1201'
WHERE idpedido=43;


UPDATE pedidosjb_pedidos.tipo_documento
SET nombre='Recibo de caja', correlativo='NO', estado='ACTIVO', fecha_creacion='2026-05-04 16:36:52', usuario_creacion='admin', fecha_modificacion='2026-05-15 10:52:09', usuario_modificacion=NULL
WHERE idtipo_documento=1;
