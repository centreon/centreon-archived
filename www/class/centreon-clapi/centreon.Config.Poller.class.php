<?php
/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonClapi;

require_once "centreonUtils.class.php";
require_once "centreonClapiException.class.php";
require_once _CENTREON_PATH_ . 'www/class/config-generate/generate.class.php';

/**
 *
 * @author Julien Mathis
 *
 */
class CentreonConfigPoller
{
    private $_DB;
    private $_DBC;
    private $resultTest;
    private $optGen;
    private $nagiosCFGPath;
    private $centreon_path;
    private $centcore_pipe;
    const MISSING_POLLER_ID = "Missing poller ID";
    const UNKNOWN_POLLER_ID = "Unknown poller ID";

    /**
     * Constructor
     * @param CentreonDB $DB
     * @param string $centreon_path
     * @param CentreonDB $DBC
     * @return void
     */
    public function __construct($DB, $centreon_path, $DBC)
    {
        $this->_DB = $DB;
        $this->_DBC = $DBC;
        $this->resultTest = 0;
        $this->nagiosCFGPath = "$centreon_path/filesGeneration/engine/";
        $this->centreon_path = $centreon_path;
        $this->resultTest = array("warning" => 0, "errors" => 0);
        $this->centcore_pipe = _CENTREON_VARLIB_ . "/centcore.cmd";
    }

    /**
     * Get General option of Centreon
     */
    private function getOptGen()
    {
        $DBRESULT = $this->_DB->query("SELECT * FROM options");
        while ($row = $DBRESULT->fetchRow()) {
            $this->optGen[$row["key"]] = $row["value"];
        }
        $DBRESULT->free();
    }

    /**
     *
     * @param type $poller
     * @return type
     */
    private function testPollerId($poller)
    {
        if (is_numeric($poller)) {
            $sQuery = "SELECT id FROM nagios_server WHERE `id` = '" . $this->_DB->escape($poller) . "'";
        } else {
            $sQuery = "SELECT id FROM nagios_server WHERE `name` = '" . $this->_DB->escape($poller) . "'";
        }

        $DBRESULT = $this->_DB->query($sQuery);
        if ($DBRESULT->numRows() != 0) {
            return;
        } else {
            print "ERROR: Unknown poller...\n";
            $this->getPollerList($this->format);
            exit(1);
        }
    }

    /**
     *
     * @param type $poller
     * @return type
     */
    private function isPollerLocalhost($poller)
    {
        if (is_numeric($poller)) {
            $sQuery = "SELECT localhost FROM nagios_server WHERE `id` = '" . $this->_DB->escape($poller) . "'";
        } else {
            $sQuery = "SELECT localhost FROM nagios_server WHERE `name` = '" . $this->_DB->escape($poller) . "'";
        }

        $DBRESULT = $this->_DB->query($sQuery);
        if ($data = $DBRESULT->fetchRow()) {
            return $data["localhost"];
        } else {
            print "ERROR: Unknown poller...\n";
            $this->getPollerList($this->format);
            exit(1);
        }
    }

    /**
     * Returns monitoring engines for generation purpose
     *
     * @param int $poller
     * @return string
     */
    private function getMonitoringEngine($poller)
    {
        if (is_numeric($poller)) {
            $sQuery = "SELECT monitoring_engine FROM nagios_server WHERE `id` = " . $this->_DB->escape($poller);
        } else {
            $sQuery = "SELECT monitoring_engine FROM nagios_server WHERE `name` = '"
                . $this->_DB->escape($poller) . "'";
        }

        $res = $this->_DB->query($sQuery);

        $row = $res->fetchRow();
        if (isset($row['monitoring_engine'])) {
            return $row['monitoring_engine'];
        }
        return "";
    }

    /**
     *
     * @param type $format
     * @return int
     */
    public function getPollerList($format)
    {
        $DBRESULT = $this->_DB->query("SELECT id,name FROM nagios_server WHERE ns_activate = '1' ORDER BY id");
        if ($format == "xml") {
            print "";
        }
        print "poller_id;name\n";
        while ($data = $DBRESULT->fetchRow()) {
            print $data["id"] . ";" . $data["name"] . "\n";
        }
        $DBRESULT->free();
        return 0;
    }

    /**
     * Restart Centreon broker
     */
    protected function restartCentreonBroker()
    {
        if (file_exists($this->centreon_path . "/www/class/centreonBroker.class.php")) {
            require_once $this->centreon_path . "/www/class/centreonBroker.class.php";
            $brk = new \CentreonBroker($this->_DB);
            $brk->reload();
        }
    }

