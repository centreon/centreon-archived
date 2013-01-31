<?php

/*
 * Copyright 2005-2011 MERETHIS
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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

require_once $centreon_path . 'www/class/centreonLDAP.class.php';

/**
 * Class for Ldap authentication
 */
class CentreonAuthLDAP {

    var $pearDB;
    var $ldap;
    var $CentreonLog;
    var $contactInfos;
    var $typePassword;
    var $debug;
    var $firstCheck = true;
    protected $arId;

    /**
     * Constructor
     *
     * @param CentreonDB $pearDB Connection to centreon database
     * @param CentreonLog $CentreonLog Log event
     * @param string $login The username
     * @param string $password The user password
     * @param string $contactInfos
     * @param int $arId | Auth Ressource ID
     * @return void
     */
    function CentreonAuthLDAP($pearDB, $CentreonLog, $login, $password, $contactInfos, $arId) {
        $this->arId = $arId;
        $this->pearDB = $pearDB;

        $this->CentreonLog = $CentreonLog;

        $this->ldap = new CentreonLDAP($pearDB, $CentreonLog, $arId);
        $this->ldap->connect();
        $this->ds = $this->ldap->getDs();

        /*
         * Set contact Informations
         */
        $this->contactInfos = $contactInfos;

        /*
         * Keep password
         */
        $this->typePassword = $password;

        $this->debug = $this->getLogFlag();
    }

    /**
     * Is loging enable ?
     *
     * @return int 1 enable 0 disable
     */
    private function getLogFlag() {
        global $pearDB;
        
        $res = $pearDB->query("SELECT value FROM options WHERE `key` = 'debug_ldap_import'");
        $data = $res->fetchRow();
        if (isset($data["value"])) {
            return $data["value"];
        }
        return 0;
    }

    /**
     * Check the user pass
     *
     */
    function checkPassword() {

        /*
         * Check if it's a new user
         */
        $newUser = false;
        if (!isset($this->contactInfos['contact_ldap_dn']) || $this->contactInfos['contact_ldap_dn'] == '') {
            $this->contactInfos['contact_ldap_dn'] = $this->ldap->findUserDn($this->contactInfos['contact_alias']);
            $newUser = true;
        }

        /*
         * LDAP BIND
         */
        if (!isset($this->contactInfos['contact_ldap_dn']) || trim($this->contactInfos['contact_ldap_dn']) == '') {
            return 2;
        }
        @ldap_bind($this->ds, $this->contactInfos['contact_ldap_dn'], $this->typePassword);
        if ($this->debug) {
            $this->CentreonLog->insertLog(3, "Connexion = " . $this->contactInfos['contact_ldap_dn'] . " :: " . ldap_error($this->ds));
        }

        /*
         * In some case, we fallback to local Auth
         * 0 : Bind succesfull => Default case
         * 2 : Protocol error
         * -1 : Can't contact LDAP server (php4) => Fallback
         * 51 : Server is busy => Fallback
         * 52 : Server is unavailable => Fallback
         * 81 : Can't contact LDAP server (php5) => Fallback
         */
        if (isset($this->ds) && $this->ds) {
            switch (ldap_errno($this->ds)) {
                case 0:
                    if ($this->debug)
                        $this->CentreonLog->insertLog(3, "LDAP AUTH : OK, let's go ! ");
                    //if ($newUser) {
                    $this->updateUserDn();
                    //}
                    return 1;
                    break;
                case 2:
                    if ($this->debug)
                        $this->CentreonLog->insertLog(3, "LDAP AUTH : Protocol Error ");
                    return 2;
                    break;
                case -1:
                case 51:
                    if ($this->debug)
                        $this->CentreonLog->insertLog(3, "LDAP AUTH : Error, Server Busy. Try later");
                    return -1;
                    break;
                case 52:
                    if ($this->debug)
                        $this->CentreonLog->insertLog(3, "LDAP AUTH : Error, Server unavailable. Try later");
                    return -1;
                    break;
                case 81:
                    if ($this->debug)
                        $this->CentreonLog->insertLog(3, "LDAP AUTH : Error, Fallback to Local AUTH");
                    return 2;
                    break;
                default:
                    if ($this->debug)
                        $this->CentreonLog->insertLog(3, "LDAP AUTH : LDAP don't like you, sorry");
                    if ($this->firstCheck && $this->updateUserDn()) {
                        $this->firstCheck = false;
                        return $this->checkPassword();
                    }
                    return 0;
                    break;
            }
        } else {
            if ($this->debug)
                $this->CentreonLog->insertLog(3, "DS empty");
            return 0; /* 2 ?? */
        }
    }

