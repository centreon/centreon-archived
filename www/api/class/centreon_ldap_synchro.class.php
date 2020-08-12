<?php
/**
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

require_once _CENTREON_PATH_ . "/www/class/centreonLog.class.php";

/**
 * Webservice to request LDAP data synchronization for a selected contact.
 */
class CentreonLdapSynchro extends CentreonWebService
{
    /**
     * @var CentreonDB
     */
    protected $pearDB;

    /**
     * @var CentreonLog
     */
    protected $centreonLog;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->pearDB = new CentreonDB();
        $this->centreonLog = new CentreonLog();
    }

    /**
     * Used to request a data synchronization of a contact from the contact page
     * Using the contact ID or the session PHPSESSID value
     *
     * Each instance of Centreon using this contact account will be killed
     *
     * Method POST
     *
     * @return boolean
     */
    public function postRequestLdapSynchro(): bool
    {
        $result = false;

        $contactId = filter_var(
            $_POST['contactId'] ?? false,
            FILTER_VALIDATE_INT
        );

        if (!$this->isLdapEnabled()) {
            return $result;
        }

        if ($contactId === false) {
            $this->centreonLog->insertLog(
                3, //ldap.log
                "LDAP MANUAL SYNC : Error - Chosen contact id is not consistent."
            );
            return $result;
        }

        $this->pearDB->beginTransaction();
        try {
            $resUser = $this->pearDB->prepare(
                'SELECT `contact_id`, `contact_name` FROM `contact`
                WHERE `contact_id` = :contactId'
            );
            $resUser->bindValue(':contactId', $contactId, PDO::PARAM_INT);
            $resUser->execute();
            $contact = $resUser->fetch();

            // requiring a manual synchronization at next login of the contact
            $stmtRequiredSync = $this->pearDB->prepare(
                'UPDATE contact
                SET `contact_ldap_required_sync` = "1"
                WHERE contact_id = :contactId'
            );
            $stmtRequiredSync->bindValue(':contactId', $contact['contact_id'], PDO::PARAM_INT);
            $stmtRequiredSync->execute();

            // checking if the contact is currently connected to Centreon
            $activeSession = $this->pearDB->prepare(
                "SELECT session_id FROM `session` WHERE user_id = :contactId"
            );
            $activeSession->bindValue(':contactId', $contact['contact_id'], PDO::PARAM_INT);
            $activeSession->execute();

            //disconnecting every session using this contact data
            $logoutContact = $this->pearDB->prepare(
                "DELETE FROM session WHERE session_id = :userSessionId"
            );
            while ($rowSession = $activeSession->fetch()) {
                $logoutContact->bindValue(':userSessionId', $rowSession['session_id'], PDO::PARAM_STR);
                $logoutContact->execute();
            }
            $this->pearDB->commit();
            $this->centreonLog->insertLog(
                3,
                "LDAP MANUAL SYNC : Successfully planned LDAP synchronization for " . $contact['contact_name']
            );
            $result = true;
        } catch (PDOException $e) {
            $this->centreonLog->insertLog(
                2, //sql-error.log
                "LDAP MANUAL SYNC : Error - unable to read or update the contact data in the DB."
            );
            $this->pearDB->rollBack();
        }
        return $result;
    }

    /**
     * Checking if LDAP is enabled
     *
     * @return boolean
     */
    private function isLdapEnabled()
    {
        // checking if at least one LDAP configuration is still enabled
        $ldapEnable = $this->pearDB->query(
            "SELECT `value` FROM `options` WHERE `key` = 'ldap_auth_enable'"
        );
        $row = $ldapEnable->fetch();
        if ($row['value'] !== '1') {
            $this->centreonLog->insertLog(
                3,
                "LDAP MANUAL SYNC : Error - No enabled LDAP configuration found."
            );
            return false;
        }
        return true;
    }
}
