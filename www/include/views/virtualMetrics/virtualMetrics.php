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

if (!isset($centreon)) {
    exit();
}

/*
 * Path to the configuration dir
 */
$path = "./include/views/virtualMetrics/";

require_once $path . "DB-Func.php";
require_once "./include/common/common-Func.php";

define('METRIC_ADD', 'a');
define('METRIC_MODIFY', 'c');
define('METRIC_DELETE', 'd');
define('METRIC_DUPLICATE', 'm');
define('METRIC_ENABLE', 's');
define('METRIC_DISABLE', 'u');
define('METRIC_WATCH', 'w');

$action = filter_var(
    $_POST['o1'] ?? $_POST['o2'] ?? null,
    FILTER_VALIDATE_REGEXP,
    array(
        "options" => array("regexp" => "/([a|c|d|m|s|u|w]{1})/")
    )
);
if ($action !== false) {
    $o = $action;
}

$vmetricId = filter_var(
    $_GET["vmetric_id"] ?? $_POST["vmetric_id"] ?? null,
    FILTER_VALIDATE_INT
);

$selectIds = filter_var_array(
    $_GET["select"] ?? $_POST["select"] ?? array(),
    FILTER_VALIDATE_INT
);

$duplicateNbr = filter_var_array(
    $_GET["dupNbr"] ?? $_POST["dupNbr"] ?? array(),
    FILTER_VALIDATE_INT
);

switch ($o) {
    case METRIC_ADD:
        require_once($path . "formVirtualMetrics.php");
        break;
    case METRIC_WATCH:
        if (is_int($vmetricId)) {
            require_once($path . "formVirtualMetrics.php");
        } else {
            require_once($path . "listVirtualMetrics.php");
        }
        break;
    case METRIC_MODIFY:
        if (is_int($vmetricId)) {
            require_once($path . "formVirtualMetrics.php");
        } else {
            require_once($path . "listVirtualMetrics.php");
        }
        break;
    case METRIC_ENABLE:
        if (isCSRFTokenValid()) {
            if (is_int($vmetricId)) {
                enableVirtualMetricInDB($vmetricId);
            }
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listVirtualMetrics.php");
        break;
    case METRIC_DISABLE:
        if (isCSRFTokenValid()) {
            if (is_int($vmetricId)) {
                disableVirtualMetricInDB($vmetricId);
            }
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listVirtualMetrics.php");
        break;
    case METRIC_DUPLICATE:
        if (isCSRFTokenValid()) {
            if (!in_array(false, $selectIds) && !in_array(false, $duplicateNbr)) {
                multipleVirtualMetricInDB($selectIds, $duplicateNbr);
            }
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listVirtualMetrics.php");
        break;
    case METRIC_DELETE:
        if (isCSRFTokenValid()) {
            if (!in_array(false, $selectIds)) {
                deleteVirtualMetricInDB($selectIds);
            }
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listVirtualMetrics.php");
        break;
    default:
        require_once($path . "listVirtualMetrics.php");
        break;
}
