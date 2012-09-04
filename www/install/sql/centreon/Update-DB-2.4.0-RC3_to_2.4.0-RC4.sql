UPDATE `cfg_nagios` SET `check_for_orphaned_hosts` = '0' WHERE `check_for_orphaned_hosts` IS NULL;

UPDATE `informations` SET `value` = '2.4.0-RC4' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.0-RC3' LIMIT 1;
