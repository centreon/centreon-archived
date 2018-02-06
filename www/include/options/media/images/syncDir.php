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

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");

require_once("./class/centreonDB.class.php");

/**
 *
 * DB Connection
 */
$pearDB = new CentreonDB();

/**
 * Counters
 */
global $regCounter, $gdCounter, $fileRemoved, $dirCreated;

session_start();
session_write_close();

$sid = session_id();
if (!isset($sid)) {
    exit ;
}

if (isset($sid)) {
    $DBRESULT = $pearDB->query("SELECT * FROM session WHERE session_id = '".$pearDB->escape($sid)."'");
    if ($DBRESULT->numRows() == 0) {
        exit();
    }
}

$dir = "./img/media/";

$rejectedDir = array("." => 1, ".." => 1);
$allowedExt = array("jpg" => 1, "jpeg" => 1, "png" => 1, "gif" => 1, "gd2" => 1);

$dirCreated = 0;
$regCounter = 0;
$gdCounter = 0;

if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($subdir = readdir($dh)) !== false) {
            if (!isset($rejectedDir[$subdir]) && filetype($dir . $subdir) == "dir") {
                $dir_id = checkDirectory($subdir, $pearDB);
                if ($dh2 = opendir($dir.$subdir)) {
                    while (($picture = readdir($dh2)) !== false) {
                        if (!isset($rejectedDir[$picture])) {
                            checkPicture($picture, $dir.$subdir, $dir_id, $pearDB);
                        }
                    }
                    closedir($dh2);
                }
            }
        }
        closedir($dh);
    }
}

$fileRemoved = DeleteOldPictures($pearDB);

/*
 * Display Stats
 */

?>
    <br>
<?php
print "<b>&nbsp;&nbsp;"._("Media Detection")."</b>";
?>
    <br><br>
    <div style="width:250px;height:50px;margin-left:5px;padding:20px;background-color:#FFFFFF;border:1px #CDCDCD solid;-moz-border-radius:4px;">
        <div style='float:left;width:270px;text-align:left;'>
        <p>
<?php
print _("Bad picture alias detected :") . " $fileRemoved<br>";
print _("New directory added :") . " $dirCreated<br>";
print _("New images added :") . " $regCounter<br>";
print _("Convert gd2 -> png :") . " $gdCounter<br><br><br>";
?>
        </p>
        <br><br><br>
        <center><a href='javascript:window.opener.location.reload();javascript:window.close();'>
<?php print _("Close"); ?>
        </a></center>
    </div>
    <br>
<?php

/*
 * recreates local centreon directories as defined in DB
 */
function checkDirectory($dir, $pearDB)
{
    global $dirCreated;
    $DBRESULT = $pearDB->query(
        "SELECT dir_id FROM view_img_dir WHERE dir_alias = '".$dir."'"
    );
    if (!$DBRESULT->numRows()) {
        $DBRESULT = $pearDB->query(
            "INSERT INTO view_img_dir (`dir_name`, `dir_alias`) VALUES ('".$dir."', '".$dir."')"
        );
        @mkdir("./img/media/$dir");
        $DBRESULT = $pearDB->query("SELECT dir_id FROM view_img_dir WHERE dir_alias = '".$dir."'");
        $data = $DBRESULT->fetchRow();
        $dirCreated++;
        return $data["dir_id"];
    } else {
        $data = $DBRESULT->fetchRow();
        return $data["dir_id"];
    }
}

/*
 * inserts $dir_id/$picture into DB if not registered yet
 */
function checkPicture($picture, $dirpath, $dir_id, $pearDB)
{
    global $allowedExt;
    global $regCounter, $gdCounter, $fileRemoved, $dirCreated;

    $img_info = pathinfo($picture);
    $img_ext = $img_info["extension"];

    if (!isset($allowedExt[$img_ext])) {
        return 0;
    }

    if (!isset($img_info["filename"])) {
        $img_parts = explode(".", $img_info["basename"]);
        $img_info["filename"] = $img_parts[0];
    }
    if ($img_info["extension"] == 'gd2' && !is_file($img_info["filename"] . ".png")) {
        $im = imagecreatefromgd2($dirpath ."/". $picture);
        if (!$im) {
            return 0;
        }
        //unlink($picture);
        $picture = $img_info["filename"] . ".png";
        imagepng($im, $dirpath ."/". $picture);
        imagedestroy($im);
        $gdCounter++;
    }

    $DBRESULT = $pearDB->query("SELECT img_id " .
    "FROM view_img, view_img_dir_relation vidh " .
    "WHERE img_path = '".$picture."' " .
    "	AND vidh.dir_dir_parent_id = '".$dir_id."'" .
    "	AND vidh.img_img_id = img_id");
    if (!$DBRESULT->numRows()) {
        $DBRESULT = $pearDB->query(
            "INSERT INTO view_img (`img_name`, `img_path`) VALUES ('"
            .$img_info["filename"]."', '".$picture."')"
        );
        $DBRESULT = $pearDB->query(
            "SELECT img_id FROM view_img WHERE `img_name` = '"
            .$img_info["filename"]."' AND `img_path` = '".$picture."'"
        );
        $data = $DBRESULT->fetchRow();
        $regCounter++;
        $DBRESULT = $pearDB->query(
            "INSERT INTO view_img_dir_relation (`dir_dir_parent_id`, `img_img_id`) VALUES ('"
            .$dir_id."', '".$data['img_id']."')"
        );
        return $data['img_id'];
    } else {
        $data = $DBRESULT->fetchRow();
        return 0;
    }
}

/*
 * removes obsolete files from DB if not on filesystem
 */
function DeleteOldPictures($pearDB)
{
    $fileRemoved = 0;
    $DBRESULT = $pearDB->query(
        "SELECT img_id, img_path, dir_alias FROM view_img vi, "
        . "view_img_dir vid, view_img_dir_relation vidr "
        . "WHERE vidr.img_img_id = vi.img_id AND vid.dir_id = vidr.dir_dir_parent_id"
    );
    while ($row2 = $DBRESULT->fetchRow()) {
        if (!file_exists("./img/media/".$row2["dir_alias"]."/".$row2["img_path"])) {
            $pearDB->query("DELETE FROM view_img WHERE img_id = '".$row2["img_id"]."'");
            $fileRemoved++;
        }
    }
    $DBRESULT->free();
    return $fileRemoved;
}

?>
