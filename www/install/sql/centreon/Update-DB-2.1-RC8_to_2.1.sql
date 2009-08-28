UPDATE `topology` SET topology_url = 'http://trac.centreon.com/' WHERE topology_url LIKE 'http://bugs.centreon.com%'; 

UPDATE `informations` SET `value` = '2.1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.1-RC8' LIMIT 1;

