ALTER TABLE options ENGINE=InnoDB;
ALTER TABLE css_color_menu ENGINE=InnoDB;

-- Change version of Centreon
UPDATE `informations` SET `value` = '2.7.0' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.6.2' LIMIT 1;

