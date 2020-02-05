<?php
/**
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

function sanitizeFilename($filename)
{
    $cleanstr = str_replace(
        array(' ', '/', '\\'),
        "_",
        $filename
    );
    return $cleanstr;
}

function sanitizePath($path)
{
    $cleanstr = str_replace(
        array('#', '/', '\\'),
        "_",
        $path
    );
    return $cleanstr;
}


function extractDir($zipfile, $path)
{
    if (file_exists($zipfile)) {
        $files = array();
        $zip = new ZipArchive;
        if ($zip->open($zipfile) === true) {
            if ($zip->extractTo($path) === true) {
                return true;
            } else {
                return false;
            }
            $zip->close();
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function isValidImage($filename)
{
    if (!$filename) {
        return false;
    }
    $imginfo = getimagesize($filename);

    if (isset($imginfo) && false !== $imginfo) {
        return true;
    } else {
        return is_gd2($filename);
    }
    return false;
}

function is_gd2($filename)
{
    if (getimagesize($filename) !== false) {
        return false;
    }
    $gd_res = imagecreatefromgd2($filename);
    if ($gd_res) {
        imagedestroy($gd_res);
        return true;
    }
    return false;
}


function insertImg($src_dir, $src_file, $dst_dir, $dst_file, $img_comment = "")
{
    global $pearDB;
    $mediadir = "./img/media/";

    if (!($dir_id = testDirectoryExistence($dst_dir))) {
        $dir_id = insertDirectory($dst_dir);
    }

    $dst_file = sanitizeFilename($dst_file);
    $dst  = $mediadir.$dst_dir."/".$dst_file;
    if (is_file($dst)) {
        return false;
    } // file exists
    if (!rename($src_dir.$src_file, $dst)) {
        return false;
    } // access denied, path error

    $img_parts = explode(".", $dst_file);
    $img_name = $img_parts[0];
    
    $prepare = $pearDB->prepare(
        "INSERT INTO view_img (img_name, img_path, img_comment) VALUES "
        . "(:image_name, :image_path, :image_comment)"
    );
    $prepare->bindValue(':image_name', $img_name, \PDO::PARAM_STR);
    $prepare->bindValue(':image_path', $dst_file, \PDO::PARAM_STR);
    $prepare->bindValue(':image_comment', $img_comment, \PDO::PARAM_STR);
   
    $prepare->execute();
    $image_id = $pearDB->lastInsertId();
    
    $prepare2 = $pearDB->prepare(
        "INSERT INTO view_img_dir_relation (dir_dir_parent_id, img_img_id) "
        . "VALUES (:dir_id, :image_id)"
    );
    $prepare2->bindValue(':dir_id', $dir_id, \PDO::PARAM_INT);
    $prepare2->bindValue(':image_id', $image_id, \PDO::PARAM_INT);
    $prepare2->execute();

    return $image_id;
}

function deleteMultImg($images = array())
{
    foreach (array_keys($images) as $selector) {
        $id = explode('-', $selector);
        if (count($id)!=2) {
            continue;
        }
        deleteImg($id[1]);
    }
}

function deleteImg($imageId)
{
    if (!isset($imageId)) {
        return;
    }
    $imageId = (int) $imageId;

    global $pearDB;

    $mediadir = "./img/media/";

    $dbResult = $pearDB->query(
        "SELECT dir_alias, img_path "
        . "FROM view_img, view_img_dir, view_img_dir_relation "
        . "WHERE img_id = $imageId AND img_id = img_img_id "
        . "AND dir_dir_parent_id = dir_id"
    );
    while ($imagePath = $dbResult->fetch()) {
        $fullpath = $mediadir.$imagePath["dir_alias"]."/".$imagePath["img_path"];
        if (is_file($fullpath)) {
            unlink($fullpath);
        }
        $pearDB->query("DELETE FROM view_img WHERE img_id = $imageId");
        $pearDB->query("DELETE FROM view_img_dir_relation WHERE img_img_id = $imageId");
    }
    $dbResult->closeCursor();
}

function moveMultImg($images, $dirName)
{
    if (count($images)>0) {
        foreach ($images as $id) {
            moveImg($id, $dirName);
        }
    }
}

function moveImg($img_id, $dir_alias)
{
    if (!$img_id) {
        return;
    }
    global $pearDB;
    $mediadir = "./img/media/";
    $prepare = $pearDB->prepare(
        "SELECT dir_id, dir_alias, img_path, img_comment "
        . "FROM view_img, view_img_dir, view_img_dir_relation "
        . "WHERE img_id = :image_id AND img_id = img_img_id "
        . "AND dir_dir_parent_id = dir_id"
    );
    $prepare->bindValue(':image_id', $img_id, \PDO::PARAM_INT);
    
    if (!$prepare->execute()) {
        return;
    }
    $img_info = $prepare->fetch(PDO::FETCH_ASSOC);

    if ($dir_alias) {
        $dir_alias = sanitizePath($dir_alias);
    } else {
        $dir_alias = $img_info["dir_alias"];
    }
    if ($dir_alias != $img_info["dir_alias"]) {
        $oldpath = $mediadir.$img_info["dir_alias"]."/".$img_info["img_path"];
        $newpath = $mediadir.$dir_alias."/".$img_info["img_path"];
        
        if (!file_exists($newpath)) {
            /**
             * Only if file doesn't already exist in the destination
             */
            if (!testDirectoryExistence($dir_alias)) {
                $dir_id = insertDirectory($dir_alias);
            } else {
                $prepare2 = $pearDB->prepare(
                    "SELECT dir_id FROM view_img_dir WHERE dir_alias = :dir_alias"
                );
                $prepare2->bindValue(':dir_alias', $dir_alias, \PDO::PARAM_STR);

                if (!$prepare2->execute()) {
                    return;
                }
                $dir_info = $prepare2->fetch();
                $dir_id = $dir_info["dir_id"];
            }

            if (rename($oldpath, $newpath)) {
                $prepare2 = $pearDB->prepare(
                    "UPDATE view_img_dir_relation SET dir_dir_parent_id = :dir_id "
                    . " WHERE img_img_id = :image_id"
                );
                $prepare2->bindValue(':dir_id', $dir_id, \PDO::PARAM_INT);
                $prepare2->bindValue(':image_id', $img_id, \PDO::PARAM_INT);
                $prepare2->execute();
            }
        }
    }
}

