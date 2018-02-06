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

ALTER TABLE timeperiod MODIFY COLUMN `tp_sunday` varchar(2048);
ALTER TABLE timeperiod MODIFY COLUMN `tp_monday` varchar(2048);
ALTER TABLE timeperiod MODIFY COLUMN `tp_tuesday` varchar(2048);
ALTER TABLE timeperiod MODIFY COLUMN `tp_wednesday` varchar(2048);
ALTER TABLE timeperiod MODIFY COLUMN `tp_thursday` varchar(2048);
ALTER TABLE timeperiod MODIFY COLUMN `tp_friday` varchar(2048);
ALTER TABLE timeperiod MODIFY COLUMN `tp_saturday` varchar(2048);

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
('Group', './img/icones/16x16/factory.gif', 617, 61705, 25, 1, './include/configuration/configObject/traps-groups/groups.php', '0', '0');

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

-- #3787
DELETE FROM topology WHERE topology_page IN ('60902', '60903', '60707', '60804');
DELETE FROM topology WHERE topology_page IS NULL AND topology_name LIKE 'Plugins' AND topology_url IS NULL;
DELETE FROM topology WHERE topology_page IS NULL AND topology_name LIKE 'NDOutils' AND topology_url IS NULL;

-- Add new general option for centreon broker
ALTER TABLE cfg_centreonbroker
ADD COLUMN retention_path varchar(255),
ADD COLUMN stats_activate enum('0','1') DEFAULT '1',
ADD COLUMN correlation_activate enum('0','1') DEFAULT '0';


