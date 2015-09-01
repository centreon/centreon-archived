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

namespace CentreonConfiguration\Forms\Validators;

use Centreon\Internal\Di;
use Centreon\Internal\Form\Validators\ValidatorInterface;

use CentreonConfiguration\Repository\ServicetemplateRepository;
use CentreonConfiguration\Repository\ServiceRepository;
use CentreonConfiguration\Repository\HostTemplateRepository;
use CentreonConfiguration\Repository\HostRepository;
use CentreonConfiguration\Repository\CommandRepository;

use CentreonAdministration\Repository\ContactRepository;
use CentreonAdministration\Repository\UserRepository;

use CentreonBam\Repository\BusinessActivityRepository;

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
        
        if (isset($params['object']) && $params['object'] == 'service') {
            $objClass = "CentreonConfiguration\Repository\\".ucfirst($params['object']."Repository");
            
            if (isset($params['extraParams']['service_description'])) {
                $sLabel = $params['extraParams']['service_description'];
            }
            if (isset($params['extraParams']['service_id'])) {
                $iId = $params['extraParams']['service_id'];
            }

            if (isset($params['extraParams']['service_hosts'])) {
                $aHosts = explode(",", $params['extraParams']['service_hosts']);
                $aHosts = array_diff($aHosts, array( '' ) );
                
                $iObjectId = '';
                
                if (isset($params['extraParams']['object_id']) && !empty($params['extraParams']['object_id'])) {
                    $iObjectId = $params['extraParams']['object_id'];
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
                        $idReturned = $objClass::getIdFromUnicity($aParams);

                        $return[] = self::compareResponse($iObjectId, $idReturned);
                        
                    } catch (MissingParameterException $e) {
                        $return[] = 0;
                    }
                }
            }
        } elseif (isset($params['object']) && $params['object'] == 'servicetemplate') {
            $objClass = "CentreonConfiguration\Repository\\".ucfirst($params['object']."Repository");
            if (isset($params['extraParams']['service_description'])) {
                $sLabel = $params['extraParams']['service_description'];
            }

            $aParams['servicetemplate'] = $sLabel;
            
            try {
                $idReturned = $objClass::getIdFromUnicity($aParams);
                $iObjectId = '';
                
                if (isset($params['extraParams']['object_id']) && !empty($params['extraParams']['object_id'])) {
                    $iObjectId = $params['extraParams']['object_id'];
                }
                $return[] = self::compareResponse($iObjectId, $idReturned);
                
            } catch (MissingParameterException $e) {
                $return[] = 0;
            }
        } elseif (isset($params['object']) && $params['object'] == 'host') {
            $objClass = "CentreonConfiguration\Repository\\".ucfirst($params['object']."Repository");
            
            if (isset($params['extraParams']['host_name'])) {
                $sLabel = $params['extraParams']['host_name'];
            }

            $aParams['host'] = $sLabel;
            try {
                $idReturned = $objClass::getIdFromUnicity($aParams);
                $iObjectId = '';
                if (isset($params['extraParams']['object_id']) && !empty($params['extraParams']['object_id'])) {
                    $iObjectId = $params['extraParams']['object_id'];
                }
                $return[] = self::compareResponse($iObjectId, $idReturned);               
            } catch (MissingParameterException $e) {
                $return[] = 0;
            }
        } elseif (isset($params['object']) && $params['object'] == 'hosttemplate') {
            $objClass = "CentreonConfiguration\Repository\HostTemplateRepository";
            
            if (isset($params['extraParams']['host_name'])) {
                $sLabel = $params['extraParams']['host_name'];
            }

            $aParams['hosttemplate'] = $sLabel;
            try {
                $idReturned = $objClass::getIdFromUnicity($aParams);
                $iObjectId = '';
                
                if (isset($params['extraParams']['object_id']) && !empty($params['extraParams']['object_id'])) {
                    $iObjectId = $params['extraParams']['object_id'];
                }
                $return[] = self::compareResponse($iObjectId, $idReturned); 
                
            } catch (MissingParameterException $e) {
                $return[] = 0;
            }
        } elseif (isset($params['object']) && $params['object'] == 'command') {
            $objClass = "CentreonConfiguration\Repository\\".ucfirst($params['object']."Repository");
            
            if (isset($params['extraParams']['command_name'])) {
                $sLabel = $params['extraParams']['command_name'];
            }

            $aParams['command'] = $sLabel;
            try {
                $idReturned = $objClass::getIdFromUnicity($aParams);
                $iObjectId = '';
                
                if (isset($params['extraParams']['object_id']) && !empty($params['extraParams']['object_id'])) {
                    $iObjectId = $params['extraParams']['object_id'];
                }
                $return[] = self::compareResponse($iObjectId, $idReturned);
                
            } catch (MissingParameterException $e) {
                $return[] = 0;
            }
        } elseif (isset($params['object']) && $params['object'] == 'contact') {
            $objClass = "CentreonAdministration\Repository\\".ucfirst($params['object']."Repository");
            if (isset($params['extraParams']['description'])) {
                $sLabel = $params['extraParams']['description'];
            }

            $aParams['contact'] = $sLabel;
            try {
                $idReturned = $objClass::getIdFromUnicity($aParams);
                $iObjectId = '';
                
                if (isset($params['extraParams']['object_id']) && !empty($params['extraParams']['object_id'])) {
                    $iObjectId = $params['extraParams']['object_id'];
                }
                $return[] = self::compareResponse($iObjectId, $idReturned);
            } catch (MissingParameterException $e) {
                $return[] = 0;
            }
        } elseif (isset($params['object']) && $params['object'] == 'user') {
            $objClass = "CentreonAdministration\Repository\\".ucfirst($params['object']."Repository");
            
            if (isset($params['extraParams']['login'])) {
                $sLabel = $params['extraParams']['login'];
            }

            $aParams['user'] = $sLabel;
            
            try {
                $idReturned = $objClass::getIdFromUnicity($aParams);
                $iObjectId = '';
                
                if (isset($params['extraParams']['object_id']) && !empty($params['extraParams']['object_id'])) {
                    $iObjectId = $params['extraParams']['object_id'];
                }
                $return[] = self::compareResponse($iObjectId, $idReturned);
                
            } catch (MissingParameterException $e) {
                $return[] = 0;
            }
        } elseif (isset($params['object']) && $params['object'] == 'businessactivity') {
            $objClass = "CentreonBam\Repository\\".ucfirst($params['object']."Repository");
            
            if (isset($params['extraParams']['name'])) {
                $sLabel = $params['extraParams']['name'];
            }

            $aParams['bam'] = $sLabel;
            
            try {
                $idReturned = $objClass::getIdFromUnicity($aParams);
                $iObjectId = '';
                
                if (isset($params['extraParams']['object_id']) && !empty($params['extraParams']['object_id'])) {
                    $iObjectId = $params['extraParams']['object_id'];
                }
                $return[] = self::compareResponse($iObjectId, $idReturned);
                
            } catch (MissingParameterException $e) {
                $return[] = 0;
            }
        } elseif (isset($params['object']) && $params['object'] == 'connector') {
            $objClass = "CentreonConfiguration\Repository\\".ucfirst($params['object']."Repository");
            
            if (isset($params['extraParams']['name'])) {
                $sLabel = $params['extraParams']['name'];
            }

            $aParams['connector'] = $sLabel;
            try {
                $idReturned = $objClass::getIdFromUnicity($aParams);
                $iObjectId = '';
                
                if (isset($params['extraParams']['object_id']) && !empty($params['extraParams']['object_id'])) {
                    $iObjectId = $params['extraParams']['object_id'];
                }
                $return[] = self::compareResponse($iObjectId, $idReturned);
                
            } catch (MissingParameterException $e) {
                $return[] = 0;
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
            $reponse = $bSuccess;
        } else {
            $reponse = array('success' => $bSuccess, 'error' => $sMessage);
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
        //echo "<>".$iObjectId."<>".$iIdReturned."<>".$iRetour;
        return $iRetour;
    }
}
