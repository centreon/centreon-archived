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

namespace Centreon\Internal;

use Centreon\Internal\Di;
use Centreon\Internal\Utils\CommandLine\Colorize;
use Centreon\Internal\Module\Informations;
use GetOptionKit\Argument;
use GetOptionKit\OptionCollection;
use GetOptionKit\OptionParser;
use GetOptionKit\OptionResult;
use GetOptionKit\OptionPrinter\ConsoleOptionPrinter;
use Centreon\Events\ManageCommandOptions as ManageCommandOptionsEvent;


class Command
{
    private $requestLine;
    private $arguments;
    private $commandList;
    
    /**
     * 
     * @param string $requestLine
     * @param array $arguments
     */
    public function __construct($requestLine, $arguments)
    {
        try {
            $bootstrap = new Bootstrap();
            $sectionToInit = array('configuration', 'database', 'cache', 'logger', 'organization', 'events');
            $bootstrap->init($sectionToInit);
            $this->requestLine = $requestLine;
            $this->arguments = $arguments;
            $modulesToParse = array();
            
            $coreCheck = preg_match("/^core:/", $requestLine);
            if (($coreCheck === 0) || ($coreCheck === false)){
                foreach (glob(__DIR__."/../../modules/*Module") as $moduleTemplateDir) {
                    $modulesToParse[] = basename($moduleTemplateDir);
                }
            }
            $this->parseCommand($modulesToParse);
        } catch (\Exception $e) {
            echo $e;
        }
    }
    
    /**
     * 
     * @param string $username
     * @param string $password
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
        echo "Usage: centreonConsole [-v] [-l] [-h] [-u <user>] [-p <password>] <request> <parameters>\n";
        echo "-v Get Centreon Core version\n";
        echo "-l List available commands\n";
        echo "-h Print this help\n";
        echo "-u / -p To authenticate\n";
        echo "request Command or request to execute, as listed by '-l'\n";
        echo "parameters List of parameters for the request, separated by ':'\n";
    }
    
    /**
     * 
     */
    public function getCommandList()
    {
        $requestLineExploded = explode(':', $this->requestLine);
        
        $nbOfElements = count($requestLineExploded);
        
        if (($nbOfElements == 1) && ($requestLineExploded[0] == "")) {
            $this->displayCommandList($this->commandList);
        } else {
            $module = $requestLineExploded[0];
            $this->displayCommandList($this->commandList[$module], $module);
        }
    }

    /**
     * Display the current installed version
     */
    public function getVersion()
    {
        $dbconn = Di::getDefault()->get('db_centreon');
        try {
            $stmt = $dbconn->query('SELECT value FROM cfg_informations where `key` = "version"');
        } catch (\Exception $e) {
            throw new \Exception("Version not present.");
        }
        if (0 === $stmt->rowCount()) {
            throw new \Exception("Version not present.");
        }
        $row = $stmt->fetch();
        $stmt->closeCursor();
        echo $row['value'] . "\n";
    }
    
    /**
     * 
     * @param array $ListOfCommands
     * @param string $module
     */
    private function displayCommandList($ListOfCommands, $module = null)
    {
        if (!is_null($module)) {
            $ListOfCommands = array($module => $ListOfCommands);
        }

        foreach ($ListOfCommands as $module => $section) {
            if ($module == 'core') {
                $moduleColorized = Colorize::colorizeText($module, "blue", "black", true);
            } else {
                $moduleColorized = Colorize::colorizeText($module, "purple", "black", true);
            }
            echo "[" . $moduleColorized . "]\n";
            foreach ($section as $sectionName => $call) {
                
                $explodedSectionName = explode('\\', $sectionName);
                $nbOfChunk = count($explodedSectionName);
                
                $commandName = "";
                for ($i=0 ;$i<($nbOfChunk-1); $i++) {
                    $commandName .= strtolower($explodedSectionName[$i]) . ':';
                }

                $commandName .= preg_replace('/Command/i', "", $explodedSectionName[$nbOfChunk - 1], 1);
                                
                // Get Action List
                $classReflection = new \ReflectionClass($call);
                $actionList = $classReflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                
                foreach ($actionList as $action) {
                    if (strpos($action->getName(), "Action")) {
                        $actionName = str_replace("Action", "", $action->getName());
                        $colorizedAction = Colorize::colorizeText($actionName, "yellow", "black", true);
                        echo "    $moduleColorized:$commandName:$colorizedAction\n";
                    }
                }
                
                echo "\n";
            }
            echo "\n\n";
        }
    }
    
    public function getFormsParams($aliveObject,$docComment){
        preg_match_all('/@cmdForm\s+(\S+|\/)+\s+(\S+)/', $docComment, $matches);
        $formRoute = "";
        $required = false;
        if(!empty($matches[1][0])){
            $formRoute = $matches[1][0];
            if(!empty($matches[2][0])){
                switch($matches[2][0]){
                    case 'required' : 
                        $required = true;
                        break;
                    case 'optional' : 
                    default :
                        $required = false;
                        break;
                }
            }
            if (method_exists($aliveObject, 'getFieldsFromForm')) {
                $aliveObject->getFieldsFromForm($formRoute,$required);
            }
        }
    }
    
