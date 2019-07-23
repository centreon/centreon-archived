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

namespace ConfigGenerateRemote;

use \PDO;

require_once dirname(__FILE__) . '/object.class.php';

abstract class AbstractHost extends AbstractObject
{
    protected $attributes_select = '
        host_id,
        command_command_id,
        command_command_id_arg1,
        timeperiod_tp_id,
        timeperiod_tp_id2,
        command_command_id2,
        command_command_id_arg2,
        host_name,
        host_alias,
        host_address,
        display_name,
        host_max_check_attempts,
        host_check_interval,
        host_retry_check_interval,
        host_active_checks_enabled,
        host_passive_checks_enabled,
        host_event_handler_enabled,
        host_notification_interval,
        host_notification_options,
        host_notifications_enabled,
        host_snmp_community,
        host_snmp_version,
        host_register,
        ehi_notes,
        ehi_notes_url,
        ehi_action_url,
        ehi_icon_image,
        ehi_icon_image_alt,
        ehi_statusmap_image,
        ehi_2d_coords,
        ehi_3d_coords,
        host_location,
        host_acknowledgement_timeout
    ';
    protected $attributes_write = array(
        'host_id',
        'command_command_id',
        'command_command_id_arg1',
        'timeperiod_tp_id',
        'timeperiod_tp_id2',
        'command_command_id2',
        'command_command_id_arg2',
        'host_name',
        'host_alias',
        'host_address',
        'display_name',
        'host_max_check_attempts',
        'host_check_interval',
        'host_retry_check_interval',
        'host_active_checks_enabled',
        'host_passive_checks_enabled',
        'host_event_handler_enabled',
        'host_notification_interval',
        'host_notification_options',
        'host_notifications_enabled',
        'host_snmp_community',
        'host_snmp_version',
        'host_register',
        'host_location',
        'host_acknowledgement_timeout'
    );
    
    protected $loop_htpl = array(); # To be reset
    protected $stmt_macro = null;
    protected $stmt_htpl = null;
    protected $stmt_contact = null;
    protected $stmt_cg = null;

    protected function getExtendedInformation(&$host)
    {
        $extended_information = array(
            'host_host_id' => $host['host_id'],
            'ehi_notes' => $host['ehi_notes'],
            'ehi_notes_url' => $host['ehi_notes_url'],
            'ehi_action_url' => $host['ehi_action_url'],
            'ehi_icon_image' => $host['ehi_icon_image'],
            'ehi_icon_image_alt' => $host['ehi_icon_image_alt'],
            'ehi_2d_coords' => $host['ehi_2d_coords'],
            'ehi_3d_coords' => $host['ehi_3d_coords'],
        );
        unset($host['ehi_notes']);
        unset($host['ehi_notes_url']);
        unset($host['ehi_action_url']);
        unset($host['ehi_icon_image']);
        unset($host['ehi_icon_image_alt']);
        unset($host['ehi_2d_coords']);
        unset($host['ehi_3d_coords']);
        return $extended_information;
    }

    protected function getImages(&$host)
    {
        $media = Media::getInstance($this->dependencyInjector);
        $media->getMediaPathFromId($host['ehi_icon_image']);
        $media->getMediaPathFromId($host['ehi_statusmap_image']);
    }

    protected function getMacros(&$host)
    {
        if (isset($host['macros'])) {
            return 1;
        }

        if (is_null($this->stmt_macro)) {
            $this->stmt_macro = $this->backend_instance->db->prepare("SELECT 
              host_macro_id, host_macro_name, host_macro_value, is_password, description, host_host_id
            FROM on_demand_macro_host
            WHERE host_host_id = :host_id
            ");
        }
        $this->stmt_macro->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
        $this->stmt_macro->execute();
        $macros = $this->stmt_macro->fetchAll(PDO::FETCH_ASSOC);

        $host['macros'] = array();
        foreach ($macros as $macro) {
            $host['macros'][$macro['host_macro_name']] = $macro['host_macro_value'];
            macroHost::getInstance($this->dependencyInjector)->add($macro, $host['host_id']);
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
        $order = 1;
        foreach ($host['htpl'] as $template_id) {
            $host_template->generateFromHostId($template_id);
            hostTemplateRelation::getInstance($this->dependencyInjector)->addRelation($host['host_id'], $template_id, $order);
            $order++;
        }
    }

    protected function getContacts(&$host)
    {
        if (!isset($host['contacts_cache'])) {
            if (is_null($this->stmt_contact)) {
                $this->stmt_contact = $this->backend_instance->db->prepare("SELECT 
                    contact_id
                FROM contact_host_relation
                WHERE host_host_id = :host_id
                ");
            }
            $this->stmt_contact->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
            $this->stmt_contact->execute();
            $host['contacts_cache'] = $this->stmt_contact->fetchAll(PDO::FETCH_COLUMN);
        }

        $contact = Contact::getInstance($this->dependencyInjector);
        foreach ($host['contacts_cache'] as $contact_id) {
            $contact->generateFromContactId($contact_id);
            contactHostRelation::getInstance($this->dependencyInjector)->addRelation($host['host_id'], $contact_id);
        }
    }

    protected function getContactGroups(&$host)
    {
        if (!isset($host['contact_groups_cache'])) {
            if (is_null($this->stmt_cg)) {
                $this->stmt_cg = $this->backend_instance->db->prepare("SELECT 
                    contactgroup_cg_id
                FROM contactgroup_host_relation
                WHERE host_host_id = :host_id
                ");
            }
            $this->stmt_cg->bindParam(':host_id', $host['host_id'], PDO::PARAM_INT);
            $this->stmt_cg->execute();
            $host['contact_groups_cache'] = $this->stmt_cg->fetchAll(PDO::FETCH_COLUMN);
        }

        $cg = Contactgroup::getInstance($this->dependencyInjector);
        foreach ($host['contact_groups_cache'] as $cg_id) {
            $cg->generateFromCgId($cg_id);
            contactgroupHostRelation::getInstance($this->dependencyInjector)->addRelation($host['host_id'], $cg_id);
        }
    }

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

    protected function getHostTimezone(&$host)
    {
        # not needed
    }

    protected function getHostCommand(&$host, $command_id_label)
    {
        Command::getInstance($this->dependencyInjector)
            ->generateFromCommandId($host[$command_id_label]);
        return 0;
    }

    protected function getHostCommands(&$host)
    {
        $this->getHostCommand($host, 'command_command_id');
        $this->getHostCommand($host, 'command_command_id2');
    }

    protected function getHostPeriods(&$host)
    {
        $period = Timeperiod::getInstance($this->dependencyInjector);
        $period->generateFromTimeperiodId($host['timeperiod_tp_id']);
        $period->generateFromTimeperiodId($host['timeperiod_tp_id2']);
    }

    public function getString($host_id, $attr)
    {
        if (isset($this->hosts[$host_id][$attr])) {
            return $this->hosts[$host_id][$attr];
        }
        return null;
    }
}
