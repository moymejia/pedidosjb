-- Ajuste: agregar cliente al reporte de recibos
-- Fecha: 2026-10-14
-- Descripcion:
--   Agrega el codigo y nombre del cliente a la vista utilizada por el reporte de recibos.

CREATE OR REPLACE
ALGORITHM = UNDEFINED
DEFINER = `root`@`%`
SQL SECURITY DEFINER
VIEW `pedidosjb_pedidos`.`view_reporte_recibo_cheques` AS
SELECT
    `idcliente` AS `idcliente`,
    IFNULL(`nombre_cliente`, '') AS `cliente`,
    IFNULL(`referencia_pago`, '') AS `cheque_no`,
    TRIM(IFNULL(`banco`, '')) AS `banco`,
    `fecha_pago` AS `fecha_cheque_raw`,
    IFNULL(`correlativo_documento`, '') AS `recibo_caja`,
    IFNULL(`tipo_documento`, '') AS `tipo_recibo`,
    IFNULL(`estado_pago_individual`, '') AS `estado_cheque`,
    IFNULL(`monto_pago`, 0) AS `valor_cheque`
FROM `pedidosjb_pedidos`.`view_estado_cuenta_despacho_detallado`
WHERE `fecha_pago` IS NOT NULL
    AND UPPER(TRIM(IFNULL(`tipo_pago`, ''))) LIKE '%CHEQUE%'
    AND UPPER(TRIM(IFNULL(`estado_pago_individual`, ''))) IN ('EJECUTADO', 'PROGRAMADO')
    AND UPPER(TRIM(IFNULL(`tipo_documento`, ''))) NOT LIKE 'RECUPER%';
