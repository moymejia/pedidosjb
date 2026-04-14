-- Fecha: 2026-04-10 
-- Descripcion: mantenimiento integral accion/rol_accion (columna nombre, indices y triggers de sincronizacion)
-- Estrategia: recreacion segura (drop + create) para permitir re-ejecucion sin errores por duplicados.

-- Configuracion:
-- Reemplaza el prefijo del esquema en todo el archivo:
--   pedidosjb_  ->  tu_prefijo_
-- Ejemplo: pedidosjb_seguridad -> demo_seguridad

-- Validacion previa sugerida para unique en accion.nombre (si retorna filas, corregir antes):
-- SELECT nombre, COUNT(1) cantidad
-- FROM pedidosjb_seguridad.accion
-- GROUP BY nombre
-- HAVING COUNT(1) > 1;


-- agregar y quitar permisos desde el sistema para revisar que todo funciona correctamente.

DROP TRIGGER IF EXISTS pedidosjb_seguridad.bi_rol_accion_nombre; 
DROP TRIGGER IF EXISTS pedidosjb_seguridad.bu_rol_accion_nombre;
DROP TRIGGER IF EXISTS pedidosjb_seguridad.au_accion_sync_nombre_rol_accion;

ALTER TABLE pedidosjb_seguridad.rol_accion DROP INDEX IF EXISTS rol_accion_idrol_nombre_idx;
ALTER TABLE pedidosjb_seguridad.accion     DROP INDEX IF EXISTS accion_nombre_uq;
ALTER TABLE pedidosjb_seguridad.rol_accion DROP COLUMN IF EXISTS nombre;
ALTER TABLE pedidosjb_seguridad.rol_accion ADD COLUMN nombre varchar(100) NULL AFTER idaccion;

UPDATE pedidosjb_seguridad.rol_accion ra
SET ra.nombre = (
    SELECT a.nombre
    FROM pedidosjb_seguridad.accion a
    WHERE a.idaccion = ra.idaccion
);

CREATE INDEX rol_accion_idrol_nombre_idx ON pedidosjb_seguridad.rol_accion (idrol, nombre);

CREATE UNIQUE INDEX accion_nombre_uq ON pedidosjb_seguridad.accion (nombre);

CREATE TRIGGER pedidosjb_seguridad.bi_rol_accion_nombre
BEFORE INSERT ON pedidosjb_seguridad.rol_accion
FOR EACH ROW
SET NEW.nombre = (
    SELECT a.nombre
    FROM pedidosjb_seguridad.accion a
    WHERE a.idaccion = NEW.idaccion
);

CREATE TRIGGER pedidosjb_seguridad.bu_rol_accion_nombre
BEFORE UPDATE ON pedidosjb_seguridad.rol_accion
FOR EACH ROW
SET NEW.nombre = (
    SELECT a.nombre
    FROM pedidosjb_seguridad.accion a
    WHERE a.idaccion = NEW.idaccion
);

CREATE TRIGGER pedidosjb_seguridad.au_accion_sync_nombre_rol_accion
AFTER UPDATE ON pedidosjb_seguridad.accion
FOR EACH ROW
UPDATE pedidosjb_seguridad.rol_accion
SET nombre = NEW.nombre
WHERE idaccion = NEW.idaccion AND (nombre IS NULL OR nombre <> NEW.nombre)  AND NOT (OLD.nombre <=> NEW.nombre);
