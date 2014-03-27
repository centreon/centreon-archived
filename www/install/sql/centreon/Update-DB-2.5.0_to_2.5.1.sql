
-- #5241
ALTER TABLE `nagios_server` CHANGE `init_script_snmptt` `init_script_centreontrapd` VARCHAR(255);

-- #5243
UPDATE nagios_server SET init_script_centreontrapd = "/etc/init.d/centreontrapd" WHERE init_script_centreontrapd = "/etc/init.d/snmptt";

-- #5374
UPDATE topology SET readonly = '0' WHERE topology_page IN (60104, 60106, 60216);
UPDATE topology SET readonly = '1' WHERE topology_page IN (60804, 60708);

-- #5399
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (601,NULL,'./include/common/javascript/jquery/plugins/sheepit/jquery.sheepItPlugin.min.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (601,NULL,'./include/common/javascript/centreon/doClone.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (602,NULL,'./include/common/javascript/jquery/plugins/sheepit/jquery.sheepItPlugin.min.js',NULL);
INSERT INTO `topology_JS` (`id_page`, `o`, `PathName_js`, `Init`) VALUES (602,NULL,'./include/common/javascript/centreon/doClone.js',NULL);

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.5.1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.5.0' LIMIT 1;