INSERT INTO timezone (`timezone_name`, `timezone_offset`, `timezone_dst_offset`) VALUES 
                        ('Africa/Abidjan', '-00:00', '-00:00'),
                        ('Africa/Accra', '-00:00', '-00:00'),
                        ('Africa/Addis_Ababa', '+03:00', '+03:00'),
                        ('Africa/Algiers', '+01:00', '+01:00'),
                        ('Africa/Asmara', '+03:00', '+03:00'),
                        ('Africa/Bamako', '-00:00', '-00:00'),
                        ('Africa/Bangui', '+01:00', '+01:00'),
                        ('Africa/Banjul', '-00:00', '-00:00'),
                        ('Africa/Bissau', '-00:00', '-00:00'),
                        ('Africa/Blantyre', '+02:00', '+02:00'),
                        ('Africa/Brazzaville', '+01:00', '+01:00'),
                        ('Africa/Bujumbura', '+02:00', '+02:00'),
                        ('Africa/Cairo', '+02:00', '+02:00'),
                        ('Africa/Casablanca', '-00:00', '+01:00'),
                        ('Africa/Ceuta', '+01:00', '+02:00'),
                        ('Africa/Conakry', '-00:00', '-00:00'),
                        ('Africa/Dakar', '-00:00', '-00:00'),
                        ('Africa/Dar_es_Salaam', '+03:00', '+03:00'),
                        ('Africa/Djibouti', '+03:00', '+03:00'),
                        ('Africa/Douala', '+01:00', '+01:00'),
                        ('Africa/El_Aaiun', '-00:00', '+01:00'),
                        ('Africa/Freetown', '-00:00', '-00:00'),
                        ('Africa/Gaborone', '+02:00', '+02:00'),
                        ('Africa/Harare', '+02:00', '+02:00'),
                        ('Africa/Johannesburg', '+02:00', '+02:00'),
                        ('Africa/Juba', '+03:00', '+03:00'),
                        ('Africa/Kampala', '+03:00', '+03:00'),
                        ('Africa/Khartoum', '+03:00', '+03:00'),
                        ('Africa/Kigali', '+02:00', '+02:00'),
                        ('Africa/Kinshasa', '+01:00', '+01:00'),
                        ('Africa/Lagos', '+01:00', '+01:00'),
                        ('Africa/Libreville', '+01:00', '+01:00'),
                        ('Africa/Lome', '-00:00', '-00:00'),
                        ('Africa/Luanda', '+01:00', '+01:00'),
                        ('Africa/Lubumbashi', '+02:00', '+02:00'),
                        ('Africa/Lusaka', '+02:00', '+02:00'),
                        ('Africa/Malabo', '+01:00', '+01:00'),
                        ('Africa/Maputo', '+02:00', '+02:00'),
                        ('Africa/Maseru', '+02:00', '+02:00'),
                        ('Africa/Mbabane', '+02:00', '+02:00'),
                        ('Africa/Mogadishu', '+03:00', '+03:00'),
                        ('Africa/Monrovia', '-00:00', '-00:00'),
                        ('Africa/Nairobi', '+03:00', '+03:00'),
                        ('Africa/Ndjamena', '+01:00', '+01:00'),
                        ('Africa/Niamey', '+01:00', '+01:00'),
                        ('Africa/Nouakchott', '-00:00', '-00:00'),
                        ('Africa/Ouagadougou', '-00:00', '-00:00'),
                        ('Africa/Porto-Novo', '+01:00', '+01:00'),
                        ('Africa/Sao_Tome', '-00:00', '-00:00'),
                        ('Africa/Tripoli', '+02:00', '+02:00'),
                        ('Africa/Tunis', '+01:00', '+01:00'),
                        ('Africa/Windhoek', '+02:00', '+01:00'),
                        ('America/Adak', '-10:00', '-09:00'),
                        ('America/Anchorage', '-09:00', '-08:00'),
                        ('America/Anguilla', '-04:00', '-04:00'),
                        ('America/Antigua', '-04:00', '-04:00'),
                        ('America/Araguaina', '-03:00', '-03:00'),
                        ('America/Argentina/Buenos_Aires', '-03:00', '-03:00'),
                        ('America/Argentina/Catamarca', '-03:00', '-03:00'),
                        ('America/Argentina/Cordoba', '-03:00', '-03:00'),
                        ('America/Argentina/Jujuy', '-03:00', '-03:00'),
                        ('America/Argentina/La_Rioja', '-03:00', '-03:00'),
                        ('America/Argentina/Mendoza', '-03:00', '-03:00'),
                        ('America/Argentina/Rio_Gallegos', '-03:00', '-03:00'),
                        ('America/Argentina/Salta', '-03:00', '-03:00'),
                        ('America/Argentina/San_Juan', '-03:00', '-03:00'),
                        ('America/Argentina/San_Luis', '-03:00', '-03:00'),
                        ('America/Argentina/Tucuman', '-03:00', '-03:00'),
                        ('America/Argentina/Ushuaia', '-03:00', '-03:00'),
                        ('America/Aruba', '-04:00', '-04:00'),
                        ('America/Asuncion', '-03:00', '-04:00'),
                        ('America/Atikokan', '-05:00', '-05:00'),
                        ('America/Bahia', '-03:00', '-03:00'),
                        ('America/Bahia_Banderas', '-06:00', '-05:00'),
                        ('America/Barbados', '-04:00', '-04:00'),
                        ('America/Belem', '-03:00', '-03:00'),
                        ('America/Belize', '-06:00', '-06:00'),
                        ('America/Blanc-Sablon', '-04:00', '-04:00'),
                        ('America/Boa_Vista', '-04:00', '-04:00'),
                        ('America/Bogota', '-05:00', '-05:00'),
                        ('America/Boise', '-07:00', '-06:00'),
                        ('America/Cambridge_Bay', '-07:00', '-06:00'),
                        ('America/Campo_Grande', '-03:00', '-04:00'),
                        ('America/Cancun', '-06:00', '-05:00'),
                        ('America/Caracas', '-04:30', '-04:30'),
                        ('America/Cayenne', '-03:00', '-03:00'),
                        ('America/Cayman', '-05:00', '-05:00'),
                        ('America/Chicago', '-06:00', '-05:00'),
                        ('America/Chihuahua', '-07:00', '-06:00'),
                        ('America/Costa_Rica', '-06:00', '-06:00'),
                        ('America/Creston', '-07:00', '-07:00'),
                        ('America/Cuiaba', '-03:00', '-04:00'),
                        ('America/Curacao', '-04:00', '-04:00'),
                        ('America/Danmarkshavn', '-00:00', '-00:00'),
                        ('America/Dawson', '-08:00', '-07:00'),
                        ('America/Dawson_Creek', '-07:00', '-07:00'),
                        ('America/Denver', '-07:00', '-06:00'),
                        ('America/Detroit', '-05:00', '-04:00'),
                        ('America/Dominica', '-04:00', '-04:00'),
                        ('America/Edmonton', '-07:00', '-06:00'),
                        ('America/Eirunepe', '-05:00', '-05:00'),
                        ('America/El_Salvador', '-06:00', '-06:00'),
                        ('America/Fortaleza', '-03:00', '-03:00'),
                        ('America/Glace_Bay', '-04:00', '-03:00'),
                        ('America/Godthab', '-03:00', '-02:00'),
                        ('America/Goose_Bay', '-04:00', '-03:00'),
                        ('America/Grand_Turk', '-05:00', '-04:00'),
                        ('America/Grenada', '-04:00', '-04:00'),
                        ('America/Guadeloupe', '-04:00', '-04:00'),
                        ('America/Guatemala', '-06:00', '-06:00'),
                        ('America/Guayaquil', '-05:00', '-05:00'),
                        ('America/Guyana', '-04:00', '-04:00'),
                        ('America/Halifax', '-04:00', '-03:00'),
                        ('America/Havana', '-05:00', '-04:00'),
                        ('America/Hermosillo', '-07:00', '-07:00'),
                        ('America/Indiana/Indianapolis', '-05:00', '-04:00'),
                        ('America/Indiana/Knox', '-06:00', '-05:00'),
                        ('America/Indiana/Marengo', '-05:00', '-04:00'),
                        ('America/Indiana/Petersburg', '-05:00', '-04:00'),
                        ('America/Indiana/Tell_City', '-06:00', '-05:00'),
                        ('America/Indiana/Vevay', '-05:00', '-04:00'),
                        ('America/Indiana/Vincennes', '-05:00', '-04:00'),
                        ('America/Indiana/Winamac', '-05:00', '-04:00'),
                        ('America/Inuvik', '-07:00', '-06:00'),
                        ('America/Iqaluit', '-05:00', '-04:00'),
                        ('America/Jamaica', '-05:00', '-05:00'),
                        ('America/Juneau', '-09:00', '-08:00'),
                        ('America/Kentucky/Louisville', '-05:00', '-04:00'),
                        ('America/Kentucky/Monticello', '-05:00', '-04:00'),
                        ('America/Kralendijk', '-04:00', '-04:00'),
                        ('America/La_Paz', '-04:00', '-04:00'),
                        ('America/Lima', '-05:00', '-05:00'),
                        ('America/Los_Angeles', '-08:00', '-07:00'),
                        ('America/Lower_Princes', '-04:00', '-04:00'),
                        ('America/Maceio', '-03:00', '-03:00'),
                        ('America/Managua', '-06:00', '-06:00'),
                        ('America/Manaus', '-04:00', '-04:00'),
                        ('America/Marigot', '-04:00', '-04:00'),
                        ('America/Martinique', '-04:00', '-04:00'),
                        ('America/Matamoros', '-06:00', '-05:00'),
                        ('America/Mazatlan', '-07:00', '-06:00'),
                        ('America/Menominee', '-06:00', '-05:00'),
                        ('America/Merida', '-06:00', '-05:00'),
                        ('America/Metlakatla', '-08:00', '-08:00'),
                        ('America/Mexico_City', '-06:00', '-05:00'),
                        ('America/Miquelon', '-03:00', '-02:00'),
                        ('America/Moncton', '-04:00', '-03:00'),
                        ('America/Monterrey', '-06:00', '-05:00'),
                        ('America/Montevideo', '-02:00', '-03:00'),
                        ('America/Montserrat', '-04:00', '-04:00'),
                        ('America/Nassau', '-05:00', '-04:00'),
                        ('America/New_York', '-05:00', '-04:00'),
                        ('America/Nipigon', '-05:00', '-04:00'),
                        ('America/Nome', '-09:00', '-08:00'),
                        ('America/Noronha', '-02:00', '-02:00'),
                        ('America/North_Dakota/Beulah', '-06:00', '-05:00'),
                        ('America/North_Dakota/Center', '-06:00', '-05:00'),
                        ('America/North_Dakota/New_Salem', '-06:00', '-05:00'),
                        ('America/Ojinaga', '-07:00', '-06:00'),
                        ('America/Panama', '-05:00', '-05:00'),
                        ('America/Pangnirtung', '-05:00', '-04:00'),
                        ('America/Paramaribo', '-03:00', '-03:00'),
                        ('America/Phoenix', '-07:00', '-07:00'),
                        ('America/Port-au-Prince', '-05:00', '-04:00'),
                        ('America/Port_of_Spain', '-04:00', '-04:00'),
                        ('America/Porto_Velho', '-04:00', '-04:00'),
                        ('America/Puerto_Rico', '-04:00', '-04:00'),
                        ('America/Rainy_River', '-06:00', '-05:00'),
                        ('America/Rankin_Inlet', '-06:00', '-05:00'),
                        ('America/Recife', '-03:00', '-03:00'),
                        ('America/Regina', '-06:00', '-06:00'),
                        ('America/Resolute', '-06:00', '-05:00'),
                        ('America/Rio_Branco', '-05:00', '-05:00'),
                        ('America/Santa_Isabel', '-08:00', '-07:00'),
                        ('America/Santarem', '-03:00', '-03:00'),
                        ('America/Santiago', '-03:00', '-03:00'),
                        ('America/Santo_Domingo', '-04:00', '-04:00'),
                        ('America/Sao_Paulo', '-02:00', '-03:00'),
                        ('America/Scoresbysund', '-01:00', '-00:00'),
                        ('America/Sitka', '-09:00', '-08:00'),
                        ('America/St_Barthelemy', '-04:00', '-04:00'),
                        ('America/St_Johns', '-03:30', '-02:30'),
                        ('America/St_Kitts', '-04:00', '-04:00'),
                        ('America/St_Lucia', '-04:00', '-04:00'),
                        ('America/St_Thomas', '-04:00', '-04:00'),
                        ('America/St_Vincent', '-04:00', '-04:00'),
                        ('America/Swift_Current', '-06:00', '-06:00'),
                        ('America/Tegucigalpa', '-06:00', '-06:00'),
                        ('America/Thule', '-04:00', '-03:00'),
                        ('America/Thunder_Bay', '-05:00', '-04:00'),
                        ('America/Tijuana', '-08:00', '-07:00'),
                        ('America/Toronto', '-05:00', '-04:00'),
                        ('America/Tortola', '-04:00', '-04:00'),
                        ('America/Vancouver', '-08:00', '-07:00'),
                        ('America/Whitehorse', '-08:00', '-07:00'),
                        ('America/Winnipeg', '-06:00', '-05:00'),
                        ('America/Yakutat', '-09:00', '-08:00'),
                        ('America/Yellowknife', '-07:00', '-06:00'),
                        ('Antarctica/Casey', '+08:00', '+08:00'),
                        ('Antarctica/Davis', '+07:00', '+07:00'),
                        ('Antarctica/DumontDUrville', '+10:00', '+10:00'),
                        ('Antarctica/Macquarie', '+11:00', '+11:00'),
                        ('Antarctica/Mawson', '+05:00', '+05:00'),
                        ('Antarctica/McMurdo', '+13:00', '+12:00'),
                        ('Antarctica/Palmer', '-03:00', '-03:00'),
                        ('Antarctica/Rothera', '-03:00', '-03:00'),
                        ('Antarctica/Syowa', '+03:00', '+03:00'),
                        ('Antarctica/Troll', '+02:00', '+02:00'),
                        ('Antarctica/Vostok', '+06:00', '+06:00'),
                        ('Arctic/Longyearbyen', '+01:00', '+02:00'),
                        ('Asia/Aden', '+03:00', '+03:00'),
                        ('Asia/Almaty', '+06:00', '+06:00'),
                        ('Asia/Amman', '+02:00', '+03:00'),
                        ('Asia/Anadyr', '+12:00', '+12:00'),
                        ('Asia/Aqtau', '+05:00', '+05:00'),
                        ('Asia/Aqtobe', '+05:00', '+05:00'),
                        ('Asia/Ashgabat', '+05:00', '+05:00'),
                        ('Asia/Baghdad', '+03:00', '+03:00'),
                        ('Asia/Bahrain', '+03:00', '+03:00'),
                        ('Asia/Baku', '+04:00', '+05:00'),
                        ('Asia/Bangkok', '+07:00', '+07:00'),
                        ('Asia/Beirut', '+02:00', '+03:00'),
                        ('Asia/Bishkek', '+06:00', '+06:00'),
                        ('Asia/Brunei', '+08:00', '+08:00'),
                        ('Asia/Chita', '+08:00', '+08:00'),
                        ('Asia/Choibalsan', '+08:00', '+09:00'),
                        ('Asia/Colombo', '+05:30', '+05:30'),
                        ('Asia/Damascus', '+02:00', '+03:00'),
                        ('Asia/Dhaka', '+06:00', '+06:00'),
                        ('Asia/Dili', '+09:00', '+09:00'),
                        ('Asia/Dubai', '+04:00', '+04:00'),
                        ('Asia/Dushanbe', '+05:00', '+05:00'),
                        ('Asia/Gaza', '+02:00', '+03:00'),
                        ('Asia/Hebron', '+02:00', '+03:00'),
                        ('Asia/Ho_Chi_Minh', '+07:00', '+07:00'),
                        ('Asia/Hong_Kong', '+08:00', '+08:00'),
                        ('Asia/Hovd', '+07:00', '+08:00'),
                        ('Asia/Irkutsk', '+08:00', '+08:00'),
                        ('Asia/Jakarta', '+07:00', '+07:00'),
                        ('Asia/Jayapura', '+09:00', '+09:00'),
                        ('Asia/Jerusalem', '+02:00', '+03:00'),
                        ('Asia/Kabul', '+04:30', '+04:30'),
                        ('Asia/Kamchatka', '+12:00', '+12:00'),
                        ('Asia/Karachi', '+05:00', '+05:00'),
                        ('Asia/Kathmandu', '+05:45', '+05:45'),
                        ('Asia/Khandyga', '+09:00', '+09:00'),
                        ('Asia/Kolkata', '+05:30', '+05:30'),
                        ('Asia/Krasnoyarsk', '+07:00', '+07:00'),
                        ('Asia/Kuala_Lumpur', '+08:00', '+08:00'),
                        ('Asia/Kuching', '+08:00', '+08:00'),
                        ('Asia/Kuwait', '+03:00', '+03:00'),
                        ('Asia/Macau', '+08:00', '+08:00'),
                        ('Asia/Magadan', '+10:00', '+10:00'),
                        ('Asia/Makassar', '+08:00', '+08:00'),
                        ('Asia/Manila', '+08:00', '+08:00'),
                        ('Asia/Muscat', '+04:00', '+04:00'),
                        ('Asia/Nicosia', '+02:00', '+03:00'),
                        ('Asia/Novokuznetsk', '+07:00', '+07:00'),
                        ('Asia/Novosibirsk', '+06:00', '+06:00'),
                        ('Asia/Omsk', '+06:00', '+06:00'),
                        ('Asia/Oral', '+05:00', '+05:00'),
                        ('Asia/Phnom_Penh', '+07:00', '+07:00'),
                        ('Asia/Pontianak', '+07:00', '+07:00'),
                        ('Asia/Pyongyang', '+09:00', '+09:00'),
                        ('Asia/Qatar', '+03:00', '+03:00'),
                        ('Asia/Qyzylorda', '+06:00', '+06:00'),
                        ('Asia/Rangoon', '+06:30', '+06:30'),
                        ('Asia/Riyadh', '+03:00', '+03:00'),
                        ('Asia/Sakhalin', '+10:00', '+10:00'),
                        ('Asia/Samarkand', '+05:00', '+05:00'),
                        ('Asia/Seoul', '+09:00', '+09:00'),
                        ('Asia/Shanghai', '+08:00', '+08:00'),
                        ('Asia/Singapore', '+08:00', '+08:00'),
                        ('Asia/Srednekolymsk', '+11:00', '+11:00'),
                        ('Asia/Taipei', '+08:00', '+08:00'),
                        ('Asia/Tashkent', '+05:00', '+05:00'),
                        ('Asia/Tbilisi', '+04:00', '+04:00'),
                        ('Asia/Tehran', '+03:30', '+04:30'),
                        ('Asia/Thimphu', '+06:00', '+06:00'),
                        ('Asia/Tokyo', '+09:00', '+09:00'),
                        ('Asia/Ulaanbaatar', '+08:00', '+09:00'),
                        ('Asia/Urumqi', '+06:00', '+06:00'),
                        ('Asia/Ust-Nera', '+10:00', '+10:00'),
                        ('Asia/Vientiane', '+07:00', '+07:00'),
                        ('Asia/Vladivostok', '+10:00', '+10:00'),
                        ('Asia/Yakutsk', '+09:00', '+09:00'),
                        ('Asia/Yekaterinburg', '+05:00', '+05:00'),
                        ('Asia/Yerevan', '+04:00', '+04:00'),
                        ('Atlantic/Azores', '-01:00', '-00:00'),
                        ('Atlantic/Bermuda', '-04:00', '-03:00'),
                        ('Atlantic/Canary', '-00:00', '+01:00'),
                        ('Atlantic/Cape_Verde', '-01:00', '-01:00'),
                        ('Atlantic/Faroe', '-00:00', '+01:00'),
                        ('Atlantic/Madeira', '-00:00', '+01:00'),
                        ('Atlantic/Reykjavik', '-00:00', '-00:00'),
                        ('Atlantic/South_Georgia', '-02:00', '-02:00'),
                        ('Atlantic/St_Helena', '-00:00', '-00:00'),
                        ('Atlantic/Stanley', '-03:00', '-03:00'),
                        ('Australia/Adelaide', '+10:30', '+09:30'),
                        ('Australia/Brisbane', '+10:00', '+10:00'),
                        ('Australia/Broken_Hill', '+10:30', '+09:30'),
                        ('Australia/Currie', '+11:00', '+10:00'),
                        ('Australia/Darwin', '+09:30', '+09:30'),
                        ('Australia/Eucla', '+08:45', '+08:45'),
                        ('Australia/Hobart', '+11:00', '+10:00'),
                        ('Australia/Lindeman', '+10:00', '+10:00'),
                        ('Australia/Lord_Howe', '+11:00', '+10:30'),
                        ('Australia/Melbourne', '+11:00', '+10:00'),
                        ('Australia/Perth', '+08:00', '+08:00'),
                        ('Australia/Sydney', '+11:00', '+10:00'),
                        ('Europe/Amsterdam', '+01:00', '+02:00'),
                        ('Europe/Andorra', '+01:00', '+02:00'),
                        ('Europe/Athens', '+02:00', '+03:00'),
                        ('Europe/Belgrade', '+01:00', '+02:00'),
                        ('Europe/Berlin', '+01:00', '+02:00'),
                        ('Europe/Bratislava', '+01:00', '+02:00'),
                        ('Europe/Brussels', '+01:00', '+02:00'),
                        ('Europe/Bucharest', '+02:00', '+03:00'),
                        ('Europe/Budapest', '+01:00', '+02:00'),
                        ('Europe/Busingen', '+01:00', '+02:00'),
                        ('Europe/Chisinau', '+02:00', '+03:00'),
                        ('Europe/Copenhagen', '+01:00', '+02:00'),
                        ('Europe/Dublin', '-00:00', '+01:00'),
                        ('Europe/Gibraltar', '+01:00', '+02:00'),
                        ('Europe/Guernsey', '-00:00', '+01:00'),
                        ('Europe/Helsinki', '+02:00', '+03:00'),
                        ('Europe/Isle_of_Man', '-00:00', '+01:00'),
                        ('Europe/Istanbul', '+02:00', '+03:00'),
                        ('Europe/Jersey', '-00:00', '+01:00'),
                        ('Europe/Kaliningrad', '+02:00', '+02:00'),
                        ('Europe/Kiev', '+02:00', '+03:00'),
                        ('Europe/Lisbon', '-00:00', '+01:00'),
                        ('Europe/Ljubljana', '+01:00', '+02:00'),
                        ('Europe/London', '-00:00', '+01:00'),
                        ('Europe/Luxembourg', '+01:00', '+02:00'),
                        ('Europe/Madrid', '+01:00', '+02:00'),
                        ('Europe/Malta', '+01:00', '+02:00'),
                        ('Europe/Mariehamn', '+02:00', '+03:00'),
                        ('Europe/Minsk', '+03:00', '+03:00'),
                        ('Europe/Monaco', '+01:00', '+02:00'),
                        ('Europe/Moscow', '+03:00', '+03:00'),
                        ('Europe/Oslo', '+01:00', '+02:00'),
                        ('Europe/Paris', '+01:00', '+02:00'),
                        ('Europe/Podgorica', '+01:00', '+02:00'),
                        ('Europe/Prague', '+01:00', '+02:00'),
                        ('Europe/Riga', '+02:00', '+03:00'),
                        ('Europe/Rome', '+01:00', '+02:00'),
                        ('Europe/Samara', '+04:00', '+04:00'),
                        ('Europe/San_Marino', '+01:00', '+02:00'),
                        ('Europe/Sarajevo', '+01:00', '+02:00'),
                        ('Europe/Simferopol', '+03:00', '+03:00'),
                        ('Europe/Skopje', '+01:00', '+02:00'),
                        ('Europe/Sofia', '+02:00', '+03:00'),
                        ('Europe/Stockholm', '+01:00', '+02:00'),
                        ('Europe/Tallinn', '+02:00', '+03:00'),
                        ('Europe/Tirane', '+01:00', '+02:00'),
                        ('Europe/Uzhgorod', '+02:00', '+03:00'),
                        ('Europe/Vaduz', '+01:00', '+02:00'),
                        ('Europe/Vatican', '+01:00', '+02:00'),
                        ('Europe/Vienna', '+01:00', '+02:00'),
                        ('Europe/Vilnius', '+02:00', '+03:00'),
                        ('Europe/Volgograd', '+03:00', '+03:00'),
                        ('Europe/Warsaw', '+01:00', '+02:00'),
                        ('Europe/Zagreb', '+01:00', '+02:00'),
                        ('Europe/Zaporozhye', '+02:00', '+03:00'),
                        ('Europe/Zurich', '+01:00', '+02:00'),
                        ('Indian/Antananarivo', '+03:00', '+03:00'),
                        ('Indian/Chagos', '+06:00', '+06:00'),
                        ('Indian/Christmas', '+07:00', '+07:00'),
                        ('Indian/Cocos', '+06:30', '+06:30'),
                        ('Indian/Comoro', '+03:00', '+03:00'),
                        ('Indian/Kerguelen', '+05:00', '+05:00'),
                        ('Indian/Mahe', '+04:00', '+04:00'),
                        ('Indian/Maldives', '+05:00', '+05:00'),
                        ('Indian/Mauritius', '+04:00', '+04:00'),
                        ('Indian/Mayotte', '+03:00', '+03:00'),
                        ('Indian/Reunion', '+04:00', '+04:00'),
                        ('Pacific/Apia', '+14:00', '+13:00'),
                        ('Pacific/Auckland', '+13:00', '+12:00'),
                        ('Pacific/Bougainville', '+11:00', '+11:00'),
                        ('Pacific/Chatham', '+13:45', '+12:45'),
                        ('Pacific/Chuuk', '+10:00', '+10:00'),
                        ('Pacific/Easter', '-05:00', '-05:00'),
                        ('Pacific/Efate', '+11:00', '+11:00'),
                        ('Pacific/Enderbury', '+13:00', '+13:00'),
                        ('Pacific/Fakaofo', '+13:00', '+13:00'),
                        ('Pacific/Fiji', '+13:00', '+12:00'),
                        ('Pacific/Funafuti', '+12:00', '+12:00'),
                        ('Pacific/Galapagos', '-06:00', '-06:00'),
                        ('Pacific/Gambier', '-08:59', '-08:59'),
                        ('Pacific/Guadalcanal', '+11:00', '+11:00'),
                        ('Pacific/Guam', '+10:00', '+10:00'),
                        ('Pacific/Honolulu', '-10:00', '-10:00'),
                        ('Pacific/Johnston', '-10:00', '-10:00'),
                        ('Pacific/Kiritimati', '+14:00', '+14:00'),
                        ('Pacific/Kosrae', '+11:00', '+11:00'),
                        ('Pacific/Kwajalein', '+12:00', '+12:00'),
                        ('Pacific/Majuro', '+12:00', '+12:00'),
                        ('Pacific/Marquesas', '-09:30', '-09:30'),
                        ('Pacific/Midway', '-11:00', '-11:00'),
                        ('Pacific/Nauru', '+12:00', '+12:00'),
                        ('Pacific/Niue', '-11:00', '-11:00'),
                        ('Pacific/Norfolk', '+11:30', '+11:30'),
                        ('Pacific/Noumea', '+11:00', '+11:00'),
                        ('Pacific/Pago_Pago', '-11:00', '-11:00'),
                        ('Pacific/Palau', '+09:00', '+09:00'),
                        ('Pacific/Pitcairn', '-08:00', '-08:00'),
                        ('Pacific/Pohnpei', '+11:00', '+11:00'),
                        ('Pacific/Port_Moresby', '+10:00', '+10:00'),
                        ('Pacific/Rarotonga', '-10:00', '-10:00'),
                        ('Pacific/Saipan', '+10:00', '+10:00'),
                        ('Pacific/Tahiti', '-10:00', '-10:00'),
                        ('Pacific/Tarawa', '+12:00', '+12:00'),
                        ('Pacific/Tongatapu', '+13:00', '+13:00'),
                        ('Pacific/Wake', '+12:00', '+12:00'),
                        ('Pacific/Wallis', '+12:00', '+12:00');

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

