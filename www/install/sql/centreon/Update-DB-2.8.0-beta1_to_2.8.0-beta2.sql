-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.0-beta1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.7.6' LIMIT 1;

-- Remove failover field from graphite broker output
DELETE cbfr FROM cb_type_field_relation cbfr
INNER JOIN cb_field cbf ON cbfr.cb_field_id=cbf.cb_field_id
INNER JOIN cb_type cbt ON cbfr.cb_type_id=cbt.cb_type_id
AND cbf.fieldname = 'failover'
AND cbt.type_shortname = 'graphite';
