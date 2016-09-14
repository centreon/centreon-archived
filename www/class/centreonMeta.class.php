<?php
/**
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
 * @author Sylvestre Ho <sho@centreon.com>
 */
class CentreonMeta
{
    /**
     *
     * @var type
     */
    protected $db;
    
    /**
     * Constructor
     * @param type $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * Return host id
     *
     * @return int
     */
    public function getRealHostId()
    {
        static $hostId = null;

        if (is_null($hostId)) {
            $queryHost = 'SELECT host_id '
                . 'FROM host '
                . 'WHERE host_name = "_Module_Meta" '
                . 'AND host_register = "2" '
                . 'LIMIT 1 ';
            $res = $this->db->query($queryHost);
            if ($res->numRows()) {
                $row = $res->fetchRow();
                $hostId = $row['host_id'];
            } else {
                $query = 'INSERT INTO host (host_name, host_register) '
                    . 'VALUES ("_Module_Meta", "2") ';
                $this->db->query($query);
                $res = $this->db->query($queryHost);
                if ($res->numRows()) {
                    $row = $res->fetchRow();
                    $hostId = $row['host_id'];
                } else {
                    $hostId = 0;
                }
            }
        }

        return $hostId;
    }
    
    /**
     * Return service id
     *
     * @param int $metaId
     * @return int
     */
    public function getRealServiceId($metaId)
    {
        static $services = null;
        if (isset($services[$metaId])) {
            return $services[$metaId];
        }
        
        $sql = 'SELECT s.service_id '
            . 'FROM service s '
            . 'WHERE s.service_description = "meta_' . $metaId . '" ';

        $res = $this->db->query($sql);
        if ($res->numRows()) {
            while ($row = $res->fetchRow()) {
                 $services[$metaId] = $row['service_id'];
            }
        }

        if (isset($services[$metaId])) {
            return $services[$metaId];
        }
        return 0;
    }

    /**
     * Return metaservice id
     *
     * @param string $serviceDisplayName
     * @return int
     */
    public function getMetaIdFromServiceDisplayName($serviceDisplayName)
    {
        $metaId = null;
        $query = 'SELECT service_description '
            . 'FROM service '
            . 'WHERE display_name = "' . $serviceDisplayName . '" ';
        $res = $this->db->query($query);
        if ($res->numRows()) {
            $row = $res->fetchRow();
            if (preg_match('/meta_(\d+)/', $row['service_description'], $matches)) {
                $metaId = $matches[1];
            }
        }

        return $metaId;
    }
    
    /**
     *
     * @param integer $field
     * @return array
     */
    public static function getDefaultValuesParameters($field)
    {
        $parameters = array();
        $parameters['currentObject']['table'] = 'meta_service';
        $parameters['currentObject']['id'] = 'meta_id';
        $parameters['currentObject']['name'] = 'meta_name';
        $parameters['currentObject']['comparator'] = 'meta_id';

        switch ($field) {
            case 'check_period':
            case 'notification_period':
                $parameters['type'] = 'simple';
                $parameters['externalObject']['table'] = 'timeperiod';
                $parameters['externalObject']['id'] = 'tp_id';
                $parameters['externalObject']['name'] = 'tp_name';
                $parameters['externalObject']['comparator'] = 'tp_id';
                break;
            case 'ms_cs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'contact';
                $parameters['externalObject']['id'] = 'contact_id';
                $parameters['externalObject']['name'] = 'contact_name';
                $parameters['externalObject']['comparator'] = 'contact_id';
                $parameters['relationObject']['table'] = 'meta_contact';
                $parameters['relationObject']['field'] = 'contact_id';
                $parameters['relationObject']['comparator'] = 'meta_id';
                break;
            case 'ms_cs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['table'] = 'contactgroup';
                $parameters['externalObject']['id'] = 'cg_id';
                $parameters['externalObject']['name'] = 'cg_name';
                $parameters['externalObject']['comparator'] = 'cg_id';
                $parameters['relationObject']['table'] = 'meta_contactgroup_relation';
                $parameters['relationObject']['field'] = 'cg_cg_id';
                $parameters['relationObject']['comparator'] = 'meta_id';
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
        $items = array();
        
        $explodedValues = implode(',', $values);
        if (empty($explodedValues)) {
            $explodedValues = "''";
        }

        # get list of selected meta
        $query = "SELECT meta_id, meta_name "
            . "FROM meta_service "
            . "WHERE meta_id IN (" . $explodedValues . ") "
            . "ORDER BY meta_name ";
        
        $resRetrieval = $this->db->query($query);
        while ($row = $resRetrieval->fetchRow()) {
            $items[] = array(
                'id' => $row['meta_id'],
                'text' => $row['meta_name']
            );
        }

        return $items;
    }


    /**
     * Get the list of all meta-service
     *
     * @return array
     */
    public function getList()
    {
        $queryList = "SELECT `meta_id`, `meta_name`
 	    	FROM `meta_service`
 	    	ORDER BY `meta_name`";

        $res = $this->db->query($queryList);
        if (PEAR::isError($res)) {
            return array();
        }
        $listMeta = array();
        while ($row = $res->fetchRow()) {
            $listMeta[$row['meta_id']] = $row['meta_name'];
        }
        return $listMeta;
    }

    /**
     * Returns service details
     *
     * @param int $id
     * @return array
     */
    public function getParameters($id, $parameters = array())
    {
        $sElement = "*";
        $values = array();
        if (empty($id) || empty($parameters)) {
            return array();
        }

        if (count($parameters) > 0) {
            $sElement = implode(",", $parameters);
        }

        $query = "SELECT " . $sElement . " "
            . "FROM meta_service "
            . "WHERE meta_id = " . $this->db->escape($id) . " ";

        $res = $this->db->query($query);

        if ($res->numRows()) {
            $values = $res->fetchRow();
        }

        return $values;
    }

    /**
     * Returns service id
     *
     * @param int $metaId
     * @param string $metaName
     * @return int
     */
    public function insertVirtualService($metaId, $metaName)
    {
        $hostId = $this->getRealHostId();
        $serviceId = null;

        $composedName = 'meta_' . $metaId;

        $queryService = 'SELECT service_id '
            . 'FROM service '
            . 'WHERE service_register = "2" '
            . 'AND service_description = "' . $composedName . '" '
            . 'AND display_name = "' . $metaName . '" ';
        $res = $this->db->query($queryService);
        if ($res->numRows()) {
            $row = $res->fetchRow();
            $serviceId = $row['service_id'];
        } else {
            $query = 'INSERT INTO service (service_description, display_name, service_register) '
                . 'VALUES '
                . '("' . $composedName . '", "' . $metaName . '", "2")';
            $this->db->query($query);
            $query = 'INSERT INTO host_service_relation(host_host_id, service_service_id) '
                . 'VALUES ('
                . $hostId . ','
                . '(SELECT service_id FROM service WHERE service_description = "' . $composedName . '" AND service_register = "2" LIMIT 1)'
                . ')';
            $this->db->query($query);
            $res = $this->db->query($queryService);
            if ($res->numRows()) {
                $row = $res->fetchRow();
                $serviceId = $row['service_id'];
            }
        }

        return $serviceId;
    }
}