-- America/Guyana -04:00
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

-- Migrate default timezone
update `contact` set `contact_location` = (select `value` from `options` where `key` ='gmt')  where contact_location IS Null;
update `host` set `host_location` = (select `value` from `options` where `key` ='gmt')  where host_location IS Null;


DELETE FROM topology WHERE topology_page IN ('20103', '20105', '20215', '20202','2020403', '20210', '202013', 
'2020401', '2020402','20205', '2020501', '2020502', '2020902', '2020903', '2021001', '2021002', '2021201', '2021202', '2021203', 
'20213','2021301', '2021302', '2020901');

-- Moving Graphs section to Performances
SET foreign_key_checks = 0;
DELETE FROM topology_JS WHERE id_page = 40201;
DELETE FROM topology WHERE topology_page = 4;
UPDATE topology SET topology_page = 204, topology_name = 'Performances', topology_parent = 2 WHERE topology_page = 402;
UPDATE topology SET topology_page = 20401, topology_parent = 204, topology_name = 'Graphs' WHERE topology_page = 40201;
UPDATE topology SET topology_page = 20404, topology_parent = 204 WHERE topology_page = 40204;
UPDATE topology SET topology_page = 20405, topology_parent = 204 WHERE topology_page = 40205;
UPDATE topology SET topology_parent = 204 WHERE topology_id = 402 AND topology_name = 'Virtuals' AND topology_page IS NULL;
UPDATE topology SET topology_page = 20408, topology_parent = 204 WHERE topology_page = 40208;
UPDATE topology SET topology_parent = 204 WHERE topology_parent = 402;
UPDATE topology_JS SET id_page = 20404 WHERE id_page = 40204;
UPDATE topology_JS SET id_page = 20405 WHERE id_page = 40205;
SET foreign_key_checks = 1;

