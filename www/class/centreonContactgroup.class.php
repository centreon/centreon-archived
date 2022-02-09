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

require_once realpath(__DIR__ . "/centreonLDAP.class.php");
require_once realpath(__DIR__ . "/centreonACL.class.php");

/**
 * Manage contactgroups
 */
class CentreonContactgroup
{
    private $db;

    /**
     * Constructor
     *
     * @param CentreonDB $pearDB
     */
    public function __construct($pearDB)
    {
        $this->db = $pearDB;
    }

    /**
     * Get the list of contactgroups with its id, or its name for a ldap groups if isn't sync in database
     *
     * @param bool $withLdap if include LDAP group
     * @param bool $dbOnly | will not return ldap groups that are not stored in db
     * @return array
     */
    public function getListContactgroup($withLdap = false, $dbOnly = false)
    {
        // Contactgroup from database
        $contactgroups = array();

        $query = "SELECT a.cg_id, a.cg_name, a.cg_ldap_dn, b.ar_name FROM contactgroup a ";
        $query .= " LEFT JOIN auth_ressource b ON a.ar_id = b.ar_id";
        if (false === $withLdap) {
            $query .= " WHERE a.cg_type != 'ldap'";
        }
        $query .= " ORDER BY a.cg_name";

        $res = $this->db->query($query);
        while ($contactgroup = $res->fetch()) {
            $contactgroups[$contactgroup["cg_id"]] = $contactgroup["cg_name"];
            if ($withLdap && isset($contactgroup['cg_ldap_dn']) && $contactgroup['cg_ldap_dn'] != "") {
                $contactgroups[$contactgroup["cg_id"]] = $this->formatLdapContactgroupName(
                    $contactgroup["cg_name"],
                    $contactgroup['ar_name']
                );
            }
        }
        $res->closeCursor();

        # Get ldap contactgroups
        if ($withLdap && $dbOnly === false) {
            $ldapContactgroups = $this->getLdapContactgroups();
            $contactgroupNames = array_values($contactgroups);
            foreach ($ldapContactgroups as $id => $name) {
                if (!in_array($name, $contactgroupNames)) {
                    $contactgroups[$id] = $name;
                }
            }
        }

        return $contactgroups;
    }

    /**
     * Get the list of ldap contactgroups
     *
     * @param string : $filter
     *
     * @return array : $cgs
     */
    public function getLdapContactgroups($filter = '')
    {
        $cgs = array();

        $query = "SELECT `value` FROM `options` WHERE `key` = 'ldap_auth_enable'";
        $res = $this->db->query($query);
        $row = $res->fetch();
        if ($row['value'] == 1) {
            $query = "SELECT ar_id, ar_name FROM auth_ressource WHERE ar_enable = '1'";
            $ldapRes = $this->db->query($query);
            while ($ldapRow = $ldapRes->fetch()) {
                $ldap = new CentreonLDAP($this->db, null, $ldapRow['ar_id']);
                $ldap->connect(null, $ldapRow['ar_id']);
                $ldapGroups = $ldap->listOfGroups();

                foreach ($ldapGroups as $ldapGroup) {
                    $ldapGroupName = $ldapGroup['name'];
                    if (
                        false === array_search($ldapGroupName . " (LDAP : " . $ldapRow['ar_name'] . ")", $cgs)
                        && preg_match('/' . $filter . '/i', $ldapGroupName)
                    ) {
                        $cgs["[" . $ldapRow['ar_id'] . "]" . $ldapGroupName] = $this->formatLdapContactgroupName(
                            $ldapGroupName,
                            $ldapRow['ar_name']
                        );
                    }
                }
            }
        }

        return $cgs;
    }

    /**
     * format ldap contactgroup name
     *
     * @param string $cg_name : name of the ldap group name
     * @param string $ldap_name : name of the ldap
     *
     * @return string
     */
    public function formatLdapContactgroupName($cg_name, $ldap_name)
    {
        return $cg_name . " (LDAP : " . $ldap_name . ")";
    }