    /**
     * Search and update the user dn
     *
     * @return bool If the DN is modified
     */
    function updateUserDn() {
        if ($this->ldap->rebind()) {
            $userDn = $this->ldap->findUserDn(html_entity_decode($this->contactInfos['contact_alias'], ENT_QUOTES, 'UTF-8'));

            if (false === $userDn) {
                $this->CentreonLog->insertLog(3, "LDAP AUTH : No DN for user " . html_entity_decode($this->contactInfos['contact_alias'], ENT_QUOTES, 'UTF-8'));
                return false;
            }

            /*
             * Get ldap user informations
             */
            $userInfos = $this->ldap->getEntry($userDn);
            $userDisplay = $userInfos[$this->ldap->getAttrName('user', 'name')];
            /*
             * Get the first if there are multiple entries
             */
            if (is_array($userDisplay)) {
                $userDisplay = $userDisplay[0];
            }
            /*
             * Replace space by underscore
             */
            $userDisplay = str_replace(array(' ', ','), '_', $userDisplay);
            $userEmail = "NULL";
            if (isset($userInfos[$this->ldap->getAttrName('user', 'email')]) && trim($userInfos[$this->ldap->getAttrName('user', 'email')]) != '') {
                if (is_array($userInfos[$this->ldap->getAttrName('user', 'email')])) {
                    /*
                     * Get the first if there are multiple entries
                     */
                    $userEmail = "'" . $userInfos[$this->ldap->getAttrName('user', 'email')][0] . "'";
                } else {
                    $userEmail = "'" . $userInfos[$this->ldap->getAttrName('user', 'email')] . "'";
                }
            }
            $userPager = "NULL";
            if (isset($userInfos[$this->ldap->getAttrName('user', 'pager')]) && trim($userInfos[$this->ldap->getAttrName('user', 'pager')]) != '') {
                if (is_array($userInfos[$this->ldap->getAttrName('user', 'pager')])) {
                    /*
                     * Get the first if there are multiple entries
                     */
                    $userPager = "'" . $userInfos[$this->ldap->getAttrName('user', 'pager')][0] . "'";
                } else {
                    $userPager = "'" . $userInfos[$this->ldap->getAttrName('user', 'pager')] . "'";
                }
            }
            if (isset($this->contactInfos['contact_id'])) {
                /*
                 * Update the user dn and extended informations for user
                 */
                $this->CentreonLog->insertLog(3, "LDAP AUTH : Update user DN for user " . html_entity_decode($this->contactInfos['contact_alias'], ENT_QUOTES, 'UTF-8'));
                $queryUpdateExtInfos = "UPDATE contact SET
					contact_ldap_dn = '" . $userDn . "',
					contact_name = '" . $userDisplay . "',
					contact_email = " . $userEmail . ",
					contact_pager = " . $userPager . ",
                                        ar_id = ".$this->arId."
					WHERE contact_id = " . $this->contactInfos['contact_id'];

                $ret = $this->pearDB->query($queryUpdateExtInfos);
                if (PEAR::isError($ret)) {
                    $this->CentreonLog->insertLog(3, 'Error in update ldap informations for user ' . html_entity_decode($this->contactInfos['contact_alias'], ENT_QUOTES, 'UTF-8'));
                    return false;
                }
                $this->contactInfos['contact_ldap_dn'] = $userDn;
                return true;
            } else {
                /*
                 * Find the template ID
                 */
                $query = "SELECT ari_value 
                          FROM `auth_ressource_info` 
                          WHERE `ari_name` = 'ldap_contact_tmpl' 
                          AND ar_id = ".$this->pearDB->escape($this->arId);
                $res = $this->pearDB->query($query);
                $row = $res->fetchRow();
                if (!isset($row['ari_value']) || !$row['ari_value']) {
                    $this->CentreonLog->insertLog(3, "LDAP AUTH : No contact template defined.");
                    return false;
                }
                $tmplId = $row['ari_value'];
                /*
                 * Insert user in database
                 */
                $query = "INSERT INTO contact (contact_template_id, contact_alias, contact_name, contact_auth_type, contact_ldap_dn, ar_id, contact_email, contact_pager, contact_oreon, contact_activate, contact_register)
		        	VALUES (" . $tmplId . ", '" . htmlentities($this->contactInfos['contact_alias'], ENT_QUOTES, 'UTF-8') . "', '" . htmlentities($userDisplay, ENT_QUOTES, 'UTF-8') . "', 'ldap', '" . $userDn . "', ".$this->arId.", " . $userEmail . ", " . $userPager . ", '1', '1', 1)";
                if (false === PEAR::isError($this->pearDB->query($query))) {
                    /*
                     * Get the contact_id
                     */
                    $query = "SELECT contact_id FROM contact WHERE contact_ldap_dn = '" . $userDn . "'";
                    $res = $this->pearDB->query($query);
                    $row = $res->fetchRow();
                    $contact_id = $row['contact_id'];
                    $listGroup = $this->ldap->listGroupsForUser($userDn);
                    $listGroupStr = "";
                    foreach ($listGroup as $gName) {
                        if ($listGroupStr != "") {
                            $listGroupStr .= ",";
                        }
                        $listGroupStr .= "'" . $gName . "'";
                    }
                    if ($listGroupStr == "") {
                        $listGroupStr = "''";
                    }
                    $query = "SELECT cg_id FROM contactgroup WHERE cg_name IN (" . $listGroupStr . ")";
                    $res = $this->pearDB->query($query);
                    /*
                     * Insert the relation between contact and contact group
                     */
                    while ($row = $res->fetchRow()) {
                        $query = "INSERT INTO contactgroup_contact_relation
    	            						(contactgroup_cg_id, contact_contact_id)
    	            					VALUES (" . $row['cg_id'] . ", " . $contact_id . ")";
                        $this->pearDB->query($query);
                    }
                    return true;
                }
            }
        }
        return false;
    }

}

?>