-- Move downtime pages
DELETE FROM topology WHERE topology_page IN ('20218', '20106', '60305');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES (NULL,'Downtimes',NULL,2,210,60,1,NULL,NULL,'0','0','1',NULL,NULL,NULL,'1');
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES (NULL,'Downtimes','./img/icones/16x16/warning.gif',210,21001,10,1,'./include/monitoring/downtime/downtime.php', NULL,'0','0','1',NULL,NULL,NULL,'1');

-- Move comment pages
UPDATE topology SET topology_page = '21002', topology_parent = '210', topology_name = 'Comments', topology_url = './include/monitoring/comments/comments.php', topology_url_opt = NULL, topology_group = '1', topology_order = 30 WHERE topology_page = '20107';
DELETE FROM topology WHERE topology_page = 20219;

-- Move service pages
UPDATE topology SET topology_name = 'Status Details' WHERE topology_page = 202;
UPDATE topology SET topology_name = 'Services', topology_url_opt = NULL WHERE topology_name = 'All Services' AND topology_page = 20201;
UPDATE topology SET topology_name = 'Services Grid', topology_group = 7 WHERE topology_name = 'Details' AND topology_page = 20204;
UPDATE topology SET topology_name = 'Services by Hostgroup', topology_group = 7 WHERE topology_name = 'Details' AND topology_page = 20209;
UPDATE topology SET topology_name = 'Services by Servicegroup', topology_group = 7, topology_order = 80 WHERE topology_name = 'Details' AND topology_page = 20212;

