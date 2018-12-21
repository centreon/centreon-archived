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

class CentreonFileManager implements iFileManager
{

    protected $rawFile;
    protected $comment;
    protected $tmpFile;
    protected $mediaPath;
    protected $destinationPath;
    protected $destinationDir;
    protected $originalFile;
    protected $fileName;
    protected $size;
    protected $extension;
    protected $newFile;
    protected $completePath;
    protected $legalExtensions;
    protected $legalSize;

    /**
     * @param $rawFile
     * @param $mediaPath
     * @param $destinationDir
     * @param string $comment
     */
    public function __construct(
        $rawFile,
        $mediaPath,
        $destinationDir,
        $comment = ''
    ) {
        $this->mediaPath = $mediaPath;
        $this->comment = $comment;
        $this->rawFile = $rawFile["filename"];
        $this->destinationDir = $this->secureName($destinationDir);
        $this->destinationPath = $this->mediaPath . $this->destinationDir;
        $this->dirExist($this->destinationPath);
        $this->originalFile = $this->rawFile['name'];
        $this->tmpFile = $this->rawFile['tmp_name'];
        $this->size = $this->rawFile['size'];
        $this->extension = pathinfo($this->originalFile, PATHINFO_EXTENSION);
        $this->fileName = $this->secureName(basename($this->originalFile, '.' . $this->extension));
        $this->newFile = $this->fileName . '.' . $this->extension;
        $this->completePath = $this->destinationPath . '/' . $this->newFile;
        $this->legalExtensions = array();
        $this->legalSize = 500000;
    }

    /**
     * Upload a new image
     *
     * @return bool Returns true on success
     */
    public function upload()
    {
        if ($this->securityCheck()) {
            $this->moveFile();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Indicates if all tests are passed
     *
     * @see CentreonFileManager::validFile()
     * @see CentreonFileManager::validSize()
     * @see CentreonFileManager::secureExtension()
     * @see CentreonFileManager::fileExist()
     * @return bool
     */
    protected function securityCheck()
    {
        if (!$this->validFile() ||
            !$this->validSize() ||
            !$this->secureExtension() ||
            $this->fileExist()
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Secure the text given as a parameter
     *
     * @param string $text text to secure
     * @return string Returns the secured text
     */
    protected function secureName($text)
    {
        $utf8 = array(
            '/[áàâãªä]/u' => 'a',
            '/[ÁÀÂÃÄ]/u' => 'A',
            '/[ÍÌÎÏ]/u' => 'I',
            '/[íìîï]/u' => 'i',
            '/[éèêë]/u' => 'e',
            '/[ÉÈÊË]/u' => 'E',
            '/[óòôõºö]/u' => 'o',
            '/[ÓÒÔÕÖ]/u' => 'O',
            '/[úùûü]/u' => 'u',
            '/[ÚÙÛÜ]/u' => 'U',
            '/ç/' => 'c',
            '/Ç/' => 'C',
            '/ñ/' => 'n',
            '/Ñ/' => 'N',
            '/–/' => '-',
            '/[“”«»„"’‘‹›‚]/u' => '',
            '/ /' => '',
            '/\//' => '',
            '/\'/' => '',
        );
        return preg_replace(array_keys($utf8), array_values($utf8), $text);
    }

    /**
     * Indicates whether the extension file is allowed
     *
     * @return bool Returns true if the extension file is allowed
     */
    protected function secureExtension()
    {

       return in_array(strtolower($this->extension), $this->legalExtensions);
    }

    /**
     * Indicates whether the uploaded file is valid.
     * Check if the file name is not empty.
     * Check if the file size is not equal to 0.
     *
     * @return bool
     */
    protected function validFile()
    {
        if (empty($this->tmpFile) || $this->size == 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Indicates whether the size of the uploaded file is valid
     * @return bool
     */
    protected function validSize()
    {
        if ($this->size < $this->legalSize) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Indicates if the destination file exists
     *
     * @return bool Returns true if the file exists
     */
    protected function fileExist()
    {
        if (file_exists($this->completePath)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create directory if not exist
     *
     * @param string $dir Directory name to check
     * @return bool Returns true on success
     */
    protected function dirExist($dir)
    {
        if (!is_dir($dir)) {
            return @mkdir($dir);
        }
    }

    /**
     * Move the uploaded file
     *
     * @return bool Returns true on success
     */
    protected function moveFile()
    {
        return move_uploaded_file($this->tmpFile, $this->completePath);
    }
}
