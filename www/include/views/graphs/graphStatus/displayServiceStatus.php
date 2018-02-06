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
 * SVN : $URL$
 * SVN : $Id$
 *
 */
function escape_command($command)
{
    return preg_replace("/(\\\$|;`)/", "", $command);
}

    require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");

    require_once _CENTREON_PATH_."/www/class/centreonDB.class.php";
    require_once _CENTREON_PATH_."/www/class/centreonSession.class.php";
    require_once _CENTREON_PATH_."/www/class/centreonGMT.class.php";
    require_once _CENTREON_PATH_."/www/class/centreon.class.php";
    require_once _CENTREON_PATH_."/www/include/common/common-Func.php";

    $pearDB = new CentreonDB();

    CentreonSession::start();
    $centreon = $_SESSION["centreon"];

function getStatusDBDir($pearDBO)
{
    $data = $pearDBO->query("SELECT `RRDdatabase_status_path` FROM `config` LIMIT 1");
    $dir = $data->fetchRow();
    return $dir["RRDdatabase_status_path"];
}

    /*
	 * Verify if start and end date
	 */

    (!isset($_GET["start"])) ? $start = time() - (60*60*48): $start = $_GET["start"];
    (!isset($_GET["end"])) ? $end = time() : $end = $_GET["end"];

if (false === is_numeric($start) || false === is_numeric($end)) {
    header('HTTP/1.1 406 Not Acceptable');
    exit();
}
if (isset($_GET['template_id']) && false === is_numeric($_GET['template_id'])) {
    header('HTTP/1.1 406 Not Acceptable');
    exit();
}

    $len = $end - $start;

    /*
	 * Verify if session is active
	 */

    $sid = session_id();
    //$sid = $pearDB->escape($_GET['session_id']);

    $session = $pearDB->query("SELECT * FROM `session` WHERE session_id = '".$sid."'");
