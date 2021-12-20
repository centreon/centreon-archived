<?php

/*
 * Copyright 2005-2021 Centreon
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

class CentreonContact
{
    protected $db;

    /**
     * Constructor
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Get contact templates
     *
     * @param array $fields | columns to return
     * @param array $filters
     * @param array $order | i.e: array('contact_name', 'ASC')
     * @param array $limit | i.e: array($limit, $offset)
     * @return array
     */
    public function getContactTemplates($fields = array(), $filters = array(), $order = array(), $limit = array())
    {
        $fieldStr = "*";
        if (count($fields)) {
            $fieldStr = implode(', ', $fields);
        }
        $filterStr = " WHERE contact_register = '0' ";
        foreach ($filters as $k => $v) {
            $filterStr .= " AND {$k} LIKE '{$this->db->escape($v)}' ";
        }
        $orderStr = "";
        if (count($order) === 2) {
            $orderStr = " ORDER BY {$order[0]} {$order[1]} ";
        }
        $limitStr = "";
        if (count($limit) === 2) {
            $limitStr = " LIMIT {$limit[0]},{$limit[1]}";
        }
        $res = $this->db->query("SELECT SQL_CALC_FOUND_ROWS {$fieldStr} 
                                FROM contact 
                                {$filterStr}
                                {$orderStr}
                                {$limitStr}");
        $arr = array();
        while ($row = $res->fetchRow()) {
            $arr[] = $row;
        }
        return $arr;
    }

    /**
     * Get contactgroup from contact id
     *
     * @param CentreonDB $db
     * @param int $contactId
     */
    public static function getContactGroupsFromContact($db, $contactId)
    {
        $sql = "SELECT cg_id, cg_name
            FROM contactgroup_contact_relation r, contactgroup cg 
            WHERE cg.cg_id = r.contactgroup_cg_id
            AND r.contact_contact_id = " . $db->escape($contactId);
        $stmt = $db->query($sql);

        $cgs = array();
        while ($row = $stmt->fetchRow()) {
            $cgs[$row['cg_id']] = $row['cg_name'];
        }
        return $cgs;
    }

    /**
     *
     * @param integer $field
     * @return array
     */
    public static function getDefaultValuesParameters($field)
    {
        $parameters = array();
        $parameters['currentObject']['table'] = 'contact';
        $parameters['currentObject']['id'] = 'contact_id';
        $parameters['currentObject']['name'] = 'contact_name';
        $parameters['currentObject']['comparator'] = 'contact_id';

        switch ($field) {
            case 'timeperiod_tp_id':
            case 'timeperiod_tp_id2':
                $parameters['type'] = 'simple';
                $parameters['externalObject']['table'] = 'timeperiod';
                $parameters['externalObject']['id'] = 'tp_id';
                $parameters['externalObject']['name'] = 'tp_name';
                $parameters['externalObject']['comparator'] = 'tp_id';
                break;
            case 'contact_hostNotifCmds':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'command';
                $parameters['externalObject']['id'] = 'command_id';
                $parameters['externalObject']['name'] = 'command_name';
                $parameters['externalObject']['comparator'] = 'command_id';
                $parameters['relationObject']['table'] = 'contact_hostcommands_relation';
                $parameters['relationObject']['field'] = 'command_command_id';
                $parameters['relationObject']['comparator'] = 'contact_contact_id';
                break;
            case 'contact_svNotifCmds':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'command';
                $parameters['externalObject']['id'] = 'command_id';
                $parameters['externalObject']['name'] = 'command_name';
                $parameters['externalObject']['comparator'] = 'command_id';
                $parameters['relationObject']['table'] = 'contact_servicecommands_relation';
                $parameters['relationObject']['field'] = 'command_command_id';
                $parameters['relationObject']['comparator'] = 'contact_contact_id';
                break;
            case 'contact_cgNotif':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'contactgroup';
                $parameters['externalObject']['id'] = 'cg_id';
                $parameters['externalObject']['name'] = 'cg_name';
                $parameters['externalObject']['comparator'] = 'cg_id';
                $parameters['relationObject']['table'] = 'contactgroup_contact_relation';
                $parameters['relationObject']['field'] = 'contactgroup_cg_id';
                $parameters['relationObject']['comparator'] = 'contact_contact_id';
                break;
            case 'contact_location':
                $parameters['type'] = 'simple';
                $parameters['externalObject']['table'] = 'timezone';
                $parameters['externalObject']['id'] = 'timezone_id';
                $parameters['externalObject']['name'] = 'timezone_name';
                $parameters['externalObject']['comparator'] = 'timezone_id';
                break;
            case 'contact_acl_groups':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'acl_groups';
                $parameters['externalObject']['id'] = 'acl_group_id';
                $parameters['externalObject']['name'] = 'acl_group_name';
                $parameters['externalObject']['comparator'] = 'acl_group_id';
                $parameters['relationObject']['table'] = 'acl_group_contacts_relations';
                $parameters['relationObject']['field'] = 'acl_group_id';
                $parameters['relationObject']['comparator'] = 'contact_contact_id';
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

        # get list of authorized contacts
        if (!$centreon->user->access->admin) {
            $cAcl = $centreon->user->access->getContactAclConf(
                array(
                    'fields' => array('contact_id'),
                    'get_row' => 'contact_id',
                    'keys' => array('contact_id'),
                    'conditions' => array(
                        'contact_id' => array(
                            'IN',
                            $values
                        )
                    )
                ),
                false
            );
        }

        $listValues = '';
        $queryValues = array();
        if (!empty($values)) {
            foreach ($values as $k => $v) {
                $listValues .= ':contact' . $v . ',';
                $queryValues['contact' . $v] = (int)$v;
            }
            $listValues = rtrim($listValues, ',');
        } else {
            $listValues .= '""';
        }

        # get list of selected contacts
        $query = "SELECT contact_id, contact_name FROM contact " .
            "WHERE contact_id IN (" . $listValues . ") ORDER BY contact_name ";

        $stmt = $this->db->prepare($query);

        if (!empty($queryValues)) {
            foreach ($queryValues as $key => $id) {
                $stmt->bindValue(':' . $key, $id, PDO::PARAM_INT);
            }
        }
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            # hide unauthorized contacts
            $hide = false;
            if (!$centreon->user->access->admin && !in_array($row['contact_id'], $cAcl)) {
                $hide = true;
            }

            $items[] = array(
                'id' => $row['contact_id'],
                'text' => $row['contact_name'],
                'hide' => $hide
            );
        }
        return $items;
    }

    /**
     * Insert contact password
     *
     * @param int $contactId
     * @param string $hashedPassword
     * @return void
     * @throws \PDOException
     */
    public function insertPasswordByContactId(int $contactId, string $hashedPassword): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO `contact_password` (`password`, `contact_id`, `creation_date`)
            VALUES (:password, :contactId, :creationDate)'
        );
        $statement->bindValue(':password', $hashedPassword, \PDO::PARAM_STR);
        $statement->bindValue(':contactId', $contactId, \PDO::PARAM_INT);
        $statement->bindValue(':creationDate', time(), \PDO::PARAM_INT);
        $statement->execute();

        $this->deleteOldPasswords($contactId);
    }

    /**
     * Replace stored password for a contact
     *
     * @param int $contactId
     * @param string $oldHashedPassword
     * @param string $newHashedPassword
     * @return void
     * @throws \PDOException
     */
    public function replacePasswordByContactId(
        int $contactId,
        string $oldHashedPassword,
        string $newHashedPassword
    ): void {
        $statement = $this->db->prepare(
            'UPDATE `contact_password`
            SET password = :newPassword
            WHERE contact_id = :contactId
            AND password = :oldPassword'
        );
        $statement->bindValue(':oldPassword', $oldHashedPassword, \PDO::PARAM_STR);
        $statement->bindValue(':newPassword', $newHashedPassword, \PDO::PARAM_STR);
        $statement->bindValue(':contactId', $contactId, \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Update contact password
     *
     * @param int $contactId
     * @param string $hashedPassword
     * @return void
     * @throws \PDOException
     */
    public function updatePasswordByContactId(int $contactId, string $hashedPassword): void
    {
        $this->insertPasswordByContactId($contactId, $hashedPassword);

        $this->deleteOldPasswords($contactId);
    }

    /**
     * Delete old passwords to store only 3 last passwords
     *
     * @param int $contactId
     * @return void
     * @throws \PDOException
     */
    private function deleteOldPasswords(int $contactId): void
    {
        $statement = $this->db->prepare(
            'SELECT creation_date
            FROM `contact_password`
            WHERE `contact_id` = :contactId
            ORDER BY `creation_date` DESC'
        );
        $statement->bindValue(':contactId', $contactId, \PDO::PARAM_INT);
        $statement->execute();

        //If 3 or more passwords are saved, delete the oldest ones.
        if (($result = $statement->fetchAll()) && count($result) > 3) {
            $maxCreationDateToDelete = $result[3]['creation_date'];
            $statement = $this->db->prepare(
                'DELETE FROM `contact_password`
                WHERE contact_id = :contactId
                AND creation_date <= :creationDate'
            );
            $statement->bindValue(':contactId', $contactId, \PDO::PARAM_INT);
            $statement->bindValue(':creationDate', $maxCreationDateToDelete, \PDO::PARAM_INT);
            $statement->execute();
        }
    }
}
