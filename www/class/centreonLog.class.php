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

class CentreonUserLog
{

    private static $instance;
    private $errorType;
    private $uid;
    private $path;

    /*
     * Constructor
     */

    public function __construct($uid, $pearDB)
    {

        $this->uid = $uid;
        $this->errorType = array();

        /*
         * Get Log directory path
         */
        $DBRESULT = $pearDB->query("SELECT * FROM `options` WHERE `key` = 'debug_path'");
        while ($res = $DBRESULT->fetchRow()) {
            $this->ldapInfos[$res["key"]] = $res["value"];
        }
        $DBRESULT->free();

        /*
         * Init log Directory
         */
        if (isset($optGen["debug_path"]) && $optGen["debug_path"] != "") {
            $this->path = $optGen["debug_path"];
        } else {
            $this->path = _CENTREON_LOG_;
        }

        $this->errorType[1] = $this->path . "/login.log";
        $this->errorType[2] = $this->path . "/sql-error.log";
        $this->errorType[3] = $this->path . "/ldap.log";
    }

    /*
     * Function for writing logs
     */

    public function insertLog($id, $str, $print = 0, $page = 0, $option = 0)
    {
        /*
         * Construct alerte message
         */
        $string = date("Y-m-d H:i") . "|" . $this->uid . "|$page|$option|$str";

        /*
         * Display error on Standard exit
         */
        if ($print) {
            print $str;
        }

        /*
         * Replace special char
         */
        $string = str_replace("`", "", $string);
        $string = str_replace("*", "\*", $string);

        /*
         * Write Error in log file.
         */
        file_put_contents($this->errorType[$id], $string . "\n", FILE_APPEND);
    }

    public function setUID($uid)
    {
        $this->uid = $uid;
    }

    /**
     * Singleton
     *
     * @param int $uid The user id
     * @return CentreonUserLog
     */
    public static function singleton($uid = 0)
    {
        if (is_null(self::$instance)) {
            self::$instance = new CentreonUserLog($uid, CentreonDB::factory('centreon'));
        }
        return self::$intance;
    }
}

class CentreonLog
{

    private $errorType;
    private $path;

    /*
     * Constructor
     */

    public function __construct($customLogs = array())
    {
        $this->errorType = array();

        /*
         * Init log Directory
         */
        $this->path = _CENTREON_LOG_;

        $this->errorType[1] = $this->path . "/login.log";
        $this->errorType[2] = $this->path . "/sql-error.log";
        $this->errorType[3] = $this->path . "/ldap.log";

        foreach ($customLogs as $key => $value) {
            if (!preg_match('@' . $this->path . '@', $value)) {
                $value = $this->path . '/' . $value;
            }
            $this->errorType[$key] = $value;
        }
    }

    /*
     * Function for writing logs
     */

    public function insertLog($id, $str, $print = 0, $page = 0, $option = 0)
    {
        /*
         * Construct alerte message
         */
        $string = date("Y-m-d H:i") . "|$page|$option|$str";

        /*
         * Display error on Standard exit
         */
        if ($print) {
            print $str;
        }


        /*
         * Replace special char
         */
        $string = str_replace("`", "", $string);
        $string = str_replace("*", "\*", $string);


        /*
         * Write Error in log file.
         */
        file_put_contents($this->errorType[$id], $string . "\n", FILE_APPEND);
    }
}
