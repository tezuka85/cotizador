ALTER TABLE `claro_envios`.`costos_mensajerias_porcentajes`
ADD COLUMN `status` VARCHAR(45) NULL AFTER `updated_at`;

ALTER TABLE `claro_envios`.`costos_mensajerias_porcentajes`
CHANGE COLUMN `status` `status` VARCHAR(45) COLLATE 'utf8mb4_unicode_ci' NULL DEFAULT '1' ;

SET SQL_SAFE_UPDATES=0;
use claro_envios;
update costos_mensajerias_porcentajes set status=1 where 1;
SET SQL_SAFE_UPDATES=1;

ALTER TABLE `claro_envios`.`costos_mensajerias_porcentajes`
ADD COLUMN `tipo` VARCHAR(45) NULL AFTER `status`;

INSERT INTO `claro_envios`.`negociaciones` (`id`, `clave`, `descripcion`, `usuario_id`) VALUES ('3', 'COM_003', 'Mixto', '1');


