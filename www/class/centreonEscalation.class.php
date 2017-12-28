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
class CentreonEscalation
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
        $parameters['currentObject']['table'] = 'escalation';
        $parameters['currentObject']['id'] = 'esc_id';
        $parameters['currentObject']['name'] = 'esc_name';
        $parameters['currentObject']['comparator'] = 'esc_id';

        switch ($field) {
            case 'esc_cgs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonContactgroup';
                $parameters['relationObject']['table'] = 'escalation_contactgroup_relation';
                $parameters['relationObject']['field'] = 'contactgroup_cg_id';
                $parameters['relationObject']['comparator'] = 'escalation_esc_id';
                break;
            case 'esc_hServices':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonService';
                $parameters['relationObject']['table'] = 'escalation_service_relation';
                $parameters['relationObject']['field'] = 'host_host_id';
                $parameters['relationObject']['additionalField'] = 'service_service_id';
                $parameters['relationObject']['comparator'] = 'escalation_esc_id';
                break;
            case 'esc_hosts':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonHost';
                $parameters['relationObject']['table'] = 'escalation_host_relation';
                $parameters['relationObject']['field'] = 'host_host_id';
                $parameters['relationObject']['comparator'] = 'escalation_esc_id';
                break;
            case 'esc_hgs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonHostgroups';
                $parameters['relationObject']['table'] = 'escalation_hostgroup_relation';
                $parameters['relationObject']['field'] = 'hostgroup_hg_id';
                $parameters['relationObject']['comparator'] = 'escalation_esc_id';
                break;
            case 'esc_sgs':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonServicegroups';
                $parameters['relationObject']['table'] = 'escalation_servicegroup_relation';
                $parameters['relationObject']['field'] = 'servicegroup_sg_id';
                $parameters['relationObject']['comparator'] = 'escalation_esc_id';
                break;
            case 'esc_metas':
                $parameters['type'] = 'relation';
                $parameters['externalObject']['object'] = 'centreonMeta';
                $parameters['relationObject']['table'] = 'escalation_meta_service_relation';
                $parameters['relationObject']['field'] = 'meta_service_meta_id';
                $parameters['relationObject']['comparator'] = 'escalation_esc_id';
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

        # get list of authorized host categories
        if (!$centreon->user->access->admin) {
            $hcAcl = $centreon->user->access->getHostCategories();
        }

        $listValues = '';
        $queryValues = array();
        if (!empty($values)) {
            foreach ($values as $k => $v) {
                $listValues .= ':hc' . $v . ',';
                $queryValues['hc' . $v] = (int)$v;
            }
            $listValues = rtrim($listValues, ',');
        } else {
            $listValues .= '""';
        }

        # get list of selected host categories
        $query = "SELECT hc_id, hc_name FROM hostcategories " .
            "WHERE hc_id IN (" . $listValues . ") ORDER BY hc_name ";

        $stmt = $this->db->prepare($query);

        if (!empty($queryValues)) {
            foreach ($queryValues as $key => $id) {
                $stmt->bindValue(':' . $key, $id, PDO::PARAM_INT);
            }
        }
        $stmt->execute();

        while ($row = $stmt->fetchRow()) {
            # hide unauthorized host categories
            $hide = false;
            if (!$centreon->user->access->admin && count($hcAcl) && !in_array($row['hc_id'], array_keys($hcAcl))) {
                $hide = true;
            }

            $items[] = array(
                'id' => $row['hc_id'],
                'text' => $row['hc_name'],
                'hide' => $hide
            );
        }

        return $items;
    }
}
