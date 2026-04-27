USE pedidosjb_pedidos;

CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_ventas_temporada` AS
SELECT
    p.nopedido AS nopedido,
    p.idpedido AS idpedido,
    p.idcliente AS idcliente,
    COALESCE(SUM(pd.subtotal), 0) AS monto_total,
    CONCAT(c.codigo, ' - ', c.nombre) AS nombre_cliente,
    p.idmarca AS idmarca,
    m.nombre AS nombre_marca,
    p.idtemporada AS idtemporada,
    COALESCE(SUM(pd.cantidad), 0) AS cantidad_pares,
    COUNT(DISTINCT pd.idproducto) AS cantidad_modelos,
    p.estado AS estado
FROM pedidosjb_pedidos.pedido p
JOIN pedidosjb_pedidos.marca m
    ON m.idmarca = p.idmarca
LEFT JOIN pedidosjb_pedidos.cliente c
    ON c.idcliente = p.idcliente
LEFT JOIN pedidosjb_pedidos.pedido_detalle pd
    ON pd.idpedido = p.idpedido
GROUP BY
    p.nopedido,
    p.idpedido,
    p.idcliente,
    c.codigo,
    c.nombre,
    p.idmarca,
    m.nombre,
    p.idtemporada,
    p.estado;
