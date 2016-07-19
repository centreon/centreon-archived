<?php
/*
 * Copyright 2005-2013 Centreon
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
        $res = $this->db->query("SELECT {$fieldStr} 
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
     *
     * @param type $values
     * @return type
     */
    public function getObjectForSelect2($values = array(), $options = array())
    {
        global $centreon;
        $items = array();

        # get list of authorized contacts
        if (!$centreon->user->access->admin) {
            $cAcl = $centreon->user->access->getContactAclConf(
                array(
                    'fields'  => array('contact_id'),
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

        $explodedValues = implode(',', $values);
        if (empty($explodedValues)) {
            $explodedValues = "''";
        }

        # get list of selected contacts
        $query = "SELECT contact_id, contact_name "
            . "FROM contact "
            . "WHERE contact_id IN (" . $explodedValues. ") "
            . "ORDER BY contact_name ";

        $resRetrieval = $this->db->query($query);
        while ($row = $resRetrieval->fetchRow()) {
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
}
