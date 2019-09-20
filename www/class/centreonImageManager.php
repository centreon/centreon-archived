<?php
/*
 * Copyright 2005-2017 Centreon
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
/**
 * Created by PhpStorm.
 * User: loic
 * Date: 31/10/17
 * Time: 11:55
 */

class CentreonImageManager extends centreonFileManager
{

    protected $legalExtensions;
    protected $legalSize;

    /**
     * @param string $rawFile
     * @param string $basePath
     * @param string $destinationDir
     * @param string $comment
     */
    public function __construct($rawFile, $basePath, $destinationDir, $comment = '')
    {
        parent::__construct($rawFile, $basePath, $destinationDir, $comment);
        $this->legalExtensions = array("jpg", "jpeg", "png", "gif");
        $this->legalSize = 2000000;
    }

    /**
     * Upload one or more images
     *
     * @param bool $insert Indicates if we're inserting the upload into database
     * @return int|false Returns the new ids of all images uploaded otherwise false
     * @throws Exception
     */
    public function upload($insert = true)
    {
        $parentUpload = parent::upload();
        if ($parentUpload) {
            if ($insert) {
                $img_ids[] = $this->insertImg(
                    $this->destinationPath,
                    $this->destinationDir,
                    $this->newFile,
                    $this->comment
                );
                return $img_ids;
            }
        } else {
            return false;
        }
    }

    /**
     * Update an image
     *
     * @param int $imgId Id of the image to update
     * @param string $imgName New name
     * @return bool Returns true on success
     * @throws Exception
     */
    public function update($imgId, $imgName)
    {
        if (!is_int($imgId) || empty($imgName)) {
            return false;
        }

        global $pearDB;

        $dbResult = $pearDB->query(
            'SELECT dir_id, dir_alias, img_path, img_comment '
            . 'FROM view_img, view_img_dir, view_img_dir_relation '
            . 'WHERE img_id = ' . $imgId . ' AND img_id = img_img_id '
            . 'AND dir_dir_parent_id = dir_id'
        );

        if (!$dbResult) {
            return false;
        }
        $img_info = $dbResult->fetchRow();

        // update if new file
        if (!empty($this->originalFile) && !empty($this->tmpFile)) {
            $this->deleteImg($this->mediaPath . $img_info["dir_alias"] . '/' . $img_info["img_path"]);
            $this->upload(false);
            $query = "UPDATE view_img SET img_path  = '" . $this->newFile . "' WHERE img_id = '" . $imgId . "'";
            $pearDB->query($query);
        }

        // update image info
        $query = "UPDATE view_img SET img_name  = '" . $this->secureName($imgName) .
            "', img_comment = '" . $this->comment . "' WHERE img_id = '" . $imgId . "'";
        $pearDB->query($query);

        //check directory
        if (!($dirId = $this->checkDirectoryExistence())) {
            $dirId = $this->insertDirectory();
        }
        if ($img_info['dir_alias'] != $this->destinationDir) {
            $old = $this->mediaPath . $img_info['dir_alias'] . '/' . $img_info["img_path"];
            $new = $this->mediaPath . $this->destinationDir . '/' . $img_info["img_path"];
            $this->moveImage($old, $new);
        }

        //update relation
        $query = "UPDATE view_img_dir_relation SET dir_dir_parent_id  = '" . $dirId .
            "' WHERE img_img_id = '" . $imgId . "'";
        $pearDB->query($query);

        return true;
    }

    /**
     * Delete an image on disc
     *
     * @param $fullPath
     */
    protected function deleteImg($fullPath)
    {
        unlink($fullPath);
    }

    /**
     * Check if a directory given as parameter exists and return his id
     *
     * @global CentreonDB $pearDB DB connector
     * @return int|false Returns the directory if found otherwise false
     * @throws Exception
     */
    protected function checkDirectoryExistence()
    {
        global $pearDB;
        $dbResult = $pearDB->query(
            "SELECT dir_name, dir_id FROM view_img_dir WHERE dir_name = '"
            . CentreonDB::escape($this->destinationDir) . "'"
        );
        if ($dbResult->numRows() >= 1) {
            $dir = $dbResult->fetchRow();
            return (int) $dir["dir_id"];
        }
        return false;
    }

    /**
     * Create a new empty directory
     *
     * @global CentreonDB $pearDB DB connector
     * @return int Returns the new directory id
     * @throws Exception
     */
    protected function insertDirectory()
    {
        global $pearDB;

        touch($this->destinationPath . "/index.html");
        $pearDB->query(
            'INSERT INTO view_img_dir (dir_name, dir_alias) VALUES '
            . "('" . CentreonDB::escape($this->destinationDir)
            . "', '" . CentreonDB::escape($this->destinationDir) . "')"
        );
        $dbResult = $pearDB->query(
            'SELECT MAX(dir_id) AS image_id FROM view_img_dir'
        );
        $dirId = $dbResult->fetchRow();
        $dbResult->free();
        return (int) $dirId['image_id'];
    }

    /**
     * Update a directory
     *
     * @param int $dirId Id of the directory to update
     * @global CentreonDB $pearDB DB connector
     * @throws Exception
     */
    protected function updateDirectory($dirId)
    {
        global $pearDB;
        $query = "UPDATE view_img_dir SET dir_name = '" . $this->destinationDir .
            "', dir_alias = '" . $this->destinationDir . "' WHERE dir_id = " . $dirId;
        $pearDB->query($query);
    }

    /**
     * Insert an image into database
     *
     * @global CentreonDB $pearDB DB connector
     * @return int|false Returns the new id otherwise false
     * @throws Exception
     */
    protected function insertImg()
    {
        global $pearDB;

        if (($dirId = $this->checkDirectoryExistence()) === false) {
            if(($dirId = $this->insertDirectory()) === false) {
                return false;
            }
        }

        $pearDB->query(
            'INSERT INTO view_img '
            . '(img_name, img_path, img_comment) VALUES '
            . "('" . $this->fileName . "', '" . $this->newFile . "', '"
            . CentreonDB::escape($this->comment) . "')"
        );

        $res = $pearDB->query("SELECT MAX(img_id) AS image_id FROM view_img");
        $imgId = $res->fetchRow();
        $imgId = (int) $imgId['image_id'];

        $pearDB->query(
            'INSERT INTO view_img_dir_relation (dir_dir_parent_id, img_img_id) '
            . 'VALUES (' . $dirId . ', ' . $imgId . ')'
        );
        $res->free();
        return $imgId;
    }

    /**
     * Move an image
     *
     * @param string $old Old absolute path of image
     * @param string $new New absolute path of image
     */
    protected function moveImage($old, $new)
    {
        copy($old, $new);
        $this->deleteImg($old);
    }
}
