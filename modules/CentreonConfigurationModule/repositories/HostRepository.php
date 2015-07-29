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
use CentreonConfiguration\Models\Host;
use CentreonConfiguration\Models\Command;
use CentreonConfiguration\Models\Timeperiod;
use CentreonConfiguration\Models\Service;
use Centreon\Internal\Utils\YesNoDefault;
use CentreonConfiguration\Repository\Repository;
use CentreonConfiguration\Repository\ServiceRepository;
use CentreonConfiguration\Models\AclresourceHostsParams;
use CentreonConfiguration\Models\Relation\Host\Service as HostServiceRelation;
use CentreonConfiguration\Models\Relation\Hosttemplate\Servicetemplate as HostTemplateServiceTemplateRelation;
use CentreonConfiguration\Models\Relation\Service\Hosttemplate as ServiceHostTemplateRelation;
use CentreonConfiguration\Models\Relation\Aclresource\Host as AclresourceHostRelation;
use Centreon\Internal\CentreonSlugify;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class HostRepository extends Repository
{
    
    public static $objectClass = '\CentreonConfiguration\Models\Host';
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
        'initial_state',
        'host_obsess_over_host',
        'host_check_freshness',
        'host_event_handler_enabled',
        'host_low_flap_threshold',
        'host_high_flap_threshold',
        'host_flap_detection_enabled',
        'flap_detection_options',
        'host_snmp_community',
        'host_snmp_version',
        'host_check_timeout'
    );
    
    /**
     *
     * @var type 
     */
    public static $unicityFields = array(
        'fields' => array(
            'host' => 'cfg_hosts,host_id,host_name'
        ),
    );

    /**
     *
     * @var type 
     */
    public static $defaultIcon = "icon-host";
    
    /**
     * Host create action
     *
     * @param array $givenParameters
     * @return int id of created object
     */
    public static function create($givenParameters, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {
        if($origin == 'api' && !isset($givenParameters['command_command_id'])){
            $givenParameters['command_command_id'] = " ";
        }
        $id = parent::create($givenParameters, $origin, $route, $validate, $validateMandatory);
        self::deployServices($id);
        return $id;
    }

    /**
     * Host update action
     *
     * @param array $givenParameters
     * @throws \Centreon\Internal\Exception
     */
    public static function update($givenParameters, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {
        if($origin == 'api' && !isset($givenParameters['command_command_id'])){
            $givenParameters['command_command_id'] = " ";
        }
        
        parent::update($givenParameters, $origin, $route, $validate, $validateMandatory);
        if (isset($givenParameters['object_id'])) {
            self::deployServices($givenParameters['object_id']);
            if (isset($givenParameters['host_name'])) {
                self::updateSlugServices($givenParameters['object_id'], $givenParameters['host_name']);
            }
        }
    }

    public static function getIconImagePath($hostId){
   // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $router = $di->get('router');
        
        $finalRoute['value'] = "";
        $finalRoute['type'] = "";
        $templates = array();
        $alreadyProcessed = false;


        $stmt = $dbconn->prepare(
            "SELECT b.filename "
            . "FROM cfg_hosts h, cfg_hosts_images_relations hir, cfg_binaries b "
            . "WHERE h.host_id = :hostId "
            . "AND h.host_id = hir.host_id "
            . "AND hir.binary_id = b.binary_id"
        );
        while (empty($finalRoute['value'])) {
            $stmt->bindParam(':hostId', $hostId, \PDO::PARAM_INT);
            $stmt->execute();
            $ehiResult = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!is_null($ehiResult['filename'])) {
                $filenameExploded = explode('.', $ehiResult['filename']);
                $nbOfOccurence = count($filenameExploded);
                $fileFormat = $filenameExploded[$nbOfOccurence-1];
                $filenameLength = strlen($ehiResult['filename']);
                $routeAttr = array(
                    'image' => substr($ehiResult['filename'], 0, ($filenameLength - (strlen($fileFormat) + 1))),
                    'format' => '.'.$fileFormat
                );
                $imgSrc = $router->getPathFor('/uploads/[*:image][png|jpg|gif|jpeg:format]', $routeAttr);
                $finalRoute['value'] = $imgSrc;
                $finalRoute['type'] = 'image';
            } else {
                if (count($templates) == 0 && !$alreadyProcessed) {
                    $templates = static::getTemplateChain($hostId, array(), -1);
                    $alreadyProcessed = true;
                } else if (count($templates) == 0 && $alreadyProcessed) {
                    $finalRoute['value'] = static::$defaultIcon;
                    $finalRoute['type'] = 'class';
                }
                $currentHost = array_shift($templates);
                $hostId = $currentHost['id'];
            }
        }

        return $finalRoute;
        
        
    }
    
    
    /**
     * 
     * @param string $host_name
     * @return string
     */
    public static function getIconImage($host_name)
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $router = $di->get('router');
        
        $finalRoute = "";
        $templates = array();
        $alreadyProcessed = false;
        $hostIdTab = Host::getIdByParameter('host_name', array($host_name));
        if (count($hostIdTab) == 0) {
            $finalRoute = "<i class='icon-host ico-20'></i>";
        } else {
            $hostId = $hostIdTab[0];
        }

        $stmt = $dbconn->prepare(
            "SELECT b.filename "
            . "FROM cfg_hosts h, cfg_hosts_images_relations hir, cfg_binaries b "
            . "WHERE h.host_id = :hostId "
            . "AND h.host_id = hir.host_id "
            . "AND hir.binary_id = b.binary_id"
        );
        while (empty($finalRoute)) {
            $stmt->bindParam(':hostId', $hostId, \PDO::PARAM_INT);
            $stmt->execute();
            $ehiResult = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!is_null($ehiResult['filename'])) {
                $filenameExploded = explode('.', $ehiResult['filename']);
                $nbOfOccurence = count($filenameExploded);
                $fileFormat = $filenameExploded[$nbOfOccurence-1];
                $filenameLength = strlen($ehiResult['filename']);
                $routeAttr = array(
                    'image' => substr($ehiResult['filename'], 0, ($filenameLength - (strlen($fileFormat) + 1))),
                    'format' => '.'.$fileFormat
                );
                $imgSrc = $router->getPathFor('/uploads/[*:image][png|jpg|gif|jpeg:format]', $routeAttr);
                $finalRoute = '<img src="'.$imgSrc.'">';
            } else {
                if (count($templates) == 0 && !$alreadyProcessed) {
                    $templates = static::getTemplateChain($hostId, array(), -1);
                    $alreadyProcessed = true;
                } else if (count($templates) == 0 && $alreadyProcessed) {
                    $finalRoute = "<i class='icon-host ico-20'></i>";
                }
                $currentHost = array_shift($templates);
                $hostId = $currentHost['id'];
            }
        }

        return $finalRoute;    
    }

    /**
     * Get configuration data of a host
     * 
     * @param int $hostId
     * @return array
     */
    public static function getConfigurationData($hostId)
    {
        $myHostParameters = Host::getParameters($hostId, "*");
        $myHostParameters['icon'] = self::getIconImagePath($hostId);

        //$myHostParameters['templates'] = HostRepository::getTemplateChainTree($hostId);

        return $myHostParameters;
    }

    /**
     * Get configuration data of a host templates
     * 
     * @param int $hostId
     * @return array
     */
    public static function getTemplatesChainConfigurationData($hostId){
        return HostRepository::getTemplateChainTree($hostId);
    }
    
    /**
     * Get configurated services of a host
     * 
     * @param string $relClass
     * @param int $id
     * @return array 
     */
    public static function getServicesForHost($relClass, $id){
        $curObj = static::$objectClass;
        if ($relClass::$firstObject == $curObj) {
            $tmp = $relClass::$secondObject;
            $fArr = array();
            $sArr = array('*');
        } else {
            $tmp = $relClass::$firstObject;
            $fArr = array($tmp::getPrimaryKey(), $tmp::getUniqueLabelField());
            $sArr = array();
        }
        $cmp = $curObj::getTableName() . '.' . $curObj::getPrimaryKey();
        $list = $relClass::getMergedParameters(
            $fArr,
            $sArr,
            -1,
            0,
            null,
            "ASC",
            array($cmp => $id),
            "AND"
        );
        /*
        $finalList = array();
        foreach ($list as $obj) {
            $finalList[] = array(
                "id" => $obj[$tmp::getPrimaryKey()],
                "text" => $obj[$tmp::getUniqueLabelField()]
            );
        }*/
        return $list;
       
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
        if (isset($data['host_id'])) {
            $checkdata[_('id')] = $data['host_id'];
        }
        
        if (isset($data['host_name'])) {
            $checkdata[_('name')] = $data['host_name'];
        }
        
        if (isset($data['command_command_id'])) {
            $checkdata[_('command')] = static::getObjectName('\CentreonConfiguration\Models\Command', $data['command_command_id']);
        }
        
        if (isset($data['timeperiod_tp_id'])) {
            $checkdata[_('time_period')] = static::getObjectName('\CentreonConfiguration\Models\Timeperiod', $data['timeperiod_tp_id']);
        }
        
        $checkdata[_('max_check attempts')] = "";
        if(isset($data['host_max_check_attempts'])){
            $checkdata[_('max_check attempts')] = $data['host_max_check_attempts'];
        }
        
        $checkdata[_('check_interval')] = "";
        if(isset($data['host_check_interval'])){
            $checkdata[_('check_interval')] = $data['host_check_interval'];
        }
        
        $checkdata[_('retry_check_interval')] = "";
        if(isset($data['host_retry_check_interval'])){
            $checkdata[_('retry_check_interval')] = $data['host_retry_check_interval'];
        }

        $checkdata[_('active_checks_enabled')] = "";
        if(isset($data['host_active_checks_enabled'])){
            $checkdata[_('active_checks_enabled')] = YesNoDefault::toString($data['host_active_checks_enabled']);
        }
        
        $checkdata[_('passive_checks_enabled')] = "";
        if(isset($data['host_passive_checks_enabled'])){
            $checkdata[_('passive_checks_enabled')] = $data['host_passive_checks_enabled'];
        }
        
        if(!empty($data['icon'])){
            $checkdata[_('icon')] = $data['icon'];
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
            'value' => $data['host_name']
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
            'value' => $data['host_max_check_attempts']
        );
        $checkdata[] = array(
            'label' => _('Check interval'),
            'value' => $data['host_check_interval']
        );
        $checkdata[] = array(
            'label' => _('Retry check interval'),
            'value' => $data['host_retry_check_interval']
        );
        $checkdata[] = array(
            'label' => _('Active checks enabled'),
            'value' => YesNoDefault::toString($data['host_active_checks_enabled'])
        );
        $checkdata[] = array(
            'label' => _('Passive checks enabled'),
            'value' => YesNoDefault::toString($data['host_passive_checks_enabled'])
        );

        return $checkdata;
    }

    /**
     * Get the value from template
     *
     * @param int $hostId The host template Id
     * @return array
     */
    public static function getInheritanceValues($hostId, $withHostValues = false)
    {
        $values = array();
        $templates = static::getTemplateChain($hostId, array(), -1);

        if ($withHostValues) {
            array_unshift($templates, $hostId);
        }

        foreach ($templates as $template) {
            $inheritanceValues = HostTemplateRepository::getInheritanceValues($template['id']);
            $tmplValues = Host::getParameters($template['id'], self::$inheritanceColumns);
            $tmplValues = array_filter($tmplValues, function($value) {
                return !is_null($value);
            });
            $tmplValues = array_merge($inheritanceValues, $tmplValues);
            $values = array_merge($tmplValues, $values);
        }

        return $values;
    }
    
    /**
     * Get template chain ordinated in tree (id, text) 
     *
     * @param int $hostId The host or host template Id
     * @param array $alreadyProcessed The host templates already processed
     * @param int $depth The depth to search
     * @return array
     */
    public static function getTemplateChainTree($hostId, $depth = -1)
    {
        $templates = array();

        $db = Di::getDefault()->get('db_centreon');
        $router = Di::getDefault()->get('router');
        $sql = "SELECT h.* "
            . " FROM cfg_hosts h, cfg_hosts_templates_relations htr"
            . " WHERE h.host_id=htr.host_tpl_id"
            . " AND htr.host_host_id=:host_id"
            . " AND host_activate = '1'"
            . " AND host_register = '0'"
            . " ORDER BY `order` ASC";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':host_id', $hostId, \PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetchAll();
        foreach ($row as $template) {
            $templatesTmp = self::formatDataForSlider($template);
            $templatesTmp['url_edit'] = $router->getPathFor('/centreon-configuration/hosttemplate/'.$template['host_id']);
            $templatesTmp['icon'] = self::getIconImagePath($template['host_id']);
            $templatesTmp['servicesTemplate'] = HostTemplateRepository::getRelationsCustom("\CentreonConfiguration\Models\Relation\Hosttemplate\Servicetemplate", $template['host_id']);
            $templatesTmp['templates'] = self::getTemplateChainTree($template['host_id'], $depth);

            $templates[] = $templatesTmp;
        }
        return $templates;

    }

    /**
     * Get template chain (id, text)
     *
     * @param int $hostId The host or host template Id
     * @param array $alreadyProcessed The host templates already processed
     * @param int $depth The depth to search
     * @return array
     */
    public static function getTemplateChain($hostId, $alreadyProcessed = array(), $depth = -1, $allFields = false)
    {
        $templates = array();
        if (($depth == -1) || ($depth > 0)) {
            if ($depth > 0) {
                $depth--;
            }
            if (in_array($hostId, $alreadyProcessed)) {
                return $templates;
            } else {
                $alreadyProcessed[] = $hostId;
                // @todo improve performance
                $db = Di::getDefault()->get('db_centreon');

                if(!$allFields){
                    $fields = "h.host_id, h.host_name";
                }else{
                    $fields = " * ";
                }
                $sql = "SELECT " . $fields . " " 
                    . " FROM cfg_hosts h, cfg_hosts_templates_relations htr"
                    . " WHERE h.host_id=htr.host_tpl_id"
                    . " AND htr.host_host_id=:host_id"
                    . " AND host_activate = '1'"
                    . " AND host_register = '0'"
                    . " ORDER BY `order` ASC";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':host_id', $hostId, \PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetchAll();

                foreach ($row as $template) {
                    if(!$allFields){
                        $templates[] = array(
                            "id" => $template['host_id'],
                            "text" => $template['host_name']
                        );
                    }else{
                        $templates[] = $template;
                    }
                    
                    $templates = array_merge($templates, self::getTemplateChain($template['host_id'], $alreadyProcessed, $depth,$allFields));
                }
                return $templates;
            }
        }
        return $templates;
    }
    /**
     * Get template chain (id, text)
     *
     * @param int $hostId The host or host template Id
     * @param array $alreadyProcessed The host templates already processed
     * @param int $depth The depth to search
     * @return array
     */
    public static function getTemplateChainInverse($hostId, $alreadyProcessed = array())
    {
        $templates = array();
        
        if (in_array($hostId, $alreadyProcessed)) {
            return $templates;
        } else {
            $alreadyProcessed[] = $hostId;
            // @todo improve performance
            $db = Di::getDefault()->get('db_centreon');

            $sql = "SELECT htr.host_host_id, h.host_name  FROM cfg_hosts h, cfg_hosts_templates_relations htr
                 WHERE h.host_id = htr.host_tpl_id
                 AND htr.host_tpl_id = :tpl_id
                 AND host_activate = '1'
                 ORDER BY `order` ASC";
            //echo $sql."<>".$hostId;
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':tpl_id', $hostId, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetchAll();

            foreach ($row as $template) {
                $templates[] = array(
                    "id" => $template['host_host_id'],
                    "text" => $template['host_name']
                );
                $templates = array_merge($templates, self::getTemplateChainInverse($template['host_host_id'], $alreadyProcessed));
            }
            return $templates;
        }

        return $templates;
    }
    /**
     * Returns array of services that are linked to a poller
     *
     * @param int $pollerId
     * @return array
     */
    public static function getHostsByPollerId($pollerId)
    {
        $db = Di::getDefault()->get('db_centreon');

        $sql = "SELECT h.host_id, h.host_name
            FROM cfg_hosts h
            WHERE h.poller_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($pollerId));
        $arr = array();
        if ($stmt->rowCount()) {
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $arr[$row['host_id']] = $row['host_name'];
            }
        }

        return $arr;
    }

    /**
     * Deploy services by host templates
     *
     * @param int $hostId
     * @param int $hostTemplateId
     */
    public static function deployServices($hostId, $hostTemplateId = null)
    {
        static $deployedServices = array();
        $aServices = array();

        $db = Di::getDefault()->get('db_centreon');

        //get host template
        $aHostTemplates = HostRepository::getTemplateChain($hostId, array(), -1);

        // get host services
        $aHostServices = HostServiceRelation::getMergedParameters(
            array('host_id'),
            array(
                'service_id',
                'service_description',
                'service_alias',
                'inherited'
            ),
            -1,
            0,
            null,
            "ASC",
            array('host_id' => $hostId),
            "OR"
        );

        // get all service templates linked to a host by its host templates
        $aHostServiceTemplates = array();
        foreach ($aHostTemplates as $oHostTemplate) {

            $aHostTemplateServiceTemplates = HostTemplateServiceTemplateRelation::getMergedParameters(
                array('host_id'),
                array(
                    'service_id', 
                    'service_description',
                    'service_alias'
                ),
                -1,
                0,
                null,
                "ASC",
                array('host_id' => $oHostTemplate['id']),
                "OR"
            );

            // Remove services with same description
            foreach ($aHostTemplateServiceTemplates as $oHostTemplateServiceTemplate) {
                if (!isset($aHostServiceTemplates[$oHostTemplateServiceTemplate['service_alias']])) {
                    $aHostServiceTemplates[$oHostTemplateServiceTemplate['service_alias']] = $oHostTemplateServiceTemplate;
                }
            }
        }

        // get services linked to the host
        $aServicesDescription = array_values(array_column($aHostServices, 'service_description'));
        
        $repository = "CentreonConfiguration\Repository\ServiceRepository";
        $repository::setRelationMap(
                array(
                        "service_hosts" => "\\CentreonConfiguration\\Models\\Relation\\Service\\Host",
                        "service_parents" => "\\CentreonConfiguration\\Models\\Relation\\Service\\Serviceparents",
                        "service_children" => "\\CentreonConfiguration\\Models\\Relation\\Service\\Servicechildren")
            );
        $repository::setObjectName("Service");
        $repository::setObjectClass("\CentreonConfiguration\\Models\\Service");
        $oModel = "CentreonConfiguration\Models\Service";

        $oSlugify = new CentreonSlugify($oModel, $repository);
        
        $db->beginTransaction();
        
        $aTemplateServicesDescription = array_keys($aHostServiceTemplates);
        foreach($aHostServices as $service){
            if($service['inherited'] == 1 && !in_array($service['service_description'], $aTemplateServicesDescription)){
                Service::delete($service['service_id']);
            }
        }
        
        // create services which don't yet exist
        foreach ($aHostServiceTemplates as $oHostServiceTemplate) {
            if (!in_array($oHostServiceTemplate['service_alias'], $aServicesDescription)) {
                $sHostName = Host::get($hostId, 'host_name');
                if (isset($sHostName['host_name'])) {
                    $sString = $sHostName['host_name']." ".$oHostServiceTemplate['service_alias'];
                } else {
                    $sString = $oHostServiceTemplate['service_alias'];
                }
                $sSlug = $oSlugify->slug($sString);
                
                if (!empty($oHostServiceTemplate['service_alias'])) {
                    $sData = $oHostServiceTemplate['service_alias'];
                } else {
                    $sData = $oHostServiceTemplate['service_description'];
                }
                               
                $newService['service_slug'] = $sSlug;            
                $newService['service_description'] = $sData;
                $newService['service_template_model_stm_id'] = $oHostServiceTemplate['service_id'];
                $newService['service_register'] = 1;
                $newService['service_activate'] = 1;
                $newService['organization_id'] = Di::getDefault()->get('organization');
                $newService['inherited'] = 1;
                $serviceId = Service::insert($newService);
                
                HostServiceRelation::insert($hostId, $serviceId);
                ServiceHostTemplateRelation::insert($serviceId, $oHostServiceTemplate['host_id']);
            }
        }

        $db->commit();
    }

    /**
     * update Host acl
     *
     * @param string $action
     * @param int $objectId
     * @param array $hostIds
     */
    public static function updateHostAcl($action, $objectId, $hostIds)
    {
        if (($action === 'create') || ($action === 'update')) {
            AclresourceHostRelation::delete($objectId);
            foreach ($hostIds as $hostId) {
                AclresourceHostRelation::insert($objectId, $hostId);
            }
        }
    }

    /**
     * get Hosts by acl id
     *
     * @param int $aclId
     */
    public static function updateAllHostsAcl($action, $objectId, $allHosts)
    {
        if (($action === 'create') || ($action === 'update')) {
            try {
                AclresourceHostsParams::delete($objectId);
            } catch (\Exception $e) {

            }
            AclresourceHostsParams::insert(array(
                "acl_resource_id" => $objectId,
                "all_hosts" => $allHosts
                ),
                true
            );
        }
    }

    /**
     * get Hosts by acl id
     *
     * @param int $aclId
     */
    public static function getHostsByAclResourceId($aclId)
    {
        $hostList = AclresourceHostRelation::getMergedParameters(
            array(),
            array('host_id', 'host_name'),
            -1,
            0,
            null,
            "ASC",
            array('cfg_acl_resources_hosts_relations.acl_resource_id' => $aclId),
            "AND"
        );

        $finalHostList = array();
        foreach ($hostList as $host) {
            $finalHostList[] = array(
                "id" => $host['host_id'],
                "text" => $host['host_name']
            );
        }

        return $finalHostList;
    }
    
    
    /**
     * Update slug services by host
     *
     * @param int $iHostId
     * @param string $sHostName
     */
    public static function updateSlugServices($iHostId, $sHostName)
    {
        $aServices = array();
        $db = Di::getDefault()->get('db_centreon');
        
        $repository = "CentreonConfiguration\Repository\ServiceRepository";
        
        $repository::setObjectName("Service");
        $repository::setObjectClass("\CentreonConfiguration\\Models\\Service");
        $oModel = "CentreonConfiguration\Models\Service";

        $oSlugify = new CentreonSlugify($oModel, $repository);

        // get services
        $aHostServices = HostServiceRelation::getMergedParameters(
            array('host_id'),
            array(
                'service_id',
                'service_description',
                'service_alias'
            ),
            -1,
            0,
            null,
            "ASC",
            array('host_id' => $iHostId),
            "OR"
        );

        foreach ($aHostServices as $key => $oService) {
            $sString = $sHostName ." ".$oService['service_description'];
            $sSlug = $oSlugify->slug($sString);
            Service::updateSlug($oService['service_id'], $sSlug);
       }
        
    }
    
    public static function getSlugByUniqueField($object){
        
        $objectClass = self::$objectClass;
        return $objectClass::getSlugByUniqueField($object['host-name'], array('host_register' => '1'));
    }
    
}
