<?php
/**
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

/**
 * Include config file
 */
include "@CENTREON_ETC@/centreon.conf.php";

require_once "$centreon_path/www/class/centreonGraph.class.php";
require_once "$centreon_path/www/class/centreonDB.class.php";

/**
 * Check if autlogin key is passed, if so create a session for the user if it not exist
 */
if (isset($_GET["akey"]) && isset($_GET['username'])) {
    $pearDB = new CentreonDB();
    $DBRESULT = $pearDB->query("SELECT * FROM `contact`
    						    WHERE `contact_alias` = '".$pearDB->escape($_GET["username"])."'
    						   	AND `contact_activate` = '1'
    						   	AND `contact_autologin_key` = '".$pearDB->escape($_GET["akey"])."' LIMIT 1");
    if ($DBRESULT->numRows()) {
        $res = $DBRESULT->fetchRow();
        session_start();
        $_GET["session_id"] = session_id();
        $pearDB->query("INSERT INTO `session` (`session_id` , `user_id` , `current_page` , `last_reload`, `ip_address`) VALUES ('".session_id()."', '".$res["contact_id"]."', '', '".time()."', '".$_SERVER["REMOTE_ADDR"]."')");
    } else {
        /**
         * Return silently in case autologinKey was invalid.
         */
        exit;
    }

}

if (isset($_GET["hostname"]) && isset($_GET["service"])) {
    $pearDBO = new CentreonDB("centstorage");
    $DBRESULT = $pearDBO->query("SELECT `id`
    							 FROM index_data
    							 WHERE host_name = '".$pearDB->escape($_GET["hostname"])."'
    							 AND service_description = '".$pearDB->escape($_GET["service"])."'
    							 LIMIT 1");
    if ($DBRESULT->numRows()) {
        $res = $DBRESULT->fetchRow();
        $_GET["index"] = $res["id"];
    } else {
        $_GET["index"] = 0;
    }
}

/**
 * Create XML Request Objects
 */
$obj = new CentreonGraph($_GET["session_id"], $_GET["index"], 0, 1);

if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
    ;
} else {
    $obj->displayError();
}

require_once $centreon_path."www/include/common/common-Func.php";

/**
 * Set arguments from GET
 */
$obj->setRRDOption("start", $obj->checkArgument("start", $_GET, time() - (60*60*48)) );
$obj->setRRDOption("end",   $obj->checkArgument("end", $_GET, time()) );

$obj->GMT->getMyGMTFromSession($obj->session_id, $pearDB);

/**
 * Template Management
 */
if (isset($_GET["template_id"])) {
    $obj->setTemplate($_GET["template_id"]);
} else {
    $obj->setTemplate();
}

$obj->init();
if (isset($_GET["flagperiod"])) {
    $obj->setCommandLineTimeLimit($_GET["flagperiod"]);
}

/**
 * Init Curve list
 */
if (isset($_GET["metric"])) {
    $obj->setMetricList($_GET["metric"]);
}
$obj->initCurveList();

/**
 * Comment time
 */
$obj->setOption("comment_time");

/**
 * Create Legende
 */
$obj->createLegend();

/**
 * Display Images Binary Data
 */
$obj->displayImageFlow();
?>
