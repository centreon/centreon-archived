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

namespace CentreonMain\Forms\Validators;

use Centreon\Internal\Form\Validators\ValidatorInterface;
use CentreonConfiguration\Repository\HostRepository;
use CentreonConfiguration\Repository\ServiceRepository;

/**
 * @author Kevin Duret <kduret@centreon.com>
 * @package Centreon
 * @subpackage Core
 */
class CircularDependency implements ValidatorInterface
{
    /**
     * 
     */
    public function validate($value, $params = array(), $sContext = 'server')
    {
        $result = true;
        $resultError = _("Circular redundancy detected");

        if ((isset($params['object'])) && (($params['object'] === 'host') || ($params['object'] === 'hosttemplate'))) {
            $objectStack = explode(',', trim($value));
            foreach ($objectStack as $hostId) {
                if (isset($params['object_id']) && $hostId == $params['object_id']) {
                    $result = false;
                }
                $listHostId = HostRepository::getTemplateChain($hostId, array(), -1);
                foreach($listHostId as $hostTemplateId) {
                    if (isset($params['object_id']) && ($hostTemplateId['id'] == $params['object_id'])) {
                        $result = false;
                    }
                }
            }
        } else if ((isset($params['object'])) && (($params['object'] === 'service') || ($params['object'] === 'servicetemplate'))) {
            $serviceId = $value;
            $listServiceId = ServiceRepository::getListTemplates($serviceId);
            if (isset($params['object_id']) && $serviceId == $params['object_id']) {
                $result = false;
            }
            foreach($listServiceId as $serviceTemplateId) {
                if (isset($params['object_id']) && ($serviceTemplateId == $params['object_id'])) {
                    $result = false;
                }
            }
        }
        
        if ($sContext == 'client') {
            $reponse = $result;
        } else {
            $reponse = array('success' => $result, 'error' => $resultError);
        }
        return $reponse;
    }
}
