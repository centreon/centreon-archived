<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

session_start();
require_once '../functions.php';
define('PROCESS_ID', 'dbstorage');

$link = myConnect();
if (false === $link) {
    exitProcess(PROCESS_ID, 1, mysql_error());
}
if (!isset($_SESSION['STORAGE_DB'])) {
    exitProcess(PROCESS_ID, 1, _('Could not find storage database. Session probably expired.'));
}
if (false === mysql_query("CREATE DATABASE ".$_SESSION['STORAGE_DB']) && !is_file('../../tmp/createTablesCentstorage')) {
    exitProcess(PROCESS_ID, 1, mysql_error());
}
mysql_select_db($_SESSION['STORAGE_DB']);
$result = splitQueries('../../createTablesCentstorage.sql', ';', null, '../../tmp/createTablesCentstorage');
if ("0" != $result) {
    exitProcess(PROCESS_ID, 1, $result);
}
$result = splitQueries('../../installBroker.sql', ';', null, '../../tmp/installBroker');
if ("0" != $result) {
    exitProcess(PROCESS_ID, 1, $result);
}

exitProcess(PROCESS_ID, 0, "OK");