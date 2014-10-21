UPDATE cb_field SET external = 'T=options:C=value:CK=key:K=index_data' WHERE cb_field_id = 43;

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.5.3' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.5.2' LIMIT 1;
