<?php

/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace Centreon\Internal;

class Command
{
    private $requestLine;
    private $parametersLine;
    private $commandList;
    
    /**
     * 
     * @param string $requestLine
     * @param string $parametersLine
     */
    public function __construct($requestLine, $parametersLine)
    {
        $this->requestLine = $requestLine;
        $this->parametersLine = $parametersLine;
        $modulesToParse = array();
        foreach (glob(__DIR__."/../../modules/*Module") as $moduleTemplateDir) {
            $modulesToParse[] = basename($moduleTemplateDir);
        }
        $this->parseCommand($modulesToParse);
    }
    
    /**
     * 
     */
    public function authenticate($username, $password = "")
    {
        echo "Authentication not implemented yet\n";
    }
    
    /**
     * 
     */
    public function getHelp()
    {
        echo "Help not yet implemented\n";
    }
    
    /**
     * 
     * @throws Exception
     */
    public function executeRequest()
    {
        $requestLineExploded = explode(':', $this->requestLine);
        $module = $requestLineExploded[0];
        $object = ucfirst($requestLineExploded[1] . 'Command');
        $action = $requestLineExploded[2] . 'Action';
        
        if (strtolower($module) != 'core') {
            if (!\Centreon\Custom\Module\ModuleInformations::isModuleReachable($module)) {
                throw new Exception("The module doesn't exist");
            }
        }
        
        if (!isset($this->commandList[$object])) {
            throw new Exception("The object doesn't exist");
        }
        
        $aliveObject = new $this->commandList[$object]();
        
        if (!method_exists($aliveObject, $action)) {
            throw new Exception("The action '$action' doesn't exist");
        }
        
        $actionArgs = array();
        if (!is_null($this->parametersLine)) {
            $this->getArgs($actionArgs, $aliveObject, $action);
        }
        
        // Call the action
        $aliveObject->named($action, $actionArgs);
        
        echo "\n";
    }
    
    /**
     * 
     * @param type $aliveObject
     * @param type $action
     */
    private function getArgs(array &$argsList, $aliveObject, $action)
    {
        //$this->parseAction($aliveObject, $action);
        
        $rawRistOfArgs = explode(':', $this->parametersLine);
        
        foreach ($rawRistOfArgs as $rawArgs) {
            $currentArgsValue = explode('=', $rawArgs);
            $argsList[$currentArgsValue[0]] = $currentArgsValue[1];
        }
    }
    
    /**
     * 
     * @param type $object
     * @param type $method
     */
    public function parseAction($object, $method)
    {
        $classReflection = new \ReflectionClass($object);
        $methodReflection = $classReflection->getMethod($method);
        $docComment = $methodReflection->getDocComment();
        
        preg_match_all('/@param\s+([A-z]+)\s+(\$[A-z]+)(.*)/', $docComment, $matches);
        
        $paramList = array();
        $nbElement = count($matches) - 1;
        for ($i=0; $i<$nbElement; $i++) {
            $pDescription = "";
            $pName = str_replace('$', '', $matches[2][$i]);
            $pType = $matches[1][$i];
            if (isset($matches[3][$i])) {
                $pDescription .= trim($matches[3][$i]);
            }
            
            $paramList[$pName] = array(
                'type' => $pType,
                'description' => $pDescription
            );
        }
        
    }
    
    /**
     * 
     * @param type $modules
     */
    private function parseCommand($modules)
    {
        $this->commandList = array();
        
        // First get the Core one
        $coreCommandsFiles = glob(__DIR__."/../commands/*Command.php");
        foreach ($coreCommandsFiles as $coreCommand) {
            $objectName = basename($coreCommand, '.php');
            $this->commandList[$objectName] = '\\Centreon\\Commands\\'.$objectName;
        }
        
        // Now lets see the modules
        foreach ($modules as $module) {
            $moduleName = str_replace('Module', '', $module);
            preg_match_all('/[A-Z]?[a-z]+/', $moduleName, $myMatches);
            $moduleShortName = strtolower(implode('-', $myMatches[0]));
            if (\Centreon\Custom\Module\ModuleInformations::isModuleReachable($moduleShortName)) {
                $myModuleCommandsFiles = glob(__DIR__."/../../modules/$module/commands/*Command.php");
                foreach ($myModuleCommandsFiles as $moduleCommand) {
                    $objectName = basename($moduleCommand, '.php');
                    $this->commandList[$objectName] = '\\'.$moduleName.'\\Commands\\'. $objectName;
                }
            }
        }
    }
}
