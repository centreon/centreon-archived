<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

require_once './class/centreonDuration.class.php';
include_once './include/monitoring/common-Func.php';
include_once './include/monitoring/external_cmd/cmd.php';

// Init Continue Value
$continue = true;

// DB Connect
include_once './class/centreonDB.class.php';

if (!isset($_GET["cmd"])
    && isset($_POST["cmd"])
) {
    $param = $_POST;
} else {
    $param = $_GET;
}

if (isset($param["cmd"])
    && $param["cmd"] == 15
    && isset($param["author"])
    && isset($param["en"])
    && $param["en"] == 1
) {
    if (!isset($param["sticky"])
        || !in_array($param["sticky"], array('0', '1'))
    ) {
        $param["sticky"] = '0';
    }
    if (!isset($param["notify"])
        || !in_array($param["notify"], array('0', '1'))
    ) {
        $param["notify"] = '0';
    }
    if (!isset($param["persistent"])
        || !in_array($param["persistent"], array('0', '1'))
    ) {
        $param["persistent"] = '0';
    }
    acknowledgeService($param);
} elseif (isset($param["cmd"])
    && $param["cmd"] == 15
    && isset($param["author"])
    && isset($param["en"])
    && $param["en"] == 0
) {
    acknowledgeServiceDisable();
}

if (isset($param["cmd"])
    && $param["cmd"] == 16
    && isset($param["output"])
) {
    submitPassiveCheck();
}

if ($o == "svcSch") {
    $param["sort_types"] = "next_check";
    $param["order"] = "sort_asc";
}

$path = "./include/monitoring/status/";
$metaservicepath = $path . "service.php";

$pathRoot = "./include/monitoring/";
$pathExternal = "./include/monitoring/external_cmd/";
$pathDetails = "./include/monitoring/objectDetails/";

/*
 * Special Paths
 */
$svc_path = $path . "Services/";
$hg_path = $path . "ServicesHostGroups/";
$sg_path = $path . "ServicesServiceGroups/";

if ($continue) {
    switch ($o) {
        /*
         * View of Service
         */
        case "svc":
        case "svcpb":
        case "svc_warning":
        case "svc_critical":
        case "svc_unknown":
        case "svc_ok":
        case "svc_pending":
        case "svc_unhandled":
            require_once $svc_path . "service.php";
            break;
        /*
         * Special Views
         */
        case "svcd":
            require_once $pathDetails . "serviceDetails.php";
            break;
        case "svcak":
            require_once "./include/monitoring/acknowlegement/serviceAcknowledge.php";
            break;
        case "svcpc":
            require_once "./include/monitoring/submitPassivResults/servicePassiveCheck.php";
            break;

        case "svcgrid":
        case "svcOV":
        case "svcOV_pb":
            require_once $svc_path . "serviceGrid.php";
            break;
        case "svcSum":
            require_once $svc_path . "serviceSummary.php";
            break;
        /*
         * View by Service Groups
         */
        case "svcgridSG":
        case "svcOVSG":
        case "svcOVSG_pb":
            require_once $sg_path . "serviceGridBySG.php";
            break;
        case "svcSumSG":
            require_once $sg_path . "serviceSummaryBySG.php";
            break;

        /*
         * View By hosts groups
         */
        case "svcgridHG":
        case "svcOVHG":
        case "svcOVHG_pb":
            require_once $hg_path . "serviceGridByHG.php";
            break;
        case "svcSumHG":
            require_once $hg_path . "serviceSummaryByHG.php";
            break;
        default:
            require_once $svc_path . "service.php";
            break;
    }
}
