ALTER TABLE `connector` MODIFY `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `connector` MODIFY `description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE `connector` MODIFY `command_line` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci;

UPDATE `connector` SET `command_line` = '$USER3$/centreon_connector_perl' WHERE `command_line` = '$USER2$/centreon_connector_perl';
UPDATE `connector` SET `command_line` = '$USER3$/centreon_connector_ssh' WHERE `command_line` = '$USER2$/centreon_connector_ssh';

UPDATE `informations` SET `value` = '2.4.0-RC3' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.0-RC2' LIMIT 1;