    public function getObject($aliveObject,$docComment){
        preg_match_all('/@cmdObject\s+(\S+)\s+(\S+)\s*(.*)/', $docComment, $matches);
        $object = array();
        if(!empty($matches[1])){
            if(!empty($matches[1][0])){
                $object['objectType'] = $matches[1][0];
            }

            if(!empty($matches[2][0])){
                $object['objectName'] = $matches[2][0];
            }

            if(!empty($matches[3][0])){
                $object['objectComment'] = $matches[3][0];
            }

            if (method_exists($aliveObject, 'getObject')) {
                $aliveObject->getObject($object);
            }
        }
    }
    
    public function getCustomsParams($aliveObject,$docComment){
        
        preg_match_all('/@cmdParam\s+(\S+)\s+(\S+)\s+(\S+)\s*(.*)/', $docComment, $matches);
        
        
        
        $paramsArray = array();
        if(!empty($matches[1])){
            foreach($matches[1] as $key=>$paramType){
                $paramsArray[$key]['paramType'] = $paramType;
            }
        }
        if(!empty($matches[2])){
            foreach($matches[2] as $key=>$paramName){
                $paramsArray[$key]['paramName'] = $paramName;
            }
        }
        if(!empty($matches[3])){
            foreach($matches[3] as $key=>$paramRequired){
                $paramsArray[$key]['paramRequired'] = ($paramRequired == 'required') ? true : false;
            }
        }
        if(!empty($matches[4])){
            foreach($matches[4] as $key=>$paramComment){
                $paramsArray[$key]['paramComment'] = $paramComment;
            }
        }

        $aliveObject->getCustomsParams($paramsArray);

    }
    
    
    /**
     * 
     * @throws Exception
     */
    public function executeRequest()
    {
        $requestLineElements = $this->parseRequestLine();
        $module = $requestLineElements['module'];
        $object = ltrim($requestLineElements['object'], '\\');
        $action = $requestLineElements['action'];
        
        if (strtolower($module) != 'core') {
            if (!Informations::isModuleReachable($module)) {
                throw new Exception("The module doesn't exist");
            }
        }
        
        if (!isset($this->commandList[$module][$object])) {
            throw new Exception("The object $object doesn't exist");
        }
        
        $aliveObject = new $this->commandList[$module][$object]();
        
        if (!method_exists($aliveObject, $action)) {
            throw new Exception("The action '$action' doesn't exist");
        }
        
        $classReflection = new \ReflectionClass($aliveObject);
        $methodReflection = $classReflection->getMethod($action);
        $docComment = $methodReflection->getDocComment();
        
        $this->getFormsParams($aliveObject, $docComment);
        $this->getObject($aliveObject, $docComment);
        $this->getCustomsParams($aliveObject, $docComment);
        
        $actionArgs = array();
        $this->getArgs($actionArgs, $aliveObject, $action);
        // Call the action
        $aliveObject->named($action, $actionArgs);
        
        echo "\n";
    }
    
    /**
     * 
     * @return array
     * @throws Exception
     */
    private function parseRequestLine()
    {
        $requestLineExploded = explode(':', $this->requestLine);
        
        $nbOfElements = count($requestLineExploded);
        
        $module = $requestLineExploded[0];
        $object = ucfirst($requestLineExploded[($nbOfElements - 2)]) . 'Command';
        $action = $requestLineExploded[($nbOfElements - 1)] . 'Action';
        
        if ($nbOfElements > 3) {
            //
            $objectRaw = "";
            for ($i=1; $i<($nbOfElements - 2); $i++) {
                $objectRaw .= ucfirst($requestLineExploded[$i]) . '\\';
            }
            $object = $objectRaw . $object;
        
        } elseif ($nbOfElements < 3) {
            throw new Exception("The request is not valid");
        }
        
        return array(
            'module' => $module,
            'object' => $object,
            'action' => $action
        );
    }
    
