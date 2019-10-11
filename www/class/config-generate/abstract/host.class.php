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

require_once dirname(__FILE__) . '/object.class.php';

abstract class AbstractHost extends AbstractObject
{

    const VERTICAL_NOTIFICATION = 1;
    // CLOSE_NOTIFICATION = 2
    const CUMULATIVE_NOTIFICATION = 3;

    protected $attributes_select = '
        host_id,
        command_command_id as check_command_id,
        command_command_id_arg1 as check_command_arg,
        timeperiod_tp_id as check_period_id,
        timeperiod_tp_id2 as notification_period_id,
        command_command_id2 as event_handler_id,
        command_command_id_arg2 as event_handler_arg,
        host_name,
        host_alias as alias,
        host_address as address,
        display_name,
        host_max_check_attempts as max_check_attempts,
        host_check_interval as check_interval,
        host_retry_check_interval as retry_interval,
        host_active_checks_enabled as active_checks_enabled,
        host_passive_checks_enabled as passive_checks_enabled,
        initial_state,
        host_obsess_over_host as obsess_over_host,
        host_check_freshness as check_freshness,
        host_freshness_threshold as freshness_threshold,
        host_event_handler_enabled as event_handler_enabled,
        host_low_flap_threshold as low_flap_threshold,
        host_high_flap_threshold as high_flap_threshold,
        host_flap_detection_enabled as flap_detection_enabled,
        flap_detection_options,
        host_process_perf_data as process_perf_data,
        host_retain_status_information as retain_status_information,
        host_retain_nonstatus_information as retain_nonstatus_information,
        host_notification_interval as notification_interval,
        host_notification_options as notification_options,
        host_notifications_enabled as notifications_enabled,
        contact_additive_inheritance,
        cg_additive_inheritance,
        host_first_notification_delay as first_notification_delay,
        host_recovery_notification_delay as recovery_notification_delay,
        host_stalking_options as stalking_options,
        host_snmp_community,
        host_snmp_version,
        host_register as register,
        ehi_notes as notes,
        ehi_notes_url as notes_url,
        ehi_action_url as action_url,
        ehi_icon_image as icon_image_id,
        ehi_icon_image_alt as icon_image_alt,
        ehi_statusmap_image as statusmap_image_id,
        host_location,
        host_acknowledgement_timeout as acknowledgement_timeout
    ';
    protected $attributes_write = array(
        'host_name',
        'alias',
        'address',
        'display_name',
        'contacts',
        'contact_groups',
        'check_command',
        'check_period',
        'notification_period',
        'event_handler',
        'max_check_attempts',
        'check_interval',
        'retry_interval',
        'initial_state',
        'freshness_threshold',
        'low_flap_threshold',
        'high_flap_threshold',
        'flap_detection_options',
        'notification_interval',
        'notification_options',
        'first_notification_delay',
        'recovery_notification_delay',
        'stalking_options',
        'register',
        'notes',
        'notes_url',
        'action_url',
        'icon_image',
        'icon_image_alt',
        'statusmap_image',
        'timezone',
        'acknowledgement_timeout'
    );
    protected $attributes_default = array(
        'active_checks_enabled',
        'passive_checks_enabled',
        'event_handler_enabled',
        'flap_detection_enabled',
        'notifications_enabled',
        'obsess_over_host',
        'check_freshness',
        'process_perf_data',
        'retain_status_information',
        'retain_nonstatus_information',
    );
    protected $attributes_array = array(
        'use',
        'parents',
    );
    protected $attributes_hash = array(
        'macros'
    );
    protected $loop_htpl = array(); # To be reset
    protected $stmt_macro = null;
    protected $stmt_htpl = null;
    protected $stmt_contact = null;
    protected $notificationOption = null;
    protected $stmt_cg = null;

    protected function getImages(&$host)
    {
        $media = Media::getInstance($this->dependencyInjector);
        if (!isset($host['icon_image'])) {
            $host['icon_image'] = $media->getMediaPathFromId($host['icon_image_id']);
        }
        if (!isset($host['statusmap_image'])) {
            $host['statusmap_image'] = $media->getMediaPathFromId($host['statusmap_image_id']);
        }
    }

