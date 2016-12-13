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


require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once dirname(__FILE__) . "/centreon_configuration_objects.class.php";

class CentreonPerformanceService extends CentreonConfigurationObjects
{
    /**
     *
     * @var type 
     */
    protected $pearDBMonitoring;

    /**
     * 
     */
    public function __construct()
    {
        global $pearDBO;
        parent::__construct();
        $this->pearDBMonitoring = new CentreonDB('centstorage');
    }
    
    /**
     * 
     * @param array $args
     * @return array
     */
    public function getList()
    {
        global $centreon;
        
        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;
        $aclServices = '';
        
        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
        }
        
        if (false === isset($this->arguments['q'])) {
            $q = '';
        } else {
            $q = $this->arguments['q'];
        }

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            $limit = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $range = 'LIMIT ' . $limit . ',' . $this->arguments['page_limit'];
        } else {
            $range = '';
        }        
        
        $query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT i.service_description, i.service_id, i.host_name, i.host_id, m.index_id "
            . "FROM index_data i, metrics m ".(!$isAdmin ? ', centreon_acl acl ' : '')
            . 'WHERE i.id = m.index_id '
            . (!$isAdmin ? ' AND acl.host_id = i.host_id AND acl.service_id = i.service_id AND acl.group_id IN ('.$acl->getAccessGroupsString().') ' : '')
            . "AND (i.service_description LIKE '%$q%' OR i.host_name LIKE '%$q%') "
            . "AND i.host_name NOT LIKE '%_Module%' "
            . (isset($this->arguments['host']) ? 'AND i.host_id = ' . $this->arguments['host'] . ' ' : '')
            . $aclServices
            . "ORDER BY i.host_name, i.service_description "
            . $range;
        $DBRESULT = $this->pearDBMonitoring->query($query);
        $serviceList = array();
        while ($data = $DBRESULT->fetchRow()) {
            $serviceCompleteName = $data['host_name'].' - '.$data['service_description'];
            $serviceCompleteId = $data['host_id'].'-'.$data['service_id'];
            $serviceList[] = array('id' => htmlentities($serviceCompleteId), 'text' => $serviceCompleteName);
        }
        
        return array(
            'items' => $serviceList,
            'total' => $this->pearDB->numberRows()
        );
    }
    
    /**
     *
     * @param array $args
     * @return array
     */
    public function getList28()
    {
        global $centreon;
        
        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;
        $additionnalTables = '';
        $additionnalCondition = '';
        
        /* Get ACL if user is not admin */
        $acl = null;
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
        }
        
        if (false === isset($this->arguments['q'])) {
            $q = '';
        } else {
            $q = $this->arguments['q'];
        }

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            $limit = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $range = 'LIMIT ' . $limit . ',' . $this->arguments['page_limit'];
        } else {
            $range = '';
        }
        
        if (isset($this->arguments['hostgroup'])) {
            $additionnalTables .= ',hosts_hostgroups hg ';
            $additionnalCondition .= 'AND (hg.host_id = i.host_id AND hg.hostgroup_id IN (' .
                join(',', $this->arguments['hostgroup']) . ')) ';
        }
        if (isset($this->arguments['servicegroup'])) {
            $additionnalTables .= ',services_servicegroups sg ';
            $additionnalCondition .= 'AND (sg.host_id = i.host_id AND sg.service_id = i.service_id '
                . 'AND sg.servicegroup_id IN (' . join(',', $this->arguments['servicegroup']) . ')) ';
        }
        if (isset($this->arguments['host'])) {
            $additionnalCondition .= 'AND i.host_id IN (' . join(',', $this->arguments['host']) . ') ';
        }

        $virtualServicesCondition = $this->getVirtualServicesCondition($additionnalCondition, $acl);
        
        $query = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT fullname, host_id, service_id, index_id '
            . 'FROM ( '
            . '( SELECT CONCAT(i.host_name, " - ", i.service_description) as fullname, i.host_id, i.service_id, m.index_id '
            . 'FROM index_data i, metrics m ' . (!$isAdmin ? ', centreon_acl acl ' : '')
            . 'WHERE i.id = m.index_id '
            . 'AND i.host_name NOT LIKE "_Module_%" '
            . (!$isAdmin ? ' AND acl.host_id = i.host_id AND acl.service_id = i.service_id AND acl.group_id IN ('.$acl->getAccessGroupsString().') ' : '')
            . $additionnalCondition
            . ') '
            . $virtualServicesCondition
            . ') as t_union '
            . 'WHERE fullname LIKE "%' . $q . '%" '
            . 'GROUP BY host_id, service_id '
            . 'ORDER BY fullname '
            . $range;


        $DBRESULT = $this->pearDBMonitoring->query($query);
        $serviceList = array();
        while ($data = $DBRESULT->fetchRow()) {
            $serviceCompleteName = $data['fullname'];
            $serviceCompleteId = $data['host_id'].'-'.$data['service_id'];
            $serviceList[] = array('id' => htmlentities($serviceCompleteId), 'text' => $serviceCompleteName);
        }
        
        return array(
            'items' => $serviceList,
            'total' => $this->pearDB->numberRows()
        );
    }

    private function getVirtualServicesCondition($additionnalCondition, $aclObj = null)
    {
        /* First, get virtual services for metaservices */
        $metaServiceCondition = '';
        if (!is_null($acl0bj)) {
            $metaServices = $aclObj->getMetaServices();
            $virtualServices = array();
            foreach ($metaServices as $metaServiceId => $metaServiceName) {
                $virtualServices[] = 'meta_' . $metaServiceId;
            }
            if (count($virtualServices)) {
                $metaServiceCondition = 'AND s.description IN (' . implode(',', $virtualServices) . ') ';
            } else {
                return '';
            }
        } else {
            $metaServiceCondition = 'AND s.description LIKE "meta_%" ';
        }

        $virtualServicesCondition = 'UNION ALL ('
            . 'SELECT CONCAT("Meta - ", s.display_name) as fullname, i.host_id, i.service_id, m.index_id '
            . 'FROM index_data i, metrics m, services s '
            . 'WHERE i.id = m.index_id '
            . $additionnalCondition
            . $metaServiceCondition
            . 'AND i.service_id = s.service_id '
            . ') ';

        /* Then, get virtual services for modules*/
        $allVirtualServiceIds = CentreonHook::execute('Service', 'getVirtualServiceIds');
        foreach ($allVirtualServiceIds as $moduleVirtualServiceIds) {
            foreach ($moduleVirtualServiceIds as $hostname => $virtualServiceIds) {
                if (count($virtualServiceIds)) {
                    $virtualServicesCondition .= 'UNION ALL ('
                        . 'SELECT CONCAT("' . $hostname . ' - ", s.display_name) as fullname, i.host_id, i.service_id, m.index_id '
                        . 'FROM index_data i, metrics m, services s '
                        . 'WHERE i.id = m.index_id '
                        . $additionnalCondition
                        . 'AND s.service_id IN (' . implode(',', $virtualServiceIds) . ') '
                        . 'AND i.service_id = s.service_id '
                        . ') ';
                }
            }
        }

        return $virtualServicesCondition;
    }
}