function testDirectoryCallback($name)
{
    return testDirectoryExistence($name)==0;
}

function testDirectoryExistence($name)
{
    global $pearDB;
    $dir_id = 0;
    $prepare = $pearDB->prepare(
        "SELECT dir_name, dir_id FROM view_img_dir WHERE dir_name = :dir_name"
    );
    $prepare->bindValue(':dir_name', $name, \PDO::PARAM_STR);
    $prepare->execute();
    $result = $prepare->fetch(PDO::FETCH_ASSOC);
    if (isset($result['dir_id'])) {
        $dir_id = $result['dir_id'];
    }
    return $dir_id;
}

function testDirectoryIsEmpty($dir_id)
{
    if (!$dir_id) {
        return true;
    }
    global $pearDB;

    $rq = "SELECT img_img_id FROM view_img_dir_relation WHERE dir_dir_parent_id = '".$dir_id."'";
    $dbResult = $pearDB->query($rq);
    $empty = true;
    if ($dbResult && $dbResult->rowCount() >= 1) {
        $empty = false;
    }
    $dbResult->closeCursor();
    return $empty;
}

function insertDirectory($dir_alias, $dir_comment = "")
{
    global $pearDB;
    $mediadir = "./img/media/";
    $dir_alias_safe = sanitizePath($dir_alias);
    @mkdir($mediadir.$dir_alias);
    if (is_dir($mediadir.$dir_alias)) {
        touch($mediadir.$dir_alias."/index.html");
        $prepare = $pearDB->prepare(
            "INSERT INTO view_img_dir (dir_name, dir_alias, dir_comment) VALUES "
            . "(:dir_alias, :dir_alias, :dir_comment)"
        );
        $prepare->bindValue(':dir_alias', $dir_alias_safe, \PDO::PARAM_STR);
        $prepare->bindValue(':dir_comment', $dir_comment, \PDO::PARAM_STR);
        $prepare->execute();
        $dir_id = $pearDB->lastInsertId();
        return $dir_id;
    } else {
        return "";
    }
}

