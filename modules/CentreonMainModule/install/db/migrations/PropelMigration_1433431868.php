<?php

/**
 * Data object containing the SQL and PHP code to migrate the database
 * up to version 1433431868.
 * Generated on 2015-06-04 17:31:08 by root
 */
class PropelMigration_1433431868
{

    public function preUp($manager)
    {
        // add the pre-migration code here
    }

    public function postUp($manager)
    {
        // add the post-migration code here
    }

    public function preDown($manager)
    {
        // add the pre-migration code here
    }

    public function postDown($manager)
    {
        // add the post-migration code here
    }

    /**
     * Get the SQL statements for the Up migration
     *
     * @return array list of the SQL strings to execute for the Up migration
     *               the keys being the datasources
     */
    public function getUpSQL()
    {
        return array (
  'centreon' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `cfg_acl_resources`
    ADD `slug` VARCHAR(254) NOT NULL AFTER `name`;

ALTER TABLE `cfg_auth_resources`
    ADD `ar_slug` VARCHAR(254) NOT NULL AFTER `ar_name`;

ALTER TABLE `cfg_commands`
    ADD `command_slug` VARCHAR(200) AFTER `command_name`;

ALTER TABLE `cfg_connectors`
    ADD `slug` VARCHAR(254) NOT NULL AFTER `name`;

ALTER TABLE `cfg_contacts`
    ADD `slug` VARCHAR(254) NOT NULL AFTER `description`;

ALTER TABLE `cfg_domains`
    ADD `slug` VARCHAR(254) NOT NULL AFTER `name`;

ALTER TABLE `cfg_environments`
    ADD `slug` VARCHAR(254) NOT NULL AFTER `name`;

ALTER TABLE `cfg_hosts`
    ADD `host_slug` VARCHAR(254) NOT NULL AFTER `host_name`;

ALTER TABLE `cfg_languages`
    ADD `slug` VARCHAR(254) NOT NULL AFTER `name`;

ALTER TABLE `cfg_pollers`
    ADD `slug` VARCHAR(254) NOT NULL AFTER `name`;

ALTER TABLE `cfg_resources`
    ADD `resource_slug` VARCHAR(254) NOT NULL AFTER `resource_name`;

ALTER TABLE `cfg_services`
    ADD `service_slug` VARCHAR(254) NOT NULL AFTER `service_description`;

ALTER TABLE `cfg_tags`
    ADD `slug` VARCHAR(254) NOT NULL AFTER `tagname`;

ALTER TABLE `cfg_timeperiods`
    ADD `tp_slug` VARCHAR(254) NOT NULL AFTER `tp_name`;

ALTER TABLE `cfg_traps`
    ADD `traps_slug` VARCHAR(254) NOT NULL AFTER `traps_name`;

ALTER TABLE `cfg_traps_vendors`
    ADD `slug` VARCHAR(254) NOT NULL AFTER `name`;

ALTER TABLE `cfg_usergroups`
    ADD `slug` VARCHAR(254) NOT NULL AFTER `name`;

ALTER TABLE `cfg_users`
    ADD `slug` VARCHAR(254) NOT NULL AFTER `login`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

    /**
     * Get the SQL statements for the Down migration
     *
     * @return array list of the SQL strings to execute for the Down migration
     *               the keys being the datasources
     */
    public function getDownSQL()
    {
        return array (
  'centreon' => '
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `cfg_acl_resources` DROP `slug`;

ALTER TABLE `cfg_auth_resources` DROP `ar_slug`;

ALTER TABLE `cfg_commands` DROP `command_slug`;

ALTER TABLE `cfg_connectors` DROP `slug`;

ALTER TABLE `cfg_contacts` DROP `slug`;

ALTER TABLE `cfg_domains` DROP `slug`;

ALTER TABLE `cfg_environments` DROP `slug`;

ALTER TABLE `cfg_hosts` DROP `host_slug`;

ALTER TABLE `cfg_languages` DROP `slug`;

ALTER TABLE `cfg_pollers` DROP `slug`;

ALTER TABLE `cfg_resources` DROP `resource_slug`;

ALTER TABLE `cfg_services` DROP `service_slug`;

ALTER TABLE `cfg_tags` DROP `slug`;

ALTER TABLE `cfg_timeperiods` DROP `tp_slug`;

ALTER TABLE `cfg_traps` DROP `traps_slug`;

ALTER TABLE `cfg_traps_vendors` DROP `slug`;

ALTER TABLE `cfg_usergroups` DROP `slug`;

ALTER TABLE `cfg_users` DROP `slug`;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
',
);
    }

}