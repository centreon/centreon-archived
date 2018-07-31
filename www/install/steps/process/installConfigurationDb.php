<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

session_start();
require_once '../functions.php';
define('PROCESS_ID', 'dbconf');

// because some monitoring engines don't seem to have /var/lib dir
if (isset($_SESSION['MONITORING_VAR_LOG']) && !isset($_SESSION['MONITORING_VAR_LIB'])) {
    $_SESSION['MONITORING_VAR_LIB'] = $_SESSION['MONITORING_VAR_LOG'];
}

$link = myConnect();
if (false === $link) {
    exitProcess(PROCESS_ID, 1, mysql_error());
}
if (!isset($_SESSION['CONFIGURATION_DB'])) {
    exitProcess(PROCESS_ID, 1, _('Could not find configuration database. Session probably expired.'));
}

/* Check if MySQL innodb_file_perf_table is enabled */
$innodb_file_per_table = getDatabaseVariable('innodb_file_per_table');
if (is_null($innodb_file_per_table) || strtolower($innodb_file_per_table) == 'off') {
    exitProcess(
        PROCESS_ID,
        1,
        _('Add innodb_file_per_table=1 in my.cnf file under the [mysqld] section and restart MySQL Server.')
    );
}

/* Check if MySQL open_files_limit parameter is higher than 32000 */
$open_files_limit = getDatabaseVariable('open_files_limit');
if (is_null($open_files_limit)) {
    $open_files_limit = 0;
}
if ($open_files_limit < 32000) {
    $exitMessage = 'If your operating system is based on SystemV (CentOS 6), ' .
        'add open_files_limit=32000 in my.cnf file under the [mysqld] section and restart MySQL Server.<br/>' .
        'If your operating system is based on systemd (CentOS 7, Debian Jessie), add LimitNOFILE=32000 value on the ' .
        'service file /etc/systemd/system/mariadb.service and reload systemd (systemctl daemon-reload).';
    exitProcess(PROCESS_ID, 1, _($exitMessage));
}

if (false === mysql_query("CREATE DATABASE ".$_SESSION['CONFIGURATION_DB']) && !is_file('../../tmp/createTables')) {
    exitProcess(PROCESS_ID, 1, mysql_error());
}

/**
 * Create tables
 */
mysql_select_db($_SESSION['CONFIGURATION_DB']);
$result = splitQueries('../../createTables.sql', ';', null, '../../tmp/createTables');
if ("0" != $result) {
    exitProcess(PROCESS_ID, 1, $result);
}
exitProcess(PROCESS_ID, 0, "OK");
