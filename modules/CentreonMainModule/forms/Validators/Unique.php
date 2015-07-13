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

namespace CentreonMain\Forms\Validators;

use Centreon\Internal\Di;
use Centreon\Internal\Form\Validators\ValidatorInterface;

use CentreonConfiguration\Repository\ServicetemplateRepository;
use CentreonConfiguration\Repository\ServiceRepository;
use CentreonConfiguration\Repository\HostTemplateRepository;
use CentreonConfiguration\Repository\HostRepository;
use CentreonConfiguration\Repository\CommandRepository;
use CentreonConfiguration\Repository\TrapRepository;
use CentreonConfiguration\Repository\PollerRepository;
use CentreonConfiguration\Repository\ResourceRepository;
use CentreonConfiguration\Models\Poller;

use CentreonAdministration\Repository\ContactRepository;
use CentreonAdministration\Repository\UserRepository;
use CentreonAdministration\Repository\LanguageRepository;
use CentreonAdministration\Repository\DomainRepository;
use CentreonAdministration\Repository\EnvironmentRepository;
use CentreonAdministration\Repository\UsergroupRepository;
use CentreonAdministration\Repository\AclresourceRepository;
use CentreonAdministration\Repository\TagsRepository;

use CentreonBam\Repository\BusinessActivityRepository;
use CentreonBam\Repository\IndicatorRepository;

use CentreonPerformance\Repository\GraphTemplate;

use Centreon\Internal\Exception\Validator\MissingParameterException;
use CentreonConfiguration\Models\Host;


