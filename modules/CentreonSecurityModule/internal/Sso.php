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
