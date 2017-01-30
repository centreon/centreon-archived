<?php
/**
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

/**
 * Include config file
 */
require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");
require_once "$centreon_path/www/class/centreonDB.class.php";
require_once _CENTREON_PATH_."/www/class/centreonGraph.class.php";

/**
 * Create XML Request Objects
 */
session_start();
session_write_close();

$sid = session_id();
$pearDB = new CentreonDB();

if (!CentreonSession::checkSession($sid, $pearDB)) {
    CentreonGraph::displayError();
}


require_once _CENTREON_PATH_."www/include/common/common-Func.php";
$contactId = CentreonSession::getUser($sid, $pearDB);
$obj = new CentreonGraph($contactId, $_GET["index"], 0, 1);

/**
 * Set One curve
 **/
$obj->onecurve = true;

/**
 * Set metric id
 */
if (isset($_GET["metric"])) {
    $obj->setMetricList($_GET["metric"]);
}

/**
 * Set arguments from GET
 */
$obj->setRRDOption("start", $obj->checkArgument("start", $_GET, time() - (60*60*48)));
$obj->setRRDOption("end", $obj->checkArgument("end", $_GET, time()));

//$obj->GMT->getMyGMTFromSession($obj->session_id, $pearDB);

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
 * Set Colors
 */
$obj->setColor('BACK', '#FFFFFF');
$obj->setColor('FRAME', '#FFFFFF');
$obj->setColor('SHADEA', '#EFEFEF');
$obj->setColor('SHADEB', '#EFEFEF');
$obj->setColor('ARROW', '#FF0000');

/**
 * Display Images Binary Data
 */
$obj->displayImageFlow();
