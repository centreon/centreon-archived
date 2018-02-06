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

class CentreonSession
{
    /*
	 * Constructor class
	 *
	 * @access public
	 * @return 	object	object session
	 */
    public function __construct()
    {

    }

    public function start($flag = 0)
    {
        session_start();
        if ($flag) {
            session_write_close();
        }
    }

    public function stop()
    {
        session_unset();
        session_destroy();
    }

    public function restart()
    {
        self::stop();
        self::start();
        session_regenerate_id(true);
    }

    public function s_unset()
    {
        session_unset();
    }

    public function unregisterVar($registerVar)
    {
        unset($_SESSION[$registerVar]);
    }

    public function registerVar($registerVar)
    {
        if (!isset($_SESSION[$registerVar])) {
            $_SESSION[$registerVar] = $$registerVar;
        }
    }

    public function checkSession($sessionId, $pearDB)
    {
        $sessionId = str_replace(array('_', '%'), array('', ''), $sessionId);
        $DBRESULT = $pearDB->query(
            "SELECT id FROM session WHERE `session_id` = '" . htmlentities(trim($sessionId), ENT_QUOTES, "UTF-8") . "'"
        );
        if ($DBRESULT->numRows()) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Update session to keep alive
     *
     * @param \CentreonDB $pearDB
     */
    public function updateSession($pearDB)
    {
        session_start();

        /* Update last_reload parameter */
        $query = 'UPDATE `session` '
            . 'SET `last_reload` = "' . time() . '", '
            . '`ip_address` = "' . $_SERVER["REMOTE_ADDR"] . '" '
            . 'WHERE `session_id` = "' . session_id() . '" ';
        $pearDB->query($query);
    }

    public static function getUser($sessionId, $pearDB)
    {
        $sessionId = str_replace(array('_', '%'), array('', ''), $sessionId);
        $DBRESULT = $pearDB->query(
            "SELECT user_id FROM session
                WHERE `session_id` = '".htmlentities(trim($sessionId), ENT_QUOTES, "UTF-8")."'"
        );
        $row = $DBRESULT->fetchRow();
        if (!$row) {
            return 0;
        }
        return $row['user_id'];
    }
}
