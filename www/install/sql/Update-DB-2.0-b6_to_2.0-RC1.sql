
DELETE FROM topology WHERE topology_page = 50103;

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Modules', NULL, 5, 507, 10, 1, NULL, NULL, '0', '0', '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Modules', NULL, 507, NULL, NULL, 1, NULL, NULL, NULL, NULL, '1', NULL, NULL, NULL);
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`) VALUES(NULL, 'Setup', './img/icones/16x16/press.gif', 507, 50701, 10, 1, './include/options/oreon/modules/modules.php', NULL, NULL, NULL, '1', NULL, NULL, NULL);


UPDATE `topology` SET `topology_order` = '12' WHERE `topology`.`topology_page` = 502 LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '15' WHERE `topology`.`topology_page` = 503 LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '14' WHERE `topology`.`topology_page` = 504 LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '15' WHERE `topology`.`topology_page` = 505 LIMIT 1 ;
UPDATE `topology` SET `topology_order` = '16' WHERE `topology`.`topology_page` = 506 LIMIT 1 ;

UPDATE `topology` SET `topology_icone` = './img/icones/16x16/text_code.gif' WHERE `topology`.`topology_page` = 20301 LIMIT 1 ;

-- 
-- Update Centreon version
-- 

UPDATE `informations` SET `value` = '2.0-RC1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.0-b6' LIMIT 1;
