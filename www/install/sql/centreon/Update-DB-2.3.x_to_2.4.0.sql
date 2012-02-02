INSERT INTO `topology` (topology_name, topology_icone,topology_parent, topology_page, topology_order, topology_group, topology_url, topology_show) VALUES
('Custom Views', NULL, '1', '103', '1', '1', './include/home/customViews/index.php', '1'),
('Edit', NULL, '103', '10301', NULL, NULL, './include/home/customViews/form.php', '0'),
('Share', NULL, '103', '10302', NULL, NULL, './include/home/customViews/shareView.php', '0'),
('Parameters', NULL, '103', '10303', NULL, NULL, './include/home/customViews/widgetParam.php', '0'),
('Add Widget', NULL, '103', '10304', NULL, NULL, './include/home/customViews/addWidget.php', '0'),
('Rotation', NULL, '103', '10305', NULL, NULL, './include/home/customViews/rotation.php', '0'),
('Widgets', NULL, '507', NULL, '2', '30', NULL, '1'),
('Setup', './img/icones/16x16/press.gif', '507', '50702', '30', '30', './include/options/oreon/widgets/widgets.php', '1');

-- --------------------------------------------------------

--
-- Structure de la table `custom_views`
--

CREATE TABLE  `custom_views` (
	`custom_view_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
	`name` VARCHAR( 255 ) NOT NULL,
	`layout` VARCHAR( 255 ) NOT NULL,	
	PRIMARY KEY (  `custom_view_id` )
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `custom_view_user_relation`
--

CREATE TABLE `custom_view_user_relation` (
	`custom_view_id` INT( 11 ) NOT NULL,
	`user_id` INT( 11 ) NULL,
	`usergroup_id` INT( 11 ) NULL,
	`locked` TINYINT( 6 ) DEFAULT 0,	
	`is_owner` TINYINT( 6 ) DEFAULT 0,
	CONSTRAINT `fk_custom_views_user_id`
    FOREIGN KEY (`user_id` )
    REFERENCES `contact` (`contact_id` )
    ON DELETE CASCADE,
    CONSTRAINT `fk_custom_views_usergroup_id`
    FOREIGN KEY (`usergroup_id` )
    REFERENCES `contactgroup` (`cg_id` )
    ON DELETE CASCADE,
    CONSTRAINT `fk_custom_view_user_id`
    FOREIGN KEY (`custom_view_id` )
    REFERENCES `custom_views` (`custom_view_id` )
    ON DELETE CASCADE
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE  custom_view_user_relation` ADD UNIQUE  `view_user_unique_index` ( `custom_view_id` , `user_id`, `usergroup_id` );


-- --------------------------------------------------------

--
-- Structure de la table `custom_view_default`
--

CREATE TABLE `custom_view_default` (
  `user_id` INT (11) NOT NULL,
  `custom_view_id` INT (11) NOT NULL,
  CONSTRAINT `fk_custom_view_default_user_id`
  FOREIGN KEY (`user_id` )
  REFERENCES `contact` (`contact_id` )
  ON DELETE CASCADE,
  CONSTRAINT `fk_custom_view_default_cv_id`
  FOREIGN KEY (`custom_view_id` )
  REFERENCES `custom_views` ( `custom_view_id` )
  ON DELETE CASCADE
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `widget_models`
--

CREATE TABLE  `widget_models` (
	`widget_model_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
	`title` VARCHAR( 255 ) NOT NULL ,
	`description` VARCHAR( 255 ) NOT NULL ,
	`url` VARCHAR( 255 ) NOT NULL ,
	`version` VARCHAR( 255 ) NOT NULL ,
	`directory` VARCHAR( 255 ) NOT NULL,
	`author` VARCHAR( 255 ) NOT NULL ,
	`email` VARCHAR( 255 ) NULL ,
	`website` VARCHAR( 255 ) NULL ,
	`keywords` VARCHAR( 255 ) NULL ,
	`screenshot` VARCHAR( 255 ) NULL ,
	`thumbnail` VARCHAR( 255 ) NULL ,
	`autoRefresh` INT( 11 ) NULL,	
	PRIMARY KEY (  `widget_model_id` )
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `widgets`
--

CREATE TABLE  `widgets` (
	`widget_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
	`widget_model_id` INT( 11 ) NOT NULL,
	`title` VARCHAR( 255 ) NOT NULL ,	
	CONSTRAINT `fk_wdg_model_id`
	FOREIGN KEY (`widget_model_id`)
	REFERENCES `widget_models` (`widget_model_id`)
	ON DELETE CASCADE,
	PRIMARY KEY (  `widget_id` )
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `widget_views`
--

CREATE TABLE  `widget_views` (
	`widget_view_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
	`custom_view_id` INT( 11 ) NOT NULL ,
	`widget_id` INT( 11 ) NOT NULL ,	
	`widget_order` VARCHAR( 255 ) NOT NULL ,	
	PRIMARY KEY (  `widget_view_id` ),
	CONSTRAINT `fk_custom_view_id`
    FOREIGN KEY (`custom_view_id` )
    REFERENCES `custom_views` (`custom_view_id` )
    ON DELETE CASCADE,
    CONSTRAINT `fk_widget_id`
    FOREIGN KEY (`widget_id` )
    REFERENCES `widgets` (`widget_id` )
    ON DELETE CASCADE    
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `widget_parameters_field_type`
--

CREATE TABLE  `widget_parameters_field_type` (
  `field_type_id` INT ( 11 ) NOT NULL AUTO_INCREMENT ,
  `ft_typename` VARCHAR(50) NOT NULL ,
  `is_connector` TINYINT(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`field_type_id`) 
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `widget_parameters`
--

CREATE TABLE  `widget_parameters` (
	`parameter_id` INT( 11 ) NOT NULL AUTO_INCREMENT,
	`parameter_name` VARCHAR( 255 ) NOT NULL,
	`parameter_code_name` VARCHAR( 255 ) NOT NULL,
	`default_value` VARCHAR( 255 ) NULL,
	`parameter_order` TINYINT(6) NOT NULL,
	`header_title` VARCHAR( 255 ) NULL,
	`require_permission` VARCHAR( 255 ) NOT NULL,
	`widget_model_id` INT( 11 ) NOT NULL ,
	`field_type_id` INT( 11 ) NOT NULL,
	PRIMARY KEY (  `parameter_id` ),
	CONSTRAINT `fk_widget_param_widget_id`
    FOREIGN KEY (`widget_model_id` )
    REFERENCES `widget_models` (`widget_model_id` )
    ON DELETE CASCADE,    
	CONSTRAINT `fk_widget_field_type_id`
    FOREIGN KEY (`field_type_id` )
    REFERENCES `widget_parameters_field_type` (`field_type_id` )
    ON DELETE CASCADE
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `widget_preferences`
--

CREATE TABLE  `widget_preferences` (
	`widget_view_id` INT( 11 ) NOT NULL ,
	`parameter_id` INT( 11 ) NOT NULL ,
	`preference_value` VARCHAR( 255 ) NOT NULL,
	`user_id` INT( 11 ) NOT NULL,
	CONSTRAINT `fk_widget_view_id`
    FOREIGN KEY (`widget_view_id` )
    REFERENCES `widget_views` (`widget_view_id` )
    ON DELETE CASCADE,
    CONSTRAINT `fk_widget_parameter_id`
    FOREIGN KEY (`parameter_id` )
    REFERENCES `widget_parameters` (`parameter_id` )
    ON DELETE CASCADE
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE `widget_preferences` ADD UNIQUE  `widget_preferences_unique_index` (  `widget_view_id` ,  `parameter_id`, `user_id` );

-- --------------------------------------------------------

--
-- Structure de la table `widget_parameters_multiple_options`
--

CREATE TABLE `widget_parameters_multiple_options` (
	`parameter_id` INT ( 11 ) NOT NULL,
	`option_name` VARCHAR ( 255 ) NOT NULL,
	`option_value` VARCHAR ( 255 ) NOT NULL,
	CONSTRAINT `fk_option_parameter_id`
    FOREIGN KEY (`parameter_id` )
    REFERENCES `widget_parameters` (`parameter_id` )
    ON DELETE CASCADE
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `widget_parameters_range`
--

CREATE TABLE `widget_parameters_range` (
	`parameter_id` INT ( 11 ) NOT NULL,
	`min_range` INT ( 11 ) NOT NULL,
	`max_range` INT ( 11 ) NOT NULL,
	`step` INT ( 11 ) NOT NULL,
	CONSTRAINT `fk_option_range_id`
    FOREIGN KEY (`parameter_id` )
    REFERENCES `widget_parameters` (`parameter_id` )
    ON DELETE CASCADE
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

-- 
-- For LDAP store password option default value
--
INSERT INTO `options` (`key`, `value`) VALUES ('ldap_store_password', '1');

--
-- Table structure for table `cfg_resource_instance_relations`
--

CREATE TABLE IF NOT EXISTS `cfg_resource_instance_relations` (
  `resource_id` int(11) NOT NULL,
  `instance_id` int(11) NOT NULL,
  CONSTRAINT `fk_crir_res_id` 
  FOREIGN KEY (`resource_id`)
  REFERENCES `cfg_resource` (`resource_id`)
  ON DELETE CASCADE,
  CONSTRAINT `fk_crir_ins_id` 
  FOREIGN KEY (`instance_id`)
  REFERENCES `nagios_server` (`id`)
  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE  `cfg_cgi` ADD  `instance_id` INT( 11 ) NULL AFTER  `cgi_name`;
ALTER TABLE  `cfg_cgi` ADD CONSTRAINT `fk_cgi_instance_id` FOREIGN KEY (`instance_id`) REFERENCES `nagios_server` (`id`) ON DELETE SET NULL;

UPDATE `informations` SET `value` = '2.4.0' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.x' LIMIT 1;