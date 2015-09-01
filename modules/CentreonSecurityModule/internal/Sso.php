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

/**
 * Class for authentication with SSO
 *
 * @see \Centreon\Auth
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Core
 */
class Sso extends Auth
{
    /**
     *
     * @var boolean 
     */
    protected $ssoEnable = false;
    
    /**
     *
     * @var integer 
     */
    protected $sso_mandatory = 0;

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
        $config = \Centreon\Internal\Di::getDefault()->get('config');
        
        if (1 === $config->get('default', 'sso_enable', 0)) {
            if ('' !== $config->get('default', 'sso_header_username', '')) {
                $this->sso_username = $_SERVER[$config->get('default', 'sso_header_username', '')];
                $this->enable = true;
                if ($this->checkSsoClient()) {
                    $self->sso_mandatory = 1;
                    $username = $this->sso_username;
                }
            }
        }
        parent::__construct($username, $password, $autologin, $token);
    }

    /**
     * Check if it's a SSO client
     * 
     * @return boolean
     */
    protected function checkSsoClient()
    {
        $config = \Centreon\Internal\Di::getDefault()->get('config');
        if (1 === $config->get('default', 'sso_mode', 0)) {
            /* Mixed. Only trusted site for sso. */
            if (preg_match(
                '/' . filter_input(INPUT_SERVER, 'REMOTE_ADDR') . '(\s|,|$)/',
                $config->get('default', 'sso_trusted_clients')
            )) {
                /* SSO */
                return true;
            }
        } else {
            /* Only SSO (no login from local users) */
            return true;
        }
        return false;
    }

    /**
     * Check password 
     *
     * @param $password string The password to check
     * @param $token string The token
     */
    protected function checkPassword($password, $token)
    {
        if ($this->enable && $this->login) {
            $this->passwdOk = 1;
        } else {
            /* local connect (when sso not enabled and 'sso_mode' == 1 */
            parent::checkPassword($password, $token);
        }
    }
}
