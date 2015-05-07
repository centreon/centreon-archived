<?php
/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonConfiguration\Repository;

use Centreon\Internal\Di;
use CentreonConfiguration\Models\Hosttemplate;
use CentreonConfiguration\Repository\Repository;
use Centreon\Internal\Utils\YesNoDefault;
use CentreonConfiguration\Repository\HostRepository;
use CentreonConfiguration\Models\Command;
use CentreonConfiguration\Models\Timeperiod;
use Centreon\Internal\Utils\YesNoDefault;
/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class HostTemplateRepository extends Repository
{
    public static $objectClass = '\CentreonConfiguration\Models\Hosttemplate';
    
    /**
     * List of column for inheritance
     * @var array
     */
    protected static $inheritanceColumns = array(
        'command_command_id',
        'command_command_id_arg1',
        'timeperiod_tp_id',
        'command_command_id2',
        'command_command_id_arg2',
        'host_max_check_attempts',
        'host_check_interval',
        'host_retry_check_interval',
        'host_active_checks_enabled',
        'host_passive_checks_enabled',
        'host_checks_enabled',
        'initial_state',
        'host_obsess_over_host',
        'host_check_freshness',
        'host_event_handler_enabled',
        'host_low_flap_threshold',
        'host_high_flap_threshold',
        'host_flap_detection_enabled',
        'flap_detection_options',
        'host_snmp_community',
        'host_snmp_version'
    );
    
    /**
     *
     * @var type 
     */
    protected static $unicityFields = array(
        'fields' => array(
            'hosttemplate' => 'cfg_hosts,host_id,host_name'
        ),
    );

    /**
     *
     * @var string
     */
    public static $tableName = 'cfg_hosts';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Hosttemplate';

    /**
     * Get list of host templates
     *
     * @param string $searchStr
     * @param int $objectId The self id to skip
     * @return array
     */
    public static function getFormList($searchStr = "", $objectId = null)
    {
        $listHostTemplate = parent::getFormList($searchStr, $objectId);

        foreach ($listHostTemplate as $key => $hostTemplate) {
            if ($hostTemplate['id'] == $objectId) {
                unset($listHostTemplate[$key]);
            }
        }
        $listHostTemplate = array_values($listHostTemplate);

        return $listHostTemplate;
    }

    /**
     * 
     * @param int $host_id
     * @return string
     */
    public static function getTemplates($host_id)
    {
        $di = Di::getDefault();
        
        /* Get Database Connexion */
        $dbconn = $di->get('db_centreon');
        
        /* Init Array to return */
        $hostTemplates = "";
        
        /* Get information into the database. */
        $query = "SELECT host_tpl_id, host_name, `order` "
            . "FROM cfg_hosts h, cfg_hosts_templates_relations hr "
            . "WHERE h.host_id = hr.host_tpl_id "
            . "AND hr.host_host_id = '$host_id' "
            . "AND host_activate = '1' "
            . "AND host_register = '0' "
            . "ORDER BY `order` ASC";
        $stmt = $dbconn->prepare($query);
        $stmt->execute();
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if ($hostTemplates != "") {
                $hostTemplates .= ',';
            }
            $hostTemplates .= $row["host_name"];
        }
        return $hostTemplates;
    }

    /**
     * Get the value from template
     *
     * @param int $hostId The host template Id
     * @param bool $isBase If the host template id is the base for get values
     * @return array
     */
    public static function getInheritanceValues($hostId, $isBase=false)
    {
        $values = array();
        $templates = HostRepository::getTemplateChain($hostId, array(), -1);
        if ($isBase) {
            array_unshift($templates, array('id' => $hostId));
        }
        foreach ($templates as $template) {
            $inheritanceValues = static::getInheritanceValues($template['id']);
            $tmplValues = Hosttemplate::getParameters($template['id'], self::$inheritanceColumns);
            $tmplValues = array_filter($tmplValues, function($value) {
                return !is_null($value);
            });
            $tmplValues = array_merge($inheritanceValues, $tmplValues);
            $values = array_merge($tmplValues, $values);
        }
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
                $command = Command::get($value);
                return $command['command_name'];
            case 'timeperiod_tp_id':
                $timeperiod = Timeperiod::get($value);
                return $timeperiod['tp_name'];
            case 'host_active_checks_enabled':
            case 'host_passive_checks_enabled':
            case 'host_obsess_over_host':
            case 'host_check_freshness':
            case 'flap_detection_options':
            case 'host_event_handler_enabled':
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
    
    /**
     * Get configuration data of a host
     * 
     * @param int $hostId
     * @return array
     */
    public static function getConfigurationData($hostId)
    {
        return HostTemplate::getParameters($hostId, "*");
    }
    
    /**
     * Format data so that it can be displayed in tooltip
     *
     * @param array $templates
     * @return array $checkdataTemplate
     */
    public static function formatDataForTooltip($templates)
    {
        
        $checkdataTemplate = array();
        
        
        foreach($templates as $template){
           $oneTemplateDataArray = array();
           $oneTemplateDataArray[] = array(
            'label' => _('Command'),
            'value' => static::getObjectName('\CentreonConfiguration\Models\Command', $template['command_command_id'])
            );
            $oneTemplateDataArray[] = array(
                'label' => _('Time period'),
                'value' => static::getObjectName('\CentreonConfiguration\Models\Timeperiod', $template['timeperiod_tp_id'])
            );
            $oneTemplateDataArray[] = array(
                'label' => _('Max check attempts'),
                'value' => $template['host_max_check_attempts']
            );
            $oneTemplateDataArray[] = array(
                'label' => _('Check interval'),
                'value' => $template['host_check_interval']
            );
            $oneTemplateDataArray[] = array(
                'label' => _('Retry check interval'),
                'value' => $template['host_retry_check_interval']
            );
            $oneTemplateDataArray[] = array(
                'label' => _('Active checks enabled'),
                'value' => YesNoDefault::toString($template['host_active_checks_enabled'])
            );
            $oneTemplateDataArray[] = array(
                'label' => _('Passive checks enabled'),
                'value' => $template['host_passive_checks_enabled']
            );

           $checkdataTemplate[] = $oneTemplateDataArray;
        }

        return $checkdataTemplate;
        
    }
}
