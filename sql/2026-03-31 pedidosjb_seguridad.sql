INSERT INTO pedidosjb_seguridad.opcion (idopcion,idmenu,nombre,entity,funcion,orden,estado)
	VALUES (10,2,'Producto','producto','cargar_opcion_producto',10,'ACTIVO');

INSERT INTO pedidosjb_seguridad.accion (idaccion,idopcion,nombre,indOpcion,estado)VALUES 
	(48,10,'Opcion producto','SI','ACTIVO'),
	(49,10,'Crear producto','NO','ACTIVO'),
	(50,10,'Modificar producto','NO','ACTIVO'),
	(51,10,'Cambiar estado producto','NO','ACTIVO'),
	(52,10,'Crear producto precio','NO','ACTIVO'),
	(53,10,'Modificar producto precio','NO','ACTIVO');