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
               
        
        //echo "obj".$params['object'];
        //var_dump($params);die;

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

            //echo "rrr => ".$objClass;die;
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
//        var_dump($return); die;
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
