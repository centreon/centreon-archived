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
define('PROCESS_ID', 'baseconf');

$link = myConnect();
if (false === $link) {
    exitProcess(PROCESS_ID, 1, mysql_error());
}

/**
 * Create tables
 */
mysql_select_db($_SESSION['CONFIGURATION_DB']);

splitQueries('../../insertMacros.sql', ';', null, '../../tmp/insertMacros');
splitQueries('../../insertCmd-Tps.sql', ';', null, '../../tmp/insertCmd-Tps.sql');
if (isset($_SESSION['MONITORING_ENGINE']) && is_file('../../var/baseconf/'.$_SESSION['MONITORING_ENGINE'].'.sql')) {
    splitQueries('../../var/baseconf/'.$_SESSION['MONITORING_ENGINE'].'.sql', ';', null, '../../tmp/'.$_SESSION['MONITORING_ENGINE']);
}
if (isset($_SESSION['BROKER_MODULE']) && is_file('../../var/baseconf/'.$_SESSION['BROKER_MODULE'].'.sql')) {
    splitQueries('../../var/baseconf/'.$_SESSION['BROKER_MODULE'].'.sql', ';', null, '../../tmp/'.$_SESSION['BROKER_MODULE']);
}
splitQueries('../../insertTopology.sql', ';', null, '../../tmp/insertTopology');
splitQueries('../../insertBaseConf.sql', ';', null, '../../tmp/insertBaseConf');
splitQueries('../../insertACL.sql', ';', null, '../../tmp/insertACL');
exitProcess(PROCESS_ID, 0, "OK");