    /**
     *
     * Reload a server
     * @param unknown_type $variables
     */
    public function pollerReload($variables)
    {
        $return_value = 0;

        if (!isset($variables)) {
            print "Cannot get poller";
            exit(1);
        }

        $poller_id = $this->getPollerId($variables);

        $this->testPollerId($poller_id);

        /*
         * Restart broker
         */
        $this->restartCentreonBroker();

        /*
         * Get Init Script
         */
        $DBRESULT = $this->_DB->query(
            "SELECT id, init_script FROM nagios_server WHERE localhost = '1' AND ns_activate = '1'"
        );
        $serveurs = $DBRESULT->fetchrow();
        $DBRESULT->free();
        (isset($serveurs["init_script"]))
            ? $nagios_init_script = $serveurs["init_script"]
            : $nagios_init_script = "centengine";
        unset($serveurs);

        $DBRESULT = $this->_DB->query(
            "SELECT * FROM `nagios_server` WHERE `id` = '" . $this->_DB->escape($poller_id) . "'  LIMIT 1"
        );
        $host = $DBRESULT->fetchRow();
        $DBRESULT->free();

        $msg_restart = "";
        if (isset($host['localhost']) && $host['localhost'] == 1) {
            $msg_restart = exec("sudo service " . $nagios_init_script . " reload", $stdout, $return_code);
        } else {
            exec("echo 'RELOAD:" . $host["id"] . "' >> " . $this->centcore_pipe, $stdout, $return_code);
            $msg_restart .= _("OK: A reload signal has been sent to '" . $host["name"] . "'");
        }
        print $msg_restart . "\n";
        $this->_DB->query(
            "UPDATE `nagios_server` SET `last_restart` = '" . time()
            . "' WHERE `id` = '" . $this->_DB->escape($poller_id) . "' LIMIT 1"
        );
        return $return_code;
    }

    /**
     * Execute post generation command
     *
     * @param int $pollerId
     * @throws CentreonClapiException
     */
    public function execCmd($pollerId)
    {
        $this->testPollerId($pollerId);

        $instanceClassFile = $this->centreon_path . 'www/class/centreonInstance.class.php';
        if (!is_file($instanceClassFile)) {
            throw new CentreonClapiException('This action is not available in the version of Centreon you are using');
        }
        require_once $instanceClassFile;

        $pollerId = $this->getPollerId($pollerId);

        $instanceObj = new \CentreonInstance($this->_DB);
        $cmds = $instanceObj->getCommandData($pollerId);
        $result = 0;
        foreach ($cmds as $cmd) {
            echo "Executing command {$cmd['command_name']}... ";
            exec($cmd['command_line'], $output, $cmdResult);
            if ($cmdResult) {
                $resultStr = "Error: {$output}";
                $result += $cmdResult;
            } else {
                $resultStr = "OK";
            }
            echo "{$resultStr}\n";
        }
        // if result > 0, return 1, return 0 otherwise
        return ($result ? 1 : 0);
    }

    /**
     *
     * Restart a serveur
     * @param unknown_type $variables
     */
    public function pollerRestart($variables)
    {
        if (!isset($variables)) {
            print "Cannot get poller";
            exit(1);
        }

        $this->testPollerId($variables);

        $poller_id = $this->getPollerId($variables);

        /*
         * Restart broker
         */
        $this->restartCentreonBroker();

        /*
         * Get Init Script
         */
        $DBRESULT = $this->_DB->query(
            "SELECT id, init_script FROM nagios_server WHERE localhost = '1' AND ns_activate = '1'"
        );
        $serveurs = $DBRESULT->fetchrow();
        $DBRESULT->free();
        (isset($serveurs["init_script"]))
            ? $nagios_init_script = $serveurs["init_script"]
            : $nagios_init_script = "centengine";
        unset($serveurs);

        $DBRESULT = $this->_DB->query(
            "SELECT * FROM `nagios_server` WHERE `id` = '" . $this->_DB->escape($poller_id) . "'  LIMIT 1"
        );
        $host = $DBRESULT->fetchRow();
        $DBRESULT->free();

        $msg_restart = "";
        if (isset($host['localhost']) && $host['localhost'] == 1) {
            $msg_restart = exec(
                escapeshellcmd("sudo service " . $nagios_init_script . " restart"),
                $lines,
                $return_code
            );
        } else {
            exec("echo 'RESTART:" . $host["id"] . "' >> " . $this->centcore_pipe, $stdout, $return_code);
            $msg_restart = _("OK: A restart signal has been sent to '" . $host["name"] . "'");
        }
        print $msg_restart . "\n";
        $DBRESULT = $this->_DB->query(
            "UPDATE `nagios_server` SET `last_restart` = '" . time()
            . "' WHERE `id` = '" . $this->_DB->escape($poller_id) . "' LIMIT 1"
        );
        return $return_code;
    }

