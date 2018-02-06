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
define('PROCESS_ID', 'createuser');

$link = myConnect();
if (false === $link) {
    exitProcess(PROCESS_ID, 1, mysql_error());
}
if (!isset($_SESSION['DB_USER'])) {
    exitProcess(PROCESS_ID, 1, _('Could not find database user. Session probably expired.'));
}
$dbUser = $_SESSION['DB_USER'];
$dbPass = $_SESSION['DB_PASS'];
$host = "localhost";
// if database server is not on localhost...
if (isset($_SESSION['ADDRESS']) && $_SESSION['ADDRESS'] && 
    $_SESSION['ADDRESS'] != "127.0.0.1" && $_SESSION['ADDRESS'] != "localhost") {
        $host = $_SERVER['SERVER_ADDR'];
}
$query = "GRANT ALL PRIVILEGES ON `%s`.* TO `". $dbUser . "`@`". $host . "` IDENTIFIED BY '". $dbPass . "' WITH GRANT OPTION";
if (false === mysql_query(sprintf($query, $_SESSION['CONFIGURATION_DB']))) {
    exitProcess(PROCESS_ID, 1, mysql_error());
}
if (false === mysql_query(sprintf($query, $_SESSION['STORAGE_DB']))) {
    exitProcess(PROCESS_ID, 1, mysql_error());
}
exitProcess(PROCESS_ID, 0, "OK");
