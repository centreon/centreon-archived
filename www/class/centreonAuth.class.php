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

class CentreonAuth
{
    /**
     * The default page has to be Resources status
     */
    public const DEFAULT_PAGE = 200;
    public const PWS_OCCULTATION = '******';

    public const AUTOLOGIN_ENABLE = 1;
    public const AUTOLOGIN_DISABLE = 0;

    public const ENCRYPT_MD5 = 1;
    public const ENCRYPT_SHA1 = 2;

    protected const SOURCE_LOCAL = 'local';

    // Declare Values
    public $userInfos;
    protected $login;
    protected $password;
    protected $enable;
    protected $userExists;
    protected $cryptEngine;
    protected $autologin;
    protected $cryptPossibilities;
    protected $pearDB;
    protected $debug;
    protected $dependencyInjector;

    // Flags
    public $passwdOk;
    protected $authType;
    protected $ldap_auto_import;
    protected $ldap_store_password;
    protected $default_page;

    // keep log class
    protected $CentreonLog;

    // Error Message
    protected $error;

    /**
     * Constructor
     *
     * @param string $username
     * @param string $password
     * @param int $autologin
     * @param CentreonDB $pearDB
     * @param CentreonUserLog $CentreonLog
     * @param int $encryptType
     * @param string $token | for autologin
     * @return void
     */
    public function __construct(
        $dependencyInjector,
        $username,
        $password,
        $autologin,
        $pearDB,
        $CentreonLog,
        $encryptType = self::ENCRYPT_MD5,
        $token = ""
    ) {
        $this->dependencyInjector = $dependencyInjector;
        $this->cryptPossibilities = array('MD5', 'SHA1');
        $this->CentreonLog = $CentreonLog;
        $this->login = $username;
        $this->password = $password;
        $this->pearDB = $pearDB;
        $this->autologin = $autologin;
        $this->cryptEngine = $encryptType;
        $this->debug = $this->getLogFlag();
        $this->ldap_auto_import = array();
        $this->ldap_store_password = array();
        $this->default_page = self::DEFAULT_PAGE;

        $res = $pearDB->query(
            "SELECT ar.ar_id, ari.ari_value, ari.ari_name " .
            "FROM auth_ressource_info ari, auth_ressource ar " .
            "WHERE ari_name IN ('ldap_auto_import', 'ldap_store_password') " .
            "AND ari.ar_id = ar.ar_id " .
            "AND ar.ar_enable = '1'"
        );
        while ($row = $res->fetch()) {
            if ($row['ari_name'] == 'ldap_auto_import' && $row['ari_value']) {
                $this->ldap_auto_import[$row['ar_id']] = $row['ari_value'];
            } elseif ($row['ari_name'] == 'ldap_store_password') {
                $this->ldap_store_password[$row['ar_id']] = $row['ari_value'];
            }
        }
        $this->checkUser($username, $password, $token);
    }

    /**
     * Log enabled
     *
     * @return int
     */
    protected function getLogFlag()
    {
        $res = $this->pearDB->query("SELECT value FROM options WHERE `key` = 'debug_auth'");
        $data = $res->fetch();
        if (isset($data["value"])) {
            return $data["value"];
        }
        return 0;
    }

