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

 /**
  *
  * Servicegroups objects
  *
  */
class CentreonServicegroups
{
    /**
     *
     * @var type
     */
    private $DB;
    
    /**
     *
     * @var type
     */
    private $relationCache;
    
    /**
     *
     * @var type
     */
    private $dataTree;

    /**
     *
     * Constructor
     * @param $pearDB
     */
    public function __construct($pearDB)
    {
        $this->DB = $pearDB;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $sg_id
     */
    public function getServiceGroupServices($sg_id = null)
    {
        if (!$sg_id) {
            return;
        }

        $services = array();
        $query = "SELECT host_host_id, service_service_id "
            . "FROM servicegroup_relation "
            . "WHERE servicegroup_sg_id = " . $sg_id . " "
            . "AND host_host_id IS NOT NULL "
            . "UNION "
            . "SELECT hgr.host_host_id, hsr.service_service_id "
            . "FROM servicegroup_relation sgr, host_service_relation hsr, hostgroup_relation hgr "
            . "WHERE sgr.servicegroup_sg_id = " . $sg_id . " "
            . "AND sgr.hostgroup_hg_id = hsr.hostgroup_hg_id "
            . "AND hsr.service_service_id = sgr.service_service_id "
            . "AND sgr.hostgroup_hg_id = hgr.hostgroup_hg_id ";

        $res = $this->DB->query($query);
        while ($row = $res->fetchRow()) {
            $services[] = array($row['host_host_id'], $row['service_service_id']);
        }
        $res->free();

        return $services;
    }
    
    public function getServicesGroups($sg_id = array())
    {
        $arrayReturn = array();

        if (!empty($sg_id)) {
            $query = "SELECT sg_id, sg_name "
                . "FROM servicegroup "
                . "WHERE sg_id IN (" . $this->DB->escape(implode(",", $sg_id))." ) ";
            $res = $this->DB->query($query);
            while ($row = $res->fetchRow()) {
                $arrayReturn[] = array(
                    "id" => $row['sg_id'],
                    "name" => $row['sg_name']
                );
            }
        }

        return $arrayReturn;
    }
    
    
    /**
     *
     * @param type $field
     * @return string
     */
    public static function getDefaultValuesParameters($field)
    {
        $parameters = array();
        $parameters['currentObject']['table'] = 'servicegroup';
        $parameters['currentObject']['id'] = 'sg_id';
        $parameters['currentObject']['name'] = 'sg_name';
        $parameters['currentObject']['comparator'] = 'sg_id';

        switch ($field) {
            case 'sg_hServices':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonService';
                $parameters['relationObject']['table'] = 'servicegroup_relation';
                $parameters['relationObject']['field'] = 'host_host_id';
                $parameters['relationObject']['additionalField'] = 'service_service_id';
                $parameters['relationObject']['comparator'] = 'servicegroup_sg_id';
                break;
            case 'sg_tServices':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonServicetemplates';
                $parameters['externalObject']['objectOptions'] = array('withHosttemplate' => true);
                $parameters['relationObject']['table'] = 'servicegroup_relation';
                $parameters['relationObject']['field'] = 'service_service_id';
                $parameters['relationObject']['comparator'] = 'servicegroup_sg_id';
                break;
            case 'sg_hgServices':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonService';
                $parameters['externalObject']['objectOptions'] = array('hostgroup' => true);
                $parameters['relationObject']['table'] = 'servicegroup_relation';
                $parameters['relationObject']['field'] = 'hostgroup_hg_id';
                $parameters['relationObject']['additionalField'] = 'service_service_id';
                $parameters['relationObject']['comparator'] = 'servicegroup_sg_id';
                break;
        }
        
        return $parameters;
    }

    /**
     *
     * @param array $values
     * @return array
     */
    public function getObjectForSelect2($values = array(), $options = array())
    {
        global $centreon;
        $items = array();

        # get list of authorized servicegroups
        if (!$centreon->user->access->admin) {
            $sgAcl = $centreon->user->access->getServiceGroupAclConf(
                null,
                'broker',
                array(
                    'distinct' => true,
                    'fields'  => array('servicegroup.sg_id'),
                    'get_row' => 'sg_id',
                    'keys' => array('sg_id'),
                    'conditions' => array(
                        'servicegroup.sg_id' => array(
                            'IN',
                            $values
                        )
                    )
                ),
                true
            );
        }

        $explodedValues = implode(',', $values);
        if (empty($explodedValues)) {
            $explodedValues = "''";
        }

        # get list of selected servicegroups
        $query = "SELECT sg_id, sg_name "
            . "FROM servicegroup "
            . "WHERE sg_id IN (" . $explodedValues . ") "
            . "ORDER BY sg_name ";

        $resRetrieval = $this->DB->query($query);
        while ($row = $resRetrieval->fetchRow()) {
            # hide unauthorized servicegroups
            $hide = false;
            if (!$centreon->user->access->admin && !in_array($row['sg_id'], $sgAcl)) {
                $hide = true;
            }

            $items[] = array(
                'id' => $row['sg_id'],
                'text' => $row['sg_name'],
                'hide' => $hide
            );
        }

        return $items;
    }
}