-- Hosts pages
DELETE FROM topology_JS WHERE id_page = 20102;
UPDATE topology SET topology_page = 20202, topology_group = 7, topology_parent = 202, topology_order = 30, topology_url_opt = NULL WHERE topology_page = 20102; 

DELETE FROM topology_JS WHERE id_page = 20104;
UPDATE topology SET topology_page = 20203, topology_group = 7, topology_parent = 202, topology_order = 120 WHERE topology_page = 20104;
UPDATE topology SET topology_name = 'Hostgroups Summary' WHERE topology_page = 20203;
INSERT INTO topology_JS (id_page, PathName_js, Init) VALUES (20203, './include/common/javascript/ajaxMonitoring.js', 'initM');
DELETE FROM topology WHERE topology_parent = '20203';

-- Delete Host tab
DELETE FROM topology WHERE topology_page = 201;

-- Add System Logs
UPDATE topology set topology_name = 'Event Logs' WHERE topology_page = '20301';
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES (NULL,'System Logs','./img/icones/16x16/text_code.gif',203,20302,20,30,'./include/eventLogs/viewLog.php','&engine=true','0','0','1',NULL,NULL,NULL,'1');

-- DELETE Global Health
DELETE FROM topology WHERE topology_page = 10102;

-- DELETE Topology for System information
DELETE FROM topology WHERE topology_page = 50501;