function deleteMultDirectory($dirs = array())
{
    foreach (array_keys($dirs) as $selector) {
        $id = explode('-', $selector);
        if (count($id)!=1) {
            continue;
        }
        deleteDirectory($id[0]);
    }
}

function deleteDirectory($directoryId)
{
    global $pearDB;
    $mediadir = "./img/media/";
    
    $directoryId = (int) $directoryId;
    /*
     * Purge images of the directory
     */
    $dbResult = $pearDB->query(
        "SELECT img_img_id "
        . "FROM view_img_dir_relation "
        . "WHERE dir_dir_parent_id = $directoryId"
    );
    while ($img = $dbResult->fetch()) {
        deleteImg($img["img_img_id"]);
    }
    /*
     * Delete directory
     */
    $dbResult = $pearDB->query(
        "SELECT dir_alias FROM view_img_dir WHERE dir_id = $directoryId"
    );
    $dirAlias = $dbResult->fetch();
    $filenames = scandir($mediadir . $dirAlias["dir_alias"]);
    foreach ($filenames as $fileName) {
        if (is_file($mediadir . $dirAlias["dir_alias"] . "/" . $fileName)) {
            unlink($mediadir . $dirAlias["dir_alias"] . "/" . $fileName);
        }
    }
    rmdir($mediadir.$dirAlias["dir_alias"]);
    if (!is_dir($mediadir.$dirAlias["dir_alias"])) {
        $dbResult = $pearDB->query("DELETE FROM view_img_dir WHERE dir_id = $directoryId");
    }
}

function updateDirectory($dir_id, $dir_alias, $dir_comment = "")
{
    if (!$dir_id) {
        return;
    }

    global $pearDB;
    $mediadir = "./img/media/";
    $rq = "SELECT dir_alias FROM view_img_dir WHERE dir_id = '".$dir_id."'";
    $dbResult = $pearDB->query($rq);
    $old_dir = $dbResult->fetch();
    $dir_alias = sanitizePath($dir_alias);
    if (!is_dir($mediadir.$old_dir["dir_alias"])) {
        mkdir($mediadir.$dir_alias);
    } else {
        rename($mediadir.$old_dir["dir_alias"], $mediadir.$dir_alias);
    }

    if (is_dir($mediadir.$dir_alias)) {
        $prepare = $pearDB->prepare(
            "UPDATE view_img_dir SET dir_name = :dir_name, "
            . "dir_alias = :dir_alias, dir_comment = :dir_comment "
            . "WHERE dir_id = :dir_id"
        );
        $prepare->bindValue(':dir_name', $dir_alias, \PDO::PARAM_STR);
        $prepare->bindValue(':dir_alias', $dir_alias, \PDO::PARAM_STR);
        $prepare->bindValue(':dir_comment', $dir_comment, \PDO::PARAM_STR);
        $prepare->bindValue(':dir_id', $dir_id, \PDO::PARAM_INT);
        $prepare->execute();
    }
}

function getListDirectory($filter = null)
{
    global $pearDB;

    $query = "SELECT dir_id, dir_name FROM view_img_dir ";
    if (!is_null($filter) && strlen($filter) > 0) {
        $query .= "WHERE dir_name LIKE '" . $filter . "%' ";
    }
    $query .= "ORDER BY dir_name";
    $list_dir = array();
    $dbresult = $pearDB->query($query);
    while ($row = $dbresult->fetch(PDO::FETCH_ASSOC)) {
        $list_dir[$row['dir_id']] = CentreonUtils::escapeSecure(
            $row['dir_name'],
            CentreonUtils::ESCAPE_ALL_EXCEPT_LINK
        );
    }
    $dbresult->closeCursor();
    return $list_dir;
}