    /**
     *
     * Test poller configuration
     * @param unknown_type $format
     * @param unknown_type $variables
     */
    public function pollerTest($format, $variables)
    {
        if (!isset($variables)) {
            print "Cannot get poller";
            exit(1);
        }

        $this->testPollerId($variables);

        $idPoller = $this->getPollerId($variables);

        /**
         * Get Nagios Bin
         */
        $DBRESULT_Servers = $this->_DB->query(
            "SELECT `nagios_bin` FROM `nagios_server` WHERE `localhost` = '1' ORDER BY `ns_activate` DESC LIMIT 1"
        );
        $nagios_bin = $DBRESULT_Servers->fetchRow();
        $DBRESULT_Servers->free();

        /*
         * Launch test command
         */
        if (isset($nagios_bin["nagios_bin"])) {
            exec(
                escapeshellcmd(
                    $nagios_bin["nagios_bin"] . " -v "
                    . $this->nagiosCFGPath . $idPoller . "/centengine.DEBUG"
                ),
                $lines,
                $return_code
            );
        } else {
            throw new CentreonClapiException("Can't find engine binary");
        }

        $msg_debug = "";
        foreach ($lines as $line) {
            if (strncmp($line, "Processing object config file", strlen("Processing object config file"))
                && strncmp($line, "Website: http://www.nagios.org", strlen("Website: http://www.nagios.org"))
            ) {
                $msg_debug .= $line . "\n";

                /**
                 * Detect Errors
                 */
                if (preg_match("/Total Warnings: ([0-9])*/", $line, $matches)) {
                    if (isset($matches[1])) {
                        $this->resultTest["warning"] = $matches[1];
                    }
                }
                if (preg_match("/Total Errors: ([0-9])*/", $line, $matches)) {
                    if (isset($matches[1])) {
                        $this->resultTest["errors"] = $matches[1];
                    }
                }
                if (preg_match("/^Error:/", $line, $matches)) {
                    $this->resultTest["errors"]++;
                }
                if (preg_match("/^Errors:/", $line, $matches)) {
                    $this->resultTest["errors"]++;
                }
            }
        }
        if ($this->resultTest["errors"] != 0) {
            print "Error: Centreon Poller $variables cannot restart. configuration broker. Please see debug bellow :\n";
            print "-----------------------------------------------------------"
                . "----------------------------------------\n";
            print $msg_debug . "\n";
            print "---------------------------------------------------"
                . "------------------------------------------------\n";
        } elseif ($this->resultTest["warning"] != 0) {
            print "Warning: Centreon Poller $variables can restart but "
                . "configuration is not optimal. Please see debug bellow :\n";
            print "-----------------------------------------------"
                . "----------------------------------------------------\n";
            print $msg_debug . "\n";
            print "------------------------------------------------"
                . "---------------------------------------------------\n";
        } elseif ($return_code) {
            print implode("\n", $lines);
        } else {
            print "OK: Centreon Poller $variables can restart without problem...\n";
        }
        return $return_code;
    }

    /**
     *
     * Generate configuration files for a specific poller
     * @param $variables
     * @param $login
     * @param $password
     */
    public function pollerGenerate($variables, $login, $password)
    {
        $config_generate = new \Generate();

        $this->testPollerId($variables);

        $poller_id = $this->getPollerId($variables);

        $config_generate->configPollerFromId($poller_id, $login);

        /* Change files owner */
        $apacheUser = $this->getApacheUser();

        $setFilesOwner = 1;
        if ($apacheUser != "") {
            /* Change engine Path mod */
            chown($this->centreon_path . "/filesGeneration/engine/$poller_id", $apacheUser);
            chgrp($this->centreon_path . "/filesGeneration/engine/$poller_id", $apacheUser);

            foreach (glob($this->centreon_path . "/filesGeneration/engine/$poller_id/*.cfg") as $file) {
                chown($file, $apacheUser);
                chgrp($file, $apacheUser);
            }

            foreach (glob($this->centreon_path . "/filesGeneration/engine/$poller_id/*.DEBUG") as $file) {
                chown($file, $apacheUser);
                chgrp($file, $apacheUser);
            }

            /* Change broker Path mod */
            chown($this->centreon_path . "/filesGeneration/broker/$poller_id", $apacheUser);
            chgrp($this->centreon_path . "/filesGeneration/broker/$poller_id", $apacheUser);

            foreach (glob($this->centreon_path
                . "/filesGeneration/broker/$poller_id/*.{xml,cfg}", GLOB_BRACE) as $file) {
                chown($file, $apacheUser);
                chgrp($file, $apacheUser);
            }
        } else {
            $setFilesOwner = 0;
        }

        if ($setFilesOwner == 0) {
            print "We can set configuration file owner after the generation. \n";
            print "Please check that files in the followings directory are writable by apache user : "
                . $this->centreon_path . "/filesGeneration/engine/$poller_id/\n";
            print "Please check that files in the followings directory are writable by apache user : "
                . $this->centreon_path . "/filesGeneration/broker/$poller_id/\n";
        }

        print "Configuration files generated for poller '" . $variables . "'\n";
        return 0;
    }

