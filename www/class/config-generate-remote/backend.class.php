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
$configFile = realpath(dirname(__FILE__) . "/../../../config/centreon.config.php");
if ($configFile !== false) {
    require_once $configFile;
}

class Backend
{
    private static $_instance = null;
    public $generate_path = '/usr/share/centreon/filesGeneration/export';
    public $db = null;
    public $db_cs = null;

    private $subdirs = array('configuration', 'media');

    private $fieldSeparatorInfile = '~~~';
    private $lineSeparatorInfile = '######';

    private $tmp_dir_suffix = '.d';
    private $tmp_dir_prefix = 'tmpdir_';

    private $tmp_file = null;
    private $tmp_dir = null;
    private $full_path = null;
    private $whoaim = 'unknown';
    
    private $exportContact = 0;

    private $poller_id = null;
    private $central_poller_id = null;

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
            $files = array_diff(scandir($path), array('.', '..'));
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
        $dir_append = '';
        foreach ($paths as $path) {
            $dir .= $dir_append . $path;
            $dir_append .= '/';

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
        return $this->generate_path . '/' . $this->engine_sub;
    }

    public function initPath($poller_id, $engine = 1)
    {
        $this->createDirectories(array($this->generate_path));
        $this->full_path = $this->generate_path;
    
        if (is_dir($this->full_path . '/' . $poller_id) && !is_writable($this->full_path . '/' . $poller_id)) {
            throw new Exception("Not writeable directory '" . $this->full_path . '/' . $poller_id . "'");
        }

        if (!is_writable($this->full_path)) {
            throw new Exception("Not writeable directory '" . $this->full_path . "'");
        }
        $this->tmp_file = basename(tempnam($this->full_path, $this->tmp_dir_prefix));
        $this->tmp_dir = $this->tmp_file . $this->tmp_dir_suffix;
        if (!mkdir($this->full_path . '/' . $this->tmp_dir, 0770, true)) {
            throw new Exception("Cannot create directory '" . $dir . "'");
        }
        $this->full_path .= '/' . $this->tmp_dir;
        foreach ($this->subdirs as $subdir) {
            $this->createDirectories(array($this->full_path . '/' . $subdir));
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
        return $this->full_path;
    }

    public function movePath($poller_id)
    {
        $subdir = dirname($this->full_path);
        $this->deleteDir($subdir . '/' . $poller_id);
        unlink($subdir . '/' . $this->tmp_file);
        rename($this->full_path, $subdir . '/' . $poller_id);
    }

    public function cleanPath()
    {
        $subdir = dirname($this->full_path);
        if (is_dir($this->full_path)) {
            $this->deleteDir($this->full_path);
        }

        @unlink($subdir . '/' . $this->tmp_file);
    }

    public function setUserName($username)
    {
        $this->whoaim = $username;
    }

    public function getUserName()
    {
        return $this->whoaim;
    }

    public function setPollerId($poller_id)
    {
        $this->poller_id = $poller_id;
    }

    public function getPollerId()
    {
        return $this->poller_id;
    }

    public function getCentralPollerId()
    {
        if (!is_null($this->central_poller_id)) {
            return $this->central_poller_id;
        }
        $this->stmt_central_poller = $this->db->prepare("SELECT id
          FROM nagios_server
          WHERE localhost = '1' AND ns_activate = '1'
        ");
        $this->stmt_central_poller->execute();
        if ($this->stmt_central_poller->rowCount()) {
            $row = $this->stmt_central_poller->fetch(PDO::FETCH_ASSOC);
            $this->central_poller_id = $row['id'];
            return $this->central_poller_id;
        } else {
            throw new Exception("Cannot get central poller id");
        }
    }

    private function __construct(\Pimple\Container $dependencyInjector)
    {
        #$this->generate_path = _CENTREON_PATH_ . '/filesGeneration';
        $this->db = $dependencyInjector['configuration_db'];
        $this->db_cs = $dependencyInjector['realtime_db'];
    }
}
