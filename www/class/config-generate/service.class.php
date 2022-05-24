<?php
/*
 * Copyright 2005-2019 Centreon
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

require_once dirname(__FILE__) . '/abstract/service.class.php';

class Service extends AbstractService
{
    const VERTICAL_NOTIFICATION = 1;
    const CLOSE_NOTIFICATION = 2;
    const CUMULATIVE_NOTIFICATION = 3;

    private $use_cache = 0;
    private $use_cache_poller = 1;
    private $done_cache = 0;
    protected $service_cache = null;
    protected $generated_services = array(); // for index_data build and escalation
    protected $generate_filename = 'services.cfg';
    protected $object_name = 'service';
    public $poller_id = null; // for by poller cache

    public function use_cache() : void
    {
        $this->use_cache = 1;
    }

    /**
     * @param int $serviceId
     * @param int $hostId
     * @param string $hostName
     */
    protected function getServiceGroups(int $serviceId, int $hostId, string $hostName) : void
    {
        $servicegroup = Servicegroup::getInstance($this->dependencyInjector);
        $this->service_cache[$serviceId]['sg'] = $servicegroup->getServiceGroupsForService($hostId, $serviceId);
        foreach ($this->service_cache[$serviceId]['sg'] as &$value) {
            if (is_null($value['host_host_id']) || $hostId == $value['host_host_id']) {
                $servicegroup->addServiceInSg(
                    $value['servicegroup_sg_id'],
                    $serviceId,
                    $this->service_cache[$serviceId]['service_description'],
                    $hostId,
                    $hostName
                );
            }
        }
    }

    private function getServiceByPollerCache() : void
    {
        $query = "SELECT $this->attributes_select FROM ns_host_relation, host_service_relation, service " .
            "LEFT JOIN extended_service_information ON extended_service_information.service_service_id = " .
            "service.service_id WHERE ns_host_relation.nagios_server_id = :server_id " .
            "AND ns_host_relation.host_host_id = host_service_relation.host_host_id " .
            "AND host_service_relation.service_service_id = service.service_id AND service_activate = '1'";
        $stmt = $this->backend_instance->db->prepare($query);
        $stmt->bindParam(':server_id', $this->poller_id, PDO::PARAM_INT);
        $stmt->execute();

        while (($value = $stmt->fetch(PDO::FETCH_ASSOC))) {
            $this->service_cache[$value['service_id']] = $value;
        }
    }

    private function getServiceCache() : void
    {
        $query = "SELECT $this->attributes_select FROM service " .
            "LEFT JOIN extended_service_information ON extended_service_information.service_service_id = " .
            "service.service_id WHERE service_register = '1' AND service_activate = '1'";
        $stmt = $this->backend_instance->db->prepare($query);
        $stmt->execute();
        $this->service_cache = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    /**
     * @param int $serviceId
     * @param array $attr
     */
    public function addServiceCache(int $serviceId, $attr = array()): void
    {
        $this->service_cache[$serviceId] = $attr;
    }

    /**
     * @param int $serviceId
     */
    private function getServiceFromId(int $serviceId): void
    {
        if (is_null($this->stmt_service)) {
            $query = "SELECT $this->attributes_select FROM service " .
                "LEFT JOIN extended_service_information ON extended_service_information.service_service_id = " .
                "service.service_id WHERE service_id = :service_id AND service_activate = '1'";
            $this->stmt_service = $this->backend_instance->db->prepare($query);
        }
        $this->stmt_service->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $this->stmt_service->execute();
        $results = $this->stmt_service->fetchAll(PDO::FETCH_ASSOC);
        $this->service_cache[$serviceId] = array_pop($results);
    }

    /**
     * @param int $hostId
     * @param int $serviceId
     * @param bool $isOnlyContactHost
     */
    protected function getContactsFromHost(int $hostId, int $serviceId, bool $isOnlyContactHost) : void
    {
        if ($isOnlyContactHost) {
            $host = Host::getInstance($this->dependencyInjector);
            $this->service_cache[$serviceId]['contacts'] = $host->getString($hostId, 'contacts');
            $this->service_cache[$serviceId]['contact_groups'] = $host->getString($hostId, 'contact_groups');
            $this->service_cache[$serviceId]['contact_from_host'] = 1;
        } elseif (
            empty($this->service_cache[$serviceId]['contacts'])
            && empty($this->service_cache[$serviceId]['contact_groups'])
        ) {
            $this->service_cache[$serviceId]['contact_from_host'] = 0;
            $host = Host::getInstance($this->dependencyInjector);
            $this->service_cache[$serviceId]['contacts'] = $host->getString($hostId, 'contacts');
            $this->service_cache[$serviceId]['contact_groups'] = $host->getString($hostId, 'contact_groups');
            $this->service_cache[$serviceId]['contact_from_host'] = 1;
        }
    }

    /**
     * @param $service (passing by Reference)
     * @return array
     */
    private function manageCumulativeInheritance(array &$service): array
    {
        $results = array('cg' => $service['contact_groups_cache'], 'contact' => $service['contacts_cache']);

        $servicesTpl = ServiceTemplate::getInstance($this->dependencyInjector)->service_cache;
        $serviceId = isset($this->service_cache[$service['service_id']]['service_template_model_stm_id'])
            ? $this->service_cache[$service['service_id']]['service_template_model_stm_id']
            : null;
        $serviceIdTopLevel = $serviceId;

        if (!is_null($serviceIdTopLevel) && !isset($servicesTpl[$serviceIdTopLevel]['contacts_computed_cache'])) {
            $contacts = array();
            $cg = array();
            $loop = array();
            while (!is_null($serviceId)) {
                if (isset($loop[$serviceId]) || ! isset($servicesTpl[$serviceId])) {
                    break;
                }
                $loop[$serviceId] = 1;
                // if notifications_enabled is disabled. We don't go in branch
                if (!is_null($servicesTpl[$serviceId]['notifications_enabled'])
                    && (int)$servicesTpl[$serviceId]['notifications_enabled'] === 0) {
                    break;
                }

                if (count($servicesTpl[$serviceId]['contact_groups_cache']) > 0) {
                    $cg = array_merge($cg, $servicesTpl[$serviceId]['contact_groups_cache']);
                }
                if (count($servicesTpl[$serviceId]['contacts_cache']) > 0) {
                    $contacts = array_merge($contacts, $servicesTpl[$serviceId]['contacts_cache']);
                }

                $serviceId = isset($servicesTpl[$serviceId]['service_template_model_stm_id'])
                    ? $servicesTpl[$serviceId]['service_template_model_stm_id']
                    : null;
            }

            $servicesTpl[$serviceIdTopLevel]['contacts_computed_cache'] = array_unique($contacts);
            $servicesTpl[$serviceIdTopLevel]['contact_groups_computed_cache'] = array_unique($cg);
        }

        if (!is_null($serviceIdTopLevel)) {
            $results['cg'] = array_unique(
                array_merge($results['cg'],
                $servicesTpl[$serviceIdTopLevel]['contact_groups_computed_cache']),
                SORT_NUMERIC
            );
            $results['contact'] = array_unique(
                array_merge($results['contact'],
                $servicesTpl[$serviceIdTopLevel]['contacts_computed_cache']),
                SORT_NUMERIC
            );
        }
        return $results;
    }

    /**
     * @param array $service (passing by Reference)
     * @param string $attribute
     * @return array
     */
    private function manageCloseInheritance(array &$service, string $attribute): array
    {
        if (count($service[$attribute . '_cache']) > 0) {
            return $service[$attribute . '_cache'];
        }

        $servicesTpl = ServiceTemplate::getInstance($this->dependencyInjector)->service_cache;
        $serviceId = isset($this->service_cache[$service['service_id']]['service_template_model_stm_id'])
            ? $this->service_cache[$service['service_id']]['service_template_model_stm_id']
            : null;
        $serviceIdTopLevel = $serviceId;

        if (!is_null($serviceIdTopLevel) && !isset($servicesTpl[$serviceIdTopLevel][$attribute . '_computed_cache'])) {
            $servicesTpl[$serviceIdTopLevel][$attribute . '_computed_cache'] = array();
            $loop = array();
            while (!is_null($serviceId)) {
                if (isset($loop[$serviceId])) {
                    break;
                }
                $loop[$serviceId] = 1;
                // if notifications_enabled is disabled. We don't go in branch
                if (!is_null($servicesTpl[$serviceId]['notifications_enabled'])
                    && (int)$servicesTpl[$serviceId]['notifications_enabled'] === 0) {
                    break;
                }

                if (count($servicesTpl[$serviceId][$attribute . '_cache']) > 0) {
                    $servicesTpl[$serviceIdTopLevel][$attribute . '_computed_cache'] =
                        $servicesTpl[$serviceId][$attribute . '_cache'];
                    break;
                }

                $serviceId = isset($servicesTpl[$serviceId]['service_template_model_stm_id'])
                    ? $servicesTpl[$serviceId]['service_template_model_stm_id']
                    : null;
            }
            return $servicesTpl[$serviceIdTopLevel][$attribute . '_computed_cache'];
        }
        return array();
    }

    /**
     * @param array $service
     * @param string $attribute
     * @param string $attributeAdditive
     * @return array
     */
    private function manageVerticalInheritance(array &$service, string $attribute, string $attributeAdditive): array
    {
        $results = $service[$attribute . '_cache'];
        if (count($results) > 0 &&
            (is_null($service[$attributeAdditive]) || $service[$attributeAdditive] != 1)) {
            return $results;
        }

        $servicesTpl = ServiceTemplate::getInstance($this->dependencyInjector)->service_cache;
        $serviceId = isset($this->service_cache[$service['service_id']]['service_template_model_stm_id'])
            ? $this->service_cache[$service['service_id']]['service_template_model_stm_id']
            : null;
        $serviceIdTopLevel = $serviceId;
        $computedCache = array();
        if (!is_null($serviceIdTopLevel) && !isset($servicesTpl[$serviceIdTopLevel][$attribute . '_computed_cache'])) {
            $loop = array();
            while (!is_null($serviceId)) {
                if (isset($loop[$serviceId])) {
                    break;
                }
                $loop[$serviceId] = 1;

                if (!is_null($servicesTpl[$serviceId]['notifications_enabled'])
                    && (int)$servicesTpl[$serviceId]['notifications_enabled'] === 0) {
                    break;
                }

                if (count($servicesTpl[$serviceId][$attribute . '_cache']) > 0) {
                    $computedCache = array_merge($computedCache, $servicesTpl[$serviceId][$attribute . '_cache']);
                    if (is_null($servicesTpl[$serviceId][$attributeAdditive])
                        || $servicesTpl[$serviceId][$attributeAdditive] != 1) {
                        break;
                    }
                }
                $serviceId = isset($servicesTpl[$serviceId]['service_template_model_stm_id'])
                    ? $servicesTpl[$serviceId]['service_template_model_stm_id']
                    : null;
            }
            $servicesTpl[$serviceIdTopLevel][$attribute . '_computed_cache'] = array_unique($computedCache);
        }

        if (!is_null($serviceIdTopLevel)) {
            $results = array_unique(
                array_merge($results, $servicesTpl[$serviceIdTopLevel][$attribute . '_computed_cache']),
                SORT_NUMERIC
            );
        }
        return $results;
    }

    /**
     * @param array $service
     * @param array $cg
     */
    private function setContactGroups(array &$service, array $cg = []) : void
    {
        $cgInstance = Contactgroup::getInstance($this->dependencyInjector);
        $cgResult = '';
        $cgResultAppend = '';
        foreach ($cg as $cgId) {
            $tmp = $cgInstance->generateFromCgId($cgId);
            if (!is_null($tmp)) {
                $cgResult .= $cgResultAppend . $tmp;
                $cgResultAppend = ',';
            }
        }
        if ($cgResult != '') {
            $service['contact_groups'] = $cgResult;
        }
    }

    /**
     * @param array $service
     * @param array $contacts
     */
    private function setContacts(array &$service, array $contacts = []): void
    {
        $contactInstance = Contact::getInstance($this->dependencyInjector);
        $contactResult = '';
        $contactResultAppend = '';
        foreach ($contacts as $contactId) {
            $tmp = $contactInstance->generateFromContactId($contactId);
            if (!is_null($tmp)) {
                $contactResult .= $contactResultAppend . $tmp;
                $contactResultAppend = ',';
            }
        }
        if ($contactResult != '') {
            $service['contacts'] = $contactResult;
        }
    }

    /**
     * @param array $service
     * @param bool $generate
     * @return array
     */
    protected function manageNotificationInheritance(array &$service, bool $generate = true): array
    {
        $results = array('cg' => array(), 'contact' => array());

        if (!is_null($service['notifications_enabled']) && (int)$service['notifications_enabled'] === 0) {
            return $results;
        }
        if (isset($service['service_use_only_contacts_from_host']) &&
            $service['service_use_only_contacts_from_host'] == 1
        ) {
            $service['contact_groups'] = '';
            $service['contacts'] = '';
            return $results;
        }

        $mode = $this->getInheritanceMode();
        if ($mode === self::CUMULATIVE_NOTIFICATION) {
            $results = $this->manageCumulativeInheritance($service);
        } elseif ($mode === self::CLOSE_NOTIFICATION) {
            $results['cg'] = $this->manageCloseInheritance($service, 'contact_groups');
            $results['contact'] = $this->manageCloseInheritance($service, 'contacts');
        } else {
            $results['cg'] = $this->manageVerticalInheritance($service, 'contact_groups', 'cg_additive_inheritance');
            $results['contact'] = $this->manageVerticalInheritance(
                $service,
                'contacts',
                'contact_additive_inheritance'
            );
        }

        if ($generate) {
            $this->setContacts($service, $results['contact']);
            $this->setContactGroups($service, $results['cg']);
        }

        return $results;
    }

    /**
     * @param int $serviceIdArg
     */
    private function getSeverityInServiceChain(int $serviceIdArg): void
    {
        if (isset($this->service_cache[$serviceIdArg]['severity_id'])) {
            return;
        }

        $this->service_cache[$serviceIdArg]['severity_id'] = Severity::getInstance($this->dependencyInjector)
            ->getServiceSeverityByServiceId($serviceIdArg);
        $severity = Severity::getInstance($this->dependencyInjector)
            ->getServiceSeverityById($this->service_cache[$serviceIdArg]['severity_id']);
        if (!is_null($severity)) {
            $this->service_cache[$serviceIdArg]['macros']['_CRITICALITY_LEVEL'] = $severity['level'];
            $this->service_cache[$serviceIdArg]['macros']['_CRITICALITY_ID'] = $severity['sc_id'];
            return;
        }

        // Check from service templates
        $loop = array();
        $servicesTpl = &ServiceTemplate::getInstance($this->dependencyInjector)->service_cache;
        $servicesTopTpl = isset($this->service_cache[$serviceIdArg]['service_template_model_stm_id'])
            ? $this->service_cache[$serviceIdArg]['service_template_model_stm_id']
            : null;
        $serviceId = $servicesTopTpl;
        $severityId = null;
        while (!is_null($serviceId)) {
            if (isset($loop[$serviceId])) {
                break;
            }
            if (isset($servicesTpl[$serviceId]['severity_id_from_below'])) {
                $this->service_cache[$serviceIdArg]['severity_id'] = $servicesTpl[$serviceId]['severity_id_from_below'];
                break;
            }
            $loop[$serviceId] = 1;
            if (isset($servicesTpl[$serviceId]['severity_id'])
                && !is_null($servicesTpl[$serviceId]['severity_id'])
            ) {
                $this->service_cache[$serviceIdArg]['severity_id'] = $servicesTpl[$serviceId]['severity_id'];
                $servicesTpl[$servicesTopTpl]['severity_id_from_below'] = $servicesTpl[$serviceId]['severity_id'];
                break;
            }
            $serviceId = isset($servicesTpl[$serviceId]['service_template_model_stm_id'])
                ? $servicesTpl[$serviceId]['service_template_model_stm_id']
                : null;
        }

        return;
    }

    /**
     * @param int $hostId
     * @param int $serviceId
     */
    protected function getSeverity(int $hostId, int $serviceId) : void
    {
        $this->service_cache[$serviceId]['severity_from_host'] = 0;
        $this->getSeverityInServiceChain($serviceId);
        // Get from the hosts
        if (is_null($this->service_cache[$serviceId]['severity_id'])) {
            $this->service_cache[$serviceId]['severity_from_host'] = 1;
            $severity = Host::getInstance($this->dependencyInjector)->getSeverityForService($hostId);
            if (!is_null($severity)) {
                $serviceSeverity = Severity::getInstance($this->dependencyInjector)
                    ->getServiceSeverityMappingHostSeverityByName($severity['hc_name']);
                if (!is_null($serviceSeverity)) {
                    $this->service_cache[$serviceId]['macros']['_CRITICALITY_LEVEL'] = $serviceSeverity['level'];
                    $this->service_cache[$serviceId]['macros']['_CRITICALITY_ID'] = $serviceSeverity['sc_id'];
                }
            }
        }
    }

    protected function clean(&$service)
    {
        if ($service['severity_from_host'] == 1) {
            unset($service['macros']['_CRITICALITY_LEVEL']);
            unset($service['macros']['_CRITICALITY_ID']);
        }
    }

    /**
     * @param int $hostId
     * @param int $serviceId
     */
    public function addGeneratedServices(int $hostId, int $serviceId): void
    {
        if (!isset($this->generated_services[$hostId])) {
            $this->generated_services[$hostId] = array();
        }
        $this->generated_services[$hostId][] = $serviceId;
    }

    /**
     * @return array
     */
    public function getGeneratedServices(): array
    {
        return $this->generated_services;
    }

    private function buildCache() : void
    {
        if ($this->done_cache == 1 || ($this->use_cache == 0 && $this->use_cache_poller == 0)) {
            return;
        }
        if ($this->use_cache_poller == 1) {
            $this->getServiceByPollerCache();
        } else {
            $this->getServiceCache();
        }
        $this->done_cache = 1;
    }

    /**
     * @param int $hostId
     * @param string $hostName
     * @param int|null $serviceId
     * @param int $byHg default 0
     * @return string|null service description
     */
    public function generateFromServiceId(int $hostId, string $hostName, ?int $serviceId, int $byHg = 0): ?string
    {
        if (is_null($serviceId)) {
            return null;
        }

        $this->buildCache();

        if (($this->use_cache == 0 || $byHg == 1) && !isset($this->service_cache[$serviceId])) {
            $this->getServiceFromId($serviceId);
        }
        if (!isset($this->service_cache[$serviceId]) || is_null($this->service_cache[$serviceId])) {
            return null;
        }
        // We skip anomalydetection services represented by enum value '3'
        if ($this->service_cache[$serviceId]['register'] === '3') {
            return null;
        }
        if ($this->checkGenerate($hostId . '.' . $serviceId)) {
            return $this->service_cache[$serviceId]['service_description'];
        }

        // we reset notifications for service multiples and hg
        $this->service_cache[$serviceId]['contacts'] = '';
        $this->service_cache[$serviceId]['contact_groups'] = '';

        $this->getImages($this->service_cache[$serviceId]);
        $this->getMacros($this->service_cache[$serviceId]);
        $this->service_cache[$serviceId]['macros']['_SERVICE_ID'] = $serviceId;
        // useful for servicegroup on servicetemplate
        $service_template = ServiceTemplate::getInstance($this->dependencyInjector);
        $service_template->resetLoop();
        $service_template->current_host_id = $hostId;
        $service_template->current_host_name = $hostName;
        $service_template->current_service_id = $serviceId;
        $service_template->current_service_description = $this->service_cache[$serviceId]['service_description'];
        $this->getServiceTemplates($this->service_cache[$serviceId]);

        $this->getServiceCommands($this->service_cache[$serviceId]);
        $this->getServicePeriods($this->service_cache[$serviceId]);

        $this->getContactGroups($this->service_cache[$serviceId]);
        $this->getContacts($this->service_cache[$serviceId]);

        $this->manageNotificationInheritance($this->service_cache[$serviceId]);

        if (is_null($this->service_cache[$serviceId]['notifications_enabled'])
            || (int)$this->service_cache[$serviceId]['notifications_enabled'] !== 0) {
            $this->getContactsFromHost(
                $hostId,
                $serviceId,
                $this->service_cache[$serviceId]['service_use_only_contacts_from_host'] == '1'
            );
        }

        $this->getSeverity($hostId, $serviceId);
        $this->getServiceGroups($serviceId, $hostId, $hostName);
        $this->generateObjectInFile(
            $this->service_cache[$serviceId] + array('host_name' => $hostName),
            $hostId . '.' . $serviceId
        );
        $this->addGeneratedServices($hostId, $serviceId);
        $this->clean($this->service_cache[$serviceId]);
        return $this->service_cache[$serviceId]['service_description'];
    }

    public function set_poller($pollerId) : void
    {
        $this->poller_id = $pollerId;
    }

    /**
     * @param int $serviceId
     * @return array
     */
    public function getCgAndContacts(int $serviceId) : array
    {
        $this->getServiceFromId($serviceId);
        $this->getContactGroups($this->service_cache[$serviceId]);
        $this->getContacts($this->service_cache[$serviceId]);
        $serviceTplInstance = ServiceTemplate::getInstance($this->dependencyInjector);
        
        $serviceTplId = isset($this->service_cache[$serviceId]['service_template_model_stm_id'])
            ? $this->service_cache[$serviceId]['service_template_model_stm_id']
            : null;
        $loop = array();
        while (!is_null($serviceTplId)) {
            if (isset($loop[$serviceTplId])) {
                break;
            }
            $loop[$serviceTplId] = 1;

            $serviceTplInstance->getServiceFromId($serviceTplId);
            if (is_null($serviceTplInstance->service_cache[$serviceTplId])) {
                break;
            }
            $serviceTplInstance->getContactGroups($serviceTplInstance->service_cache[$serviceTplId]);
            $serviceTplInstance->getContacts($serviceTplInstance->service_cache[$serviceTplId]);

            $serviceTplId = isset($serviceTplInstance->service_cache[$serviceTplId]['service_template_model_stm_id'])
                    ? $serviceTplInstance->service_cache[$serviceTplId]['service_template_model_stm_id']
                    : null;
        }
        return $this->manageNotificationInheritance($this->service_cache[$serviceId], false);
    }

    public function reset()
    {
        # We reset it by poller (dont need all. We save memory)
        if ($this->use_cache_poller == 1) {
            $this->service_cache = array();
            $this->done_cache = 0;
        }
        $this->generated_services = array();
        parent::reset();
    }
}