    /**
     * Check if password is ok
     *
     * @param string $password
     * @param string $token
     * @param boolean $autoImport
     * @return void
     */
    protected function checkPassword($password, $token = "", $autoImport = false)
    {
        if ((strlen($password) == 0 || $password === "") && $token === "") {
            $this->passwdOk = 0;
            return;
        }
        if (isset($this->userInfos["contact_passwd"]) &&
            !$this->dependencyInjector['utils']->detectPassPattern($this->userInfos["contact_passwd"])
        ) {
            $this->userInfos["contact_passwd"] = 'md5__' . $this->userInfos["contact_passwd"];
        }
        if ($this->userInfos["contact_auth_type"] == "ldap" && $this->autologin == 0) {
            /*
             * Insert LDAP Class
             */
            include_once(_CENTREON_PATH_ . "/www/class/centreonAuth.LDAP.class.php");

            $query = "SELECT ar_id FROM auth_ressource WHERE ar_enable = '1'";
            $res = $this->pearDB->query($query);
            $authResources = array();
            while ($row = $res->fetch()) {
                $index = $row['ar_id'];
                if (isset($this->userInfos['ar_id']) && $this->userInfos['ar_id'] == $row['ar_id']) {
                    $index = 0;
                }
                $authResources[$index] = $row['ar_id'];
            }

            foreach ($authResources as $arId) {
                if ($autoImport && !isset($this->ldap_auto_import[$arId])) {
                    break;
                }
                if ($this->passwdOk == 1) {
                    break;
                }
                $authLDAP = new CentreonAuthLDAP(
                    $this->pearDB,
                    $this->CentreonLog,
                    $this->login,
                    $this->password,
                    $this->userInfos,
                    $arId
                );
                $this->passwdOk = $authLDAP->checkPassword();
                if ($this->passwdOk == -1) {
                    $this->passwdOk = 0;
                    if (isset($this->userInfos["contact_passwd"])
                        && $this->userInfos["contact_passwd"] === $this->myCrypt($password)
                    ) {
                        $this->passwdOk = 1;
                        if (isset($this->ldap_store_password[$arId]) && $this->ldap_store_password[$arId]) {
                            $this->pearDB->query(
                                "UPDATE `contact` " .
                                "SET `contact_passwd` = '" . $this->myCrypt($this->password) . "'" .
                                "WHERE `contact_alias` = '" . $this->login . "' AND `contact_register` = '1'"
                            );
                        }
                    }
                } elseif ($this->passwdOk == 1) {
                    if (isset($this->ldap_store_password[$arId]) && $this->ldap_store_password[$arId]) {
                        if (!isset($this->userInfos["contact_passwd"])) {
                            $this->pearDB->query(
                                "UPDATE `contact` " .
                                "SET `contact_passwd` = '" . $this->myCrypt($this->password) . "'" .
                                "WHERE `contact_alias` = '" . $this->login . "' AND `contact_register` = '1'"
                            );
                        } elseif ($this->userInfos["contact_passwd"] != $this->myCrypt($this->password)) {
                            $this->pearDB->query(
                                "UPDATE `contact` " .
                                "SET `contact_passwd` = '" . $this->myCrypt($this->password) . "'" .
                                "WHERE `contact_alias` = '" . $this->login . "' AND `contact_register` = '1'"
                            );
                        }
                    }
                }
            }
        } elseif ($this->userInfos["contact_auth_type"] == ""
            || $this->userInfos["contact_auth_type"] == "local"
            || $this->autologin
        ) {
            if ($this->autologin
                && $this->userInfos["contact_autologin_key"]
                && $this->userInfos["contact_autologin_key"] === $token
            ) {
                $this->passwdOk = 1;
            } elseif (!empty($password)
                && $this->userInfos["contact_passwd"] === $password
                && $this->autologin
            ) {
                $this->passwdOk = 1;
            } elseif (!empty($password)
                && $this->userInfos["contact_passwd"] === $this->myCrypt($password)
                && $this->autologin == 0
            ) {
                $this->passwdOk = 1;
            } else {
                $this->passwdOk = 0;
            }
        }

        /**
         * LDAP - fallback
         */
        if ($this->passwdOk == 2) {
            if ($this->autologin && $this->userInfos["contact_autologin_key"]
                && $this->userInfos["contact_autologin_key"] === $token
            ) {
                $this->passwdOk = 1;
            } elseif (!empty($password)
                && isset($this->userInfos["contact_passwd"])
                && $this->userInfos["contact_passwd"] === $password && $this->autologin
            ) {
                $this->passwdOk = 1;
            } elseif (!empty($password)
                && isset($this->userInfos["contact_passwd"])
                && $this->userInfos["contact_passwd"] === $this->myCrypt($password)
                && $this->autologin == 0
            ) {
                $this->passwdOk = 1;
            } else {
                $this->passwdOk = 0;
            }
        }
    }