-- DELETE topology for Process control
DELETE FROM topology_JS WHERE id_page = 50502;
DELETE FROM topology WHERE topology_page = 50502;

-- DELETE topology for Tactical overview
DELETE FROM topology WHERE topology_page = 10101;
DELETE FROM topology WHERE topology_page = 101;

-- DELETE topology for Performance info
DELETE FROM topology_JS WHERE id_page = 10203;
DELETE FROM topology WHERE topology_page = 10203;

-- DELETE topology for Scheduling queue
DELETE FROM topology_JS WHERE id_page = 20207;
DELETE FROM topology WHERE topology_page = 20207; 
DELETE FROM topology WHERE topology_parent = 202 AND topology_group = 33 AND topology_name = 'Monitoring Engine';

-- Rename Monitoring Engine Statistics menu
UPDATE topology SET topology_name = 'Poller Statistics' WHERE topology_page = 102;

-- Change centreon tab menus
SET foreign_key_checks = 0;
UPDATE topology_JS SET id_page = 60902 WHERE id_page = 60701;
UPDATE topology SET topology_page = 60902, topology_parent = 609, topology_group = 1, topology_show = '0' WHERE topology_page = 60701;
UPDATE topology_JS SET id_page = 60903 WHERE id_page = 60703;
UPDATE topology SET topology_page = 60903, topology_parent = 609, topology_group = 1, topology_name = 'Engine configuration' WHERE topology_page = 60703;
UPDATE topology SET topology_page = 60904, topology_parent = 609, topology_group = 1, topology_name = 'Resources' WHERE topology_page = 60704;
DELETE FROM topology WHERE topology_parent = 607;
DELETE FROM topology WHERE topology_page = 607;
UPDATE topology SET topology_name = "Pollers" WHERE topology_page = 609;
UPDATE topology SET topology_name = "Broker configuration", topology_order = 35, topology_group = 1 WHERE topology_page = 60909;
DELETE FROM topology WHERE topology_name = "Centreon-Broker";
SET foreign_key_checks = 1;

