<?php
/*
 * Copyright 2005-2016 Centreon
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

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");
require_once _CENTREON_PATH_."www/class/centreonSession.class.php";
require_once _CENTREON_PATH_."www/class/centreon.class.php";

CentreonSession::start(1);
if (!isset($_SESSION['centreon'])) {
    die();
}
$oreon = $_SESSION["centreon"];

// -----------------------------------------------------
$value = $_GET["value"];
foreach ($value as $key => $val) {
    if ($val) {
        if (!isset($oreon->optGen["color_".strtolower($key)])) {
            //$color[] = $oreon->optGen["color_undetermined"];
            $color[] = '#F0F0F0';
            $val = str_replace(",", ".", $val);
            $data[] = $val;
            $legend[] = "";
        } else {
            $color[] = $oreon->optGen["color_".strtolower($key)];
            $val = str_replace(",", ".", $val);
            $data[] = $val;
            $legend[] = "";
        }
    }
}
include_once(_CENTREON_PATH_ . '/www/lib/ofc-library/open-flash-chart.php');

$g = new graph();
$g->bg_colour = '#F3F6F6';
//
// PIE chart, 60% alpha
//
$g->pie(60, '#505050', '#000000');
//
// pass in two arrays, one of data, the other data labels
//

$g->pie_values($data, $legend);
//
// Colours for each slice, in this case some of the colours
// will be re-used (3 colurs for 5 slices means the last two
// slices will have colours colour[0] and colour[1]):
//

$g->pie_slice_colours($color);

$g->set_tool_tip('#val#%');

if (isset($_GET["service_name"]) && isset($_GET["host_name"])) {
    $g->title(
        utf8_encode($_GET["service_name"]) . " on " . utf8_encode($_GET["host_name"]),
        '{font-size:15px; color: #424242}'
    );
} elseif (isset($_GET["host_name"])) {
    $g->title(utf8_encode($_GET["host_name"]), '{font-size:18px; color: #424242}');
}
header("Cache-Control: cache, must-revalidate");
header("Pragma: public");
echo $g->render();
