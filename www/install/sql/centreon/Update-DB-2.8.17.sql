ALTER TABLE `extended_host_information` DROP FOREIGN KEY `extended_host_information_ibfk_3`;
ALTER TABLE `extended_host_information` DROP COLUMN `ehi_vrml_image`;

DELETE FROM topology_JS WHERE PathName_js LIKE '%aculous%';

UPDATE `cb_field`
SET `fieldname` = 'negotiation', `displayname` = 'Enable negotiation',
`description` = 'Enable negotiation option (use only for version of Centren Broker >= 2.5)'
WHERE `fieldname` = 'negociation';

UPDATE `cfg_centreonbroker_info`
SET `config_key` = 'negotiation'
WHERE `config_key` = 'negociation';

-- Delete duplicate entries in custom_view_user_relation
ALTER TABLE `custom_view_user_relation`
    DROP FOREIGN KEY `fk_custom_views_usergroup_id`,
    DROP FOREIGN KEY `fk_custom_views_user_id`,
    DROP FOREIGN KEY `fk_custom_view_user_id`,
    DROP INDEX `view_user_unique_index`;
ALTER IGNORE TABLE `custom_view_user_relation`
    ADD UNIQUE INDEX `view_user_unique_index` (`custom_view_id`, `user_id`),
    ADD UNIQUE INDEX `view_usergroup_unique_index` (`custom_view_id`, `usergroup_id`);
ALTER TABLE `custom_view_user_relation`
    ADD CONSTRAINT `fk_custom_views_usergroup_id`
        FOREIGN KEY (`usergroup_id`)
        REFERENCES `centreon`.`contactgroup` (`cg_id`)
        ON DELETE CASCADE,
    ADD CONSTRAINT `fk_custom_views_user_id`
        FOREIGN KEY (`user_id`)
        REFERENCES `centreon`.`contact` (`contact_id`)
        ON DELETE CASCADE,
    ADD CONSTRAINT `fk_custom_view_user_id`
        FOREIGN KEY (`custom_view_id`)
        REFERENCES `centreon`.`custom_views` (`custom_view_id`)
        ON DELETE CASCADE;
