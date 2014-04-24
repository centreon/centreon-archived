CREATE TABLE `module` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `alias` varchar(45) DEFAULT NULL,
  `description` varchar(45) DEFAULT NULL,
  `version` varchar(45) NOT NULL,
  `author` varchar(255) NOT NULL,
  `isactivated` char(1) NOT NULL DEFAULT '0',
  `isinstalled` char(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1

