INSERT INTO pedidosjb_seguridad.opcion (idopcion,idmenu,nombre,entity,funcion,orden,estado)
	VALUES (7,2,'Carga productos','producto','cargar_opcion',7,'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion (idaccion,idopcion,nombre,indOpcion,estado)
	VALUES (22,7,'Opcion carga productos','SI','ACTIVO')
    ,(23,7,'Cargar productos','NO','ACTIVO')
	,(24,7,'Crear producto','NO','ACTIVO')
	,(25,7,'Crear producto precio','NO','ACTIVO')
	,(26,7,'Eliminar productos','NO','ACTIVO')
	,(27,7,'Activar productos','NO','ACTIVO')
	,(28,7,'Modificar producto','NO','ACTIVO')
	,(29,7,'Modificar producto precio','NO','ACTIVO');