-- RC1

ALTER TABLE `config` ADD `len_storage_mysql` INT NULL ;

CREATE TABLE `data_stats_daily` (
  `data_stats_daily_id` int(11) NOT NULL auto_increment,
  `metric_id` int(11) default NULL,
  `min` int(11) default NULL,
  `max` int(11) default NULL,
  `average` int(11) default NULL,
  `count` int(11) default NULL,
  `day_time` int(11) default NULL,
  PRIMARY KEY  (`data_stats_daily_id`),
  KEY `metric_id` (`metric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- 
-- Structure de la table `data_stats_monthly`
-- 

CREATE TABLE `data_stats_monthly` (
  `data_stats_monthly_id` int(11) NOT NULL auto_increment,
  `metric_id` int(11) default NULL,
  `min` int(11) default NULL,
  `max` int(11) default NULL,
  `average` int(11) default NULL,
  `count` int(11) default NULL,
  `month_time` int(11) default NULL,
  PRIMARY KEY  (`data_stats_monthly_id`),
  KEY `metric_id` (`metric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

-- 
-- Structure de la table `data_stats_yearly`
-- 

CREATE TABLE `data_stats_yearly` (
  `data_stats_yearly_id` int(11) NOT NULL auto_increment,
  `metric_id` int(11) default NULL,
  `min` int(11) default NULL,
  `max` int(11) default NULL,
  `average` int(11) default NULL,
  `count` int(11) default NULL,
  `year_time` int(11) default NULL,
  PRIMARY KEY  (`data_stats_yearly_id`),
  KEY `metric_id` (`metric_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `index_data`
-- 

ALTER TABLE `index_data` ADD `check_interval` INT NULL , ADD `trashed` INT NOT NULL DEFAULT '0', ADD `must_be_rebuild` INT NOT NULL DEFAULT '0';

ALTER TABLE `index_data` ADD INDEX ( `host_name` , `service_description` );
ALTER TABLE `metrics` DROP `rrdDataBase_Path`;

ALTER TABLE `statistics` ADD `lineRead` INT NULL , ADD `valueReccorded` INT NULL ;

RENAME TABLE `data_bin`  TO `data_bin_tmp` ;
RENAME TABLE `metrics`  TO `metrics_tmp` ;
RENAME TABLE `index_data`  TO `index_data_tmp` ;


CREATE TABLE `index_data` (
  `id` int(11) NOT NULL auto_increment,
  `host_name` varchar(75) default NULL,
  `host_id` int(11) default NULL,
  `service_description` varchar(75) default NULL,
  `service_id` int(11) default NULL,
  `check_interval` int(11) default NULL,
  `special` enum('0','1') default '0',
  `trashed` enum('0','1') default '0',
  `must_be_rebuild` enum('0','1') default '0',
  `storage_type` enum('0','1','2') default '2',
  PRIMARY KEY  (`id`),
  KEY `host_name` (`host_name`),
  KEY `service_description` (`service_description`),
  KEY `host_id` (`host_id`),
  KEY `service_id` (`service_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `metrics` (
  `metric_id` int(11) NOT NULL auto_increment,
  `index_id` int(11) default NULL,
  `metric_name` varchar(32) default NULL,
  `unit_name` varchar(32) default NULL,
  `warn` float default NULL,
  `crit` float default NULL,
  PRIMARY KEY  (`metric_id`),
  KEY `index` (`index_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `data_bin` (
  `id_bin` int(11) NOT NULL auto_increment,
  `id_metric` int(11) default NULL,
  `ctime` int(11) default NULL,
  `value` float default NULL,
  `status` enum('0','1','2','3','4') default NULL,
  PRIMARY KEY  (`id_bin`),
  KEY `index_metric` (`id_metric`),
  KEY `ctime` (`ctime`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


INSERT INTO `data_bin` SELECT * FROM `data_bin_tmp` ;
INSERT INTO `metrics` SELECT * FROM `metrics_tmp` ;
INSERT INTO `index_data` SELECT * FROM `index_data_tmp` ;

-- RC2

ALTER TABLE `index_data` ADD INDEX ( `must_be_rebuild` );
ALTER TABLE `index_data` ADD INDEX ( `trashed` );
ALTER TABLE `index_data` ADD INDEX ( `host_name` );
ALTER TABLE `index_data` ADD INDEX ( `service_description` );
UPDATE `config` SET `len_storage_mysql` = '365' ;
ALTER TABLE `index_data` CHANGE `must_be_rebuild` `must_be_rebuild` ENUM( '0', '1', '2' ) NULL DEFAULT '0';

-- RC3

UPDATE `index_data` SET `storage_type` = '2';
UPDATE `index_data` SET `trashed` = '0', `must_be_rebuild` = '0';