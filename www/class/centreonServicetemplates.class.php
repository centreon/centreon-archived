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
 * SVN : $URL$
 * SVN : $Id$
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
                $serviceList[] = array('id' => htmlentities($data['service_id']), 'text' => htmlentities($data['service_description']));
            }
        }
        
        return $serviceList;
    }
}

?>
