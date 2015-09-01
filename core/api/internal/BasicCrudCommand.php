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

namespace Centreon\Api\Internal;

use Centreon\Api\Internal\BasicCrud;

/**
 * Description of BasicCrudCommand
 *
 * @author lionel
 */
class BasicCrudCommand extends BasicCrud
{
    /**
     * 
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 
     * @param type $fields
     * @param type $count
     * @param type $offset
     */
    public function listAction($fields = null, $count = -1, $offset = 0)
    {
        $objectList = parent::listAction($fields, $count, $offset);
        
        // Displaying
        if (count($objectList) > 0) {
            $selectedFields = array_keys($objectList[0]);
            $result = implode(';', $selectedFields) . "\n";
            foreach ($objectList as $object) {
                $result .= implode(';', $object) . "\n";
            }
        } else {
            $result = _("No result found");
        }
        
        echo $result;
    }
    
    /**
     * 
     * @param type $object
     * @param type $fields
     * @param type $linkedObject
     */
    public function showAction($object, $fields = null, $linkedObject = '')
    {
        $myObject = parent::showAction($object, $fields, $linkedObject);
        
        $result = '';
        foreach ($myObject as $key => $value) {
            $result .= $key . ': ' . $value . "\n";
        }
        
        echo $result;
    }
    
    /**
     * Action for add
     * 
     * @param string $params
     */
    public function createAction($params)
    {
        parent::createAction($params);
    }
    
    /**
     * Action for update
     * 
     * @param string $object
     * @param string $params
     */
    public function updateAction($object, $params)
    {
        parent::updateAction($object, $params);
    }

    /**
     * Action for delete
     * 
     * @param type $object
     */
    public function deleteAction($object)
    {
        parent::deleteAction($object);
    }
    
    /**
     * 
     */
    public function duplicateAction()
    {
        parent::duplicateAction();
    }
    
    /**
     * 
     * @cmdObject string $object-name the name of the object
     */
    public function getSlugAction($object){
        
        $repository = $this->repository;
        $slug = $repository::getSlugByUniqueField($object);
        \Centreon\Internal\Utils\CommandLine\InputOutput::display($slug, true, 'green');
        
    }
    
    
    
    private function echoTable($inputTable,$tableInfos){
        $cnt = 0;
        foreach($tableInfos as $infos){
            echo '-+-';
            for($i = 0; $i < $infos['size']; $i++){
                echo '-';
            }
            $cnt = $cnt + 1;    
        }
        echo '-+';
        echo "\n";
        $cnt = 0;
        foreach($tableInfos as $infos){
            echo ' | ';
            echo $infos['header'];
            for($i = (strlen($infos['header']) + 1); $i <= $infos['size']; $i++){
                echo ' ';
            }
            $cnt = $cnt + 1;    
        }
        echo ' |';
        echo "\n";
        foreach($inputTable as $inputLines){
            $cnt = 0;
            foreach($tableInfos as $infos){
                echo '-+-';
                for($i = 0; $i < $infos['size']; $i++){
                    echo '-';
                }
                $cnt = $cnt + 1;    
            }
            echo '-+';
            echo "\n";
            $cnt = 0;
            foreach($inputLines as $inputField){
                echo ' | ';
                echo  $inputField;
                for($i = (strlen($inputField) + 1); $i <= $tableInfos[$cnt]['size']; $i++){
                    echo ' ';
                }
                $cnt = $cnt + 1;
            }
            echo ' |';
            echo "\n";
        }
        $cnt = 0;
        foreach($tableInfos as $infos){
            echo '-+-';
            for($i = 0; $i < $infos['size']; $i++){
                echo '-';
            }
            $cnt = $cnt + 1;    
        }
        echo '-+';
        echo "\n";
        
        
    }
    
    
    /**
     * Display result of a command as table in command line
     * 
     * @param array $headers
     * @param array $inputTable
     */
    public function tableDisplay($headers, $inputTable){

        $cnt = 0;
        foreach($headers as $header){
            $tableInfos[$cnt]['size'] = strlen($header);
            $tableInfos[$cnt]['header'] = $header;
            $cnt = $cnt + 1;
        }
        
        foreach($inputTable as $inputLines){
            $cnt = 0;
            foreach($inputLines as $keys=>$inputField){
                if(array_key_exists($keys,$headers)){
                    $lenght = strlen($inputField);
                    if($tableInfos[$cnt]['size'] < $lenght){
                        $tableInfos[$cnt]['size'] = $lenght;
                    }
                    $cnt = $cnt + 1;    
                }
            }
        }

        $this->echoTable($inputTable,$tableInfos);
        
    }

    
}
