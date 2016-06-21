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

require_once("Archive/Tar.php");
require_once("Archive/Zip.php");

/*
 *  Class used for managing images
 */

class CentreonMedia {

    protected $_db;
    protected $_filenames;

    /*
     *  Constructor
     */
    function __construct($db)
    {
        $this->_db = $db;
        $this->_filenames = array();
    }

    /*
     *  Get media directory path
     */
    public function getMediaDirectory()
    {
        $query = "SELECT options.value FROM options WHERE options.key = 'nagios_path_img'";
        $result = $this->_db->query($query);
        if (\PEAR::isError($result)) {
            throw new \Exception('Error while getting media directory ');
        }

        $mediaDirectory = '';
        if ($result->numRows()) {
            $row = $result->fetchRow();
            $mediaDirectory = $row['value'];
        }

        if (trim($mediaDirectory) == '') {
            throw new \Exception('Error while getting media directory ');
        }

        return $mediaDirectory;        
    }

    /*
     *  Returns ID of target directory
     */
    public function getDirectoryId($dirname)
    {
        $dirname = $this->sanitizePath($dirname);

        $query = "SELECT dir_id FROM view_img_dir WHERE dir_name = '" . $dirname . "' LIMIT 1";
        $RES = $this->_db->query($query);
        $dir_id = null;
        if ($RES->numRows()) {
            $row = $RES->fetchRow();
            $dir_id = $row['dir_id'];
        }
        return $dir_id;
    }

    /*
     *  Returns name of target directory
     */
    public function getDirectoryName($directoryId)
    {
        $query = "SELECT dir_name FROM view_img_dir WHERE dir_id = " . $directoryId . " LIMIT 1";

        $result = $this->_db->query($query);

        $directoryName = null;
        if ($result->numRows()) {
            $row = $result->fetchRow();
            $directoryName = $row['dir_name'];
        }

        return $directoryName;
    }

    /*
     *  Add directory
     */
    public function addDirectory($dirname, $dirAlias = null)
    {
        $dirname = $this->sanitizePath($dirname);

        if (is_null($this->getDirectoryId($dirname))) {

            if (is_null($dirAlias)) {
                $dirAlias = $dirname;
            }
            $query = "INSERT INTO view_img_dir (dir_name, dir_alias) VALUES ('" . $dirname . "', '" . $dirAlias . "')";
            $result = $this->_db->query($query);
            if (\PEAR::isError($result)) {
                throw new \Exception('Error while creating directory ' . $dirname);
            }
        }

        $this->createDirectory($dirname);

        return $this->getDirectoryId($dirname);
    }

    /*
     *  Add directory
     */
    private function createDirectory($dirname)
    {
        $mediaDirectory = $this->getMediaDirectory();

        $fullPath = $mediaDirectory . '/' . $dirname;

        // Create directory
        if (!is_dir($fullPath)) {
            mkdir($fullPath);
        }
    }

    /*
     *  Returns ID of target Image
     */
    function getImageId($imagename, $dirname = null)
    {
        if (!isset($dirname)) {
            $tab = preg_split("/\//", $imagename);
            isset($tab[0]) ? $dirname = $tab[0] : $dirname = null;
            isset($tab[1]) ? $imagename = $tab[1] : $imagename = null;
        }

        if (!isset($imagename) || !isset($dirname)) {
            return null;
        }

        $query = "SELECT img.img_id " .
                "FROM view_img_dir dir, view_img_dir_relation rel, view_img img " .
                "WHERE dir.dir_id = rel.dir_dir_parent_id " .
                "AND rel.img_img_id = img.img_id " .
                "AND img.img_path = '" . $imagename . "' " .
                "AND dir.dir_name = '" . $dirname . "' " .
                "LIMIT 1";
        $RES = $this->_db->query($query);
        $img_id = null;
        if ($RES->numRows()) {
            $row = $RES->fetchRow();
            $img_id = $row['img_id'];
        }
        return $img_id;
    }

    /**
     * Returns the filename from a given id
     *
     * @param int $imgId
     * @return string
     */
    public function getFilename($imgId = null)
    {
        if (!isset($imgId)) {
            return "";
        }
        if (count($this->_filenames)) {
            if (isset($this->_filenames[$imgId])) {
                return $this->_filenames[$imgId];
            } else {
                return "";
            }
        }
        $query = "SELECT img_id, img_path, dir_alias
	    		  FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr
	    		  WHERE vidr.img_img_id = vi.img_id
	    		  AND vid.dir_id = vidr.dir_dir_parent_id";
        $res = $this->_db->query($query);
        $this->_filenames[0] = 0;
        while ($row = $res->fetchRow()) {
            $this->_filenames[$row['img_id']] = $row["dir_alias"] . "/" . $row["img_path"];
        }
        if (isset($this->_filenames[$imgId])) {
            return $this->_filenames[$imgId];
        }
        return "";
    }

