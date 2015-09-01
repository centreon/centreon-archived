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

namespace CentreonSecurity\Internal;

use CentreonAdministration\Repository\UserRepository;
use Centreon\Internal\Exception;

/**
 * Class for authentication
 *
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Core
 */
class Auth
{
    /*
     * Declare Values
     */
    protected $login;
    protected $password;
    protected $autologin;
    public $userInfos;
    protected $debug;
    /*
     * Error Message
     */
    protected $error;

    /**
     * Constructor
     * 
     * @param string $username The username for authentication
     * @param string $password The password
     * @param boolean $autologin If the authentication is by autologin
     * @param string $token The token string
     */
    public function __construct($username, $password, $autologin, $token = "")
    {
        $this->login = $username;
        $this->password = $password;
        $this->autologin = $autologin;
        $this->debug = false;
        /*if (1 === Di::getDefault()->get('config')->get('default', 'debug_auth', 0)) {
            $this->debug = true;
        }*/
        $this->checkUser($username, $password, $token);
    }
    
    /**
     * Check user password
     *
     * @param $username string The username
     * @param $password string The password
     * @param $token string The token
     */
    protected function checkUser($username, $password, $token)
    {
        //$logger = \Monolog\Registry::getInstance('MAIN');
        
        try {
            $login = htmlentities($username, ENT_QUOTES, "UTF-8");
            if ($this->autologin == 0 || ($this->autologin && $token != "")) {
                $this->userInfos = UserRepository::checkUser($login, $password);
            } else {
                $this->userInfos = UserRepository::checkUser($login, $password, $token);
            }
            //$logger->debug("Contact '" . $login . "' logged in - IP : " . filter_input(INPUT_SERVER, "REMOTE_ADDR"));
        } catch (Exception $e) {
            if ($this->debug) {
                //$logger->debug($e->getMessage());
            }
            throw new \Centreon\Internal\Exception($e->getMessage(), $e->getCode());
        }
    }
}
