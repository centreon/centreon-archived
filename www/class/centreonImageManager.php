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
        $this->legalExtensions = array("jpg", "jpeg", "png", "gif");
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
     * @param $imgId
     * @param $imgName
     * @return bool
     */
    public function update(
        $imgId,
        $imgName
    ) {
        if (!$imgId || empty($imgName)) {
            return false;
        }

        $query = "SELECT dir_id, dir_alias, img_path, img_comment FROM view_img, view_img_dir, view_img_dir_relation " .
            "WHERE img_id = :imgId AND img_id = img_img_id AND dir_dir_parent_id = dir_id";
        $stmt = $this->dbConfig->prepare($query);
        $stmt->bindParam(':imgId', $imgId);
        $stmt->execute();

        $img_info = $stmt->fetch();

        // update if new file
        if (!empty($this->originalFile) && !empty($this->tmpFile)) {
            $this->deleteImg($this->mediaPath . $img_info["dir_alias"] . '/' . $img_info["img_path"]);
            $this->upload(false);
            $query = "UPDATE view_img SET img_path  = :path WHERE img_id = :imgId";
            $stmt = $this->dbConfig->prepare($query);
            $stmt->bindParam(':path', $this->newFile);
            $stmt->bindParam(':imgId', $imgId);
            $stmt->execute();
        }

        // update image info
        $query = "UPDATE view_img SET img_name  = :imgName, img_comment = :imgComment WHERE img_id = :imgId";
        $stmt = $this->dbConfig->prepare($query);
        $stmt->bindParam(':imgName', $this->secureName($imgName));
        $stmt->bindParam(':imgComment', $this->comment);
        $stmt->bindParam(':imgId', $imgId);
        $stmt->execute();


        //check directory
        if (!($dirId = $this->checkDirectoryExistence())) {
            $dirId = $this->insertDirectory();
        } elseif ($img_info['dir_alias'] != $this->destinationDir) {
            $old = $this->mediaPath . $img_info['dir_alias'] . '/' . $img_info["img_path"];
            $new = $this->mediaPath . $this->destinationDir . '/' . $img_info["img_path"];
            $this->moveImage($old, $new);
        }

        //update relation
        $query = "UPDATE view_img_dir_relation SET dir_dir_parent_id  = :dirId WHERE img_img_id = :imgId";
        $stmt = $this->dbConfig->prepare($query);
        $stmt->bindParam(':dirId', $dirId);
        $stmt->bindParam(':imgId', $imgId);
        $stmt->execute();
        
        return true;
    }

    /**
     * @param $fullPath
     */
    protected function deleteImg(
        $fullPath
    ) {
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
        $query = "INSERT INTO view_img_dir (dir_name, dir_alias) VALUES (:dirName, :dirAlias)";
        $stmt = $this->dbConfig->prepare($query);
        $stmt->bindParam(':dirName', $this->destinationDir);
        $stmt->bindParam(':dirAlias', $this->destinationDir);
        $stmt->execute();

        $stmt = $this->dbConfig->query("SELECT MAX(dir_id) FROM view_img_dir");
        $dirId = $stmt->fetch();
        $stmt->closeCursor();
        return ($dirId["MAX(dir_id)"]);
    }

    /**
     * @param $dirId
     */
    protected function updateDirectory(
        $dirId
    ) {
        $query = "UPDATE view_img_dir SET dir_name = :dirName, dir_alias = :dirAlias WHERE dir_id = :dirId";
        $stmt = $this->dbConfig->prepare($query);
        $stmt->bindParam(':dirName', $this->destinationDir);
        $stmt->bindParam(':dirAlias', $this->destinationDir);
        $stmt->bindParam(':dirId', $dirId);
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

        $query = "INSERT INTO view_img (img_name, img_path, img_comment) VALUES (:imgName, :imgPath, :dirComment)";
        $stmt = $this->dbConfig->prepare($query);
        $stmt->bindParam(':imgName', $this->fileName);
        $stmt->bindParam(':imgPath', $this->newFile);
        $stmt->bindParam(':dirComment', $this->comment);
        $stmt->execute();

        $stmt = $this->dbConfig->query("SELECT MAX(img_id) FROM view_img");
        $imgId = $stmt->fetch();
        $imgId = $imgId["MAX(img_id)"];

        $query = "INSERT INTO view_img_dir_relation (dir_dir_parent_id, img_img_id) VALUES (:dirId, :imgId)";
        $stmt = $this->dbConfig->prepare($query);
        $stmt->bindParam(':dirId', $dirId);
        $stmt->bindParam(':imgId', $imgId);
        $stmt->execute();
        $stmt->closeCursor();
        return ($imgId);
    }

    /**
     * @param $old
     * @param $new
     */
    protected function moveImage(
        $old,
        $new
    ) {
        copy($old, $new);
        $this->deleteImg($old);
    }
}
