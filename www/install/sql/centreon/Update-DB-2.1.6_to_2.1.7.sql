UPDATE `contact_param` SET cp_value = '0' WHERE `cp_key` LIKE 'monitoring_default_poller' AND cp_value = 'ALL';

UPDATE `informations` SET `value` = '2.1.7' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.1.6' LIMIT 1;