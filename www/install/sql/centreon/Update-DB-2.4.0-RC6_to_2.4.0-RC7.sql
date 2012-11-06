-- Update 'RRD length' field definition
UPDATE cb_field SET `external` = 'D=centreon_storage:T=config:C=len_storage_rrd:RPN=86400 *:CK=id:K=1' WHERE cb_field_id=17;

UPDATE `informations` SET `value` = '2.4.0-RC7' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.4.0-RC6' LIMIT 1;