<?php

/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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
namespace Centreon\Internal;

use Centreon\Internal\Di;

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