-- Add option for number of groups per page
INSERT INTO `options` (`key`, `value`) VALUES ('maxGraphPerformances','5');

-- Change Options informations 
SET foreign_key_checks = 0;
UPDATE topology SET topology_name = 'Parameters' WHERE topology_page = 501; 
UPDATE topology SET topology_name = 'Centreon UI', topology_page = 50110, topology_parent = 501 WHERE topology_page = 5010101; 
UPDATE topology SET topology_page = 50111, topology_parent = 501 WHERE topology_page = 5010102; 
UPDATE topology SET topology_page = 50112, topology_parent = 501 WHERE topology_page = 5010103; 
UPDATE topology_JS set id_page = 50112 WHERE id_page = 5010103;
UPDATE topology SET topology_page = 50113, topology_parent = 501 WHERE topology_page = 5010105;
UPDATE topology_JS set id_page = 50113 WHERE id_page = 5010103; 
UPDATE topology SET topology_page = 50114, topology_parent = 501 WHERE topology_page = 5010106; 
UPDATE topology SET topology_page = 50115, topology_parent = 501 WHERE topology_page = 5010107; 
UPDATE topology SET topology_page = 50116, topology_parent = 501 WHERE topology_page = 5010109; 
UPDATE topology SET topology_page = 50117, topology_parent = 501 WHERE topology_page = 5010110; 
SET foreign_key_checks = 1;
DELETE FROM topology WHERE topology_page = 50101; 

