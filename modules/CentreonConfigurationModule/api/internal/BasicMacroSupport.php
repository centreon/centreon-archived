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
     * @param string $macro
    */
    public function addMacroAction($object, $macro)
    {
        
        $paramList = $this->parseObjectParams($macro);
        
        if(isset($paramList['name']) && isset($paramList['value']) && isset($paramList['ispassword'])){
            $formatedParams = array(
                            $paramList['name'] => 
                                array(
                                    'value' => $paramList['value'],
                                    'ispassword' => $paramList['ispassword']
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
                "The tag has been successfully added to the object",
                true,
                'green'
            );
        } catch(\Exception $ex) {
            \Centreon\Internal\Utils\CommandLine\InputOutput::display($ex->getMessage(), true, 'red');
        }
    }
    
    
    public function updateMacroAction($object, $macro){
        /*$paramList = $this->parseObjectParams($macro);
        
        try {
            $repository = $this->repository;
            $objectId = $repository::getIdFromUnicity($this->parseObjectParams($object));
            //$macros = array();
            switch($this->objectName){
                case 'host' :
                    CustomMacroRepository::updateHostCustomMacro($objectId,$paramList);
                    //$macros = CustomMacroRepository::loadHostCustomMacro($objectId);
                    break;
                case 'service' : 
                    CustomMacroRepository::updateServiceCustomMacro($objectId,$paramList);
                    //$macros = CustomMacroRepository::loadServiceCustomMacro($objectId);
                    break;
                default :
                    break;
            }

        } catch (\Exception $ex) {
            \Centreon\Internal\Utils\CommandLine\InputOutput::display($ex->getMessage(), true, 'red');
        }
        
        */
        
        
        
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
            
            echo "macro_name;macro_value;macro_hidden";
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
     * @param string $tag
     */
    public function removeMacroAction($object, $macro)
    {
        $paramList = $this->parseObjectParams($macro);

        try {
            $repository = $this->repository;
            $objectId = $repository::getIdFromUnicity($this->parseObjectParams($object));
            switch($this->objectName){
                case 'host' :
                    CustomMacroRepository::deleteHostCustomMacro($objectId,$paramList['name']);
                    break;
                case 'service' : 
                    CustomMacroRepository::deleteServiceCustomMacro($objectId,$paramList['name']);
                    break;
                default :
                    break;
            }
            \Centreon\Internal\Utils\CommandLine\InputOutput::display(
                "The tag has been successfully removed from the object",
                true,
                'green'
            );
        } catch (\Exception $ex) {
            \Centreon\Internal\Utils\CommandLine\InputOutput::display($ex->getMessage(), true, 'red');
        }
    }
    
    
    //put your code here
}
