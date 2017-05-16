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

require_once _CENTREON_PATH_ . 'www/class/centreonService.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonInstance.class.php';

/**
 *  Class that contains various methods for managing services
 */
class CentreonServicetemplates extends CentreonService
{
    /**
     *  Constructor
     *
     *  @param CentreonDB $db
     */
    public function __construct($db)
    {
        parent::__construct($db);
    }

    /**
     *
     * @param integer $field
     * @return array
     */
    public static function getDefaultValuesParameters($field)
    {
        $parameters = array();
        $parameters['currentObject']['table'] = 'service';
        $parameters['currentObject']['id'] = 'service_id';
        $parameters['currentObject']['name'] = 'service_description';
        $parameters['currentObject']['comparator'] = 'service_id';

        switch ($field) {
            case 'service_hPars':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonHosttemplates';
                $parameters['externalObject']['table'] = 'host';
                $parameters['externalObject']['id'] = 'host_id';
                $parameters['externalObject']['name'] = 'host_name';
                $parameters['externalObject']['comparator'] = 'host_id';
                $parameters['relationObject']['table'] = 'host_service_relation';
                $parameters['relationObject']['field'] = 'host_host_id';
                $parameters['relationObject']['comparator'] = 'service_service_id';
                break;
            default:
                $parameters = parent::getDefaultValuesParameters($field);
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
        $serviceList = array();
        if (isset($options['withHosttemplate']) && $options['withHosttemplate'] === true) {
            $serviceList = parent::getObjectForSelect2($values, $options, '0');
        } else {
            $selectedServices = '';
            $explodedValues = implode(',', $values);
            if (empty($explodedValues)) {
                $explodedValues = "''";
            } else {
                $selectedServices .= "AND s.service_id IN ($explodedValues) ";
            }

            $queryService = "SELECT DISTINCT s.service_id, s.service_description "
                . "FROM service s "
                . "WHERE s.service_register = '0' "
                . $selectedServices
                . "ORDER BY s.service_description ";

            $DBRESULT = $this->db->query($queryService);


            while ($data = $DBRESULT->fetchRow()) {
                $serviceList[] = array('id' => $data['service_id'], 'text' => $data['service_description']);
            }
        }
        
        return $serviceList;
    }
    
    /**
     * Returns array of Service linked to the template
     *
     * @return array
     */
    public function getLinkedServicesByName($serviceTemplateName, $checkTemplates = true)
    {
        if ($checkTemplates) {
            $register = 0;
        } else {
            $register = 1;
        }

        $linkedServices = array();
        $query = 'SELECT DISTINCT s.service_description '
            . 'FROM service s, service st '
            . 'WHERE s.service_template_model_stm_id = st.service_id '
            . 'AND st.service_register = "0" '
            . 'AND s.service_register = "' . $register . '" '
            . 'AND st.service_description = "' . $this->db->escape($serviceTemplateName) . '" ';

        $result = $this->db->query($query);

        if (PEAR::isError($result)) {
            throw new \Exception('Error while getting linked services of ' . $serviceTemplateName);
        }

        while ($row = $result->fetchRow()) {
            $linkedServices[] = $row['service_description'];
        }

        return $linkedServices;
    }

    /**
     * @param string $serviceTemplateName linked service template
     * @param string $hostTemplateName linked host template
     *
     * @return array service ids
     */
    public function getServiceIdsLinkedToSTAndCreatedByHT($serviceTemplateName, $hostTemplateName)
    {
        $serviceIds = array();

        $query = 'SELECT DISTINCT(s.service_id) '
            . 'FROM service s, service st, host h, host ht, host_service_relation hsr, host_service_relation hsrt,'
            . ' host_template_relation htr '
            . 'WHERE st.service_description = "' . $this->db->escape($serviceTemplateName) . '" '
            . 'AND s.service_template_model_stm_id = st.service_id '
            . 'AND st.service_id = hsrt.service_service_id '
            . 'AND hsrt.host_host_id = ht.host_id '
            . 'AND ht.host_name = "' . $this->db->escape($hostTemplateName) . '" '
            . 'AND ht.host_id = htr.host_tpl_id '
            . 'AND htr.host_host_id = h.host_id '
            . 'AND h.host_id = hsr.host_host_id '
            . 'AND hsr.service_service_id = s.service_id '
            . 'AND s.service_register = "1" ';
        $result = $this->db->query($query);
        while ($row = $result->fetchRow()) {
            $serviceIds[] = $row['service_id'];
        }

        return $serviceIds;
    }

    /**
     *
     * @param string $q
     * @return array
     */
    public function getList($enable = false)
    {
        $serviceTemplates = array();

        $query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT service_id, service_description "
            . "FROM service "
            . "WHERE service_register = '0' ";

        if ($enable) {
            $query .= "AND service_activate = '1' ";
        }

        $query .= "ORDER BY service_description ";

        $res = $this->db->query($query);
        if (PEAR::isError($res)) {
            return array();
        }

        $serviceTemplates = array();
        while ($row = $res->fetchRow()) {
            $serviceTemplates[$row['service_id']] = $row['service_description'];
        }

        return $serviceTemplates;
    }
}
