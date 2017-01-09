ALTER TABLE ws_token DROP FOREIGN KEY ws_token_ibfk_1;
ALTER TABLE ws_token DROP PRIMARY KEY;
ALTER TABLE ws_token ADD CONSTRAINT ws_token_idfk_1 FOREIGN KEY (contact_id) REFERENCES contact (contact_id) ON DELETE CASCADE;

UPDATE topology SET topology_url = 'https://github.com/centreon/centreon/issues' WHERE topology_url = 'http://forge.centreon.com/';

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.6.4' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.6.3' LIMIT 1;
