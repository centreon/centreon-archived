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
 *
 */
class CentreonResources
{
    protected $db;

    /*
     * constructor
     */
    public function __construct($pearDB)
    {
        $this->db = $pearDB;
    }
    
    /**
     *
     * @param integer $field
     * @return array
     */
    public static function getDefaultValuesParameters($field)
    {
        $parameters = array();
        $parameters['currentObject']['table'] = 'cfg_resource';
        $parameters['currentObject']['id'] = 'resource_id';
        $parameters['currentObject']['name'] = 'resource_name';
        $parameters['currentObject']['comparator'] = 'resource_id';

        switch ($field) {
            case 'instance_id':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonInstance';
                $parameters['relationObject']['table'] = 'cfg_resource_instance_relations';
                $parameters['relationObject']['field'] = 'instance_id';
                $parameters['relationObject']['comparator'] = 'resource_id';
                break;
        }
        
        return $parameters;
    }
    
    /**
     *
     * @param type $db
     * @param string $name
     * @return array
     * @throws Exception
     */
    public static function getResourceByName($db, $name)
    {
        $queryResources = "SELECT * FROM cfg_resource WHERE resource_name = '$name'";
        $resultQueryResources = $db->query($queryResources);
        
        $finalResource = array();
        while ($resultResources = $resultQueryResources->fetchRow()) {
            $finalResource = $resultResources;
        }
        
        if (count($finalResource) === 0) {
            throw new Exception("No central broker found", 500);
        }
        
        return $finalResource;
    }
}
