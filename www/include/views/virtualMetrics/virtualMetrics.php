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

define('VIRTUAL_METRIC_ADD', 'a');
define('VIRTUAL_METRIC_MODIFY', 'c');
define('VIRTUAL_METRIC_DELETE', 'd');
define('VIRTUAL_METRIC_DUPLICATE', 'm');
define('VIRTUAL_METRIC_ENABLE', 's');
define('VIRTUAL_METRIC_DISABLE', 'u');
define('VIRTUAL_METRIC_WATCH', 'w');

/**
 * @var int $vmetric_id Id of the metric
 */
$vmetric_id = filter_var(
    call_user_func(function () {
        if (isset($_GET['vmetric_id'])) {
            return $_GET['vmetric_id'];
        } elseif (isset($_POST['vmetric_id'])) {
            return $_POST['vmetric_id'];
        } else {
            return null;
        }
    }),
    FILTER_VALIDATE_INT
);

/**
 * @var int[] $select Id of virtual metrics selected
 */
$select = filter_var_array(
    call_user_func(function () {
        if (isset($_GET["select"])) {
            return $_GET["select"];
        } elseif (isset($_POST["select"])) {
            return $_POST["select"];
        } else {
            return array();
        }
    }),
    FILTER_VALIDATE_INT
);

/**
 * @var int[] $dupNbr Number of copy of virtual metrics selected
 */
$dupNbr = filter_var_array(
    call_user_func(function () {
        if (isset($_GET["dupNbr"])) {
            return $_GET["dupNbr"];
        } elseif (isset($_POST["dupNbr"])) {
            return $_POST["dupNbr"];
        } else {
            return array();
        }
    }),
    FILTER_VALIDATE_INT
);

if (isset($_POST["o1"]) && isset($_POST["o2"])) {
    if ($_POST["o1"] != "") {
        $o = $_POST["o1"];
    }
    if ($_POST["o2"] != "") {
        $o = $_POST["o2"];
    }
}
/*
 * Pear library
 */
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/advmultiselect.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

/*
 * Path to the configuration dir
 */
$path = "./include/views/virtualMetrics/";

/*
 * PHP functions
 */
require_once $path."DB-Func.php";
require_once "./include/common/common-Func.php";

switch ($o) {
    case VIRTUAL_METRIC_ADD:
        require_once $path."formVirtualMetrics.php";
        break;
    case VIRTUAL_METRIC_WATCH:
        require_once $path."formVirtualMetrics.php";
        break;
    case VIRTUAL_METRIC_MODIFY:
        require_once $path."formVirtualMetrics.php" ;
        break;
    case VIRTUAL_METRIC_ENABLE:
        if ($vmetric_id !== false) {
            enableVirtualMetricInDB($vmetric_id);
        }
        require_once $path."listVirtualMetrics.php";
        break;
    case VIRTUAL_METRIC_DISABLE:
        if ($vmetric_id !== false) {
            disableVirtualMetricInDB($vmetric_id);
        }
        require_once $path."listVirtualMetrics.php";
        break;
    case VIRTUAL_METRIC_DUPLICATE:
        if (!in_array(false, $select) && !in_array(false, $dupNbr)) {
            duplicateVirtualMetric($select, $dupNbr);
        }
        require_once $path."listVirtualMetrics.php";
        break;
    case VIRTUAL_METRIC_DELETE:
        if (!in_array(false, $select)) {
            deleteVirtualMetricInDB($select);
        }
        require_once $path."listVirtualMetrics.php";
        break;
    default:
        require_once $path."listVirtualMetrics.php";
        break;
}
