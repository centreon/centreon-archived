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
    protected $dependencyInjector;
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
     * centreonFileManager constructor.
     * @param \Pimple\Container $dependencyInjector
     * @param $rawFile
     * @param $mediaPath
     * @param $destinationDir
     * @param string $comment
     */
    public function __construct(
        \Pimple\Container $dependencyInjector,
        $rawFile,
        $mediaPath,
        $destinationDir,
        $comment = ''
    ) {

        $this->dependencyInjector = $dependencyInjector;
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
     * @return mixed
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
     * @param $text
     * @return mixed
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
     * @return bool
     */
    protected function secureExtension()
    {

        if (in_array(strtolower($this->extension), $this->legalExtensions)) {
            return true;
        } else {
            return false;
        }
    }

    /**
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
     * @return bool
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
     * @param $dir
     */
    protected function dirExist($dir)
    {
        if (!is_dir($dir)) {
            @mkdir($dir);
        }
    }

    /**
     * @return mixed
     */
    protected function moveFile()
    {
        move_uploaded_file($this->tmpFile, $this->completePath);
    }
}
