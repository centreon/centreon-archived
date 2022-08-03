<?php

/*
 * Copyright 2005-2020 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
require_once _CENTREON_PATH_ . "/www/class/centreonACL.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonHook.class.php";
require_once __DIR__ . "/centreon_configuration_objects.class.php";

class CentreonPerformanceService extends CentreonConfigurationObjects
{
    /**
     * @var CentreonDB
     */
    protected $pearDBMonitoring;

    /**
     * CentreonPerformanceService constructor.
     */
    public function __construct()
    {
        global $pearDBO;
        parent::__construct();
        $this->pearDBMonitoring = new CentreonDB('centstorage');
    }

    /**
     * @return array
     * @throws RestBadRequestException
     */
    public function getList()
    {
        global $centreon, $conf_centreon;

        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;
        $additionalTables = '';
        $additionalValues = array();
        $additionalCondition = '';
        $bindParams = array();
        $excludeAnomalyDetection = false;

        /* Get ACL if user is not admin */
        $acl = null;
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
        }

        if (false === isset($this->arguments['q'])) {
            $bindParams[':fullName'] = '%%';
        } else {
            $bindParams[':fullName'] = '%' . (string)$this->arguments['q'] . '%';
        }

        if (isset($this->arguments['e']) && strcmp('anomaly', $this->arguments['e']) == 0) {
            $excludeAnomalyDetection = true;
        }

        $query = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT fullname, host_id, service_id, index_id ' .
            'FROM ( ' .
            '( SELECT CONCAT(i.host_name, " - ", i.service_description) as fullname, i.host_id, ' .
            'i.service_id, m.index_id ' .
            'FROM index_data i, metrics m, services s ' . (!$isAdmin ? ', centreon_acl acl ' : '');
        if (isset($this->arguments['hostgroup'])) {
            $additionalTables .= ',hosts_hostgroups hg ';
        }
        if (isset($this->arguments['servicegroup'])) {
            $additionalTables .= ',services_servicegroups sg ';
        }

        $query .= $additionalTables .
            'WHERE i.id = m.index_id ' .
            'AND s.enabled = 1 ' .
            'AND i.service_id = s.service_id ' .
            'AND i.host_name NOT LIKE "\_Module\_%" ' .
            'AND CONCAT(i.host_name, " - ", i.service_description) LIKE :fullName ';

        if (!$isAdmin) {
            $query .= 'AND acl.host_id = i.host_id ' .
                'AND acl.service_id = i.service_id ' .
                'AND acl.group_id IN (' . $acl->getAccessGroupsString() . ') ';
        }

        if ($excludeAnomalyDetection) {
            $additionalCondition .= 'AND s.service_id NOT IN (SELECT service_id 
            FROM `' . $conf_centreon['db'] . '`.mod_anomaly_service) ';
        }
        if (isset($this->arguments['hostgroup'])) {
            $additionalCondition .= 'AND (hg.host_id = i.host_id ' .
                'AND hg.hostgroup_id IN (';
            $params = array();
            foreach ($this->arguments['hostgroup'] as $k => $v) {
                if (!is_numeric($v)) {
                    throw new \RestBadRequestException('Error, host group id must be numerical');
                }
                $params[':hgId' . $v] = (int)$v;
            }
            $bindParams = array_merge($bindParams, $params);
            $additionalCondition .= implode(',', array_keys($params)) . ')) ';
        }

        if (isset($this->arguments['servicegroup'])) {
            $additionalCondition .= 'AND (sg.host_id = i.host_id AND sg.service_id = i.service_id ' .
                'AND sg.servicegroup_id IN (';
            $params = array();
            foreach ($this->arguments['servicegroup'] as $k => $v) {
                if (!is_numeric($v)) {
                    throw new \RestBadRequestException('Error, service group id must be numerical');
                }
                $params[':sgId' . $v] = (int)$v;
            }
            $bindParams = array_merge($bindParams, $params);
            $additionalCondition .= implode(',', array_keys($params)) . ')) ';
        }

        if (isset($this->arguments['host'])) {
            $additionalCondition .= 'AND i.host_id IN (';
            $params = array();
            foreach ($this->arguments['host'] as $k => $v) {
                if (!is_numeric($v)) {
                    throw new \RestBadRequestException('Error, host id must be numerical');
                }
                $params[':hostId' . $v] = (int)$v;
            }
            $bindParams = array_merge($bindParams, $params);
            $additionalCondition .= implode(',', array_keys($params)) . ') ';
        }
        $query .= $additionalCondition . ') ';
        if (isset($acl)) {
            $virtualObject = $this->getVirtualServicesCondition(
                $additionalTables,
                $additionalCondition,
                $additionalValues,
                $acl
            );
            $virtualServicesCondition = $virtualObject['query'];
            $virtualValues = $virtualObject['value'];
        } else {
            $virtualObject = $this->getVirtualServicesCondition(
                $additionalTables,
                $additionalCondition,
                $additionalValues
            );
            $virtualServicesCondition = $virtualObject['query'];
            $virtualValues = $virtualObject['value'];
        }

        $query .= $virtualServicesCondition . ') as t_union ' .
            'WHERE fullname LIKE :fullName ' .
            'GROUP BY host_id, service_id ' .
            'ORDER BY fullname ';

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            if (
                !is_numeric($this->arguments['page'])
                || !is_numeric($this->arguments['page_limit'])
                || $this->arguments['page_limit'] < 1
            ) {
                throw new \RestBadRequestException('Error, limit must be an integer greater than zero');
            }
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $query .= 'LIMIT :offset, :limit';
            $bindParams[':offset'] = (int)$offset;
            $bindParams[':limit'] = (int)$this->arguments['page_limit'];
        }

        $stmt = $this->pearDBMonitoring->prepare($query);
        $stmt->bindValue(':fullName', $bindParams[':fullName'], PDO::PARAM_STR);
        unset($bindParams[':fullName']);
        foreach ($bindParams as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_INT);
        }

        if (isset($virtualValues['metaService'])) {
            foreach ($virtualValues['metaService'] as $k => $v) {
                $stmt->bindValue(':' . $k, $v, PDO::PARAM_INT);
            }
        }
        if (isset($virtualValues['virtualService'])) {
            foreach ($virtualValues['virtualService'] as $k => $v) {
                $stmt->bindValue(':' . $k, $v, PDO::PARAM_INT);
            }
        }
        $stmt->execute();
        $serviceList = array();
        while ($data = $stmt->fetch()) {
            $serviceCompleteName = $data['fullname'];
            $serviceCompleteId = $data['host_id'] . '-' . $data['service_id'];
            $serviceList[] = array('id' => htmlentities($serviceCompleteId), 'text' => $serviceCompleteName);
        }
        return array(
            'items' => $serviceList,
            'total' => (int) $this->pearDBMonitoring->numberRows()
        );
    }

    /**
     * @param $additionalTables
     * @param $additionalCondition
     * @param $additionalValues
     * @param null $aclObj
     * @return array
     * @throws RestBadRequestException
     */
    private function getVirtualServicesCondition(
        $additionalTables,
        $additionalCondition,
        $additionalValues,
        $aclObj = null
    ) {
        global $centreon;

        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;

        /* Get ACL if user is not admin */
        $acl = null;
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
        }

        /* First, get virtual services for metaservices */
        $metaServiceCondition = '';
        $metaValues = $additionalValues;
        if (isset($aclObj) && !is_null($aclObj)) {
            $metaServices = $aclObj->getMetaServices();
            $virtualServices = array();
            foreach ($metaServices as $metaServiceId => $metaServiceName) {
                $virtualServices[] = 'meta_' . $metaServiceId;
            }
            if (count($virtualServices)) {
                $metaServiceCondition = 'AND s.description IN (';
                $explodedValues = '';
                foreach ($virtualServices as $k => $v) {
                    $explodedValues .= ':meta' . $k . ',';
                    $metaValues['metaService']['meta' . $k] = (string)$v;
                }
                $explodedValues = rtrim($explodedValues, ',');
                $metaServiceCondition .= $explodedValues . ') ';
            }
        }

        $metaServiceCondition .= 'AND s.description LIKE "meta_%" ';

        $virtualServicesCondition = 'UNION ALL (' .
            'SELECT CONCAT("Meta - ", s.display_name) as fullname, i.host_id, i.service_id, m.index_id ' .
            'FROM index_data i, metrics m, services s ' . (!$isAdmin ? ', centreon_acl acl ' : '') .
            $additionalTables .
            'WHERE i.id = m.index_id ' .
            'AND s.enabled = 1 ' .
            $additionalCondition .
            $metaServiceCondition .
            'AND i.service_id = s.service_id ';
        if (!$isAdmin) {
            $virtualServicesCondition .= 'AND acl.host_id = i.host_id ' .
                'AND acl.service_id = i.service_id ' .
                'AND acl.group_id IN (' . $acl->getAccessGroupsString() . ') ';
        }
        $virtualServicesCondition .= ') ';

        /* Then, get virtual services for modules */
        $allVirtualServiceIds = CentreonHook::execute('Service', 'getVirtualServiceIds');
        foreach ($allVirtualServiceIds as $moduleVirtualServiceIds) {
            foreach ($moduleVirtualServiceIds as $hostname => $virtualServiceIds) {
                if (count($virtualServiceIds)) {
                    $virtualServicesCondition .= 'UNION ALL (' .
                        'SELECT CONCAT("' . $hostname . ' - ", s.display_name) as fullname, ' .
                        'i.host_id, i.service_id, m.index_id ' .
                        'FROM index_data i, metrics m, services s ' .
                        $additionalTables .
                        'WHERE i.id = m.index_id ' .
                        'AND s.enabled = 1 ' .
                        $additionalCondition .
                        'AND s.service_id IN (';

                    $explodedValues = '';
                    foreach ($virtualServiceIds as $k => $v) {
                        if (!is_numeric($v)) {
                            throw new \RestBadRequestException('Error, virtual service id must be numerical');
                        }
                        $explodedValues .= ':vService' . $v . ',';
                        $metaValues['virtualService']['vService' . $v] = (int)$v;
                    }
                    $explodedValues = rtrim($explodedValues, ',');

                    $virtualServicesCondition .= $explodedValues . ') ' .
                        'AND i.service_id = s.service_id ' .
                        ') ';
                }
            }
        }

        return array('query' => $virtualServicesCondition, 'value' => $metaValues);
    }
}
