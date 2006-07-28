-- phpMyAdmin SQL Dump
-- version 2.6.4-pl3
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Jeudi 16 Février 2006 à 18:00
-- Version du serveur: 4.1.15
-- Version de PHP: 4.4.0
--
-- Base de données: `oreon`
--

-- --------------------------------------------------------

--
-- Structure de la table `inventory_index`
--

CREATE TABLE `inventory_index` (
  `id` int(11) NOT NULL auto_increment,
  `host_id` int(11) default NULL,
  `name` varchar(254) default NULL,
  `contact` varchar(254) default NULL,
  `description` text,
  `location` varchar(254) default NULL,
  `manufacturer` varchar(254) default NULL,
  `serial_number` varchar(254) default NULL,
  `os` text,
  `os_revision` varchar(254) default NULL,
  `type_ressources` int(11) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `host_id` (`host_id`)
) TYPE=InnoDB;


--
-- Structure de la table `inventory_log`
--

CREATE TABLE `inventory_log` (
  `id` int(11) NOT NULL auto_increment,
  `host_id` int(11) default NULL,
  `type` varchar(20) default NULL,
  `replaced_value` text,
  `value` text,
  `ctime` int(11) default '0',
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;


--
-- Structure de la table `inventory_manufacturer`
--

CREATE TABLE `inventory_manufacturer` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(254) default NULL,
  `alias` varchar(254) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;

--
-- Contenu de la table `inventory_manufacturer`
--

INSERT INTO `inventory_manufacturer` (`name`, `alias`) VALUES ('cisco', 'Cisco Networks');
INSERT INTO `inventory_manufacturer` (`name`, `alias`) VALUES ('hp', 'HP Networks');
INSERT INTO `inventory_manufacturer` (`name`, `alias`) VALUES ('3com', '3Com');
INSERT INTO `inventory_manufacturer` (`name`, `alias`) VALUES ('ciscolinksys', 'Cisco-Linksys');
INSERT INTO `inventory_manufacturer` (`name`, `alias`) VALUES ('allied', 'Allied Telesyn');
INSERT INTO `inventory_manufacturer` (`name`, `alias`) VALUES ('dell', 'Dell');
INSERT INTO `inventory_manufacturer` (`name`, `alias`) VALUES ('saintsongcorp', 'Saint Song Corp');

--
-- Structure de la table `inventory_mac_address`
--

CREATE TABLE `inventory_mac_address` (
  `id` int(11) NOT NULL auto_increment,
  `mac_address_begin` varchar(8) default NULL,
  `manufacturer` int(11) default NULL,
  PRIMARY KEY  (`id`)
) TYPE=InnoDB;

--
-- Contenu de la table `inventory_mac_address`
--

INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:00:0C', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:01:42', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:01:43', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:01:63', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:01:64', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:01:96', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:01:97', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:01:C7', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:01:C9', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:02:16', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:02:17', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:02:4A', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:02:4B', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:02:7D', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:02:7E', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:02:B9', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:02:BA', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:02:FC', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:02:FD', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:03:31', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:03:32', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:03:6B', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:03:6C', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:03:9F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:03:A0', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:03:E3', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:03:E4', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:03:FD', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:03:FE', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:04:27', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:04:28', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:04:4D', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:04:4E', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:04:6D', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:04:6E', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:04:9A', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:04:9B', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:04:C0', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:04:C1', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:04:DD', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:04:DE', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:05:00', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:05:01', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:05:31', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:05:32', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:05:5E', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:05:5F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:05:73', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:05:74', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:05:9A', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:05:9B', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:05:DC', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:05:DD', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:06:28', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:06:2A', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:06:52', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:06:53', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:06:7C', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:06:C1', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:06:D6', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:06:D7', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:07:01', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:07:0D', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:07:0E', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:07:4F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:07:50', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:07:84', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:07:85', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:07:B3', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:07:B4', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:07:EB', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:07:EC', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:08:20', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:08:21', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:08:7C', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:08:7D', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:08:A3', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:08:A4', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:08:C2', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:08:E2', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:08:E3', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:09:11', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:09:12', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:09:43', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:09:44', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:09:7B', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:09:7C', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:09:B6', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:09:B7', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:09:E8', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:09:E9', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0A:41', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0A:42', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0A:8A', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0A:8B', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0A:B7', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0A:B8', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0A:F3', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0A:F4', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0B:45', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0B:46', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0B:5F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0B:60', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0B:BE', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0B:BF', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0B:FC', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0B:FD', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0C:30', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0C:31', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0C:85', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0C:86', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0C:CE', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0C:CF', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0D:28', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0D:29', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0D:65', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0D:66', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0E:38', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0E:39', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0E:83', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0E:84', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0E:D6', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0E:D7', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0F:23', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0F:24', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0F:34', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0F:35', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0F:8F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0F:90', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0F:F7', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0F:F8', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:07', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:0B', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:0D', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:11', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:14', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:1F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:29', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:2F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:54', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:79', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:7B', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:A6', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:F6', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:FF', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:11:20', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:11:21', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:11:5C', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:11:5D', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:11:92', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:11:93', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:11:BB', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:11:BC', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:12:00', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:12:01', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:12:17', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:12:43', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:12:44', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:12:7F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:12:80', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:12:D9', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:12:DA', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:13:10', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:13:19', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:13:1A', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:13:5F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:13:60', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:13:7F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:13:80', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:13:C3', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:13:C4', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:14:1B', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:14:1C', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:14:69', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:14:6A', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:14:A8', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:14:A9', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:14:BF', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:14:F1', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:14:F2', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:15:2B', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:15:2C', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:15:62', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:15:63', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:15:C6', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:15:C7', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:15:F9', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:15:FA', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:16:46', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:16:47', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:16:9C', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:16:9D', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:16:B6', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:16:C7', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:16:C8', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:19', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:24', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:40', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:71', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:78', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:7B', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:80', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:85', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:94', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:96', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:A3', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:B6', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:F2', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:0B', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:0F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:14', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:2A', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:3E', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:50', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:53', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:54', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:73', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:80', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:A2', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:A7', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:BD', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:D1', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:E2', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:F0', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:60:09', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:60:2F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:60:3E', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:60:47', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:60:5C', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:60:70', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:60:83', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:80:1C', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:0C', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:21', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:2B', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:5F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:6D', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:6F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:86', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:92', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:A6', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:AB', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:B1', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:BF', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:D9', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:F2', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:B0:4A', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:B0:64', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:B0:8E', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:B0:C2', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:D0:06', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:D0:58', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:D0:63', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:D0:79', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:D0:90', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:D0:97', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:D0:BA', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:D0:BB', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:D0:BC', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:D0:C0', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:D0:D3', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:D0:E4', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:D0:FF', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:E0:14', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:E0:1E', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:E0:34', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:E0:4F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:E0:8F', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:E0:A3', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:E0:B0', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:E0:F7', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:E0:F9', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:E0:FE', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0D:BC', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0D:BD', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0D:EC', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0D:ED', 1);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:01:02', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:01:03', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:02:9C', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:04:0B', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:05:1A', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:06:8C', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0A:04', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0A:5E', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0B:AC', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0D:54', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0E:6A', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0F:CB', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:4B', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:5A', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:12:A9', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:14:7C', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:16:E0', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:20:AF', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:26:54', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:1E', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:04', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:99', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:50:DA', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:60:08', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:60:8C', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:60:97', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:04', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:A0:24', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:D0:96', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:D0:D8', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '02:60:60', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '02:60:8C', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '02:C0:8C', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '08:00:4E', 3);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:00:63', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:06:0D', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:60:B0', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '08:00:09', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '10:00:90', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0B:CD', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:01:E6', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:01:E7', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:04:EA', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:08:83', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0A:57', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:10:83', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:C1', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0E:7F', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0F:20', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:11:0A', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:11:85', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:12:79', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:13:21', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:14:38', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:14:C2', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:15:60', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:16:35', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:6E', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0D:9D', 2);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:04:5A', 4);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:06:25', 4);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0C:41', 4);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0F:66', 4);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:12:17', 4);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:13:10', 4);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:14:BF', 4);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:16:B6', 4);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:00:CD', 5);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:00:F4', 5);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:01:71', 5);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:03:AE', 5);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:09:41', 5);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0A:47', 5);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0C:25', 5);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0C:46', 5);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0D:DA', 5);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:11:30', 5);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:15:77', 5);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:20:58', 5);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:30:84', 5);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:90:99', 5);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:A0:D2', 5);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:06:5B', 6);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:08:74', 6);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0B:DB', 6);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:0D:56', 6);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:11:43', 6);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:12:3F', 6);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:13:72', 6);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:14:22', 6);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:15:C5', 6);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:B0:D0', 6);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:C0:4F', 6);
INSERT INTO `inventory_mac_address` (`mac_address_begin`, `manufacturer`) VALUES ( '00:03:5C', 7);