    /**
     * 
     * @param array $argsList
     * @param type $aliveObject
     * @param string $action
     */
    private function getArgs(array &$argsList, $aliveObject, $action)
    {
        $listOptions = array();
        if(isset($aliveObject->options)){
            $listOptions = $aliveObject->options;
        }
        

        $specs = new OptionCollection();
        foreach ($listOptions as $option => $spec) {
            if ($spec['type'] != 'boolean') {
                if ($spec['multiple']) {
                    $option .= '+';
                } else if ($spec['required']) {
                    $option .= ':';
                } else {
                    $option .= '?';
                }
            }
            $specs->add($option, $spec['help'])->isa($spec['type']);
        }        
        
        $parser = new OptionParser($specs);
        $parsedOptions = self::parseOptions($this->arguments, $parser);
        
        if (isset($aliveObject->objectName)) {
            $events = Di::getDefault()->get('events');
            $manageCommandOptionsEvent = new ManageCommandOptionsEvent($aliveObject->objectName, $action, $listOptions, $parsedOptions);
            $events->emit('core.manage.command.options', array($manageCommandOptionsEvent));
            $listOptions = $manageCommandOptionsEvent->getOptions();
            $aliveObject->options = $listOptions;
        }
        
        $listOptions = array_merge($listOptions,
            array(
            'h|help' => array(
                'help' => 'help',
                'type' => 'boolean',
                'functionParams' => '',
                "toTransform" => '',
                'required' => false,
                'defaultValue' => false)
            )
        );
        
        $specs = new OptionCollection();
        foreach ($listOptions as $option => $spec) {
            if ($spec['type'] != 'boolean') {
                if ($spec['multiple']) {
                    $option .= '+';
                } else if ($spec['required']) {
                    $option .= ':';
                } else {
                    $option .= '?';
                }
            }
            $specs->add($option, $spec['help'])->isa($spec['type']);
        }
        
        try {
            $parser = new OptionParser($specs);
            $optionsParsed = $parser->parse($this->arguments);
        } catch (RequireValueException $ex) {
            echo $ex->getMessage();
        }

        if ($optionsParsed->help) {
            //echo "centreonConsole centreon-configuration:Service:listMacro\n\n";
            $printer = new ConsoleOptionPrinter();
            echo $printer->render($specs);
            die;
        }
        foreach( $optionsParsed as $key => $spec ) {
            $argsList[$key] = $spec->value;
        }
        unset($listOptions['h|help']);
        foreach($listOptions as $key=>$options){
            if($options['type'] === 'boolean'){
                if(isset($options['booleanValue'])){
                    if(isset($argsList[$key])){
                        $argsList[$key] = $options['booleanValue'];
                    }else if(isset($options['booleanSetDefault']) && $options['booleanSetDefault']){
                        $argsList[$key] = !$options['booleanValue'];
                    }
                    if (isset($argsList[$key]) && $argsList[$key]) { //true 
                        $argsList[$key] = 1;
                    } else if(isset($argsList[$key])){ //false
                        $argsList[$key] = 0;
                    }
                }
            }
            
            if(isset($argsList[$key]) && $options['multiple']){
                if(is_array($argsList[$key])){
                    $argsList[$key] = implode(',',$argsList[$key]);
                }
            }
        }
    }
    
    /**
     * 
     * @param string $object
     * @param string $method
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
     * @param array $modules
     */
    private function parseCommand($modules)
    {
        $this->commandList = array();
        
        // First get the Core one
        $this->getCommandDirectoryContent(realpath(__DIR__."/../commands/"));
        
        // Now lets see the modules
        foreach ($modules as $module) {
            $moduleName = str_replace('Module', '', $module);
            preg_match_all('/[A-Z]?[a-z]+/', $moduleName, $myMatches);
            $moduleShortName = strtolower(implode('-', $myMatches[0]));
            if (Informations::isModuleReachable($moduleShortName)) {
                $this->getCommandDirectoryContent(
                    __DIR__ . "/../../modules/$module/commands/",
                    $moduleShortName,
                    $moduleName
                );
            }
        }
    }
    
    /**
     * 
     * @param string $dirname
     * @param string $module
     * @param string $namespace
     */
    private function getCommandDirectoryContent($dirname, $module = 'core', $namespace = 'Centreon')
    {
        $path = realpath($dirname);
        
        if (file_exists($path)) {
            $listOfDirectories = glob($path . '/*');

            while (count($listOfDirectories) > 0) {
                $currentFolder = array_shift($listOfDirectories);
                if (is_dir($currentFolder)) {
                    $listOfDirectories = array_merge($listOfDirectories, glob($currentFolder . '/*'));
                } elseif (pathinfo($currentFolder, PATHINFO_EXTENSION) == 'php') {
                    $objectName = str_replace(
                        '/',
                        '\\',
                        substr(
                            $currentFolder,
                            (strlen($path) + 1),
                            (strlen($currentFolder) - strlen($path) - 5)
                        )
                    );
                    $this->commandList[$module][$objectName] = '\\'.$namespace.'\\Commands\\'.str_replace('/', '\\', $objectName);
                }
            }
        }
    }

    /**
     *
     * @param array $argv
     * @param OptionParser $parser
     * @return $result
     */
    public function parseOptions(array $argv, $parser)
    {
        $result = array();
        $argv = $parser->preprocessingArguments($argv);
        $len = count($argv);
        for ($i = 0; $i < $len; ++$i)
        {
            $arg = new Argument( $argv[$i] );
            if (! $arg->isOption()) {
                continue;
            }

            $next = null;
            if ($i + 1 < count($argv) )  {
                $next = new Argument($argv[$i + 1]);
            }
            $spec = $parser->specs->get($arg->getOptionName());
            if (! $spec) {
                continue;
            }
            if ($spec->isRequired()) {
                if (! $parser->foundRequireValue($spec, $arg, $next) ) {
                    continue;
                }
                $parser->takeOptionValue($spec, $arg, $next);
                
                if ($next && ! $next->anyOfOptions($parser->specs)) {
                    $result[$spec->getId()] = $next->arg;
                    $i++;
                }
            } 
        }
        return $result;
    }
}
