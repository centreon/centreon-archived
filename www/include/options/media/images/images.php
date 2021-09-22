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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

if (!isset($oreon)) {
    exit();
}

define('IMAGE_ADD', 'a');
define('IMAGE_WATCH', 'w');
define('IMAGE_MODIFY', 'ci');
define('IMAGE_MODIFY_DIRECTORY', 'cd');
define('IMAGE_MOVE', 'm');
define('IMAGE_DELETE', 'd');
define('IMAGE_SYNC_DIR', 'sd');

$imageId = filter_var(
    $_GET["img_id"] ?? $_POST["img_id"] ?? null,
    FILTER_VALIDATE_INT
);

$directoryId = filter_var(
    $_GET["dir_id"] ?? $_POST["dir_id"] ?? null,
    FILTER_VALIDATE_INT
);

// If one data are not correctly typed in array, it will be set to false
$selectIds = filter_var_array(
    $_GET["select"] ?? $_POST["select"] ?? array(),
    FILTER_VALIDATE_INT
);

/*
 * Path to the cities dir
 */
$path = "./include/options/media/images/";

/*
 * PHP functions
 */
require_once $path . "DB-Func.php";
require_once "./include/common/common-Func.php";

switch ($o) {
    case IMAGE_ADD:
        require_once($path . "formImg.php");
        break;
    case IMAGE_WATCH:
        if (is_int($imageId)) {
            require_once($path . "formImg.php");
        }
        break;
    case IMAGE_MODIFY:
        require_once($path . "formImg.php");
        break;
    case IMAGE_MODIFY_DIRECTORY:
        require_once($path . "formDirectory.php");
        break;
    case IMAGE_MOVE:
        require_once($path . "formDirectory.php");
        break;
    case IMAGE_DELETE:
        purgeOutdatedCSRFTokens();
        if (isCSRFTokenValid()) {
            purgeCSRFToken();
            if (!in_array(false, $selectIds)) {
                deleteMultImg($selectIds);
                deleteMultDirectory($selectIds);
            }
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listImg.php");
        break;
    case IMAGE_SYNC_DIR:
        require_once($path . "syncDir.php");
        break;
    default:
        require_once($path . "listImg.php");
        break;
}
