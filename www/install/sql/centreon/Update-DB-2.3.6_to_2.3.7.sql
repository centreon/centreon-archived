INSERT INTO nagios_macro (`macro_name`) VALUES ('$_HOSTHOST_ID$'), ('$_SERVICESERVICE_ID$');

UPDATE `informations` SET `value` = '2.3.7' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.6' LIMIT 1;