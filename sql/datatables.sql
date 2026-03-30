
use pedidosjb_seguridad; 
CREATE TABLE `datatables` (
    `usuario` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `tabla` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `estado` json NOT NULL,
    PRIMARY KEY (`usuario`,`tabla`),
    CONSTRAINT `fk_datatables_usuario` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;