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

require_once dirname(__FILE__) . '/abstract/host.class.php';
require_once dirname(__FILE__) . '/abstract/service.class.php';

class Host extends AbstractHost
{
    const VERTICAL_NOTIFICATION = 1;
    const CLOSE_NOTIFICATION = 2;
    const CUMULATIVE_NOTIFICATION = 3;
    
    protected $hosts_by_name = array();
    protected $hosts = null;
    protected $generate_filename = 'hosts.cfg';
    protected $object_name = 'host';
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
        }
    }

    private function getServicesByHg(&$host)
    {
        if (count($host['hg']) == 0) {
            return 1;
        }
        if (is_null($this->stmt_service_sg)) {
            $query = "SELECT service_service_id FROM host_service_relation " .
                "JOIN hostgroup_relation ON (hostgroup_relation.hostgroup_hg_id = " .
                "host_service_relation.hostgroup_hg_id) WHERE hostgroup_relation.host_host_id = :host_id";
            $this->stmt_service_sg = $this->backend_instance->db->prepare($query);
        }
        $this->stmt_service_sg->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
        $this->stmt_service_sg->execute();
        $host['services_hg_cache'] = $this->stmt_service_sg->fetchAll(PDO::FETCH_COLUMN);

        $service = Service::getInstance($this->dependencyInjector);
        foreach ($host['services_hg_cache'] as $service_id) {
            $service->generateFromServiceId($host['host_id'], $host['host_name'], $service_id, 1);
        }
    }

    /**
     * @param array $host (passing by Reference)
     * @return array
     */
    private function manageCumulativeInheritance(array &$host): array
    {
        $results = array('cg' => array(), 'contact' => array());

        $hostsTpl = HostTemplate::getInstance($this->dependencyInjector)->hosts;
        foreach ($host['htpl'] as $hostIdTopLevel) {
            $stack = array($hostIdTopLevel);
            $loop = array();
            if (!isset($hostsTpl[$hostIdTopLevel]['contacts_computed_cache'])) {
                $contacts = array();
                $cg = array();
                while (($hostId = array_shift($stack))) {
                    if (isset($loop[$hostId]) || !isset($hostsTpl[$hostId])) {
                        continue;
                    }
                    $loop[$hostId] = 1;
                    // if notifications_enabled is disabled. We don't go in branch
                    if (!is_null($hostsTpl[$hostId]['notifications_enabled'])
                        && (int)$hostsTpl[$hostId]['notifications_enabled'] === 0) {
                        continue;
                    }

                    if (count($hostsTpl[$hostId]['contact_groups_cache']) > 0) {
                        $cg = array_merge($cg, $hostsTpl[$hostId]['contact_groups_cache']);
                    }
                    if (count($hostsTpl[$hostId]['contacts_cache']) > 0) {
                        $contacts = array_merge($contacts, $hostsTpl[$hostId]['contacts_cache']);
                    }

                    $stack = array_merge($hostsTpl[$hostId]['htpl'], $stack);
                }

                $hostsTpl[$hostIdTopLevel]['contacts_computed_cache'] = array_unique($contacts);
                $hostsTpl[$hostIdTopLevel]['contact_groups_computed_cache'] = array_unique($cg);
            }

            $results['cg'] = array_merge(
                $results['cg'],
                $hostsTpl[$hostIdTopLevel]['contact_groups_computed_cache']
            );
            $results['contact'] = array_merge(
                $results['contact'],
                $hostsTpl[$hostIdTopLevel]['contacts_computed_cache']
            );
        }

        $results['cg'] = array_unique(array_merge($results['cg'], $host['contact_groups_cache']), SORT_NUMERIC);
        $results['contact'] = array_unique(array_merge($results['contact'], $host['contacts_cache']), SORT_NUMERIC);
        return $results;
    }

    /**
     * @param array $host (passing by Reference)
     * @param string $attribute
     * @return array
     */
    private function manageCloseInheritance(array &$host, string $attribute): array
    {
        if (count($host[$attribute . '_cache']) > 0) {
            return $host[$attribute . '_cache'];
        }

        $hostsTpl = HostTemplate::getInstance($this->dependencyInjector)->hosts;
        foreach ($host['htpl'] as $hostIdTopLevel) {
            $stack = array($hostIdTopLevel);
            $loop = array();
            if (!isset($hostsTpl[$hostIdTopLevel][$attribute . '_computed_cache'])) {
                $hostsTpl[$hostIdTopLevel][$attribute . '_computed_cache'] = array();

                while (($hostId = array_shift($stack))) {
                    if (isset($loop[$hostId])) {
                        continue;
                    }
                    $loop[$hostId] = 1;

                    if (!is_null($hostsTpl[$hostId]['notifications_enabled'])
                        && (int)$hostsTpl[$hostId]['notifications_enabled'] === 0) {
                        continue;
                    }

                    if (count($hostsTpl[$hostId][$attribute . '_cache']) > 0) {
                        $hostsTpl[$hostIdTopLevel][$attribute . '_computed_cache'] =
                            $hostsTpl[$hostId][$attribute . '_cache'];
                        break;
                    }

                    $stack = array_merge($hostsTpl[$hostId]['htpl'], $stack);
                }
            }

            if (count($hostsTpl[$hostIdTopLevel][$attribute . '_computed_cache']) > 0) {
                return $hostsTpl[$hostIdTopLevel][$attribute . '_computed_cache'];
            }
        }
        return array();
    }

    /**
     * @param array $host
     * @param string $attribute
     * @param string $attributeAdditive
     * @return array
     */
    private function manageVerticalInheritance(array &$host, string $attribute, string $attributeAdditive): array
    {
        $results = $host[$attribute . '_cache'];
        if (count($results) > 0
            && (is_null($host[$attributeAdditive]) || $host[$attributeAdditive] != 1)) {
            return $results;
        }
        
        $hostsTpl = HostTemplate::getInstance($this->dependencyInjector)->hosts;
        $hostIdCache = null;
        foreach ($host['htpl'] as $hostIdTopLevel) {
            $computedCache = array();
            if (!isset($hostsTpl[$hostIdTopLevel][$attribute . '_computed_cache'])) {
                $stack = array(array($hostIdTopLevel, 1));
                $loop = array();
                $currentLevelCatch = null;
                while ((list($hostId, $level) = array_shift($stack))) {
                    if (!is_null($currentLevelCatch) && $currentLevelCatch >= $level) {
                        break;
                    }
                    if (isset($loop[$hostId])) {
                        continue;
                    }
                    $loop[$hostId] = 1;

                    if (!is_null($hostsTpl[$hostId]['notifications_enabled'])
                        && (int)$hostsTpl[$hostId]['notifications_enabled'] === 0) {
                        continue;
                    }

                    if (count($hostsTpl[$hostId][$attribute . '_cache']) > 0) {
                        $computedCache = array_merge($computedCache, $hostsTpl[$hostId][$attribute . '_cache']);
                        $currentLevelCatch = $level;
                        if (is_null($hostsTpl[$hostId][$attributeAdditive]) || $hostsTpl[$hostId][$attributeAdditive] != 1) {
                            break;
                        }
                    }

                    foreach (array_reverse($hostsTpl[$hostId]['htpl']) as $htplId) {
                        array_unshift($stack, array($htplId, $level + 1));
                    }
                }

                $hostsTpl[$hostIdTopLevel][$attribute . '_computed_cache'] = array_unique($computedCache);
            }

            if (count($hostsTpl[$hostIdTopLevel][$attribute . '_computed_cache']) > 0) {
                $hostIdCache = $hostIdTopLevel;
                break;
            }
        }

        if (!is_null($hostIdCache)) {
            $results = array_unique(
                array_merge($results, $hostsTpl[$hostIdCache][$attribute . '_computed_cache']),
                SORT_NUMERIC
            );
        }
        return $results;
    }

    /**
     * @param array $host
     * @param array $cg
     */
    private function setContactGroups(array &$host, array $cg = []) : void
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
            $host['contact_groups'] = $cgResult;
        }
    }

    /**
     * @param array $host
     * @param array $contacts
     */
    private function setContacts(array &$host, array $contacts = []) : void
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
            $host['contacts'] = $contactResult;
        }
    }

    /**
     * @param array $host
     * @param bool $generate
     * @return array
     */
    private function manageNotificationInheritance(array &$host, bool $generate = true): array
    {
        $results = array('cg' => array(), 'contact' => array());

        if (!is_null($host['notifications_enabled']) && (int)$host['notifications_enabled'] === 0) {
            return $results;
        }

        $mode = $this->getInheritanceMode();

        if ($mode === self::CUMULATIVE_NOTIFICATION) {
            $results = $this->manageCumulativeInheritance($host);
        } elseif ($mode === self::CLOSE_NOTIFICATION) {
            $results['cg'] = $this->manageCloseInheritance($host, 'contact_groups');
            $results['contact'] = $this->manageCloseInheritance($host, 'contacts');
        } else {
            $results['cg'] = $this->manageVerticalInheritance($host, 'contact_groups', 'cg_additive_inheritance');
            $results['contact'] = $this->manageVerticalInheritance($host, 'contacts', 'contact_additive_inheritance');
        }

        if ($generate) {
            $this->setContacts($host, $results['contact']);
            $this->setContactGroups($host, $results['cg']);
        }

        return $results;
    }

    public function getSeverityForService($host_id)
    {
        return $this->hosts[$host_id]['severity_id_for_services'];
    }

    protected function getSeverity($host_id_arg)
    {
        $host_id = null;
        $loop = array();

        $severity_instance = Severity::getInstance($this->dependencyInjector);
        $severity_id = $severity_instance->getHostSeverityByHostId($host_id_arg);
        $this->hosts[$host_id_arg]['severity'] = $severity_instance->getHostSeverityById($severity_id);
        if (!is_null($this->hosts[$host_id_arg]['severity'])) {
            $this->hosts[$host_id_arg]['macros']['_CRITICALITY_LEVEL'] =
                $this->hosts[$host_id_arg]['severity']['level'];
            $this->hosts[$host_id_arg]['macros']['_CRITICALITY_ID'] = $this->hosts[$host_id_arg]['severity']['hc_id'];
        }

        $hosts_tpl = &HostTemplate::getInstance($this->dependencyInjector)->hosts;
        $stack = $this->hosts[$host_id_arg]['htpl'];
        while ((is_null($severity_id) && (!is_null($stack) && ($host_id = array_shift($stack))))) {
            if (isset($loop[$host_id])) {
                continue;
            }
            $loop[$host_id] = 1;
            if (isset($hosts_tpl[$host_id]['severity_id']) && !is_null($hosts_tpl[$host_id]['severity_id'])) {
                $severity_id = $hosts_tpl[$host_id]['severity_id'];
                break;
            }
            if (isset($hosts_tpl[$host_id]['severity_id_from_below'])) {
                $severity_id = $hosts_tpl[$host_id]['severity_id_from_below'];
                break;
            }

            $stack2 = $hosts_tpl[$host_id]['htpl'];
            while ((is_null($severity_id) && (!is_null($stack2) && ($host_id2 = array_shift($stack2))))) {
                if (isset($loop[$host_id2])) {
                    continue;
                }
                $loop[$host_id2] = 1;
                if (isset($hosts_tpl[$host_id2]['severity_id']) && !is_null($hosts_tpl[$host_id2]['severity_id'])) {
                    $severity_id = $hosts_tpl[$host_id2]['severity_id'];
                }
                if (isset($hosts_tpl[$host_id2]['severity_id'])) {
                    $severity_id = $hosts_tpl[$host_id2]['severity_id'];
                    break;
                }
                $stack2 = array_merge($hosts_tpl[$host_id2]['htpl'], $stack2);
            }

            if ($severity_id) {
                $hosts_tpl[$host_id]['severity_id_from_below'] = $severity_id;
            }
        }

        # For applied on services without severity
        $this->hosts[$host_id_arg]['severity_id_for_services'] = $severity_instance->getHostSeverityById($severity_id);
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
        $host['macros']['_HOST_ID'] = $host['host_id'];

        $this->getHostTimezone($host);
        $this->getHostTemplates($host);
        $this->getHostCommands($host);
        $this->getHostPeriods($host);
        $this->getContactGroups($host);
        $this->getContacts($host);
        $this->getHostGroups($host);
        $this->getParents($host);
        $this->getSeverity($host['host_id']);
        
        $this->manageNotificationInheritance($host);
        
        $this->getServices($host);
        $this->getServicesByHg($host);

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
            MetaService::getInstance($this->dependencyInjector)->generateObjects();
        }

        Hostgroup::getInstance($this->dependencyInjector)->generateObjects();
        Servicegroup::getInstance($this->dependencyInjector)->generateObjects();
        Escalation::getInstance($this->dependencyInjector)->generateObjects();
        Dependency::getInstance($this->dependencyInjector)->generateObjects();
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

    /**
     * @param int $hostId
     * @return array
     */
    public function getCgAndContacts(int $hostId) : array
    {
        // we pass null because it can be a meta_host with host_register = '2'
        $host = $this->getHostById($hostId, null);
    
        $this->getContacts($host);
        $this->getContactGroups($host);
        $this->getHostTemplates($host, false);

        $hostTplInstance = &HostTemplate::getInstance($this->dependencyInjector);

        $stack = $host['htpl'];
        $loop = array();
        while (($hostTplId = array_shift($stack))) {
            if (isset($loop[$hostTplId])) {
                continue;
            }
            $loop[$hostTplId] = 1;

            $hostTplInstance->addCacheHostTpl($hostTplId);
            if (!is_null($hostTplInstance->hosts[$hostTplId])) {
                $hostTplInstance->getHostTemplates($hostTplInstance->hosts[$hostTplId], false);
                $hostTplInstance->getContactGroups($hostTplInstance->hosts[$hostTplId]);
                $hostTplInstance->getContacts($hostTplInstance->hosts[$hostTplId]);
                $stack = array_merge($hostTplInstance->hosts[$hostTplId]['htpl'], $stack);
            }
        }
        return $this->manageNotificationInheritance($host, false);
    }

    public function reset()
    {
        $this->hosts_by_name = array();
        $this->hosts = null;
        $this->generated_parentship = array();
        $this->generatedHosts = array();
        parent::reset();
    }
}
