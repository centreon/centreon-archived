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

require_once realpath(dirname(__FILE__) . "/../../../config/centreon.config.php");
require_once("../../$classdir/centreonSession.class.php");
require_once("../../$classdir/centreon.class.php");
require_once("../../$classdir/centreonDB.class.php");

CentreonSession::start();

$pearDB = new CentreonDB();

$session = $pearDB->query("SELECT * FROM `session` WHERE `session_id` = '".session_id()."'");
if (!$session->numRows()) {
    exit;
}

$logos_path = "../../img/media/";

if (isset($_GET["id"]) && $_GET["id"] && is_numeric($_GET["id"])) {
    $result = $pearDB->query("SELECT dir_name, img_path FROM view_img_dir, view_img, view_img_dir_relation vidr WHERE view_img_dir.dir_id = vidr.dir_dir_parent_id AND vidr.img_img_id = img_id AND img_id = '".$pearDB->escape($_GET["id"])."'");
    while ($img = $result->fetchRow()) {
        $imgpath = $logos_path . $img["dir_name"] ."/". $img["img_path"];
        if (!is_file($imgpath)) {
            $imgpath = _CENTREON_PATH_ . 'www/img/media/' . $img["dir_name"] ."/". $img["img_path"];
        }

        if (is_file($imgpath)) {
            $fd = fopen($imgpath, "r");
            $buffer = null;
            while (!feof($fd)) {
                $buffer .= fgets($fd, 4096);
            }
            fclose($fd);
            print $buffer;
            break;
        } else {
            print "File not found";
        }
    }
}
