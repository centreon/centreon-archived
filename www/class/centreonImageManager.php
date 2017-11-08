<?php
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
     * centreonImageUploader constructor.
     * @param $rawFile
     * @param $basePath
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
     * @param bool $insert
     * @return array
     */
    public function upload($insert = true)
    {
        $parentUpload = parent::upload();
        if ($parentUpload && $insert) {
            $img_ids[] = $this->insertImg(
                $this->destinationPath,
                $this->destinationDir,
                $this->newFile,
                $this->comment
            );
            return $img_ids;
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

        global $pearDB;
        $query = "SELECT dir_id, dir_alias, img_path, img_comment FROM view_img, view_img_dir, view_img_dir_relation " .
            "WHERE img_id = '" . $imgId . "' AND img_id = img_img_id AND dir_dir_parent_id = dir_id";
        $dbResult = $pearDB->query($query);

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
        } elseif ($img_info['dir_alias'] != $this->destinationDir) {
            $old = $this->mediaPath . $img_info['dir_alias'] . '/' . $img_info["img_path"];
            $new = $this->mediaPath . $this->destinationDir . '/' . $img_info["img_path"];
            $this->moveImage($old, $new);
        }

        //update relation
        $query = "UPDATE view_img_dir_relation SET dir_dir_parent_id  = '" . $dirId .
            "' WHERE img_img_id = '" . $imgId . "'";
        $pearDB->query($query);
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
        global $pearDB;
        $dirId = 0;
        $dbResult = $pearDB->query(
            "SELECT dir_name, dir_id FROM view_img_dir WHERE dir_name = '" . $this->destinationDir . "'"
        );
        if ($dbResult->numRows() >= 1) {
            $dir = $dbResult->fetchRow();
            $dirId = $dir["dir_id"];
        }
        return $dirId;
    }

    /**
     * @return mixed
     */
    protected function insertDirectory()
    {
        global $pearDB;
        touch($this->destinationPath . "/index.html");
        $query = "INSERT INTO view_img_dir " .
            "(dir_name, dir_alias) " .
            "VALUES " .
            "('" . $this->destinationDir . "', '" . $this->destinationDir . "')";
        $pearDB->query($query);
        $dbResult = $pearDB->query("SELECT MAX(dir_id) FROM view_img_dir");
        $dirId = $dbResult->fetchRow();
        $dbResult->free();
        return ($dirId["MAX(dir_id)"]);
    }

    /**
     * @param $dirId
     */
    protected function updateDirectory($dirId)
    {
        global $pearDB;
        $query = "UPDATE view_img_dir SET dir_name = '" . $this->destinationDir .
            "', dir_alias = '" . $this->destinationDir . "' WHERE dir_id = " . $dirId;
        $pearDB->query($query);
    }

    /**
     * @return mixed
     */
    protected function insertImg()
    {
        global $pearDB;
        if (!($dirId = $this->checkDirectoryExistence())) {
            $dirId = $this->insertDirectory();
        }
        $query = "INSERT INTO view_img " .
            "(img_name, img_path, img_comment) " .
            "VALUES " .
            "('" . $this->fileName . "', '" . $this->newFile . "', '" . $pearDB->escape($this->comment) . "')";
        $pearDB->query($query);

        $res = $pearDB->query("SELECT MAX(img_id) FROM view_img");
        $imgId = $res->fetchRow();
        $imgId = $imgId["MAX(img_id)"];

        $pearDB->query(
            "INSERT INTO view_img_dir_relation (dir_dir_parent_id, img_img_id) " .
            "VALUES ('" . $dirId . "', '" . $imgId . "')"
        );
        $res->free();
        return ($imgId);
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