    /**
     *
     * Move configuration files to servers
     * @param unknown_type $variables
     */
    public function cfgMove($variables)
    {
        global $pearDB, $pearDBO;

        $pearDB = $this->_DB;
        $pearDBO = $this->_DBC;

        require_once _CENTREON_PATH_ . "www/include/configuration/configGenerate/DB-Func.php";
        if (!isset($variables)) {
            print "Cannot get poller";
            exit(1);
        }

        $return = 0;

        /**
         * Check poller existance
         */
        $this->testPollerId($variables);

        $poller_id = $this->getPollerId($variables);

        /* Get Apache user name */
        $apacheUser = $this->getApacheUser();

        /**
         * Move files.
         */
        $DBRESULT_Servers = $this->_DB->query(
            "SELECT `cfg_dir` FROM `cfg_nagios` WHERE `nagios_server_id` = '"
            . $this->_DB->escape($poller_id) . "' LIMIT 1"
        );
        $Nagioscfg = $DBRESULT_Servers->fetchRow();
        $DBRESULT_Servers->free();

        $DBRESULT_Servers = $this->_DB->query(
            "SELECT * FROM `nagios_server` WHERE `id` = '"
            . $this->_DB->escape($poller_id) . "'  LIMIT 1"
        );
        $host = $DBRESULT_Servers->fetchRow();
        $DBRESULT_Servers->free();
        if (isset($host['localhost']) && $host['localhost'] == 1) {
            $msg_copy = "";
            foreach (glob($this->nagiosCFGPath . '/' . $poller_id . "/*.cfg") as $filename) {
                $bool = @copy($filename, $Nagioscfg["cfg_dir"] . "/" . basename($filename));
                $filename = array_pop(explode("/", $filename));
                if (!$bool) {
                    $msg_copy .= $this->display_copying_file($filename, " - " . _("movement") . " KO");
                    $return = 1;
                }
            }

            /* Change files owner */
            if ($apacheUser != "") {
                foreach (glob($Nagioscfg["cfg_dir"] . "/*.cfg") as $file) {
                    chown($file, $apacheUser);
                    chgrp($file, $apacheUser);
                }
                foreach (glob($Nagioscfg["cfg_dir"] . "/*.DEBUG") as $file) {
                    chown($file, $apacheUser);
                    chgrp($file, $apacheUser);
                }
            } else {
                print "Please check that files in the followings directory are writable by apache user : "
                    . $Nagioscfg["cfg_dir"] . "\n";
            }

            /*
             * Centreon Broker configuration
             */
            $centreonBrokerPath = $this->centreon_path . "/filesGeneration/broker/";
            $listBrokerFile = glob($centreonBrokerPath . $host['id'] . "/*.{xml,cfg}", GLOB_BRACE);
            if (count($listBrokerFile) > 0) {
                $centreonBrokerDirCfg = getCentreonBrokerDirCfg($host['id']);
                if (!is_null($centreonBrokerDirCfg)) {
                    if (!is_dir($centreonBrokerDirCfg)) {
                        if (!mkdir($centreonBrokerDirCfg, 0755)) {
                            throw new Exception(
                                sprintf(
                                    _("Centreon Broker's configuration directory '%s' does not exist and could not be "
                                        . "created for monitoring engine '%s'. Please check it's path or create it"),
                                    $centreonBrokerDirCfg,
                                    $host['name']
                                )
                            );
                        }
                    }
                    foreach ($listBrokerFile as $fileCfg) {
                        $succeded = @copy($fileCfg, rtrim($centreonBrokerDirCfg, "/") . '/' . basename($fileCfg));
                        if (!$succeded) {
                            throw new Exception(
                                sprintf(
                                    _("Could not write to Centreon Broker's configuration file '%s' for monitoring "
                                        . "engine '%s'. Please add writing permissions for the webserver's user"),
                                    basename($fileCfg),
                                    $host['name']
                                )
                            );
                        }
                    }
                }

                /* Change files owner */
                if ($apacheUser != "") {
                    foreach (glob(rtrim($centreonBrokerDirCfg, "/") . "/" . "/*.{xml,cfg}", GLOB_BRACE) as $file) {
                        chown($file, $apacheUser);
                        chgrp($file, $apacheUser);
                    }
                } else {
                    print "Please check that files in the followings directory are writable by apache user : "
                        . rtrim($centreonBrokerDirCfg, "/") . "/\n";
                }
            }

            if (strlen($msg_copy) == 0) {
                $msg_copy .= _("OK: All configuration files copied with success.");
            }
        } else {
            exec("echo 'SENDCFGFILE:" . $host['id'] . "' >> " . $this->centcore_pipe, $stdout, $return);
            if (!isset($msg_copy)) {
                $msg_copy = "";
            }
            $msg_copy .= _(
                "OK: All configuration will be send to '"
                . $host['name'] . "' by centcore in several minutes."
            );
        }
        print $msg_copy . "\n";
        return $return;
    }

