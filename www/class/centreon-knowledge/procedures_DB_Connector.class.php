<?php

/*
 * Copyright 2005-2009 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

class procedures_DB_Connector
{
    private $db_type = "mysql";
    private $retry;
    private $privatePearDB;
    private $options;
    private $log;
    public $debug;

    /**
     * procedures_DB_Connector constructor.
     * @param int $retry
     * @param $db_name
     * @param $db_user
     * @param $db_host
     * @param $db_password
     */
    public function __construct($retry = 3, $db_name = '', $db_user = '', $db_host = '', $db_password = '')
    {
        $this->retry = $retry;
        $this->options = array('debug' => 2, 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE);
        $this->log = new CentreonLog();
        $this->connect($db_name, $db_user, $db_host, $db_password);
        $this->debug = 0;
    }

    private function displayConnectionErrorPage()
    {
        echo "<center><b>" .
            _(
                "Connection to Wiki database failed, please contact your administrator " .
                "or read the Centreon online documentation to configure wiki access"
            ) .
            "</b></center>";
        exit;
    }

    /**
     * @param $db_name
     * @param $db_user
     * @param $db_host
     * @param $db_password
     * @return array
     */
    public function connect($db_name, $db_user, $db_host, $db_password)
    {
        $separator = explode(':', $db_host);
        $host = $separator[0];
        $port = isset($separator[1]) ? $separator[1] : 3306;

        $dsn = $this->db_type . ':dbname=' . $db_name . ';host=' . $host . ';port=' . $port;

        try {
            $this->privatePearDB = new PDO($dsn, $db_user, $db_password);
            $outcome = true;
            $message = _('Connection Successful');
        } catch (PDOException $e) {
            $outcome = false;
            $message = $e->getMessage();
        }

        return array(
            'outcome' => $outcome,
            'message' => $message
        );
    }

    /**
     * Disconnection
     */
    public function disconnect()
    {
        $this->privatePearDB->disconnect();
    }

    public function toString()
    {
        return $this->privatePearDB->toString();
    }

    /**
     * @param null $query_string
     * @return mixed
     */
    public function query($query_string = null)
    {
        if ($this->debug) {
            $query = str_replace("`", "", $query_string);
            $query = str_replace("'", "\'", $query);
            $query = str_replace("*", "\*", $query);
            exec("echo '$query' >> " . _CENTREON_LOG_ . "/procedure.log");
        }

        $DBRES = null;
        try {
            $DBRES = $this->privatePearDB->query($query_string);
        } catch (\PDOException $e) {
            $this->log->insertLog(2, $e->getMessage() . " QUERY : " . $query_string);
        }

        return $DBRES;
    }
}
