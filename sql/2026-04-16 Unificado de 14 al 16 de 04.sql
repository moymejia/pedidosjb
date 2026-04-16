
---
--seguridad
---
INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES(247, 52, 'opcion_ventas_temporada', 'SI', 'idpedido', 'idmarca', 'idtemporada', 'ACTIVO');

INSERT INTO pedidosjb_seguridad.rol_accion
(idrol, idaccion, indFavorito)
VALUES(1, 247, 'NO');

-----------------------------------------
--pedidos
-----------------------------------------

ALTER TABLE pedidosjb_pedidos.marca
DROP FOREIGN KEY fk_marca_set_talla_preferido;

Alter table pedidosjb_pedidos.marca
drop column idset_talla_preferido;


ALTER TABLE pedidosjb_pedidos.producto
DROP FOREIGN KEY fk_producto_set_talla;

ALTER TABLE pedidosjb_pedidos.producto
DROP INDEX idx_producto_idset_talla;

ALTER TABLE pedidosjb_pedidos.producto
DROP COLUMN idset_talla;

REPLACE INTO pedidosjb_pedidos.temporada
(idtemporada, nombre, fecha_inicio, fecha_fin, estado, fecha_creacion, usuario_creacion, fecha_modificacion, usuario_modificacion)
VALUES
(100, 'DESPACHO INMEDIATO', '0001-01-01', '0001-01-01', 'PROTEGIDO', '2026-04-13 16:47:52', 'admin', NULL, NULL);

CREATE OR REPLACE VIEW pedidosjb_pedidos.view_producto_precio AS
SELECT
    pp.idproducto_precio,
    pp.idproducto,
    p.modelo,
    p.idmarca,  
    pp.idset_talla,
    pp.precio,
    pp.estado,
    pp.fecha_creacion,
    pp.usuario_creacion,
    pp.fecha_modificacion,
    pp.usuario_modificacion,
    (CASE
        WHEN (st.descripcion IS NULL OR st.descripcion = '')
        THEN st.grupo
        ELSE CONCAT(st.grupo, ' - ', st.descripcion)
    END) AS set_talla
FROM pedidosjb_pedidos.producto_precio pp
JOIN pedidosjb_pedidos.producto p 
    ON pp.idproducto = p.idproducto
JOIN pedidosjb_pedidos.set_talla st 
    ON pp.idset_talla = st.idset_talla;

    CREATE OR REPLACE
ALGORITHM = UNDEFINED VIEW `pedidosjb_pedidos`.`view_ventas_temporada` AS
select
    `p`.`nopedido` AS `nopedido`,
    `p`.`idpedido` AS `idpedido`,
    `p`.`idcliente` AS `idcliente`,
    `p`.`monto_total` AS `monto_total`,
    concat(`c`.`codigo`, ' - ', `c`.`nombre`) AS `nombre_cliente`,
    `p`.`idmarca` AS `idmarca`,
    `m`.`nombre` AS `nombre_marca`,
    `p`.`idtemporada` AS `idtemporada`,
    `p`.`total_pares` AS `cantidad_pares`,
    count(distinct `pr`.`modelo`) AS `cantidad_modelos`,
    `p`.`estado` AS `estado`
from
    ((((`pedidosjb_pedidos`.`pedido` `p`
join `pedidosjb_pedidos`.`marca` `m` on
    ((`m`.`idmarca` = `p`.`idmarca`)))
left join `pedidosjb_pedidos`.`cliente` `c` on
    ((`c`.`idcliente` = `p`.`idcliente`)))
left join `pedidosjb_pedidos`.`pedido_detalle` `pd` on
    ((`pd`.`idpedido` = `p`.`idpedido`)))
left join `pedidosjb_pedidos`.`producto` `pr` on
    ((`pr`.`idproducto` = `pd`.`idproducto`)))
group by
    `p`.`idpedido`,
    `p`.`idcliente`,
    `c`.`nombre`,
    `p`.`idmarca`,
    `m`.`nombre`,
    `p`.`idtemporada`,
    `p`.`total_pares`,
    `p`.`estado`;

-- =========================================
-- fix bitacora
-- =========================================

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

-- =========================================
-- SCRIPT REINSERTAR ACCIONES (IDEMPOTENTE)
-- =========================================

