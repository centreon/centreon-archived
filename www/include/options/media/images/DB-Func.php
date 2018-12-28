<?php
/**
 * Copyright 2005-2018 Centreon
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

define('MEDIA_DIR', './img/media/');

/**
 * Replace the characters space, / and \ by _
 *
 * @param string $filename Filename to filter
 * @return string Filtered filename
 */
function sanitizeFilename($filename)
{
    return str_replace(
        array(' ', '/', '\\'),
        "_",
        $filename
    );
}

/**
 * Replace the characters #, / and \ by _
 *
 * @param string $path Path to filter
 * @return string Filtered path
 */
function sanitizePath($path)
{
    return str_replace(
        array('#', '/', '\\'),
        "_",
        $path
    );
}

/**
 * Extract the content of ZIP file
 *
 * @param string $zipfile ZIP file to extract
 * @param string $path Path where the files will be extracted
 * @uses ZipArchive
 * @return bool Return true if the extraction has been done
 */
function extractDir($zipfile, $path)
{
    if (class_exists('ZipArchive')
        && file_exists($zipfile)
        && !is_null($path)
    ) {
        $zip = new ZipArchive();
        if ($zip->open($zipfile) === true) {
            if ($zip->extractTo($path) === true) {
                $zip->close();
                return true;
            } else {
                $zip->close();
                return false;
            }
        }
    }
    return false;
}

/**
 * Indicates if the file is a valid image
 *
 * @param string $filename File name of the image to check
 * @return bool Returns true if the file is a valid image
 */
function isValidImage($filename)
{
    if (is_null($filename)) {
        return false;
    }
    $imginfo = getimagesize($filename);

    return ($imginfo !== false)
        ? true
        : is_gd2($filename);
}

/**
 * Indicates if the file is an image in GD2 format.
 * Uses the GD extension.
 *
 * @param string $filename File name of the image to check
 * @return bool Returns true if the file is in GD2 format
 */
function is_gd2($filename)
{
    if (is_null($filename)) {
        return false;
    }
    if (getimagesize($filename) !== false) {
        return false;
    }
    $gd_res = imagecreatefromgd2($filename);
    if ($gd_res !== false) {
        return imagedestroy($gd_res);
    }
    return false;
}

/**
 * Delete multiple images
 *
 * @param array $images
 * @throws Exception
 */
function deleteMultImg($images = array())
{
    foreach (array_keys($images) as $selector) {
        $id = explode('-', $selector);
        if (count($id) !== 2) {
            continue;
        }
        deleteImg((int) $id[1]);
    }
}

/**
 * Delete an image
 *
 * @param int $img_id Image id to remove
 * @global CentreonDB $pearDB DB connector
 * @throws Exception
 */
function deleteImg($img_id)
{
    if (!is_int($img_id)) {
        return;
    }

    global $pearDB;

    $DBRESULT = $pearDB->query(
        'SELECT dir_alias, img_path '
        . 'FROM view_img, view_img_dir, view_img_dir_relation '
        . 'WHERE img_id = ' . $img_id
        . ' AND img_id = img_img_id AND dir_dir_parent_id = dir_id'
    );
    while ($img_path = $DBRESULT->fetchRow()) {
        $fullpath = MEDIA_DIR . $img_path["dir_alias"] . "/" . $img_path["img_path"];
        if (is_file($fullpath)) {
            unlink($fullpath);
        }
        $pearDB->query('DELETE FROM view_img WHERE img_id = ' . $img_id);
        $pearDB->query('DELETE FROM view_img_dir_relation WHERE img_img_id = ' . $img_id);
    }
    $DBRESULT->free();
}

/**
 * Move multiple images
 *
 * @param int[] $images List of directories ids to move
 * @param string $destinationDirectoryAlias Destination directory alias
 * @throws Exception
 */
function moveMultImg($images, $destinationDirectoryAlias)
{
    if (count($images) > 0) {
        foreach ($images as $id) {
            moveImg((int) $id, $destinationDirectoryAlias);
        }
    }
}

/**
 * Move an image
 *
 * @param int $imageId Id of the image to move
 * @param string $destinationDirectoryAlias Destination directory alias
 * @global CentreonDB $pearDB DB connector
 * @throws Exception
 */
