UPDATE `oreon_informations` SET `value` = '1.4.1' WHERE CONVERT( `oreon_informations`.`key` USING utf8 ) = 'version' AND CONVERT( `oreon_informations`.`value` USING utf8 ) = '1.4.1-RC3' LIMIT 1 ;
