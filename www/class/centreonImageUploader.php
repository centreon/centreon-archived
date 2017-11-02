<?php
/**
 * Created by PhpStorm.
 * User: loic
 * Date: 31/10/17
 * Time: 11:55
 */

class centreonImageUploader extends centreonFileUploader
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
     * @return mixed
     */
    public function upload()
    {
        parent::upload();
        $img_ids[] = $this->insertImg($this->destinationPath, $this->destinationDir, $this->newFile, $this->comment);
        return $img_ids;
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
}