INSERT INTO pedidosjb_seguridad.accion
(idaccion, idopcion, nombre, indOpcion, referencia1, referencia2, referencia3, estado)
VALUES
(1,1,'opcion usuarios','SI',NULL,NULL,NULL,'ACTIVO'),
(2,1,'Crear_usuario','NO','usuario',NULL,NULL,'ACTIVO'),
(3,1,'Modificar_usuario','NO','usuario',NULL,NULL,'ACTIVO'),
(4,1,'Cambiar_clave_usuario','NO','usuario',NULL,NULL,'ACTIVO'),
(5,2,'opcion roles de usuario','SI',NULL,NULL,NULL,'ACTIVO'),
(6,2,'Crear_rol','NO','rol',NULL,NULL,'ACTIVO'),
(7,2,'Modificar_rol','NO','rol',NULL,NULL,'ACTIVO'),
(8,3,'opcion permisos por rol','SI',NULL,NULL,NULL,'ACTIVO'),
(9,3,'Cargar_permisos_rol','NO','rol',NULL,NULL,'ACTIVO'),
(10,3,'Agregar_permiso_accion','NO','rol','accion',NULL,'ACTIVO'),
(11,3,'Retirar_permiso_accion','NO','rol','accion',NULL,'ACTIVO'),
(12,1,'Cambiar_estado_usuario','NO','usuario','estado nuevo',NULL,'ACTIVO'),
(13,2,'Cambiar_estado_rol','NO','rol',NULL,NULL,'ACTIVO'),
(14,3,'Agregar_opcion_accion','NO','rol','accion',NULL,'ACTIVO'),
(15,3,'Retirar_opcion_accion','NO','rol','accion',NULL,'ACTIVO'),
(16,4,'opcion Bitacora','SI',NULL,NULL,NULL,'ACTIVO'),
(17,4,'Consultar_bitacora','NO','usuario','desde','hasta','ACTIVO'),
(18,5,'opcion configuracion','SI',NULL,NULL,NULL,'ACTIVO'),
(19,5,'Modificar_configuracion','NO','clave',NULL,NULL,'ACTIVO'),
(20,6,'Opcion orden de menus','SI',NULL,NULL,NULL,'ACTIVO'),
(21,6,'Modificar_orden_de_menus','NO','menu',NULL,NULL,'ACTIVO'),
(22,7,'Opcion carga productos','SI',NULL,NULL,NULL,'ACTIVO'),
(23,7,'Cargar_productos','NO',NULL,NULL,NULL,'ACTIVO'),
(24,7,'Crear_producto_carga_productos','NO',NULL,NULL,NULL,'ACTIVO'),
(25,7,'Crear_color_producto_precio_carga_productos','NO',NULL,NULL,NULL,'ACTIVO'),
(26,7,'Eliminar_productos_borrador_carga_productos','NO',NULL,NULL,NULL,'ACTIVO'),
(27,7,'Activar_productos_carga_productos','NO',NULL,NULL,NULL,'ACTIVO'),
(28,7,'Modificar_producto_carga_productos','NO',NULL,NULL,NULL,'ACTIVO'),
(29,7,'Crear_producto_precio_carga_productos','NO',NULL,NULL,NULL,'ACTIVO'),
(30,7,'Eliminar productos','NO',NULL,NULL,NULL,'ACTIVO'),
(31,7,'Activar productos','NO',NULL,NULL,NULL,'ACTIVO'),
(32,7,'Crear set de talla','NO',NULL,NULL,NULL,'ACTIVO'),
(33,7,'Crear detalle set de talla','NO',NULL,NULL,NULL,'ACTIVO'),
(34,7,'Modificar producto','NO',NULL,NULL,NULL,'ACTIVO'),
(35,7,'Modificar producto precio','NO',NULL,NULL,NULL,'ACTIVO'),
(36,8,'Opcion_cliente','SI','idcliente',NULL,NULL,'ACTIVO'),
(37,8,'Crear_cliente','NO','idcliente',NULL,NULL,'ACTIVO'),
(38,8,'Modificar_cliente','NO','idcliente',NULL,NULL,'ACTIVO'),
(39,8,'Cambiar_estado_cliente','NO','idcliente',NULL,NULL,'ACTIVO'),
(40,9,'Opcion pedido','SI',NULL,NULL,NULL,'ACTIVO'),
(41,9,'Crear_pedido','NO',NULL,NULL,NULL,'ACTIVO'),
(42,9,'Eliminar_pedido','NO',NULL,NULL,NULL,'ACTIVO'),
(43,9,'Cerrar_pedido','NO',NULL,NULL,NULL,'ACTIVO'),
(44,9,'Crear_detalle_pedido','NO',NULL,NULL,NULL,'ACTIVO'),
(45,9,'Eliminar_detalle_pedido','NO',NULL,NULL,NULL,'ACTIVO'),
(46,9,'Modificar_detalle_pedido','NO',NULL,NULL,NULL,'ACTIVO'),
(47,9,'Imprimir_pedido','NO',NULL,NULL,NULL,'ACTIVO'),
(48,10,'Opcion producto','SI',NULL,NULL,NULL,'ACTIVO'),
(49,10,'Crear_producto_mantenimiento','NO',NULL,NULL,NULL,'ACTIVO'),
(50,10,'Modificar_producto_mantenimiento','NO',NULL,NULL,NULL,'ACTIVO'),
(51,10,'Cambiar_estado_producto_mantenimiento','NO',NULL,NULL,NULL,'ACTIVO'),
(52,10,'Crear_producto_precio_mantenimiento','NO',NULL,NULL,NULL,'ACTIVO'),
(53,10,'Modificar_producto_precio_mantenimiento','NO',NULL,NULL,NULL,'ACTIVO'),
(190,39,'Opcion_tipo_contacto','SI','idtipo_contacto',NULL,NULL,'ACTIVO'),
(191,39,'Crear_tipo_contacto','NO','idtipo_contacto',NULL,NULL,'ACTIVO'),
(192,39,'Modificar_tipo_contacto','NO','idtipo_contacto',NULL,NULL,'ACTIVO'),
(193,39,'Cambiar_estado_tipo_contacto','NO','idtipo_contacto',NULL,NULL,'ACTIVO'),
(194,40,'Opcion_temporada','SI','idtemporada','fecha_inicio','','ACTIVO'),
(195,40,'Crear_temporada','NO','idtemporada','fecha_inicio','','ACTIVO'),
(196,40,'Modificar_temporada','NO','idtemporada','fecha_inicio','fecha_final','ACTIVO'),
(197,40,'Cambiar_estado_temporada','NO','idtemporada','estado','','ACTIVO'),
(198,41,'Opcion_talla','SI','idtalla',NULL,NULL,'ACTIVO'),
(199,41,'Crear_talla','NO','idtalla','numero',NULL,'ACTIVO'),
(200,41,'Modificar_talla','NO','idtalla','numero',NULL,'ACTIVO'),
(201,41,'Cambiar_estado_talla','NO','idtalla','estado',NULL,'ACTIVO'),
(202,42,'Opcion_tipo_pago','SI','idtipo_pago',NULL,NULL,'ACTIVO'),
(203,42,'Crear_tipo_pago','NO','idtipo_pago','descripcion',NULL,'ACTIVO'),
(204,42,'Modificar_tipo_pago','NO','idtipo_pago','descrpcion',NULL,'ACTIVO'),
(205,42,'Cambiar_estado_tipo_pago','NO','idtipo_pago','estado',NULL,'ACTIVO'),
(206,43,'Opcion_forma_pago','SI','idforma_pago',NULL,NULL,'ACTIVO'),
(207,43,'Crear_forma_pago','NO','idforma_pago','descripcion',NULL,'ACTIVO'),
(208,43,'Modificar_forma_pago','NO','idforma_pago','descripcion',NULL,'ACTIVO'),
(209,43,'Cambiar_estado_forma_pago','NO','idforma_pago','estado',NULL,'ACTIVO'),
(210,44,'Opcion_marca','SI','idmarca',NULL,NULL,'ACTIVO'),
(211,44,'Crear_marca','NO','idmarca','nombre',NULL,'ACTIVO'),
(212,44,'Modificar_marca','NO','idmarca','nombre','','ACTIVO'),
(213,44,'Cambiar_estado_marca','NO','idmarca','estado',NULL,'ACTIVO'),
(214,45,'Opcion_set_talla','SI',NULL,NULL,NULL,'ACTIVO'),
(215,45,'Crear_set_talla','NO',NULL,NULL,NULL,'ACTIVO'),
(216,45,'Modificar_set_talla','NO',NULL,NULL,NULL,'ACTIVO'),
(217,45,'Cambiar_estado_set_talla','NO',NULL,NULL,NULL,'ACTIVO'),
(220,45,'Cargar_tallas_set_talla','NO','idset_talla_detalle','idset_talla','id_talla','ACTIVO'),
(221,45,'Agregar_talla','NO','idset_talla_detalle','idset_talla','id_talla','ACTIVO'),
(222,45,'Retirar_talla','NO','idset_talla_detalle','idset_talla','id_talla','ACTIVO'),
(223,46,'Opcion_transporte','SI','idtransporte',NULL,NULL,'ACTIVO'),
(224,46,'Crear_transporte','NO','idtransporte',NULL,NULL,'ACTIVO'),
(225,46,'Modificar_transporte','NO','idtransporte','nombre',NULL,'ACTIVO'),
(226,46,'Cambiar_estado_transporte','NO','idtransporte','estado',NULL,'ACTIVO'),
(227,47,'Opcion color','SI','idcolor',NULL,NULL,'ACTIVO'),
(228,47,'Crear_color','NO','idcolor','nombre',NULL,'ACTIVO'),
(229,47,'Modificar_color','NO','idcolor','nombre',NULL,'ACTIVO'),
(230,47,'Cambiar_estado_color','NO','idcolor','estado',NULL,'ACTIVO'),
(231,48,'Opcion_concepto','SI','idconcepto',NULL,NULL,'ACTIVO'),
(232,48,'Crear_concepto','NO','idconcepto','nombre',NULL,'ACTIVO'),
(233,48,'Modificar_concepto','NO','idconcepto','nombre',NULL,'ACTIVO'),
(234,48,'Cambiar_estado_concepto','NO','idconcepto','estado',NULL,'ACTIVO'),
(235,49,'Opcion_corte','SI','idconcepto',NULL,NULL,'ACTIVO'),
(236,49,'Crear_corte','NO','idconcepto','nombre',NULL,'ACTIVO'),
(237,49,'Modificar_corte','NO','idconcepto','nombre',NULL,'ACTIVO'),
(238,49,'Cambiar_estado_corte','NO','idconcepto','estado',NULL,'ACTIVO'),
(239,50,'Opcion_tipo_suela','SI','idtipo_suela',NULL,NULL,'ACTIVO'),
(240,50,'Crear_tipo_suela','NO','idtipo_suela','nombre',NULL,'ACTIVO'),
(241,50,'Modificar_tipo_suela','NO','idtipo_suela','nombre',NULL,'ACTIVO'),
(242,50,'Cambiar_estado_tipo_suela','NO','idtipo_suela','estado',NULL,'ACTIVO'),
(243,51,'Opcion_despacho','SI',NULL,NULL,NULL,'ACTIVO'),
(244,51,'Crear_despacho','NO',NULL,NULL,NULL,'ACTIVO'),
(245,51,'Despachar_lineas','NO',NULL,NULL,NULL,'ACTIVO'),
(246,51,'Cerrar_despacho','NO',NULL,NULL,NULL,'ACTIVO'),
(247,52,'Opcion_ventas_temporada','SI','idpedido','idmarca','idtemporada','ACTIVO'),
(248,8,'Crear_cliente_contacto','NO',NULL,NULL,NULL,'ACTIVO'),
(249,8,'Modificar_cliente_contacto','NO',NULL,NULL,NULL,'ACTIVO'),
(250,8,'Cambiar_estado_cliente_contacto','NO',NULL,NULL,NULL,'ACTIVO'),
(251,10,'Cambiar_estado_producto_precio','NO',NULL,NULL,NULL,'ACTIVO')

ON DUPLICATE KEY UPDATE
idopcion=VALUES(idopcion),
nombre=VALUES(nombre),
indOpcion=VALUES(indOpcion),
referencia1=VALUES(referencia1),
referencia2=VALUES(referencia2),
referencia3=VALUES(referencia3),
estado=VALUES(estado);
