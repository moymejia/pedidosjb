USE pedidosjb_seguridad;

ALTER TABLE datatables
    ADD COLUMN nombre_estado VARCHAR(100) NOT NULL DEFAULT 'default' AFTER tabla,
    ADD COLUMN fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER estado,
    ADD COLUMN fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER fecha_creacion;

ALTER TABLE datatables
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (usuario, tabla, nombre_estado);