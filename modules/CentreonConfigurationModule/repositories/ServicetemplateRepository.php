<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
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

namespace CentreonConfiguration\Repository;

use Centreon\Internal\Di;
use CentreonConfiguration\Models\Service;
use CentreonConfiguration\Models\Servicetemplate;
use CentreonConfiguration\Models\Command;
use CentreonConfiguration\Models\Timeperiod;
use CentreonAdministration\Models\Domain;
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
        'domain_id',
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
     * Get configuration data of a service
     * 
     * @param int $serviceId
     * @return array
     */
    public static function getConfigurationData($serviceId)
    {
        return ServiceTemplate::getParameters($serviceId, "*");
    }
    
    
    
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
        $listServiceTemplate = parent::getFormList($searchStr, $objectId);

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
        
        $stmt = $dbconn->prepare(
            "SELECT service_description FROM cfg_services WHERE service_id = :service_template_id LIMIT 1"
        );
        $stmt->bindParam(':service_template_id', $service_template_id, \PDO::PARAM_INT);
        $stmt->execute();
        
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
     * @param bool $withServiceValues If the service template id is the base for get values
     * @return array
     */
    public static function getInheritanceValues($svcId, $withServiceValues = false)
    {
        $values = array();

        if ($withServiceValues) {
            $tmpl = $svcId;
        } else {
            $tmpl = Service::getParameters($svcId, array('service_template_model_stm_id'));
            $tmpl = $tmpl['service_template_model_stm_id'];
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
            case 'domain_id':
                $domain = Domain::get($value);
                return $domain['name'];
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
    
    /**
     * Get domain
     *
     * @return array | array(domain_id => name)
     */
    public static function getDomain($serviceId)
    {
        static $domains = null;

        if (is_null($domains)) {
            $domains = array();
            $db = Di::getDefault()->get('db_centreon');
            $sql = "SELECT d.domain_id, d.name, s.service_id
                FROM cfg_domains d, cfg_services s
                WHERE s.domain_id = d.domain_id";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $domains[$row['service_id']] = array($row['domain_id'] => $row['name']);
            }
        }
        if (isset($domains[$serviceId])) {
            return $domains[$serviceId];
        }
        return array();
    }
    
    public static function getSlugByUniqueField($object){
        
        $objectClass = self::$objectClass;
        return $objectClass::getSlugByUniqueField($object['servicetemplate-name'], array('service_register' => '0'));
        
    }
    
}
