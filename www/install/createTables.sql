
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_actions` (
  `acl_action_id` int(11) NOT NULL AUTO_INCREMENT,
  `acl_action_name` varchar(255) DEFAULT NULL,
  `acl_action_description` varchar(255) DEFAULT NULL,
  `acl_action_activate` enum('0','1','2') DEFAULT NULL,
  PRIMARY KEY (`acl_action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_actions_rules` (
  `aar_id` int(11) NOT NULL AUTO_INCREMENT,
  `acl_action_rule_id` int(11) DEFAULT NULL,
  `acl_action_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`aar_id`),
  KEY `acl_action_rule_id` (`acl_action_rule_id`),
  CONSTRAINT `acl_actions_rules_ibfk_1` FOREIGN KEY (`acl_action_rule_id`) REFERENCES `acl_actions` (`acl_action_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_group_actions_relations` (
  `acl_action_id` int(11) DEFAULT NULL,
  `acl_group_id` int(11) DEFAULT NULL,
  KEY `acl_action_id` (`acl_action_id`),
  KEY `acl_group_id` (`acl_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_group_contactgroups_relations` (
  `cg_cg_id` int(11) DEFAULT NULL,
  `acl_group_id` int(11) DEFAULT NULL,
  KEY `cg_cg_id` (`cg_cg_id`),
  KEY `acl_group_id` (`acl_group_id`),
  CONSTRAINT `acl_group_contactgroups_relations_ibfk_2` FOREIGN KEY (`acl_group_id`) REFERENCES `acl_groups` (`acl_group_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_group_contactgroups_relations_ibfk_1` FOREIGN KEY (`cg_cg_id`) REFERENCES `contactgroup` (`cg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_group_contacts_relations` (
  `contact_contact_id` int(11) DEFAULT NULL,
  `acl_group_id` int(11) DEFAULT NULL,
  KEY `contact_contact_id` (`contact_contact_id`),
  KEY `acl_group_id` (`acl_group_id`),
  CONSTRAINT `acl_group_contacts_relations_ibfk_1` FOREIGN KEY (`contact_contact_id`) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_group_contacts_relations_ibfk_2` FOREIGN KEY (`acl_group_id`) REFERENCES `acl_groups` (`acl_group_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_group_topology_relations` (
  `acl_group_id` int(11) DEFAULT NULL,
  `acl_topology_id` int(11) DEFAULT NULL,
  KEY `acl_group_id` (`acl_group_id`),
  KEY `acl_topology_id` (`acl_topology_id`),
  CONSTRAINT `acl_group_topology_relations_ibfk_1` FOREIGN KEY (`acl_group_id`) REFERENCES `acl_groups` (`acl_group_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_group_topology_relations_ibfk_2` FOREIGN KEY (`acl_topology_id`) REFERENCES `acl_topology` (`acl_topo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_groups` (
  `acl_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `acl_group_name` varchar(255) DEFAULT NULL,
  `acl_group_alias` varchar(255) DEFAULT NULL,
  `acl_group_changed` int(11) NOT NULL DEFAULT 1,
  `acl_group_activate` enum('0','1','2') DEFAULT NULL,
  PRIMARY KEY (`acl_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_res_group_relations` (
  `acl_res_id` int(11) DEFAULT NULL,
  `acl_group_id` int(11) DEFAULT NULL,
  KEY `acl_res_id` (`acl_res_id`),
  KEY `acl_group_id` (`acl_group_id`),
  CONSTRAINT `acl_res_group_relations_ibfk_1` FOREIGN KEY (`acl_res_id`) REFERENCES `acl_resources` (`acl_res_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_res_group_relations_ibfk_2` FOREIGN KEY (`acl_group_id`) REFERENCES `acl_groups` (`acl_group_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_resources` (
  `acl_res_id` int(11) NOT NULL AUTO_INCREMENT,
  `acl_res_name` varchar(255) DEFAULT NULL,
  `acl_res_alias` varchar(255) DEFAULT NULL,
  `all_hosts` enum('0','1') DEFAULT NULL,
  `all_hostgroups` enum('0','1') DEFAULT NULL,
  `all_servicegroups` enum('0','1') DEFAULT NULL,
  `acl_res_activate` enum('0','1','2') DEFAULT NULL,
  `acl_res_comment` text,
  `acl_res_status` enum('0','1') DEFAULT NULL,
  `changed` int(11) DEFAULT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`acl_res_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_resources_hc_relations` (
  `hc_id` int(11) DEFAULT NULL,
  `acl_res_id` int(11) DEFAULT NULL,
  KEY `hc_id` (`hc_id`),
  KEY `acl_res_id` (`acl_res_id`),
  CONSTRAINT `acl_resources_hc_relations_ibfk_1` FOREIGN KEY (`hc_id`) REFERENCES `hostcategories` (`hc_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_resources_hc_relations_ibfk_2` FOREIGN KEY (`acl_res_id`) REFERENCES `acl_resources` (`acl_res_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_resources_hg_relations` (
  `hg_hg_id` int(11) DEFAULT NULL,
  `acl_res_id` int(11) DEFAULT NULL,
  KEY `hg_hg_id` (`hg_hg_id`),
  KEY `acl_res_id` (`acl_res_id`),
  KEY `hg_hg_id_2` (`hg_hg_id`,`acl_res_id`),
  CONSTRAINT `acl_resources_hg_relations_ibfk_2` FOREIGN KEY (`acl_res_id`) REFERENCES `acl_resources` (`acl_res_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_resources_hg_relations_ibfk_1` FOREIGN KEY (`hg_hg_id`) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_resources_host_relations` (
  `host_host_id` int(11) DEFAULT NULL,
  `acl_res_id` int(11) DEFAULT NULL,
  KEY `host_host_id` (`host_host_id`),
  KEY `acl_res_id` (`acl_res_id`),
  CONSTRAINT `acl_resources_host_relations_ibfk_1` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_resources_host_relations_ibfk_2` FOREIGN KEY (`acl_res_id`) REFERENCES `acl_resources` (`acl_res_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_resources_hostex_relations` (
  `host_host_id` int(11) DEFAULT NULL,
  `acl_res_id` int(11) DEFAULT NULL,
  KEY `host_host_id` (`host_host_id`),
  KEY `acl_res_id` (`acl_res_id`),
  CONSTRAINT `acl_resources_hostex_relations_ibfk_1` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_resources_hostex_relations_ibfk_2` FOREIGN KEY (`acl_res_id`) REFERENCES `acl_resources` (`acl_res_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_resources_meta_relations` (
  `meta_id` int(11) DEFAULT NULL,
  `acl_res_id` int(11) DEFAULT NULL,
  KEY `meta_id` (`meta_id`),
  KEY `acl_res_id` (`acl_res_id`),
  CONSTRAINT `acl_resources_meta_relations_ibfk_2` FOREIGN KEY (`acl_res_id`) REFERENCES `acl_resources` (`acl_res_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_resources_meta_relations_ibfk_1` FOREIGN KEY (`meta_id`) REFERENCES `meta_service` (`meta_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_resources_poller_relations` (
  `poller_id` int(11) DEFAULT NULL,
  `acl_res_id` int(11) DEFAULT NULL,
  KEY `poller_id` (`poller_id`),
  KEY `acl_res_id` (`acl_res_id`),
  CONSTRAINT `acl_resources_poller_relations_ibfk_1` FOREIGN KEY (`poller_id`) REFERENCES `nagios_server` (`id`) ON DELETE CASCADE,
  CONSTRAINT `acl_resources_poller_relations_ibfk_2` FOREIGN KEY (`acl_res_id`) REFERENCES `acl_resources` (`acl_res_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_resources_sc_relations` (
  `sc_id` int(11) DEFAULT NULL,
  `acl_res_id` int(11) DEFAULT NULL,
  KEY `sc_id` (`sc_id`),
  KEY `acl_res_id` (`acl_res_id`),
  CONSTRAINT `acl_resources_sc_relations_ibfk_1` FOREIGN KEY (`sc_id`) REFERENCES `service_categories` (`sc_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_resources_sc_relations_ibfk_2` FOREIGN KEY (`acl_res_id`) REFERENCES `acl_resources` (`acl_res_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_resources_service_relations` (
  `service_service_id` int(11) DEFAULT NULL,
  `acl_group_id` int(11) DEFAULT NULL,
  KEY `service_service_id` (`service_service_id`),
  KEY `acl_group_id` (`acl_group_id`),
  CONSTRAINT `acl_resources_service_relations_ibfk_1` FOREIGN KEY (`service_service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_resources_service_relations_ibfk_2` FOREIGN KEY (`acl_group_id`) REFERENCES `acl_groups` (`acl_group_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_resources_sg_relations` (
  `sg_id` int(11) DEFAULT NULL,
  `acl_res_id` int(11) DEFAULT NULL,
  KEY `sg_id` (`sg_id`),
  KEY `acl_res_id` (`acl_res_id`),
  KEY `sg_id_2` (`sg_id`,`acl_res_id`),
  CONSTRAINT `acl_resources_sg_relations_ibfk_2` FOREIGN KEY (`acl_res_id`) REFERENCES `acl_resources` (`acl_res_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_resources_sg_relations_ibfk_1` FOREIGN KEY (`sg_id`) REFERENCES `servicegroup` (`sg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_topology` (
  `acl_topo_id` int(11) NOT NULL AUTO_INCREMENT,
  `acl_topo_name` varchar(255) DEFAULT NULL,
  `acl_topo_alias` varchar(255) DEFAULT NULL,
  `acl_comments` text,
  `acl_topo_activate` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`acl_topo_id`),
  KEY `acl_topo_id` (`acl_topo_id`,`acl_topo_activate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_topology_relations` (
  `topology_topology_id` int(11) DEFAULT NULL,
  `acl_topo_id` int(11) DEFAULT NULL,
  `access_right` tinyint(4) NOT NULL DEFAULT '1',
  KEY `topology_topology_id` (`topology_topology_id`),
  KEY `acl_topo_id` (`acl_topo_id`),
  CONSTRAINT `acl_topology_relations_ibfk_2` FOREIGN KEY (`topology_topology_id`) REFERENCES `topology` (`topology_id`) ON DELETE CASCADE,
  CONSTRAINT `acl_topology_relations_ibfk_3` FOREIGN KEY (`acl_topo_id`) REFERENCES `acl_topology` (`acl_topo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_ressource` (
  `ar_id` int(11) NOT NULL AUTO_INCREMENT,
  `ar_name` varchar(255) NOT NULL DEFAULT 'Default',
  `ar_description` varchar(255) NOT NULL DEFAULT 'Default description',
  `ar_type` varchar(50) NOT NULL DEFAULT 'ldap',
  `ar_enable` enum('0','1') DEFAULT '0',
  `ar_sync_base_date` int(11) DEFAULT 0,
  PRIMARY KEY (`ar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_ressource_host` (
  `ldap_host_id` int(11) NOT NULL AUTO_INCREMENT,
  `auth_ressource_id` int(11) NOT NULL,
  `host_address` varchar(255) NOT NULL,
  `host_port` int(11) NOT NULL,
  `use_ssl` tinyint(4) DEFAULT '0',
  `use_tls` tinyint(4) DEFAULT '0',
  `host_order` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ldap_host_id`),
  KEY `fk_auth_ressource_id` (`auth_ressource_id`),
  CONSTRAINT `fk_auth_ressource_id` FOREIGN KEY (`auth_ressource_id`) REFERENCES `auth_ressource` (`ar_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_ressource_info` (
  `ar_id` int(11) NOT NULL,
  `ari_name` varchar(100) NOT NULL,
  `ari_value` varchar(1024) NOT NULL,
  PRIMARY KEY (`ar_id`,`ari_name`),
  CONSTRAINT `auth_ressource_info_ibfk_1` FOREIGN KEY (`ar_id`) REFERENCES `auth_ressource` (`ar_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cb_fieldset` (
  `cb_fieldset_id` INT NOT NULL,
  `fieldset_name` VARCHAR(255) NOT NULL,
  PRIMARY KEY(`cb_fieldset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cb_fieldgroup` (
  `cb_fieldgroup_id` INT NOT NULL AUTO_INCREMENT,
  `groupname` VARCHAR(100) NOT NULL,
  `displayname` VARCHAR(255) NOT NULL DEFAULT '',
  `multiple` TINYINT NOT NULL DEFAULT 0,
  `group_parent_id` INT DEFAULT NULL,
  PRIMARY KEY(`cb_fieldgroup_id`),
  FOREIGN KEY(`group_parent_id`) REFERENCES `cb_fieldgroup` (`cb_fieldgroup_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cb_field` (
  `cb_field_id` int(11) NOT NULL AUTO_INCREMENT,
  `fieldname` varchar(100) NOT NULL,
  `displayname` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `fieldtype` varchar(255) NOT NULL DEFAULT 'text',
  `external` varchar(255) DEFAULT NULL,
  `cb_fieldgroup_id` INT DEFAULT NULL,
  PRIMARY KEY (`cb_field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cb_list` (
  `cb_list_id` int(11) NOT NULL,
  `cb_field_id` int(11) NOT NULL,
  `default_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cb_list_id`,`cb_field_id`),
  UNIQUE KEY `cb_field_idx_01` (`cb_field_id`),
  KEY `fk_cb_list_1` (`cb_field_id`),
  CONSTRAINT `fk_cb_list_1` FOREIGN KEY (`cb_field_id`) REFERENCES `cb_field` (`cb_field_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cb_list_values` (
  `cb_list_id` int(11) NOT NULL,
  `value_name` varchar(255) NOT NULL,
  `value_value` varchar(255) NOT NULL,
  PRIMARY KEY (`cb_list_id`,`value_name`),
  KEY `fk_cb_list_values_1` (`cb_list_id`),
  CONSTRAINT `fk_cb_list_values_1` FOREIGN KEY (`cb_list_id`) REFERENCES `cb_list` (`cb_list_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cb_module` (
  `cb_module_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `libname` varchar(50) DEFAULT NULL,
  `loading_pos` int(11) DEFAULT NULL,
  `is_bundle` int(1) NOT NULL DEFAULT '0',
  `is_activated` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cb_module_id`),
  UNIQUE KEY `cb_module_idx01` (`name`),
  UNIQUE KEY `cb_module_idx02` (`libname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cb_module_relation` (
  `cb_module_id` int(11) NOT NULL,
  `module_depend_id` int(11) NOT NULL,
  `inherit_config` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cb_module_id`,`module_depend_id`),
  KEY `fk_cb_module_relation_1` (`cb_module_id`),
  KEY `fk_cb_module_relation_2` (`module_depend_id`),
  CONSTRAINT `fk_cb_module_relation_1` FOREIGN KEY (`cb_module_id`) REFERENCES `cb_module` (`cb_module_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_cb_module_relation_2` FOREIGN KEY (`module_depend_id`) REFERENCES `cb_module` (`cb_module_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cb_tag` (
  `cb_tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `tagname` varchar(50) NOT NULL,
  PRIMARY KEY (`cb_tag_id`),
  UNIQUE KEY `cb_tag_ix01` (`tagname`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cb_tag_type_relation` (
  `cb_tag_id` int(11) NOT NULL,
  `cb_type_id` int(11) NOT NULL,
  `cb_type_uniq` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cb_tag_id`,`cb_type_id`),
  KEY `fk_cb_tag_type_relation_1` (`cb_tag_id`),
  KEY `fk_cb_tag_type_relation_2` (`cb_type_id`),
  CONSTRAINT `fk_cb_tag_type_relation_1` FOREIGN KEY (`cb_tag_id`) REFERENCES `cb_tag` (`cb_tag_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_cb_tag_type_relation_2` FOREIGN KEY (`cb_type_id`) REFERENCES `cb_type` (`cb_type_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cb_type` (
  `cb_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(50) NOT NULL,
  `type_shortname` varchar(50) NOT NULL,
  `cb_module_id` int(11) NOT NULL,
  PRIMARY KEY (`cb_type_id`),
  KEY `fk_cb_type_1` (`cb_module_id`),
  CONSTRAINT `fk_cb_type_1` FOREIGN KEY (`cb_module_id`) REFERENCES `cb_module` (`cb_module_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cb_type_field_relation` (
  `cb_type_id` int(11) NOT NULL,
  `cb_field_id` int(11) NOT NULL,
  `cb_fieldset_id` INT,
  `is_required` int(11) NOT NULL DEFAULT '0',
  `order_display` int(11) NOT NULL DEFAULT '0',
  `jshook_name` VARCHAR(255) DEFAULT NULL,
  `jshook_arguments` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`cb_type_id`,`cb_field_id`),
  KEY `fk_cb_type_field_relation_1` (`cb_type_id`),
  KEY `fk_cb_type_field_relation_2` (`cb_field_id`),
  CONSTRAINT `fk_cb_type_field_relation_1` FOREIGN KEY (`cb_type_id`) REFERENCES `cb_type` (`cb_type_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_cb_type_field_relation_2` FOREIGN KEY (`cb_field_id`) REFERENCES `cb_field` (`cb_field_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cfg_centreonbroker` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT,
  `config_name` varchar(100) NOT NULL,
  `config_filename` varchar(255) NOT NULL,
  `config_write_timestamp` enum('0','1') DEFAULT '1',
  `config_write_thread_id` enum('0','1') DEFAULT '1',
  `config_activate` enum('0','1') DEFAULT '0',
  `ns_nagios_server` int(11) NOT NULL,
  `event_queue_max_size` int(11) DEFAULT '100000',
  `command_file` varchar(255),
  `cache_directory` varchar(255),
  `stats_activate` enum('0','1') DEFAULT '1',
  `daemon` TINYINT(1),
  `pool_size` int(11) DEFAULT NULL,
  PRIMARY KEY (`config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cfg_centreonbroker_info` (
  `config_id` int(11) NOT NULL,
  `config_key` varchar(50) NOT NULL,
  `config_value` varchar(255) NOT NULL,
  `config_group` varchar(50) NOT NULL,
  `config_group_id` int(11) DEFAULT NULL,
  `grp_level` INT NOT NULL DEFAULT 0,
  `subgrp_id` INT DEFAULT NULL,
  `parent_grp_id` INT DEFAULT NULL,
  `fieldIndex` INT DEFAULT NULL,
  KEY `cfg_centreonbroker_info_idx01` (`config_id`),
  KEY `cfg_centreonbroker_info_idx02` (`config_id`,`config_group`),
  CONSTRAINT `cfg_centreonbroker_info_ibfk_01` FOREIGN KEY (`config_id`) REFERENCES `cfg_centreonbroker` (`config_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cfg_nagios` (
  `nagios_id` int(11) NOT NULL AUTO_INCREMENT,
  `nagios_name` varchar(255) DEFAULT NULL,
  `use_timezone` int(11) unsigned DEFAULT NULL,
  `log_file` varchar(255) DEFAULT NULL,
  `cfg_dir` varchar(255) DEFAULT NULL,
  `temp_file` varchar(255) DEFAULT NULL,
  `status_file` varchar(255) DEFAULT NULL,
  `status_update_interval` int(11) DEFAULT NULL,
  `nagios_user` varchar(255) DEFAULT NULL,
  `nagios_group` varchar(255) DEFAULT NULL,
  `enable_notifications` enum('0','1','2') DEFAULT NULL,
  `execute_service_checks` enum('0','1','2') DEFAULT NULL,
  `accept_passive_service_checks` enum('0','1','2') DEFAULT NULL,
  `execute_host_checks` enum('0','1','2') DEFAULT NULL,
  `accept_passive_host_checks` enum('0','1','2') DEFAULT NULL,
  `enable_event_handlers` enum('0','1','2') DEFAULT NULL,
  `log_rotation_method` varchar(255) DEFAULT NULL,
  `log_archive_path` varchar(255) DEFAULT NULL,
  `check_external_commands` enum('0','1','2') DEFAULT NULL,
  `external_command_buffer_slots` int(11) DEFAULT NULL,
  `command_check_interval` varchar(255) DEFAULT NULL,
  `command_file` varchar(255) DEFAULT NULL,
  `downtime_file` varchar(255) DEFAULT NULL,
  `comment_file` varchar(255) DEFAULT NULL,
  `lock_file` varchar(255) DEFAULT NULL,
  `retain_state_information` enum('0','1','2') DEFAULT NULL,
  `state_retention_file` varchar(255) DEFAULT NULL,
  `retention_update_interval` int(11) DEFAULT NULL,
  `use_retained_program_state` enum('0','1','2') DEFAULT NULL,
  `use_retained_scheduling_info` enum('0','1','2') DEFAULT NULL,
  `retained_contact_host_attribute_mask` bigint(20) DEFAULT NULL,
  `retained_contact_service_attribute_mask` bigint(20) DEFAULT NULL,
  `retained_process_host_attribute_mask` bigint(20) DEFAULT NULL,
  `retained_process_service_attribute_mask` bigint(20) DEFAULT NULL,
  `retained_host_attribute_mask` bigint(20) DEFAULT NULL,
  `retained_service_attribute_mask` bigint(20) DEFAULT NULL,
  `use_syslog` enum('0','1','2') DEFAULT NULL,
  `log_notifications` enum('0','1','2') DEFAULT NULL,
  `log_service_retries` enum('0','1','2') DEFAULT NULL,
  `log_host_retries` enum('0','1','2') DEFAULT NULL,
  `log_event_handlers` enum('0','1','2') DEFAULT NULL,
  `log_initial_states` enum('0','1','2') DEFAULT NULL,
  `log_external_commands` enum('0','1','2') DEFAULT NULL,
  `log_passive_checks` enum('0','1','2') DEFAULT NULL,
  `global_host_event_handler` int(11) DEFAULT NULL,
  `global_service_event_handler` int(11) DEFAULT NULL,
  `sleep_time` varchar(10) DEFAULT NULL,
  `service_inter_check_delay_method` varchar(255) DEFAULT NULL,
  `host_inter_check_delay_method` varchar(255) DEFAULT NULL,
  `service_interleave_factor` varchar(255) DEFAULT NULL,
  `max_concurrent_checks` int(11) DEFAULT NULL,
  `max_service_check_spread` int(11) DEFAULT NULL,
  `max_host_check_spread` int(11) DEFAULT NULL,
  `check_result_reaper_frequency` int(11) DEFAULT NULL,
  `max_check_result_reaper_time` int(11) DEFAULT NULL,
  `interval_length` int(11) DEFAULT NULL,
  `auto_reschedule_checks` enum('0','1','2') DEFAULT NULL,
  `auto_rescheduling_interval` int(11) DEFAULT NULL,
  `auto_rescheduling_window` int(11) DEFAULT NULL,
  `use_aggressive_host_checking` enum('0','1','2') DEFAULT NULL,
  `enable_flap_detection` enum('0','1','2') DEFAULT NULL,
  `low_service_flap_threshold` varchar(255) DEFAULT NULL,
  `high_service_flap_threshold` varchar(255) DEFAULT NULL,
  `low_host_flap_threshold` varchar(255) DEFAULT NULL,
  `high_host_flap_threshold` varchar(255) DEFAULT NULL,
  `soft_state_dependencies` enum('0','1','2') DEFAULT NULL,
  `service_check_timeout` int(11) DEFAULT NULL,
  `host_check_timeout` int(11) DEFAULT NULL,
  `event_handler_timeout` int(11) DEFAULT NULL,
  `notification_timeout` int(11) DEFAULT NULL,
  `ocsp_timeout` int(11) DEFAULT NULL,
  `ochp_timeout` int(11) DEFAULT NULL,
  `perfdata_timeout` int(11) DEFAULT NULL,
  `obsess_over_services` enum('0','1','2') DEFAULT NULL,
  `ocsp_command` int(11) DEFAULT NULL,
  `obsess_over_hosts` enum('0','1','2') DEFAULT NULL,
  `ochp_command` int(11) DEFAULT NULL,
  `process_performance_data` enum('0','1','2') DEFAULT NULL,
  `host_perfdata_command` int(11) DEFAULT NULL,
  `service_perfdata_command` int(11) DEFAULT NULL,
  `host_perfdata_file` varchar(255) DEFAULT NULL,
  `service_perfdata_file` varchar(255) DEFAULT NULL,
  `host_perfdata_file_template` text,
  `service_perfdata_file_template` text,
  `host_perfdata_file_mode` enum('a','w','2') DEFAULT NULL,
  `service_perfdata_file_mode` enum('a','w','2') DEFAULT NULL,
  `host_perfdata_file_processing_interval` int(11) DEFAULT NULL,
  `service_perfdata_file_processing_interval` int(11) DEFAULT NULL,
  `host_perfdata_file_processing_command` int(11) DEFAULT NULL,
  `service_perfdata_file_processing_command` int(11) DEFAULT NULL,
  `check_for_orphaned_services` enum('0','1','2') DEFAULT NULL,
  `check_for_orphaned_hosts` enum('0','1','2') DEFAULT NULL,
  `check_service_freshness` enum('0','1','2') DEFAULT NULL,
  `service_freshness_check_interval` int(11) DEFAULT NULL,
  `freshness_check_interval` int(11) DEFAULT NULL,
  `check_host_freshness` enum('0','1','2') DEFAULT NULL,
  `host_freshness_check_interval` int(11) DEFAULT NULL,
  `date_format` varchar(255) DEFAULT NULL,
  `illegal_object_name_chars` varchar(255) DEFAULT NULL,
  `illegal_macro_output_chars` varchar(255) DEFAULT NULL,
  `use_regexp_matching` enum('0','1','2') DEFAULT NULL,
  `use_true_regexp_matching` enum('0','1','2') DEFAULT NULL,
  `admin_email` varchar(255) DEFAULT NULL,
  `admin_pager` varchar(255) DEFAULT NULL,
  `nagios_comment` text,
  `nagios_activate` enum('0','1') DEFAULT NULL,
  `event_broker_options` varchar(255) DEFAULT NULL,
  `translate_passive_host_checks` int(11) DEFAULT NULL,
  `nagios_server_id` int(11) DEFAULT NULL,
  `enable_predictive_host_dependency_checks` enum('0','1','2') DEFAULT NULL,
  `enable_predictive_service_dependency_checks` enum('0','1','2') DEFAULT NULL,
  `cached_host_check_horizon` int(11) DEFAULT NULL,
  `cached_service_check_horizon` int(11) DEFAULT NULL,
  `passive_host_checks_are_soft` int(11) DEFAULT NULL,
  `use_large_installation_tweaks` enum('0','1','2') DEFAULT NULL,
  `enable_environment_macros` enum('0','1','2') DEFAULT NULL,
  `use_setpgid` enum('0','1','2') DEFAULT NULL,
  `additional_freshness_latency` int(11) DEFAULT NULL,
  `debug_file` varchar(255) DEFAULT NULL,
  `debug_level` int(11) DEFAULT NULL,
  `debug_level_opt` varchar(200) DEFAULT '0',
  `debug_verbosity` enum('0','1','2') DEFAULT NULL,
  `max_debug_file_size` int(11) DEFAULT NULL,
  `daemon_dumps_core` enum('0','1') DEFAULT NULL,
  `cfg_file` varchar(255) NOT NULL DEFAULT 'centengine.cfg',
  `log_pid` enum('0','1') DEFAULT '1',
  `enable_macros_filter` enum('0', '1') DEFAULT '0',
  `macros_filter` TEXT DEFAULT '',
  PRIMARY KEY (`nagios_id`),
  KEY `cmd1_index` (`global_host_event_handler`),
  KEY `cmd2_index` (`global_service_event_handler`),
  KEY `cmd3_index` (`ocsp_command`),
  KEY `cmd4_index` (`ochp_command`),
  KEY `cmd5_index` (`host_perfdata_command`),
  KEY `cmd6_index` (`service_perfdata_command`),
  KEY `cmd7_index` (`host_perfdata_file_processing_command`),
  KEY `cmd8_index` (`service_perfdata_file_processing_command`),
  KEY `nagios_server_id` (`nagios_server_id`),
  CONSTRAINT `cfg_nagios_ibfk_15` FOREIGN KEY (`service_perfdata_command`) REFERENCES `command` (`command_id`) ON DELETE SET NULL,
  CONSTRAINT `cfg_nagios_ibfk_18` FOREIGN KEY (`global_host_event_handler`) REFERENCES `command` (`command_id`) ON DELETE SET NULL,
  CONSTRAINT `cfg_nagios_ibfk_19` FOREIGN KEY (`global_service_event_handler`) REFERENCES `command` (`command_id`) ON DELETE SET NULL,
  CONSTRAINT `cfg_nagios_ibfk_20` FOREIGN KEY (`ocsp_command`) REFERENCES `command` (`command_id`) ON DELETE SET NULL,
  CONSTRAINT `cfg_nagios_ibfk_21` FOREIGN KEY (`ochp_command`) REFERENCES `command` (`command_id`) ON DELETE SET NULL,
  CONSTRAINT `cfg_nagios_ibfk_22` FOREIGN KEY (`host_perfdata_command`) REFERENCES `command` (`command_id`) ON DELETE SET NULL,
  CONSTRAINT `cfg_nagios_ibfk_23` FOREIGN KEY (`service_perfdata_command`) REFERENCES `command` (`command_id`) ON DELETE SET NULL,
  CONSTRAINT `cfg_nagios_ibfk_24` FOREIGN KEY (`host_perfdata_file_processing_command`) REFERENCES `command` (`command_id`) ON DELETE SET NULL,
  CONSTRAINT `cfg_nagios_ibfk_25` FOREIGN KEY (`service_perfdata_file_processing_command`) REFERENCES `command` (`command_id`) ON DELETE SET NULL,
  CONSTRAINT `cfg_nagios_ibfk_26` FOREIGN KEY (`nagios_server_id`) REFERENCES `nagios_server` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cfg_nagios_ibfk_27` FOREIGN KEY (`use_timezone`) REFERENCES `timezone` (`timezone_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cfg_nagios_broker_module` (
  `bk_mod_id` int(11) NOT NULL AUTO_INCREMENT,
  `cfg_nagios_id` int(11) DEFAULT NULL,
  `broker_module` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`bk_mod_id`),
  KEY `fk_nagios_cfg` (`cfg_nagios_id`),
  CONSTRAINT `fk_nagios_cfg` FOREIGN KEY (`cfg_nagios_id`) REFERENCES `cfg_nagios` (`nagios_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cfg_resource` (
  `resource_id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_name` varchar(255) DEFAULT NULL,
  `resource_line` varchar(255) DEFAULT NULL,
  `resource_comment` varchar(255) DEFAULT NULL,
  `resource_activate` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cfg_resource_instance_relations` (
  `resource_id` int(11) NOT NULL,
  `instance_id` int(11) NOT NULL,
  KEY `fk_crir_res_id` (`resource_id`),
  KEY `fk_crir_ins_id` (`instance_id`),
  CONSTRAINT `fk_crir_res_id` FOREIGN KEY (`resource_id`) REFERENCES `cfg_resource` (`resource_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_crir_ins_id` FOREIGN KEY (`instance_id`) REFERENCES `nagios_server` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `command` (
  `command_id` int(11) NOT NULL AUTO_INCREMENT,
  `connector_id` int(10) unsigned DEFAULT NULL,
  `command_name` varchar(200) DEFAULT NULL,
  `command_line` text,
  `command_example` varchar(254) DEFAULT NULL,
  `command_type` tinyint(4) DEFAULT NULL,
  `enable_shell` int(1) unsigned NOT NULL DEFAULT '0',
  `command_comment` text,
  `command_activate` enum('0','1') DEFAULT '1',
  `graph_id` int(11) DEFAULT NULL,
  `cmd_cat_id` int(11) DEFAULT NULL,
  `command_locked` BOOLEAN DEFAULT 0,
  PRIMARY KEY (`command_id`),
  KEY `connector_id` (`connector_id`),
  CONSTRAINT `command_ibfk_1` FOREIGN KEY (`connector_id`) REFERENCES `connector` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `command_arg_description` (
  `cmd_id` int(11) NOT NULL,
  `macro_name` varchar(255) NOT NULL,
  `macro_description` varchar(255) NOT NULL,
  KEY `command_arg_description_ibfk_1` (`cmd_id`),
  CONSTRAINT `command_arg_description_ibfk_1` FOREIGN KEY (`cmd_id`) REFERENCES `command` (`command_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `command_categories` (
  `cmd_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `category_alias` varchar(255) NOT NULL,
  `category_order` int(11) NOT NULL,
  PRIMARY KEY (`cmd_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `command_categories_relation` (
  `category_id` int(11) DEFAULT NULL,
  `command_command_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `connector` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `command_line` varchar(512) NOT NULL,
  `enabled` int(1) unsigned NOT NULL DEFAULT '1',
  `created` int(10) unsigned NOT NULL,
  `modified` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact` (
  `contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `timeperiod_tp_id` int(11) DEFAULT NULL,
  `timeperiod_tp_id2` int(11) DEFAULT NULL,
  `contact_name` varchar(200) DEFAULT NULL,
  `contact_alias` varchar(200) DEFAULT NULL,
  `contact_passwd` varchar(255) DEFAULT NULL,
  `contact_lang` varchar(255) DEFAULT 'browser',
  `contact_host_notification_options` varchar(200) DEFAULT NULL,
  `contact_service_notification_options` varchar(200) DEFAULT NULL,
  `contact_email` varchar(200) DEFAULT NULL,
  `contact_pager` varchar(200) DEFAULT NULL,
  `contact_address1` varchar(200) DEFAULT NULL,
  `contact_address2` varchar(200) DEFAULT NULL,
  `contact_address3` varchar(200) DEFAULT NULL,
  `contact_address4` varchar(200) DEFAULT NULL,
  `contact_address5` varchar(200) DEFAULT NULL,
  `contact_address6` varchar(200) DEFAULT NULL,
  `contact_comment` text,
  `contact_js_effects` enum('0','1') DEFAULT '0',
  `contact_location` int(11) DEFAULT '0',
  `contact_oreon` enum('0','1') DEFAULT NULL,
  `reach_api` int(11) DEFAULT '0',
  `reach_api_rt` int(1) DEFAULT 0,
  `contact_enable_notifications` enum('0','1','2') DEFAULT '2',
  `contact_template_id` int(11) DEFAULT NULL,
  `contact_admin` enum('0','1') DEFAULT '0',
  `contact_type_msg` enum('txt','html','pdf') DEFAULT 'txt',
  `contact_activate` enum('0','1') DEFAULT NULL,
  `contact_auth_type` varchar(255) DEFAULT '',
  `contact_ldap_dn` text,
  `ar_id` int(11) DEFAULT NULL,
  `contact_acl_group_list` varchar(255) DEFAULT NULL,
  `contact_autologin_key` varchar(255) DEFAULT NULL,
  `default_page` int(11) DEFAULT NULL,
  `show_deprecated_pages` enum('0','1') DEFAULT '0',
  `contact_charset` varchar(255) DEFAULT NULL,
  `contact_register` tinyint(6) NOT NULL DEFAULT '1',
  `contact_ldap_last_sync` int(11) NOT NULL DEFAULT 0,
  `contact_ldap_required_sync` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`contact_id`),
  KEY `name_index` (`contact_name`),
  KEY `alias_index` (`contact_alias`),
  KEY `tp1_index` (`timeperiod_tp_id`),
  KEY `tp2_index` (`timeperiod_tp_id2`),
  KEY `tmpl_index` (`contact_template_id`),
  KEY `fk_ar_id` (`ar_id`),
  CONSTRAINT `contact_ibfk_1` FOREIGN KEY (`timeperiod_tp_id`) REFERENCES `timeperiod` (`tp_id`) ON DELETE SET NULL,
  CONSTRAINT `contact_ibfk_2` FOREIGN KEY (`timeperiod_tp_id2`) REFERENCES `timeperiod` (`tp_id`) ON DELETE SET NULL,
  CONSTRAINT `contact_ibfk_3` FOREIGN KEY (`contact_template_id`) REFERENCES `contact` (`contact_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ar_id` FOREIGN KEY (`ar_id`) REFERENCES `auth_ressource` (`ar_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_host_relation` (
  `host_host_id` int(11) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  KEY `host_index` (`host_host_id`),
  KEY `contact_id` (`contact_id`),
  CONSTRAINT `contact_host_relation_ibfk_2` FOREIGN KEY (`contact_id`) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE,
  CONSTRAINT `contact_host_relation_ibfk_1` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_hostcommands_relation` (
  `contact_contact_id` int(11) DEFAULT NULL,
  `command_command_id` int(11) DEFAULT NULL,
  KEY `contact_index` (`contact_contact_id`),
  KEY `command_index` (`command_command_id`),
  CONSTRAINT `contact_hostcommands_relation_ibfk_1` FOREIGN KEY (`contact_contact_id`) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE,
  CONSTRAINT `contact_hostcommands_relation_ibfk_2` FOREIGN KEY (`command_command_id`) REFERENCES `command` (`command_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_param` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `cp_key` varchar(255) NOT NULL,
  `cp_value` varchar(255) NOT NULL,
  `cp_contact_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`cp_contact_id`),
  CONSTRAINT `contact_param_ibfk_1` FOREIGN KEY (`cp_contact_id`) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_service_relation` (
  `service_service_id` int(11) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  KEY `service_index` (`service_service_id`),
  KEY `contact_id` (`contact_id`),
  CONSTRAINT `contact_service_relation_ibfk_2` FOREIGN KEY (`contact_id`) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE,
  CONSTRAINT `contact_service_relation_ibfk_1` FOREIGN KEY (`service_service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_servicecommands_relation` (
  `contact_contact_id` int(11) DEFAULT NULL,
  `command_command_id` int(11) DEFAULT NULL,
  KEY `contact_index` (`contact_contact_id`),
  KEY `command_index` (`command_command_id`),
  CONSTRAINT `contact_servicecommands_relation_ibfk_1` FOREIGN KEY (`contact_contact_id`) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE,
  CONSTRAINT `contact_servicecommands_relation_ibfk_2` FOREIGN KEY (`command_command_id`) REFERENCES `command` (`command_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactgroup` (
  `cg_id` int(11) NOT NULL AUTO_INCREMENT,
  `cg_name` varchar(200) DEFAULT NULL,
  `cg_alias` varchar(200) DEFAULT NULL,
  `cg_comment` text,
  `cg_activate` enum('0','1') DEFAULT NULL,
  `cg_type` varchar(10) DEFAULT 'local',
  `cg_ldap_dn` varchar(255) DEFAULT NULL,
  `ar_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`cg_id`),
  KEY `name_index` (`cg_name`),
  KEY `alias_index` (`cg_alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactgroup_contact_relation` (
  `contact_contact_id` int(11) DEFAULT NULL,
  `contactgroup_cg_id` int(11) DEFAULT NULL,
  KEY `contact_index` (`contact_contact_id`),
  KEY `contactgroup_index` (`contactgroup_cg_id`),
  CONSTRAINT `contactgroup_contact_relation_ibfk_1` FOREIGN KEY (`contact_contact_id`) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE,
  CONSTRAINT `contactgroup_contact_relation_ibfk_2` FOREIGN KEY (`contactgroup_cg_id`) REFERENCES `contactgroup` (`cg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactgroup_host_relation` (
  `host_host_id` int(11) DEFAULT NULL,
  `contactgroup_cg_id` int(11) DEFAULT NULL,
  KEY `host_index` (`host_host_id`),
  KEY `contactgroup_index` (`contactgroup_cg_id`),
  CONSTRAINT `contactgroup_host_relation_ibfk_1` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `contactgroup_host_relation_ibfk_2` FOREIGN KEY (`contactgroup_cg_id`) REFERENCES `contactgroup` (`cg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactgroup_hostgroup_relation` (
  `contactgroup_cg_id` int(11) DEFAULT NULL,
  `hostgroup_hg_id` int(11) DEFAULT NULL,
  KEY `contactgroup_index` (`contactgroup_cg_id`),
  KEY `hostgroup_index` (`hostgroup_hg_id`),
  CONSTRAINT `contactgroup_hostgroup_relation_ibfk_1` FOREIGN KEY (`contactgroup_cg_id`) REFERENCES `contactgroup` (`cg_id`) ON DELETE CASCADE,
  CONSTRAINT `contactgroup_hostgroup_relation_ibfk_2` FOREIGN KEY (`hostgroup_hg_id`) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactgroup_service_relation` (
  `contactgroup_cg_id` int(11) DEFAULT NULL,
  `service_service_id` int(11) DEFAULT NULL,
  KEY `contactgroup_index` (`contactgroup_cg_id`),
  KEY `service_index` (`service_service_id`),
  CONSTRAINT `contactgroup_service_relation_ibfk_1` FOREIGN KEY (`contactgroup_cg_id`) REFERENCES `contactgroup` (`cg_id`) ON DELETE CASCADE,
  CONSTRAINT `contactgroup_service_relation_ibfk_2` FOREIGN KEY (`service_service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactgroup_servicegroup_relation` (
  `servicegroup_sg_id` int(11) DEFAULT NULL,
  `contactgroup_cg_id` int(11) DEFAULT NULL,
  KEY `servicegroup_index` (`servicegroup_sg_id`),
  KEY `contactgroup_index` (`contactgroup_cg_id`),
  CONSTRAINT `contactgroup_servicegroup_relation_ibfk_1` FOREIGN KEY (`contactgroup_cg_id`) REFERENCES `contactgroup` (`cg_id`) ON DELETE CASCADE,
  CONSTRAINT `contactgroup_servicegroup_relation_ibfk_2` FOREIGN KEY (`servicegroup_sg_id`) REFERENCES `servicegroup` (`sg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cron_operation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(254) DEFAULT NULL,
  `command` varchar(254) DEFAULT NULL,
  `time_launch` int(11) DEFAULT NULL,
  `last_modification` int(11) DEFAULT '0',
  `system` enum('0','1') DEFAULT NULL,
  `module` enum('0','1') DEFAULT NULL,
  `running` enum('0','1') DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `last_execution_time` int(11) NOT NULL DEFAULT '0',
  `activate` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `css_color_menu` (
  `id_css_color_menu` int(11) NOT NULL AUTO_INCREMENT,
  `menu_nb` int(11) DEFAULT NULL,
  `css_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_css_color_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_view_default` (
  `user_id` int(11) NOT NULL,
  `custom_view_id` int(11) NOT NULL,
  KEY `fk_custom_view_default_user_id` (`user_id`),
  KEY `fk_custom_view_default_cv_id` (`custom_view_id`),
  CONSTRAINT `fk_custom_view_default_user_id` FOREIGN KEY (`user_id`) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_custom_view_default_cv_id` FOREIGN KEY (`custom_view_id`) REFERENCES `custom_views` (`custom_view_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_view_user_relation` (
  `custom_view_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `usergroup_id` int(11) DEFAULT NULL,
  `locked` tinyint(6) DEFAULT '0',
  `is_owner` tinyint(6) DEFAULT '0',
  `is_share` tinyint(6) DEFAULT '0',
  `is_consumed` int(1) NOT NULL DEFAULT 1,
  UNIQUE KEY `view_user_unique_index` (`custom_view_id`,`user_id`),
  UNIQUE KEY `view_usergroup_unique_index` (`custom_view_id`,`usergroup_id`),
  KEY `fk_custom_views_user_id` (`user_id`),
  KEY `fk_custom_views_usergroup_id` (`usergroup_id`),
  CONSTRAINT `fk_custom_views_user_id` FOREIGN KEY (`user_id`) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_custom_views_usergroup_id` FOREIGN KEY (`usergroup_id`) REFERENCES `contactgroup` (`cg_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_custom_view_user_id` FOREIGN KEY (`custom_view_id`) REFERENCES `custom_views` (`custom_view_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_views` (
  `custom_view_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `layout` varchar(255) NOT NULL,
  `public` tinyint(6) null default 0,
  PRIMARY KEY (`custom_view_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dependency` (
  `dep_id` int(11) NOT NULL AUTO_INCREMENT,
  `dep_name` varchar(255) DEFAULT NULL,
  `dep_description` varchar(255) DEFAULT NULL,
  `inherits_parent` enum('0','1') DEFAULT NULL,
  `execution_failure_criteria` varchar(255) DEFAULT NULL,
  `notification_failure_criteria` varchar(255) DEFAULT NULL,
  `dep_comment` text,
  PRIMARY KEY (`dep_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dependency_hostChild_relation` (
  `dependency_dep_id` int(11) DEFAULT NULL,
  `host_host_id` int(11) DEFAULT NULL,
  KEY `dependency_index` (`dependency_dep_id`),
  KEY `host_index` (`host_host_id`),
  CONSTRAINT `dependency_hostChild_relation_ibfk_1` FOREIGN KEY (`dependency_dep_id`) REFERENCES `dependency` (`dep_id`) ON DELETE CASCADE,
  CONSTRAINT `dependency_hostChild_relation_ibfk_2` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dependency_hostParent_relation` (
  `dependency_dep_id` int(11) DEFAULT NULL,
  `host_host_id` int(11) DEFAULT NULL,
  KEY `dependency_index` (`dependency_dep_id`),
  KEY `host_index` (`host_host_id`),
  CONSTRAINT `dependency_hostParent_relation_ibfk_1` FOREIGN KEY (`dependency_dep_id`) REFERENCES `dependency` (`dep_id`) ON DELETE CASCADE,
  CONSTRAINT `dependency_hostParent_relation_ibfk_2` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dependency_hostgroupChild_relation` (
  `dependency_dep_id` int(11) DEFAULT NULL,
  `hostgroup_hg_id` int(11) DEFAULT NULL,
  KEY `dependency_index` (`dependency_dep_id`),
  KEY `hostgroup_index` (`hostgroup_hg_id`),
  CONSTRAINT `dependency_hostgroupChild_relation_ibfk_1` FOREIGN KEY (`dependency_dep_id`) REFERENCES `dependency` (`dep_id`) ON DELETE CASCADE,
  CONSTRAINT `dependency_hostgroupChild_relation_ibfk_2` FOREIGN KEY (`hostgroup_hg_id`) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dependency_hostgroupParent_relation` (
  `dependency_dep_id` int(11) DEFAULT NULL,
  `hostgroup_hg_id` int(11) DEFAULT NULL,
  KEY `dependency_index` (`dependency_dep_id`),
  KEY `hostgroup_index` (`hostgroup_hg_id`),
  CONSTRAINT `dependency_hostgroupParent_relation_ibfk_1` FOREIGN KEY (`dependency_dep_id`) REFERENCES `dependency` (`dep_id`) ON DELETE CASCADE,
  CONSTRAINT `dependency_hostgroupParent_relation_ibfk_2` FOREIGN KEY (`hostgroup_hg_id`) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dependency_metaserviceChild_relation` (
  `dependency_dep_id` int(11) DEFAULT NULL,
  `meta_service_meta_id` int(11) DEFAULT NULL,
  KEY `dependency_index` (`dependency_dep_id`),
  KEY `meta_service_index` (`meta_service_meta_id`),
  CONSTRAINT `dependency_metaserviceChild_relation_ibfk_1` FOREIGN KEY (`dependency_dep_id`) REFERENCES `dependency` (`dep_id`) ON DELETE CASCADE,
  CONSTRAINT `dependency_metaserviceChild_relation_ibfk_2` FOREIGN KEY (`meta_service_meta_id`) REFERENCES `meta_service` (`meta_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dependency_metaserviceParent_relation` (
  `dependency_dep_id` int(11) DEFAULT NULL,
  `meta_service_meta_id` int(11) DEFAULT NULL,
  KEY `dependency_index` (`dependency_dep_id`),
  KEY `meta_service_index` (`meta_service_meta_id`),
  CONSTRAINT `dependency_metaserviceParent_relation_ibfk_1` FOREIGN KEY (`dependency_dep_id`) REFERENCES `dependency` (`dep_id`) ON DELETE CASCADE,
  CONSTRAINT `dependency_metaserviceParent_relation_ibfk_2` FOREIGN KEY (`meta_service_meta_id`) REFERENCES `meta_service` (`meta_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dependency_serviceChild_relation` (
  `dependency_dep_id` int(11) DEFAULT NULL,
  `service_service_id` int(11) DEFAULT NULL,
  `host_host_id` int(11) DEFAULT NULL,
  KEY `dependency_index` (`dependency_dep_id`),
  KEY `service_index` (`service_service_id`),
  KEY `host_index` (`host_host_id`),
  CONSTRAINT `dependency_serviceChild_relation_ibfk_1` FOREIGN KEY (`dependency_dep_id`) REFERENCES `dependency` (`dep_id`) ON DELETE CASCADE,
  CONSTRAINT `dependency_serviceChild_relation_ibfk_2` FOREIGN KEY (`service_service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE,
  CONSTRAINT `dependency_serviceChild_relation_ibfk_3` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dependency_serviceParent_relation` (
  `dependency_dep_id` int(11) DEFAULT NULL,
  `service_service_id` int(11) DEFAULT NULL,
  `host_host_id` int(11) DEFAULT NULL,
  KEY `dependency_index` (`dependency_dep_id`),
  KEY `service_index` (`service_service_id`),
  KEY `host_index` (`host_host_id`),
  CONSTRAINT `dependency_serviceParent_relation_ibfk_1` FOREIGN KEY (`dependency_dep_id`) REFERENCES `dependency` (`dep_id`) ON DELETE CASCADE,
  CONSTRAINT `dependency_serviceParent_relation_ibfk_2` FOREIGN KEY (`service_service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE,
  CONSTRAINT `dependency_serviceParent_relation_ibfk_3` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dependency_servicegroupChild_relation` (
  `dependency_dep_id` int(11) DEFAULT NULL,
  `servicegroup_sg_id` int(11) DEFAULT NULL,
  KEY `dependency_index` (`dependency_dep_id`),
  KEY `sg_index` (`servicegroup_sg_id`),
  CONSTRAINT `dependency_servicegroupChild_relation_ibfk_1` FOREIGN KEY (`dependency_dep_id`) REFERENCES `dependency` (`dep_id`) ON DELETE CASCADE,
  CONSTRAINT `dependency_servicegroupChild_relation_ibfk_2` FOREIGN KEY (`servicegroup_sg_id`) REFERENCES `servicegroup` (`sg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dependency_servicegroupParent_relation` (
  `dependency_dep_id` int(11) DEFAULT NULL,
  `servicegroup_sg_id` int(11) DEFAULT NULL,
  KEY `dependency_index` (`dependency_dep_id`),
  KEY `sg_index` (`servicegroup_sg_id`),
  CONSTRAINT `dependency_servicegroupParent_relation_ibfk_1` FOREIGN KEY (`dependency_dep_id`) REFERENCES `dependency` (`dep_id`) ON DELETE CASCADE,
  CONSTRAINT `dependency_servicegroupParent_relation_ibfk_2` FOREIGN KEY (`servicegroup_sg_id`) REFERENCES `servicegroup` (`sg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `downtime` (
  `dt_id` int(11) NOT NULL AUTO_INCREMENT,
  `dt_name` varchar(100) NOT NULL,
  `dt_description` varchar(255) DEFAULT NULL,
  `dt_activate` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`dt_id`),
  UNIQUE KEY `downtime_idx02` (`dt_name`),
  KEY `downtime_idx01` (`dt_id`,`dt_activate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `downtime_host_relation` (
  `dt_id` int(11) NOT NULL,
  `host_host_id` int(11) NOT NULL,
  PRIMARY KEY (`dt_id`,`host_host_id`),
  KEY `downtime_host_relation_ibfk_1` (`host_host_id`),
  CONSTRAINT `downtime_host_relation_ibfk_1` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `downtime_host_relation_ibfk_2` FOREIGN KEY (`dt_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `downtime_hostgroup_relation` (
  `dt_id` int(11) NOT NULL,
  `hg_hg_id` int(11) NOT NULL,
  PRIMARY KEY (`dt_id`,`hg_hg_id`),
  KEY `downtime_hostgroup_relation_ibfk_1` (`hg_hg_id`),
  CONSTRAINT `downtime_hostgroup_relation_ibfk_1` FOREIGN KEY (`hg_hg_id`) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE,
  CONSTRAINT `downtime_hostgroup_relation_ibfk_2` FOREIGN KEY (`dt_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `downtime_period` (
  `dt_id` int(11) NOT NULL,
  `dtp_start_time` time NOT NULL,
  `dtp_end_time` time NOT NULL,
  `dtp_day_of_week` varchar(15) DEFAULT NULL,
  `dtp_month_cycle` varchar(100) DEFAULT 'all',
  `dtp_day_of_month` varchar(100) DEFAULT NULL,
  `dtp_fixed` enum('0','1') DEFAULT '1',
  `dtp_duration` int(11) DEFAULT NULL,
  `dtp_next_date` date DEFAULT NULL,
  `dtp_activate` enum('0','1') DEFAULT '1',
  KEY `downtime_period_idx01` (`dt_id`,`dtp_activate`),
  CONSTRAINT `downtime_period_ibfk_1` FOREIGN KEY (`dt_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `downtime_service_relation` (
  `dt_id` int(11) NOT NULL,
  `host_host_id` int(11) NOT NULL,
  `service_service_id` int(11) NOT NULL,
  PRIMARY KEY (`dt_id`,`host_host_id`,`service_service_id`),
  KEY `downtime_service_relation_ibfk_1` (`service_service_id`),
  KEY `downtime_service_relation_ibfk_3` (`host_host_id`),
  CONSTRAINT `downtime_service_relation_ibfk_1` FOREIGN KEY (`service_service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE,
  CONSTRAINT `downtime_service_relation_ibfk_3` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `downtime_service_relation_ibfk_2` FOREIGN KEY (`dt_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `downtime_servicegroup_relation` (
  `dt_id` int(11) NOT NULL,
  `sg_sg_id` int(11) NOT NULL,
  PRIMARY KEY (`dt_id`,`sg_sg_id`),
  KEY `downtime_servicegroup_relation_ibfk_1` (`sg_sg_id`),
  CONSTRAINT `downtime_servicegroup_relation_ibfk_1` FOREIGN KEY (`sg_sg_id`) REFERENCES `servicegroup` (`sg_id`) ON DELETE CASCADE,
  CONSTRAINT `downtime_servicegroup_relation_ibfk_2` FOREIGN KEY (`dt_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `escalation` (
  `esc_id` int(11) NOT NULL AUTO_INCREMENT,
  `esc_name` varchar(255) DEFAULT NULL,
  `esc_alias` varchar(255) DEFAULT NULL,
  `first_notification` int(11) DEFAULT NULL,
  `last_notification` int(11) DEFAULT NULL,
  `notification_interval` int(11) DEFAULT NULL,
  `escalation_period` int(11) DEFAULT NULL,
  `escalation_options1` varchar(255) DEFAULT NULL,
  `escalation_options2` varchar(255) DEFAULT NULL,
  `esc_comment` text,
  `host_inheritance_to_services` tinyint(1) DEFAULT 0 NOT NULL,
  `hostgroup_inheritance_to_services` tinyint(1) DEFAULT 0 NOT NULL,
  PRIMARY KEY (`esc_id`),
  KEY `period_index` (`escalation_period`),
  CONSTRAINT `escalation_ibfk_1` FOREIGN KEY (`escalation_period`) REFERENCES `timeperiod` (`tp_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `escalation_contactgroup_relation` (
  `escalation_esc_id` int(11) DEFAULT NULL,
  `contactgroup_cg_id` int(11) DEFAULT NULL,
  KEY `escalation_index` (`escalation_esc_id`),
  KEY `cg_index` (`contactgroup_cg_id`),
  CONSTRAINT `escalation_contactgroup_relation_ibfk_1` FOREIGN KEY (`escalation_esc_id`) REFERENCES `escalation` (`esc_id`) ON DELETE CASCADE,
  CONSTRAINT `escalation_contactgroup_relation_ibfk_2` FOREIGN KEY (`contactgroup_cg_id`) REFERENCES `contactgroup` (`cg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `escalation_host_relation` (
  `escalation_esc_id` int(11) DEFAULT NULL,
  `host_host_id` int(11) DEFAULT NULL,
  KEY `escalation_index` (`escalation_esc_id`),
  KEY `host_index` (`host_host_id`),
  CONSTRAINT `escalation_host_relation_ibfk_1` FOREIGN KEY (`escalation_esc_id`) REFERENCES `escalation` (`esc_id`) ON DELETE CASCADE,
  CONSTRAINT `escalation_host_relation_ibfk_2` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `escalation_hostgroup_relation` (
  `escalation_esc_id` int(11) DEFAULT NULL,
  `hostgroup_hg_id` int(11) DEFAULT NULL,
  KEY `escalation_index` (`escalation_esc_id`),
  KEY `hg_index` (`hostgroup_hg_id`),
  CONSTRAINT `escalation_hostgroup_relation_ibfk_1` FOREIGN KEY (`escalation_esc_id`) REFERENCES `escalation` (`esc_id`) ON DELETE CASCADE,
  CONSTRAINT `escalation_hostgroup_relation_ibfk_2` FOREIGN KEY (`hostgroup_hg_id`) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `escalation_meta_service_relation` (
  `escalation_esc_id` int(11) DEFAULT NULL,
  `meta_service_meta_id` int(11) DEFAULT NULL,
  KEY `escalation_index` (`escalation_esc_id`),
  KEY `meta_service_index` (`meta_service_meta_id`),
  CONSTRAINT `escalation_meta_service_relation_ibfk_1` FOREIGN KEY (`escalation_esc_id`) REFERENCES `escalation` (`esc_id`) ON DELETE CASCADE,
  CONSTRAINT `escalation_meta_service_relation_ibfk_2` FOREIGN KEY (`meta_service_meta_id`) REFERENCES `meta_service` (`meta_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `escalation_service_relation` (
  `escalation_esc_id` int(11) DEFAULT NULL,
  `service_service_id` int(11) DEFAULT NULL,
  `host_host_id` int(11) DEFAULT NULL,
  KEY `escalation_index` (`escalation_esc_id`),
  KEY `service_index` (`service_service_id`),
  KEY `host_index` (`host_host_id`),
  CONSTRAINT `escalation_service_relation_ibfk_1` FOREIGN KEY (`escalation_esc_id`) REFERENCES `escalation` (`esc_id`) ON DELETE CASCADE,
  CONSTRAINT `escalation_service_relation_ibfk_2` FOREIGN KEY (`service_service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE,
  CONSTRAINT `escalation_service_relation_ibfk_3` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `escalation_servicegroup_relation` (
  `escalation_esc_id` int(11) DEFAULT NULL,
  `servicegroup_sg_id` int(11) DEFAULT NULL,
  KEY `escalation_index` (`escalation_esc_id`),
  KEY `sg_index` (`servicegroup_sg_id`),
  CONSTRAINT `escalation_servicegroup_relation_ibfk_1` FOREIGN KEY (`escalation_esc_id`) REFERENCES `escalation` (`esc_id`) ON DELETE CASCADE,
  CONSTRAINT `escalation_servicegroup_relation_ibfk_2` FOREIGN KEY (`servicegroup_sg_id`) REFERENCES `servicegroup` (`sg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `extended_host_information` (
  `ehi_id` int(11) NOT NULL AUTO_INCREMENT,
  `host_host_id` int(11) DEFAULT NULL,
  `ehi_notes` TEXT DEFAULT NULL,
  `ehi_notes_url` TEXT DEFAULT NULL,
  `ehi_action_url` TEXT DEFAULT NULL,
  `ehi_icon_image` int(11) DEFAULT NULL,
  `ehi_icon_image_alt` varchar(200) DEFAULT NULL,
  `ehi_vrml_image` int(11) DEFAULT NULL,
  `ehi_statusmap_image` int(11) DEFAULT NULL,
  `ehi_2d_coords` varchar(200) DEFAULT NULL,
  `ehi_3d_coords` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`ehi_id`),
  UNIQUE KEY `host_host_id` (`host_host_id`),
  KEY `host_index` (`host_host_id`),
  KEY `extended_host_information_ibfk_2` (`ehi_icon_image`),
  KEY `extended_host_information_ibfk_4` (`ehi_statusmap_image`),
  CONSTRAINT `extended_host_information_ibfk_1` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `extended_host_information_ibfk_2` FOREIGN KEY (`ehi_icon_image`) REFERENCES `view_img` (`img_id`) ON DELETE SET NULL,
  CONSTRAINT `extended_host_information_ibfk_4` FOREIGN KEY (`ehi_statusmap_image`) REFERENCES `view_img` (`img_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `extended_service_information` (
  `esi_id` int(11) NOT NULL AUTO_INCREMENT,
  `service_service_id` int(11) DEFAULT NULL,
  `esi_notes` TEXT DEFAULT NULL,
  `esi_notes_url` TEXT DEFAULT NULL,
  `esi_action_url` TEXT DEFAULT NULL,
  `esi_icon_image` int(11) DEFAULT NULL,
  `esi_icon_image_alt` varchar(200) DEFAULT NULL,
  `graph_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`esi_id`),
  KEY `service_index` (`service_service_id`),
  KEY `graph_index` (`graph_id`),
  KEY `extended_service_information_ibfk_3` (`esi_icon_image`),
  CONSTRAINT `extended_service_information_ibfk_1` FOREIGN KEY (`graph_id`) REFERENCES `giv_graphs_template` (`graph_id`) ON DELETE SET NULL,
  CONSTRAINT `extended_service_information_ibfk_2` FOREIGN KEY (`service_service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE,
  CONSTRAINT `extended_service_information_ibfk_3` FOREIGN KEY (`esi_icon_image`) REFERENCES `view_img` (`img_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `giv_components_template` (
  `compo_id` int(11) NOT NULL AUTO_INCREMENT,
  `host_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `ds_order` int(11) DEFAULT NULL,
  `ds_hidecurve` enum('0','1') DEFAULT NULL,
  `ds_name` varchar(200) DEFAULT NULL,
  `ds_color_line` varchar(255) DEFAULT NULL,
  `ds_color_line_mode` enum('0','1') DEFAULT '0',
  `ds_color_area` varchar(255) DEFAULT NULL,
  `ds_color_area_warn` varchar(14) DEFAULT NULL,
  `ds_color_area_crit` varchar(14) DEFAULT NULL,
  `ds_filled` enum('0','1') DEFAULT NULL,
  `ds_max` enum('0','1') DEFAULT NULL,
  `ds_min` enum('0','1') DEFAULT NULL,
  `ds_minmax_int` enum('0','1') DEFAULT '0',
  `ds_average` enum('0','1') DEFAULT NULL,
  `ds_last` enum('0','1') DEFAULT NULL,
  `ds_total` enum('0','1') DEFAULT '0',
  `ds_tickness` int(11) DEFAULT NULL,
  `ds_transparency` varchar(254) DEFAULT NULL,
  `ds_invert` enum('0','1') DEFAULT NULL,
  `ds_legend` varchar(200) DEFAULT NULL,
  `ds_jumpline` enum('0','1','2','3') DEFAULT NULL,
  `ds_stack` enum('0','1') DEFAULT NULL,
  `default_tpl1` enum('0','1') DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`compo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `giv_graphs_template` (
  `graph_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `vertical_label` varchar(200) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `base` int(11) DEFAULT '1000',
  `lower_limit` float DEFAULT NULL,
  `upper_limit` float DEFAULT NULL,
  `size_to_max` tinyint(6) NOT NULL DEFAULT '0',
  `default_tpl1` enum('0','1') DEFAULT NULL,
  `stacked` enum('0','1') DEFAULT NULL,
  `split_component` enum('0','1') DEFAULT '0',
  `scaled` enum('0','1') DEFAULT '1',
  `comment` text,
  PRIMARY KEY (`graph_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `host` (
  `host_id` int(11) NOT NULL AUTO_INCREMENT,
  `host_template_model_htm_id` int(11) DEFAULT NULL,
  `command_command_id` int(11) DEFAULT NULL,
  `command_command_id_arg1` text,
  `timeperiod_tp_id` int(11) DEFAULT NULL,
  `timeperiod_tp_id2` int(11) DEFAULT NULL,
  `command_command_id2` int(11) DEFAULT NULL,
  `command_command_id_arg2` text,
  `host_name` varchar(200) DEFAULT NULL,
  `host_alias` varchar(200) DEFAULT NULL,
  `host_address` varchar(255) DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `host_max_check_attempts` int(11) DEFAULT NULL,
  `host_check_interval` int(11) DEFAULT NULL,
  `host_retry_check_interval` int(11) DEFAULT NULL,
  `host_active_checks_enabled` enum('0','1','2') DEFAULT NULL,
  `host_passive_checks_enabled` enum('0','1','2') DEFAULT NULL,
  `host_checks_enabled` enum('0','1','2') DEFAULT NULL,
  `initial_state` enum('o','d','u') DEFAULT NULL,
  `host_obsess_over_host` enum('0','1','2') DEFAULT NULL,
  `host_check_freshness` enum('0','1','2') DEFAULT NULL,
  `host_freshness_threshold` int(11) DEFAULT NULL,
  `host_event_handler_enabled` enum('0','1','2') DEFAULT NULL,
  `host_low_flap_threshold` int(11) DEFAULT NULL,
  `host_high_flap_threshold` int(11) DEFAULT NULL,
  `host_flap_detection_enabled` enum('0','1','2') DEFAULT NULL,
  `flap_detection_options` varchar(255) DEFAULT NULL,
  `host_process_perf_data` enum('0','1','2') DEFAULT NULL,
  `host_retain_status_information` enum('0','1','2') DEFAULT NULL,
  `host_retain_nonstatus_information` enum('0','1','2') DEFAULT NULL,
  `host_notification_interval` int(11) DEFAULT NULL,
  `host_recovery_notification_delay` int(11) DEFAULT NULL,
  `host_notification_options` varchar(200) DEFAULT NULL,
  `host_notifications_enabled` enum('0','1','2') DEFAULT NULL,
  `contact_additive_inheritance` boolean DEFAULT 0,
  `cg_additive_inheritance` boolean DEFAULT 0,
  `host_first_notification_delay` int(11) DEFAULT NULL,
  `host_acknowledgement_timeout` int(11) DEFAULT NULL,
  `host_stalking_options` varchar(200) DEFAULT NULL,
  `host_snmp_community` varchar(255) DEFAULT NULL,
  `host_snmp_version` varchar(255) DEFAULT NULL,
  `host_location` int(11) DEFAULT '0',
  `host_comment` text,
  `geo_coords` varchar(32) DEFAULT NULL,
  `host_locked` BOOLEAN DEFAULT 0,
  `host_register` enum('0','1','2','3') DEFAULT NULL,
  `host_activate` enum('0','1','2') DEFAULT '1',
  PRIMARY KEY (`host_id`),
  KEY `htm_index` (`host_template_model_htm_id`),
  KEY `cmd1_index` (`command_command_id`),
  KEY `cmd2_index` (`command_command_id2`),
  KEY `tp1_index` (`timeperiod_tp_id`),
  KEY `tp2_index` (`timeperiod_tp_id2`),
  KEY `name_index` (`host_name`),
  KEY `host_id_register` (`host_id`,`host_register`),
  KEY `alias_index` (`host_alias`),
  KEY `host_register` (`host_register`),
  CONSTRAINT `host_ibfk_1` FOREIGN KEY (`command_command_id`) REFERENCES `command` (`command_id`) ON DELETE SET NULL,
  CONSTRAINT `host_ibfk_2` FOREIGN KEY (`command_command_id2`) REFERENCES `command` (`command_id`) ON DELETE SET NULL,
  CONSTRAINT `host_ibfk_3` FOREIGN KEY (`timeperiod_tp_id`) REFERENCES `timeperiod` (`tp_id`) ON DELETE SET NULL,
  CONSTRAINT `host_ibfk_4` FOREIGN KEY (`timeperiod_tp_id2`) REFERENCES `timeperiod` (`tp_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `host_hostparent_relation` (
  `host_parent_hp_id` int(11) DEFAULT NULL,
  `host_host_id` int(11) DEFAULT NULL,
  KEY `host1_index` (`host_parent_hp_id`),
  KEY `host2_index` (`host_host_id`),
  CONSTRAINT `host_hostparent_relation_ibfk_1` FOREIGN KEY (`host_parent_hp_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `host_hostparent_relation_ibfk_2` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `host_service_relation` (
  `hsr_id` int(11) NOT NULL AUTO_INCREMENT,
  `hostgroup_hg_id` int(11) DEFAULT NULL,
  `host_host_id` int(11) DEFAULT NULL,
  `servicegroup_sg_id` int(11) DEFAULT NULL,
  `service_service_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`hsr_id`),
  KEY `hostgroup_index` (`hostgroup_hg_id`),
  KEY `host_index` (`host_host_id`),
  KEY `servicegroup_index` (`servicegroup_sg_id`),
  KEY `service_index` (`service_service_id`),
  KEY `host_service_index` (`host_host_id`,`service_service_id`),
  CONSTRAINT `host_service_relation_ibfk_1` FOREIGN KEY (`hostgroup_hg_id`) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE,
  CONSTRAINT `host_service_relation_ibfk_2` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `host_service_relation_ibfk_3` FOREIGN KEY (`servicegroup_sg_id`) REFERENCES `servicegroup` (`sg_id`) ON DELETE CASCADE,
  CONSTRAINT `host_service_relation_ibfk_4` FOREIGN KEY (`service_service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `host_template_relation` (
  `host_host_id` int(11) NOT NULL DEFAULT '0',
  `host_tpl_id` int(11) NOT NULL DEFAULT '0',
  `order` int(11) DEFAULT NULL,
  PRIMARY KEY (`host_host_id`,`host_tpl_id`),
  KEY `host_tpl_id` (`host_tpl_id`),
  CONSTRAINT `host_template_relation_ibfk_2` FOREIGN KEY (`host_tpl_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `host_template_relation_ibfk_1` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hostcategories` (
  `hc_id` int(11) NOT NULL AUTO_INCREMENT,
  `hc_name` varchar(200) DEFAULT NULL,
  `hc_alias` varchar(200) DEFAULT NULL,
  `level` TINYINT(5) DEFAULT NULL,
  `icon_id` INT(11) DEFAULT NULL,
  `hc_comment` text,
  `hc_activate` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`hc_id`),
  KEY `name_index` (`hc_name`),
  KEY `alias_index` (`hc_alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hostcategories_relation` (
  `hostcategories_hc_id` int(11) DEFAULT NULL,
  `host_host_id` int(11) DEFAULT NULL,
  KEY `hostcategories_index` (`hostcategories_hc_id`),
  KEY `host_index` (`host_host_id`),
  CONSTRAINT `hostcategories_relation_ibfk_2` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `hostcategories_relation_ibfk_1` FOREIGN KEY (`hostcategories_hc_id`) REFERENCES `hostcategories` (`hc_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hostgroup` (
  `hg_id` int(11) NOT NULL AUTO_INCREMENT,
  `hg_name` varchar(200) DEFAULT NULL,
  `hg_alias` varchar(200) DEFAULT NULL,
  `hg_notes` varchar(255) DEFAULT NULL,
  `hg_notes_url` varchar(255) DEFAULT NULL,
  `hg_action_url` varchar(255) DEFAULT NULL,
  `hg_icon_image` int(11) DEFAULT NULL,
  `hg_map_icon_image` int(11) DEFAULT NULL,
  `hg_rrd_retention` int(11) DEFAULT NULL,
  `geo_coords` varchar(32) DEFAULT NULL,
  `hg_comment` text,
  `hg_activate` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`hg_id`),
  KEY `name_index` (`hg_name`),
  KEY `alias_index` (`hg_alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hostgroup_hg_relation` (
  `hg_parent_id` int(11) DEFAULT NULL,
  `hg_child_id` int(11) DEFAULT NULL,
  KEY `hg_parent_id` (`hg_parent_id`),
  KEY `hg_child_id` (`hg_child_id`),
  CONSTRAINT `hostgroup_hg_relation_ibfk_2` FOREIGN KEY (`hg_child_id`) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE,
  CONSTRAINT `hostgroup_hg_relation_ibfk_1` FOREIGN KEY (`hg_parent_id`) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hostgroup_relation` (
  `hgr_id` int(11) NOT NULL AUTO_INCREMENT,
  `hostgroup_hg_id` int(11) DEFAULT NULL,
  `host_host_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`hgr_id`),
  KEY `hostgroup_index` (`hostgroup_hg_id`),
  KEY `host_index` (`host_host_id`),
  CONSTRAINT `hostgroup_relation_ibfk_1` FOREIGN KEY (`hostgroup_hg_id`) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE,
  CONSTRAINT `hostgroup_relation_ibfk_2` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `informations` (
  `key` varchar(25) DEFAULT NULL,
  `value` varchar(1024) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meta_contactgroup_relation` (
  `meta_id` int(11) DEFAULT NULL,
  `cg_cg_id` int(11) DEFAULT NULL,
  KEY `meta_index` (`meta_id`),
  KEY `cg_index` (`cg_cg_id`),
  CONSTRAINT `meta_contactgroup_relation_ibfk_1` FOREIGN KEY (`meta_id`) REFERENCES `meta_service` (`meta_id`) ON DELETE CASCADE,
  CONSTRAINT `meta_contactgroup_relation_ibfk_2` FOREIGN KEY (`cg_cg_id`) REFERENCES `contactgroup` (`cg_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meta_service` (
  `meta_id` int(11) NOT NULL AUTO_INCREMENT,
  `meta_name` varchar(254) DEFAULT NULL,
  `meta_display` varchar(254) DEFAULT NULL,
  `check_period` int(11) DEFAULT NULL,
  `max_check_attempts` int(11) DEFAULT NULL,
  `normal_check_interval` int(11) DEFAULT NULL,
  `retry_check_interval` int(11) DEFAULT NULL,
  `notification_interval` int(11) DEFAULT NULL,
  `notification_period` int(11) DEFAULT NULL,
  `notification_options` varchar(255) DEFAULT NULL,
  `notifications_enabled` enum('0','1','2') DEFAULT NULL,
  `calcul_type` enum('SOM','AVE','MIN','MAX') DEFAULT NULL,
  `data_source_type` tinyint(3) NOT NULL DEFAULT '0',
  `meta_select_mode` enum('1','2') DEFAULT '1',
  `regexp_str` varchar(254) DEFAULT NULL,
  `metric` varchar(255) DEFAULT NULL,
  `warning` varchar(254) DEFAULT NULL,
  `critical` varchar(254) DEFAULT NULL,
  `graph_id` int(11) DEFAULT NULL,
  `meta_comment` text,
  `geo_coords` varchar(32) DEFAULT NULL,
  `meta_activate` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`meta_id`),
  KEY `name_index` (`meta_name`),
  KEY `check_period_index` (`check_period`),
  KEY `notification_period_index` (`notification_period`),
  KEY `graph_index` (`graph_id`),
  CONSTRAINT `meta_service_ibfk_1` FOREIGN KEY (`check_period`) REFERENCES `timeperiod` (`tp_id`) ON DELETE SET NULL,
  CONSTRAINT `meta_service_ibfk_2` FOREIGN KEY (`notification_period`) REFERENCES `timeperiod` (`tp_id`) ON DELETE SET NULL,
  CONSTRAINT `meta_service_ibfk_3` FOREIGN KEY (`graph_id`) REFERENCES `giv_graphs_template` (`graph_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meta_service_relation` (
  `msr_id` int(11) NOT NULL AUTO_INCREMENT,
  `meta_id` int(11) DEFAULT NULL,
  `host_id` int(11) DEFAULT NULL,
  `metric_id` int(11) DEFAULT NULL,
  `msr_comment` text,
  `activate` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`msr_id`),
  KEY `meta_index` (`meta_id`),
  KEY `metric_index` (`metric_id`),
  KEY `host_index` (`host_id`),
  CONSTRAINT `meta_service_relation_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `meta_service_relation_ibfk_2` FOREIGN KEY (`meta_id`) REFERENCES `meta_service` (`meta_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules_informations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `rname` varchar(255) DEFAULT NULL,
  `mod_release` varchar(255) DEFAULT NULL,
  `is_removeable` enum('0','1') DEFAULT NULL,
  `infos` text,
  `author` varchar(255) DEFAULT NULL,
  `svc_tools` enum('0','1') DEFAULT NULL,
  `host_tools` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_macro` (
  `macro_id` int(11) NOT NULL AUTO_INCREMENT,
  `macro_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`macro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nagios_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  `localhost` enum('0','1') DEFAULT NULL,
  `is_default` int(11) DEFAULT '0',
  `last_restart` int(11) DEFAULT NULL,
  `ns_ip_address` varchar(255) DEFAULT NULL,
  `ns_activate` enum('1','0') DEFAULT '1',
  `ns_status` enum('0','1','2','3','4') DEFAULT '0',
  `engine_start_command` varchar(255) DEFAULT 'service centengine start',
  `engine_stop_command` varchar(255) DEFAULT 'service centengine stop',
  `engine_restart_command` varchar(255) DEFAULT 'service centengine restart',
  `engine_reload_command` varchar(255) DEFAULT 'service centengine reload',
  `nagios_bin` varchar(255) DEFAULT NULL,
  `nagiostats_bin` varchar(255) DEFAULT NULL,
  `nagios_perfdata` varchar(255) DEFAULT NULL,
  `broker_reload_command` varchar(255) DEFAULT 'service cbd reload',
  `centreonbroker_cfg_path` varchar(255) DEFAULT NULL,
  `centreonbroker_module_path` varchar(255) DEFAULT NULL,
  `centreonconnector_path` varchar(255) DEFAULT NULL,
  `ssh_port` int(11) DEFAULT NULL,
  `gorgone_communication_type` enum('1', '2') NOT NULL DEFAULT '1',
  `gorgone_port` int(11) DEFAULT NULL,
  `init_script_centreontrapd` varchar(255) DEFAULT NULL,
  `snmp_trapd_path_conf` varchar(255) DEFAULT NULL,
  `engine_name` varchar(255) DEFAULT NULL,
  `engine_version` varchar(255) DEFAULT NULL,
  `centreonbroker_logs_path` VARCHAR(255),
  `remote_id` int(11) NULL,
  `remote_server_use_as_proxy` enum('0','1') NOT NULL DEFAULT '1',
  `updated` enum('1','0') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  CONSTRAINT `nagios_server_remote_id_id` FOREIGN KEY (`remote_id`) REFERENCES `nagios_server` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ns_host_relation` (
  `nagios_server_id` int(11) NOT NULL DEFAULT '0',
  `host_host_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`nagios_server_id`,`host_host_id`),
  KEY `host_host_id` (`host_host_id`),
  KEY `nagios_server_id` (`nagios_server_id`),
  CONSTRAINT `ns_host_relation_ibfk_2` FOREIGN KEY (`nagios_server_id`) REFERENCES `nagios_server` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ns_host_relation_ibfk_3` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Relation Table For centreon Servers and hosts ';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ods_view_details` (
  `dv_id` int(11) NOT NULL AUTO_INCREMENT,
  `index_id` int(11) DEFAULT NULL,
  `metric_id` int(11) DEFAULT NULL,
  `rnd_color` varchar(7) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `all_user` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`dv_id`),
  KEY `index_id` (`index_id`),
  KEY `contact_index` (`contact_id`, `index_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `on_demand_macro_host` (
  `host_macro_id` int(11) NOT NULL AUTO_INCREMENT,
  `host_macro_name` varchar(255) NOT NULL,
  `host_macro_value` varchar(4096) NOT NULL,
  `is_password` tinyint(2) DEFAULT 0,
  `description` text DEFAULT NULL,
  `host_host_id` int(11) NOT NULL,
  `macro_order` int(11) NULL DEFAULT 0,
  PRIMARY KEY (`host_macro_id`),
  KEY `host_host_id` (`host_host_id`),
  CONSTRAINT `on_demand_macro_host_ibfk_1` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `on_demand_macro_service` (
  `svc_macro_id` int(11) NOT NULL AUTO_INCREMENT,
  `svc_macro_name` varchar(255) NOT NULL,
  `svc_macro_value` varchar(4096) NOT NULL,
  `is_password` tinyint(2) DEFAULT 0,
  `description` text DEFAULT NULL,
  `svc_svc_id` int(11) NOT NULL,
  `macro_order` int(11) NULL DEFAULT 0,
  PRIMARY KEY (`svc_macro_id`),
  KEY `svc_svc_id` (`svc_svc_id`),
  CONSTRAINT `on_demand_macro_service_ibfk_1` FOREIGN KEY (`svc_svc_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `options` (
  `key` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poller_command_relations` (
  `poller_id` int(11) NOT NULL,
  `command_id` int(11) NOT NULL,
  `command_order` tinyint (3) DEFAULT NULL,
  KEY `poller_id` (`poller_id`),
  KEY `command_id` (`command_id`),
  CONSTRAINT `poller_command_relations_fk_1` FOREIGN KEY (`poller_id`) REFERENCES `nagios_server` (`id`) ON DELETE CASCADE,
  CONSTRAINT `poller_command_relations_fk_2` FOREIGN KEY (`command_id`) REFERENCES `command` (`command_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `service_template_model_stm_id` int(11) DEFAULT NULL,
  `command_command_id` int(11) DEFAULT NULL,
  `timeperiod_tp_id` int(11) DEFAULT NULL,
  `command_command_id2` int(11) DEFAULT NULL,
  `timeperiod_tp_id2` int(11) DEFAULT NULL,
  `service_description` varchar(200) DEFAULT NULL,
  `service_alias` varchar(255) DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `service_is_volatile` enum('0','1','2') DEFAULT '2',
  `service_max_check_attempts` int(11) DEFAULT NULL,
  `service_normal_check_interval` int(11) DEFAULT NULL,
  `service_retry_check_interval` int(11) DEFAULT NULL,
  `service_active_checks_enabled` enum('0','1','2') DEFAULT '2',
  `service_passive_checks_enabled` enum('0','1','2') DEFAULT '2',
  `initial_state` enum('o','w','u','c') DEFAULT NULL,
  `service_parallelize_check` enum('0','1','2') DEFAULT '2',
  `service_obsess_over_service` enum('0','1','2') DEFAULT '2',
  `service_check_freshness` enum('0','1','2') DEFAULT '2',
  `service_freshness_threshold` int(11) DEFAULT NULL,
  `service_event_handler_enabled` enum('0','1','2') DEFAULT '2',
  `service_low_flap_threshold` int(11) DEFAULT NULL,
  `service_high_flap_threshold` int(11) DEFAULT NULL,
  `service_flap_detection_enabled` enum('0','1','2') DEFAULT '2',
  `service_process_perf_data` enum('0','1','2') DEFAULT '2',
  `service_retain_status_information` enum('0','1','2') DEFAULT '2',
  `service_retain_nonstatus_information` enum('0','1','2') DEFAULT '2',
  `service_notification_interval` int(11) DEFAULT NULL,
  `service_recovery_notification_delay` int(11) DEFAULT NULL,
  `service_notification_options` varchar(200) DEFAULT NULL,
  `service_notifications_enabled` enum('0','1','2') DEFAULT '2',
  `contact_additive_inheritance` boolean DEFAULT 0,
  `cg_additive_inheritance` boolean DEFAULT 0,
  `service_inherit_contacts_from_host` enum('0','1') DEFAULT '1',
  `service_use_only_contacts_from_host` enum('0','1') DEFAULT '0',
  `service_first_notification_delay` int(11) DEFAULT NULL,
  `service_acknowledgement_timeout` int(11) DEFAULT NULL,
  `service_stalking_options` varchar(200) DEFAULT NULL,
  `service_comment` text,
  `geo_coords` varchar(32) DEFAULT NULL,
  `command_command_id_arg` text,
  `command_command_id_arg2` text,
  `service_locked` BOOLEAN DEFAULT 0,
  `service_register` enum('0','1','2','3') NOT NULL DEFAULT '0',
  `service_activate` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`service_id`),
  KEY `stm_index` (`service_template_model_stm_id`),
  KEY `cmd1_index` (`command_command_id`),
  KEY `cmd2_index` (`command_command_id2`),
  KEY `tp1_index` (`timeperiod_tp_id`),
  KEY `tp2_index` (`timeperiod_tp_id2`),
  KEY `description_index` (`service_description`),
  CONSTRAINT `service_ibfk_1` FOREIGN KEY (`command_command_id`) REFERENCES `command` (`command_id`) ON DELETE SET NULL,
  CONSTRAINT `service_ibfk_2` FOREIGN KEY (`command_command_id2`) REFERENCES `command` (`command_id`) ON DELETE SET NULL,
  CONSTRAINT `service_ibfk_3` FOREIGN KEY (`timeperiod_tp_id`) REFERENCES `timeperiod` (`tp_id`) ON DELETE SET NULL,
  CONSTRAINT `service_ibfk_4` FOREIGN KEY (`timeperiod_tp_id2`) REFERENCES `timeperiod` (`tp_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_categories` (
  `sc_id` int(11) NOT NULL AUTO_INCREMENT,
  `sc_name` varchar(255) DEFAULT NULL,
  `sc_description` varchar(255) DEFAULT NULL,
  `level` TINYINT(5) DEFAULT NULL,
  `icon_id` INT(11) DEFAULT NULL,
  `sc_activate` enum('0','1') DEFAULT NULL,
  PRIMARY KEY (`sc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Services Catygories For best Reporting';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_categories_relation` (
  `scr_id` int(11) NOT NULL AUTO_INCREMENT,
  `service_service_id` int(11) DEFAULT NULL,
  `sc_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`scr_id`),
  KEY `service_service_id` (`service_service_id`),
  KEY `sc_id` (`sc_id`),
  CONSTRAINT `service_categories_relation_ibfk_1` FOREIGN KEY (`service_service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE,
  CONSTRAINT `service_categories_relation_ibfk_2` FOREIGN KEY (`sc_id`) REFERENCES `service_categories` (`sc_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servicegroup` (
  `sg_id` int(11) NOT NULL AUTO_INCREMENT,
  `sg_name` varchar(200) DEFAULT NULL,
  `sg_alias` varchar(200) DEFAULT NULL,
  `sg_comment` text,
  `geo_coords` varchar(32) DEFAULT NULL,
  `sg_activate` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`sg_id`),
  KEY `name_index` (`sg_name`),
  KEY `alias_index` (`sg_alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servicegroup_relation` (
  `sgr_id` int(11) NOT NULL AUTO_INCREMENT,
  `host_host_id` int(11) DEFAULT NULL,
  `hostgroup_hg_id` int(11) DEFAULT NULL,
  `service_service_id` int(11) DEFAULT NULL,
  `servicegroup_sg_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`sgr_id`),
  KEY `service_index` (`service_service_id`),
  KEY `servicegroup_index` (`servicegroup_sg_id`),
  KEY `host_host_id` (`host_host_id`),
  KEY `hostgroup_hg_id` (`hostgroup_hg_id`),
  CONSTRAINT `servicegroup_relation_ibfk_10` FOREIGN KEY (`servicegroup_sg_id`) REFERENCES `servicegroup` (`sg_id`) ON DELETE CASCADE,
  CONSTRAINT `servicegroup_relation_ibfk_7` FOREIGN KEY (`host_host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `servicegroup_relation_ibfk_8` FOREIGN KEY (`hostgroup_hg_id`) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE,
  CONSTRAINT `servicegroup_relation_ibfk_9` FOREIGN KEY (`service_service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(256) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `current_page` int(11) DEFAULT NULL,
  `last_reload` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `s_nbHostsUp` int(11) DEFAULT NULL,
  `s_nbHostsDown` int(11) DEFAULT NULL,
  `s_nbHostsUnreachable` int(11) DEFAULT NULL,
  `s_nbHostsPending` int(11) DEFAULT NULL,
  `s_nbServicesOk` int(11) DEFAULT NULL,
  `s_nbServicesWarning` int(11) DEFAULT NULL,
  `s_nbServicesCritical` int(11) DEFAULT NULL,
  `s_nbServicesPending` int(11) DEFAULT NULL,
  `s_nbServicesUnknown` int(11) DEFAULT NULL,
  `update_acl` enum('0','1') DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`(255)),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeperiod` (
  `tp_id` int(11) NOT NULL AUTO_INCREMENT,
  `tp_name` varchar(200) DEFAULT NULL,
  `tp_alias` varchar(200) DEFAULT NULL,
  `tp_sunday` varchar(2048) DEFAULT NULL,
  `tp_monday` varchar(2048) DEFAULT NULL,
  `tp_tuesday` varchar(2048) DEFAULT NULL,
  `tp_wednesday` varchar(2048) DEFAULT NULL,
  `tp_thursday` varchar(2048) DEFAULT NULL,
  `tp_friday` varchar(2048) DEFAULT NULL,
  `tp_saturday` varchar(2048) DEFAULT NULL,
  PRIMARY KEY (`tp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeperiod_exceptions` (
  `exception_id` int(11) NOT NULL AUTO_INCREMENT,
  `timeperiod_id` int(11) NOT NULL,
  `days` varchar(255) NOT NULL,
  `timerange` varchar(255) NOT NULL,
  PRIMARY KEY (`exception_id`),
  KEY `timeperiod_exceptions_relation_ibfk_1` (`timeperiod_id`),
  CONSTRAINT `timeperiod_exceptions_relation_ibfk_1` FOREIGN KEY (`timeperiod_id`) REFERENCES `timeperiod` (`tp_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeperiod_exclude_relations` (
  `exclude_id` int(11) NOT NULL AUTO_INCREMENT,
  `timeperiod_id` int(11) NOT NULL,
  `timeperiod_exclude_id` int(11) NOT NULL,
  FOREIGN KEY (`timeperiod_id`) REFERENCES `timeperiod` (`tp_id`) ON DELETE CASCADE,
  FOREIGN KEY (`timeperiod_exclude_id`) REFERENCES `timeperiod` (`tp_id`) ON DELETE CASCADE,
  PRIMARY KEY (`exclude_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeperiod_include_relations` (
  `include_id` int(11) NOT NULL AUTO_INCREMENT,
  `timeperiod_id` int(11) NOT NULL,
  `timeperiod_include_id` int(11) NOT NULL,
  FOREIGN KEY (`timeperiod_id`) REFERENCES `timeperiod` (`tp_id`) ON DELETE CASCADE,
  FOREIGN KEY (`timeperiod_include_id`) REFERENCES `timeperiod` (`tp_id`) ON DELETE CASCADE,
  PRIMARY KEY (`include_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `topology` (
  `topology_id` int(11) NOT NULL AUTO_INCREMENT,
  `topology_name` varchar(255) DEFAULT NULL,
  `topology_parent` int(11) DEFAULT NULL,
  `topology_page` int(11) DEFAULT NULL,
  `topology_order` int(11) DEFAULT NULL,
  `topology_group` int(11) DEFAULT NULL,
  `topology_url` varchar(255) DEFAULT NULL,
  `topology_url_opt` varchar(255) DEFAULT NULL,
  `topology_popup` enum('0','1') DEFAULT NULL,
  `topology_modules` enum('0','1') DEFAULT NULL,
  `topology_show` enum('0','1') DEFAULT '1',
  `is_deprecated` enum('0','1') NOT NULL DEFAULT '0',
  `topology_style_class` varchar(255) DEFAULT NULL,
  `topology_style_id` varchar(255) DEFAULT NULL,
  `topology_OnClick` varchar(255) DEFAULT NULL,
  `readonly` enum('0','1') NOT NULL DEFAULT '1',
  `is_react` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`topology_id`),
  KEY `topology_page` (`topology_page`),
  KEY `topology_parent` (`topology_parent`),
  KEY `topology_order` (`topology_order`),
  KEY `topology_group` (`topology_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `topology_JS` (
  `id_t_js` int(11) NOT NULL AUTO_INCREMENT,
  `id_page` int(11) DEFAULT NULL,
  `o` varchar(12) DEFAULT NULL,
  `PathName_js` text,
  `Init` text,
  PRIMARY KEY (`id_t_js`),
  KEY `id_page` (`id_page`),
  KEY `id_page_2` (`id_page`,`o`),
  CONSTRAINT `topology_JS_ibfk_1` FOREIGN KEY (`id_page`) REFERENCES `topology` (`topology_page`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `traps` (
  `traps_id` int(11) NOT NULL AUTO_INCREMENT,
  `traps_name` varchar(255) DEFAULT NULL,
  `traps_oid` varchar(255) DEFAULT NULL,
  `traps_mode` enum('0','1') DEFAULT '0',
  `traps_args` text,
  `traps_status` enum('-1','0','1','2','3') DEFAULT NULL,
  `severity_id` int(11) DEFAULT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  `traps_reschedule_svc_enable` enum('0','1') DEFAULT '0',
  `traps_execution_command` text DEFAULT NULL,
  `traps_execution_command_enable` enum('0','1') DEFAULT '0',
  `traps_submit_result_enable` enum('0','1') DEFAULT '0',
  `traps_advanced_treatment` enum('0','1') DEFAULT '0',
  `traps_advanced_treatment_default` enum('0','1', '2') DEFAULT '0',
  `traps_timeout` int(11) DEFAULT NULL,
  `traps_exec_interval` int(11) DEFAULT NULL,
  `traps_exec_interval_type` enum('0','1','2','3') DEFAULT '0',
  `traps_log` enum('0','1') DEFAULT '0',
  `traps_routing_mode` enum('0','1') DEFAULT '0',
  `traps_routing_value` varchar(255) DEFAULT NULL,
  `traps_routing_filter_services` varchar(255) DEFAULT NULL,
  `traps_exec_method` enum('0', '1') DEFAULT '0',
  `traps_downtime` enum('0','1','2') DEFAULT '0',
  `traps_output_transform` varchar(255) DEFAULT NULL,
  `traps_customcode` text,
  `traps_comments` text,
  UNIQUE KEY `traps_name` (`traps_name`,`traps_oid`),
  KEY `traps_id` (`traps_id`),
  KEY `traps_ibfk_1` (`manufacturer_id`),
  KEY `traps_ibfk_2` (`severity_id`),
  CONSTRAINT `traps_ibfk_1` FOREIGN KEY (`manufacturer_id`) REFERENCES `traps_vendor` (`id`) ON DELETE CASCADE,
  CONSTRAINT `traps_ibfk_2` FOREIGN KEY (`severity_id`) REFERENCES `service_categories` (`sc_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `traps_matching_properties` (
  `tmo_id` int(11) NOT NULL AUTO_INCREMENT,
  `trap_id` int(11) DEFAULT NULL,
  `tmo_order` int(11) DEFAULT NULL,
  `tmo_regexp` varchar(255) DEFAULT NULL,
  `tmo_string` varchar(255) DEFAULT NULL,
  `tmo_status` int(11) DEFAULT NULL,
  `severity_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`tmo_id`),
  KEY `trap_id` (`trap_id`),
  CONSTRAINT `traps_matching_properties_ibfk_1` FOREIGN KEY (`trap_id`) REFERENCES `traps` (`traps_id`) ON DELETE CASCADE,
  CONSTRAINT `traps_matching_properties_ibfk_2` FOREIGN KEY (`severity_id`) REFERENCES `service_categories` (`sc_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `traps_preexec` (
  `trap_id` int(11) DEFAULT NULL,
  `tpe_order` int(11) DEFAULT NULL,
  `tpe_string` varchar(512) DEFAULT NULL,
  KEY `trap_id` (`trap_id`),
  CONSTRAINT `traps_preexec_ibfk_1` FOREIGN KEY (`trap_id`) REFERENCES `traps` (`traps_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `traps_service_relation` (
  `traps_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  KEY `service_index` (`service_id`),
  KEY `traps_index` (`traps_id`),
  CONSTRAINT `traps_service_relation_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE,
  CONSTRAINT `traps_service_relation_ibfk_3` FOREIGN KEY (`traps_id`) REFERENCES `traps` (`traps_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `traps_group` (
  `traps_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `traps_group_name` varchar(255) NOT NULL,
  PRIMARY KEY (`traps_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `traps_group_relation` (
  `traps_group_id` int(11) NOT NULL,
  `traps_id` int(11) NOT NULL,
  KEY `traps_group_id` (`traps_group_id`),
  KEY `traps_id` (`traps_id`),
  CONSTRAINT `traps_group_relation_ibfk_1` FOREIGN KEY (`traps_id`) REFERENCES `traps` (`traps_id`) ON DELETE CASCADE,
  CONSTRAINT `traps_group_relation_ibfk_2` FOREIGN KEY (`traps_group_id`) REFERENCES `traps_group` (`traps_group_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `traps_vendor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(254) DEFAULT NULL,
  `alias` varchar(254) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `view_img` (
  `img_id` int(11) NOT NULL AUTO_INCREMENT,
  `img_name` varchar(255) DEFAULT NULL,
  `img_path` varchar(255) DEFAULT NULL,
  `img_comment` text,
  PRIMARY KEY (`img_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `view_img_dir` (
  `dir_id` int(11) NOT NULL AUTO_INCREMENT,
  `dir_name` varchar(255) DEFAULT NULL,
  `dir_alias` varchar(255) DEFAULT NULL,
  `dir_comment` text,
  PRIMARY KEY (`dir_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `view_img_dir_relation` (
  `vidr_id` int(11) NOT NULL AUTO_INCREMENT,
  `dir_dir_parent_id` int(11) DEFAULT NULL,
  `img_img_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`vidr_id`),
  KEY `directory_parent_index` (`dir_dir_parent_id`),
  KEY `image_index` (`img_img_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `virtual_metrics` (
  `vmetric_id` int(11) NOT NULL AUTO_INCREMENT,
  `index_id` int(11) DEFAULT NULL,
  `vmetric_name` varchar(255) DEFAULT NULL,
  `def_type` enum('0','1') DEFAULT '0',
  `rpn_function` varchar(255) DEFAULT NULL,
  `warn` int(11) DEFAULT NULL,
  `crit` int(11) DEFAULT NULL,
  `unit_name` varchar(32) DEFAULT NULL,
  `hidden` enum('0','1') DEFAULT '0',
  `comment` text,
  `vmetric_activate` enum('0','1') DEFAULT NULL,
  `ck_state` enum('0','1','2') DEFAULT NULL,
  PRIMARY KEY (`vmetric_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widget_models` (
  `widget_model_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` TEXT NOT NULL,
  `url` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `directory` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `screenshot` varchar(255) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `autoRefresh` int(11) DEFAULT NULL,
  PRIMARY KEY (`widget_model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widget_parameters` (
  `parameter_id` int(11) NOT NULL AUTO_INCREMENT,
  `parameter_name` varchar(255) NOT NULL,
  `parameter_code_name` varchar(255) NOT NULL,
  `default_value` varchar(255) DEFAULT NULL,
  `parameter_order` tinyint(6) NOT NULL,
  `header_title` varchar(255) DEFAULT NULL,
  `require_permission` varchar(255) NOT NULL,
  `widget_model_id` int(11) NOT NULL,
  `field_type_id` int(11) NOT NULL,
  PRIMARY KEY (`parameter_id`),
  KEY `fk_widget_param_widget_id` (`widget_model_id`),
  KEY `fk_widget_field_type_id` (`field_type_id`),
  CONSTRAINT `fk_widget_param_widget_id` FOREIGN KEY (`widget_model_id`) REFERENCES `widget_models` (`widget_model_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_widget_field_type_id` FOREIGN KEY (`field_type_id`) REFERENCES `widget_parameters_field_type` (`field_type_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widget_parameters_field_type` (
  `field_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `ft_typename` varchar(50) NOT NULL,
  `is_connector` tinyint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`field_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widget_parameters_multiple_options` (
  `parameter_id` int(11) NOT NULL,
  `option_name` varchar(255) NOT NULL,
  `option_value` varchar(255) NOT NULL,
  KEY `fk_option_parameter_id` (`parameter_id`),
  CONSTRAINT `fk_option_parameter_id` FOREIGN KEY (`parameter_id`) REFERENCES `widget_parameters` (`parameter_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widget_parameters_range` (
  `parameter_id` int(11) NOT NULL,
  `min_range` int(11) NOT NULL,
  `max_range` int(11) NOT NULL,
  `step` int(11) NOT NULL,
  KEY `fk_option_range_id` (`parameter_id`),
  CONSTRAINT `fk_option_range_id` FOREIGN KEY (`parameter_id`) REFERENCES `widget_parameters` (`parameter_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widget_preferences` (
  `widget_view_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `preference_value` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  UNIQUE KEY `widget_preferences_unique_index` (`widget_view_id`,`parameter_id`,`user_id`),
  KEY `fk_widget_parameter_id` (`parameter_id`),
  CONSTRAINT `fk_widget_parameter_id` FOREIGN KEY (`parameter_id`) REFERENCES `widget_parameters` (`parameter_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_widget_view_id` FOREIGN KEY (`widget_view_id`) REFERENCES `widget_views` (`widget_view_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widget_views` (
  `widget_view_id` int(11) NOT NULL AUTO_INCREMENT,
  `custom_view_id` int(11) NOT NULL,
  `widget_id` int(11) NOT NULL,
  `widget_order` varchar(255) NOT NULL,
  PRIMARY KEY (`widget_view_id`),
  KEY `fk_custom_view_id` (`custom_view_id`),
  KEY `fk_widget_id` (`widget_id`),
  CONSTRAINT `fk_custom_view_id` FOREIGN KEY (`custom_view_id`) REFERENCES `custom_views` (`custom_view_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_widget_id` FOREIGN KEY (`widget_id`) REFERENCES `widgets` (`widget_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widgets` (
  `widget_id` int(11) NOT NULL AUTO_INCREMENT,
  `widget_model_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`widget_id`),
  KEY `fk_wdg_model_id` (`widget_model_id`),
  CONSTRAINT `fk_wdg_model_id` FOREIGN KEY (`widget_model_id`) REFERENCES `widget_models` (`widget_model_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE ws_token (
  contact_id INT NOT NULL,
  token VARCHAR(100) NOT NULL,
  generate_date DATETIME NOT NULL,
  UNIQUE (token),
  KEY `index1` (`generate_date`),
  FOREIGN KEY (contact_id) REFERENCES contact (contact_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

-- Create table for relation between metaservice and contact
CREATE TABLE `meta_contact` (
  `meta_id` INT NOT NULL,
  `contact_id` INT NOT NULL,
  PRIMARY KEY (`meta_id`, `contact_id`),
  FOREIGN KEY (`meta_id`) REFERENCES `meta_service` (`meta_id`) ON DELETE CASCADE,
  FOREIGN KEY (`contact_id`) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


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

CREATE TABLE IF NOT EXISTS `locale` (
  `locale_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`locale_id`),
  `locale_short_name` varchar(3) NOT NULL,
  `locale_long_name` varchar(255) NOT NULL,
  `locale_img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create downtime cache table for recurrent downtimes
CREATE TABLE IF NOT EXISTS `downtime_cache` (
  `downtime_cache_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`downtime_cache_id`),
  `downtime_id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `service_id` int(11),
  `start_timestamp` int(11) NOT NULL,
  `end_timestamp` int(11) NOT NULL,
  `start_hour` varchar(255) NOT NULL,
  `end_hour` varchar(255) NOT NULL,
  CONSTRAINT `downtime_cache_ibfk_1` FOREIGN KEY (`downtime_id`) REFERENCES `downtime` (`dt_id`) ON DELETE CASCADE,
  CONSTRAINT `downtime_cache_ibfk_2` FOREIGN KEY (`host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE,
  CONSTRAINT `downtime_cache_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Manage new feature proposal
CREATE TABLE IF NOT EXISTS contact_feature (
  contact_id INT NOT NULL,
  feature VARCHAR(255) NOT NULL,
  feature_version VARCHAR(50) NOT NULL,
  feature_enabled TINYINT DEFAULT 0,
  PRIMARY KEY (contact_id, feature, feature_version),
  FOREIGN KEY (contact_id) REFERENCES contact (contact_id) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- Create remote servers table for keeping track of remote instances
CREATE TABLE IF NOT EXISTS `remote_servers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `ip` VARCHAR(255) NOT NULL,
  `app_key` VARCHAR(40) NOT NULL,
  `version` VARCHAR(16) NOT NULL,
  `is_connected` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL,
  `connected_at` TIMESTAMP NULL,
  `centreon_path` VARCHAR(255) NULL,
  `http_method` enum('http','https') NOT NULL DEFAULT 'http',
  `http_port` int(11) DEFAULT NULL,
  `no_check_certificate` enum('0','1') NOT NULL DEFAULT '0',
  `no_proxy` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create rs_poller_relation for the additional relationship between poller and remote servers
CREATE TABLE IF NOT EXISTS `rs_poller_relation` (
  `remote_server_id` int(11) NOT NULL,
  `poller_server_id` int(11) NOT NULL,
  KEY `remote_server_id` (`remote_server_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Relation Table For centreon pollers and remote servers';

-- Create tasks table
CREATE TABLE IF NOT EXISTS `task` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(40) NOT NULL,
  `status` VARCHAR(40) NOT NULL,
  `parent_id` INT(11) NULL,
  `params` BLOB NULL,
  `created_at` TIMESTAMP NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create user_filter table
CREATE TABLE `user_filter` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `user_id` int(11) NOT NULL,
    `page_name` varchar(255) NOT NULL,
    `criterias` text,
    `order` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `filter_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `contact` (`contact_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create platform_topology table
CREATE TABLE `platform_topology` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `address` varchar(255) NOT NULL,
    `hostname` varchar(255) NULL,
    `name` varchar(255) NOT NULL,
    `type` varchar(255) NOT NULL,
    `parent_id` int(11),
    `pending` enum('0', '1') DEFAULT '1',
    `server_id` int(11),
    PRIMARY KEY (`id`),
    CONSTRAINT `platform_topology_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `nagios_server` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `platform_topology_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `platform_topology` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
COMMENT='Registration and parent relation Table used to set the platform topology';

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