    /**
     * find LDAP group id by name
     *
     * @param int $ldapId
     * @param string $name
     * @return int|null
     */
    private function findLdapGroupIdByName($ldapId, $name)
    {
        $ldapGroupId = null;

        $query = "SELECT cg_id "
            . "FROM contactgroup "
            . "WHERE cg_name = :name "
            . "AND ar_id = :ldapId";
        $statement = $this->db->prepare($query);
        $statement->bindValue(':name', $name, \PDO::PARAM_STR);
        $statement->bindValue(':ldapId', $ldapId, \PDO::PARAM_INT);
        $statement->execute();

        if ($row = $statement->fetch()) {
            $ldapGroupId = $row['cg_id'];
        }

        return $ldapGroupId;
    }

    /**
     * find LDAP group id by dn
     *
     * @param int $ldapId
     * @param string $dn
     * @return int
     */
    private function findLdapGroupIdByDn($ldapId, $dn)
    {
        $ldapGroupId = null;

        $query = "SELECT cg_id "
            . "FROM contactgroup "
            . "WHERE cg_ldap_dn = :dn "
            . "AND ar_id = :ldapId";
        $statement = $this->db->prepare($query);
        $statement->bindValue(':dn', $dn, \PDO::PARAM_STR);
        $statement->bindValue(':ldapId', $ldapId, \PDO::PARAM_INT);
        $statement->execute();

        if ($row = $statement->fetch()) {
            $ldapGroupId = $row['cg_id'];
        }

        return $ldapGroupId;
    }

    /**
     * Insert ldap group if does not exist
     *
     * @param int $ldapId
     * @param string $name
     * @param string $dn
     * @return int
     */
    public function insertLdapGroupByNameAndDn($ldapId, $name, $dn)
    {
        // Check if contactgroup is not in the database
        $ldapGroupId = $this->findLdapGroupIdByName($ldapId, $name);
        if ($ldapGroupId !== null) {
            return $ldapGroupId;
        }

        $query = "INSERT INTO contactgroup (cg_name, cg_alias, cg_activate, cg_type, cg_ldap_dn, ar_id) "
            . "VALUES (:name, :name, '1', 'ldap', :dn, :ldapId)";
        $statement = $this->db->prepare($query);
        $statement->bindValue(':name', $name, \PDO::PARAM_STR);
        $statement->bindValue(':dn', $dn, \PDO::PARAM_STR);
        $statement->bindValue(':ldapId', $ldapId, \PDO::PARAM_INT);
        $statement->execute();

        return $this->findLdapGroupIdByName($ldapId, $name);
    }

    /**
     * Insert the ldap groups in table contactgroups
     *
     * @param string $cgName The ldap group name
     * @return int The contactgroup id or null if not found
     */
    public function insertLdapGroup($cgName)
    {
        // Parse contactgroup name
        if (false === preg_match('/\[(\d+)\](.*)/', $cgName, $matches)) {
            return 0;
        }
        $arId = $matches[1];
        $cgName = $matches[2];

        // Check if contactgroup is not in the database
        $ldapGroupId = $this->findLdapGroupIdByName($arId, $cgName);
        if ($ldapGroupId !== null) {
            return $ldapGroupId;
        }

        $ldap = new CentreonLDAP($this->db, null, $arId);
        $ldap->connect();
        $ldapDn = $ldap->findGroupDn($cgName);

        // Reset ldap build cache time
        $this->db->query('UPDATE options SET `value` = 0 WHERE `key` = "ldap_last_acl_update"');

        if ($ldapDn !== false) {
            $this->insertLdapGroupByNameAndDn($arId, $cgName, $ldapDn);
            return $this->findLdapGroupIdByDn($arId, $ldapDn);
        }

        return null;
    }

