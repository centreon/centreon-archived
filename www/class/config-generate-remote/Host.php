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
use ConfigGenerateRemote\Abstracts\AbstractHost;

class Host extends AbstractHost
{
    protected $hostsByName = [];
    protected $hosts = null;
    protected $table = 'host';
    protected $generateFilename = 'hosts.infile';
    protected $stmtHg = null;
    protected $stmtParent = null;
    protected $stmtService = null;
    protected $stmtServiceSg = null;
    protected $generatedParentship = [];
    protected $generatedHosts = [];

    private function getHostGroups(&$host)
    {
        if (!isset($host['hg'])) {
            if (is_null($this->stmtHg)) {
                $this->stmtHg = $this->backendInstance->db->prepare("SELECT
                    hostgroup_hg_id
                FROM hostgroup_relation
                WHERE host_host_id = :host_id
                ");
            }
            $this->stmtHg->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
            $this->stmtHg->execute();
            $host['hg'] = $this->stmtHg->fetchAll(PDO::FETCH_COLUMN);
        }

        $hostgroup = HostGroup::getInstance($this->dependencyInjector);
        foreach ($host['hg'] as $hgId) {
            $hostgroup->addHostInHg($hgId, $host['host_id'], $host['host_name']);
            Relations\HostGroupRelation::getInstance($this->dependencyInjector)->addRelation(
                $hgId,
                $host['host_id']
            );
        }
    }

    private function getServices(&$host)
    {
        if (is_null($this->stmtService)) {
            $this->stmtService = $this->backendInstance->db->prepare("SELECT
                    service_service_id
                FROM host_service_relation
                WHERE host_host_id = :host_id AND service_service_id IS NOT NULL
                ");
        }
        $this->stmtService->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
        $this->stmtService->execute();
        $host['services_cache'] = $this->stmtService->fetchAll(PDO::FETCH_COLUMN);

        $service = Service::getInstance($this->dependencyInjector);
        foreach ($host['services_cache'] as $serviceId) {
            $service->generateFromServiceId($host['host_id'], $host['host_name'], $serviceId);
            Relations\HostServiceRelation::getInstance($this->dependencyInjector)
                ->addRelationHostService($host['host_id'], $serviceId);
        }
    }

    private function getServicesByHg(&$host)
    {
        if (count($host['hg']) == 0) {
            return 1;
        }
        if (is_null($this->stmtServiceSg)) {
            $query = "SELECT host_service_relation.hostgroup_hg_id, service_service_id FROM host_service_relation " .
                "JOIN hostgroup_relation ON (hostgroup_relation.hostgroup_hg_id = " .
                "host_service_relation.hostgroup_hg_id) WHERE hostgroup_relation.host_host_id = :host_id";
            $this->stmtServiceSg = $this->backendInstance->db->prepare($query);
        }
        $this->stmtServiceSg->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
        $this->stmtServiceSg->execute();
        $host['services_hg_cache'] = $this->stmtServiceSg->fetchAll(PDO::FETCH_ASSOC);

        $service = Service::getInstance($this->dependencyInjector);
        foreach ($host['services_hg_cache'] as $value) {
            $service->generateFromServiceId($host['host_id'], $host['host_name'], $value['service_service_id'], 1);
            Relations\HostServiceRelation::getInstance($this->dependencyInjector)
                ->addRelationHgService($value['hostgroup_hg_id'], $value['service_service_id']);
        }
    }

    protected function getSeverity($hostIdArg)
    {
        $severityId = HostCategory::getInstance($this->dependencyInjector)->getHostSeverityByHostId($hostIdArg);
        if (!is_null($severityId)) {
            Relations\HostCategoriesRelation::getInstance($this->dependencyInjector)->addRelation($severityId, $hostIdArg);
        }
    }

    public function addHost($hostId, $attr = [])
    {
        $this->hosts[$hostId] = $attr;
    }

    private function getHosts($pollerId)
    {
        // We use host_register = 1 because we don't want _Module_* hosts
        $stmt = $this->backendInstance->db->prepare("SELECT
              $this->attributesSelect
            FROM ns_host_relation, host
                LEFT JOIN extended_host_information ON extended_host_information.host_host_id = host.host_id
            WHERE ns_host_relation.nagios_server_id = :server_id
                AND ns_host_relation.host_host_id = host.host_id
                AND host.host_activate = '1' AND host.host_register = '1'");
        $stmt->bindParam(':server_id', $pollerId, PDO::PARAM_INT);
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

        if ($this->backendInstance->isExportContact()) {
            $this->getContactGroups($host);
            $this->getContacts($host);
        }

        $this->getHostGroups($host);
        $this->getSeverity($host['host_id']);
        $this->getServices($host);
        $this->getServicesByHg($host);

        $extendedInformation = $this->getExtendedInformation($host);
        Relations\ExtendedHostInformation::getInstance($this->dependencyInjector)->add($extendedInformation, $host['host_id']);

        $this->generateObjectInFile($host, $host['host_id']);
        $this->addGeneratedHost($host['host_id']);
    }

    public function generateFromPollerId($pollerId, $localhost = 0)
    {
        if (is_null($this->hosts)) {
            $this->getHosts($pollerId);
        }

        Service::getInstance($this->dependencyInjector)->setPoller($pollerId);

        foreach ($this->hosts as $hostId => &$host) {
            $this->hostsByName[$host['host_name']] = $hostId;
            $host['host_id'] = $hostId;
            $this->generateFromHostId($host);
        }

        if ($localhost == 1) {
            #MetaService::getInstance($this->dependencyInjector)->generateObjects();
        }

        Curves::getInstance($this->dependencyInjector)->generateObjects();
    }

    public function getHostIdByHostName($hostName)
    {
        if (isset($this->hostsByName[$hostName])) {
            return $this->hostsByName[$hostName];
        }
        return null;
    }

    public function getGeneratedParentship()
    {
        return $this->generatedParentship;
    }

    public function addGeneratedHost($hostId)
    {
        $this->generatedHosts[] = $hostId;
    }

    public function getGeneratedHosts()
    {
        return $this->generatedHosts;
    }

    public function reset($resetparent = false, $createfile = false)
    {
        $this->hostsByName = [];
        $this->hosts = null;
        $this->generatedParentship = [];
        $this->generatedHosts = [];
        if ($resetparent == true) {
            parent::reset($createfile);
        }
    }
}
