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

include_once _CENTREON_PATH_ . "/www/class/centreonAuth.class.php";

class CentreonAuthSSO extends CentreonAuth
{

    protected $options_sso = array();
    protected $sso_mandatory = 0;

    public function __construct(
        $username,
        $password,
        $autologin,
        $pearDB,
        $CentreonLog,
        $encryptType = 1,
        $token = "",
        $generalOptions = array()
    ) {
    
        $this->options_sso = $generalOptions;
        
        if (isset($this->options_sso['sso_enable']) && $this->options_sso['sso_enable'] == 1 &&
            isset($this->options_sso['sso_header_username']) && $this->options_sso['sso_header_username'] != '' &&
            isset($_SERVER[$this->options_sso['sso_header_username']])) {
            $this->sso_username = $_SERVER[$this->options_sso['sso_header_username']];
            if ($this->checkSsoClient()) {
                $this->sso_mandatory = 1;
                $username = $this->sso_username;
                if (isset($this->options_sso['sso_username_pattern']) &&
                    $this->options_sso['sso_username_pattern'] != '') {
                    $username = preg_replace(
                        $this->options_sso['sso_username_pattern'],
                        $this->options_sso['sso_username_replace'],
                        $username
                    );
                }
            }
        }

        parent::__construct($username, $password, $autologin, $pearDB, $CentreonLog, $encryptType, $token);
        if ($this->error != '' && $this->sso_mandatory == 1) {
            $this->error .= " SSO Protection (user=" . $this->sso_username . ').';
            global $msg_error;
            $msg_error = "Invalid User. SSO Protection (user=" . $this->sso_username . ")";
        }
        
    }

    protected function checkSsoClient()
    {
        if (isset($this->options_sso['sso_mode']) && $this->options_sso['sso_mode'] == 1) {
            # Mixed

            $blacklist = explode(',', $this->options_sso['sso_blacklist_clients']);
            foreach ($blacklist as $value) {
                $value = trim($value);
                if ($value != "" && preg_match('/' . $value . '/', $_SERVER['REMOTE_ADDR'])) {
                    return 0;
                }
            }

            $whitelist = explode(',', $this->options_sso['sso_trusted_clients']);
            foreach ($whitelist as $value) {
                $value = trim($value);
                if ($value != "" && preg_match('/' . $value . '/', $_SERVER['REMOTE_ADDR'])) {
                    return 1;
                }
            }

        } else {
            # Only SSO (no login from local users)
            return 1;
        }
    }

    protected function checkPassword($password, $token, $autoimport = false)
    {
        if ($this->sso_mandatory == 1) {
           # Mode LDAP autoimport. Need to call it
            if ($autoimport) {
                # Password is only because it needs one...
                parent::checkPassword('test', $token, $autoimport);
            }
           # We delete old sessions with same SID
            global $pearDB;
            $pearDB->query("DELETE FROM session WHERE session_id = '".session_id()."'");
            $this->passwdOk = 1;
        } else {
            # local connect (when sso not enabled and 'sso_mode' == 1
            return parent::checkPassword($password, $token);
        }
    }
}
