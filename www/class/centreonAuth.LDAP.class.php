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

require_once __DIR__ . '/centreonAuth.class.php';
require_once __DIR__ . '/centreonLDAP.class.php';

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
     * @param CentreonUserLog $CentreonLog Log event
     * @param string $login The username
     * @param string $password The user password
     * @param mixed[] $contactInfos
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
         * Set contact Information
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
        if (empty($this->contactInfos['contact_ldap_dn'])) {
            $this->contactInfos['contact_ldap_dn'] = $this->ldap->findUserDn($this->contactInfos['contact_alias']);
        } elseif (
            ($userDn = $this->ldap->findUserDn($this->contactInfos['contact_alias']))
            && $userDn !== $this->contactInfos['contact_ldap_dn']
        ) { // validate if user exists in this resource
            if (! $userDn) {
                //User resource error
                return CentreonAuth::PASSWORD_INVALID;
            } else {
                //LDAP fallback
                return CentreonAuth::PASSWORD_CANNOT_BE_VERIFIED;
            }
        }

        if (empty(trim($this->contactInfos['contact_ldap_dn']))) {
            return CentreonAuth::PASSWORD_CANNOT_BE_VERIFIED;
        }

        if ($this->debug) {
            $this->CentreonLog->insertLog(
                3,
                'LDAP AUTH : ' . $this->contactInfos['contact_ldap_dn'] . ' :: Authentication in progress'
            );
        }

        @ldap_bind($this->ds, $this->contactInfos['contact_ldap_dn'], $this->typePassword);

        if (empty($this->ds)) {
            if ($this->debug) {
                $this->CentreonLog->insertLog(3, "DS empty");
            }
            return CentreonAuth::PASSWORD_CANNOT_BE_VERIFIED;
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
        switch (ldap_errno($this->ds)) {
            case 0:
                if ($this->debug) {
                    $this->CentreonLog->insertLog(3, "LDAP AUTH : Success");
                }
                if (false == $this->updateUserDn()) {
                    return CentreonAuth::PASSWORD_INVALID;
                }
                return CentreonAuth::PASSWORD_VALID;
            case -1:
            case 2: // protocol error
            case 51: // busy
            case 52: // unavailable
            case 81: // server down
                if ($this->debug) {
                    $this->CentreonLog->insertLog(3, "LDAP AUTH : " . ldap_error($this->ds));
                }
                return CentreonAuth::PASSWORD_CANNOT_BE_VERIFIED;
            default:
                if ($this->debug) {
                    $this->CentreonLog->insertLog(3, "LDAP AUTH : " . ldap_error($this->ds));
                }
                return CentreonAuth::PASSWORD_INVALID;
        }
    }

    /**
     * Search and update the user dn at login
     *
     * @return bool If the DN is modified
     */
    public function updateUserDn()
    {
        $contactAlias = html_entity_decode($this->contactInfos['contact_alias'], ENT_QUOTES, 'UTF-8');

        if ($this->ldap->rebind()) {
            $userDn = $this->ldap->findUserDn($contactAlias);
            if (false === $userDn) {
                $this->CentreonLog->insertLog(3, "LDAP AUTH - Error : No DN for user " . $contactAlias);
                return false;
            }

            // Get ldap user information
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

            //getting user's email
            $userEmail = $this->contactInfos['contact_email'];
            if (
                isset($userInfos[$this->ldap->getAttrName('user', 'email')])
                && trim($userInfos[$this->ldap->getAttrName('user', 'email')]) != ''
            ) {
                if (is_array($userInfos[$this->ldap->getAttrName('user', 'email')])) {
                    // Get the first if there are multiple entries
                    if ($userInfos[$this->ldap->getAttrName('user', 'email')][0]) {
                        $userEmail = $userInfos[$this->ldap->getAttrName('user', 'email')][0];
                    }
                } elseif ($userInfos[$this->ldap->getAttrName('user', 'email')]) {
                    $userEmail = $userInfos[$this->ldap->getAttrName('user', 'email')];
                }
            }
            //getting user's pager
            $userPager = $this->contactInfos['contact_pager'];
            if (
                isset($userInfos[$this->ldap->getAttrName('user', 'pager')])
                && trim($userInfos[$this->ldap->getAttrName('user', 'pager')]) != ''
            ) {
                if (is_array($userInfos[$this->ldap->getAttrName('user', 'pager')])) {
                    // Get the first if there are multiple entries
                    if ($userInfos[$this->ldap->getAttrName('user', 'pager')][0]) {
                        $userPager = $userInfos[$this->ldap->getAttrName('user', 'pager')][0];
                    }
                } elseif ($userInfos[$this->ldap->getAttrName('user', 'pager')]) {
                    $userPager = $userInfos[$this->ldap->getAttrName('user', 'pager')];
                }
            }

            /**
             * Searching if the user already exist in the DB and updating OR adding him
             */
            if (isset($this->contactInfos['contact_id'])) {
                try {
                    // checking if the LDAP synchronization on login is enabled or needed
                    if (!$this->ldap->isSyncNeededAtLogin($this->arId, $this->contactInfos['contact_id'])) {
                        // skipping the update
                        return true;
                    }

                    $this->CentreonLog->insertLog(
                        3,
                        'LDAP AUTH : Updating user DN of ' . $userDisplay
                    );
                    $stmt = $this->pearDB->prepare(
                        'UPDATE contact SET
                        contact_ldap_dn = :userDn,
                        contact_name = :userDisplay,
                        contact_email = :userEmail,
                        contact_pager = :userPager,
                        ar_id = :arId
                        WHERE contact_id = :contactId'
                    );
                    $stmt->bindValue(':userDn', $userDn, \PDO::PARAM_STR);
                    $stmt->bindValue(':userDisplay', $userDisplay, \PDO::PARAM_STR);
                    $stmt->bindValue(':userEmail', $userEmail, \PDO::PARAM_STR);
                    $stmt->bindValue(':userPager', $userPager, \PDO::PARAM_STR);
                    $stmt->bindValue(':arId', $this->arId, \PDO::PARAM_INT);
                    $stmt->bindValue(':contactId', $this->contactInfos['contact_id'], \PDO::PARAM_INT);
                    $stmt->execute();
                } catch (\PDOException $e) {
                    $this->CentreonLog->insertLog(
                        3,
                        'LDAP AUTH - Error : when trying to update user : ' . $userDisplay
                    );
                    return false;
                }
                $this->contactInfos['contact_ldap_dn'] = $userDn;

                // Updating user's contactgroup relations from LDAP
                try {
                    include_once(realpath(__DIR__ .  '/centreonContactgroup.class.php'));
                    $cgs = new CentreonContactgroup($this->pearDB);
                    $cgs->syncWithLdap();
                } catch (\Exception $e) {
                    $this->CentreonLog->insertLog(
                        3,
                        'LDAP AUTH - Error : when updating ' . $userDisplay . '\'s ldap contactgroups'
                    );
                }

                $this->ldap->setUserCurrentSyncTime($this->contactInfos);
                $this->CentreonLog->insertLog(
                    3,
                    'LDAP AUTH : User DN updated for ' . $userDisplay
                );
                return true;
            } else {
                /**
                 * The current user wasn't found. Adding him to the DB
                 * First, searching if a contact template has been specified in the LDAP parameters
                 */
                $res = $this->pearDB->prepare(
                    "SELECT ari_value FROM `auth_ressource_info` a, `contact` c
                    WHERE a.`ari_name` = 'ldap_contact_tmpl'
                    AND a.ar_id = :arId
                    AND a.ari_value = c.contact_id"
                );
                try {
                    $res->bindValue(':arId', $this->arId, \PDO::PARAM_INT);
                    $res->execute();

                    $row = $res->fetch();
                    if (empty($row['ari_value'])) {
                        $this->CentreonLog->insertLog(3, "LDAP AUTH - Error : No contact template defined.");
                        return false;
                    }
                    $tmplId = $row['ari_value'];
                } catch (\PDOException $e) {
                    $this->CentreonLog->insertLog(
                        3,
                        'LDAP AUTH - Error : when trying to get LDAP data for : ' . $userDisplay
                    );
                    return false;
                }

                // Inserting the new user in the database
                $stmt = $this->pearDB->prepare(
                    "INSERT INTO contact
                    (contact_template_id, contact_alias, contact_name,
                    contact_auth_type, contact_ldap_dn, ar_id,
                    contact_email, contact_pager, contact_oreon,
                    contact_activate, contact_register, contact_enable_notifications)
                    VALUES
                    (:templateId, :contactAlias, :userDisplay,
                    'ldap', :userDn, :arId,
                    :userEmail, :userPager, '1',
                    '1', '1', '2')"
                );
                try {
                    $stmt->bindValue(':templateId', $tmplId, \PDO::PARAM_INT);
                    $stmt->bindValue(':contactAlias', $contactAlias, \PDO::PARAM_STR);
                    $stmt->bindValue(':userDisplay', $userDisplay, \PDO::PARAM_STR);
                    $stmt->bindValue(':userDn', $userDn, \PDO::PARAM_STR);
                    $stmt->bindValue(':arId', $this->arId, \PDO::PARAM_INT);
                    $stmt->bindValue(':userEmail', $userEmail, \PDO::PARAM_STR);
                    $stmt->bindValue(':userPager', $userPager, \PDO::PARAM_STR);
                    $stmt->execute();

                    // Retrieving the created contact_id
                    $res = $this->pearDB->prepare(
                        "SELECT contact_id FROM contact
                        WHERE contact_ldap_dn = :userDn"
                    );
                    $res->bindValue(':userDn', $userDn, \PDO::PARAM_STR);
                    $res->execute();
                    $row = $res->fetch();
                    $this->contactInfos['contact_id'] = $row['contact_id'];

                    /**
                     * Searching the user's affiliated contactgroups in the LDAP
                     */
                    $listGroup = $this->ldap->listGroupsForUser($userDn);
                    $listGroupStr = "";
                    foreach ($listGroup as $gName) {
                        if ($listGroupStr !== "") {
                            $listGroupStr .= ",";
                        }
                        $listGroupStr .= "'" . $gName . "'";
                    }
                    if ($listGroupStr === "") {
                        $listGroupStr = "''";
                    }
                    $res2 = $this->pearDB->query(
                        "SELECT cg_id FROM contactgroup
                        WHERE cg_name IN (" . $listGroupStr . ")"
                    );

                    // Inserting the relation between the LDAP's contactgroups and the user
                    $stmt = $this->pearDB->prepare(
                        "INSERT INTO contactgroup_contact_relation
                        (contactgroup_cg_id, contact_contact_id)
                        VALUES (:ldapCg, :contactId)"
                    );
                    while ($row2 = $res2->fetch()) {
                        $stmt->bindValue(':ldapCg', $row2['cg_id'], PDO::PARAM_INT);
                        $stmt->bindValue(':contactId', $this->contactInfos['contact_id'], \PDO::PARAM_INT);
                        $stmt->execute();
                    }
                    $this->ldap->setUserCurrentSyncTime($this->contactInfos);

                    $this->CentreonLog->insertLog(3, "LDAP AUTH - New user DN added : " . $contactAlias);

                    // Inserting the relation between the LDAP's default contactgroup and the user
                    // @returns true if everything goes well
                    return $this->ldap->addUserToLdapDefaultCg(
                        $this->arId,
                        $this->contactInfos['contact_id']
                    );
                } catch (\PDOException $e) {
                    $this->CentreonLog->insertLog(
                        3,
                        'LDAP AUTH - Error : processing new user ' . $userDisplay . ' from ldap id : ' . $this->arId
                    );
                }
            }
        }
        return false;
    }
}
