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
require_once _CENTREON_PATH_ . "/www/class/centreonHost.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonHook.class.php";
require_once dirname(__FILE__) . "/centreon_configuration_objects.class.php";

class CentreonConfigurationHost extends CentreonConfigurationObjects
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
        $pearDBO = $this->pearDBMonitoring;
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
        $aclHosts = '';
        $additionnalTables = '';
        $additionnalCondition = '';
        
        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclHosts .= 'AND h.host_id IN (' . $acl->getHostsString('ID', $this->pearDBMonitoring) . ') ';
        }
        
        // Check for select2 'q' argument
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
            $additionnalTables .= ',hostgroup_relation hg ';
            $additionnalCondition .= 'AND hg.host_host_id = h.host_id AND hg.hostgroup_hg_id IN (' .
                join(',', $this->arguments['hostgroup']) . ') ';
        }

        // Check for virtual hosts
        $virtualHostCondition = '';
        if (!isset($this->arguments['hostgroup']) && isset($this->arguments['h']) && $this->arguments['h'] == 'all') {
            $allVirtualHosts = CentreonHook::execute('Host', 'getVirtualHosts');
            foreach ($allVirtualHosts as $virtualHosts) {
                foreach ($virtualHosts as $virtualHostId => $virtualHostName) {
                    $virtualHostCondition .= 'UNION ALL ('
                        . 'SELECT "' . $virtualHostName . '" as host_name, ' . $virtualHostId . ' as host_id '
                        . ') ';
                }
            }
        }
        
        $queryHost = "SELECT SQL_CALC_FOUND_ROWS DISTINCT host_name, host_id "
            . "FROM ( "
            . "( SELECT DISTINCT h.host_name, h.host_id "
            . "FROM host h "
            . $additionnalTables
            . "WHERE h.host_register = '1' "
            . $aclHosts
            . $additionnalCondition
            . ") "
            . $virtualHostCondition
            . ") t_union "
            . "WHERE host_name LIKE '%" . $q . "%' "
            . "ORDER BY host_name "
            . $range;
        
        $DBRESULT = $this->pearDB->query($queryHost);

        $total = $this->pearDB->numberRows();
        
        $hostList = array();
        while ($data = $DBRESULT->fetchRow()) {
            $hostList[] = array(
                'id' => htmlentities($data['host_id']),
                'text' => $data['host_name']
            );
        }
        
        return array(
            'items' => $hostList,
            'total' => $total
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
