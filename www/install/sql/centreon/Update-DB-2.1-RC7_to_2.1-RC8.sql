UPDATE `acl_resources` SET `changed` = '1';

CREATE TABLE IF NOT EXISTS `acl_resources_meta_relations` (
  `armse_id` int(11) NOT NULL auto_increment,
  `meta_id` int(11) default NULL,
  `acl_res_id` int(11) default NULL,
  PRIMARY KEY  (`armse_id`),
  KEY `meta_id` (`meta_id`),
  KEY `acl_res_id` (`acl_res_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `acl_resources_meta_relations` ADD FOREIGN KEY ( `meta_id` ) REFERENCES `meta_service` (`meta_id`) ON DELETE CASCADE ;
ALTER TABLE `acl_resources_meta_relations` ADD FOREIGN KEY ( `acl_res_id` ) REFERENCES `acl_resources` (`acl_res_id`) ON DELETE CASCADE ;

DELETE FROM acl_resources_sg_relations WHERE NOT EXISTS (SELECT * FROM acl_resources WHERE acl_resources_sg_relations.acl_res_id = acl_resources.acl_res_id);
DELETE FROM acl_resources_sg_relations WHERE NOT EXISTS (SELECT * FROM servicegroup WHERE servicegroup.sg_id = acl_resources_sg_relations.sg_id);
ALTER TABLE `acl_resources_sg_relations` ADD INDEX ( `sg_id` , `acl_res_id` );
ALTER TABLE `acl_resources_sg_relations` ADD INDEX ( `sg_id` );
ALTER TABLE `acl_resources_sg_relations` ADD INDEX ( `acl_res_id` );
ALTER TABLE `acl_resources_sg_relations` ADD FOREIGN KEY ( `sg_id` ) REFERENCES `servicegroup` (`sg_id`) ON DELETE CASCADE ;
ALTER TABLE `acl_resources_sg_relations` ADD FOREIGN KEY ( `acl_res_id` ) REFERENCES `acl_resources` (`acl_res_id`) ON DELETE CASCADE ;

DELETE FROM acl_resources_hg_relations WHERE NOT EXISTS (SELECT * FROM hostgroup WHERE hg_id = hg_hg_id);
DELETE FROM acl_resources_hg_relations WHERE NOT EXISTS (SELECT * FROM acl_resources WHERE acl_resources_hg_relations.acl_res_id = acl_resources.acl_res_id);
ALTER TABLE `acl_resources_hg_relations` ADD INDEX ( `hg_hg_id` );
ALTER TABLE `acl_resources_hg_relations` ADD INDEX ( `acl_res_id` );
ALTER TABLE `acl_resources_hg_relations` ADD INDEX ( `hg_hg_id` , `acl_res_id` );
ALTER TABLE `acl_resources_hg_relations` ADD FOREIGN KEY ( `hg_hg_id` ) REFERENCES `hostgroup` (`hg_id`) ON DELETE CASCADE ;
ALTER TABLE `acl_resources_hg_relations` ADD FOREIGN KEY ( `acl_res_id` ) REFERENCES `acl_resources` (`acl_res_id`) ON DELETE CASCADE ;

DELETE FROM acl_actions_rules WHERE NOT EXISTS (SELECT * FROM acl_actions WHERE acl_action_rule_id = acl_action_id);
ALTER TABLE `acl_actions_rules` ADD INDEX ( `acl_action_rule_id` );
ALTER TABLE `acl_actions_rules` ADD FOREIGN KEY ( `acl_action_rule_id` ) REFERENCES `acl_actions` (`acl_action_id`) ON DELETE CASCADE ;

UPDATE `informations` SET `value` = '2.1-RC8' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.1-RC7' LIMIT 1;

