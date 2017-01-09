--
-- Add size_to_max option to graph templates
--
ALTER TABLE `giv_graphs_template` ADD COLUMN `size_to_max` TINYINT(6) NOT NULL AFTER upper_limit;

--
-- Add password type for Centreon Broker field configuration
--
UPDATE  `cb_field` SET  `fieldtype` =  'password' WHERE  `cb_field_id` = 9;

--
-- Insert DB Port for Centreon Broker Configuration
--
INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES (14, 18, 1, 5);
INSERT INTO `cb_type_field_relation` (`cb_type_id`, `cb_field_id`, `is_required`, `order_display`) VALUES (16, 18, 1, 3);

--
-- Update default information for Centreon Broker configuration in poller
-- 
UPDATE `nagios_server` SET `centreonbroker_cfg_path` = '/etc/centreon-broker', `centreonbroker_module_path` = '/usr/share/centreon/lib/centreon-broker';

--
-- Update progress bar lib
--
UPDATE `topology_JS` SET `PathName_js` = './include/common/javascript/scriptaculous/jsProgressBarHandler.js ' WHERE `PathName_js` = './include/common/javascript/scriptaculous/s2.js ';

UPDATE contact SET contact_enable_notifications = '1';
UPDATE contact SET contact_register = '1';

UPDATE `informations` SET `value` = '2.3.0-RC3' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.0-RC2' LIMIT 1;