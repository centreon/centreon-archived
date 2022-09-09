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
require_once _CENTREON_PATH_ . "/www/class/centreonHost.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonHook.class.php";
require_once __DIR__ . "/centreon_configuration_objects.class.php";

class CentreonConfigurationHost extends CentreonConfigurationObjects
{
    /**
     * @var CentreonDB
     */
    protected $pearDBMonitoring;

    /**
     * CentreonConfigurationHost constructor.
     */
    public function __construct()
    {
        global $pearDBO;
        parent::__construct();
        $this->pearDBMonitoring = new CentreonDB('centstorage');
        $pearDBO = $this->pearDBMonitoring;
    }

    /**
     * @return array
     * @throws RestBadRequestException
     */
    public function getList()
    {
        global $centreon;

        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;
        $aclHosts = '';
        $additionalTables = '';
        $additionalCondition = '';
        $explodedValues = '';
        $queryValues = array();
        $query = '';

        // Check for select2 'q' argument
        if (false === isset($this->arguments['q'])) {
            $queryValues["hostName"] = '%%';
        } else {
            $queryValues["hostName"] = '%' . (string)$this->arguments['q'] . '%';
        }
        $query .= 'SELECT SQL_CALC_FOUND_ROWS DISTINCT host_name, host_id, host_activate ' .
            'FROM ( ' .
            '( SELECT DISTINCT h.host_name, h.host_id, h.host_activate ' .
            'FROM host h ';

        if (isset($this->arguments['hostgroup'])) {
            $additionalTables .= ',hostgroup_relation hg ';
            $additionalCondition .= 'AND hg.host_host_id = h.host_id AND hg.hostgroup_hg_id IN (';
            foreach (explode(',', $this->arguments['hostgroup']) as $hgId => $hgValue) {
                if (!is_numeric($hgValue)) {
                    throw new \RestBadRequestException('Error, host group id must be numerical');
                }
                $explodedValues .= ':hostgroup' . $hgId . ',';
                $queryValues['hostgroup'][$hgId] = (int)$hgValue;
            }
            $explodedValues = rtrim($explodedValues, ',');
            $additionalCondition .= $explodedValues . ') ';
        }
        $query .= $additionalTables . 'WHERE h.host_register = "1" ';

        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclHosts .= 'AND h.host_id IN (' . $acl->getHostsString('ID', $this->pearDBMonitoring) . ') ';
        }
        $query .= $aclHosts;
        $query .= $additionalCondition . ') ';

        // Check for virtual hosts
        $virtualHostCondition = '';
        if (!isset($this->arguments['hostgroup']) && isset($this->arguments['h']) && $this->arguments['h'] == 'all') {
            $allVirtualHosts = CentreonHook::execute('Host', 'getVirtualHosts');
            foreach ($allVirtualHosts as $virtualHosts) {
                foreach ($virtualHosts as $vHostId => $vHostName) {
                    $virtualHostCondition .= 'UNION ALL ' .
                        "(SELECT :hostNameTable$vHostId as host_name, "
                        . ":virtualHostId$vHostId as host_id, "
                        . "'1' AS host_activate ) ";
                    $queryValues['virtualHost'][$vHostId] = (string)$vHostName;
                }
            }
        }
        $query .= $virtualHostCondition .
            ') t_union ' .
            'WHERE host_name LIKE :hostName ' .
            'ORDER BY host_name ';

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
            $queryValues['offset'] = (int)$offset;
            $queryValues['limit'] = (int)$this->arguments['page_limit'];
        }

        $stmt = $this->pearDB->prepare($query);
        $stmt->bindParam(':hostName', $queryValues['hostName'], PDO::PARAM_STR);

        if (isset($queryValues['hostgroup'])) {
            foreach ($queryValues['hostgroup'] as $hgId => $hgValue) {
                $stmt->bindValue(':hostgroup' . $hgId, $hgValue, PDO::PARAM_INT);
            }
        }
        if (isset($queryValues['virtualHost'])) {
            foreach ($queryValues['virtualHost'] as $vhId => $vhValue) {
                $stmt->bindValue(':hostNameTable' . $vhId, $vhValue, PDO::PARAM_STR);
                $stmt->bindValue(':virtualHostId' . $vhId, $vhId, PDO::PARAM_INT);
            }
        }
        if (isset($queryValues['offset'])) {
            $stmt->bindParam(':offset', $queryValues["offset"], PDO::PARAM_INT);
            $stmt->bindParam(':limit', $queryValues["limit"], PDO::PARAM_INT);
        }
        $stmt->execute();
        $hostList = array();
        while ($data = $stmt->fetch()) {
            $hostList[] = array(
                'id' => htmlentities($data['host_id']),
                'text' => $data['host_name'],
                'status' => (bool) $data['host_activate'],
            );
        }

        return array(
            'items' => $hostList,
            'total' => (int) $this->pearDB->numberRows()
        );
    }

    /**
     *
     * @return type
     * @throws RestBadRequestException
     */
    public function getServices()
    {
        // Check for id
        if (false === isset($this->arguments['id'])) {
            throw new RestBadRequestException("Missing host id");
        }
        $id = $this->arguments['id'];

        $allServices = false;
        if (isset($this->arguments['all'])) {
            $allServices = true;
        }

        $hostObj = new CentreonHost($this->pearDB);
        $serviceList = array();
        $serviceListRaw = $hostObj->getServices($id, false, $allServices);

        foreach ($serviceListRaw as $service_id => $service_description) {
            if ($allServices || service_has_graph($id, $service_id)) {
                $serviceList[$service_id] = $service_description;
            }
        }

        return $serviceList;
    }
}
