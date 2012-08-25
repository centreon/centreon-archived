--
-- Update Centreon-Broker debug value name	
--	
UPDATE `cb_list_values` SET `value_value` = 'low' WHERE `cb_list_id` = 4 AND `value_name` = 'Base';	
UPDATE `cb_list_values` SET `value_value` = 'high' WHERE `cb_list_id` = 4 AND `value_name` = 'Very detailed';

UPDATE `informations` SET `value` = '2.3.3' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.2' LIMIT 1;