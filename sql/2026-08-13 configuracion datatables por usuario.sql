USE `pedidosjb_seguridad`;

DELIMITER $$
CREATE PROCEDURE `validar_admin_datatable_configuracion`()
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM `usuario`
        WHERE `usuario` = 'admin'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No existe el usuario admin; no se puede migrar datatable_configuracion.';
    END IF;
END$$
DELIMITER ;

CALL `validar_admin_datatable_configuracion`();
DROP PROCEDURE `validar_admin_datatable_configuracion`;

DROP VIEW IF EXISTS `view_datatable_configuracion`;

ALTER TABLE `datatable_configuracion_detalle`
    DROP FOREIGN KEY `fk_datatable_configuracion_detalle_encabezado`,
    DROP INDEX `uk_datatable_configuracion_detalle_parametro`;

ALTER TABLE `datatable_configuracion`
    DROP PRIMARY KEY,
    ADD COLUMN `usuario` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin' FIRST,
    ADD PRIMARY KEY (`usuario`, `idtabla`),
    ADD CONSTRAINT `fk_datatable_configuracion_usuario`
        FOREIGN KEY (`usuario`) REFERENCES `usuario` (`usuario`) ON UPDATE CASCADE;

ALTER TABLE `datatable_configuracion_detalle`
    ADD COLUMN `usuario` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin' AFTER `idtabla_detalle`,
    ADD UNIQUE KEY `uk_datatable_configuracion_detalle_parametro` (`usuario`, `idtabla`, `parametro`),
    ADD CONSTRAINT `fk_datatable_configuracion_detalle_encabezado`
        FOREIGN KEY (`usuario`, `idtabla`)
        REFERENCES `datatable_configuracion` (`usuario`, `idtabla`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_datatable_configuracion_detalle_usuario`
        FOREIGN KEY (`usuario`) REFERENCES `usuario` (`usuario`) ON UPDATE CASCADE;

ALTER TABLE `datatable_configuracion`
    ALTER COLUMN `usuario` DROP DEFAULT;

ALTER TABLE `datatable_configuracion_detalle`
    ALTER COLUMN `usuario` DROP DEFAULT;

CREATE OR REPLACE VIEW `view_datatable_configuracion` AS
SELECT
    `c`.`usuario`,
    `c`.`idtabla`,
    `d`.`idtabla_detalle`,
    `d`.`parametro`,
    `v`.`clave`,
    CASE
        WHEN `v`.`idtabla_detalle_valor` IS NULL THEN `d`.`valor`
        ELSE `v`.`valor`
    END AS `valor`,
    `v`.`orden`
FROM `datatable_configuracion` `c`
LEFT JOIN `datatable_configuracion_detalle` `d`
    ON `d`.`usuario` = `c`.`usuario`
   AND `d`.`idtabla` = `c`.`idtabla`
LEFT JOIN `datatable_configuracion_detalle_valor` `v`
    ON `v`.`idtabla_detalle` = `d`.`idtabla_detalle`;

