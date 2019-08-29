<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

namespace ConfigGenerateRemote;

use \Exception;

// file centreon.config.php may not exist in test environment
$configFile = realpath(__DIR__ . "/../../../config/centreon.config.php");
if ($configFile !== false) {
    require_once $configFile;
}

class Backend
{
    private static $_instance = null;
    public $generatePath = '/usr/share/centreon/filesGeneration/export';
    public $db = null;
    public $dbCs = null;

    private $subdirs = ['configuration', 'media'];

    private $fieldSeparatorInfile = '~~~';
    private $lineSeparatorInfile = '######';

    private $tmpDirSuffix = '.d';
    private $tmpDirPrefix = 'tmpdir_';

    private $tmpFile = null;
    private $tmpDir = null;
    private $fullPath = null;
    private $whoaim = 'unknown';

    private $exportContact = 0;

    private $pollerId = null;
    private $centralPollerId = null;

    public static function getInstance(\Pimple\Container $dependencyInjector)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Backend($dependencyInjector);
        }

        return self::$_instance;
    }

    private function deleteDir($path)
    {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), ['.', '..']);
            foreach ($files as $file) {
                $this->deleteDir(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        } elseif (is_file($path) === true) {
            return unlink($path);
        }

        return false;
    }

    public function createDirectories($paths)
    {
        $dir = '';
        $dirAppend = '';
        foreach ($paths as $path) {
            $dir .= $dirAppend . $path;
            $dirAppend .= '/';

            if (file_exists($dir)) {
                if (!is_dir($dir)) {
                    throw new Exception("Generation path '" . $dir . "' is not a directory.");
                }
            } else {
                if (!mkdir($dir, 0775, true)) {
                    throw new Exception("Cannot create directory '" . $dir . "'");
                }
            }
        }

        return $dir;
    }

    public function getEngineGeneratePath()
    {
        return $this->generatePath . '/' . $this->engine_sub;
    }

    public function initPath($pollerId, $engine = 1)
    {
        $this->createDirectories([$this->generatePath]);
        $this->fullPath = $this->generatePath;

        if (is_dir($this->fullPath . '/' . $pollerId) && !is_writable($this->fullPath . '/' . $pollerId)) {
            throw new Exception("Not writeable directory '" . $this->fullPath . '/' . $pollerId . "'");
        }

        if (!is_writable($this->fullPath)) {
            throw new Exception("Not writeable directory '" . $this->fullPath . "'");
        }
        $this->tmpFile = basename(tempnam($this->fullPath, $this->tmpDirPrefix));
        $this->tmpDir = $this->tmpFile . $this->tmpDir_suffix;
        if (!mkdir($this->fullPath . '/' . $this->tmpDir, 0770, true)) {
            throw new Exception("Cannot create directory '" . $dir . "'");
        }
        $this->fullPath .= '/' . $this->tmpDir;
        foreach ($this->subdirs as $subdir) {
            $this->createDirectories([$this->fullPath . '/' . $subdir]);
        }
    }

    public function getFieldSeparatorInfile()
    {
        return $this->fieldSeparatorInfile;
    }

    public function getLineSeparatorInfile()
    {
        return $this->lineSeparatorInfile;
    }

    public function isExportContact()
    {
        return $this->exportContact;
    }

    public function getPath()
    {
        return $this->fullPath;
    }

    public function movePath($pollerId)
    {
        $subdir = dirname($this->fullPath);
        $this->deleteDir($subdir . '/' . $pollerId);
        unlink($subdir . '/' . $this->tmpFile);
        rename($this->fullPath, $subdir . '/' . $pollerId);
    }

    public function cleanPath()
    {
        $subdir = dirname($this->fullPath);
        if (is_dir($this->fullPath)) {
            $this->deleteDir($this->fullPath);
        }

        @unlink($subdir . '/' . $this->tmpFile);
    }

    public function setUserName($username)
    {
        $this->whoaim = $username;
    }

    public function getUserName()
    {
        return $this->whoaim;
    }

    public function setPollerId($pollerId)
    {
        $this->pollerId = $pollerId;
    }

    public function getPollerId()
    {
        return $this->pollerId;
    }

    public function getCentralPollerId()
    {
        if (!is_null($this->centralPollerId)) {
            return $this->centralPollerId;
        }
        $this->stmtCentralPoller = $this->db->prepare("SELECT id
          FROM nagios_server
          WHERE localhost = '1' AND ns_activate = '1'
        ");
        $this->stmtCentralPoller->execute();
        if ($this->stmtCentralPoller->rowCount()) {
            $row = $this->stmtCentralPoller->fetch(PDO::FETCH_ASSOC);
            $this->centralPollerId = $row['id'];
            return $this->centralPollerId;
        } else {
            throw new Exception("Cannot get central poller id");
        }
    }

    private function __construct(\Pimple\Container $dependencyInjector)
    {
        #$this->generatePath = _CENTREON_PATH_ . '/filesGeneration';
        $this->db = $dependencyInjector['configuration_db'];
        $this->db_cs = $dependencyInjector['realtime_db'];
    }
}
