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

$host_id = filter_var(
    $_GET['host_id'] ?? $_POST['host_id'] ?? null,
    FILTER_VALIDATE_INT
);


/*
 * Path to the configuration dir
 */
global $path;

$path = "./include/configuration/configObject/host/";

/*
 * PHP functions
 */
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
if (isset($ret2) && is_array($ret2) && $ret2['topology_page'] != "" && $p != $ret2['topology_page']) {
    $p = $ret2['topology_page'];
} elseif (isset($ret) && is_array($ret) && $ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

$acl = $centreon->user->access;
$dbmon = new CentreonDB('centstorage');
$aclDbName = $acl->getNameDBAcl('broker');
$hgs = $acl->getHostGroupAclConf(null, 'broker');
$aclHostString = $acl->getHostsString('ID', $dbmon);
$aclPollerString = $acl->getPollerString();

const HOST_ADD = 'a';
const HOST_WATCH = 'w';
const HOST_MODIFY = 'c';
const HOST_MASSIVE_CHANGE = 'mc';
const HOST_ACTIVATION = 's';
const HOST_MASSIVE_ACTIVATION = 'ms';
const HOST_DEACTIVATION = 'u';
const HOST_MASSIVE_DEACTIVATION = 'mu';
const HOST_DUPLICATION = 'm';
const HOST_DELETION = 'd';
const HOST_SERVICE_DEPLOYMENT = 'dp';

switch ($o) {
    case HOST_ADD:
    case HOST_WATCH:
    case HOST_MODIFY:
    case HOST_MASSIVE_CHANGE:
        require_once($path . "formHost.php");
        break;
    case HOST_ACTIVATION:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            enableHostInDB($host_id);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listHost.php");
        break; #Activate a host
    case HOST_MASSIVE_ACTIVATION:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            enableHostInDB(null, $select ?? []);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listHost.php");
        break;
    case HOST_DEACTIVATION:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            disableHostInDB($host_id);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listHost.php");
        break; #Desactivate a host
    case HOST_MASSIVE_DEACTIVATION:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            disableHostInDB(null, $select ?? []);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listHost.php");
        break;
    case HOST_DUPLICATION:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            multipleHostInDB($select ?? [], $dupNbr);
        } else {
            unvalidFormMessage();
        }
        $hgs = $acl->getHostGroupAclConf(null, 'broker');
        $aclHostString = $acl->getHostsString('ID', $dbmon);
        $aclPollerString = $acl->getPollerString();
        require_once($path . "listHost.php");
        break;
    case HOST_DELETION:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            deleteHostInDB($select ?? []);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listHost.php");
        break;
    case HOST_SERVICE_DEPLOYMENT:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            applytpl($select ?? []);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listHost.php");
        break; #Deploy service n hosts
    default:
        require_once($path . "listHost.php");
        break;
}
