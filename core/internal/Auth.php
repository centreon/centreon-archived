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
 *
 */

namespace Centreon\Internal;

/**
 * Class for authentication
 *
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Core
 */
class Auth
{
    protected $cryptPossibilities = array('MD5', 'SHA1');
    /*
     * Declare Values
     */
    protected $login;
    protected $password;
    protected $enable;
    protected $userExists;
    protected $cryptEngine;
    protected $autologin;
    public $userInfos;
    protected $debug;

    /*
     * Flags
     */
    public $passwdOk;
    protected $authType;
    protected $ldap_auto_import;
    protected $ldap_store_password;

    /*
     * Error Message
     */
    protected $error;

    /**
     * Constructor
     *
     * @param $username string The username for authentication
     * @param $password string The password
     * @param $autologin boolean If the authentication is by autologin
     * @param $encryptType int The type of crypt
     * @param $token string The token string
     */
    public function __construct($username, $password, $autologin, $encryptType = 1, $token = "")
    {
        $this->login = $username;
        $this->password = $password;
        $this->autologin = $autologin;
        $this->cryptEngine = $encryptType;
        $this->debug = false;
        if (1 === Di::getDefault()->get('config')->get('default', 'debug_auth', 0)) {
            $this->debug = true;
        }

        $this->ldap_auto_import = array();
        $this->ldap_store_password = array();

        $query = "SELECT ar.ar_id, ari.ari_value, ari.ari_name
                  FROM cfg_auth_resources_info ari, cfg_auth_resources ar
                  WHERE ari_name IN ('ldap_auto_import', 'ldap_store_password')
                  AND ari.ar_id = ar.ar_id
                  AND ar.ar_enable = '1'";
        $dbconn = Di::getDefault()->get('db_centreon');
        $res = $dbconn->query($query);
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
     * Check if password is ok
     *
     * @param $password The password to check
     * @param $token string The token
     * @param $autoimport bool If autoimport from ldap
     */
    protected function checkPassword($password, $token = "", $autoimport = false)
    {
        if ((strlen($password) == 0 || $password == "") && $token == "") {
            $this->passwdOk = 0;
            return;
        }

        $dbconn = Di::getDefault()->get('db_centreon');

        if ($this->userInfos["contact_auth_type"] == "ldap" && $this->autologin == 0) {
            $query = "SELECT ar_id FROM cfg_auth_resources WHERE ar_enable = '1'";
            $res = $dbconn->query($query);
            $authResources = array();
            while ($row = $res->fetch()) {
                $index = $row['ar_id'];
                if (isset($this->userInfos['ar_id']) && $this->userInfos['ar_id'] == $row['ar_id']) {
                    $index = 0;
                }
                $authResources[$index] = $row['ar_id'];
            }

            foreach ($authResources as $arId) {
                if ($autoimport && !isset($this->ldap_auto_import[$arId])) {
                    break;
                }
                if ($this->passwdOk == 1) {
                    break;
                }
                $authLDAP = new \Centreon\Auth\Ldap($this->login, $this->password, $this->userInfos, $arId);
                $this->passwdOk = $authLDAP->checkPassword();
                if ($this->passwdOk == -1) {
                    $this->passwdOk = 0;
                    if (isset($this->userInfos["contact_passwd"])
                        && $this->userInfos["contact_passwd"] == $this->myCrypt($password)) {
                        $this->passwdOk = 1;
                        if (isset($this->ldap_store_password[$arId]) && $this->ldap_store_password[$arId]) {
                            $dbconn->query(
                                "UPDATE `cfg_contacts`
                                    SET `contact_passwd` = '" . $this->myCrypt($this->password) . "'
                                    WHERE `contact_alias` = '" . $this->login . "' AND `contact_register` = '1'"
                            );
                        }
                    }
                } elseif ($this->passwdOk == 1) {
                    if (isset($this->ldap_store_password[$arId]) && $this->ldap_store_password[$arId]) {
                        if (!isset($this->userInfos["contact_passwd"])) {
                            $dbconn->query(
                                "UPDATE `cfg_contacts`
                                    SET `contact_passwd` = '" . $this->myCrypt($this->password) . "'
                                    WHERE `contact_alias` = '" . $this->login . "' AND `contact_register` = '1'"
                            );
                        } elseif ($this->userInfos["contact_passwd"] != $this->myCrypt($this->password)) {
                            $dbconn->query(
                                "UPDATE `cfg_contacts`
                                    SET `contact_passwd` = '" . $this->myCrypt($this->password) . "'
                                    WHERE `contact_alias` = '" . $this->login . "' AND `contact_register` = '1'"
                            );
                        }
                    }
                }
                $cnt++;
            }
        } elseif ($this->userInfos["contact_auth_type"] == ""
            || $this->userInfos["contact_auth_type"] == "local"
            || $this->autologin) {
            if ($this->autologin && $this->userInfos["contact_autologin_key"]
                && $this->userInfos["contact_autologin_key"] == $token) {
                $this->passwdOk = 1;
            } elseif ($this->userInfos["contact_passwd"] == $password && $this->autologin) {
                $this->passwdOk = 1;
            } elseif ($this->userInfos["contact_passwd"] == $this->myCrypt($password) && $this->autologin == 0) {
                $this->passwdOk = 1;
            } else {
                $this->passwdOk = 0;
            }
        }

        /**
         * LDAP - fallback
         */
        if ($this->passwdOk == 2) {
            if ($this->autologin
                && $this->userInfos["contact_autologin_key"]
                && $this->userInfos["contact_autologin_key"] == $token) {
                $this->passwdOk = 1;
            } elseif (isset($this->userInfos["contact_passwd"])
                && $this->userInfos["contact_passwd"] == $password
                && $this->autologin) {
                $this->passwdOk = 1;
            } elseif (isset($this->userInfos["contact_passwd"])
                && $this->userInfos["contact_passwd"] == $this->myCrypt($password)
                && $this->autologin == 0) {
                $this->passwdOk = 1;
            } else {
                $this->passwdOk = 0;
            }
        }
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
        $dbconn = Di::getDefault()->get('db_centreon');
        $logger = \Monolog\Registry::getInstance('MAIN');
        if ($this->autologin == 0 || ($this->autologin && $token != "")) {
            $res = $dbconn->query(
                "SELECT *
                    FROM `cfg_contacts`
                    WHERE `contact_alias` = '" . htmlentities($username, ENT_QUOTES, "UTF-8") . "'
                        AND `contact_activate` = '1'
                        AND `contact_register` = '1'"
            );
        } else {
            $res = $dbconn->query(
                "SELECT *
                    FROM `cfg_contacts`
                    WHERE MD5(contact_alias) = '" . htmlentities($username, ENT_QUOTES, "UTF-8") . "'
                        AND `contact_activate` = '1'
                        AND `contact_register` = '1'"
            );
        }
        $userInfos = $res->fetch();
        if (false !== $userInfos) {
            $this->userInfos = $userInfos;
            if ($this->userInfos["contact_oreon"]) {
                /*
                 * Check password matching
                 */
                $this->getCryptFunction();
                $this->checkPassword($password, $token);

                if ($this->passwdOk == 1) {
                    /*
                     * @todo see CentreonLog
                     * $this->CentreonLog->setUID($this->userInfos["contact_id"]);
                     */
                    if ($this->debug) {
                        $logger->debug("Contact '" . $username . "' logged in - IP : " . $_SERVER["REMOTE_ADDR"]);
                    }
                } else {
                    if ($this->debug) {
                        $logger->debug("Contact '" . $username . "' doesn't match with password");
                    }
                    $this->error = "Invalid user";
                }
            } else {
                if ($this->debug) {
                    $logger->debug("Contact '" . $username . "' is not enable for reaching centreon");
                }
                $this->error = "Invalid user";
            }
        } elseif (count($this->ldap_auto_import)) {
            /*
             * Add temporary userinfo auth_type
             */
            $this->userInfos['contact_alias'] = $username;
            $this->userInfos['contact_auth_type'] = "ldap";
            $this->checkPassword($password, "", true);
            /*
             * Reset userInfos with imported informations
             */
            $res = $dbconn->query(
                "SELECT * FROM `cfg_contacts`
                WHERE `contact_alias` = '" . htmlentities($username, ENT_QUOTES, "UTF-8") . "'
                AND `contact_activate` = '1'
                AND `contact_register` = '1'"
            );
            $userInfos = $res->fetch();
            if (false !== $userInfos) {
                $this->userInfos = $userInfos;
            }
        } else {
            if ($this->debug) {
                $logger->debug("No contact found with this login : '$username'");
            }
            $this->error = "Invalid user";
        }
    }

    /**
     * Return the hash system
     *
     * @return string
     */
    protected function getCryptFunction()
    {
        if (isset($this->cryptEngine)) {
            switch ($this->cryptEngine) {
                case 1:
                    return "MD5";
                case 2:
                    return "SHA1";
                default:
                    return "MD5";
            }
        } else {
            return "MD5";
        }
    }

    /**
     * Hash a string
     *
     * @param $str The string to hash
     * @retun string
     */
    protected function myCrypt($str)
    {
        switch ($this->cryptEngine) {
            case 1:
                return md5($str);
            case 2:
                return sha1($str);
            default:
                return md5($str);
        }
    }

    /**
     * Return the hash system
     *
     * @return string
     */
    protected function getCryptEngine()
    {
        return $this->cryptEngine;
    }

    /**
     * Return if the user exists
     *
     * @return bool
     * @todo Valid if used
     */
    protected function userExists()
    {
        return $this->userExists;
    }

    /**
     * Return if the user is enable
     *
     * @return bool
     * @todo Valid if used
     */
    protected function userIsEnable()
    {
        return $this->enable;
    }

    /**
     * Return if the password is valid for authentication
     *
     * @return bool
     * @todo Valid if used
     */
    protected function passwordIsOk()
    {
        return $this->passwdOk;
    }

    /**
     * Get authentication type
     *
     * @return string
     * @todo Valid if used
     */
    protected function getAuthType()
    {
        return $this->authType;
    }
}
