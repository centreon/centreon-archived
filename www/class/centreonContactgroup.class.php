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

require_once realpath(dirname(__FILE__) . "/centreonLDAP.class.php");
require_once realpath(dirname(__FILE__) . "/centreonACL.class.php");

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
     * Get the list of contactgroups with his id, or his name for a ldap groups if is not sync in database
     *
     * @param bool $withLdap if include LDAP group
     * @param bool $dbOnly | will not return ldap groups that are not stored in db
     * @return array
     */
    public function getListContactgroup($withLdap = false, $dbOnly = false)
    {
        /* Contactgroup from database */
        $contactgroups = array();

        $query = "SELECT a.cg_id, a.cg_name, a.cg_ldap_dn, b.ar_name FROM contactgroup a ";
        $query .= " LEFT JOIN auth_ressource b ON a.ar_id = b.ar_id";
        if (false === $withLdap) {
            $query .= " WHERE a.cg_type != 'ldap'";
        }
        $query .= " ORDER BY a.cg_name";

        $res = $this->db->query($query);
        while ($contactgroup = $res->fetchRow()) {
            $contactgroups[$contactgroup["cg_id"]] = $contactgroup["cg_name"];
            if ($withLdap && isset($contactgroup['cg_ldap_dn']) && $contactgroup['cg_ldap_dn'] != "") {
                $contactgroups[$contactgroup["cg_id"]] = $this->formatLdapContactgroupName(
                    $contactgroup["cg_name"],
                    $contactgroup['ar_name']
                );
            }
        }
        $res->free();

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
     * @return array
     */
    public function getLdapContactgroups($filter = '')
    {
        $cgs = array();

        $query = "SELECT `value` FROM `options` WHERE `key` = 'ldap_auth_enable'";
        $res = $this->db->query($query);
        $row = $res->fetchRow();
        if ($row['value'] == 1) {
            $query = "SELECT ar_id, ar_name FROM auth_ressource WHERE ar_enable = '1'";
            $ldapres = $this->db->query($query);
            while ($ldaprow = $ldapres->fetchRow()) {
                $ldap = new CentreonLDAP($this->db, null, $ldaprow['ar_id']);
                $ldap->connect(null, $ldaprow['ar_id']);
                $cg_ldap = $ldap->listOfGroups();

                foreach ($cg_ldap as $cg_name) {
                    if (false === array_search($cg_name . " (LDAP : " . $ldaprow['ar_name'] . ")", $cgs) &&
                        preg_match('/' . $filter . '/i', $cg_name)) {
                        $cgs["[" . $ldaprow['ar_id'] . "]" . $cg_name] = $this->formatLdapContactgroupName(
                            $cg_name,
                            $ldaprow['ar_name']
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
     * @return string
     */
    public function formatLdapContactgroupName($cg_name, $ldap_name)
    {
        return $cg_name . " (LDAP : " . $ldap_name . ")";
    }

    /**
     * Insert the ldap groups in table contactgroups
     *
     * @param string $cg_name The ldap group name
     * @return int The contactgroup id or 0 if error
     */
    public function insertLdapGroup($cg_name)
    {
        /*
         * Parse contactgroup name
         */
        if (false === preg_match('/\[(\d+)\](.*)/', $cg_name, $matches)) {
            return 0;
        }
        $ar_id = $matches[1];
        $cg_name = $matches[2];
        /*
         * Check if contactgroup is not in databas
         */
        $queryCheck = "SELECT cg_id FROM contactgroup
            WHERE cg_name = '" . $this->db->escape($cg_name) . "'";
        $res = $this->db->query($queryCheck);
        if ($res->numRows() == 1) {
            $row = $res->fetchRow();
            return $row['cg_id'];
        }
        $ldap = new CentreonLDAP($this->db, null, $ar_id);
        $ldap->connect();
        $ldap_dn = $ldap->findGroupDn($cg_name);
        $query = "INSERT INTO contactgroup
        	(cg_name, cg_alias, cg_activate, cg_type, cg_ldap_dn, ar_id)
        	VALUES
        	('" . $this->db->escape($cg_name) . "', '" . $this->db->escape($cg_name) . "', '1', 'ldap', '" .
                $this->db->escape($ldap_dn) . "', " . CentreonDB::escape($ar_id) . ")";
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return 0;
        }
        $query = "SELECT cg_id FROM contactgroup
            WHERE cg_ldap_dn = '" . $this->db->escape($ldap_dn) . "' AND ar_id = " . CentreonDB::escape($ar_id);
        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return 0;
        }
        $row = $res->fetchRow();
        /*
         * Reset ldap build cache time
         */
        $queryCacheLdap = 'UPDATE options
            SET `value` = 0
            WHERE `key` = "ldap_last_acl_update"';
        $this->db->query($queryCacheLdap);
        return $row['cg_id'];
    }

    /**
     * Synchronize with LDAP groups
     *
     * @return array | array of error messages
     */
    public function syncWithLdap()
    {
        $query = "SELECT ar_id FROM auth_ressource WHERE ar_enable = '1'";
        $ldapres = $this->db->query($query);

        $msg = array();
        
        /*
         * Connect to LDAP Server
         */
        while ($ldaprow = $ldapres->fetchRow()) {
            $ldapConn = new CentreonLDAP($this->db, null, $ldaprow['ar_id']);
            $connectionResult = $ldapConn->connect();
            if (false != $connectionResult) {
                $res = $this->db->query("SELECT cg_id, cg_name, cg_ldap_dn FROM contactgroup
                    WHERE cg_type = 'ldap' AND ar_id = " . $ldaprow['ar_id']);
                while ($row = $res->fetchRow()) {
                    /*
                     * Test is the group a not move or delete in ldap
                     */
                    if (empty($row['cg_ldap_dn']) || false === $ldapConn->getEntry($row['cg_ldap_dn'])) {
                        $dn = $ldapConn->findGroupDn($row['cg_name']);
                        if (false === $dn) {
                            /*
                             * Delete the ldap group in contactgroup
                             */
                            $queryDelete = "DELETE FROM contactgroup WHERE cg_id = " . $row['cg_id'];
                            if (PEAR::isError($this->db->query($queryDelete))) {
                                $msg[] = "Error in delete contactgroup for ldap group : " . $row['cg_name'];
                            }
                            continue;
                        } else {
                            /*
                             * Update the ldap group in contactgroup
                             */
                            $queryUpdateDn = "UPDATE contactgroup SET cg_ldap_dn = '" . $dn . "'
                                WHERE cg_id = " . $row['cg_id'];
                            if (PEAR::isError($this->db->query($queryUpdateDn))) {
                                $msg[] = "Error in update contactgroup for ldap group : " . $row['cg_name'];
                                continue;
                            } else {
                                $row['cg_ldap_dn'] = $dn;
                            }
                        }
                    }
                    $members = $ldapConn->listUserForGroup($row['cg_ldap_dn']);

                    /*
                     * Refresh Users Groups.
                     */
                    $queryDeleteRelation = "DELETE FROM contactgroup_contact_relation
                        WHERE contactgroup_cg_id = " . $row['cg_id'];
                    $this->db->query($queryDeleteRelation);
                    $queryContact = "SELECT contact_id FROM contact
                        WHERE contact_ldap_dn IN ('" .
                            join("', '", array_map('mysql_real_escape_string', $members)) . "')";
                    $resContact = $this->db->query($queryContact);
                    if (PEAR::isError($resContact)) {
                        $msg[] = "Error in getting contact id form members.";
                        continue;
                    }
                    while ($rowContact = $resContact->fetchRow()) {
                        $queryAddRelation = "INSERT INTO contactgroup_contact_relation
                            (contactgroup_cg_id, contact_contact_id)
            	            VALUES (" . $row['cg_id'] . ", " . $rowContact['contact_id'] . ")";
                        if (PEAR::isError($this->db->query($queryAddRelation))) {
                            $msg[] ="Error insert relation between contactgroup " . $row['cg_id'] .
                                " and contact " . $rowContact['contact_id'];
                        }
                    }
                }
                $queryUpdateTime = "UPDATE `options` SET `value` = '" . time() . "'
                    WHERE `key` = 'ldap_last_acl_update'";
                $this->db->query($queryUpdateTime);
            } else {
                $msg[] = "Unable to connect to LDAP server.";
            }
        }
        return $msg;
    }
    
    /**
     * Get contact group name from contact group id
     *
     * @param int $cgId
     * @return string
     * @throws Exception
     */
    public function getNameFromCgId($cgId)
    {
        $query = "SELECT cg_name FROM contactgroup WHERE cg_id = " . CentreonDB::escape($cgId) . " LIMIT 1";
        $res = $this->db->query($query);
        if ($res->numRows()) {
            $row = $res->fetchRow();
            return $row['cg_name'];
        } else {
            throw Exception('No contact group name found');
        }
    }
    
    /**
     * Verified if ldap contactgroup as not the same name of a Centreon contactgroup
     *
     * @param array $listCgs The list of contactgroups to validate
     * @return boolean
     */
    public static function verifiedExists($listCgs)
    {
        global $pearDB;
        foreach ($listCgs as $cg) {
            if (false === is_numeric($cg)) {
                /* Parse the name */
                if (false === preg_match('/\[(\d+)\](.*)/', $cg, $matches)) {
                    return false;
                }
                $ar_id = $matches[1];
                $cg_name = $matches[2];

                /* Query test if exists */
                $query = "SELECT COUNT(*) as nb FROM contactgroup
                    WHERE cg_name = '" . $pearDB->escape($cg_name) ."' AND cg_type != 'ldap' ";
                $res = $pearDB->query($query);
                if (PEAR::isError($res)) {
                    return false;
                }
                $row = $res->fetchRow();
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
     *
     * @param type $values
     * @return type
     */
    public function getObjectForSelect2($values = array(), $options = array())
    {
        global $centreon;
        $items = array();

        # get list of authorized contactgroups
        if (!$centreon->user->access->admin) {
            $cgAcl = $centreon->user->access->getContactGroupAclConf(
                array(
                    'fields'  => array('cg_id'),
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

        $explodedValues = implode(',', $aElement);
        if (empty($explodedValues)) {
            $explodedValues = "''";
        }

        # get list of selected contactgroups
        $query = "SELECT cg.cg_id, cg.cg_name, cg.cg_ldap_dn, ar.ar_id, ar.ar_name FROM contactgroup cg "
            . "LEFT JOIN auth_ressource ar ON cg.ar_id = ar.ar_id "
            . "WHERE cg.cg_id IN (" . $explodedValues . ") "
            . "ORDER BY cg.cg_name ";

        $resRetrieval = $this->db->query($query);
        while ($row = $resRetrieval->fetchRow()) {
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
