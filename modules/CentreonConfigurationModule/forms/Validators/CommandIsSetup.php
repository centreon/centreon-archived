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

/**
 * Description of CommandIsSetup
 *
 * @author bsauveton
 */

namespace CentreonConfiguration\Forms\Validators;
use Centreon\Internal\Form\Validators\ValidatorInterface;
use CentreonConfiguration\Repository\HostRepository;
use CentreonConfiguration\Models\Hosttemplate;

class CommandIsSetup implements ValidatorInterface
{
    /**
     * 
     */
    public function validate($value, $params = array(), $sContext = 'server')
    {
        if(isset($params['extraParams']['host_active_checks_enabled']) && $params['extraParams']['host_active_checks_enabled'] == "0"){
            $reponse = array('success' => true, 'error' => '');
            return $reponse;
        }
        
        if(!isset($value) || $value === " " || $value == ""){
            // case : UI or API with host-templates params
            if(isset($params['extraParams']['host_hosttemplates']) && $params['extraParams']['host_hosttemplates'] != " " ){
                $tplIds = explode(',',$params['extraParams']['host_hosttemplates']);
                foreach($tplIds as $tplId){
                    if(!empty($tplId)){
                        $template = Hosttemplate::get($tplId);
                        
                        $childTemplates = HostRepository::getTemplateChain($tplId, array(), -1, true);
                        if(!empty($template['command_command_id'])){
                            $reponse = array('success' => true, 'error' => '');
                            return $reponse;
                        }
                        foreach($childTemplates as $childTemplate){
                            if(!empty($childTemplate['command_command_id'])){
                                $reponse = array('success' => true, 'error' => '');
                                return $reponse;
                            }
                        }
                    }
                }
            }else if(isset($params['object_id'])){
                // case : Only UI without host-templates params
                $childTemplates = HostRepository::getTemplateChain($params['object_id'], array(), -1, true);
                foreach($childTemplates as $childTemplate){
                    if(!empty($childTemplate['command_command_id']) || $childTemplate['command_command_id'] == "0"){
                        $reponse = array('success' => true, 'error' => '');
                        return $reponse;
                    }
                }
            }
            
            $reponse = array('success' => false, 'error' => 'No check command set on the host and its templates');
        }else{
            $reponse = array('success' => true, 'error' => '');
        }
        return $reponse;
    }
}
