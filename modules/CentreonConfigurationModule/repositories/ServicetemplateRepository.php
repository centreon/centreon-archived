<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonConfiguration\Repository;

use CentreonConfiguration\Models\Service;
use CentreonConfiguration\Models\Servicetemplate;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class ServicetemplateRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'cfg_services';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Servicetemplate';

    /**
     * List of information for inheritance
     * @var array
     */
    protected static $inheritanceColumns = array(
        'command_command_id',
        'timeperiod_tp_id',
        'timeperiod_tp_id2',
        'command_command_id2',
        'service_is_volatile',
        'service_max_check_attempts',
        'service_normal_check_interval',
        'service_retry_check_interval',
        'service_active_checks_enabled',
        'service_passive_checks_enabled',
        'initial_state',
        'service_parallelize_check',
        'service_obsess_over_service',
        'service_check_freshness',
        'service_freshness_threshold',
        'service_event_handler_enabled',
        'service_low_flap_threshold',
        'service_high_flap_threshold',
        'service_flap_detection_enabled',
        'service_process_perf_data',
        'service_retain_status_information',
        'service_retain_nonstatus_information',
        'service_notification_interval',
        'service_notification_options',
        'service_notifications_enabled',
        'contact_additive_inheritance',
        'cg_additive_inheritance',
        'service_inherit_contacts_from_host',
        'service_first_notification_delay',
        'service_stalking_options',
        'command_command_id_arg',
        'command_command_id_arg2'
    );
    
    /**
     * 
     * @param int $template_id
     * @return string
     */
    public static function getTemplateName($template_id)
    {
        $di = \Centreon\Internal\Di::getDefault();
        
        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');

        /* Check if the template_id is well formated */
        if (!isset($template_id) || $template_id == "") {
            return -1;
        }

        /* Get information into the database. */
        $query = "SELECT service_description FROM cfg_services WHERE service_id = '$template_id' AND service_register = '0'";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row["service_description"];
        }
        return -1;
    }
    
    /**
     * 
     * @param int $service_id
     * @return array
     */
    public static function getContacts($service_id)
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        $contactList = "";

        $query = "SELECT contact_alias "
            . "FROM cfg_contacts c, cfg_contacts_services_relations cs "
            . "WHERE service_service_id = '$service_id' "
            . "AND c.contact_id = ccontact_id "
            . "ORDER BY contact_alias";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($contactList != "") {
                $contactList .= ",";
            }
            $contactList .= $row["contact_alias"];
        }
        return $contactList;
    }

    /**
     * 
     * @param int $service_id
     * @return array
     */
    public static function getContactGroups($service_id)
    {
        $di = \Centreon\Internal\Di::getDefault();

        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        $contactgroupList = "";

        $query = "SELECT cg_name "
            . "FROM cfg_contactgroups cg, cfg_contactgroups_services_relations cgs "
            . "WHERE service_service_id = '$service_id' "
            . "AND cg.cg_id = cgs.contactgroup_cg_id "
            . "ORDER BY cg_name";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($contactgroupList != "") {
                $contactgroupList .= ",";
            }
            $contactgroupList .= $row["cg_name"];
        }
        return $contactgroupList;
    }

    /**
     * 
     * @param int $service_template_id
     * @return array
     */
    public static function getMyServiceTemplateModels($service_template_id)
    {
        // Initializing connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $stmt = $dbconn->query(
            "SELECT service_description FROM cfg_services WHERE service_id = '".$service_template_id."' LIMIT 1"
        );
        
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (isset($row["service_description"])) {
            $tplArr = array(
                'id' => $service_template_id,
                'description' => \html_entity_decode($row["service_description"], ENT_QUOTES, "UTF-8")
            );
            return $tplArr;
        }
        return array('id' => $service_template_id);
    }

    /**
     * Get values for templates
     *
     * @param integer $svcId The service id
     * @param bool $isBase If the service template id is the base for get values
     * @return array
     */
    public static function getInheritanceValues($svcId, $isBase=false)
    {
        $values = array();
        $tmpl = Service::getParameters($svcId, array('service_template_model_stm_id'));
        $tmpl = $tmpl['service_template_model_stm_id'];
        if ($isBase) {
            $tmpl = $svcId;
        }

        if (is_null($tmpl)) {
            return $values;
        }
        /* Get template values */
        $values = Servicetemplate::getParameters($tmpl, self::$inheritanceColumns);
        $values = array_filter($values, function($value) {
            return !is_null($value);
        });
        $tmplNext = Servicetemplate::getParameters($tmpl, array('service_template_model_stm_id'));
        if (is_null($tmplNext['service_template_model_stm_id'])) {
            return $values;
        }
        $values = array_merge(static::getInheritanceValues($tmplNext['service_template_model_stm_id'], true), $values);

        return $values;
    }

    /**
     * Get the full text of a numeric value
     *
     * @param string $name The key name
     * @param int $value The numeric value
     * @return string
     */
    public static function getTextValue($name, $value)
    {
        switch ($name) {
            case 'command_command_id':
            case 'command_command_id2':
                $command = \CentreonConfiguration\Models\Command::get($value);
                return $command['command_name'];
            case 'timeperiod_tp_id':
            case 'timeperiod_tp_id2':
                $timeperiod = \CentreonConfiguration\Models\Timeperiod::get($value);
                return $timeperiod['tp_name'];
            case 'service_is_volatile':
            case 'service_active_checks_enabled':
            case 'service_passive_checks_enabled':
            case 'service_parallelize_check':
            case 'service_obsess_over_service':
            case 'service_check_freshness':
            case 'service_event_handler_enabled':
            case 'service_flap_detection_enabled':
            case 'service_process_perf_data':
            case 'service_retain_status_information':
            case 'service_retain_nonstatus_information':
            case 'service_notifications_enabled':
                if ($value == 0) {
                    return _('No');
                } else if ($value == 1) {
                    return _('Yes');
                } else {
                    return _('Default');
                }
            default:
                return $value;
        }
    }
}
