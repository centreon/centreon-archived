<?php
/*
 * Copyright 2005-2016 Centreon
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

/**
 *  Class used for managing images
 */
class CentreonMedia
{
    /**
     *
     * @var type
     */
    protected $db;

    /**
     *
     * @var type
     */
    protected $filenames;

    /**
     *
     * @var type
     */
    protected $mediadirectoryname = '';

    /**
     * Constructor
     * @param type $db
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->filenames = array();
    }

    /**
     * Get media directory path
     * @return string
     * @throws \Exception
     */
    public function getMediaDirectory()
    {
        $mediaDirectory = '';
        if (empty($this->mediadirectoryname)) {
            $query = "SELECT options.value FROM options WHERE options.key = 'nagios_path_img'";
            $result = $this->db->query($query);
            if (\PEAR::isError($result)) {
                throw new \Exception('Error while getting media directory ');
            }


            if ($result->numRows()) {
                $row = $result->fetchRow();
                $mediaDirectory = $row['value'];
            }

            if (trim($mediaDirectory) == '') {
                throw new \Exception('Error while getting media directory ');
            }
        } else {
            $mediaDirectory .= $this->mediadirectoryname;
        }

        return $mediaDirectory;
    }

    /**
     * Get media directory path
     * @return string
     * @throws \Exception
     */
    public function setMediaDirectory($dirname)
    {
        $this->mediadirectoryname = $dirname;
    }

    /**
     * Returns ID of target directory
     * @param string $dirname
     * @return int
     */
    public function getDirectoryId($dirname)
    {
        $dirname = $this->sanitizePath($dirname);

        $query = "SELECT dir_id FROM view_img_dir WHERE dir_name = '" . $dirname . "' LIMIT 1";
        $RES = $this->db->query($query);
        $dir_id = null;
        if ($RES->numRows()) {
            $row = $RES->fetchRow();
            $dir_id = $row['dir_id'];
        }
        return $dir_id;
    }

    /**
     * Returns name of target directory
     * @param int $directoryId
     * @return string
     */
    public function getDirectoryName($directoryId)
    {
        $query = "SELECT dir_name FROM view_img_dir WHERE dir_id = " . $directoryId . " LIMIT 1";

        $result = $this->db->query($query);

        $directoryName = null;
        if ($result->numRows()) {
            $row = $result->fetchRow();
            $directoryName = $row['dir_name'];
        }

        return $directoryName;
    }

    /**
     * Add directory
     * @param string $dirname
     * @param string $dirAlias
     * @return int
     * @throws \Exception
     */
    public function addDirectory($dirname, $dirAlias = null)
    {
        $dirname = $this->sanitizePath($dirname);
        if (is_null($this->getDirectoryId($dirname))) {
            if (is_null($dirAlias)) {
                $dirAlias = $dirname;
            }
            $query = "INSERT INTO view_img_dir (dir_name, dir_alias) VALUES ('" . $dirname . "', '" . $dirAlias . "')";
            $result = $this->db->query($query);
            if (\PEAR::isError($result)) {
                throw new \Exception('Error while creating directory ' . $dirname);
            }
        }
        $this->createDirectory($dirname);
        return $this->getDirectoryId($dirname);
    }

    /**
     * Add directory
     * @param string $dirname
     */
    private function createDirectory($dirname)
    {
        $mediaDirectory = $this->getMediaDirectory();

        $fullPath = $mediaDirectory . '/' . $dirname;

        file_put_contents('/tmp/test.txt', $fullPath);

        // Create directory
        if (!is_dir($fullPath)) {
            mkdir($fullPath);
        }
    }

    /**
     * Returns ID of target Image
     * @param string $imagename
     * @param string $dirname
     * @return mixed
     */
    public function getImageId($imagename, $dirname = null)
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
        $RES = $this->db->query($query);
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
        if (count($this->filenames)) {
            if (isset($this->filenames[$imgId])) {
                return $this->filenames[$imgId];
            } else {
                return "";
            }
        }
        $query = "SELECT img_id, img_path, dir_alias
	    		  FROM view_img vi, view_img_dir vid, view_img_dir_relation vidr
	    		  WHERE vidr.img_img_id = vi.img_id
	    		  AND vid.dir_id = vidr.dir_dir_parent_id";
        $res = $this->db->query($query);
        $this->filenames[0] = 0;
        while ($row = $res->fetchRow()) {
            $this->filenames[$row['img_id']] = $row["dir_alias"] . "/" . $row["img_path"];
        }
        if (isset($this->filenames[$imgId])) {
            return $this->filenames[$imgId];
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
    public static function getFilesFromArchive($archiveFile)
    {
        $fileName = basename($archiveFile);
        $position = strrpos($fileName, ".");
        if (false === $position) {
            throw new Exception('Missing extension');
        }
        $extension = substr($fileName, ($position + 1));
        $files = array();
        $allowedExt = array(
            'zip',
            'tar',
            'gz',
            'tgzip',
            'tgz',
            'bz',
            'tbzip',
            'tbz',
            'bzip',
            'bz2',
            'tbzip2',
            'tbz2',
            'bzip2'
        );
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

    /**
     *
     * @param string $path
     * @return string
     */
    private function sanitizePath($path)
    {
        $cleanstr = htmlentities($path, ENT_QUOTES, "UTF-8");
        $cleanstr = str_replace("/", "_", $cleanstr);
        $cleanstr = str_replace("\\", "_", $cleanstr);

        return $cleanstr;
    }

    /**
     *
     * @param string $parameters
     * @param string $binary
     * @return mixed
     * @throws \Exception
     */
    public function addImage($parameters, $binary = null)
    {
        $imageId = null;

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

        $result = $this->db->query($query);

        if (!$result->numRows()) {
            // Insert image in database
            $query = 'INSERT INTO view_img '
                . '(img_name, img_path, img_comment) '
                . 'VALUES ( '
                . '"' . $imageName . '", '
                . '"' . $imagePath . '", '
                . '"' . $imageComment . '" '
                . ') ';

            $result = $this->db->query($query);
            if (\PEAR::isError($result)) {
                throw new \Exception('Error while creating image ' . $imageName);
            }

            // Get image id
            $query = 'SELECT MAX(img_id) AS img_id '
                . 'FROM view_img '
                . 'WHERE img_name = "' . $imageName . '" '
                . 'AND img_path = "' . $imagePath . '" ';
            $result = $this->db->query($query);
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
            $result = $this->db->query($query);
            if (\PEAR::isError($result)) {
                throw new \Exception('Error while inserting relation between' . $imageName . ' and ' . $directoryName);
            }
        } else {
            $imageId = $this->getImageId($imageName, $directoryName);
        }

        // Create binary file if specified
        if (!is_null($binary)) {
            $directoryPath = $this->getMediaDirectory() . '/' . $directoryName . '/';
            $this->createImage($directoryPath, $imagePath, $binary);
        }

        return $imageId;
    }

    /**
     *
     * @param string $directoryPath
     * @param string $imagePath
     * @param string $binary
     */
    private function createImage($directoryPath, $imagePath, $binary)
    {
        $fullPath = $directoryPath . '/' . $imagePath;
        $decodedBinary = base64_decode($binary);

        if (!file_exists($fullPath)) {
            file_put_contents($fullPath, $decodedBinary);
        }
    }
}
