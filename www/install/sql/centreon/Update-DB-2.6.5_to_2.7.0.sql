-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.0' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.6.5' LIMIT 1;

ALTER TABLE options ENGINE=InnoDB;
ALTER TABLE css_color_menu ENGINE=InnoDB;

alter table custom_views add `public` tinyint(6) null default 0;

ALTER TABLE timeperiod_exclude_relations
ADD FOREIGN KEY (timeperiod_id)
REFERENCES timeperiod(tp_id) ON DELETE CASCADE;

ALTER TABLE timeperiod_exclude_relations
ADD FOREIGN KEY (timeperiod_exclude_id)
REFERENCES timeperiod(tp_id) ON DELETE CASCADE;


ALTER TABLE timeperiod_include_relations
ADD FOREIGN KEY (timeperiod_id)
REFERENCES timeperiod(tp_id) ON DELETE CASCADE;

ALTER TABLE timeperiod_include_relations
ADD FOREIGN KEY (timeperiod_include_id)
REFERENCES timeperiod(tp_id) ON DELETE CASCADE;

ALTER TABLE on_demand_macro_host MODIFY COLUMN host_macro_value VARCHAR(4096);
ALTER TABLE on_demand_macro_service MODIFY COLUMN svc_macro_value VARCHAR(4096);

ALTER TABLE `on_demand_macro_host` ADD COLUMN `description` text DEFAULT NULL AFTER `is_password`;
ALTER TABLE `on_demand_macro_service` ADD COLUMN `description` text DEFAULT NULL AFTER `is_password`;

CREATE TABLE `traps_group` (
  `traps_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `traps_group_name` varchar(255) NOT NULL,
  PRIMARY KEY (traps_group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `traps_group_relation` (
  `traps_group_id` int(11) NOT NULL,
  `traps_id` int(11) NOT NULL,
  KEY `traps_group_id` (`traps_group_id`),
  KEY `traps_id` (`traps_id`),
  CONSTRAINT `traps_group_relation_ibfk_1` FOREIGN KEY (`traps_id`) REFERENCES `traps` (`traps_id`) ON DELETE CASCADE,
  CONSTRAINT `traps_group_relation_ibfk_2` FOREIGN KEY (`traps_group_id`) REFERENCES `traps_group` (`traps_group_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO topology (topology_name, topology_icone, topology_parent, topology_page, topology_order, topology_group, topology_url, topology_popup, topology_modules) VALUES 
('Group', './img/icones/16x16/factory.gif', 617, 61705, 25, 1, './include/configuration/configObject/traps-groups/groups.php', 0, 0);

-- Create table for relation between metaservice and contact
CREATE TABLE `meta_contact` (
  `meta_id` INT NOT NULL,
  `contact_id` INT NOT NULL,
  PRIMARY KEY (`meta_id`, `contact_id`),
  FOREIGN KEY (`meta_id`) REFERENCES `meta_service` (`meta_id`) ON DELETE CASCADE,
  FOREIGN KEY (`contact_id`) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `command` ADD `command_locked` BOOLEAN DEFAULT 0;
ALTER TABLE `host` ADD `host_locked` BOOLEAN DEFAULT 0 AFTER `host_comment`;

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.0' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.6.3' LIMIT 1;

ALTER TABLE `on_demand_macro_host` ADD COLUMN `macro_order` int(11) NULL DEFAULT 0;
ALTER TABLE `on_demand_macro_service` ADD COLUMN `macro_order` int(11) NULL DEFAULT 0;


CREATE TABLE `on_demand_macro_command` (
  `command_macro_id` int(11) NOT NULL AUTO_INCREMENT,
  `command_macro_name` varchar(255) NOT NULL,
  `command_macro_desciption` text DEFAULT NULL,
  `command_command_id` int(11) NOT NULL,
  `command_macro_type` enum('1','2') DEFAULT NULL,
  PRIMARY KEY (`command_macro_id`),
  KEY `command_command_id` (`command_command_id`),
  CONSTRAINT `on_demand_macro_command_ibfk_1` FOREIGN KEY (`command_command_id`) REFERENCES `command` (`command_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `timezone` (
  `timezone_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `timezone_name` varchar(200) NOT NULL,
  `timezone_offset` varchar(200) NOT NULL,
  `timezone_dst_offset` varchar(200) NOT NULL,
  `timezone_description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`timezone_id`),
  UNIQUE KEY `name` (`timezone_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Move downtime page
DELETE FROM topology WHERE topology_page IN ('20218', '20106', '60305');

-- #3787
DELETE FROM topology WHERE topology_page IN ('60902', '60903', '60707', '60804');
DELETE FROM topology WHERE topology_page IS NULL AND topology_name LIKE 'Plugins' AND topology_url IS NULL;
DELETE FROM topology WHERE topology_page IS NULL AND topology_name LIKE 'NDOutils' AND topology_url IS NULL;

-- Add new general option for centreon broker
ALTER TABLE cfg_centreonbroker
ADD COLUMN retention_path varchar(255),
ADD COLUMN stats_activate enum('0','1') DEFAULT '1',
ADD COLUMN correlation_activate enum('0','1') DEFAULT '0';

-- Migrate timezones

-- Europe/London +00:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Europe/London') where contact_location = 0;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Europe/London') where host_location = 0;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Europe/London')  where `key` ='gmt' AND `value` = '0';

-- Europe/Paris +01:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Europe/Paris') where contact_location = 1;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Europe/Paris') where host_location = 1;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Europe/Paris')  where `key` ='gmt' AND `value` = '1';

-- Europe/Athens +02:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Europe/Athens') where contact_location = 2;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Europe/Athens') where host_location = 2;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Europe/Athens')  where `key` ='gmt' AND `value` = '2';

-- Europe/Moscow +03:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Europe/Moscow') where contact_location = 3;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Europe/Moscow') where host_location = 3;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Europe/Moscow')  where `key` ='gmt' AND `value` = '3';

