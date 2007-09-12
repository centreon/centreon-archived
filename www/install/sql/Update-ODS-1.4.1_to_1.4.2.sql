
ALTER TABLE `index_data` ADD `locked` ENUM('0' , '1') NULL DEFAULT '0' AFTER `trashed` ;

ALTER TABLE `config` ADD `DailyReporting` ENUM( '0', '1' ) NULL DEFAULT '1',
ADD `HourlyReporting` ENUM( '0', '1' ) NULL DEFAULT '1',
ADD `MonthlyReporting` ENUM( '0', '1' ) NULL DEFAULT '1',
ADD `Reporting_enable` ENUM( '0', '1' ) NULL DEFAULT '1',
ADD `Force_Reporting_Rebuild` ENUM( '0', '1' ) NULL DEFAULT '0';


ALTER TABLE `metrics` ADD `hidden` ENUM( '0', '1' ) NULL DEFAULT '0';