    /**
     * Optimized method to get better performance at config generation when LDAP have groups
     * Useful to avoid calculating and refreshing configuration from LDAP when nothing has changed
     *
     * @return $msg array of error messages
     */
    public function syncWithLdapConfigGen()
    {
        $msg = array();
        $ldapServerConnError = array();

        $cgRes = $this->db->query("SELECT cg.cg_id, cg.cg_name, cg.cg_ldap_dn, cg.ar_id " .
            "FROM contactgroup as cg, auth_ressource as ar " .
            "WHERE cg.cg_type = 'ldap' AND cg.ar_id = ar.ar_id AND ar.ar_enable = '1' AND (" .
            "EXISTS(SELECT 1 FROM contactgroup_host_relation chr WHERE chr.contactgroup_cg_id = cg.cg_id LIMIT 1) "
            . " OR " .
            "EXISTS(SELECT 1 FROM contactgroup_service_relation csr WHERE csr.contactgroup_cg_id = cg.cg_id LIMIT 1)"
            . " OR " .
            "EXISTS(SELECT 1 FROM contactgroup_hostgroup_relation chr WHERE chr.contactgroup_cg_id = cg.cg_id LIMIT 1)"
            . " OR " .
            "EXISTS(SELECT 1 FROM contactgroup_servicegroup_relation csr " .
            "WHERE csr.contactgroup_cg_id = cg.cg_id LIMIT 1)"
            . " OR " .
            "EXISTS(SELECT 1 FROM escalation_contactgroup_relation ecr WHERE ecr.contactgroup_cg_id = cg.cg_id LIMIT 1)"
            . ") ORDER BY cg.ar_id");

        $currentLdapId = 0; // the chosen LDAP configuration which should never stay to 0 if the LDAP is found
        $ldapConn = null;
        while ($cgRow = $cgRes->fetch()) {
            if (isset($ldapServerConnError[$cgRow['ar_id']])) {
                continue;
            }
            // if $currentLdapId == cgRow['ar_id'], then nothing has changed and we can skip the next operations
            if ($currentLdapId != $cgRow['ar_id']) {
                $currentLdapId = $cgRow['ar_id'];
                if (!is_null($ldapConn)) {
                    $ldapConn->close();
                }
                $ldapConn = new CentreonLDAP($this->db, null, (int)$cgRow['ar_id']);
                $connectionResult = $ldapConn->connect();
                if ($connectionResult == false) {
                    $ldapServerConnError[$cgRow['ar_id']] = 1;
                    $stmt = $this->db->query("SELECT ar_name FROM auth_ressource " .
                        "WHERE ar_id = " . (int)$cgRow['ar_id']);
                    $res = $stmt->fetch();
                    $msg[] = "Unable to connect to LDAP server : " . $res['ar_name'] . ".";
                    continue;
                }
            }

            // Refresh Users Groups by deleting old relations and inserting new ones if needed.
            $this->db->query("DELETE FROM contactgroup_contact_relation " .
                "WHERE contactgroup_cg_id = " . (int)$cgRow['cg_id']);

            $members = $ldapConn->listUserForGroup($cgRow['cg_ldap_dn']);
            $contact = '';
            foreach ($members as $member) {
                $contact .= $this->db->quote($member) . ',';
            }
            $contact = rtrim($contact, ",");

            if (!$contact) {
                // no need to continue. If there's no contact, there's no relation to insert.
                $stmt = $this->db->query("SELECT ar_name FROM auth_ressource WHERE ar_id = " . (int)$cgRow['ar_id']);
                $res = $stmt->fetch();
                $msg[] = "Error : there's no contact to update for LDAP : " . $res['ar_name'] . ".";
                return $msg;
            }
            try {
                $resContact = $this->db->query("SELECT contact_id FROM contact " .
                    "WHERE contact_ldap_dn IN (" . $contact . ")");

                while ($rowContact = $resContact->fetch()) {
                    try {
                        // inserting the LDAP contactgroups relation between the cg and the user
                        $this->db->query("INSERT INTO contactgroup_contact_relation " .
                            "(contactgroup_cg_id, contact_contact_id) " .
                            "VALUES (" . (int)$cgRow['cg_id'] . ", " . (int)$rowContact['contact_id'] . ")");
                    } catch (\PDOException $e) {
                        $stmt = $this->db->query("SELECT c.contact_name, cg_name FROM contact c " .
                            "INNER JOIN contactgroup_contact_relation cgr ON cgr.contact_contact_id = c.contact_id " .
                            "INNER JOIN contactgroup cg ON cg.cg_id = cgr.contactgroup_cg_id");
                        $res = $stmt->fetch();
                        $msg[] = "Error inserting relation between contactgroup : " . $res['cg_name'] .
                            " and contact : " . $res['contact_name'] . ".";
                    }
                }
            } catch (\PDOException $e) {
                $msg[] = "Error in getting contact ID's list : " . $contact . " from members.";
                continue;
            }
        }

        return $msg;
    }

