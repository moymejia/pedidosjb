USE pedidosjb_pedidos;

SET SQL_SAFE_UPDATES = 0;

SET @idset_incorrecto = 65;
SET @idset_correcto = 95;

START TRANSACTION;

UPDATE pedido_detalle
SET idset_talla = @idset_correcto
WHERE idset_talla = @idset_incorrecto;

DELETE pp65
FROM producto_precio AS pp65
INNER JOIN producto_precio AS pp95
    ON pp95.idproducto = pp65.idproducto
   AND pp95.idset_talla = @idset_correcto
WHERE pp65.idset_talla = @idset_incorrecto;

DELETE pp_antiguo
FROM producto_precio AS pp_antiguo
INNER JOIN producto_precio AS pp_reciente
    ON pp_reciente.idproducto = pp_antiguo.idproducto
   AND pp_reciente.idset_talla = pp_antiguo.idset_talla
   AND pp_reciente.idproducto_precio > pp_antiguo.idproducto_precio
WHERE pp_antiguo.idset_talla = @idset_incorrecto;

UPDATE producto_precio
SET
    idset_talla = @idset_correcto,
    fecha_modificacion = NOW(),
    usuario_modificacion = 'admin'
WHERE idset_talla = @idset_incorrecto;

DELETE FROM set_talla
WHERE idset_talla IN (69, 89);

COMMIT;

SET SQL_SAFE_UPDATES = 1;USE pedidosjb_pedidos;

SET SQL_SAFE_UPDATES = 0;

SET @idset_incorrecto = 65;
SET @idset_correcto = 95;

START TRANSACTION;

UPDATE pedido_detalle
SET idset_talla = @idset_correcto
WHERE idset_talla = @idset_incorrecto;

DELETE pp65
FROM producto_precio AS pp65
INNER JOIN producto_precio AS pp95
    ON pp95.idproducto = pp65.idproducto
   AND pp95.idset_talla = @idset_correcto
WHERE pp65.idset_talla = @idset_incorrecto;

DELETE pp_antiguo
FROM producto_precio AS pp_antiguo
INNER JOIN producto_precio AS pp_reciente
    ON pp_reciente.idproducto = pp_antiguo.idproducto
   AND pp_reciente.idset_talla = pp_antiguo.idset_talla
   AND pp_reciente.idproducto_precio > pp_antiguo.idproducto_precio
WHERE pp_antiguo.idset_talla = @idset_incorrecto;

UPDATE producto_precio
SET
    idset_talla = @idset_correcto,
    fecha_modificacion = NOW(),
    usuario_modificacion = 'admin'
WHERE idset_talla = @idset_incorrecto;

DELETE FROM set_talla
WHERE idset_talla IN (69, 89);

COMMIT;

SET SQL_SAFE_UPDATES = 1;