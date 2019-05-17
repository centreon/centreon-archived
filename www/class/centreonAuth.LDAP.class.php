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

require_once _CENTREON_PATH_ . 'www/class/centreonLDAP.class.php';

/**
 * Class for Ldap authentication
 */
class CentreonAuthLDAP
{

    protected $pearDB;
    protected $ldap;
    protected $CentreonLog;
    protected $contactInfos;
    protected $typePassword;
    protected $debug;
    protected $firstCheck = true;
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
    public function __construct($pearDB, $CentreonLog, $login, $password, $contactInfos, $arId)
    {
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
    private function getLogFlag()
    {
        global $pearDB;

        $res = $this->pearDB->query("SELECT value FROM options WHERE `key` = 'debug_ldap_import'");
        $data = $res->fetch();
        if (isset($data["value"])) {
            return $data["value"];
        }
        return 0;
    }

    /**
     * Check the user pass
     *
     */
    public function checkPassword()
    {
        if (!isset($this->contactInfos['contact_ldap_dn']) || $this->contactInfos['contact_ldap_dn'] == '') {
            $this->contactInfos['contact_ldap_dn'] = $this->ldap->findUserDn($this->contactInfos['contact_alias']);

        /* Validate if user exists in this resource */
        } elseif (isset($this->contactInfos['contact_ldap_dn'])
            && $this->contactInfos['contact_ldap_dn'] != ''
            && $this->ldap->findUserDn($this->contactInfos['contact_alias']) !== $this->contactInfos['contact_ldap_dn']
        ) {
            return 0;
        }

        /*
         * LDAP BIND
         */
        if (!isset($this->contactInfos['contact_ldap_dn']) || trim($this->contactInfos['contact_ldap_dn']) == '') {
            return 2;
        }
        @ldap_bind($this->ds, $this->contactInfos['contact_ldap_dn'], $this->typePassword);
        if ($this->debug) {
            $this->CentreonLog->insertLog(3, "Connexion = " . $this->contactInfos['contact_ldap_dn'] . " :: " .
                                          ldap_error($this->ds));
        }

        /*
         * In some case, we fallback to local Auth
         * 0 : Bind successful => Default case
         * 2 : Protocol error
         * -1 : Can't contact LDAP server (php4) => Fallback
         * 51 : Server is busy => Fallback
         * 52 : Server is unavailable => Fallback
         * 81 : Can't contact LDAP server (php5) => Fallback
         */
        if (isset($this->ds) && $this->ds) {
            switch (ldap_errno($this->ds)) {
                case 0:
                    if ($this->debug) {
                        $this->CentreonLog->insertLog(3, "LDAP AUTH : OK, let's go ! ");
                    }
                    if (false == $this->updateUserDn()) {
                        return 0;
                    }
                    return 1;
                    break;
                case 2:
                    if ($this->debug) {
                        $this->CentreonLog->insertLog(3, "LDAP AUTH : Protocol Error ");
                    }
                    return 2;
                    break;
                case -1:
                case 51:
                    if ($this->debug) {
                        $this->CentreonLog->insertLog(3, "LDAP AUTH : Error, Server Busy. Try later");
                    }
                    return -1;
                    break;
                case 52:
                    if ($this->debug) {
                        $this->CentreonLog->insertLog(3, "LDAP AUTH : Error, Server unavailable. Try later");
                    }
                    return -1;
                    break;
                case 81:
                    if ($this->debug) {
                        $this->CentreonLog->insertLog(3, "LDAP AUTH : Error, Fallback to Local AUTH");
                    }
                    return 2;
                    break;
                default:
                    if ($this->debug) {
                        $this->CentreonLog->insertLog(3, "LDAP AUTH : LDAP don't like you, sorry");
                    }
                    return 0;
                    break;
            }
        } else {
            if ($this->debug) {
                $this->CentreonLog->insertLog(3, "DS empty");
            }
            return 0; /* 2 ?? */
        }
    }

    /**
     * Search and update the user dn
     *
     * @return bool If the DN is modified
     */
    public function updateUserDn()
    {
        if ($this->ldap->rebind()) {
            $userDn = $this->ldap->findUserDn(
                html_entity_decode($this->contactInfos['contact_alias'], ENT_QUOTES, 'UTF-8')
            );
            if (false === $userDn) {
                $this->CentreonLog->insertLog(3, "LDAP AUTH : No DN for user " .
                    html_entity_decode($this->contactInfos['contact_alias'], ENT_QUOTES, 'UTF-8'));
                return false;
            }

            // Get ldap user informations
            $userInfos = $this->ldap->getEntry($userDn);
            $userDisplay = $userInfos[$this->ldap->getAttrName('user', 'name')];

            // Get the first if there are multiple entries
            if (is_array($userDisplay)) {
                $userDisplay = $userDisplay[0];
            }
            // Replace space by underscore
            $userDisplay = str_replace(array(' ', ','), '_', $userDisplay);

            // Delete parenthesis
            $userDisplay = str_replace(array('(', ')'), '', $userDisplay);
            $userEmail = "'".$this->contactInfos['contact_email']."'";
            if (isset($userInfos[$this->ldap->getAttrName('user', 'email')])
                && trim($userInfos[$this->ldap->getAttrName('user', 'email')]) != ''
            ) {
                if (is_array($userInfos[$this->ldap->getAttrName('user', 'email')])) {
                    // Get the first if there are multiple entries
                    if ($userInfos[$this->ldap->getAttrName('user', 'email')][0]) {
                        $userEmail = "'" . $userInfos[$this->ldap->getAttrName('user', 'email')][0] . "'";
                    }
                } elseif ($userInfos[$this->ldap->getAttrName('user', 'email')]) {
                    $userEmail = "'" . $userInfos[$this->ldap->getAttrName('user', 'email')] . "'";
                }
            }
            $userPager = "'".$this->contactInfos['contact_pager']."'";
            if (isset($userInfos[$this->ldap->getAttrName('user', 'pager')])
                && trim($userInfos[$this->ldap->getAttrName('user', 'pager')]) != ''
            ) {
                if (is_array($userInfos[$this->ldap->getAttrName('user', 'pager')])) {
                    // Get the first if there are multiple entries
                    if ($userInfos[$this->ldap->getAttrName('user', 'pager')][0]) {
                        $userPager = "'" . $userInfos[$this->ldap->getAttrName('user', 'pager')][0] . "'";
                    }
                } elseif ($userInfos[$this->ldap->getAttrName('user', 'pager')]) {
                    $userPager = "'" . $userInfos[$this->ldap->getAttrName('user', 'pager')] . "'";
                }
            }
            if (isset($this->contactInfos['contact_id'])) {
                // Update the user dn and extended informations for user
                $this->CentreonLog->insertLog(3, "LDAP AUTH : Update user DN for user " .
                    html_entity_decode($this->contactInfos['contact_alias'], ENT_QUOTES, 'UTF-8'));
                $queryUpdateExtInfos = "UPDATE contact SET
					contact_ldap_dn = '" . $this->pearDB->escape($userDn) . "',
					contact_name = '" . $this->pearDB->escape($userDisplay) . "',
					contact_email = " . $userEmail . ",
					contact_pager = " . $userPager . ",
                    ar_id = " . $this->arId . "
					WHERE contact_id = " . $this->contactInfos['contact_id'];

                try {
                    $this->pearDB->query($queryUpdateExtInfos);
                } catch (\PDOException $e) {
                    $this->CentreonLog->insertLog(3, 'Error in update ldap informations for user ' .
                       html_entity_decode($this->contactInfos['contact_alias'], ENT_QUOTES, 'UTF-8'));
                    return false;
                }
                $this->contactInfos['contact_ldap_dn'] = $userDn;

                // try to update user groups from AD
                try {
                    include_once(realpath(__DIR__ .  '/centreonContactgroup.class.php'));
                    $cgs = new CentreonContactgroup($this->pearDB);
                    $cgs->syncWithLdap();
                } catch (\Exception $e) {
                    $this->CentreonLog->insertLog(3, 'Error in updating ldap groups');
                }

                return true;
            } else {
                // Find the template ID
                $query = "SELECT ari_value
                          FROM `auth_ressource_info` a, `contact` c
                          WHERE a.`ari_name` = 'ldap_contact_tmpl'
                          AND a.ar_id = ".$this->pearDB->escape($this->arId)."
                          AND a.ari_value = c.contact_id";
                $res = $this->pearDB->query($query);
                $row = $res->fetch();
                if (!isset($row['ari_value']) || !$row['ari_value']) {
                    $this->CentreonLog->insertLog(3, "LDAP AUTH : No contact template defined.");
                    return false;
                }
                $tmplId = $row['ari_value'];

                // Insert user in database
                $query = "INSERT INTO contact
                    (contact_template_id, contact_alias, contact_name, contact_auth_type, contact_ldap_dn, ar_id,
                    contact_email, contact_pager, contact_oreon, contact_activate, contact_register,
                    contact_enable_notifications)
		        	VALUES (" . $tmplId . ", '" .
                    $this->contactInfos['contact_alias'] . "', '" .
                    $userDisplay . "', 'ldap', '" . $this->pearDB->escape($userDn) . "', " . $this->arId .
                    ", " . $userEmail . ", " . $userPager . ", '1', '1', '1', '2')";
                try {
                    $this->pearDB->query($query);

                    // Get the contact_id
                    $query = "SELECT contact_id FROM contact
                        WHERE contact_ldap_dn = '" . $this->pearDB->escape($userDn) . "'";
                    $res = $this->pearDB->query($query);
                    $row = $res->fetch();
                    $this->contactInfos['contact_id'] = $row['contact_id'];
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
                    $query2 = "SELECT cg_id FROM contactgroup WHERE cg_name IN (" . $listGroupStr . ")";
                    $res2 = $this->pearDB->query($query2);

                    // Insert the relation between the LDAP's contactgroups and the user
                    // Moving the prepare statement before the while loop for better performances
                    $stmt = $this->pearDB->prepare(
                        "INSERT INTO contactgroup_contact_relation (contactgroup_cg_id, contact_contact_id) " .
                        "VALUES (:ldapCg, :contactId)"
                    );
                    while ($row2 = $res2->fetch()) {
                        $stmt->bindValue(':ldapCg', $row2['cg_id'], PDO::PARAM_INT);
                        $stmt->bindValue(':contactId', $this->contactInfos['contact_id'], PDO::PARAM_INT);
                        $stmt->execute();
                    }
                    // Insert the relation between the LDAP's default contactgroup and the user
                    // returns true if everything goes well
                    return $this->ldap->addUserToLdapDefautCg(
                        $this->arId,
                        $this->contactInfos['contact_id']
                    );
                } catch (\PDOException $e) {
                    // Nothing
                }
            }
        }
        return false;
    }
}
