<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

namespace ConfigGenerateRemote;

use \PDO;

require_once dirname(__FILE__) . '/abstract/host.class.php';
require_once dirname(__FILE__) . '/abstract/service.class.php';

class Host extends AbstractHost
{
    protected $hosts_by_name = array();
    protected $hosts = null;
    protected $table = 'host';
    protected $generate_filename = 'hosts.infile';
    protected $stmt_hg = null;
    protected $stmt_parent = null;
    protected $stmt_service = null;
    protected $stmt_service_sg = null;
    protected $generated_parentship = array();
    protected $generatedHosts = array();

    private function getHostGroups(&$host)
    {
        if (!isset($host['hg'])) {
            if (is_null($this->stmt_hg)) {
                $this->stmt_hg = $this->backend_instance->db->prepare("SELECT
                    hostgroup_hg_id
                FROM hostgroup_relation
                WHERE host_host_id = :host_id
                ");
            }
            $this->stmt_hg->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
            $this->stmt_hg->execute();
            $host['hg'] = $this->stmt_hg->fetchAll(PDO::FETCH_COLUMN);
        }

        $hostgroup = Hostgroup::getInstance($this->dependencyInjector);
        foreach ($host['hg'] as $hg_id) {
            $hostgroup->addHostInHg($hg_id, $host['host_id'], $host['host_name']);
            hostgroupRelation::getInstance($this->dependencyInjector)->addRelation(
                $hg_id,
                $host['host_id']
            );
        }
    }

    private function getParents(&$host)
    {
        if (is_null($this->stmt_parent)) {
            $this->stmt_parent = $this->backend_instance->db->prepare("SELECT
                    host_parent_hp_id
                FROM host_hostparent_relation
                WHERE host_host_id = :host_id
                ");
        }
        $this->stmt_parent->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
        $this->stmt_parent->execute();
        $result = $this->stmt_parent->fetchAll(PDO::FETCH_COLUMN);

        $host['parents'] = array();
        foreach ($result as $parent_id) {
            if (isset($this->hosts[$parent_id])) {
                $host['parents'][] = $this->hosts[$parent_id]['host_name'];

                $correlation_instance = Correlation::getInstance($this->dependencyInjector);
                if ($correlation_instance->hasCorrelation()) {
                    $this->generated_parentship[] = array(
                        '@attributes' => array(
                            'parent' => $parent_id,
                            'host' => $host['host_id'],
                            'instance_id' => $this->backend_instance->getPollerId()
                        )
                    );
                }
            }
        }
    }

    private function getServices(&$host)
    {
        if (is_null($this->stmt_service)) {
            $this->stmt_service = $this->backend_instance->db->prepare("SELECT
                    service_service_id
                FROM host_service_relation
                WHERE host_host_id = :host_id AND service_service_id IS NOT NULL
                ");
        }
        $this->stmt_service->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
        $this->stmt_service->execute();
        $host['services_cache'] = $this->stmt_service->fetchAll(PDO::FETCH_COLUMN);

        $service = Service::getInstance($this->dependencyInjector);
        foreach ($host['services_cache'] as $service_id) {
            $service->generateFromServiceId($host['host_id'], $host['host_name'], $service_id);
            hostServiceRelation::getInstance($this->dependencyInjector)->addRelationHostService($host['host_id'], $service_id);
        }
    }

    private function getServicesByHg(&$host)
    {
        if (count($host['hg']) == 0) {
            return 1;
        }
        if (is_null($this->stmt_service_sg)) {
            $query = "SELECT host_service_relation.hostgroup_hg_id, service_service_id FROM host_service_relation " .
                "JOIN hostgroup_relation ON (hostgroup_relation.hostgroup_hg_id = " .
                "host_service_relation.hostgroup_hg_id) WHERE hostgroup_relation.host_host_id = :host_id";
            $this->stmt_service_sg = $this->backend_instance->db->prepare($query);
        }
        $this->stmt_service_sg->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
        $this->stmt_service_sg->execute();
        $host['services_hg_cache'] = $this->stmt_service_sg->fetchAll(PDO::FETCH_ASSOC);

        $service = Service::getInstance($this->dependencyInjector);
        foreach ($host['services_hg_cache'] as $value) {
            $service->generateFromServiceId($host['host_id'], $host['host_name'], $value['service_service_id'], 1);
            hostServiceRelation::getInstance($this->dependencyInjector)->addRelationHgService($value['hostgroup_hg_id'], $value['service_service_id']);
        }
    }

    protected function getSeverity($host_id_arg)
    {
        $severity_id = hostCategory::getInstance($this->dependencyInjector)->getHostSeverityByHostId($host_id_arg);
        if (!is_null($severity_id)) {
            hostcategoriesRelation::getInstance($this->dependencyInjector)->addRelation($severity_id, $host_id_arg);
        }
    }

    public function addHost($host_id, $attr = array())
    {
        $this->hosts[$host_id] = $attr;
    }

    private function getHosts($poller_id)
    {
        # We use host_register = 1 because we don't want _Module_* hosts
        $stmt = $this->backend_instance->db->prepare("SELECT
              $this->attributes_select
            FROM ns_host_relation, host
                LEFT JOIN extended_host_information ON extended_host_information.host_host_id = host.host_id
            WHERE ns_host_relation.nagios_server_id = :server_id
                AND ns_host_relation.host_host_id = host.host_id
                AND host.host_activate = '1' AND host.host_register = '1'");
        $stmt->bindParam(':server_id', $poller_id, PDO::PARAM_INT);
        $stmt->execute();
        $this->hosts = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }


    public function generateFromHostId(&$host)
    {
        $this->getImages($host);
        $this->getMacros($host);

        $this->getHostTimezone($host);
        $this->getHostTemplates($host);
        $this->getHostCommands($host);
        $this->getHostPeriods($host);
        
        if ($this->backend_instance->isExportContact()) {
            $this->getContactGroups($host);
            $this->getContacts($host);
        }
        
        $this->getHostGroups($host);
        #$this->getParents($host);
        $this->getSeverity($host['host_id']);
        $this->getServices($host);
        $this->getServicesByHg($host);

        $extendedInformation = $this->getExtendedInformation($host);
        extendedHostInformation::getInstance($this->dependencyInjector)->add($extendedInformation, $host['host_id']);

        $this->generateObjectInFile($host, $host['host_id']);
        $this->addGeneratedHost($host['host_id']);
    }

    public function generateFromPollerId($poller_id, $localhost = 0)
    {
        if (is_null($this->hosts)) {
            $this->getHosts($poller_id);
        }

        Service::getInstance($this->dependencyInjector)->set_poller($poller_id);

        foreach ($this->hosts as $host_id => &$host) {
            $this->hosts_by_name[$host['host_name']] = $host_id;
            $host['host_id'] = $host_id;
            $this->generateFromHostId($host);
        }

        if ($localhost == 1) {
            #MetaService::getInstance($this->dependencyInjector)->generateObjects();
        }
        
        Curves::getInstance($this->dependencyInjector)->generateObjects();
    }

    public function getHostIdByHostName($host_name)
    {
        if (isset($this->hosts_by_name[$host_name])) {
            return $this->hosts_by_name[$host_name];
        }
        return null;
    }

    public function getGeneratedParentship()
    {
        return $this->generated_parentship;
    }

    public function addGeneratedHost($hostId)
    {
        $this->generatedHosts[] = $hostId;
    }

    public function getGeneratedHosts()
    {
        return $this->generatedHosts;
    }

    public function reset()
    {
        $this->hosts_by_name = array();
        $this->hosts = null;
        $this->generated_parentship = array();
        $this->generatedHosts = array();
        # Don't want to reset file
        #parent::reset();
    }
}
