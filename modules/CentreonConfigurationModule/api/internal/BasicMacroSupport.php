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

namespace CentreonConfiguration\Api\Internal;

use Centreon\Api\Internal\BasicCrudCommand;
use CentreonConfiguration\Repository\CustomMacroRepository;


/**
 * Description of BasicMacroSupport
 *
 * @author bsauveton
 */
class BasicMacroSupport extends BasicCrudCommand
{

    /**
     * 
     * @param string $object
     * @param array $params
    */
    public function addMacroAction($object, $params)
    {
        
        $paramList = $this->parseObjectParams($params);
        
        if(!isset($paramList['hidden'])){
            $paramList['hidden'] = 0;
        }
        
        
        if(isset($paramList['name']) && isset($paramList['value'])){
            $formatedParams = array(
                            $paramList['name'] => 
                                array(
                                    'value' => $paramList['value'],
                                    'is_password' => $paramList['hidden']
                                )
                            );
        }
        
        
        try {
            $repository = $this->repository;
            $objectId = $repository::getIdFromUnicity($this->parseObjectParams($object));
            
            switch($this->objectName){
                case 'host' :
                    CustomMacroRepository::saveHostCustomMacro($objectId, $formatedParams, false);
                    break;
                case 'service' : 
                    CustomMacroRepository::saveServiceCustomMacro($objectId, $formatedParams, false);
                    break;
                default :
                    break;
            }
            \Centreon\Internal\Utils\CommandLine\InputOutput::display(
                "The macro '".$paramList['name']."' has been successfully added to the object",
                true,
                'green'
            );
        } catch(\Exception $ex) {
            \Centreon\Internal\Utils\CommandLine\InputOutput::display($ex->getMessage(), true, 'red');
        }
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
        try {
            $repository = $this->repository;
            $objectId = $repository::getIdFromUnicity($this->parseObjectParams($object));
            switch($this->objectName){
                case 'host' :
                    CustomMacroRepository::updateHostCustomMacro($objectId,$macro,$paramList);
                    break;
                case 'service' : 
                    CustomMacroRepository::updateServiceCustomMacro($objectId,$macro,$paramList);
                    break;
                default :
                    break;
            }
            \Centreon\Internal\Utils\CommandLine\InputOutput::display(
                "The macro '".$macro."' has been successfully updated",
                true,
                'green'
            );

        } catch (\Exception $ex) {
            \Centreon\Internal\Utils\CommandLine\InputOutput::display($ex->getMessage(), true, 'red');
        }
        
    }
    

    /**
     * 
     * @param string $object
     */
    public function listMacroAction($object = null)
    {
        try {
            $repository = $this->repository;
            $objectId = $repository::getIdFromUnicity($this->parseObjectParams($object));
            $macros = array();
            switch($this->objectName){
                case 'host' :
                    $macros = CustomMacroRepository::loadHostCustomMacro($objectId);
                    break;
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
                \Centreon\Internal\Utils\CommandLine\InputOutput::display('No results', true, 'red');
            }
            
            foreach ($macros as $macro) {
                echo "\n".$macro['macro_name'] . ";" . $macro['macro_value'] . ";" . $macro['macro_hidden'];
            }
        } catch (\Exception $ex) {
            \Centreon\Internal\Utils\CommandLine\InputOutput::display($ex->getMessage(), true, 'red');
        }
    }
    
    
    
    /**
     * 
     * @param string $object
     * @param string $macro
     */
    public function removeMacroAction($object, $macro)
    {
        try {
            $repository = $this->repository;
            $objectId = $repository::getIdFromUnicity($this->parseObjectParams($object));
            switch($this->objectName){
                case 'host' :
                    CustomMacroRepository::deleteHostCustomMacro($objectId,$macro);
                    break;
                case 'service' : 
                    CustomMacroRepository::deleteServiceCustomMacro($objectId,$macro);
                    break;
                default :
                    break;
            }
            \Centreon\Internal\Utils\CommandLine\InputOutput::display(
                "The macro '".$macro."' has been successfully removed from the object",
                true,
                'green'
            );
        } catch (\Exception $ex) {
            \Centreon\Internal\Utils\CommandLine\InputOutput::display($ex->getMessage(), true, 'red');
        }
    }
    
    
    //put your code here
}
