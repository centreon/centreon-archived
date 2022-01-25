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

class CentreonImageManager extends centreonFileManager
{
    protected $legalExtensions;
    protected $legalSize;
    protected $dbConfig;

    /**
     * CentreonImageManager constructor.
     * @param \Pimple\Container $dependencyInjector
     * @param $rawFile
     * @param $basePath
     * @param $destinationDir
     * @param string $comment
     */
    public function __construct(
        \Pimple\Container $dependencyInjector,
        $rawFile,
        $basePath,
        $destinationDir,
        $comment = ''
    ) {
        parent::__construct($dependencyInjector, $rawFile, $basePath, $destinationDir, $comment);
        $this->dbConfig = $this->dependencyInjector['configuration_db'];
        $this->legalExtensions = array("jpg", "jpeg", "png", "gif", "svg");
        $this->legalSize = 2000000;
    }

    /**
     * @param bool $insert
     * @return array|bool
     */
    public function upload($insert = true)
    {
        $parentUpload = parent::upload();

        if ($parentUpload) {
            if ($insert) {
                $img_ids[] = $this->insertImg();
                return $img_ids;
            }
        } else {
            return false;
        }
    }

    /**
     * Upload file from Temp Directory
     *
     * @param string $tempDirectory
     * @param boolean $insert
     * @return array|bool
     */
    public function uploadFromDirectory(string $tempDirectory, $insert = true)
    {
        $tempFullPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tempDirectory;
        if (!parent::fileExist()) {
            $this->moveImage(
                $tempFullPath . DIRECTORY_SEPARATOR . $this->rawFile['tmp_name'],
                $this->destinationPath . DIRECTORY_SEPARATOR . $this->rawFile['name']
            );
            if ($insert) {
                $img_ids[] = $this->insertImg();
                return $img_ids;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $imgId
     * @param $imgName
     * @return bool
     */
    public function update($imgId, $imgName)
    {
        if (!$imgId || empty($imgName)) {
            return false;
        }

        $stmt = $this->dbConfig->prepare(
            "SELECT dir_id, dir_alias, img_path, img_comment "
            . "FROM view_img, view_img_dir, view_img_dir_relation "
            . "WHERE img_id = :imgId AND img_id = img_img_id "
            . "AND dir_dir_parent_id = dir_id"
        );
        $stmt->bindParam(':imgId', $imgId);
        $stmt->execute();

        $img_info = $stmt->fetch();

        // update if new file
        if (!empty($this->originalFile) && !empty($this->tmpFile)) {
            $this->deleteImg($this->mediaPath . $img_info["dir_alias"] . '/' . $img_info["img_path"]);
            $this->upload(false);

            $stmt = $this->dbConfig->prepare(
                "UPDATE view_img SET img_path  = :path WHERE img_id = :imgId"
            );
            $stmt->bindParam(':path', $this->newFile);
            $stmt->bindParam(':imgId', $imgId);
            $stmt->execute();
        }

        // update image info
        $stmt = $this->dbConfig->prepare(
            "UPDATE view_img SET img_name  = :imgName, "
            . "img_comment = :imgComment WHERE img_id = :imgId"
        );
        $stmt->bindParam(':imgName', $this->secureName($imgName));
        $stmt->bindParam(':imgComment', $this->comment);
        $stmt->bindParam(':imgId', $imgId);
        $stmt->execute();


        //check directory
        if (!($dirId = $this->checkDirectoryExistence())) {
            $dirId = $this->insertDirectory();
        }
        // Create directory if not exist
        if ($img_info['dir_alias'] != $this->destinationDir) {
            $old = $this->mediaPath . $img_info['dir_alias'] . '/' . $img_info["img_path"];
            $new = $this->mediaPath . $this->destinationDir . '/' . $img_info["img_path"];
            $this->moveImage($old, $new);
        }

        //update relation
        $stmt = $this->dbConfig->prepare(
            "UPDATE view_img_dir_relation SET dir_dir_parent_id  = :dirId "
            . "WHERE img_img_id = :imgId"
        );
        $stmt->bindParam(':dirId', $dirId);
        $stmt->bindParam(':imgId', $imgId);
        $stmt->execute();

        return true;
    }

    /**
     * @param $fullPath
     */
    protected function deleteImg($fullPath)
    {
        unlink($fullPath);
    }

    /**
     * @return int
     */
    protected function checkDirectoryExistence()
    {
        $dirId = 0;
        $query = "SELECT dir_name, dir_id FROM view_img_dir WHERE dir_name = :dirName";
        $stmt = $this->dbConfig->prepare($query);
        $stmt->bindParam(':dirName', $this->destinationDir);
        $stmt->execute();

        if ($stmt->rowCount() >= 1) {
            $dir = $stmt->fetch();
            $dirId = $dir["dir_id"];
        }
        return $dirId;
    }

    /**
     * @return mixed
     */
    protected function insertDirectory()
    {
        touch($this->destinationPath . "/index.html");

        $stmt = $this->dbConfig->prepare(
            "INSERT INTO view_img_dir (dir_name, dir_alias) "
            . "VALUES (:dirName, :dirAlias)"
        );
        $stmt->bindParam(':dirName', $this->destinationDir, \PDO::PARAM_STR);
        $stmt->bindParam(':dirAlias', $this->destinationDir, \PDO::PARAM_STR);
        if ($stmt->execute()) {
            return $this->dbConfig->lastInsertId();
        }
        return null;
    }

    /**
     * @param $dirId
     */
    protected function updateDirectory($dirId)
    {
        $query = "UPDATE view_img_dir SET dir_name = :dirName, dir_alias = :dirAlias WHERE dir_id = :dirId";
        $stmt = $this->dbConfig->prepare($query);
        $stmt->bindParam(':dirName', $this->destinationDir, \PDO::PARAM_STR);
        $stmt->bindParam(':dirAlias', $this->destinationDir, \PDO::PARAM_STR);
        $stmt->bindParam(':dirId', $dirId, \PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * @return mixed
     */
    protected function insertImg()
    {
        if (!($dirId = $this->checkDirectoryExistence())) {
            $dirId = $this->insertDirectory();
        }

        $stmt = $this->dbConfig->prepare(
            "INSERT INTO view_img (img_name, img_path, img_comment) "
            . "VALUES (:imgName, :imgPath, :dirComment)"
        );
        $stmt->bindParam(':imgName', $this->fileName, \PDO::PARAM_STR);
        $stmt->bindParam(':imgPath', $this->newFile, \PDO::PARAM_STR);
        $stmt->bindParam(':dirComment', $this->comment, \PDO::PARAM_STR);
        $stmt->execute();
        $imgId = $this->dbConfig->lastInsertId();

        $stmt = $this->dbConfig->prepare(
            "INSERT INTO view_img_dir_relation (dir_dir_parent_id, img_img_id) "
            . "VALUES (:dirId, :imgId)"
        );
        $stmt->bindParam(':dirId', $dirId, \PDO::PARAM_INT);
        $stmt->bindParam(':imgId', $imgId, \PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();
        return $imgId;
    }

    /**
     * @param $old
     * @param $new
     */
    protected function moveImage($old, $new)
    {
        copy($old, $new);
        $this->deleteImg($old);
    }
}
