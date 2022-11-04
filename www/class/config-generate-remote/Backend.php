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

use Exception;

// file centreon.config.php may not exist in test environment
$configFile = realpath(__DIR__ . "/../../../config/centreon.config.php");
if ($configFile !== false) {
    require_once $configFile;
}

class Backend
{
    private static $instance = null;
    public $db = null;
    public $dbCs = null;

    private $subdirs = ['configuration', 'media'];

    private $fieldSeparatorInfile = '~~~';
    private $lineSeparatorInfile = '######';

    private $tmpDirPrefix = 'tmpdir_';

    private $tmpFile = null;
    private $tmpDir = null;
    private $tmpDirSuffix = '.d';
    private $fullPath = null;
    private $whoaim = 'unknown';

    private $exportContact = false;

    private $pollerId = null;
    private $centralPollerId = null;

    /**
     * Constructor
     *
     * @param \Pimple\Container $dependencyInjector
     */
    private function __construct(\Pimple\Container $dependencyInjector)
    {
        $this->generatePath = _CENTREON_CACHEDIR_ . '/config/export';
        $this->db = $dependencyInjector['configuration_db'];
        $this->dbCs = $dependencyInjector['realtime_db'];
    }

    /**
     * Get backend singleton
     *
     * @param \Pimple\Container $dependencyInjector
     * @return void
     */
    public static function getInstance(\Pimple\Container $dependencyInjector)
    {
        if (is_null(self::$instance)) {
            self::$instance = new Backend($dependencyInjector);
        }

        return self::$instance;
    }

    /**
     * Delete directory recursively
     *
     * @param string $path
     * @param bool $onlyContent if set to false, do not delete directory itself
     * @return bool
     */
    private function deleteDir(?string $path, bool $onlyContent = false): bool
    {
        if (is_dir($path)) {
            $files = array_diff(scandir($path), ['.', '..']);
            foreach ($files as $file) {
                $this->deleteDir(realpath($path) . '/' . $file);
            }

            if (!$onlyContent) {
                return rmdir($path);
            } else {
                return true;
            }
        } elseif (is_file($path)) {
            return unlink($path);
        }

        return false;
    }

    /**
     * Create multiple directories
     *
     * @param array $paths
     * @return string created directory path
     */
    public function createDirectories(array $paths): string
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
                if (!mkdir($dir, 0770, true)) {
                    throw new Exception("Cannot create directory '" . $dir . "'");
                }
                chmod($dir, 0770);
            }
        }

        return $dir;
    }

    /**
     * generatePath getter
     *
     * @return string
     */
    public function getEngineGeneratePath(): string
    {
        return $this->generatePath . '/' . $this->engine_sub;
    }

    /**
     * Create directories to generation configuration
     *
     * @param int $pollerId
     * @return void
     */
    public function initPath(int $pollerId): void
    {
        $this->createDirectories([$this->generatePath]);
        $this->fullPath = $this->generatePath;

        if (!is_writable($this->fullPath)) {
            throw new Exception("Not writeable directory '" . $this->fullPath . "'");
        }

        if (is_dir($this->fullPath . '/' . $pollerId) && !is_writable($this->fullPath . '/' . $pollerId)) {
            throw new Exception("Not writeable directory '" . $this->fullPath . '/' . $pollerId . "'");
        }

        $this->tmpFile = basename(tempnam($this->fullPath, $this->tmpDirPrefix));
        $this->tmpDir = $this->tmpFile . $this->tmpDirSuffix;
        $this->fullPath .= '/' . $this->tmpDir;

        $this->createDirectories([$this->fullPath]);
        foreach ($this->subdirs as $subdir) {
            $this->createDirectories([$this->fullPath . '/' . $subdir]);
        }
    }

    /**
     * fieldSeparatorInfile getter
     *
     * @return void
     */
    public function getFieldSeparatorInfile()
    {
        return $this->fieldSeparatorInfile;
    }

    /**
     * lineSeparatorInfile getter
     *
     * @return void
     */
    public function getLineSeparatorInfile()
    {
        return $this->lineSeparatorInfile;
    }

    /**
     * exportContact getter
     *
     * @return boolean
     */
    public function isExportContact()
    {
        return $this->exportContact;
    }

    /**
     * fullPath getter
     *
     * @return void
     */
    public function getPath()
    {
        return $this->fullPath;
    }

    /**
     * Move poller directory
     *
     * @param integer $pollerId
     * @return void
     */
    public function movePath(int $pollerId)
    {
        $subdir = dirname($this->fullPath);
        $this->deleteDir($subdir . '/' . $pollerId);
        unlink($subdir . '/' . $this->tmpFile);
        rename($this->fullPath, $subdir . '/' . $pollerId);
    }

    /**
     * Clean directory and files
     *
     * @return void
     */
    public function cleanPath(): void
    {
        $subdir = dirname($this->fullPath);
        if (is_dir($this->fullPath)) {
            $this->deleteDir($this->fullPath, true);
        }

        @unlink($subdir . '/' . $this->tmpFile);
    }

    /**
     * username setter
     *
     * @param string $username
     * @return void
     */
    public function setUserName(string $username): void
    {
        $this->whoaim = $username;
    }

    /**
     * username getter
     *
     * @return string
     */
    public function getUserName(): string
    {
        return $this->whoaim;
    }

    /**
     * poller id setter
     *
     * @param integer $pollerId
     * @return void
     */
    public function setPollerId(int $pollerId): void
    {
        $this->pollerId = $pollerId;
    }

    /**
     * poller id getter
     *
     * @return int
     */
    public function getPollerId(): int
    {
        return $this->pollerId;
    }

    /**
     * Get id of central server
     *
     * @return int
     */
    public function getCentralPollerId(): int
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
}