    /**
     * Extract files from archive file and returns filenames
     * 
     * @param string $archiveFile
     * @return array
     * @throws Exception
     */
    public static function getFilesFromArchive($archiveFile) {
        $fileName = basename($archiveFile);
        $position = strrpos($fileName, ".");
        if (false === $position) {
            throw new Exception('Missing extension');
        }
        $extension = substr($fileName, ($position + 1));
        $files = array();
        $allowedExt = array('zip', 'tar', 'gz', 'tgzip',
                            'tgz', 'bz', 'tbzip',
                            'tbz', 'bzip', 'bz2',
                            'tbzip2', 'tbz2', 'bzip2');
        if (!in_array(strtolower($extension), $allowedExt)) {
            throw new Exception('Unknown extension');
        }
        if (strtolower($extension) == 'zip') {
            $archiveObj = new Archive_Zip($archiveFile);
        } else {
            $archiveObj = new Archive_Tar($archiveFile);
        }
        $elements = $archiveObj->listContent();
        foreach ($elements as $element) {
            $files[] = $element['filename'];
        }
        if (!count($files)) {
            throw new Exception('Archive file is empty');
        }
        if (strtolower($extension) == 'zip') {
            $archiveObj->extract(array('add_path' => dirname($archiveFile)));
        } else {
            if (false === $archiveObj->extractList($files, dirname($archiveFile))) {
                throw new Exception('Could not extract files');
            }
        }
        return $files;
    }

    private function sanitizePath($path)
    {
        $cleanstr = htmlentities($path, ENT_QUOTES, "UTF-8");
        $cleanstr = str_replace("/", "_", $cleanstr);
        $cleanstr = str_replace("\\", "_", $cleanstr);

        return $cleanstr;
    }

    public function addImage($parameters, $binary = null)
    {
        if (!isset($parameters['img_name']) || !isset($parameters['img_path']) || !isset($parameters['dir_name'])) {
            throw new \Exception('Cannot add media : missing parameters');
        }

        if (!isset($parameters['img_comment'])) {
            $parameters['img_comment'] = '';
        }

        $directoryName = $this->sanitizePath($parameters['dir_name']);
        $directoryId = $this->addDirectory($directoryName);

        $imageName = htmlentities($parameters['img_name'], ENT_QUOTES, "UTF-8");
        $imagePath = htmlentities($parameters['img_path'], ENT_QUOTES, "UTF-8");
        $imageComment = htmlentities($parameters['img_comment'], ENT_QUOTES, "UTF-8");

        // Check if image already exists
        $query = 'SELECT vidr.vidr_id '
            . 'FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr '
            . 'WHERE vi.img_id = vidr.img_img_id '
            . 'AND vid.dir_id = vidr.dir_dir_parent_id '
            . 'AND vi.img_name = "' . $imageName . '" '
            . 'AND vid.dir_name = "' . $directoryName . '" ';

        $result = $this->_db->query($query);

        if (!$result->numRows()) {
            // Insert image in database
            $query = 'INSERT INTO view_img '
                . '(img_name, img_path, img_comment) '
                . 'VALUES ( '
                . '"' . $imageName . '", '
                . '"' . $imagePath . '", '
                . '"' . $imageComment . '" '
                . ') ';

            $result = $this->_db->query($query);
            if (\PEAR::isError($result)) {
                throw new \Exception('Error while creating image ' . $imageName);
            }

            // Get image id
            $query = 'SELECT MAX(img_id) AS img_id '
                . 'FROM view_img '
                . 'WHERE img_name = "' . $imageName . '" '
                . 'AND img_path = "' . $imagePath . '" ';
            $result = $this->_db->query($query);
            if (\PEAR::isError($result) || !$result->numRows()) {
                throw new \Exception('Error while creating image ' . $imageName);
            }
            $row = $result->fetchRow();
            $imageId = $row['img_id'];

            // Insert relation between directory and image
            $query = 'INSERT INTO view_img_dir_relation '
                . '(dir_dir_parent_id, img_img_id) '
                . 'VALUES ('
                . $directoryId . ', '
                . $imageId . ' '
                . ') ';
            $result = $this->_db->query($query);
            if (\PEAR::isError($result)) {
                throw new \Exception('Error while inserting relation between' . $imageName . ' and ' . $directoryName);
            }
        }

        // Create binary file if specified
        if (!is_null($binary)) {
            $directoryPath = $this->getMediaDirectory() . '/' . $directoryName . '/';
            $this->createImage($directoryPath, $imagePath, $binary);
        }

        return $imageId;
    }

    private function createImage($directoryPath, $imagePath, $binary)
    {
        $fullPath = $directoryPath . '/' . $imagePath;
        $decodedBinary = base64_decode($binary);

        if (!file_exists($fullPath)) {
            file_put_contents($fullPath, $decodedBinary);
        }
    }
}
