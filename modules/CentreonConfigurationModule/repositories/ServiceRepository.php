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
use CentreonConfiguration\Models\Host;
use CentreonConfiguration\Models\Relation\Host\Service as HostServiceRelation;
use CentreonConfiguration\Models\Relation\Aclresource\Service as AclresourceServiceRelation;
use Centreon\Internal\Utils\YesNoDefault;
use Centreon\Internal\Utils\HumanReadable;
use CentreonConfiguration\Repository\Repository;
use Centreon\Internal\Exception\Validator\MissingParameterException;
use Centreon\Internal\CentreonSlugify;


/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class ServiceRepository extends Repository
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
    public static $objectName = 'Service';
    
    /**
     *
     * @var type 
     */

    public static $unicityFields = array(
        'fields' => array(
            'host' => 'cfg_hosts,host_id,host_name',
            'service' => 'cfg_services,service_id,service_description',
        ),
        'joint' => 'cfg_hosts_services_relations',
        'jointCondition' => 'cfg_hosts_services_relations.host_host_id = cfg_hosts.host_id AND cfg_hosts_services_relations.service_service_id = cfg_services.service_id'
    );

    /**
     * 
     * @param int $interval
     * @return string
     */
    public static function formatNotificationOptions($interval)
    {
        return HumanReadable::convert($interval, 's', $units, null, true);
    }
    
    /**
     * 
     * @param int $service_id
     * @param string $field
     * @return type
     */
    public static function getMyServiceField($service_id, $field)
    {
        
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $tab = array();
        while (1) {
            $stmt = $dbconn->prepare(
                "SELECT "
                . "`".$field."`, "
                . "service_template_model_stm_id "
                . "FROM cfg_services "
                . "WHERE "
                . "service_id = :service_id  LIMIT 1"
            );
            $stmt->bindParam(':service_id', $service_id, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetchAll();
            if ($row[0][$field]) {
                return $row[0][$field];
            } elseif ($row[0]['service_template_model_stm_id']) {
                if (isset($tab[$row[0]['service_template_model_stm_id']])) {
                    break;
                }
                $service_id = $row[0]["service_template_model_stm_id"];
                $tab[$service_id] = 1;
            } else {
                break;
            }
        }
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
        $tplArr = null;
        
        $stmt = $dbconn->prepare(
            "SELECT service_description FROM cfg_services WHERE service_id = :svcTmplId LIMIT 1"
        );
        $stmt->bindParam(':svcTmplId', $service_template_id, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetchAll();
        if (count($row) > 0) {
            $tplArr = array(
                'id' => $service_template_id,
                'description' => \html_entity_decode(self::db2str($row[0]["service_description"]), ENT_QUOTES, "UTF-8")
            );
        }
        return $tplArr;
    }
    
    /**
     * 
     * @param string $string
     * @return string
     */
    public static function db2str($string)
    {
        $string = str_replace('#BR#', "\\n", $string);
        $string = str_replace('#T#', "\\t", $string);
        $string = str_replace('#R#', "\\r", $string);
        $string = str_replace('#S#', "/", $string);
        $string = str_replace('#BS#', "\\", $string);
        return $string;
    }
    
    /**
     * 
     * @param int $service_id
     * @return type
     */
    public static function getMyServiceAlias($service_id)
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');

        $tab = array();
        $stmt = $dbconn->prepare(
            "SELECT "
            . "service_alias, service_template_model_stm_id "
            . "FROM cfg_services "
            . "WHERE "
            . "service_id = :serviceId LIMIT 1"
        );
        while (1) {
            $stmt->bindParam(':serviceId', $service_id, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetchRow();
            if ($row["service_alias"]) {
                return html_entity_decode(db2str($row["service_alias"]), ENT_QUOTES, "UTF-8");
            } elseif ($row["service_template_model_stm_id"]) {
                if (isset($tab[$row['service_template_model_stm_id']])) {
                    break;
                }
                $service_id = $row["service_template_model_stm_id"];
                $tab[$service_id] = 1;
            } else {
                break;
            }
        }
    }
    
    public function getServiceSlugByUniqueField($service,$host){
        return Service::getServiceSlugByUniqueField($service,$host);
    }
    
    
    /**
     * 
     * @param int $service_id
     * @return string
     */
    public static function getIconImage($service_id)
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $router = $di->get('router');
        
        $finalRoute = "";
        
        $stmt = $dbconn->prepare(
            "SELECT b.filename, s.service_template_model_stm_id "
            . "FROM cfg_services s, cfg_services_images_relations sir, cfg_binaries b "
            . "WHERE s.service_id = :service_id "
            . "AND s.service_id = sir.service_id AND sir.binary_id = b.binary_id"
        );
        while (1) {
            $stmt->bindParam(':service_id', $service_id, \PDO::PARAM_INT);
            $stmt->execute();
            $esiResult = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!is_null($esiResult['filename'])) {
                $filenameExploded = explode('.', $esiResult['filename']);
                $nbOfOccurence = count($filenameExploded);
                $fileFormat = $filenameExploded[$nbOfOccurence-1];
                $filenameLength = strlen($esiResult['filename']);
                $routeAttr = array(
                    'image' => substr($esiResult['filename'], 0, ($filenameLength - (strlen($fileFormat) + 1))),
                    'format' => '.'.$fileFormat
                );
                $imgSrc = $router->getPathFor('/uploads/[*:image][png|jpg|gif|jpeg:format]', $routeAttr);
                $finalRoute .= '<img src="'.$imgSrc.'">';
                break;
            } elseif (is_null($esiResult['filename']) && is_null($esiResult['service_template_model_stm_id'])) {
                $finalRoute .= "<i class='icon-service ico-20'></i>";
                break;
            }
            
            $service_id = $esiResult['service_template_model_stm_id'];
        }
        
        return $finalRoute;
    }
    
    /**
     * Get configuration data of a service
     * 
     * @param int $serviceId
     * @return array
     */
    public static function getConfigurationData($serviceId)
    {
        return Service::getParameters($serviceId, "*");
    }

    /**
     * Format data so that it can be displayed in slider
     *
     * @param array $data
     * @return array $checkdata
     */
    public static function formatDataForSlider($data)
    {
        /* Check data */
        $checkdata = array();
        $checkdata[_('id')] = $data['service_id'];
        $checkdata[_('name')] = $data['service_description'];
        $checkdata[_('command')] = static::getObjectName('\CentreonConfiguration\Models\Command', $data['command_command_id']);
        $checkdata[_('time_period')] = static::getObjectName('\CentreonConfiguration\Models\Timeperiod', $data['timeperiod_tp_id']);
        
        $checkdata[_('max_check_attempts')] = "";
        if(isset($data['service_max_check_attempts'])){
            $checkdata[_('max_check_attempts')] = $data['service_max_check_attempts'];
        }
        
        $checkdata[_('check_interval')] = "";
        if(isset($data['service_normal_check_interval'])){
            $checkdata[_('check_interval')] = $data['service_normal_check_interval'];
        }

        $checkdata[_('retry_check_interval')] = "";
        if(isset($data['service_retry_check_interval'])){
            $checkdata[_('retry_check_interval')] = $data['service_retry_check_interval'];
        }
        
        $checkdata[_('active_checks_enabled')] = "";
        if(isset($data['service_active_checks_enabled'])){
            $checkdata[_('active_checks_enabled')] = YesNoDefault::toString($data['service_active_checks_enabled']);
        }
        
        $checkdata[_('passive_checks_enabled')] = "";
        if(isset($data['service_passive_checks_enabled'])){
            $checkdata[_('passive_checks_enabled')] = $data['service_passive_checks_enabled'];
        }
        return $checkdata;
    }
    
    
    /**
     * Format data so that it can be displayed in tooltip
     *
     * @param array $data
     * @return array $checkdata
     */
    public static function formatDataForTooltip($data)
    {
        /* Check data */
        $checkdata = array();
        $checkdata[] = array(
            'label' => _('Name'),
            'value' => $data['service_description']
        );
        $checkdata[] = array(
            'label' => _('Command'),
            'value' => static::getObjectName('\CentreonConfiguration\Models\Command', $data['command_command_id'])
        );
        $checkdata[] = array(
            'label' => _('Time period'),
            'value' => static::getObjectName('\CentreonConfiguration\Models\Timeperiod', $data['timeperiod_tp_id'])
        );
        $checkdata[] = array(
            'label' => _('Max check attempts'),
            'value' => $data['service_max_check_attempts']
        );
        $checkdata[] = array(
            'label' => _('Check interval'),
            'value' => $data['service_normal_check_interval']
        );
        $checkdata[] = array(
            'label' => _('Retry check interval'),
            'value' => $data['service_retry_check_interval']
        );
        $checkdata[] = array(
            'label' => _('Active checks enabled'),
            'value' => YesNoDefault::toString($data['service_active_checks_enabled'])
        );
        $checkdata[] = array(
            'label' => _('Passive checks enabled'),
            'value' => YesNoDefault::toString($data['service_passive_checks_enabled'])
        );

        return $checkdata;
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

    /**
     * Returns array of services that are linked to a poller
     *
     * @param int $pollerId
     * @return array
     */
    public static function getServicesByPollerId($pollerId)
    {
        $db = Di::getDefault()->get('db_centreon');

        $sql = "SELECT s.service_id, s.service_description
            FROM cfg_services s, cfg_hosts_services_relations hsr, cfg_hosts h
            WHERE s.service_id = hsr.service_service_id 
            AND hsr.host_host_id = h.host_id
            AND h.poller_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($pollerId));
        $arr = array();
        if ($stmt->rowCount()) {
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $arr[$row['service_id']] = $row['service_description'];
            }
        }

        return $arr;
    }

    /**
     * Return the list of template
     *
     * @param int $svcId The service ID
     * @return array
     */
    public static function getListTemplates($svcId, $alreadyProcessed = array())
    {
        $svcTmpl = array();
        if (in_array($svcId, $alreadyProcessed)) {
            return $svcTmpl;
        } else {
            $alreadyProcessed[] = $svcId;
            // @todo improve performance
            $dbconn = Di::getDefault()->get('db_centreon');

            $query = "SELECT service_template_model_stm_id FROM cfg_services WHERE service_id = :id";
            $stmt = $dbconn->prepare($query);
            $stmt->bindParam(':id', $svcId, \PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount()) {
                $row = $stmt->fetch();
                $stmt->closeCursor();
                if ($row['service_template_model_stm_id'] !== NULL) {
                    $svcTmpl = array_merge($svcTmpl, self::getListTemplates($row['service_template_model_stm_id'], $alreadyProcessed));
                    $svcTmpl[] = $row['service_template_model_stm_id'];
                }
            }
            return $svcTmpl;
        }
    }
    
    /**
     * Return the list of template by host id
     *
     * @param int $iHostId The Host ID
     * @return array
     */
    public static function getListTemplatesByHostId($iHostId)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        $svcTmpl = array();
        $query = "SELECT service_service_id FROM cfg_hosts_services_relations WHERE host_host_id = :id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':id', $iHostId, \PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount()) {
            $row = $stmt->fetch();
            $stmt->closeCursor();
            $svcTmpl[] = $row['service_service_id'];
        }
        return $svcTmpl;
    }
    
    /**
     * Return the list of template
     *
     * @param int $svcId The service ID
     * @return array
     */
    public static function getListServiceByIdTemplate($svcId)
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        $svcTmpl = array();
        $query = "SELECT service_id FROM cfg_services WHERE service_register = '1' AND service_template_model_stm_id = :id";
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':id', $svcId, \PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount()) {
            $row = $stmt->fetch();
            $stmt->closeCursor();
            $svcTmpl[] = $row['service_id'];
        }
        return $svcTmpl;
    }

     /**
     * Return Service name
     *
     * @param int or array $svcId The service ID
     * @return array
     */
    public static function getName($svcId)
    {
        $di = Di::getDefault();
        $router = $di->get('router');

        $serviceId = Service::getPrimaryKey();
        $serviceDescription = Service::getUniqueLabelField();
        $hostId = Host::getPrimaryKey();
        $hostName = Host::getUniqueLabelField();
        $filters = array(
            $serviceId => $svcId,
        );

        $list = HostServiceRelation::getMergedParameters(
            array($hostId, $hostName),
            array($serviceId, $serviceDescription),
            -1,
            0,
            null,
            "ASC",
            $filters,
            "OR"
        );

        $finalList = array();
        foreach ($list as $obj) {
            $finalList[] = array(
                "id" => $obj[$serviceId],
                "text" => $obj[$hostName] . ' ' . $obj[$serviceDescription]
            );
        }

        return $finalList;
    }

    /**
     * update Service acl
     *
     * @param string $action
     * @param int $objectId
     * @param array $hostIds
     */
    public static function updateServiceAcl($action, $objectId, $serviceIds)
    {
        if (($action === 'create') || ($action === 'update')) {
            AclresourceServiceRelation::delete($objectId);
            foreach ($serviceIds as $serviceId) {
                AclresourceServiceRelation::insert($objectId, $serviceId);
            }
        }
    }

    /**
     * get Services by acl id
     *
     * @param int $aclId
     */
    public static function getServicesByAclResourceId($aclId)
    {
        $serviceList = AclresourceServiceRelation::getMergedParameters(
            array(),
            array('service_id', 'service_description'),
            -1,
            0,
            null,
            "ASC",
            array('cfg_acl_resources_services_relations.acl_resource_id' => $aclId),
            "AND"
        );

        $serviceIdList = array();
        //$finalServiceList = array();
        foreach ($serviceList as $service) {
            $serviceIdList[] = $service['service_id'];
            /*$finalServiceList[] = array(
                "id" => $service['service_id'],
                "text" => $service['service_description']
            );*/
        }

        $finalServiceList = array();
        if (count($serviceIdList)) {
            $finalServiceList = self::getName($serviceIdList);
        }

        return $finalServiceList;
    }
    
    /**
     * Returns service_id
     *
     * @param string $sSlugService
     * @param string $sSlugHost
     * @return array
     */
    public static function getServiceBySlugs($sSlugService, $sSlugHost = null)
    {
        if(!is_null($sSlugHost)){
            $db = Di::getDefault()->get('db_centreon');

            $sql = "SELECT s.service_id, h.host_id
                FROM cfg_services s, cfg_hosts_services_relations hsr, cfg_hosts h
                WHERE s.service_id = hsr.service_service_id 
                AND hsr.host_host_id = h.host_id
                AND service_register = '1' 
                AND host_register = '1' 
                AND h.host_slug = :host_slug
                AND s.service_slug = :service_slug ";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':host_slug', $sSlugHost, \PDO::PARAM_STR);
            $stmt->bindParam(':service_slug', $sSlugService, \PDO::PARAM_STR);
            $stmt->execute();

            $arr = array();

            if ($stmt->rowCount() == 1) {
                $arr = $stmt->fetch(\PDO::FETCH_ASSOC);
            }

            return $arr;
        }else {
            $db = Di::getDefault()->get('db_centreon');

            $sql = "SELECT s.service_id, h.host_id
                FROM cfg_services s, cfg_hosts_services_relations hsr, cfg_hosts h
                WHERE s.service_id = hsr.service_service_id 
                AND hsr.host_host_id = h.host_id
                AND service_register = '1' 
                AND host_register = '1' 
                AND s.service_slug = :service_slug ";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':service_slug', $sSlugService, \PDO::PARAM_STR);
            $stmt->execute();

            $arr = array();

            if ($stmt->rowCount() == 1) {
                $arr = $stmt->fetch(\PDO::FETCH_ASSOC);
            }

            return $arr;
        }

    }
    
    
    /**
     * Generic create action
     * 
     * @param type $givenParameters
     * @param type $origin
     * @param type $route
     * @param type $validate
     * @param type $validateMandatory
     */
   
    public static function create($givenParameters, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {
        $id = null;
        $db = Di::getDefault()->get('db_centreon');
        
        $class = static::$objectClass;
        $pk = $class::getPrimaryKey();
        $columns = $class::getColumns();

        
        $sField = $class::getUniqueLabelField();
        $aHostId = explode(",", $givenParameters['service_hosts']);

        $aHostId = array_filter($aHostId, 'strlen');
        if (count($aHostId) > 0) {
            $sHostName = Host::get(current($aHostId), 'host_name');

            if (isset($sField) 
                    && isset($givenParameters[$sField]) && !is_null($class::getSlugField()) 
                    && 
                    (
                        (isset($givenParameters[$class::getSlugField()])) && is_null($givenParameters[$class::getSlugField()])
                        or
                        !isset($givenParameters[$class::getSlugField()])
                    )
               ) {
                $oSlugify = new CentreonSlugify($class, get_called_class());
                $sString = $sHostName['host_name']." ".$givenParameters[$sField];
                $sSlug = $oSlugify->slug($sString);
                $givenParameters[$class::getSlugField()] = $sSlug;
            }
        }
        $givenParameters['inherited'] = 0;
        return parent::create($givenParameters, $origin, $route, $validate, $validateMandatory);
    }
    
    /**
     * Generic update function
     * 
     * @param type $givenParameters
     * @param type $origin
     * @param type $route
     * @param type $validate
     * @param type $validateMandatory
     * 
     * @throws \Centreon\Internal\Exception
     */
    public static function update($givenParameters, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {
        $id = null;
        $db = Di::getDefault()->get('db_centreon');
        
        $class = static::$objectClass;
        $pk = $class::getPrimaryKey();
        $columns = $class::getColumns();
        
        if (isset($givenParameters['service_hosts'])) {
            $sField = $class::getUniqueLabelField();
            $aHostId = explode(",", $givenParameters['service_hosts']);
            $aHostId = array_filter($aHostId, 'strlen' );

            if (count($aHostId) > 0) {
                $sHostName = Host::get(current($aHostId), 'host_name');

                if (isset($sField) 
                        && isset($givenParameters[$sField]) && !is_null($class::getSlugField()) 
                        && 
                        (
                            (isset($givenParameters[$class::getSlugField()])) && is_null($givenParameters[$class::getSlugField()])
                            or
                            !isset($givenParameters[$class::getSlugField()])
                        )
                   ) {
                    $oSlugify = new CentreonSlugify($class, get_called_class());
                    $sString = $sHostName['host_name']." ".$givenParameters[$sField];
                    $sSlug = $oSlugify->slug($sString);
                    $givenParameters[$class::getSlugField()] = $sSlug;
                }
            }
        }
        parent::update($givenParameters, $origin, $route, $validate, $validateMandatory);
       
    }
    
    public static function getHostSlugFromServiceSlug($serviceSlug)
    {
        $class = static::$objectClass;
        return $class::getHostSlugFromServiceSlug($serviceSlug);
    }
     
}