    /**
     * Synchronize with LDAP groups
     *
     * @return array | array of error messages
     */
    public function syncWithLdap()
    {
        $msg = array();
        $ldapRes = $this->db->query(
            "SELECT ar_id FROM auth_ressource WHERE ar_enable = '1'"
        );

        // Connect to LDAP Server
        while ($ldapRow = $ldapRes->fetch()) {
            $ldapConn = new CentreonLDAP($this->db, null, $ldapRow['ar_id']);
            $connectionResult = $ldapConn->connect();
            if (false != $connectionResult) {
                $res = $this->db->prepare(
                    "SELECT cg_id, cg_name, cg_ldap_dn FROM contactgroup " .
                    "WHERE cg_type = 'ldap' AND ar_id = :arId"
                );
                $res->bindValue(':arId', $ldapRow['ar_id'], \PDO::PARAM_INT);
                $res->execute();

                // insert groups from ldap into centreon
                $registeredGroupsFromDB = $res->fetchAll();
                $registeredGroups = [];
                foreach ($registeredGroupsFromDB as $registeredGroupFromDB) {
                    $registeredGroups[] = $registeredGroupFromDB['cg_name'];
                }

                $time = microtime(true);
                $ldapGroups = $ldapConn->listOfGroups();
                var_dump($ldapGroups);
                var_dump(microtime(true) - $time);

                foreach ($ldapGroups as $ldapGroup) {
                    if (!in_array($ldapGroup['name'], $registeredGroups)) {
                        $this->insertLdapGroupByNameAndDn($ldapRow['ar_id'], $ldapGroup['name'], $ldapGroup['dn']);
                    }
                }

                $res = $this->db->prepare(
                    "SELECT cg_id, cg_name, cg_ldap_dn FROM contactgroup " .
                    "WHERE cg_type = 'ldap' AND ar_id = :arId"
                );
                $res->bindValue(':arId', $ldapRow['ar_id'], \PDO::PARAM_INT);
                $res->execute();

                $this->db->beginTransaction();
                try {
                    while ($row = $res->fetch()) {
                        // Test is the group has not been moved or deleted in ldap
                        if ((empty($row['cg_ldap_dn']) || false === $ldapConn->getEntry($row['cg_ldap_dn']))
                            && ldap_errno($ldapConn->getDs()) != 3
                        ) {
                            $dn = $ldapConn->findGroupDn($row['cg_name']);
                            if (false === $dn && ldap_errno($ldapConn->getDs()) != 3) {
                                // Delete the ldap group in contactgroup
                                try {
                                    $stmt = $this->db->prepare(
                                        "DELETE FROM contactgroup WHERE cg_id = :cgId"
                                    );
                                    $stmt->bindValue('cgId', $row['cg_id'], \PDO::PARAM_INT);
                                    $stmt->execute();
                                } catch (\PDOException $e) {
                                    $msg[] = "Error processing delete contactgroup request of ldap group : " .
                                        $row['cg_name'];
                                    throw $e;
                                }
                                continue;
                            } else {
                                // Update the ldap group in contactgroup
                                $queryUpdateDn = "UPDATE contactgroup SET cg_ldap_dn = '" . $dn .
                                    "' WHERE cg_id = " . $row['cg_id'];
                                try {
                                    $this->db->query($queryUpdateDn);
                                    $row['cg_ldap_dn'] = $dn;
                                } catch (\PDOException $e) {
                                    $msg[] = "Error processing update contactgroup request of ldap group : " .
                                        $row['cg_name'];
                                    throw $e;
                                    continue;
                                }
                            }
                        }
                        $members = $ldapConn->listUserForGroup($row['cg_ldap_dn']);

                        // Refresh Users Groups.
                        $deleteStmt = $this->db->prepare(
                            "DELETE FROM contactgroup_contact_relation
                            WHERE contactgroup_cg_id = :cgId"
                        );
                        $deleteStmt->bindValue(':cgId', $row['cg_id'], \PDO::PARAM_INT);
                        $deleteStmt->execute();
                        $contact = '';
                        foreach ($members as $member) {
                            $contact .= $this->db->quote($member) . ',';
                        }
                        $contact = rtrim($contact, ",");

                        if ($contact !== '') {
                            try {
                                $resContact = $this->db->query(
                                    "SELECT contact_id FROM contact WHERE contact_ldap_dn IN (" . $contact . ")"
                                );
                            } catch (\PDOException $e) {
                                $msg[] = "Error in getting contact id from members.";
                                throw $e;
                                continue;
                            }
                            while ($rowContact = $resContact->fetch()) {
                                try {
                                    $insertStmt = $this->db->prepare(
                                        "INSERT INTO contactgroup_contact_relation
                                        (contactgroup_cg_id, contact_contact_id)
                                        VALUES (:cgId, :contactId)"
                                    );
                                    $insertStmt->bindValue(':cgId', $row['cg_id'], \PDO::PARAM_INT);
                                    $insertStmt->bindValue(':contactId', $rowContact['contact_id'], \PDO::PARAM_INT);
                                    $insertStmt->execute();
                                } catch (\PDOException $e) {
                                    $msg[] = "Error insert relation between contactgroup " . $row['cg_id'] .
                                        " and contact " . $rowContact['contact_id'];
                                    throw $e;
                                }
                            }
                        }
                    }
                    $updateTime = $this->db->prepare(
                        "UPDATE `options` SET `value` = :currentTime
                        WHERE `key` = 'ldap_last_acl_update'"
                    );
                    $updateTime->bindValue(':currentTime', time(), \PDO::PARAM_INT);
                    $updateTime->execute();
                    $this->db->commit();
                } catch (\PDOException $e) {
                    $this->db->rollBack();
                }
            } else {
                $msg[] = "Unable to connect to LDAP server.";
            }
        }
        return $msg;
    }

