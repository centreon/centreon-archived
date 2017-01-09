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

require_once "class/centreonWidget/Params/List.class.php";

class CentreonWidgetParamsConnectorService extends CentreonWidgetParamsList
{
    public function __construct($db, $quickform, $userId)
    {
        parent::__construct($db, $quickform, $userId);
        $this->trigger = true;
    }

    public function init($params)
    {
        parent::init($params);
        if (isset($this->quickform)) {
            $tab = $this->getListValues($params['parameter_id']);
            $triggerSource = './include/home/customViews/triggers/loadServiceFromHost.php';
            $this->element = $this->quickform->addElement('select',
            											  'param_trigger_'.$params['parameter_id'],
                                                          'Host',
                                                          $tab,
                                                          array('onchange' => 'javascript:loadFromTrigger("'.$triggerSource.'", '.$params['parameter_id'].', this.value);'));
            $userPref = $this->getUserPreferences($params);
            $svcTab = array();
            if (isset($userPref)) {
                list($hostId, $serviceId) = explode('-', $userPref);
                $svcTab = $this->getServiceIds($hostId);
            }
            $this->quickform->addElement('select',
                                         'param_'.$params['parameter_id'],
                                         $params['parameter_name'],
                                         $svcTab);
        }
    }

    /**
     * Get service id from host id
     *
     * @param int $hostId
     * @return array
     */
    protected function getServiceIds($hostId)
    {
        $aclString = $this->acl->queryBuilder('AND', 
                                         's.service_id',
                                         $this->acl->getServicesString('ID', $this->monitoringDb));
        $sql = "SELECT service_id, service_description
        		FROM service s, host_service_relation hsr
        		WHERE hsr.host_host_id = " . $this->db->escape($hostId) . "
        		AND hsr.service_service_id = s.service_id ";
        $sql .= $aclString;
        $sql .= " UNION ";
        $sql .= " SELECT service_id, service_description
        		FROM service s, host_service_relation hsr, hostgroup_relation hgr
        		WHERE hsr.hostgroup_hg_id = hgr.hostgroup_hg_id
        		AND hgr.host_host_id = ".$this->db->escape($hostId)."
        		AND hsr.service_service_id = s.service_id ";
        $sql .= $aclString;
        $sql .= " ORDER BY service_description ";
        $res = $this->db->query($sql);
        $tab = array();
        while ($row = $res->fetchRow()) {
            $tab[$hostId."-".$row['service_id']] = $row['service_description'];
        }
        return $tab;
    }

    public function getListValues($paramId)
    {
        static $tab;

        if (!isset($tab)) {
            $query = "SELECT host_id, host_name
                      FROM host
            	      WHERE host_activate = '1'
            	      AND host_register = '1' ";
            $query .= $this->acl->queryBuilder('AND', 
                                               'host_id',
                                               $this->acl->getHostsString('ID', $this->monitoringDb));
            $query .= " ORDER BY host_name";
            $res = $this->db->query($query);
            $tab = array(null => null);
            while ($row = $res->fetchRow()) {
                $tab[$row['host_id']] = $row['host_name'];
            }
        }
        return $tab;
    }

    /**
     * Set Value
     *
     * @param array $params
     * @return void
     */
    public function setValue($params)
    {
        $userPref = $this->getUserPreferences($params);
        if (isset($userPref)) {
            list($hostId, $serviceId) = explode('-', $userPref);
            $this->quickform->setDefaults(array('param_trigger_' . $params['parameter_id'] => $hostId));
            $this->quickform->setDefaults(array('param_' . $params['parameter_id'] => $userPref));
        }
    }
}