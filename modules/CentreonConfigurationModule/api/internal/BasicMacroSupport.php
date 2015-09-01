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

namespace CentreonConfiguration\Api\Internal;

use Centreon\Api\Internal\BasicCrudCommand;
use CentreonConfiguration\Repository\CustomMacroRepository;
use Centreon\Internal\Utils\CommandLine\InputOutput;

/**
 * Description of BasicMacroSupport
 *
 * @author bsauveton
 */
class BasicMacroSupport extends BasicCrudCommand
{
    
    public static $attributesMapHost = array(
        "name" => "host_macro_name",
        "value" => "host_macro_value",
        "hidden" => "is_password"
    );
    
    public static $attributesMapService = array(
        "name" => "svc_macro_name",
        "value" => "svc_macro_value",
        "hidden" => "is_password"
    );

    /**
     * 
     * @param string $object
     * @param array $params
    */
    public function addMacroAction($object, $params)
    {       
      
        $paramList = $this->parseObjectParams($params);        
        //try {
            $repository = $this->repository;
            $sName = $this->objectName;
            $repository::transco($object);
            $aId = $repository::getListBySlugName($object[$sName]);
            if (count($aId) > 0) {
                $objectId = $aId[0]['id'];
            } else {
                throw new \Exception(static::OBJ_NOT_EXIST, 1);
            }
            switch($this->objectName){
                case 'hosttemplate' : 
                case 'host' :
                CustomMacroRepository::setAttributesMap(self::$attributesMapHost);
                CustomMacroRepository::transco($paramList);
                    if(isset($paramList['host_macro_name']) && isset($paramList['host_macro_value'])){
                        $formatedParams = array(
                            $paramList['host_macro_name'] => 
                                array(
                                    'value' => $paramList['host_macro_value'],
                                    'is_password' => $paramList['is_password']
                            )
                        );
                    }
                    CustomMacroRepository::saveHostCustomMacro($this->objectName, $objectId, $formatedParams, false);
                    InputOutput::display(
                        "The macro '".$paramList['host_macro_name']."' has been successfully added to the object",
                        true,
                        'green'
                    );
                    break;
                case 'servicetemplate' : 
                    CustomMacroRepository::setAttributesMap(self::$attributesMapService);
                    CustomMacroRepository::transco($paramList);
                    if(isset($paramList['svc_macro_name']) && isset($paramList['svc_macro_value'])){
                        $formatedParams = array(
                            $paramList['svc_macro_name'] => 
                                array(
                                    'value' => $paramList['svc_macro_value'],
                                    'is_password' => $paramList['is_password']
                            )
                        );
                    }
                    CustomMacroRepository::saveServiceCustomMacro($this->objectName, $objectId, $formatedParams, false);
                    InputOutput::display(
                        "The macro '".$paramList['svc_macro_name']."' has been successfully added to the object",
                        true,
                        'green'
                    );
                    break;
                case 'service' :
                    CustomMacroRepository::setAttributesMap(self::$attributesMapService);
                    CustomMacroRepository::transco($paramList);
                    $aData = \CentreonConfiguration\Repository\ServiceRepository::getServiceBySlugs($object['service']);

                    if (count($aData) == 0) {
                        throw new \Exception(static::OBJ_NOT_EXIST, 1);
                    } else {
                        $objectId = $aData['service_id'];
                        $hostId   = $aData['host_id'];
                    }

                    if (isset($paramList['svc_macro_name']) && isset($paramList['svc_macro_value'])) {
                        $formatedParams = array(
                            $paramList['svc_macro_name'] => 
                                array(
                                    'value' => $paramList['svc_macro_value'],
                                    'is_password' => $paramList['is_password']
                            )
                        );
                    }
                    
                    CustomMacroRepository::saveServiceCustomMacro($this->objectName, $objectId, $formatedParams, false, $hostId);
                    InputOutput::display(
                        "The macro '".$paramList['svc_macro_name']."' has been successfully added to the object",
                        true,
                        'green'
                    );
                    break;
                default :
                    break;
            }
            
        /*} catch(\Exception $ex) {
            InputOutput::display($ex->getMessage(), true, 'red');
        }*/
    }
    
