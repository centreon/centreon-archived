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
 * Enter description here ...
 * @author jmathis
 *
 */
class CentreonServicecategories
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
        $parameters['currentObject']['table'] = 'service_categories';
        $parameters['currentObject']['id'] = 'sc_id';
        $parameters['currentObject']['name'] = 'sc_name';
        $parameters['currentObject']['comparator'] = 'sc_id';

        switch ($field) {
            case 'sc_svcTpl':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonServicetemplates';
                $parameters['relationObject']['table'] = 'service_categories_relation';
                $parameters['relationObject']['field'] = 'service_service_id';
                $parameters['relationObject']['comparator'] = 'sc_id';
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

        # get list of authorized service categories
        if (!$centreon->user->access->admin) {
            $scAcl = $centreon->user->access->getServiceCategories();
        }

        $explodedValues = implode(',', $values);
        if (empty($explodedValues)) {
            $explodedValues = "''";
        }

        # get list of selected service categories
        $query = "SELECT sc_id, sc_name "
            . "FROM service_categories "
            . "WHERE sc_id IN (" . $explodedValues . ") "
            . "ORDER BY sc_name ";

        $resRetrieval = $this->db->query($query);
        while ($row = $resRetrieval->fetchRow()) {
            # hide unauthorized service categories
            $hide = false;
            if (!$centreon->user->access->admin && count($scAcl) && !in_array($row['sc_id'], array_keys($scAcl))) {
                $hide = true;
            }

            $items[] = array(
                'id' => $row['sc_id'],
                'text' => $row['sc_name'],
                'hide' => $hide
            );
        }

        return $items;
    }
}
