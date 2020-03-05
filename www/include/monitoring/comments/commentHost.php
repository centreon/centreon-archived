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

if (!isset($oreon)) {
    exit();
}
$contactId = filter_var(
    $_GET["contact_id"] ?? $_POST["contact_id"] ?? 0,
    FILTER_VALIDATE_INT
);
$select = $_GET["select"] ?? $_POST["select"] ?? [];
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);

/*
 * Path to the configuration folder
 */
$path = "./include/monitoring/comments/";

/*
 * PHP functions
 */
require_once "./include/common/common-Func.php";
require_once "./include/monitoring/comments/common-Func.php";
require_once "./include/monitoring/external_cmd/functions.php";

switch ($o) {
    case "ah":
        require_once($path . "AddHostComment.php");
        break;
    case "dh":
        DeleteComment("HOST", $select);
        require_once($path . "viewHostComment.php");
        break;
    case "vh":
        require_once($path . "viewHostComment.php");
        break;
    default:
        require_once($path . "viewHostComment.php");
        break;
}
