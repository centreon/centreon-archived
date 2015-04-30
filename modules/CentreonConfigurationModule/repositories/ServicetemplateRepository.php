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
use CentreonConfiguration\Models\Service;
use CentreonConfiguration\Models\Servicetemplate;
use CentreonConfiguration\Models\Command;
use CentreonConfiguration\Models\Timeperiod;
use CentreonConfiguration\Repository\Repository;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class ServicetemplateRepository extends Repository
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
    
    public static $objectClass = '\CentreonConfiguration\Models\Servicetemplate';

    /**
     * List of information for inheritance
     * @var array
     */
    protected static $inheritanceColumns = array(
        'command_command_id',
        'timeperiod_tp_id',
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
        'command_command_id_arg',
        'command_command_id_arg2'
    );
    
    /**
     *
     * @var type 
     */
    public static $unicityFields = array(
        'fields' => array('servicetemplate' => 'cfg_services, service_id, service_description, service_register'
        ),
    );

    /**
     * Get list of service templates
     *
     * @param string $searchStr
     * @param int $objectId The self id to skip
     * @return array
     */
    public static function getFormList($searchStr = "", $objectId = null)
    {
        $listServiceTemplate = parent::getFormList();

        foreach ($listServiceTemplate as $key => $serviceTemplate) {
            if ($serviceTemplate['id'] == $objectId) {
                unset($listServiceTemplate[$key]);
            }
        }
        $listServiceTemplate = array_values($listServiceTemplate);

        return $listServiceTemplate;
    }
    
    /**
     * 
     * @param int $template_id
     * @return string
     */
    public static function getTemplateName($template_id)
    {
        $di = Di::getDefault();
        
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
     * @param int $service_template_id
     * @return array
     */
    public static function getMyServiceTemplateModels($service_template_id)
    {
        // Initializing connection
        $di = Di::getDefault();
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
                $command = Command::get($value);
                return $command['command_name'];
            case 'timeperiod_tp_id':
                $timeperiod = Timeperiod::get($value);
                return $timeperiod['tp_name'];
            case 'service_is_volatile':
            case 'service_active_checks_enabled':
            case 'service_passive_checks_enabled':
            case 'service_parallelize_check':
            case 'service_obsess_over_service':
            case 'service_check_freshness':
            case 'service_event_handler_enabled':
            case 'service_flap_detection_enabled':
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
     * Get all services using a service template
     *
     * @param int $tmplId The service template id
     * @return array The list of service 
     */
    public static function getServices($tmplId)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        $query = "SELECT service_id, service_register
            FROM cfg_services
            WHERE service_activate = '1'
                AND service_template_model_stm_id = :svc_tmpl_id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':svc_tmpl_id', $tmplId, \PDO::PARAM_INT);
        $stmt->execute();
        $services = array();
        while ($row = $stmt->fetch()) {
            if ($row['service_register'] == 0) {
                $services = array_merge($services, static::getServices($row['service_id']));
            }
            $services[] = $row['service_id'];
        }
        return array_unique($services);
    }
    
}