if (!$session->numRows()) {
    $image = imagecreate(250, 100);
    $fond = imagecolorallocate($image, 0xEF, 0xF2, 0xFB);
    header("Content-Type: image/gif");
    imagegif($image);

    exit;
} else {
    /*
 * Get GMT for current user
 */
    $CentreonGMT = new CentreonGMT($pearDB);
    $CentreonGMT->getMyGMTFromSession($sid, $pearDB);

    /*
 * Get Values
 */
    $session_value = $session->fetchRow();
    $session->free();

    /*
 * Connect to ods
 */
    $pearDBO = new CentreonDB("centstorage");
    $RRDdatabase_path = getStatusDBDir($pearDBO);

    /*
 * Get Graphs size
 */
    $width = 500;
    $height = 120;

    /*
 * Get index information to have acces to graph
 */

    if (!isset($_GET["host_name"]) && !isset($_GET["service_description"])) {
        $DBRESULT = $pearDBO->query("SELECT * FROM index_data WHERE `id` = '".$pearDB->escape($_GET["index"])."' LIMIT 1");
    } else {
        $pearDBO->query("SET NAMES 'utf8'");
        $DBRESULT = $pearDBO->query("SELECT * FROM index_data WHERE host_name = '".$pearDB->escape($_GET["host_name"])."' AND `service_description` = '".$pearDB->escape($_GET["service_description"])."' LIMIT 1");
    }

    $index_data_ODS = $DBRESULT->fetchRow();
    if (!isset($_GET["template_id"])|| !$_GET["template_id"]) {
        $host_id = getMyHostID($index_data_ODS["host_name"]);
        $svc_id = getMyServiceID($index_data_ODS["service_description"], $host_id);
        $template_id = getDefaultGraph($svc_id, 1);
        $index = $index_data_ODS["id"];
    } else {
        $template_id = $_GET["template_id"];
    }
    $DBRESULT->free();

    /*
 * Create command line
 */
    if (isset($_GET["flagperiod"]) && $_GET["flagperiod"] == 0) {
        if ($CentreonGMT->used()) {
            $start  = $CentreonGMT->getUTCDate($start);
            $end    = $CentreonGMT->getUTCDate($end);
        }
    }

    $command_line = " graph - --start=".$start." --end=".$end;

    /*
 * get all template infos
 */
    if (!is_numeric($template_id)) {
        exit();
    }

    $DBRESULT = $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$pearDB->escape($template_id)."' LIMIT 1");
    $GraphTemplate = $DBRESULT->fetchRow();
    if (is_null($GraphTemplate)) {
        unset($DBRESULT);
        $DBRESULT = $pearDB->query("SELECT * FROM giv_graphs_template WHERE default_tpl1 = '1' LIMIT 1");
        $GraphTemplate = $DBRESULT->fetchRow();
    }
        
    if (preg_match("/meta_([0-9]*)/", $index_data_ODS["service_description"], $matches)) {
        $DBRESULT_meta = $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
        $meta = $DBRESULT_meta->fetchRow();
        $index_data_ODS["service_description"] = $meta["meta_name"];
    }

    $index_data_ODS["service_description"] = str_replace("#S#", "/", $index_data_ODS["service_description"]);
    $index_data_ODS["service_description"] = str_replace("#BS#", "\\", $index_data_ODS["service_description"]);

    $base = "";
    if (isset($GraphTemplate["base"]) && $GraphTemplate["base"]) {
        $base = "-b ".$GraphTemplate["base"];
    }
        
    if (!isset($GraphTemplate["width"]) || $GraphTemplate["width"] == "") {
        $GraphTemplate["width"] = 500;
    }
        
    if (!isset($GraphTemplate["height"]) || $GraphTemplate["height"] == "") {
        $GraphTemplate["height"] = 120;
    }
        
    if (!isset($GraphTemplate["vertical_label"]) || $GraphTemplate["vertical_label"] == "") {
        $GraphTemplate["vertical_label"] = "sds";
    }

    $command_line .= " --interlaced $base --imgformat PNG --width=".$GraphTemplate["width"]." --height=".$GraphTemplate["height"]." ";

    $sdesc = $index_data_ODS['service_description'];
    $hname = $index_data_ODS['host_name'];
    if (!mb_detect_encoding($sdesc, 'UTF-8', true)) {
        $sdesc = utf8_encode($sdesc);
    }
    if (!mb_detect_encoding($hname, 'UTF-8', true)) {
        $hname = utf8_encode($hname);
    }

    $command_line .= "--title='".$sdesc." graph on ".$hname."' --vertical-label='Status' ";

    /*
 * Init Graph Template Value
 */
    if (isset($GraphTemplate["bg_grid_color"]) && $GraphTemplate["bg_grid_color"]) {
        $command_line .= "--color CANVAS".$GraphTemplate["bg_grid_color"]." ";
    }
    if (isset($GraphTemplate["police_color"]) && $GraphTemplate["police_color"]) {
        $command_line .= "--color FONT".$GraphTemplate["police_color"]." ";
    }
    if (isset($GraphTemplate["grid_main_color"]) && $GraphTemplate["grid_main_color"]) {
        $command_line .= "--color MGRID".$GraphTemplate["grid_main_color"]." ";
    }
    if (isset($GraphTemplate["grid_sec_color"]) && $GraphTemplate["grid_sec_color"]) {
        $command_line .= "--color GRID".$GraphTemplate["grid_sec_color"]." ";
    }
    if (isset($GraphTemplate["contour_cub_color"]) && $GraphTemplate["contour_cub_color"]) {
        $command_line .= "--color FRAME".$GraphTemplate["contour_cub_color"]." ";
    }
    if (isset($GraphTemplate["col_arrow"]) && $GraphTemplate["col_arrow"]) {
        $command_line .= "--color ARROW".$GraphTemplate["col_arrow"]." ";
    }
    if (isset($GraphTemplate["col_top"]) && $GraphTemplate["col_top"]) {
        $command_line .= "--color SHADEA".$GraphTemplate["col_top"]." ";
    }
    if (isset($GraphTemplate["col_bot"]) && $GraphTemplate["col_bot"]) {
        $command_line .= "--color SHADEB".$GraphTemplate["col_bot"]." ";
    }

    $command_line .= "--upper-limit 105 ";
    $command_line .= "--lower-limit 0 --rigid ";
    $command_line .= " DEF:v1=".$RRDdatabase_path.$index.".rrd:value:AVERAGE ";

    $command_line .= " CDEF:vname=v1,3600,TREND ";
    $command_line .= " CDEF:crit=v1,75,LT,100,0,IF ";
    $command_line .= " CDEF:warn=v1,74,GT,100,0,IF ";
    $command_line .= " CDEF:ok=v1,100,EQ,100,0,IF ";
    $command_line .= " CDEF:unk=v1,UN,100,0,IF ";

    $command_line .= " AREA:crit#F91E05 ";
    $command_line .= " AREA:warn#F8C706 ";
    $command_line .= " AREA:ok#19EE11 ";
    $command_line .= " AREA:unk#FFFFFF ";

    /*
 * Add comment start and end time inf graph footer.
 */

    $rrd_time  = addslashes($CentreonGMT->getDate("Y\/m\/d G:i", $start));
    $rrd_time  = str_replace(":", "\:", $rrd_time);
    $rrd_time2 = addslashes($CentreonGMT->getDate("Y\/m\/d G:i", $end)) ;
    $rrd_time2 = str_replace(":", "\:", $rrd_time2);
    $command_line .= " COMMENT:\" From $rrd_time to $rrd_time2 \\c\" ";

    $command_line = $centreon->optGen["rrdtool_path_bin"].$command_line." 2>&1";

    $command_line .= " LINE1:ok#19EE11:\"Ok\" ";
    $command_line .= " LINE1:warn#F8C706:\"Warning\" ";
    $command_line .= " LINE1:crit#F91E05:\"Critical\" ";
    $command_line .= " LINE1:unk#FFFFFF:\"Unknown\\l\" ";
    $command_line .= " LINE1:vname#000000:\"trend\" ";

    $command_line .= " GPRINT:v1:LAST:\"Last\:%7.2lf%s\"";
    $command_line .= " GPRINT:v1:MIN:\"Min\:%7.2lf%s\"";
    $command_line .= " GPRINT:v1:MAX:\"Max\:%7.2lf%s\"";
    $command_line .= " GPRINT:v1:AVERAGE:\"Average\:%7.2lf%s\\l\"";

    /*
 * Add Timezone for current user.
 */
    $timezone = $CentreonGMT->getMyTimezone();
    $timezone = trim($timezone);
    if (empty($timezone)) {
        $timezone = date_default_timezone_get();
    }
        
    $command_line = "export TZ='".$timezone."' ; ".$command_line;

    /*
 * Escale special char
 */
    $command_line = escape_command("$command_line");

    if ($centreon->optGen["debug_rrdtool"] == "1") {
        error_log("[" . date("d/m/Y H:s") ."] RDDTOOL : $command_line \n", 3, $centreon->optGen["debug_path"]."rrdtool.log");
    }

    $fp = popen($command_line, 'r');
    if (isset($fp) && $fp) {
        $str ='';
        while (!feof($fp)) {
            $buffer = fgets($fp, 4096);
            $str = $str . $buffer ;
        }
        print $str;
    }
}
