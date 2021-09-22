<?php

/*
 * Copyright 2005-2020 Centreon
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

global $form_service_type;
$form_service_type = "BYHOSTGROUP";

$service_id = filter_var(
    $_GET['service_id'] ?? $_POST['service_id'] ?? null,
    FILTER_VALIDATE_INT
);

/*
 * Path to the configuration dir
 */
$path = "./include/configuration/configObject/service/";

/*
 * PHP functions
 */
require_once("./class/centreonDB.class.php");

$pearDBO = new CentreonDB("centstorage");

require_once $path . "DB-Func.php";
require_once "./include/common/common-Func.php";

$select = filter_var_array(
    getSelectOption(),
    FILTER_VALIDATE_INT
);
$dupNbr = filter_var_array(
    getDuplicateNumberOption(),
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

/* Set the real page */
if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

$acl = $centreon->user->access;
$aclDbName = $acl->getNameDBAcl();

const SERVICE_ADD = 'a';
const SERVICE_WATCH = 'w';
const SERVICE_MODIFY = 'c';
const SERVICE_MASSIVE_CHANGE = 'mc';
const SERVICE_DIVISION = 'dv';
const SERVICE_MOVE_TO_HOST = 'mvH';
const SERVICE_ACTIVATION = 's';
const SERVICE_MASSIVE_ACTIVATION = 'ms';
const SERVICE_DEACTIVATION = 'u';
const SERVICE_MASSIVE_DEACTIVATION = 'mu';
const SERVICE_DUPLICATION = 'm';
const SERVICE_DELETION = 'd';

switch ($o) {
    case SERVICE_ADD:
    case SERVICE_WATCH:
    case SERVICE_MODIFY:
    case SERVICE_MASSIVE_CHANGE:
        require_once($path . "formService.php");
        break;
    case SERVICE_DIVISION:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            divideGroupedServiceInDB(null, isset($select) ? $select : array());
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listServiceByHostGroup.php");
        break;
    case SERVICE_MOVE_TO_HOST:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            divideGroupedServiceInDB(null, isset($select) ? $select : array(), 1);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listServiceByHostGroup.php");
        break;
    case SERVICE_ACTIVATION:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            enableServiceInDB($service_id);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listServiceByHostGroup.php");
        break;
    case SERVICE_MASSIVE_ACTIVATION:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            enableServiceInDB(null, isset($select) ? $select : array());
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listServiceByHostGroup.php");
        break;
    case SERVICE_DEACTIVATION:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            disableServiceInDB($service_id);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listServiceByHostGroup.php");
        break;
    case SERVICE_MASSIVE_DEACTIVATION:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            disableServiceInDB(null, isset($select) ? $select : array());
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listServiceByHostGroup.php");
        break;
    case SERVICE_DUPLICATION:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            multipleServiceInDB(isset($select) ? $select : array(), $dupNbr);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listServiceByHostGroup.php");
        break;
    case SERVICE_DELETION:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            deleteServiceInDB(isset($select) ? $select : array());
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listServiceByHostGroup.php");
        break;
    default:
        require_once($path . "listServiceByHostGroup.php");
        break;
}
