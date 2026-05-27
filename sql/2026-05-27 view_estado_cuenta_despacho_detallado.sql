CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_estado_cuenta_despacho_detallado` AS
select
    `d`.`iddespacho` AS `iddespacho`,
    `p`.`idcliente` AS `idcliente`,
    concat(`c`.`codigo`, ' - ', `c`.`nombre`) AS `nombre_cliente`,
    `p`.`idtemporada` AS `idtemporada`,
    `d`.`numero_factura` AS `numero_factura`,
    `d`.`fecha_factura` AS `fecha_factura`,
    `d`.`monto_total` AS `monto_total`,
    IFNULL(`d`.`monto_flete`, 0) AS `monto_flete`,
    `dp`.`monto` AS `monto_pago`,
    `dp`.`correlativo_documento` AS `correlativo_documento`,
    (`d`.`monto_total` - ifnull(
        (
            select sum(`dp1`.`monto`)
            from `pedidosjb_pedidos`.`despacho_pago` `dp1`
            where (
                (`dp1`.`iddespacho` = `d`.`iddespacho`)
                and (`dp1`.`estado` = 'EJECUTADO')
            )
        ),0)
    ) AS `saldo_pendiente`,
    (`d`.`fecha_factura` + interval `p`.`dias_credito` day) AS `fecha_vencimiento`,
    (to_days((`d`.`fecha_factura` + interval `p`.`dias_credito` day))
     - to_days(curdate())) AS `proximidad`,
    (case
        when ((to_days((`d`.`fecha_factura` + interval `p`.`dias_credito` day))
            - to_days(curdate())) <= 0)
            then 'Vencido'
        when ((to_days((`d`.`fecha_factura` + interval `p`.`dias_credito` day))
            - to_days(curdate())) between 1 and 30)
            then 'A 30'
        when ((to_days((`d`.`fecha_factura` + interval `p`.`dias_credito` day))
            - to_days(curdate())) between 31 and 60)
            then 'A 60'
        when ((to_days((`d`.`fecha_factura` + interval `p`.`dias_credito` day))
            - to_days(curdate())) between 61 and 90)
            then 'A 90'
        else '90 +'
    end) AS `estado`,
    (case
        when (
            ifnull(
                (
                    select sum(`dp1`.`monto`)
                    from `pedidosjb_pedidos`.`despacho_pago` `dp1`
                    where (
                        (`dp1`.`iddespacho`=`d`.`iddespacho`)
                        and (`dp1`.`estado`='EJECUTADO')
                    )
                ),0
            ) = 0
        ) then 'PENDIENTE' 
        when (
            ifnull(
                (
                    select sum(`dp1`.`monto`)
                    from `pedidosjb_pedidos`.`despacho_pago` `dp1`
                    where (
                        (`dp1`.`iddespacho`=`d`.`iddespacho`)
                        and (`dp1`.`estado`='EJECUTADO')
                    )
                ),0
            ) < `d`.`monto_total`
        ) then 'PARCIAL'
        else 'PAGADO'
    end) AS `estado_pago`,
    `dp`.`fecha` AS `fecha_pago`,
    `tp`.`descripcion` AS `tipo_pago`,
    `td`.`nombre` AS `tipo_documento`,
    `dp`.`estado` AS `estado_pago_individual`
from
    (((((`pedidosjb_pedidos`.`despacho` `d`
join `pedidosjb_pedidos`.`pedido` `p`
    on (`d`.`idpedido` = `p`.`idpedido`))
join `pedidosjb_pedidos`.`cliente` `c`
    on (`p`.`idcliente` = `c`.`idcliente`))
left join `pedidosjb_pedidos`.`despacho_pago` `dp`
    on (`d`.`iddespacho` = `dp`.`iddespacho`))
left join `pedidosjb_pedidos`.`tipo_pago` `tp`
    on (`dp`.`idtipo_pago` = `tp`.`idtipo_pago`))
left join `pedidosjb_pedidos`.`tipo_documento` `td`
    on (`dp`.`idtipo_documento` = `td`.`idtipo_documento`))
where
    (`d`.`fecha_factura` is not null);