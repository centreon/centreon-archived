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
# return argument for specific command in txt format
# use by ajax

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");
require_once(_CENTREON_PATH_."www/class/centreonDB.class.php");

function myDecodeService($arg)
{
    $arg = str_replace('#BR#', "\\n", $arg);
    $arg = str_replace('#T#', "\\t", $arg);
    $arg = str_replace('#R#', "\\r", $arg);
    $arg = str_replace('#S#', "/", $arg);
    $arg = str_replace('#BS#', "\\", $arg);
    return html_entity_decode($arg, ENT_QUOTES, "UTF-8");
}

header('Content-type: text/html; charset=utf-8');

$pearDB = new CentreonDB();

if (isset($_POST["index"])) {
    if (false === is_numeric($_POST['index'])) {
        header('HTTP/1.1 406 Not Acceptable');
        exit();
    }

    $DBRESULT = $pearDB->query("SELECT `command_example` FROM `command` WHERE `command_id` = '". $pearDB->escape($_POST["index"]) ."'");
    while ($arg = $DBRESULT->fetchRow()) {
        echo myDecodeService($arg["command_example"]);
    }
    unset($arg);
    unset($DBRESULT);
    $pearDB->disconnect();
}
