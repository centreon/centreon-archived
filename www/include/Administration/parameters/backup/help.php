<?php
$help = array();

/**
 * Backup Settings
 */
$help['tip_backup_enabled'] = dgettext(
    'help',
    'Enable Backup process'
);
$help['tip_backup_configuration_files'] = dgettext(
    'help',
    'Backup configuration files (MySQL, Zend, Apache, PHP, SNMP, centreon, centreon-engine, centreon-broker)'
);
$help['tip_backup_database_centreon'] = dgettext(
    'help',
    'Backup centreon database'
);
$help['tip_backup_database_centreon_storage'] = dgettext(
    'help',
    'Backup centreon_storage database'
);
$help['tip_backup_database_type'] = dgettext(
    'help',
    'Backup type for centreon_storage database : mysqldump or LVM snapshot (need available space on MySQL LVM)'
);
$help['tip_backup_database_full'] = dgettext(
    'help',
    'Full backup period'
);
$help['tip_backup_database_partial'] = dgettext(
    'help',
    'Partial backup period (available on partitioned tables)'
);
$help['tip_backup_directory'] = dgettext(
    'help',
    'Directory where backups will be stored'
);
$help['tip_backup_tmp_directory'] = dgettext(
    'help',
    'Temporary directory used by backup process'
);
$help['tip_backup_retention'] = dgettext(
    'help',
    'Backup retention (in days)'
);
$help['tip_backup_mysql_conf'] = dgettext(
    'help',
    'MySQL configuration file path (i.e. /etc/my.cnf.d/centreon.cnf)'
);
$help['tip_backup_zend_conf'] = dgettext(
    'help',
    'Zend configuration file path (i.e. /etc/php.d/zendguard.ini)'
);
$help['tip_backup_export_scp_enabled'] = dgettext(
    'help',
    'Use SCP to copy backup on remote host'
);
$help['tip_backup_export_scp_user'] = dgettext(
    'help',
    'Remote user used by SCP'
);
$help['tip_backup_export_scp_host'] = dgettext(
    'help',
    'Remote host to copy by SCP'
);
$help['tip_backup_export_scp_directory'] = dgettext(
    'help',
    'Remote directory to copy by SCP'
);