function moveImg($imageId, $destinationDirectoryAlias)
{
    if (!is_int($imageId)) {
        return;
    }
    global $pearDB;

    $DBRESULT = $pearDB->query(
        'SELECT dir_id, dir_alias, img_path, img_comment '
        . 'FROM view_img, view_img_dir, view_img_dir_relation '
        . 'WHERE img_id = ' . $imageId . ' AND img_id = img_img_id '
        . 'AND dir_dir_parent_id = dir_id'
    );
    if (!$DBRESULT) {
        return;
    }
    $img_info = $DBRESULT->fetchRow();

    if ($destinationDirectoryAlias) {
        $destinationDirectoryAlias = sanitizePath($destinationDirectoryAlias);
    } else {
        $destinationDirectoryAlias = $img_info["dir_alias"];
    }
    if ($destinationDirectoryAlias !== $img_info["dir_alias"]) {
        if (!testDirectoryExistence($destinationDirectoryAlias)) {
            $dir_id = insertDirectory($destinationDirectoryAlias);
        } else {
            $DBRESULT = $pearDB->query(
                "SELECT dir_id FROM view_img_dir WHERE dir_alias = '"
                . CentreonDB::escape($destinationDirectoryAlias) . "'"
            );
            if (!$DBRESULT) {
                return;
            }
            $dir_info = $DBRESULT->fetchRow();
            $dir_id = (int) $dir_info["dir_id"];
        }
        $oldpath = MEDIA_DIR . $img_info["dir_alias"] . "/" . $img_info["img_path"];
        $newpath = MEDIA_DIR . $destinationDirectoryAlias . "/" . $img_info["img_path"];
        if (rename($oldpath, $newpath)) {
             $pearDB->query(
                'UPDATE view_img_dir_relation '
                . 'SET dir_dir_parent_id = ' . $dir_id
                . ' WHERE img_img_id = ' . $imageId
            );
        }
    }
}

/**
 * Check if the directory exists in the database
 *
 * @param string $name Directory name
 * @global CentreonDB $pearDB DB connector
 * @return int|false Returns id of the directory if it was found otherwise false
 * @throws Exception
 */
function testDirectoryExistence($name)
{
    global $pearDB;
    $DBRESULT = $pearDB->query(
        "SELECT dir_name, dir_id FROM view_img_dir WHERE dir_name = '"
        . CentreonDB::escape($name) . "'"
    );
    if ($DBRESULT->numRows() >= 1) {
        $dir = $DBRESULT->fetchRow();
        return (int) $dir["dir_id"];
    }
    return false;
}

/**
 * Insert the directory in database
 *
 * @param string $dir_alias Alias of the directory
 * @param string $dir_comment Comment of the directory
 * @global CentreonDB $pearDB DB connector
 * @return int|false Returns the new directory id otherwise false
 * @throws Exception
 */
function insertDirectory($dir_alias, $dir_comment = "")
{
    global $pearDB;

    $dir_alias = sanitizePath($dir_alias);
    @mkdir(MEDIA_DIR . $dir_alias);
    if (is_dir(MEDIA_DIR . $dir_alias)) {
        touch(MEDIA_DIR . $dir_alias . "/index.html");
        $dir_alias_safe = CentreonDB::escape($dir_alias);

        $rq = 'INSERT INTO view_img_dir (dir_name, dir_alias, dir_comment) '
            . 'VALUES '
            . "('" . $dir_alias_safe . "', '" . $dir_alias_safe . "', '"
            . CentreonDB::escape($dir_comment)."')";
        $pearDB->query($rq);
        $result = $pearDB->query('SELECT MAX(dir_id) AS max_id FROM view_img_dir');
        $maxDirectoryId = $result->fetchRow();
        $result->free();
        return (int) $maxDirectoryId["max_id"];
    } else {
        return false;
    }
}

/**
 * Delete a list of directories
 *
 * @param int[] $dirs List of directory id to delete
 * @throws Exception
 */
function deleteMultDirectory($dirs)
{
    if (!is_array($dirs)) {
        return;
    }
    foreach (array_keys($dirs) as $selector) {
        $id = explode('-', $selector);
        if (count($id) != 1) {
            continue;
        }
        deleteDirectory((int) $id[0]);
    }
}

