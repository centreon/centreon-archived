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
        
        
        /*
        if(!empty($params['extraParams']['boolean_name'])){
            $errorMessage .= "\n".'--boolean-name not allowed';
        }
        
        if(!empty($params['extraParams']['boolean_expression'])){
            $errorMessage .= "\n".'--boolean-expression not allowed';
        }
        
        if(!empty($params['extraParams']['bool_state'])){
            $errorMessage .= "\n".'--bool-state not allowed';
        }

        if(!empty($params['extraParams']['service_id'])){
            $errorMessage .= "\n".'--service-id not allowed';
        }*/
        
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
        if(!empty($params['extraParams']['boolean_name'])){
            $errorMessage .= "\n".'--boolean-name not allowed';
        }
        
        if(!empty($params['extraParams']['boolean_expression'])){
            $errorMessage .= "\n".'--boolean-expression not allowed';
        }
        
        if(!empty($params['extraParams']['bool_state'])){
            $errorMessage .= "\n".'--bool-state not allowed';
        }
        
        if(!empty($params['extraParams']['id_indicator_ba'])){
            $errorMessage .= "\n".'--id-indicator-ba not allowed';
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
        /*
        if(!empty($params['extraParams']['service_id'])){
            $errorMessage .= "\n".'--service-id not allowed';
        }
        
        if(!empty($params['extraParams']['id_indicator_ba'])){
            $errorMessage .= "\n".'--id-indicator-ba not allowed';
        }
        */
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
                    break;
            }
            
        }
        
        
        $reponse = array('success' => $bSuccess, 'error' => $sMessage);
        return $reponse;
    }
    
    
    
    //put your code here
}
