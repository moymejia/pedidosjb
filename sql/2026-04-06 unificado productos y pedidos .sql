
INSERT INTO pedidosjb_seguridad.opcion (idopcion,idmenu,nombre,entity,funcion,orden,estado)
	VALUES (10,2,'Producto','producto','cargar_opcion_producto',10,'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion (idaccion,idopcion,nombre,indOpcion,estado)VALUES 
	(48,10,'Opcion producto','SI','ACTIVO'),
	(49,10,'Crear producto','NO','ACTIVO'),
	(50,10,'Modificar producto','NO','ACTIVO'),
	(51,10,'Cambiar estado producto','NO','ACTIVO'),
	(52,10,'Crear producto precio','NO','ACTIVO'),
	(53,10,'Modificar producto precio','NO','ACTIVO');



USE pedidosjb_pedidos;

ALTER TABLE pedido 
    DROP FOREIGN KEY fk_pedido_set_talla,
    DROP INDEX idx_pedido_idset_talla,
    DROP COLUMN idset_talla;
    
ALTER TABLE pedido_detalle 
    ADD COLUMN idset_talla INT NOT NULL,
    ADD INDEX idx_pedido_detalle_idset_talla (idset_talla),
    ADD CONSTRAINT fk_pedido_detalle_set_talla
        FOREIGN KEY (idset_talla)
        REFERENCES set_talla(idset_talla)
        ON UPDATE CASCADE;


CREATE OR REPLACE VIEW pedidosjb_pedidos.view_pedidos AS
SELECT
    p.idpedido,
    p.idcliente,
    p.idtemporada,
    p.nopedido,
    p.fecha_creacion,
    p.idmarca,
    p.idtransporte,
    p.email,
    p.monto_descuento,
    CONCAT(c.codigo, ' - ', c.nombre) AS cliente,
    c.telefono,
    c.direccion,
    c.nit,
    c.establecimiento,
    c.dias_credito,
    t.nombre AS temporada,
    m.nombre AS marca,
    m.descripcion AS descripcion_marca,
    tr.nombre AS transporte,
    p.estado,
    p.fecha_desde,
    p.fecha_hasta,
    p.observaciones_pedido
FROM pedidosjb_pedidos.pedido p
INNER JOIN pedidosjb_pedidos.cliente c 
    ON p.idcliente = c.idcliente
INNER JOIN pedidosjb_pedidos.temporada t 
    ON p.idtemporada = t.idtemporada
INNER JOIN pedidosjb_pedidos.marca m 
    ON p.idmarca = m.idmarca
LEFT JOIN pedidosjb_pedidos.transporte tr 
    ON p.idtransporte = tr.idtransporte;


CREATE OR REPLACE VIEW pedidosjb_pedidos.view_pedido_detalle AS
SELECT
    pd.idpedido_detalle,
    pd.idpedido,
    pd.imagen,
    pd.idproducto,
    pd.idproducto_precio,
    pd.idset_talla,
    st.grupo,
    st.descripcion AS set_descripcion,
    CONCAT(IFNULL(st.grupo,''), ' - ', IFNULL(st.descripcion,'')) AS set_talla,
    p.modelo AS codigo,
    p.linea AS descripcion,
    c.idcolor,
    c.nombre AS color,
    m.nombre AS marca,
    pp.material,
    pd.precio_venta,
    pd.cantidad,
    pd.subtotal,
    t.idtalla,
    t.numero AS talla
FROM pedidosjb_pedidos.pedido_detalle pd
JOIN pedidosjb_pedidos.producto p ON pd.idproducto = p.idproducto
JOIN pedidosjb_pedidos.color c ON p.idcolor = c.idcolor
JOIN pedidosjb_pedidos.producto_precio pp ON pp.idproducto_precio = pd.idproducto_precio
JOIN pedidosjb_pedidos.talla t ON pd.idtalla = t.idtalla
JOIN pedidosjb_pedidos.marca m ON p.idmarca = m.idmarca
LEFT JOIN pedidosjb_pedidos.set_talla st ON pd.idset_talla = st.idset_talla;


CREATE OR REPLACE VIEW pedidosjb_pedidos.view_producto AS
SELECT 
    p.idproducto,
    p.idmarca,
    m.nombre as marca,
    p.idtemporada,
    t.nombre as temporada,
    p.linea,
    p.modelo,
    p.idset_talla,
    st.grupo AS set_talla,
    p.idcolor,
    c.nombre AS color,
    p.idcorte,
    co.nombre as corte,
    p.idtipo_suela,
    ts.nombre as tipo_suela,
    p.idconcepto,
    cp.nombre as concepto,
    p.estado,
    p.fecha_creacion,
    p.usuario_creacion,
    p.fecha_modificacion,
    p.usuario_modificacion
FROM pedidosjb_pedidos.producto p
INNER JOIN pedidosjb_pedidos.marca m 
    ON p.idmarca = m.idmarca
LEFT JOIN pedidosjb_pedidos.temporada t 
    ON p.idtemporada = t.idtemporada
INNER JOIN pedidosjb_pedidos.set_talla st 
    ON p.idset_talla = st.idset_talla
LEFT JOIN pedidosjb_pedidos.color c 
    ON p.idcolor = c.idcolor
LEFT JOIN pedidosjb_pedidos.corte co 
    ON p.idcorte = co.idcorte
LEFT JOIN pedidosjb_pedidos.tipo_suela ts 
    ON p.idtipo_suela = ts.idtipo_suela
LEFT JOIN pedidosjb_pedidos.concepto cp 
    ON p.idconcepto = cp.idconcepto;

CREATE OR REPLACE VIEW pedidosjb_pedidos.view_producto_precio AS
SELECT 
    pp.idproducto_precio,
    pp.idproducto,
    p.modelo,
    pp.material,
    pp.precio,
    pp.estado,
    pp.fecha_creacion,
    pp.usuario_creacion,
    pp.fecha_modificacion,
    pp.usuario_modificacion
FROM pedidosjb_pedidos.producto_precio pp
INNER JOIN pedidosjb_pedidos.producto p 
    ON pp.idproducto = p.idproducto;