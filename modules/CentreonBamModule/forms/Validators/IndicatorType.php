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
 */

namespace CentreonBam\Forms\Validators;
use Centreon\Internal\Form\Validators\ValidatorInterface;



/**
 * Description of IndicatorType
 *
 * @author bsauveton
 */
class IndicatorType implements ValidatorInterface
{

    private function baFieldsCheck($params)
    {    
        
        $errorMessage = "";
        
        if(empty($params['extraParams']['id_indicator_ba'])){
            $errorMessage .= "\n".'--indicator-ba-slug required';
        }
        
        if(empty($errorMessage)){
            return false;
        }else{
            return $errorMessage;
        }
    }
    
    private function serviceFieldsCheck($params){
        
        $errorMessage = "";

        if(empty($params['extraParams']['service_id'])){
            $errorMessage .= "\n".'--service-slug required';
        }
        /*
        if(empty($params['extraParams']['host_id'])){
            $errorMessage .= "\n".'--host-slug required';
        }*/
        
        if(empty($errorMessage)){
            return false;
        }else{
            return $errorMessage;
        }
    }
    
    private function booleanFieldsCheck($params){
        
        $errorMessage = "";
        
        if(empty($params['extraParams']['boolean_name'])){
            $errorMessage .= "\n".'--boolean-slug required';
        }
        
        if(empty($params['extraParams']['boolean_expression'])){
            $errorMessage .= "\n".'--boolean-expression required';
        }
        
        if(empty($params['extraParams']['bool_state'])){
            $errorMessage .= "\n".'--bool-state required';
        }

        if(empty($errorMessage)){
            return false;
        }else{
            return $errorMessage;
        }
        
    }
    
    /**
     * 
     * @param type $value
     * @param array $params
     * @return boolean
     */
    public function validate($value, $params = array(), $sContext = 'server')
    {
        $bSuccess = false;
        $sMessage = "";
        
        if(isset($params['extraParams']['kpi_type'])){
            switch ($params['extraParams']['kpi_type']){
                case "0" :
                    $error = $this->serviceFieldsCheck($params);
                    if(!$error){
                       $bSuccess = true;
                    }else{
                        $bSuccess = false;
                        $sMessage = 'Wrong params for Service indicator :  '.$error;
                    }
                    break;
                case "1" :
                    $bSuccess = false;
                    $sMessage = 'MetaService not implemented yet';
                    break;
                case "2" : 
                    $error = $this->baFieldsCheck($params);
                    if(!$error){
                       $bSuccess = true;
                    }else{
                        $bSuccess = false;
                        $sMessage = 'Wrong params for Ba indicator'.$error;
                    }
                    break;
                case "3" :
                    $error = $this->booleanFieldsCheck($params);
                    if(!$error){
                       $bSuccess = true;
                    }else{
                        $bSuccess = false;
                        $sMessage = 'Wrong params for Service indicator'.$error;
                    }
                    break;
                default : 
                    $bSuccess = false;
                    $sMessage = 'Wrong params "'.$params['extraParams']['kpi_type'].'" for ba type, must be : BA, service or boolean';
                    break;
            }
        }else{

        }
        
        
        $reponse = array('success' => $bSuccess, 'error' => $sMessage);
        return $reponse;
    }

    //put your code here
}
