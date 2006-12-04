-- 04/12/2006 --- 

UPDATE `oreon_informations` SET `value` = '1.3.3' WHERE CONVERT( `key` USING utf8 ) = 'version' AND CONVERT( `value` USING utf8 ) = '1.3.2' LIMIT 1 ;