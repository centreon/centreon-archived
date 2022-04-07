<?php

/**
 * Copyright 2005-2021 Centreon
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

include_once _CENTREON_PATH_ . "/www/class/centreonAuth.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonRestHttp.class.php";

class CentreonAuthSSO extends CentreonAuth
{
    protected $ssoOptions = array();
    protected $ssoMandatory = 0;

    /**
     * @var using a proxy
     */
    private $proxy = null;

    public function __construct(
        $dependencyInjector,
        $username,
        $password,
        $autologin,
        $pearDB,
        $CentreonLog,
        $encryptType = 1,
        $token = "",
        $generalOptions = array()
    ) {
        $this->ssoOptions = $generalOptions;
        $this->CentreonLog = $CentreonLog;
        $this->getProxy();
        $this->debug = $this->getLogFlag();

        parent::__construct(
            $dependencyInjector,
            $username,
            $password,
            $autologin,
            $pearDB,
            $CentreonLog,
            $encryptType,
            $token
        );
        if ($this->error != '' && $this->ssoMandatory == 1) {
            $this->error .= " SSO Protection (user=" . $this->ssoUsername . ').';
            global $msg_error;
            $msg_error = "Invalid User. SSO Protection (user=" . $this->ssoUsername . ")";
        }
    }

    protected function checkPassword($password, $token = "", $autoimport = false)
    {
        if ($this->ssoMandatory == 1) {
            // Mode LDAP autoimport. Need to call it
            if ($autoimport) {
                // Password is only because it needs one...
                parent::checkPassword('test', $token, $autoimport);
            }
            // We delete old sessions with same SID
            global $pearDB;
            $pearDB->query("DELETE FROM session WHERE session_id = '" . session_id() . "'");
            $this->passwdOk = 1;
        } else {
            // local connect (when sso not enabled and 'sso_mode' == 1
            return parent::checkPassword($password, $token);
        }
    }

    /**
     * get proxy data
     *
     */
    private function getProxy()
    {
        global $pearDB;
        $query = 'SELECT `key`, `value` '
            . 'FROM `options` '
            . 'WHERE `key` IN ( '
            . '"proxy_url", "proxy_port", "proxy_user", "proxy_password" '
            . ') ';
        $res = $pearDB->query($query);
        while ($row = $res->fetchRow()) {
            $dataProxy[$row['key']] = $row['value'];
        }

        if (isset($dataProxy['proxy_url']) && !empty($dataProxy['proxy_url'])) {
            $this->proxy = $dataProxy['proxy_url'];
            if ($dataProxy['proxy_port']) {
                $this->proxy .= ':' . $dataProxy['proxy_port'];
            }

            /* Proxy basic authentication */
            if (
                isset($dataProxy['proxy_user'])
                && !empty($dataProxy['proxy_user'])
                && isset($dataProxy['proxy_password'])
                && !empty($dataProxy['proxy_password'])
            ) {
                $this->proxyAuthentication = $dataProxy['proxy_user'] . ':' . $dataProxy['proxy_password'];
            }
        }
    }

    /**
     * Log enabled
     *
     * @return int
     */
    protected function getLogFlag()
    {
        global $pearDB;
        $res = $pearDB->query("SELECT value FROM options WHERE `key` = 'debug_auth'");
        $data = $res->fetch();
        if (isset($data["value"])) {
            return (int) $data["value"];
        }
        return 0;
    }
}
