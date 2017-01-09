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

require_once realpath(dirname(__FILE__) . "/../../../config/centreon.config.php");

define ('TMP_DIR_PREFIX', 'tmpdir_');
define ('TMP_DIR_SUFFIX', '.d');

class Backend {
    private static $_instance = null;
    public $generate_path = '/usr/share/centreon/filesGeneration';
    public $engine_sub = 'engine';
    public $broker_sub = 'broker';
    public $db = null;
    public $db_cs = null;
    
    private $tmp_file = null;
    private $tmp_dir = null;
    private $full_path = null;
    private $whoaim = 'unknown';

    private $poller_id = null;
    private $central_poller_id = null;

    public static function getInstance() {
        if(is_null(self::$_instance)) {
            self::$_instance = new Backend();  
        }
 
        return self::$_instance;
    }
    
    private function deleteDir($path) {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));
            foreach ($files as $file) {
                $this->deleteDir(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        } else if (is_file($path) === true){
            return unlink($path);
        }

        return false;
    }
    
    protected function createDirectories($paths) {
        $dir = '';
        $dir_append = '';
        foreach ($paths as $path) {
            $dir .= $dir_append . $path;
            $dir_append .= '/';
            
            if (file_exists($dir)) {
                if (!is_dir($dir)) {
                    throw new Exception("Generation path '" .  $dir . "' is not a directory.");
                }
            } else {
                if (!mkdir($dir, 0770, true)) {
                    throw new Exception("Cannot create directory '" . $dir ."'");
                }
            }
        }
        
        return $dir;
    }
    
    public function getEngineGeneratePath() {
        return $this->generate_path . '/' . $this->engine_sub;
    }
    
    public function initPath($poller_id, $engine=1) {
        if ($engine == 1) {
            $this->createDirectories(array($this->generate_path, $this->engine_sub));
            $this->full_path = $this->generate_path . '/' . $this->engine_sub;
        } else {
            $this->createDirectories(array($this->generate_path, $this->broker_sub));
            $this->full_path = $this->generate_path . '/' . $this->broker_sub;
        }
        if (is_dir($this->full_path . '/' . $poller_id) && !is_writable($this->full_path . '/' . $poller_id)) {
            throw new Exception("Not writeable directory '" . $this->full_path . '/' . $poller_id . "'");
        }
        
        if (!is_writable($this->full_path)) {
            throw new Exception("Not writeable directory '" . $this->full_path . "'");
        }
        $this->tmp_file = basename(tempnam($this->full_path, TMP_DIR_PREFIX));
        $this->tmp_dir = $this->tmp_file . TMP_DIR_SUFFIX;
        if (!mkdir($this->full_path . '/' . $this->tmp_dir, 0770, true)) {
            throw new Exception("Cannot create directory '" . $dir ."'");
        }
        $this->full_path .= '/' . $this->tmp_dir;
    }
    
    public function getPath() {
        return $this->full_path;
    }
    
    public function movePath($poller_id) {
        $subdir = dirname($this->full_path);
        $this->deleteDir($subdir . '/' . $poller_id);
        unlink($subdir . '/' . $this->tmp_file);
        rename($this->full_path, $subdir . '/' . $poller_id);
    }
    
    public function cleanPath() {
        $subdir = dirname($this->full_path);
        if (is_dir($this->full_path)) {
            $this->deleteDir($this->full_path);
        }
        
        @unlink($subdir . '/' . $this->tmp_file);
    }
    
    public function setUserName($username) {
        $this->whoaim = $username;
    }
    public function getUserName() {
        return $this->whoaim;
    }

    public function setPollerId($poller_id) {
        $this->poller_id = $poller_id;
    }
    public function getPollerId() {
        return $this->poller_id;
    }

    public function getCentralPollerId() {
       if (!is_null($this->central_poller_id)) {
           return $this->central_poller_id;
       }
       $this->stmt_central_poller = $this->db->prepare("SELECT
           id
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

    private function __construct() {
        global $conf_centreon, $centreon_path;

        $this->generate_path = _CENTREON_PATH_ . '/filesGeneration';
                
        $mysql_host = $conf_centreon["hostCentreon"];
        $mysql_database = $conf_centreon["db"];
        $mysql_user = $conf_centreon["user"];
        $mysql_password = $conf_centreon["password"];
        $mysql_port = $conf_centreon["port"] ? $conf_centreon["port"] : '3306';
        $this->db = new PDO("mysql:dbname=pdo;host=" . $mysql_host . ";port=" . $mysql_port . ";dbname=" . $mysql_database,
        $mysql_user, $mysql_password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $mysql_host_cs = $conf_centreon["hostCentstorage"];
        $mysql_database_cs = $conf_centreon["dbcstg"];
        $this->db_cs = new PDO("mysql:dbname=pdo;host=" . $mysql_host_cs . ";port=" . $mysql_port . ";dbname=" . $mysql_database_cs,
        $mysql_user, $mysql_password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $this->db_cs->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}
