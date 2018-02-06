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

/*
 * Need Centreon Configuration file
 */
require_once realpath(dirname(__FILE__) . "/../../config/centreon.config.php");
require_once _CENTREON_PATH_ . '/www/autoloader.php';

/** * ****************************
 * Class for XML/Ajax request
 *
 */
class CentreonXMLBGRequest {
    /*
     * Objects
     */

    var $DB;
    var $DBC;
    var $XML;
    var $GMT;
    var $hostObj;
    var $serviceObj;
    var $monObj;
    var $access;
    var $session_id;
    var $broker;

    /*
     * Variables
     */
    var $buffer;
    var $debug;
    var $compress;
    var $header;
    var $is_admin;
    var $user_id;
    var $grouplist;
    var $grouplistStr;
    var $general_opt;
    var $class;
    var $stateType;
    var $statusHost;
    var $statusService;
    var $colorHost;
    var $colorHostInService;
    var $colorService;
    var $en;
    var $stateTypeFull;

    var $backgroundHost;
    var $backgroundService;

    /*
     * Filters
     */
    var $defaultPoller;
    var $defaultHostgroups;
    var $defaultServicegroups;
    var $defaultCriticality = 0;

    /*
     * Class constructor
     *
     * <code>
     * $obj = new CentreonBGRequest($_GET["session_id"], 1, 1, 0, 1);
     * </code>
     *
     * $session_id 	char 	session id
     * $dbneeds		bool 	flag for enable ndo connexion
     * $headType	bool 	send XML header
     * $debug		bool 	debug flag.
     * $compress	bool 	compress enable.
     */

    public function __construct($session_id, $dbNeeds, $headerType, $debug, $compress = null, $fullVersion = 1) {
        if (!isset($debug)) {
            $this->debug = 0;
        }

        (!isset($headerType)) ? $this->header = 1 : $this->header = $headerType;
        (!isset($compress)) ? $this->compress = 1 : $this->compress = $compress;

        if (!isset($session_id)) {
            print "Your might check your session id";
            exit(1);
        } else {
            $this->session_id = htmlentities($session_id, ENT_QUOTES, "UTF-8");
        }

        /*
         * Enable Database Connexions
         */
        $this->DB = new CentreonDB();
        $this->DBC = new CentreonDB("centstorage");

        /*
         * Init Objects
         */
        $this->hostObj = new CentreonHost($this->DB);
        $this->serviceObj = new CentreonService($this->DB);

        /*
         * Init Object Monitoring
         */
        $this->monObj = new CentreonMonitoring($this->DB);

        if ($fullVersion) {
            /*
             * Timezone management
             */
            $this->GMT = new CentreonGMT($this->DB);
            $this->GMT->getMyGMTFromSession($this->session_id, $this->DB);
        }
        
        /*
         * XML class
         */
        $this->XML = new CentreonXML();

        /*
         * ACL init
         */
        $this->getUserIdFromSID();
        $this->isUserAdmin();
        $this->access = new CentreonACL($this->user_id, $this->is_admin);
        $this->grouplist = $this->access->getAccessGroups();
        $this->grouplistStr = $this->access->getAccessGroupsString();

        /*
         * Init Color table
         */
        $this->getStatusColor();

        /*
         * Init class
         */
        $this->classLine = "list_one";

        /*
         * Init Tables
         */
        $this->en = array("0" => _("No"), "1" => _("Yes"));
        $this->stateType = array("1" => "H", "0" => "S");
        $this->stateTypeFull = array("1" => "HARD", "0" => "SOFT");
        $this->statusHost = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE", "4" => "PENDING");
        $this->statusService = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
        $this->colorHost = array(0 => 'host_up', 1 => 'host_down', 2 => 'host_unreachable', 4 => 'pending');
        $this->colorService = array(0 => 'service_ok', 1 => 'service_warning', 2 => 'service_critical', 3 => 'service_unknown', 4 => 'pending');
        
        $this->backgroundHost = array(0 => '#88b917', 1 => '#e00b3d', 2 => '#818185', 4 => '#2ad1d4');
        $this->backgroundService = array(0 => '#88b917', 1 => '#ff9a13', 2 => '#e00b3d', 3 => '#bcbdc0', 4 => '#2ad1d4');
        
        $this->colorHostInService = array(0 => "normal", 1 => "#FD8B46", 2 => "normal", 4 => "normal");
    }