/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class Unique implements ValidatorInterface
{   
    /**
     * 
     * @param string $sModule
     * @param string $sNameObject
     * @param array $aDatas
     * @return mixte
     */
    public static function validateDataSimple($sModule, $sNameObject, $aDatas) 
    {
        $return = '';
        $iObjectId = '';
        
        $oRepository = $sModule."\Repository\\".ucfirst($sNameObject."Repository");
        $oModel = $sModule."\Models\\".ucfirst($sNameObject);
        $sChampUnique = $oModel::getUniqueLabelField();
        
        if (isset($aDatas['extraParams'][$sChampUnique])) {
            $sLabel = $aDatas['extraParams'][$sChampUnique];
                  
            $aParams[$sNameObject] = $sLabel;
                        
            if (isset($aDatas['extraParams']['object_id']) && !empty($aDatas['extraParams']['object_id'])) {
                $iObjectId = $aDatas['extraParams']['object_id'];
            }
            
            try {
                $idReturned = $oRepository::getIdFromUnicity($aParams);
                $return[] = self::compareResponse($iObjectId, $idReturned);

            } catch (MissingParameterException $e) {
                $return[] = 0;
            }
        }
        
        return $return;
    }
    
    /**
     * 
     * @param type $sModule
     * @param type $sNameObject
     * @param array $aDatas
     */
    public static function validateDataService($sModule, $sNameObject, $aDatas) 
    {
        $iObjectId = '';
        $return = '';
        
        $oRepository = $sModule."\Repository\\".ucfirst($sNameObject."Repository");
        $oModel = $sModule."\Models\\".ucfirst($sNameObject);
        $sChampUnique = $oModel::getUniqueLabelField();
        $sChampPk = $oModel::getPrimaryKey();
            
        if (isset($aDatas['extraParams'][$sChampUnique])) {
            $sLabel = $aDatas['extraParams'][$sChampUnique];
        }
        if (isset($aDatas['extraParams'][$sChampPk])) {
            $iId = $aDatas['extraParams'][$sChampPk];
        }

        if (isset($aDatas['extraParams']['service_hosts'])) {
            $aHosts = explode(",", $aDatas['extraParams']['service_hosts']);
            $aHosts = array_diff($aHosts, array( '' ) );

            if (isset($aDatas['extraParams']['object_id']) && !empty($aDatas['extraParams']['object_id'])) {
                $iObjectId = $aDatas['extraParams']['object_id'];
            }

            foreach ($aHosts as $iIdHost) {
                $sHostName = "";
                $aHostName = Host::getParameters($iIdHost, 'host_name');
                if (is_array($aHostName) && isset($aHostName['host_name']) & !empty($aHostName['host_name'])) {
                    $sHostName = $aHostName['host_name'];
                }

                $aParams['host'] = $sHostName;
                $aParams['service'] = $sLabel;
                try {
                    $idReturned = $oRepository::getIdFromUnicity($aParams);

                    $return[] = self::compareResponse($iObjectId, $idReturned);

                } catch (MissingParameterException $e) {
                    $return[] = 0;
                }
            }
        }
        
        return $return;
    }

    /**
     * 
     * @param type $sModule
     * @param type $sNameObject
     * @param array $aDatas
     * @param string $value
     */
    public static function validateDataIndicator($sModule, $sNameObject, $aDatas, $value) 
    {
        $iObjectId = '';
        $return = '';
        $aParams = array();
        
        $oRepository = $sModule."\Repository\\".ucfirst($sNameObject."Repository");
        
        if (isset($aDatas['extraParams']['kpi_type']) && !empty($value)) {
            if ($aDatas['extraParams']['kpi_type'] == '0' && isset($aDatas['extraParams']['service_id']) && $aDatas['extraParams']['service_id'] == $value) {
                $serviceId = "";
                if (isset($aDatas['extraParams']['service_id'])) {
                    $serviceId = $aDatas['extraParams']['service_id'];
                }
                $aParams['id_ba'] = $aDatas['extraParams']['id_ba'];
                $aParams['serviceIndicator'] = $serviceId;

            } elseif ($aDatas['extraParams']['kpi_type'] == '2' && isset($aDatas['extraParams']['id_indicator_ba']) && $aDatas['extraParams']['id_indicator_ba'] == $value) {
                if (isset($aDatas['extraParams']['id_indicator_ba'])) {
                    $sLabel = $aDatas['extraParams']['id_indicator_ba'];
                }
                $aParams['id_ba'] = $aDatas['extraParams']['id_ba'];
                $aParams['baIndicator'] = $aDatas;

            } elseif ($aDatas['extraParams']['kpi_type'] == '3' && isset($aDatas['extraParams']['boolean_name']) && $aDatas['extraParams']['boolean_name'] == $value) {
                if (isset($aDatas['extraParams']['boolean_name'])) {
                    $sLabel = $aDatas['extraParams']['boolean_name'];
                }
                $aParams['boolean'] = $sLabel;
            }

            try {
                $idReturned = IndicatorRepository::getIdFromUnicity($aParams, $aDatas['extraParams']['kpi_type']);
                $iObjectId = '';

                if (isset($aDatas['extraParams']['object_id']) && !empty($aDatas['extraParams']['object_id'])) {
                    $iObjectId = $aDatas['extraParams']['object_id'];
                }
                $return[] = self::compareResponse($iObjectId, $idReturned);
            } catch (MissingParameterException $e) {
                $return[] = 0;
            }
        }
        return $return;
    }
    
    /**
     * 
     * @param type $sModule
     * @param type $sNameObject
     * @param array $aDatas
     */
    public static function validateDataResource($sModule, $sNameObject, $aDatas) 
    {
        $iObjectId = '';
        $return = '';
        
        $oRepository = $sModule."\Repository\\".ucfirst($sNameObject."Repository");
        $oModel = $sModule."\Models\\".ucfirst($sNameObject);
        $sChampUnique = $oModel::getUniqueLabelField();
        $sChampPk = $oModel::getPrimaryKey();
            
        if (isset($aDatas['extraParams'][$sChampUnique])) {
            $sLabel = $aDatas['extraParams'][$sChampUnique];
        }
        
        if (isset($aDatas['extraParams']['resource_pollers'])) {
            $aPollers = explode(",", $aDatas['extraParams']['resource_pollers']);
            $aPollers = array_map('trim', $aPollers);
            $aPollers = array_diff($aPollers, array( '' ) );

            if (isset($aDatas['extraParams']['resource_name']) && count($aPollers) > 0) {
                $aParams['resources'] = $sLabel;

                foreach ($aPollers as $iIdPoller) {
                    $sPollerName = "";
                    $aPollerName = Poller::getParameters($iIdPoller, 'name');
                    if (is_array($aPollerName) && isset($aPollerName['name']) & !empty($aPollerName['name'])) {
                        $sPollerName = $aPollerName['name'];
                    }

                    $aParams['poller'] = $sPollerName;

                    try {
                        $idReturned = $oRepository::getIdFromUnicity($aParams);
                        $iObjectId = '';

                        if (isset($aDatas['extraParams']['object_id']) && !empty($aDatas['extraParams']['object_id'])) {
                            $iObjectId = $aDatas['extraParams']['object_id'];
                        }
                        $return[] = self::compareResponse($iObjectId, $idReturned);
                    } catch (MissingParameterException $e) {
                        $return[] = 0;
                    }

                }
            }
        }
        return $return;
    }
    
    /**
     * 
     * @param type $sModule
     * @param type $sNameObject
     * @param array $aDatas
     */
    public static function validateDataSpecific($sModule, $oRepository, $oModel, $sNameObject, $aDatas) 
    {
        $iObjectId = '';
        $return = '';
        
        $sChampUnique = $oModel::getUniqueLabelField();
            
        if (isset($aDatas['extraParams'][$sChampUnique])) {
            $sLabel = $aDatas['extraParams'][$sChampUnique];
        
            $aParams[$sNameObject] = $sLabel;
            
            try {
                $idReturned = $oRepository::getIdFromUnicity($aParams);
                $iObjectId = '';

                if (isset($aDatas['extraParams']['object_id']) && !empty($aDatas['extraParams']['object_id'])) {
                    $iObjectId = $aDatas['extraParams']['object_id'];
                }
                $return[] = self::compareResponse($iObjectId, $idReturned); 

            } catch (MissingParameterException $e) {
                $return[] = 0;
            }
        }
        return $return;
    }

    /**
     * 
     * @param type $value
     * @param array $params
     * @return boolean
     */
    
    public function validate($value, $params = array(), $sContext = 'server')
    {
        $db = Di::getDefault()->get('db_centreon');
        $bSuccess = true;
        $resultError = _("Object already exists");
        $sMessage = '';
        
        $aParams = array();
        $aHost = array();
        $sLabel = '';
        $iId = '';
        $return = '';
        $iObjectId = '';
        
        $value = trim($value);
       
        if (isset($params['object'])) {
            switch ($params['object']) {
                case 'poller' :
                case 'host' :
                case 'servicetemplate' :
                case 'command' :
                case 'connector' :
                case 'manufacturer' :
                case 'timeperiod' :
                    $sModule = "CentreonConfiguration";
                    
                    $return = self::validateDataSimple($sModule, $params['object'], $params);
                    break;
                case 'trap' :
                    $sModule = "CentreonConfiguration";
                    
                    $oRepository = $sModule."\Repository\\".ucfirst($params['object']."Repository");
                    $oModel = $sModule."\Models\\".ucfirst($params['object']);
                    
                    $return = self::validateDataSpecific($sModule, $oRepository, $oModel, 'traps', $params);
                    break;
                case 'contact' :
                case 'user' :
                case 'language' :
                case 'domain' :
                case 'environment' :
                case 'tag' :
                case 'usergroup' :
                case 'aclresource' :
                    $sModule = "CentreonAdministration";
                    
                    $return = self::validateDataSimple($sModule, $params['object'], $params);
                    break;
                case 'businessactivity' :
                    $sModule = "CentreonBam";
                    $oRepository = $sModule."\Repository\BusinessActivityRepository";
                    $oModel = $sModule."\Models\BusinessActivity";
                    
                    $return = self::validateDataSpecific($sModule, $oRepository, $oModel, 'bam', $params);
                    break;
                case 'graphtemplate' :
                    $sModule = "CentreonPerformance";
                    
                    $oRepository = $sModule."\Repository\GraphTemplate";
                    $oModel = $sModule."\Models\GraphTemplate";
                    
                    $return = self::validateDataSpecific($sModule, $oRepository, $oModel, $params['object'], $params);
                    break;
                case 'service' :
                    $sModule = "CentreonConfiguration";
                    
                    $return = self::validateDataService($sModule, $params['object'], $params);
                    break;
                case 'resource' :
                    $sModule = "CentreonConfiguration";
                    
                    $return = self::validateDataResource($sModule, $params['object'], $params);
                    break;
                case 'hosttemplate' :
                    $sModule = "CentreonConfiguration";
                    $oRepository = $sModule."\Repository\HostTemplateRepository";
                    $oModel = $sModule."\Models\\".ucfirst($params['object']);
                    
                    $return = self::validateDataSpecific($sModule, $oRepository, $oModel, $params['object'], $params);
                    break;
                case 'indicator' :
                    $sModule = "CentreonBam";
                    $return = self::validateDataIndicator($sModule, $params['object'], $params, $value);
                    break;
            }
        }
      
        if (is_array($return)) {
            foreach($return as $valeur) {
                if ($valeur > 0) {
                    $bSuccess = false;
                    $sMessage = $resultError;
                    break;
                }
            } 
        } else {
            if ($return > 0) {
                $bSuccess = false;
                $sMessage = $resultError;
            }
        }
        
        if ($sContext == 'client') {
            if (empty($sMessage)) 
                $sMessage = true;
            
            $reponse = $sMessage;
        } else {
            $reponse = array(
                'success' => $bSuccess, 
                'error' => $sMessage
            );
        }

        return $reponse;
    }
    /**
     * 
     * @param int $iObjectId
     * @param int $iIdReturned
     * @return int
     */
    private function compareResponse($iObjectId, $iIdReturned)
    {
        $iRetour = '';
        if (!empty($iIdReturned)) {
            if ($iObjectId == $iIdReturned) {
                $iRetour = 0;
            } else {
                $iRetour = $iIdReturned;
            }
        } else {
            $iRetour = $iIdReturned;
        }
        return $iRetour;
    }
}
