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

isset($_GET["img_id"]) ? $imgG = $_GET["img_id"] : $imgG = null;
isset($_POST["img_id"]) ? $imgP = $_POST["img_id"] : $imgP = null;
$imgG ? $img_id = $imgG : $img_id = $imgP;

isset($_GET["dir_id"]) ? $dirG = $_GET["dir_id"] : $dirG = null;
isset($_POST["dir_id"]) ? $dirP = $_POST["dir_id"] : $dirP = null;
$dirG ? $dir_id = $dirG : $dir_id = $dirP;


isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = null;
isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = null;
$cG ? $select = $cG : $select = $cP;

/*
 * Pear library
 */
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/advmultiselect.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

/*
 * Path to the cities dir
 */
$path = "./include/options/media/images/";

/*
 * PHP functions
 */
require_once $path."DB-Func.php";
require_once "./include/common/common-Func.php";

switch ($o) {
    case "a": #Add a img
        require_once($path."formImg.php");
        break;
    case "w": #Watch a img
        require_once($path."formImg.php");
        break;
    case "ci": #Modify a img
        require_once($path."formImg.php");
        break;
    case "cd": #Modify a dir
        require_once($path."formDirectory.php");
        break;
    case "m": #Move files to a dir
        require_once($path."formDirectory.php");
        break;
    case "d":
        deleteMultImg(isset($select) ? $select : array());
        deleteMultDirectory(isset($select) ? $select : array());
        require_once($path."listImg.php");
        break;
    case "sd":
        require_once($path."syncDir.php");
        break;
    default:
        require_once($path."listImg.php");
        break;
}