    protected function getMacros(&$host)
    {
        if (isset($host['macros'])) {
            return 1;
        }

        if (is_null($this->stmt_macro)) {
            $this->stmt_macro = $this->backend_instance->db->prepare("SELECT 
              host_macro_name, host_macro_value
            FROM on_demand_macro_host
            WHERE host_host_id = :host_id
            ");
        }
        $this->stmt_macro->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
        $this->stmt_macro->execute();
        $macros = $this->stmt_macro->fetchAll(PDO::FETCH_ASSOC);

        $host['macros'] = array();
        foreach ($macros as $macro) {
            $host['macros'][preg_replace(
                '/\$_HOST(.*)\$/',
                '_$1',
                $macro['host_macro_name']
            )] = $macro['host_macro_value'];
        }
        if (!is_null($host['host_snmp_community']) && $host['host_snmp_community'] != '') {
            $host['macros']['_SNMPCOMMUNITY'] = $host['host_snmp_community'];
        }
        if (!is_null($host['host_snmp_version']) && $host['host_snmp_version'] != 0) {
            $host['macros']['_SNMPVERSION'] = $host['host_snmp_version'];
        }

        return 0;
    }

    protected function getHostTemplates(&$host)
    {
        if (!isset($host['htpl'])) {
            if (is_null($this->stmt_htpl)) {
                $this->stmt_htpl = $this->backend_instance->db->prepare("SELECT 
                    host_tpl_id
                FROM host_template_relation
                WHERE host_host_id = :host_id
                ORDER BY `order` ASC
                ");
            }
            $this->stmt_htpl->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
            $this->stmt_htpl->execute();
            $host['htpl'] = $this->stmt_htpl->fetchAll(PDO::FETCH_COLUMN);
        }

        $host_template = HostTemplate::getInstance($this->dependencyInjector);
        $host['use'] = array();
        foreach ($host['htpl'] as $template_id) {
            $host['use'][] = $host_template->generateFromHostId($template_id);
        }
    }

    /**
     * Get contacts list for the configuration file by host
     *
     * @param array $host
     */
    protected function getContacts(array &$host): void
    {
        $contactResult = '';
        $hostListing = $this->listHostsWithContacts($host);
        //check if we have Host link to a contact.
        if (!empty($hostListing)) {
            $contactResult = implode(',', $this->getInheritanceContact(array_unique($hostListing)));
        }
        $host['contacts'] = $contactResult;
    }

    /**
     * Get the tree of hosts with contact according to the inheritance notification option
     *
     * @param array $host
     * @return array
     */
    public function listHostsWithContacts(array $host): array
    {
        //check notification mode
        if (is_null($this->notificationOption)) {
            $this->notificationOption = (int)$this->getInheritanceMode();
        }
        $hostListing = array();
        //check cumulative option
        if (self::CUMULATIVE_NOTIFICATION === $this->notificationOption) {
            // get all host / template inheritance
            $this->getCumulativeInheritance($host['host_id'], $hostListing);
        } else {
            // get the first host (template) link to a contact group
            // use for close and vertical inheritance mode
            $this->getContactCloseInheritance($host['host_id'], $hostListing);
            //check vertical inheritance
            if (!empty($hostListing)
                && (self::VERTICAL_NOTIFICATION === $this->notificationOption)
            ) {
                //use the first template found to start
                $startHost = $hostListing[0];
                $hostListing = array();
                $this->getContactVerticalInheritance($startHost, $hostListing);
            }
        }
        return $hostListing;
    }

    /**
     * Get the tree of host for vertical notification option on contact
     *
     * @param int $hostId
     * @param array $hostList
     */
    protected function getContactVerticalInheritance(int $hostId, &$hostList = array()): void
    {
        $stmt = $this->backend_instance->db->query(
            'SELECT host_notifications_enabled, contact_additive_inheritance, host_tpl_id 
            FROM host, host_template_relation
            WHERE `host_id` = `host_host_id`
            AND `order` = 1
            AND `host_activate` != "0"
            AND `host_id` = ' . $hostId
        );
        $hostAdd = $stmt->fetch();
        if ($hostAdd && $hostAdd['host_notifications_enabled'] !== '0') {
            $hostList[] = $hostId;
        }
        if (isset($hostAdd['host_tpl_id']) && (int)$hostAdd['contact_additive_inheritance'] === 1) {
            $this->getContactVerticalInheritance((int)$hostAdd['host_tpl_id'], $hostList);
        }
    }

    /**
     * Get the tree of host for cumulative notification option
     *
     * @param int $hostId
     * @param array $hostList
     */
    protected function getCumulativeInheritance(int $hostId, &$hostList = array()): void
    {

        $stmt = $this->backend_instance->db->query(
            'SELECT host.host_notifications_enabled, host_template_relation.host_tpl_id
            FROM host
            LEFT JOIN host_template_relation ON host_template_relation.`host_host_id` = host.`host_id` 
            WHERE host.`host_id` = ' . $hostId . ' AND host.`host_activate` = "1"'
        );
        while (($row = $stmt->fetch())) {
            if($row['host_notifications_enabled'] != '0'){
                $hostList[] = $hostId;
            }
            if($row['host_tpl_id']){
                $this->getCumulativeInheritance((int)$row['host_tpl_id'], $hostList);
            }
        }
    }

    /**
     * Get the first host who have a valid notifiable contact
     *
     * @param int $hostId
     * @param array $hostList
     */
    protected function getContactCloseInheritance(int $hostId, &$hostList = array()): void
    {
        $stmt = $this->backend_instance->db->query(
            'SELECT GROUP_CONCAT(contact.contact_id) AS contact_id, 
                (SELECT GROUP_CONCAT(host_template_relation.host_tpl_id)
	            FROM host_template_relation , host
	            WHERE host_template_relation.host_host_id = ' . $hostId . '
                AND host.host_id = host_template_relation.host_host_id
	            AND host.host_activate = "1") AS host_tpl_id	
            FROM contact, contact_host_relation, host
            WHERE contact.`contact_id` = contact_host_relation.`contact_id`
            AND contact_host_relation.host_host_id = ' . $hostId . '
            AND contact.contact_enable_notifications != "0"
            AND contact.contact_activate = "1"
            AND host.host_id = contact_host_relation.host_host_id
            AND host.host_activate = "1"
            AND host.host_notifications_enabled != "0"'
        );

        if (($row = $stmt->fetch()) && empty($hostList)) {
            if ($row['contact_id']) {
                $hostList[] = (int)$hostId;
            } elseif ($row['host_tpl_id']) {
                foreach (explode(',', $row['host_tpl_id']) as $hostTplId) {
                    $this->getContactCloseInheritance((int)$hostTplId, $hostList);
                }
            }
        }
    }

    /**
     * Get enable and notifiable contact id/name of a host list
     *
     * @param array $hostIds list of host id
     * @return array
     */
    protected function getInheritanceContact(array $hostIds): array
    {
        $contact = Contact::getInstance($this->dependencyInjector);
        $contacts = array();
        $stmt = $this->backend_instance->db->query(
            'SELECT contact.contact_id , contact.contact_name 
            FROM contact, contact_host_relation 
            WHERE contact_host_relation.host_host_id IN (' . implode(',', $hostIds) . ') 
            AND contact_host_relation.contact_id = contact.contact_id 
            AND contact.contact_activate = "1" 
            AND contact.contact_enable_notifications != "0"'
        );

        while ($row = $stmt->fetch()) {
            $contacts[$row['contact_id']] = $contact->generateFromContactId($row['contact_id']);
        }
        return $contacts;
    }

    /**
     * Get contact groups list for the configuration file by host
     *
     * @param array $host
     */
    protected function getContactGroups(array &$host): void
    {
        $cgResult = '';
        $hostListing = $this->listHostsWithContactGroups($host);
        //check if we have Host link to a contactGroup.
        if (!empty($hostListing)) {
            $cgResult = implode(',', $this->getInheritanceContactGroups(array_unique($hostListing)));
        }
        $host['contact_groups'] = $cgResult;
    }

    /**
     * Get the tree of hosts with contact group according to the inheritance notification option
     *
     * @param array $host
     * @return array
     */
    public function listHostsWithContactGroups(array $host): array
    {
        //check notification mode
        if (is_null($this->notificationOption)) {
            $this->notificationOption = (int)$this->getInheritanceMode();
        }
        $hostListing = array();
        //check cumulative option
        if (self::CUMULATIVE_NOTIFICATION === $this->notificationOption) {
            // get all host / template inheritance
            $this->getCumulativeInheritance((int)$host['host_id'], $hostListing);
        } else {
            // get the first host (template) link to a contact group
            // use for close inheritance mode too
            $this->getContactGroupsCloseInheritance((int)$host['host_id'], $hostListing);
            //check vertical inheritance
            if (!empty($hostListing) && (self::VERTICAL_NOTIFICATION === $this->notificationOption)) {
                //use the first template found to start
                $startHost = (int)$hostListing[0];
                $hostListing = array();
                $this->getContactGroupsVerticalInheritance($startHost, $hostListing);
            }
        }
        return $hostListing;
    }

    /**
     * Get the tree of host for vertical notification option on contact group
     *
     * @param int $hostId
     * @param array $hostList
     */
    protected function getContactGroupsVerticalInheritance(int $hostId, &$hostList = array()): void
    {
        $stmt = $this->backend_instance->db->query(
            'SELECT cg_additive_inheritance, host_tpl_id, host_notifications_enabled
            FROM host, host_template_relation
            WHERE `host_id` = `host_host_id`
            AND `order` = 1
            AND `host_id` = ' . $hostId
        );
        $hostAdd = $stmt->fetch();
        if ($hostAdd && $hostAdd['host_notifications_enabled'] !== '0') {
            $hostList[] = $hostId;
        }
        if (isset($hostAdd['host_tpl_id']) && (int)$hostAdd['cg_additive_inheritance'] === 1) {
            $this->getContactGroupsVerticalInheritance((int)$hostAdd['host_tpl_id'], $hostList);
        }
    }

    /**
     * Get the first host who have a valid notifiable contact group
     *
     * @param int $hostId
     * @param array $hostList
     */
    protected function getContactGroupsCloseInheritance(int $hostId, &$hostList = array()): void
    {
        $stmt = $this->backend_instance->db->query(
            'SELECT GROUP_CONCAT(contactgroup.cg_id) AS cg_id, 
                (SELECT GROUP_CONCAT(host_template_relation.host_tpl_id)
	            FROM host_template_relation , host
	            WHERE host_template_relation.host_host_id = ' . $hostId . '
                AND host.host_id = host_template_relation.host_host_id
	            AND host.host_activate = "1") as host_tpl_id	
            FROM contactgroup, contactgroup_host_relation, host
            WHERE contactgroup.`cg_id` = contactgroup_host_relation.`contactgroup_cg_id`
            AND contactgroup_host_relation.host_host_id = ' . $hostId . '
            AND contactgroup.cg_activate = "1"
            AND host.host_id = contactgroup_host_relation.host_host_id
            AND host.host_activate = "1"
            AND host.host_notifications_enabled != "0"'
        );
        if (($row = $stmt->fetch()) && empty($hostList)) {
            if ($row['cg_id']) {
                $hostList[] = (int)$hostId;
            } elseif ($row['host_tpl_id']) {
                foreach (explode(',', $row['host_tpl_id']) as $hostTplId) {
                    $this->getContactGroupsCloseInheritance((int)$hostTplId, $hostList);
                }
            }
        }
    }

    /**
     * Get enable contact group id/name of a host list
     *
     * @param array $hostIds List of host id
     * @return array
     */
    protected function getInheritanceContactGroups(array $hostIds): array
    {
        $cg = Contactgroup::getInstance($this->dependencyInjector);
        $contactGroups = array();
        $stmt = $this->backend_instance->db->query(
            'SELECT c.cg_id , cg_name FROM contactgroup c, contactgroup_host_relation ch
            WHERE ch.host_host_id IN (' . implode(',', $hostIds) . ') AND ch.contactgroup_cg_id = c.cg_id 
            AND cg_activate = "1"'
        );
        while (($row = $stmt->fetch())) {
            $contactGroups[$row['cg_id']] = $cg->generateFromCgId($row['cg_id']);
        }
        return $contactGroups;
    }

    /**
     * @param $host_id
     * @param $host_tpl_id
     * @return int
     */
    public function isHostTemplate($host_id, $host_tpl_id)
    {
        $loop = array();
        $stack = array();

        $hosts_tpl = HostTemplate::getInstance($this->dependencyInjector)->hosts;
        $stack = $this->hosts[$host_id]['htpl'];
        while (($host_id = array_shift($stack))) {
            if (isset($loop[$host_id])) {
                continue;
            }
            $loop[$host_id] = 1;
            if ($host_id == $host_tpl_id) {
                return 1;
            }
            $stack = array_merge($hosts_tpl[$host_id]['htpl'], $stack);
        }

        return 0;
    }

    protected function findCommandName($host_id, $command_label)
    {
        $loop = array();
        $stack = array();

        $hosts_tpl = HostTemplate::getInstance($this->dependencyInjector)->hosts;
        $stack = $this->hosts[$host_id]['htpl'];
        while (($host_id = array_shift($stack))) {
            if (isset($loop[$host_id])) {
                continue;
            }
            $loop[$host_id] = 1;
            if (isset($hosts_tpl[$host_id][$command_label]) && !is_null($hosts_tpl[$host_id][$command_label])) {
                return $hosts_tpl[$host_id][$command_label];
            }
            $stack = array_merge($hosts_tpl[$host_id]['htpl'], $stack);
        }

        return null;
    }

    protected function getHostTimezone(&$host)
    {
        $oTimezone = Timezone::getInstance($this->dependencyInjector);
        $timezone = $oTimezone->getTimezoneFromId($host['host_location']);
        if (!is_null($timezone)) {
            $host['timezone'] = ':' . $timezone;
        }
    }

    protected function getHostCommand(&$host, $result_name, $command_id_label, $command_arg_label)
    {
        $command_name = Command::getInstance($this->dependencyInjector)
            ->generateFromCommandId($host[$command_id_label]);
        $command_arg = '';

        if (isset($host[$result_name])) {
            return 1;
        }
        $host[$result_name] = $command_name;
        if (isset($host[$command_arg_label])
            && !is_null($host[$command_arg_label]) && $host[$command_arg_label] != ''
        ) {
            $command_arg = $host[$command_arg_label];
            if (is_null($command_name)) {
                # Find Command Name in templates
                $command_name = $this->findCommandName($host['host_id'], $result_name);
                # Can have 'args after'. We replace
                if (!is_null($command_name)) {
                    $command_name = preg_replace('/!.*/', '', $command_name);
                    $host[$result_name] = $command_name . $command_arg;
                }
            } else {
                $host[$result_name] = $command_name . $command_arg;
            }
        }

        return 0;
    }

    protected function getHostCommands(&$host)
    {
        $this->getHostCommand($host, 'check_command', 'check_command_id', 'check_command_arg');
        $this->getHostCommand($host, 'event_handler', 'event_handler_id', 'event_handler_arg');
    }

    protected function getHostPeriods(&$host)
    {
        $period = Timeperiod::getInstance($this->dependencyInjector);
        $host['check_period'] = $period->generateFromTimeperiodId($host['check_period_id']);
        $host['notification_period'] = $period->generateFromTimeperiodId($host['notification_period_id']);
    }

    public function getString($host_id, $attr)
    {
        if (isset($this->hosts[$host_id][$attr])) {
            return $this->hosts[$host_id][$attr];
        }
        return null;
    }
}
