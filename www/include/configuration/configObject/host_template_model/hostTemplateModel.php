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

$host_id = filter_var(
    call_user_func(function () {
        if (isset($_GET["host_id"])) {
            return $_GET["host_id"];
        } elseif (isset($_POST["host_id"])) {
            return $_POST["host_id"];
        } else {
            return null;
        }
    }),
    FILTER_VALIDATE_INT
);

/* Pear library */
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/select2.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

/* Path to the configuration dir */
$path = "./include/configuration/configObject/host_template_model/";
$path2 = "./include/configuration/configObject/host/";

/* PHP functions */
require_once $path2."DB-Func.php";
require_once "./include/common/common-Func.php";

$select = filter_var_array(
    getSelectOption(),
    FILTER_VALIDATE_INT
);
$dupNbr = filter_var_array(
    getDuplicateNumberOption(),
    FILTER_VALIDATE_INT
);

$hostObj = new CentreonHost($pearDB);
$lockedElements = $hostObj->getLockedHostTemplates();

/* Set the real page */
if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

define('HOST_TEMPLATE_ADD', 'a');
define('HOST_TEMPLATE_WATCH', 'w');
define('HOST_TEMPLATE_MODIFY', 'c');
define('HOST_TEMPLATE_MASSIVE_CHANGE', 'mc');
define('HOST_TEMPLATE_ACTIVATION', 's');
define('HOST_TEMPLATE_MASSIVE_ACTIVATION', 'ms');
define('HOST_TEMPLATE_DEACTIVATION', 'u');
define('HOST_TEMPLATE_MASSIVE_DEACTIVATION', 'mu');
define('HOST_TEMPLATE_DUPLICATION', 'm');
define('HOST_TEMPLATE_DELETION', 'd');

switch ($o) {
    case HOST_TEMPLATE_ADD:
    case HOST_TEMPLATE_WATCH:
    case HOST_TEMPLATE_MODIFY:
    case HOST_TEMPLATE_MASSIVE_CHANGE:
        require_once($path."formHostTemplateModel.php");
        break;
    case HOST_TEMPLATE_ACTIVATION:
        enableHostInDB($host_id);
        require_once($path."listHostTemplateModel.php");
        break;
    case HOST_TEMPLATE_MASSIVE_ACTIVATION:
        enableHostInDB(null, isset($select) ? $select : array());
        require_once($path."listHostTemplateModel.php");
        break;
    case HOST_TEMPLATE_DEACTIVATION:
        disableHostInDB($host_id);
        require_once($path."listHostTemplateModel.php");
        break; #Desactivate a host template model
    case HOST_TEMPLATE_MASSIVE_DEACTIVATION:
        disableHostInDB(null, isset($select) ? $select : array());
        require_once($path."listHostTemplateModel.php");
        break;
    case HOST_TEMPLATE_DUPLICATION:
        multipleHostInDB(isset($select) ? $select : array(), $dupNbr);
        require_once($path."listHostTemplateModel.php");
        break;
    case HOST_TEMPLATE_DELETION:
        deleteHostInDB(isset($select) ? $select : array());
        require_once($path."listHostTemplateModel.php");
        break;
    default:
        require_once($path."listHostTemplateModel.php");
        break;
}