-- Asia/Dubai +04:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Asia/Dubai') where contact_location = 4;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Asia/Dubai') where host_location = 4;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Asia/Dubai')  where `key` ='gmt' AND `value` = '4';

-- Indian/Kerguelen +05:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Indian/Kerguelen') where contact_location = 5;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Indian/Kerguelen') where host_location = 5;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Indian/Kerguelen')  where `key` ='gmt' AND `value` = '5';

-- Asia/Novosibirsk +06:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Asia/Novosibirsk') where contact_location = 6;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Asia/Novosibirsk') where host_location = 6;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Asia/Novosibirsk')  where `key` ='gmt' AND `value` = '6';

-- Asia/Bangkok +07:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Asia/Bangkok') where contact_location = 7;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Asia/Bangkok') where host_location = 7;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Asia/Bangkok')  where `key` ='gmt' AND `value` = '7';

-- Asia/Hong_Kong +08:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Asia/Hong_Kong') where contact_location = 8;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Asia/Hong_Kong') where host_location = 8;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Asia/Hong_Kong')  where `key` ='gmt' AND `value` = '8';

-- Asia/Tokyo +09:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Asia/Tokyo') where contact_location = 9;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Asia/Tokyo') where host_location = 9;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Asia/Tokyo')  where `key` ='gmt' AND `value` = '9';

-- Australia/Brisbane +10:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Australia/Brisbane') where contact_location = 10;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Australia/Brisbane') where host_location = 10;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Australia/Brisbane')  where `key` ='gmt' AND `value` = '10';

-- Australia/Melbourne +11:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Australia/Melbourne') where contact_location = 11;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Australia/Melbourne') where host_location = 11;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Australia/Melbourne')  where `key` ='gmt' AND `value` = '11';

-- Pacific/Wallis +12:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Pacific/Wallis') where contact_location = 12;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Pacific/Wallis') where host_location = 12;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Pacific/Wallis')  where `key` ='gmt' AND `value` = '12';

-- Pacific/Auckland +13:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Pacific/Auckland') where contact_location = 13;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Pacific/Auckland') where host_location = 13;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Pacific/Auckland')  where `key` ='gmt' AND `value` = '13';

-- Pacific/Apia +14:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Pacific/Apia') where contact_location = 14;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Pacific/Apia') where host_location = 14;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Pacific/Apia')  where `key` ='gmt' AND `value` = '14';

-- Atlantic/Azores -01:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Atlantic/Azores') where contact_location = -1;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Atlantic/Azores') where host_location = -1;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Atlantic/Azores')  where `key` ='gmt' AND `value` = '-1';

-- America/Sao_Paulo -02:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'America/Sao_Paulo') where contact_location = -2;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'America/Sao_Paulo') where host_location = -2;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'America/Sao_Paulo')  where `key` ='gmt' AND `value` = '-2';

-- America/Argentina/Buenos_Aires -03:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'America/Argentina/Buenos_Aires') where contact_location = -3;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'America/Argentina/Buenos_Aires') where host_location = -3;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'America/Argentina/Buenos_Aires')  where `key` ='gmt' AND `value` = '-3';

--America/Guyana -04:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'America/Guyana') where contact_location = -4;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'America/Guyana') where host_location = -4;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'America/Guyana')  where `key` ='gmt' AND `value` = '-4';

-- America/New_York -05:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'America/New_York') where contact_location = -5;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'America/New_York') where host_location = -5;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'America/New_York')  where `key` ='gmt' AND `value` = '-5';

