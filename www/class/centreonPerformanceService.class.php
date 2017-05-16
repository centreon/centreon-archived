<?php
/*
 * Copyright 2005-2016 Centreon
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
 * Description of centreonPerformanceService
 *
 */
class CentreonPerformanceService
{
    /**
     *
     * @var type 
     */
    protected $dbMon;
    
    /**
     *
     * @var type 
     */
    protected $aclObj;


    /**
     * 
     * @param type $dbMon
     * @param type $aclObj
     */
    public function __construct($dbMon, $aclObj)
    {
        $this->dbMon = $dbMon;
        $this->aclObj = $aclObj;
    }
    
    /**
     * 
     * @param array $filters
     * @return array
     */
    public function getList($filters = array())
    {
        $additionnalTables = '';
        $additionnalCondition = '';
        
        if (false === isset($filters['service'])) {
            $serviceDescription = '';
        } else {
            $serviceDescription = $filters['service'];
        }

        if (isset($filters['page_limit']) && isset($filters['page'])) {
            $limit = ($filters['page'] - 1) * $filters['page_limit'];
            $range = 'LIMIT ' . $limit . ',' . $filters['page_limit'];
        } else {
            $range = '';
        }
        
        if (isset($filters['hostgroup'])) {
            $additionnalTables .= ',hosts_hostgroups hg ';
            $additionnalCondition .= 'AND (hg.host_id = i.host_id AND hg.hostgroup_id IN (' .
                implode(',', $filters['hostgroup']) . ')) ';
        }
        if (isset($filters['servicegroup'])) {
            $additionnalTables .= ',services_servicegroups sg ';
            $additionnalCondition .= 'AND (sg.host_id = i.host_id AND sg.service_id = i.service_id '
                . 'AND sg.servicegroup_id IN (' . implode(',', $filters['servicegroup']) . ')) ';
        }
        if (isset($filters['host'])) {
            $additionnalCondition .= 'AND i.host_id IN (' . implode(',', $filters['host']) . ') ';
        }

        $virtualServicesCondition = $this->getVirtualServicesCondition($additionnalCondition);
        
        $query = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT fullname, host_id, service_id, index_id '
            . 'FROM ( '
            . '( SELECT CONCAT(i.host_name, " - ", i.service_description) as fullname, i.host_id, i.service_id, m.index_id '
            . 'FROM index_data i, metrics m ' . (!$this->aclObj->admin ? ', centreon_acl acl ' : '')
            . 'WHERE i.id = m.index_id '
            . 'AND i.host_name NOT LIKE "_Module_%" '
            . (!$this->aclObj->admin ? ' AND acl.host_id = i.host_id AND acl.service_id = i.service_id AND acl.group_id IN ('.$this->aclObj->getAccessGroupsString().') ' : '')
            . $additionnalCondition
            . ') '
            . $virtualServicesCondition
            . ') as t_union '
            . 'WHERE fullname LIKE "%' . $serviceDescription . '%" '
            . 'GROUP BY host_id, service_id '
            . 'ORDER BY fullname '
            . $range;


        $DBRESULT = $this->dbMon->query($query);
        $serviceList = array();
        while ($data = $DBRESULT->fetchRow()) {
            $serviceCompleteName = $data['fullname'];
            $serviceCompleteId = $data['host_id'].'-'.$data['service_id'];
            $serviceList[] = array('id' => $serviceCompleteId, 'text' => $serviceCompleteName);
        }
        
        return $serviceList;
    }
    
    /**
     * 
     * @param string $additionnalCondition
     * @return string
     */
    private function getVirtualServicesCondition($additionnalCondition)
    {
        /* First, get virtual services for metaservices */
        $metaServiceCondition = '';
        if (!$this->aclObj->admin) {
            $metaServices = $this->aclObj->getMetaServices();
            $virtualServices = array();
            foreach ($metaServices as $metaServiceId => $metaServiceName) {
                $virtualServices[] = "'meta_" . $metaServiceId."'";
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
