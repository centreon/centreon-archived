<?php
/*
 * Copyright 2005-2014 MERETHIS
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
namespace Centreon\Internal;

use \Centreon\Internal\Di;

/**
 * Class for manage session
 *
 * The session manager use the application cache for store data
 *
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Core
 */
class Session
{
    private $savePath;
    private $useCache = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        $cache = Di::getDefault()->get('cache');
        if ($cache->getAdapter() instanceof \Desarrolla2\Cache\Adapter\NotCache) {
            $this->useCache = false;
        }
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
        register_shutdown_function('session_write_close');
    }

    /**
     * Open the user session
     *
     * @param $savePath string The path for save session file
     * @param $sessionName string The session name
     * @return bool
     */
    public function open($savePath, $sessionName)
    {
        $this->savePath = $savePath;
        return true;
    }

    /**
     * Close the user session
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Read the data from the session storage
     *
     * @param $sessionId string The session ID
     * @return bool
     */
    public function read($sessionId)
    {
        if ($this->useCache) {
            try {
                $sesssionData = Di::getDefault()->get('cache')->get('session::' . $sessionId);
            } catch (\Exception $e) {
                return "";
            }
        } else {
            if (false === file_exists($this->savePath . '/sess_' . $sessionId)) {
                return "";
            }
            $sesssionData = file_get_contents($this->savePath . '/sess_' . $sessionId);
        }
        if (is_null($sesssionData)) {
            return "";
        }
        return $sesssionData;
    }

    /**
     * Write the data to the session storage
     *
     * @param $sessionId string The session ID
     * @return bool
     */
    public function write($sessionId, $data)
    {
        if (isset($_SESSION['user'])) {
            /* Update session in db */
            $dbconn = Di::getDefault()->get('db_centreon');
            $router = Di::getDefault()->get('router');
            $route = $router->request()->pathname();
            try {
                $stmt = $dbconn->prepare(
                    'UPDATE `session` SET
                        last_reload = :now,
                        route = :route
                        WHERE session_id = :session_id'
                );
                $stmt->bindParam(':now', time(), \PDO::PARAM_INT);
                $stmt->bindParam(':route', $route, \PDO::PARAM_STR);
                $stmt->bindParam(':session_id', $sessionId, \PDO::PARAM_STR);
                $stmt->execute();
            } catch (\Exception $e) {
            }
        }
        if ($this->useCache) {
            try {
                Di::getDefault()->get('cache')->set('session::' . $sessionId, $data, session_cache_expire());
            } catch (\Exception $e) {
                return false;
            }
        } else {
            $ret = file_put_contents($this->savePath . '/sess_' . $sessionId, $data, LOCK_EX);
            if ($ret === false) {
                return false;
            }
            chmod($this->savePath . '/sess_' . $sessionId, 0600);
        }
        return true;
    }

    /**
     * Destroy a session
     *
     * @param $sessionId string The session ID
     * @return bool
     */
    public function destroy($sessionId)
    {
        if ($this->useCache) {
            try {
                Di::getDefault()->get('cache')->delete('session::' . $sessionId);
            } catch (\Exception $e) {
                return false;
            }
        } else {
            if (file_exists($this->savePath . '/sess_' . $sessionId)) {
                return unlink($this->savePath . '/sess_' . $sessionId);
            }
        }
        return true;
    }

    /**
     * Purge old sessions
     *
     * @param $maxlifetime int The time life of sessions
     * @return bool
     */
    public function gc($maxlifetime)
    {
        if (false === $this->useCache) {
            foreach (glob($this->savePath . '/sess_*') as $file) {
                if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                    unlink($file);
                }
            }
        }
        return true;
    }

    /**
     * Initialize the user session in database
     *
     * @param $userId int The user ID
     */
    public static function init($userId)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        $router = Di::getDefault()->get('router');
        $route = $router->request()->pathname();
        $ipAddress = $router->request()->ip();
        try {
            $stmt = $dbconn->prepare(
                'INSERT INTO `session`
                (session_id, user_id, session_start_time, last_reload, ip_address, route)
                VALUES (:session_id, :user_id, :start_time, :last_reload, :ip_address, :route)'
            );
            $time = time();
            $stmt->bindParam(':session_id', session_id(), \PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
            $stmt->bindParam(':start_time', $time, \PDO::PARAM_INT);
            $stmt->bindParam(':last_reload', $time, \PDO::PARAM_INT);
            $stmt->bindParam(':ip_address', $ipAddress, \PDO::PARAM_STR);
            $stmt->bindParam(':route', $route, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\Exception $e) {
        }
    }
}