    /**
     * 
     * @param string $object
     * @param string $macro
     * @param array $params
     */
    public function updateMacroAction($object, $macro, $params)
    {
        $paramList = $this->parseObjectParams($params);
                
       // try {
            $repository = $this->repository;
            //$objectId = $repository::getIdFromUnicity($this->parseObjectParams($object));
            $sName = $this->objectName;
            $repository::transco($object);
           
            $aId = $repository::getListBySlugName($object[$sName]);
            if (count($aId) > 0) {
                $objectId = $aId[0]['id'];
            } else {
                throw new \Exception(static::OBJ_NOT_EXIST, 1);
            }
            switch($this->objectName){
                case 'hosttemplate' : 
                case 'host' :
                    CustomMacroRepository::setAttributesMap(self::$attributesMapHost);
                    CustomMacroRepository::transco($paramList);
                    CustomMacroRepository::updateHostCustomMacro($objectId,$macro,$paramList);
                    break;
                case 'servicetemplate' :
                case 'service' : 
                    CustomMacroRepository::setAttributesMap(self::$attributesMapService);
                    CustomMacroRepository::transco($paramList);
                    CustomMacroRepository::updateServiceCustomMacro($objectId,$macro,$paramList);
                    break;
                default :
                    break;
            }
            InputOutput::display(
                "The macro '".$macro."' has been successfully updated",
                true,
                'green'
            );

       /* } catch (\Exception $ex) {
            InputOutput::display($ex->getMessage(), true, 'red');
        }*/
        
    }
    

    /**
     * 
     * @param string $object
     */
    public function listMacroAction($object = null)
    {
        //try {
            $repository = $this->repository;
            //$objectId = $repository::getIdFromUnicity($this->parseObjectParams($object));
            $sName = $this->objectName;
            $repository::transco($object);
            $aId = $repository::getListBySlugName($object[$sName]);
            if (count($aId) > 0) {
                $objectId = $aId[0]['id'];
            } else {
                throw new \Exception(static::OBJ_NOT_EXIST, 1);
            }
            $macros = array();
            switch($this->objectName){
                case 'hosttemplate' : 
                case 'host' :
                    $macros = CustomMacroRepository::loadHostCustomMacro($objectId);
                    break;
                case 'servicetemplate' :
                case 'service' : 
                    $macros = CustomMacroRepository::loadServiceCustomMacro($objectId);
                    break;
                default :
                    break;
            }

            //$this->tableDisplay(array('macro_name'=>'name','macro_value'=>'value','macro_hidden'=>'hidden'),$macros);
            
            if(!empty($macros)){
                echo "macro_name;macro_value;macro_hidden";
            }else{
                //InputOutput::display('No results', true, 'red');
                throw new \Exception('No results', 1);
            }
            
            foreach ($macros as $macro) {
                echo "\n".$macro['macro_name'] . ";" . $macro['macro_value'] . ";" . $macro['macro_hidden'];
            }
        /*} catch (\Exception $ex) {
            InputOutput::display($ex->getMessage(), true, 'red');
        }*/
    }
    
    
    
    /**
     * 
     * @param string $object
     * @param string $macro
     */
    public function removeMacroAction($object, $macro)
    {
        //try {
            $repository = $this->repository;
            //$objectId = $repository::getIdFromUnicity($this->parseObjectParams($object));
            $sName = $this->objectName;
            $repository::transco($object);
           
            $aId = $repository::getListBySlugName($object[$sName]);
            if (count($aId) > 0) {
                $objectId = $aId[0]['id'];
            } else {
                throw new \Exception(static::OBJ_NOT_EXIST, 1);
            }
            switch($this->objectName){
                case 'hosttemplate' : 
                case 'host' :
                    CustomMacroRepository::setAttributesMap(self::$attributesMapHost);
                    CustomMacroRepository::transco($macro);
                    CustomMacroRepository::deleteHostCustomMacro($objectId,$macro);
                    break;
                case 'servicetemplate' :
                case 'service' : 
                    CustomMacroRepository::setAttributesMap(self::$attributesMapService);
                    CustomMacroRepository::transco($macro);
                    CustomMacroRepository::deleteServiceCustomMacro($objectId,$macro);
                    break;
                default :
                    break;
            }
            InputOutput::display(
                "The macro '".$macro."' has been successfully removed from the object",
                true,
                'green'
            );
       /* } catch (\Exception $ex) {
            InputOutput::display($ex->getMessage(), true, 'red');
        }*/
    }
}
