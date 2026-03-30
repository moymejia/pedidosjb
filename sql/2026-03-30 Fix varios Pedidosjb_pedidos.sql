ALTER TABLE pedidosjb_pedidos.cliente
ADD COLUMN correo VARCHAR(150) NULL;

ALTER TABLE pedidosjb_pedidos.marca
ADD COLUMN descripcion VARCHAR(255) NULL;


CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_pedidos` AS
SELECT
    p.idpedido AS idpedido,
    p.idcliente AS idcliente,
    p.idtemporada AS idtemporada,
    p.nopedido AS nopedido,
    p.fecha_creacion AS fecha_creacion,
    p.idmarca AS idmarca,
    p.idset_talla AS idset_talla,
    p.idtransporte AS idtransporte,
    p.email AS email,
    p.monto_descuento AS monto_descuento,
    CONCAT(c.codigo, ' - ', c.nombre) AS cliente,
    c.telefono AS telefono,
    c.direccion AS direccion,
    c.nit AS nit,
    c.establecimiento AS establecimiento,
    c.dias_credito AS dias_credito,
    t.nombre AS temporada,
    m.nombre AS marca,
    st.grupo AS set_talla,
    tr.nombre AS transporte,
    p.estado AS estado,
    p.fecha_desde AS fecha_desde,
    p.fecha_hasta AS fecha_hasta,
    p.observaciones_pedido AS observaciones_pedido
FROM pedidosjb_pedidos.pedido p
JOIN pedidosjb_pedidos.cliente c 
    ON p.idcliente = c.idcliente
JOIN pedidosjb_pedidos.temporada t 
    ON p.idtemporada = t.idtemporada
JOIN pedidosjb_pedidos.marca m 
    ON p.idmarca = m.idmarca
JOIN pedidosjb_pedidos.set_talla st 
    ON p.idset_talla = st.idset_talla
LEFT JOIN pedidosjb_pedidos.transporte tr 
    ON p.idtransporte = tr.idtransporte;


    CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_marca_set_talla` AS
SELECT
    m.idmarca AS idmarca,
    m.nombre AS nombre,
    m.descripcion AS descripcion, 
    m.estado AS estado,
    m.idset_talla_preferido AS idset_talla_preferido,
    st.idset_talla AS idset_talla,
    st.grupo AS grupo
FROM pedidosjb_pedidos.marca m
JOIN pedidosjb_pedidos.set_talla st 
    ON m.idset_talla_preferido = st.idset_talla
ORDER BY m.idmarca DESC;