/**
 * Delete a directory by its id
 *
 * @param int $directoryId Directory id to delete
 * @global CentreonDB $pearDB DB connector
 * @throws Exception
 */
function deleteDirectory($directoryId)
{
    global $pearDB;

    if (!is_int($directoryId)) {
        return;
    }

    /*
     * Purge images of the directory
     */
    $result = $pearDB->query(
        'SELECT img_img_id FROM view_img_dir_relation '
        . 'WHERE dir_dir_parent_id = ' . $directoryId
    );
    while ($image = $result->fetchRow()) {
        deleteImg($image["img_img_id"]);
    }
    /*
     * Delete directory
     */
    $result = $pearDB->query(
        'SELECT dir_alias FROM view_img_dir WHERE dir_id = ' . $directoryId
    );
    $directoryAlias = $result->fetchRow();
    /**
     * Deletes all files in the directory before
     */
    $fileTab = scandir(MEDIA_DIR . $directoryAlias["dir_alias"]);
    foreach ($fileTab as $fileName) {
        if (is_file(MEDIA_DIR . $directoryAlias["dir_alias"] . "/" . $fileName)) {
            unlink(MEDIA_DIR . $directoryAlias["dir_alias"] . "/" . $fileName);
        }
    }

    if (rmdir(MEDIA_DIR . $directoryAlias["dir_alias"])
        && !is_dir(MEDIA_DIR . $directoryAlias["dir_alias"])) {
        $pearDB->query(
            'DELETE FROM view_img_dir WHERE dir_id = ' . $directoryId
        );
    }
}

/**
 * Updates one directory
 *
 * @param int $directoryId Id of the directory to update
 * @param string $newAlias New alias of the directory
 * @param string $newComment New comment of the directory
 * @global CentreonDB $pearDB DB connector
 * @throws Exception
 */
function updateDirectory($directoryId, $newAlias, $newComment = "")
{
    if (!is_int($directoryId)) {
        return;
    }

    global $pearDB;

    $result = $pearDB->query(
        'SELECT dir_alias FROM view_img_dir WHERE dir_id = ' . $directoryId
    );
    $currentDirectory = $result->fetchRow();
    $newAlias = sanitizePath($newAlias);
    if (!is_dir(MEDIA_DIR . $currentDirectory["dir_alias"])) {
        mkdir(MEDIA_DIR . $newAlias);
    } else {
        rename(
            MEDIA_DIR . $currentDirectory["dir_alias"],
            MEDIA_DIR . $newAlias
        );
    }

    if (is_dir(MEDIA_DIR . $newAlias)) {
        if (!file_exists(MEDIA_DIR . $newAlias . "/index.html")) {
            touch(MEDIA_DIR . $newAlias . "/index.html");
        }
        $pearDB->query(
            "UPDATE view_img_dir "
            . "SET dir_name = '" . CentreonDB::escape($newAlias) . "', "
            . "dir_alias = '" . CentreonDB::escape($newAlias) . "', "
            . "dir_comment = '" . CentreonDB::escape($newComment) . "' "
            . 'WHERE dir_id = ' . $directoryId
        );
    }
}

/**
 * Returns a list a directories according to the given filter
 *
 * @param string $directoryNameFilter Directory name to search
 * @return array List of directories indexed by their id. array(id => directoryName, ...)
 * @throws Exception
 */
function getListDirectory($directoryNameFilter = null)
{
    global $pearDB;

    $query = "SELECT dir_id, dir_name "
        . "FROM view_img_dir ";
    if (!is_null($directoryNameFilter) && strlen($directoryNameFilter) > 0) {
        $query .= "WHERE dir_name LIKE '" . CentreonDB::escape($directoryNameFilter) . "%' ";
    }
    $query .= "ORDER BY dir_name";
    $directories = array();
    $dbresult = $pearDB->query($query);
    while ($row = $dbresult->fetchRow(DB_FETCHMODE_ASSOC)) {
        $directories[$row['dir_id']] = htmlentities($row['dir_name']);
    }
    $dbresult->free();
    return $directories;
}
