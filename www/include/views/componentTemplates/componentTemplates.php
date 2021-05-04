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

$duplicationNumbers = [];
$selectedCurveTemplates = [];

/*
 * id of the curve template
 */
$compo_id = filter_var(
    $_GET['compo_id'] ?? $_POST['compo_id'] ?? false,
    FILTER_VALIDATE_INT
);

if (!empty($_POST['select'])) {
    foreach ($_POST['select'] as $curveIdSelected => $dupFactor) {
        if (filter_var($dupFactor, FILTER_VALIDATE_INT) !== false) {
            $selectedCurveTemplates[$curveIdSelected] = (int) $dupFactor;
        }
    }
}

if (!empty($_POST['dupNbr'])) {
    foreach ($_POST['dupNbr'] as $curveId => $dupFactor) {
        if (filter_var($dupFactor, FILTER_VALIDATE_INT) !== false) {
            $duplicationNumbers[$curveId] = (int) $dupFactor;
        }
    }
}

/*
 * Path to the configuration dir
 */
$path = './include/views/componentTemplates/';

/*
 * PHP functions
 */
require_once $path . 'DB-Func.php';
require_once './include/common/common-Func.php';

switch ($o) {
    case 'a':
        require_once $path . 'formComponentTemplate.php';
        break; //Add a Component Template
    case 'w':
        require_once $path . 'formComponentTemplate.php';
        break; //Watch a Component Template
    case 'c':
        require_once $path . 'formComponentTemplate.php';
        break; //Modify a Component Template
    case 'm':
        multipleComponentTemplateInDB(
            isset($selectedCurveTemplates) ? $selectedCurveTemplates : [],
            $duplicationNumbers
        );
        require_once $path . 'listComponentTemplates.php';
        break; //Duplicate n Component Templates
    case 'd':
        deleteComponentTemplateInDB(isset($selectedCurveTemplates) ? $selectedCurveTemplates : []);
        require_once $path . 'listComponentTemplates.php';
        break; //Delete n Component Templates
    default:
        require_once $path . 'listComponentTemplates.php';
        break;
}