    /**
     * Get apache user to set file access
     *
     * @return string
     */
    public function getApacheUser()
    {
        /* Change files owner */
        $setFilesOwner = 1;
        $installFile = "/etc/centreon/instCentWeb.conf";

        if (file_exists($installFile)) {
            $stream = file_get_contents($installFile);
            $lines = preg_split("/\n/", $stream);
            foreach ($lines as $line) {
                if (preg_match('/WEB\_USER\=([a-zA-Z\_\-]*)/', $line, $tabUser)) {
                    if (isset($tabUser[1])) {
                        return $tabUser[1];
                    } else {
                        return "";
                    }
                }
            }
        } else {
            return "";
        }
    }

    /**
     * Send Trap configuration files to poller
     *
     * @param int $pollerId
     * @return void
     * @throws CentreonClapiException
     */
    public function sendTrapCfg($pollerId = null)
    {
        if (is_null($pollerId)) {
            throw new CentreonClapiException(self::MISSING_POLLER_ID);
        }
        $this->testPollerId($pollerId);
        $centreonDir = CentreonUtils::getCentreonDir();
        $pearDB = new \CentreonDB('centreon');
        $res = $pearDB->query("SELECT snmp_trapd_path_conf FROM nagios_server WHERE id = '" . $pollerId . "'");
        $row = $res->fetchRow();
        $trapdPath = $row['snmp_trapd_path_conf'];
        if (!is_dir("{$trapdPath}/{$pollerId}")) {
            mkdir("{$trapdPath}/{$pollerId}");
        }
        $filename = "{$trapdPath}/{$pollerId}/centreontrapd.sdb";
        passthru("$centreonDir/bin/generateSqlLite '{$pollerId}' '{$filename}' 2>&1");
        exec("echo 'SYNCTRAP:" . $pollerId . "' >> " . $this->centcore_pipe, $stdout, $return);
        return $return;
    }

    /**
     *
     * Display Copying files
     * @param unknown_type $filename
     * @param unknown_type $status
     * @return string
     */
    private function display_copying_file($filename = null, $status)
    {
        if (!isset($filename)) {
            return;
        }
        $str = "- " . $filename . " -> " . $status . "\n";
        return $str;
    }

    /**
     *
     * @param type $poller
     * @return type
     */
    private function getPollerId($poller)
    {
        if (is_numeric($poller)) {
            return $poller;
        }

        $sQuery = "SELECT id FROM nagios_server WHERE `name` = '" . $this->_DB->escape($poller) . "'";
        $DBRESULT = $this->_DB->query($sQuery);
        if ($DBRESULT->numRows() > 0) {
            $row = $DBRESULT->fetchRow();
            return $row['id'];
        } else {
            throw new CentreonClapiException(self::UNKNOWN_POLLER_ID);
        }
    }

    public function getPollerState()
    {
        $pollerState = array();

        $this->_DBC = new \CentreonDB('centstorage');

        $DBRESULT = $this->_DBC->query("SELECT instance_id, running, name FROM instances");
        while ($row = $DBRESULT->fetchRow()) {
            $pollerState[$row['instance_id']] = $row['running'];
        }
        return $pollerState;
    }
}
