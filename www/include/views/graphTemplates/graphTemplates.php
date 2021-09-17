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

$duplicationNumbers = [];
$selectedGraphTemplates = [];

/*
 * id of the graph template
 */
$graph_id = filter_var(
    $_GET['graph_id'] ?? $_POST['graph_id'] ?? false,
    FILTER_VALIDATE_INT
);

 /*
  * Corresponding to the lines selected in the listing
  * $_POST['select'] = [
  *     'graphIdSelected' => 'duplicationFactor'
  * ]
  */
if (!empty($_POST['select'])) {
    foreach ($_POST['select'] as $gIdSelected => $dupFactor) {
        if (filter_var($dupFactor, FILTER_VALIDATE_INT) !== false) {
            $selectedGraphTemplates[$gIdSelected] = (int) $dupFactor;
        }
    }
}

/*
 * End of line text fields (duplicationFactor) in the UI for each lines
 * $_POST['dupNbr'] = [
 *     'graphId' => 'duplicationFactor'
 * ]
 */
if (!empty($_POST['dupNbr'])) {
    foreach ($_POST['dupNbr'] as $gId => $dupFactor) {
        if (filter_var($dupFactor, FILTER_VALIDATE_INT) !== false) {
            $duplicationNumbers[$gId] = (int) $dupFactor;
        }
    }
}

/*
 * Path to the configuration dir
 */
$path = "./include/views/graphTemplates/";

/*
 * PHP functions
 */
require_once $path . "DB-Func.php";
require_once "./include/common/common-Func.php";

switch ($o) {
    case "a":
        // Add a graph template
        require_once $path . "formGraphTemplate.php";
        break;
    case "w":
        // watch aGraph template
        require_once $path . "formGraphTemplate.php";
        break;
    case "c":
        // Modify a graph template
        require_once $path . "formGraphTemplate.php";
        break;
    case "m":
        // duplicate n time selected graph template(s)
        if (isCSRFTokenValid()) {
            multipleGraphTemplateInDB($selectedGraphTemplates, $duplicationNumbers);
        } else {
            unvalidFormMessage();
        }
        require_once $path . "listGraphTemplates.php";
        break;
    case "d":
        // delete selected graph template(s)
        if (isCSRFTokenValid()) {
            deleteGraphTemplateInDB($selectedGraphTemplates);
        } else {
            unvalidFormMessage();
        }
        require_once $path . "listGraphTemplates.php";
        break;
    default:
        require_once $path . "listGraphTemplates.php";
        break;
}