    /**
     * Check user password
     *
     * @param string $username
     * @param string $password
     * @param string $token
     * @return void
     */
    protected function checkUser($username, $password, $token)
    {

        if ($this->autologin == 0 || ($this->autologin && $token != "")) {
            $dbResult = $this->pearDB->query(
                "SELECT * FROM `contact` " .
                "WHERE `contact_alias` = '" . $this->pearDB->escape($username, true) . "'" .
                "AND `contact_activate` = '1' AND `contact_register` = '1' LIMIT 1"
            );
        } else {
            $dbResult = $this->pearDB->query(
                "SELECT * FROM `contact` " .
                "WHERE MD5(contact_alias) = '" . $this->pearDB->escape($username, true) . "'" .
                "AND `contact_activate` = '1' AND `contact_register` = '1' LIMIT 1"
            );
        }
        if ($dbResult->rowCount()) {
            $this->userInfos = $dbResult->fetch();
            if ($this->userInfos["default_page"]) {
                $dbResult2 = $this->pearDB->query(
                    "SELECT topology_url_opt FROM topology WHERE topology_page = "
                    . $this->userInfos["default_page"]
                );
                if ($dbResult2->numRows()) {
                    $data = $dbResult2->fetch();
                    $this->userInfos["default_page"] .= $data["topology_url_opt"];
                }
            }

            /*
             * Check password matching
             */
            $this->getCryptFunction();
            $this->checkPassword($password, $token);
            if ($this->passwdOk == 1) {
                $this->CentreonLog->setUID($this->userInfos["contact_id"]);
                $this->CentreonLog->insertLog(
                    CentreonUserLog::TYPE_LOGIN,
                    "[" . self::SOURCE_LOCAL . "] [" . $_SERVER["REMOTE_ADDR"] . "] "
                        . "Authentication succeeded for '" . $username . "'"
                );
            } else {
                //  Take care before modifying this message pattern as it may break tools such as fail2ban
                $this->CentreonLog->insertLog(
                    CentreonUserLog::TYPE_LOGIN,
                    "[" . self::SOURCE_LOCAL . "] [" . $_SERVER["REMOTE_ADDR"] . "] "
                        . "Authentication failed for '" . $username . "'"
                );
                $this->error = _('Your credentials are incorrect.');
            }
        } elseif (count($this->ldap_auto_import)) {
            /*
             * Add temporary userinfo auth_type
             */
            $this->userInfos['contact_alias'] = $username;
            $this->userInfos['contact_auth_type'] = "ldap";
            $this->userInfos['contact_email'] = '';
            $this->userInfos['contact_pager'] = '';
            $this->checkPassword($password, "", true);
            /*
             * Reset userInfos with imported information
             */
            $dbResult = $this->pearDB->query(
                "SELECT * FROM `contact` " .
                "WHERE `contact_alias` = '" . $this->pearDB->escape($username, true) . "'" .
                "AND `contact_activate` = '1' AND `contact_register` = '1' LIMIT 1"
            );
            if ($dbResult->rowCount()) {
                $this->userInfos = $dbResult->fetch();
                if ($this->userInfos["default_page"]) {
                    $dbResult2 = $this->pearDB->query(
                        "SELECT topology_url_opt FROM topology WHERE topology_page = "
                        . $this->userInfos["default_page"]
                    );
                    if ($dbResult2->numRows()) {
                        $data = $dbResult2->fetch();
                        $this->userInfos["default_page"] .= $data["topology_url_opt"];
                    }
                }
            }
        } else {
            if (strlen($username) > 0) {
                //  Take care before modifying this message pattern as it may break tools such as fail2ban
                $this->CentreonLog->insertLog(
                    CentreonUserLog::TYPE_LOGIN,
                    "[" . self::SOURCE_LOCAL . "] [" . $_SERVER["REMOTE_ADDR"] . "] "
                        . "Authentication failed for '" . $username . "' : not found"
                );
            }
            $this->error = _('Your credentials are incorrect.');
        }
    }

    /*
     * Check crypt system
     */
    protected function getCryptFunction()
    {
        if (isset($this->cryptEngine)) {
            switch ($this->cryptEngine) {
                case self::ENCRYPT_MD5:
                    return "MD5";
                    break;
                case self::ENCRYPT_SHA1:
                    return "SHA1";
                    break;
                default:
                    return "MD5";
                    break;
            }
        } else {
            return "MD5";
        }
    }

    /*
     * Crypt String
     */
    protected function myCrypt($str)
    {
        $algo = $this->dependencyInjector['utils']->detectPassPattern($str);
        if (!$algo) {
            switch ($this->cryptEngine) {
                case 1:
                    return $this->dependencyInjector['utils']->encodePass($str, 'md5');
                    break;
                case 2:
                    return $this->dependencyInjector['utils']->encodePass($str, 'sha1');
                    break;
                default:
                    return $this->dependencyInjector['utils']->encodePass($str, 'md5');
                    break;
            }
        } else {
            return $str;
        }
    }

    protected function getCryptEngine()
    {
        return $this->cryptEngine;
    }

    protected function userExists()
    {
        return $this->userExists;
    }

    protected function userIsEnable()
    {
        return $this->enable;
    }

    protected function passwordIsOk()
    {
        return $this->passwdOk;
    }

    protected function getAuthType()
    {
        return $this->authType;
    }
}
