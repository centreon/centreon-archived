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

function get_error($str)
{
    echo $str."<br />";
    exit(0);
}

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");
require_once '../../../class/centreonDB.class.php';

$pearDB     = new CentreonDB();
$pearDBO    = new CentreonDB("centstorage");

session_start();
session_write_close();

$sid = session_id();
if (isset($sid)) {
    $res = $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
    if (!$session = $res->fetchRow()) {
        get_error('bad session id');
    }
} else {
    get_error('need session identifiant !');
}

isset($_GET["metric_id"]) ? $mtrcs = htmlentities($_GET["metric_id"], ENT_QUOTES, "UTF-8") : $mtrcs = null;
isset($_POST["metric_id"]) ? $mtrcs = htmlentities($_POST["metric_id"], ENT_QUOTES, "UTF-8") : $mtrcs = $mtrcs;

require_once '../../../class/centreonDuration.class.php';
require_once '../../common/common-Func.php';

$period = (isset($_POST["period"])) ? htmlentities($_POST["period"], ENT_QUOTES, "UTF-8") : "today";
$period = (isset($_GET["period"])) ? htmlentities($_GET["period"], ENT_QUOTES, "UTF-8") : $period;

header("Content-Type: application/csv-tab-delimited-table");
header("Content-disposition: filename=".$mhost.".csv");

print "Date;value\n";
$begin = time() - 26000;

$res = $pearDB->query("SELECT ctime, value FROM data_bin WHERE id_metric = '".$mtrcs."' AND CTIME >= '".$begin."'");
while ($data = $res->fetchRow()) {
    print $data["ctime"].";".$data["value"].";".date("Y-m-d H:i:s", $data["ctime"])."\n";
}
