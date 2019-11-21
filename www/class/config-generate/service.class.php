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
    protected $generated_services = array(); # for index_data build and escalation
    protected $generate_filename = 'services.cfg';
    protected $object_name = 'service';
    public $poller_id = null; # for by poller cache

    public function use_cache()
    {
        $this->use_cache = 1;
    }

    private function getServiceGroups($service_id, $host_id, $host_name)
    {
        $servicegroup = Servicegroup::getInstance($this->dependencyInjector);
        $this->service_cache[$service_id]['sg'] = $servicegroup->getServiceGroupsForService($host_id, $service_id);
        foreach ($this->service_cache[$service_id]['sg'] as &$value) {
            if (is_null($value['host_host_id']) || $host_id == $value['host_host_id']) {
                $servicegroup->addServiceInSg(
                    $value['servicegroup_sg_id'],
                    $service_id,
                    $this->service_cache[$service_id]['service_description'],
                    $host_id,
                    $host_name
                );
            }
        }
    }

    private function getServiceByPollerCache()
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

    private function getServiceCache()
    {
        $query = "SELECT $this->attributes_select FROM service " .
            "LEFT JOIN extended_service_information ON extended_service_information.service_service_id = " .
            "service.service_id WHERE service_register = '1' AND service_activate = '1'";
        $stmt = $this->backend_instance->db->prepare($query);
        $stmt->execute();
        $this->service_cache = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    }

    public function addServiceCache($service_id, $attr = array())
    {
        $this->service_cache[$service_id] = $attr;
    }

    private function getServiceFromId($service_id)
    {
        if (is_null($this->stmt_service)) {
            $query = "SELECT $this->attributes_select FROM service " .
                "LEFT JOIN extended_service_information ON extended_service_information.service_service_id = " .
                "service.service_id WHERE service_id = :service_id AND service_activate = '1'";
            $this->stmt_service = $this->backend_instance->db->prepare($query);
        }
        $this->stmt_service->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $this->stmt_service->execute();
        $results = $this->stmt_service->fetchAll(PDO::FETCH_ASSOC);
        $this->service_cache[$service_id] = array_pop($results);
    }

    private function getContactsFromHost($host_id, $service_id, $sOnlyContactHost)
    {
        if ($sOnlyContactHost == 1) {
            $host = Host::getInstance($this->dependencyInjector);
            $this->service_cache[$service_id]['contacts'] = $host->getString($host_id, 'contacts');
            $this->service_cache[$service_id]['contact_groups'] = $host->getString($host_id, 'contact_groups');
            $this->service_cache[$service_id]['contact_from_host'] = 1;
        } elseif (
            empty($this->service_cache[$service_id]['contacts'])
            && empty($this->service_cache[$service_id]['contact_groups'])
        ) {
            $this->service_cache[$service_id]['contact_from_host'] = 0;
            $host = Host::getInstance($this->dependencyInjector);
            $this->service_cache[$service_id]['contacts'] = $host->getString($host_id, 'contacts');
            $this->service_cache[$service_id]['contact_groups'] = $host->getString($host_id, 'contact_groups');
            $this->service_cache[$service_id]['contact_from_host'] = 1;
        }
    }

    private function manageCumulativeInheritance(&$service)
    {
        $results = array('cg' => $service['contact_groups_cache'], 'contact' => $service['contacts_cache']);

        $services_tpl = ServiceTemplate::getInstance($this->dependencyInjector)->service_cache;
        $service_id = isset($this->service_cache[$service['service_id']]['service_template_model_stm_id'])
            ? $this->service_cache[$service['service_id']]['service_template_model_stm_id']
            : null;
        $service_id_toplevel = $service_id;
        
        if (!is_null($service_id_toplevel) && !isset($services_tpl[$service_id_toplevel]['contacts_computed_cache'])) {
            $contacts = array();
            $cg = array();
            $loop = array();
            while (!is_null($service_id)) {
                if (isset($loop[$service_id])) {
                    break;
                }
                $loop[$service_id] = 1;
                // if notifications_enabled is disabled. We don't go in branch
                if (!is_null($services_tpl[$service_id]['notifications_enabled']) && $services_tpl[$service_id]['notifications_enabled'] === 0) {
                    break;
                }

                if (count($services_tpl[$service_id]['contact_groups_cache']) > 0) {
                    $cg = array_merge($cg, $services_tpl[$service_id]['contact_groups_cache']);
                }
                if (count($services_tpl[$service_id]['contacts_cache']) > 0) {
                    $contacts = array_merge($contacts, $services_tpl[$service_id]['contacts_cache']);
                }

                $service_id = isset($services_tpl[$service_id]['service_template_model_stm_id'])
                    ? $services_tpl[$service_id]['service_template_model_stm_id']
                    : null;
            }

            $services_tpl[$service_id_toplevel]['contacts_computed_cache'] = array_unique($contacts);
            $services_tpl[$service_id_toplevel]['contact_groups_computed_cache'] = array_unique($cg);
        }

        if (!is_null($service_id_toplevel)) {
            $results['cg'] = array_unique(array_merge($results['cg'], services_tpl[$service_id_toplevel]['contact_groups_computed_cache']), SORT_NUMERIC);
            $results['contact'] = array_unique(array_merge($results['contact'], $services_tpl[$service_id_toplevel]['contacts_computed_cache']), SORT_NUMERIC);
        }
        return $results;
    }

    private function manageCloseInheritance(&$service, $attribute)
    {
        if (count($service[$attribute . '_cache']) > 0) {
            return $service[$attribute . '_cache'];
        }

        $services_tpl = ServiceTemplate::getInstance($this->dependencyInjector)->service_cache;
        $service_id = isset($this->service_cache[$service['service_id']]['service_template_model_stm_id'])
            ? $this->service_cache[$service['service_id']]['service_template_model_stm_id']
            : null;
        $service_id_toplevel = $service_id;
        
        if (!is_null($service_id_toplevel) && !isset($services_tpl[$service_id_toplevel][$attribute . '_computed_cache'])) {
            $services_tpl[$service_id_toplevel][$attribute . '_computed_cache'] = array();
            $loop = array();
            while (!is_null($service_id)) {
                if (isset($loop[$service_id])) {
                    break;
                }
                $loop[$service_id] = 1;
                // if notifications_enabled is disabled. We don't go in branch
                if (!is_null($services_tpl[$service_id]['notifications_enabled']) && $services_tpl[$service_id]['notifications_enabled'] === 0) {
                    break;
                }

                if (count($services_tpl[$service_id][$attribute . '_cache']) > 0) {
                    $services_tpl[$service_id_toplevel][$attribute . '_computed_cache'] = $services_tpl[$service_id][$attribute . '_cache'];
                    break;
                }

                $service_id = isset($services_tpl[$service_id]['service_template_model_stm_id'])
                    ? $services_tpl[$service_id]['service_template_model_stm_id']
                    : null;
            }

            return $services_tpl[$service_id_toplevel][$attribute . '_computed_cache'];
        }

        return array();
    }

    private function manageVerticalInheritance(&$service, $attribute, $attribute_additive)
    {
        $results = $service[$attribute . '_cache'];
        if (count($results) > 0 && 
            (is_null($service[$attribute_additive]) || $service[$attribute_additive] != 1)) {
            return $results;
        }

        $services_tpl = ServiceTemplate::getInstance($this->dependencyInjector)->service_cache;
        $service_id = isset($this->service_cache[$service['service_id']]['service_template_model_stm_id'])
            ? $this->service_cache[$service['service_id']]['service_template_model_stm_id']
            : null;
        $service_id_toplevel = $service_id;
        $computed_cache = array();
        if (!is_null($service_id_toplevel) && !isset($services_tpl[$service_id_toplevel][$attribute . '_computed_cache'])) {
            $loop = array();
            while (!is_null($service_id)) {
                if (isset($loop[$service_id])) {
                    break;
                }
                $loop[$service_id] = 1;

                if (!is_null($services_tpl[$service_id]['notifications_enabled']) && $services_tpl[$service_id]['notifications_enabled'] === 0) {
                    break;
                }

                if (count($services_tpl[$service_id][$attribute . '_cache']) > 0) {
                    $computed_cache = array_merge($computed_cache, $services_tpl[$service_id][$attribute . '_cache']);
                    if (is_null($services_tpl[$service_id][$attribute_additive]) || $services_tpl[$service_id][$attribute_additive] != 1) {
                        break;
                    }
                }

                $service_id = isset($services_tpl[$service_id]['service_template_model_stm_id'])
                    ? $services_tpl[$service_id]['service_template_model_stm_id']
                    : null;
            }

            $services_tpl[$service_id_toplevel][$attribute . '_computed_cache'] = array_unique($computed_cache);
        }

        if (!is_null($service_id_toplevel)) {
            $results = array_unique(array_merge($results, $services_tpl[$service_id_toplevel][$attribute . '_computed_cache']), SORT_NUMERIC);
        }
        return $results;
    }

    private function setContactGroups(&$service, $cg)
    {
        $cg = Contactgroup::getInstance($this->dependencyInjector);
        $cg_result = '';
        $cg_result_append = '';
        foreach ($cg as $cg_id) {
            $tmp = $cg->generateFromCgId($cg_id);
            if (!is_null($tmp)) {
                $cg_result .= $cg_result_append . $tmp;
                $cg_result_append = ',';
            }
        }
        if ($cg_result != '') {
            $service['contact_groups'] = $cg_result;
        }
    }

    private function setContacts(&$service, $contacts)
    {
        $contact = Contact::getInstance($this->dependencyInjector);
        $contact_result = '';
        $contact_result_append = '';
        foreach ($contacts as $contact_id) {
            $tmp = $contact->generateFromContactId($contact_id);
            if (!is_null($tmp)) {
                $contact_result .= $contact_result_append . $tmp;
                $contact_result_append = ',';
            }
        }
        if ($contact_result != '') {
            $service['contacts'] = $contact_result;
        }
    }

    private function manageNotificationInheritance(&$service)
    {
        if (!is_null($service['notifications_enabled']) && $service['notifications_enabled'] === 0) {
            return ;
        }
        if (isset($service['service_use_only_contacts_from_host']) &&
            $service['service_use_only_contacts_from_host'] == 1
        ) {
            $service['contact_groups'] = '';
            $service['contacts'] = '';
            return ;
        }

        $mode = $this->getInheritanceMode();
        $results = array('cg' => array(), 'contact' => array());
        if ($mode === self::CUMULATIVE_NOTIFICATION) {
            $results = $this->manageCumulativeInheritance($service);
        } else if ($mode === self::CLOSE_NOTIFICATION) {
            $results['cg'] = $this->manageCloseInheritance($service, 'contact_groups');
            $results['contact'] = $this->manageCloseInheritance($service, 'contacts');
        } else {
            $results['cg'] = $this->manageVerticalInheritance($service, 'contact_groups', 'cg_additive_inheritance');
            $results['contact'] = $this->manageVerticalInheritance($service, 'contacts', 'contact_additive_inheritance');
        }

        $this->setContacts($service, $results['contact']);
        $this->setContactGroups($service, $results['cg']);
    }

    private function getSeverityInServiceChain($service_id_arg)
    {
        if (isset($this->service_cache[$service_id_arg]['severity_id'])) {
            return 0;
        }

        $this->service_cache[$service_id_arg]['severity_id'] = Severity::getInstance($this->dependencyInjector)
            ->getServiceSeverityByServiceId($service_id_arg);
        $severity = Severity::getInstance($this->dependencyInjector)
            ->getServiceSeverityById($this->service_cache[$service_id_arg]['severity_id']);
        if (!is_null($severity)) {
            $this->service_cache[$service_id_arg]['macros']['_CRITICALITY_LEVEL'] = $severity['level'];
            $this->service_cache[$service_id_arg]['macros']['_CRITICALITY_ID'] = $severity['sc_id'];
            return 0;
        }

        # Check from service templates
        $loop = array();
        $services_tpl = &ServiceTemplate::getInstance($this->dependencyInjector)->service_cache;
        $services_top_tpl = isset($this->service_cache[$service_id_arg]['service_template_model_stm_id'])
            ? $this->service_cache[$service_id_arg]['service_template_model_stm_id']
            : null;
        $service_id = $services_top_tpl;
        $severity_id = null;
        while (!is_null($service_id)) {
            if (isset($loop[$service_id])) {
                break;
            }
            if (isset($services_tpl[$service_id]['severity_id_from_below'])) {
                $this->service_cache[$service_id_arg]['severity_id'] =
                    $services_tpl[$service_id]['severity_id_from_below'];
                break;
            }
            $loop[$service_id] = 1;
            if (isset($services_tpl[$service_id]['severity_id'])
                && !is_null($services_tpl[$service_id]['severity_id'])
            ) {
                $this->service_cache[$service_id_arg]['severity_id'] = $services_tpl[$service_id]['severity_id'];
                $services_tpl[$services_top_tpl]['severity_id_from_below'] = $services_tpl[$service_id]['severity_id'];
                break;
            }
            $service_id = isset($services_tpl[$service_id]['service_template_model_stm_id'])
                ? $services_tpl[$service_id]['service_template_model_stm_id']
                : null;
        }

        return 0;
    }

    protected function getSeverity($host_id, $service_id)
    {
        $this->service_cache[$service_id]['severity_from_host'] = 0;
        $this->getSeverityInServiceChain($service_id);
        # Get from the hosts
        if (is_null($this->service_cache[$service_id]['severity_id'])) {
            $this->service_cache[$service_id]['severity_from_host'] = 1;
            $severity = Host::getInstance($this->dependencyInjector)->getSeverityForService($host_id);
            if (!is_null($severity)) {
                $service_severity = Severity::getInstance($this->dependencyInjector)
                    ->getServiceSeverityMappingHostSeverityByName($severity['hc_name']);
                if (!is_null($service_severity)) {
                    $this->service_cache[$service_id]['macros']['_CRITICALITY_LEVEL'] = $service_severity['level'];
                    $this->service_cache[$service_id]['macros']['_CRITICALITY_ID'] = $service_severity['sc_id'];
                }
            }
        }

        return null;
    }

    private function clean(&$service)
    {
        #if ($service['contact_from_host'] == 1) {
        #    $service['contacts'] = null;
        #    $service['contact_groups'] = null;
        #    $service['contact_from_host'] = 0;
        #}

        if ($service['severity_from_host'] == 1) {
            unset($service['macros']['_CRITICALITY_LEVEL']);
            unset($service['macros']['_CRITICALITY_ID']);
        }
    }

    public function addGeneratedServices($host_id, $service_id)
    {
        if (!isset($this->generated_services[$host_id])) {
            $this->generated_services[$host_id] = array();
        }
        $this->generated_services[$host_id][] = $service_id;
    }

    public function getGeneratedServices()
    {
        return $this->generated_services;
    }

    private function buildCache()
    {
        if ($this->done_cache == 1 ||
            ($this->use_cache == 0 && $this->use_cache_poller == 0)
        ) {
            return 0;
        }

        if ($this->use_cache_poller == 1) {
            $this->getServiceByPollerCache();
        } else {
            $this->getServiceCache();
        }

        $this->done_cache = 1;
    }

    public function generateFromServiceId($host_id, $host_name, $service_id, $by_hg = 0)
    {
        if (is_null($service_id)) {
            return null;
        }

        $this->buildCache();

        if (($this->use_cache == 0 || $by_hg == 1) && !isset($this->service_cache[$service_id])) {
            $this->getServiceFromId($service_id);
        }
        if (!isset($this->service_cache[$service_id]) || is_null($this->service_cache[$service_id])) {
            return null;
        }
        if ($this->checkGenerate($host_id . '.' . $service_id)) {
            return $this->service_cache[$service_id]['service_description'];
        }

        $this->getImages($this->service_cache[$service_id]);
        $this->getMacros($this->service_cache[$service_id]);
        $this->service_cache[$service_id]['macros']['_SERVICE_ID'] = $service_id;
        # useful for servicegroup on servicetemplate
        $service_template = ServiceTemplate::getInstance($this->dependencyInjector);
        $service_template->resetLoop();
        $service_template->current_host_id = $host_id;
        $service_template->current_host_name = $host_name;
        $service_template->current_service_id = $service_id;
        $service_template->current_service_description = $this->service_cache[$service_id]['service_description'];
        $this->getServiceTemplates($this->service_cache[$service_id]);
        $this->getServiceCommands($this->service_cache[$service_id]);
        $this->getServicePeriods($this->service_cache[$service_id]);

        $this->getContactGroups($this->service_cache[$service_id]);
        $this->getContacts($this->service_cache[$service_id]);

        $this->manageNotificationInheritance($this->service_cache[$service_id]);

        # By default in centengine 1.4.15
        $this->getContactsFromHost(
            $host_id,
            $service_id,
            $this->service_cache[$service_id]['service_use_only_contacts_from_host']
        );

        $this->getSeverity($host_id, $service_id);
        $this->getServiceGroups($service_id, $host_id, $host_name);
        $this->generateObjectInFile(
            $this->service_cache[$service_id] + array('host_name' => $host_name),
            $host_id . '.' . $service_id
        );
        $this->addGeneratedServices($host_id, $service_id);
        $this->clean($this->service_cache[$service_id]);
        return $this->service_cache[$service_id]['service_description'];
    }

    public function set_poller($poller_id)
    {
        $this->poller_id = $poller_id;
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