    /*
     * Update session table for this user.
     * 	=> value used for logout session
     */

    public function reloadSession() {
        $DBRESULT2 = $this->DB->query("UPDATE `session` SET `last_reload` = '" . time() . "', `ip_address` = '" . $_SERVER["REMOTE_ADDR"] . "' WHERE `session_id` = '" . $this->session_id . "'");
    }

    /*
     * Check if user is admin
     */

    private function isUserAdmin() {
        $DBRESULT = $this->DB->query("SELECT contact_admin, contact_id FROM contact WHERE contact.contact_id = '" . CentreonDB::escape($this->user_id) . "' LIMIT 1");
        $admin = $DBRESULT->fetchRow();
        $DBRESULT->free();
        if ($admin["contact_admin"])
            $this->is_admin = 1;
        else
            $this->is_admin = 0;
    }

    /*
     * Get user id from session_id
     */

    protected function getUserIdFromSID() {
        $DBRESULT = $this->DB->query("SELECT user_id FROM session WHERE session_id = '" . CentreonDB::escape($this->session_id) . "' LIMIT 1");
        $admin = $DBRESULT->fetchRow();
        unset($DBRESULT);
        if (isset($admin["user_id"])) {
            $this->user_id = $admin["user_id"];
        }
    }

    /**
     * Decode Function
     */
    private function myDecode($arg) {
        return html_entity_decode($arg, ENT_QUOTES, "UTF-8");
    }

    /*
     * Get Status Color
     */

    protected function getStatusColor() {
        $this->general_opt = array();
        $DBRESULT = $this->DB->query("SELECT * FROM `options` WHERE `key` LIKE 'color%'");
        while ($c = $DBRESULT->fetchRow()) {
            $this->general_opt[$c["key"]] = $this->myDecode($c["value"]);
        }
        $DBRESULT->free();
        unset($c);
    }

    /*
     * Send headers information for web server
     */

    public function header() {
        /* Force no encoding compress */
        $encoding = false;

        header('Content-Type: text/xml');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: no-cache, must-revalidate');
        if ($this->compress && $encoding) {
            header('Content-Encoding: ' . $encoding);
        }
    }

    public function getNextLineClass() {
        if ($this->classLine == "list_one") {
            $this->classLine = "list_two";
        } else {
            $this->classLine = "list_one";
        }
        return $this->classLine;
    }

    public function getDefaultFilters() {
        $this->defaultPoller = -1;
        $this->defaultHostgroups = NULL;
        $this->defaultServicegroups = NULL;
        if (isset($_SESSION['monitoring_default_hostgroups'])) {
            $this->defaultHostgroups = $_SESSION['monitoring_default_hostgroups'];
        }
        if (isset($_SESSION['monitoring_default_servicegroups'])) {
            $this->defaultServicegroups = $_SESSION['monitoring_default_servicegroups'];
       }
        if (isset($_SESSION['monitoring_default_poller'])) {
            $this->defaultPoller = $_SESSION['monitoring_default_poller'];
        }
        if (isset($_SESSION['criticality_id'])) {
            $this->defaultCriticality = $_SESSION['criticality_id'];
        }
    }

    public function setInstanceHistory($instance) {
        $_SESSION['monitoring_default_poller'] = $instance;
    }

    public function setHostGroupsHistory($hg) {
        $_SESSION['monitoring_default_hostgroups'] = $hg;
    }

	public function setServiceGroupsHistory($sg) {
        $_SESSION['monitoring_default_servicegroups'] = sg;
    }

    public function setCriticality($criticality) {
        $_SESSION['criticality_id'] = $criticality;
    }

    public function checkArgument($name, $tab, $defaultValue) {
        if (isset($name) && isset($tab)) {
            if (isset($tab[$name])) {
                if ($name == 'num' && $tab[$name] < 0) {
                    $tab[$name] = 0;
                }
                $value = htmlspecialchars($tab[$name], ENT_QUOTES, 'utf-8');
                return CentreonDB::escape($value);
            } else {
                return CentreonDB::escape($defaultValue);
            }
        }
    }

    public function prepareObjectName($name) {
        $name = str_replace("/", "#S#", $name);
        $name = str_replace("\\", "#BS#", $name);
        return $name;
    }

}