-- Change Centstorage
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES (NULL,'Performance Management',NULL,501,NULL,20,10,NULL,NULL,'0','0','1',NULL,NULL,NULL,'1');
UPDATE topology SET topology_name = 'Options', topology_page = 50118, topology_parent = 501, topology_order = 200, topology_group = 10 WHERE topology_page = 5010601;
UPDATE topology SET topology_name = 'Data', topology_page = 50119, topology_parent = 501, topology_order = 210, topology_group = 10 WHERE topology_page = 5010602; 
DELETE FROM topology WHERE topology_page = 50106;

-- Change Media
INSERT INTO `topology` (`topology_id`, `topology_name`, `topology_icone`, `topology_parent`, `topology_page`, `topology_order`, `topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`, `topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`) VALUES (NULL,'Media',NULL,501,NULL,15,11,NULL,NULL,'0','0','1',NULL,NULL,NULL,'1');
UPDATE topology SET topology_group = 11, topology_name = 'Images' WHERE topology_page = 50102;

-- DELETE old links (Donate, Forum, Github)...
DELETE FROM topology WHERE topology_page IN (50606, 50607, 50605, 50604, 50602, 50105, 5010501, 5010502, 5010503);

-- Set required value in field password
update cb_type_field_relation set is_required = 0 where cb_type_id in (14, 16 , 28, 29, 30, 31) and cb_field_id = 9;

-- Set required field db_port, db_user, db_host and db_name
update cb_type_field_relation set is_required = 1 where cb_type_id in (14, 16 , 28, 29, 30, 31) and cb_field_id in (7, 8, 10, 18);

-- Change topology_Js for parameter ldap page
insert into topology_JS (id_page,o,PathName_js,Init) VALUES (50113,'ldap','./include/common/javascript/centreon/doClone.js',NULL);
insert into topology_JS (id_page,o,PathName_js,Init) VALUES (50113,'ldap','./include/common/javascript/jquery/plugins/sheepit/jquery.sheepItPlugin.min.js',NULL);

DELETE FROM topology_JS WHERE PathName_js LIKE './include/common/javascript/codebase/dhtmlxcommon.js' OR PathName_js LIKE './include/common/javascript/codebase/dhtmlxtree.js';

-- change Topology for modules pages
UPDATE topology SET topology_name = 'Modules' WHERE topology_page = 50701 AND topology_url IS NOT NULL;
UPDATE topology SET topology_name = 'Widgets', topology_group = 1 WHERE topology_page = 50703 AND topology_url IS NOT NULL;
DELETE FROM topology WHERE topology_parent = 507 AND topology_group = 2 AND topology_url IS NULL;

-- Delete Colors Pages
DELETE FROM topology WHERE topology_page = 50112;

-- Remove Escalation Pages
DELETE FROM topology WHERE topology_page = 60402;
DELETE FROM topology_JS WHERE id_page = 60402;
DELETE FROM topology WHERE topology_page = 60403;
DELETE FROM topology_JS WHERE id_page = 60403;
DELETE FROM topology WHERE topology_page = 60404;
DELETE FROM topology_JS WHERE id_page = 60404;
DELETE FROM topology WHERE topology_page = 60405;
DELETE FROM topology_JS WHERE id_page = 60405;
DELETE FROM topology WHERE topology_page = 60406;
DELETE FROM topology_JS WHERE id_page = 60406;

-- Update topology JS for page monitoring
UPDATE topology_JS SET Init = NULL WHERE id_page = 202 AND PathName_js = './include/common/javascript/ajaxMonitoring.js';

-- Purge Graphs Templates
ALTER TABLE giv_graphs_template DROP bg_grid_color;
ALTER TABLE giv_graphs_template DROP bg_color;
ALTER TABLE giv_graphs_template DROP police_color;
ALTER TABLE giv_graphs_template DROP grid_main_color;
ALTER TABLE giv_graphs_template DROP grid_sec_color;
ALTER TABLE giv_graphs_template DROP contour_cub_color;
ALTER TABLE giv_graphs_template DROP col_arrow;
ALTER TABLE giv_graphs_template DROP col_top;
ALTER TABLE giv_graphs_template DROP col_bot;

ALTER TABLE custom_view_user_relation ADD COLUMN is_consumed int(1) NOT NULL DEFAULT 1;

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.0-RC1' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.6.6' LIMIT 1;
