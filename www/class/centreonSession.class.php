<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

    public static function start($flag = 0)
    {
        session_start();
        if ($flag) {
            session_write_close();
        }
    }

    public static function stop()
    {
        // destroy the session
        session_unset();
        session_destroy();
    }

    public static function restart()
    {
        static::stop();
        self::start();
        // regenerate the session id value
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

    /**
     * Check user session status
     *
     * @param string $sessionId Session id to check
     * @param CentreonDB $db
     * @return int
     * @throws PDOException
     */
    public static function checkSession($sessionId, CentreonDB $db)
    {
        if (empty($sessionId)) {
            return 0;
        }
        $prepare = $db->prepare('SELECT COUNT(*) AS total FROM session WHERE `session_id` = :session_id');
        $prepare->bindValue(':session_id', $sessionId, \PDO::PARAM_STR);
        $prepare->execute();
        $total = (int) $prepare->fetch(\PDO::FETCH_ASSOC)['total'];
        return ($total > 0) ? 1 : 0;
    }

    /**
     * Update session to keep alive
     *
     * @param \CentreonDB $pearDB
     * @return bool If the session is updated or not
     */
    public function updateSession($pearDB) : bool
    {
        $sessionUpdated = false;

        session_start();
        $sessionId = session_id();

        if (self::checkSession($sessionId, $pearDB) === 1) {
            try {
                /* Update last_reload parameter */
                $query = 'UPDATE `session` '
                    . 'SET `last_reload` = "' . time() . '", '
                    . '`ip_address` = "' . $_SERVER["REMOTE_ADDR"] . '" '
                    . 'WHERE `session_id` = "' . $sessionId . '" ';
                $pearDB->query($query);
                $sessionUpdated = true; // return true if session is properly updated
            } catch (\PDOException $e) {
                $sessionUpdated = false; // return false if session is not properly updated in database
            }
        } else {
            $sessionUpdated = false; // return false if session does not exist
        }

        return $sessionUpdated;
    }

    public static function getUser($sessionId, $pearDB)
    {
        $sessionId = str_replace(array('_', '%'), array('', ''), $sessionId);
        $DBRESULT = $pearDB->query(
            "SELECT user_id FROM session
                WHERE `session_id` = '" . htmlentities(trim($sessionId), ENT_QUOTES, "UTF-8") . "'"
        );
        $row = $DBRESULT->fetchRow();
        if (!$row) {
            return 0;
        }
        return $row['user_id'];
    }
}
