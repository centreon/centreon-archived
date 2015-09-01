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

namespace Centreon\Internal\Command;

use Centreon\Internal\Di;

abstract class AbstractCommand
{
    /**
     *
     * @var type 
     */
    protected $db;
    
    /**
     *
     * @var type 
     */
    protected $di;
    
    /**
     *
     * @var string 
     */
    public static $moduleName = 'Core';

    /**
     * 
     */
    public function __construct()
    {
        $this->di = Di::getDefault();
        $this->db = $this->di->get('db_centreon');
    }
    
    /**
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function named($methodReflection, array $pass)
    {
        $methodReflection->invokeArgs($this, $pass);
    }
    
    /**
     * 
     * @param type $paramsArray
     * @param type $globalOptional
     */
    public function getCustomsParams($paramsArray,$globalOptional = false)
    {
        foreach($paramsArray as $param){
            $multiple = false;
            $typeInfos = explode('|',$param['paramType']);
            
            $paramName = $param['paramName'];

            $type = 'string';
            $defaultValue = null;
            $booleanValue = null;
            $booleanSetDefault = null;
            $isNotNone = true;
            if (!empty($typeInfos[0])) {
                $hasDefault = false;
                if ($typeInfos[0] == 'none') {
                    unset($this->options[$paramName]);
                    $isNotNone = false;
                } else if($typeInfos[0] == 'Array') {
                    $multiple = true;
                } else {
                   $type = $typeInfos[0]; 
                }
                if ($typeInfos[0] === 'boolean') {
                    if (!empty($typeInfos[1])) {
                        $booleanValue = ($typeInfos[1] == 'true') ? true : false;
                        if ($param['paramRequired']) {
                            $booleanSetDefault = true;
                        }
                    }
                } else if(!empty($typeInfos[1])) {
                    $defaultValue = $typeInfos[1];
                }
            }
            
            if($isNotNone){
                $this->options[$paramName] = array(
                    'paramType' => 'params',
                    'help' => $param['paramComment'],
                    'type' => $type,
                    'multiple' => $multiple,
                    'required' => ($globalOptional) ? false : $param['paramRequired']
                );

                if (!is_null($defaultValue)) {
                    $this->options[$paramName]['defaultValue'] = $defaultValue;
                }

                if (!is_null($booleanValue)) {
                    $this->options[$paramName]['booleanValue'] = $booleanValue;
                }

                if (!is_null($booleanSetDefault)) {
                    $this->options[$paramName]['booleanSetDefault'] = $booleanSetDefault;
                }
            }
        }
    }
    
    /**
     * 
     * @param type $objectArray
     * @param type $globalOptional
     */
    public function getObject($objectArray, $globalOptional = false)
    {
        $required = true;
        if ($globalOptional) {
            $required = false;
        }
        foreach($objectArray as $object){
            $typeInfos = explode('|',$object['objectType']);
            $type = 'string';
            $multiple = false;
            if(!empty($this->objectName)){
                $object['objectName'] = str_replace('$object',$this->objectName,$object['objectName']);
            }
            $objectName = $object['objectName'];
            if ($typeInfos[0] == 'Array') {
                $multiple = true;
            } else {
                $type = $typeInfos[0]; 
            }
            $this->options[$objectName] = array(
                'paramType' => 'object',
                'help' => $object['objectComment'],
                'type' => $type,
                'multiple' => $multiple,
                'required' => $required
            );
        }
    }
    
    
}