    /**
     * Get contact group name from contact group id
     *
     * @param int $cgId : The id of the contactgroup
     * @return string
     * @throws Exception
     */
    public function getNameFromCgId($cgId)
    {
        $query = "SELECT cg_name FROM contactgroup WHERE cg_id = " . CentreonDB::escape($cgId) . " LIMIT 1";
        $res = $this->db->query($query);
        if ($res->rowCount()) {
            $row = $res->fetch();
            return $row['cg_name'];
        } else {
            throw new \Exception('No contact group name found');
        }
    }

    /**
     * Verify if ldap contactgroup as not the same name of a Centreon contactgroup
     *
     * @param array $listCgs The list of contactgroups to validate
     * @return boolean
     */
    public static function verifiedExists($listCgs)
    {
        global $pearDB;
        foreach ($listCgs as $cg) {
            if (false === is_numeric($cg)) {
                // Parse the name
                if (false === preg_match('/\[(\d+)\](.*)/', $cg, $matches)) {
                    return false;
                }
                $cg_name = $matches[2];

                // Query test if exists
                $query = "SELECT COUNT(*) as nb FROM contactgroup " .
                    "WHERE cg_name = '" . $pearDB->escape($cg_name) . "' AND cg_type != 'ldap' ";
                try {
                    $res = $pearDB->query($query);
                } catch (\PDOException $e) {
                    return false;
                }
                $row = $res->fetch();
                if ($row['nb'] != 0) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     *
     * @param integer $field
     * @return array
     */
    public static function getDefaultValuesParameters($field)
    {
        $parameters = array();
        $parameters['currentObject']['table'] = 'contactgroup';
        $parameters['currentObject']['id'] = 'cg_id';
        $parameters['currentObject']['name'] = 'cg_name';
        $parameters['currentObject']['comparator'] = 'cg_id';

        switch ($field) {
            case 'cg_contacts':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'contact';
                $parameters['externalObject']['id'] = 'contact_id';
                $parameters['externalObject']['name'] = 'contact_name';
                $parameters['externalObject']['comparator'] = 'contact_id';
                $parameters['relationObject']['table'] = 'contactgroup_contact_relation';
                $parameters['relationObject']['field'] = 'contact_contact_id';
                $parameters['relationObject']['comparator'] = 'contactgroup_cg_id';
                break;
            case 'cg_acl_groups':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'acl_groups';
                $parameters['externalObject']['id'] = 'acl_group_id';
                $parameters['externalObject']['name'] = 'acl_group_name';
                $parameters['externalObject']['comparator'] = 'acl_group_id';
                $parameters['relationObject']['table'] = 'acl_group_contactgroups_relations';
                $parameters['relationObject']['field'] = 'acl_group_id';
                $parameters['relationObject']['comparator'] = 'cg_cg_id';
                break;
        }

        return $parameters;
    }

    /**
     * @param array $values
     * @param array $options
     * @return array
     */
    public function getObjectForSelect2($values = array(), $options = array())
    {
        global $centreon;
        $items = array();

        # get list of authorized contactgroups
        if (!$centreon->user->access->admin) {
            $cgAcl = $centreon->user->access->getContactGroupAclConf(
                array(
                    'fields' => array('cg_id'),
                    'get_row' => 'cg_id',
                    'keys' => array('cg_id'),
                    'conditions' => array(
                        'cg_id' => array(
                            'IN',
                            $values
                        )
                    )
                ),
                false
            );
        }

        $aElement = array();
        if (is_array($values)) {
            foreach ($values as $value) {
                if (preg_match_all('/\[(\w+)\]/', $value, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        if (!in_array($match[1], $aElement)) {
                            $aElement[] = $match[1];
                        }
                    }
                } else {
                    if (!in_array($value, $aElement)) {
                        $aElement[] = $value;
                    }
                }
            }
        }

        $listValues = '';
        $queryValues = array();
        if (!empty($aElement)) {
            foreach ($aElement as $k => $v) {
                $listValues .= ':cg' . $v . ',';
                $queryValues['cg' . $v] = (int)$v;
            }
            $listValues = rtrim($listValues, ',');
        } else {
            $listValues .= '""';
        }

        # get list of selected contactgroups
        $query = "SELECT cg.cg_id, cg.cg_name, cg.cg_ldap_dn, ar.ar_id, ar.ar_name FROM contactgroup cg " .
            "LEFT JOIN auth_ressource ar ON cg.ar_id = ar.ar_id " .
            "WHERE cg.cg_id IN (" . $listValues . ") ORDER BY cg.cg_name ";

        $stmt = $this->db->prepare($query);
        foreach ($queryValues as $key => $id) {
            $stmt->bindValue(':' . $key, $id, PDO::PARAM_INT);
        }
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            if (isset($row['cg_ldap_dn']) && $row['cg_ldap_dn'] != "") {
                $cgName = $this->formatLdapContactgroupName($row['cg_name'], $row['ar_name']);
            } else {
                $cgName = $row['cg_name'];
            }
            $cgId = $row['cg_id'];

            # hide unauthorized contactgroups
            $hide = false;
            if (!$centreon->user->access->admin && !in_array($cgId, $cgAcl)) {
                $hide = true;
            }

            $items[] = array(
                'id' => $cgId,
                'text' => $cgName,
                'hide' => $hide
            );
        }

        return $items;
    }
}