-- America/Mexico_City -06:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'America/Mexico_City') where contact_location = -6;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'America/Mexico_City') where host_location = -6;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'America/Mexico_City')  where `key` ='gmt' AND `value` = '-6';

-- America/Denver -07:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'America/Denver') where contact_location = -7;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'America/Denver') where host_location = -7;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'America/Denver')  where `key` ='gmt' AND `value` = '-7';

-- America/Los_Angeles -08:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'America/Los_Angeles') where contact_location = -8;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'America/Los_Angeles') where host_location = -8;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'America/Los_Angeles')  where `key` ='gmt' AND `value` = '-8';

-- America/Yakutat -09:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'America/Yakutat') where contact_location = -9;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'America/Yakutat') where host_location = -9;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'America/Yakutat')  where `key` ='gmt' AND `value` = '-9';

-- Pacific/Honolulu -10:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Pacific/Honolulu') where contact_location = -10;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Pacific/Honolulu') where host_location = -10;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Pacific/Honolulu')  where `key` ='gmt' AND `value` = '-10';

-- Pacific/Pago_Pago -11:00
update `contact` set contact_location = (select timezone_id from timezone where timezone_name= 'Pacific/Pago_Pago') where contact_location = -11;
update `host` set host_location = (select timezone_id from timezone where timezone_name= 'Pacific/Pago_Pago') where host_location = -11;
update `options` set `value` = (select timezone_id from timezone where timezone_name= 'Pacific/Pago_Pago')  where `key` ='gmt' AND `value` = '-11';

--Migrate default timezone
update `contact` set `contact_location` = (select `value` from `options` where `key` ='gmt')  where contact_location IS Null;
update `host` set `host_location` = (select `value` from `options` where `key` ='gmt')  where host_location IS Null;


DELETE FROM topology WHERE topology_page IN ('20103', '20105', '20215', '20202','2020403', '20210', '202013', 
'2020401', '2020402','20205', '2020501', '2020502', '2020902', '2020903', '2021001', '2021002', '2021201', '2021202', '2021203', 
'20213','2021301', '2021302', '2020901');

-- Reorganisation des menus

INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES (NULL,'Downtimes',NULL,2,210,60,1,NULL,NULL,'0','0','1',NULL,NULL,NULL,'1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES (NULL,'Downtime','./img/icones/16x16/warning.gif',210,21001,10,1,'./include/monitoring/downtime/downtimeService.php','&o=vs','0','0','1',NULL,NULL,NULL,'1');
UPDATE topology SET topology_name = 'Downtimes' WHERE topology_parent = 210; 

UPDATE topology SET topology_name = 'Status Details' WHERE topology_page = 202;
UPDATE topology SET topology_name = 'Status' WHERE topology_name = 'By Status' AND topology_page IS NULL; 
UPDATE topology SET topology_name = 'Services' WHERE topology_name = 'All Services' AND topology_page = 20201;
UPDATE topology SET topology_name = 'Services Grid', topology_group = 7 WHERE topology_name = 'Details' AND topology_page = 20204;
UPDATE topology SET topology_name = 'Services by Hostgroup', topology_group = 7 WHERE topology_name = 'Details' AND topology_page = 20209;
UPDATE topology SET topology_name = 'Services by Servicegroup', topology_group = 7, topology_order = 80 WHERE topology_name = 'Details' AND topology_page = 20212;

-- Hosts pages
DELETE FROM topology_JS WHERE id_page = 20102;
UPDATE topology SET topology_page = 20202, topology_group = 7, topology_parent = 202, topology_order = 30 WHERE topology_page = 20102; 

DELETE FROM topology_JS WHERE id_page = 20104;
UPDATE topology SET topology_page = 20203, topology_group = 7, topology_parent = 202, topology_order = 120 WHERE topology_page = 20104;
insert into topology_JS (id_page, PathName_js, Init) VALUES ('20203', './include/common/javascript/ajaxMonitoring.js', 'initM');
DELETE FROM topology WHERE topology_parent = '20203';

-- Move temporary comments
update topology set topology_page = '21002', topology_name = 'Host comments', topology_parent = '210', topology_group = '30' WHERE topology_page = '20107';
update topology set topology_page = '21003', topology_name = 'Service comments', topology_parent = '210', topology_group = '40' WHERE topology_page = '20219';

-- Delete Host tab
DELETE FROM topology WHERE topology_page = 201;

-- Add System Logs
UPDATE topology set topology_name = 'Event Logs' WHERE topology_page = '20301';
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES (NULL,'System Logs','./img/icones/16x16/text_code.gif',203,20302,20,30,'./include/eventLogs/viewLog.php','&engine=true','0','0','1',NULL,NULL,NULL,'1');

-- DELETE Global Health
DELETE FROM topology WHERE topology_page = 10102;
