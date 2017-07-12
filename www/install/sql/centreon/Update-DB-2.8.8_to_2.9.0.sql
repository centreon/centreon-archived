-- Change version of Centreon
UPDATE `informations` SET `value` = '2.9.0' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.7' LIMIT 1;

ALTER TABLE `cfg_nagios` DROP COLUMN `log_initial_states`;

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