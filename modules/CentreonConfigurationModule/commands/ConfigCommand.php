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

namespace CentreonConfiguration\Commands;

use Centreon\Internal\Command\AbstractCommand;
use Centreon\Internal\Utils\CommandLine\Colorize;
use CentreonConfiguration\Repository\ConfigApplyRepository;
use CentreonConfiguration\Repository\ConfigGenerateRepository;
use CentreonConfiguration\Repository\ConfigMoveRepository;
use CentreonConfiguration\Repository\ConfigTestRepository;
use CentreonConfiguration\Repository\PollerRepository;

/**
 * @authors Lionel Assepo
 * @package CentreonConfiguration
 * @subpackage Commands                                 
 */
class ConfigCommand extends AbstractCommand
{
    /**
     * @cmdObject string poller the poller slug
     */
    public function generateAction($object)
    {
        $exit = 1;
        $id = PollerRepository::getIdBySlugName($object['poller']);
        if(!is_null($id)){
            $obj = new ConfigGenerateRepository($id);
            $obj->generate();
            echo $obj->getOutput();
            if($obj->getStatus() === 1){
                $exit = 0;
            }else{
                $exit = 1;
            }
        }else{
            \Centreon\Internal\Utils\CommandLine\InputOutput::display("Error : Poller not in Database", true, 'red');
            $exit = 1;
        }
        exit($exit);
        
    }

    /**
     * @cmdObject string poller the poller slug
     */
    public function moveAction($object)
    {
        $exit = 1;
        $id = PollerRepository::getIdBySlugName($object['poller']);
        if(!is_null($id)){
            $obj = new ConfigMoveRepository($id);
            $obj->moveConfig();
            echo $obj->getOutput();
            if($obj->getStatus() === 1){
                $exit = 0;
            }else{
                $exit = 1;
            }
        }else{
            \Centreon\Internal\Utils\CommandLine\InputOutput::display("Error : Poller not in Database", true, 'red');
            $exit = 1;
        }
        exit($exit);
    }

    /**
     * @cmdObject string poller the poller slug
     */
    public function testAction($object)
    {
        $exit = 1;
        $id = PollerRepository::getIdBySlugName($object['poller']);
        if(!is_null($id)){
            $obj = new ConfigTestRepository($id);
            $obj->checkConfig();
            // Only CentEngine is tested at the moment
            // We are formatting the output to have a colored, readable ouput on terminal
            $totalWarningsStr = Colorize::colorizeMessage('Total Warnings', 'warning');
            $warningStr = Colorize::colorizeMessage('Warning', 'warning');
            $totalErrorsStr = Colorize::colorizeMessage('Total Errors', 'danger');
            $errorStr = Colorize::colorizeMessage('Error', 'danger');
            $finalStr = $obj->getOutput();
            $finalStr = str_replace("\nTotal Warnings", "\n".$totalWarningsStr, $finalStr);
            $finalStr = str_replace("\nWarning", "\n".$warningStr, $finalStr);
            $finalStr = str_replace("\nTotal Errors", "\n".$totalErrorsStr, $finalStr);
            $finalStr = str_replace("\nError", "\n".$errorStr, $finalStr);
            if($obj->getStatus() === 1){
                $exit = 0;
            }else{
                $exit = 1;
            }
            echo $finalStr;
        }else{
            \Centreon\Internal\Utils\CommandLine\InputOutput::display("Error : Poller not in Database", true, 'red');
            $exit = 1;
        }
        
        exit($exit);
        
        
    }

    /**

    /**
     * @cmdObject string poller the poller slug
     * @cmdParam string action required the action 
     */
    public function applyAction($object, $param)
    {
        $exit = 1;
        $id = PollerRepository::getIdBySlugName($object['poller']);
        if(!is_null($id)){
            $action = $param['action'];
            $obj = new ConfigApplyRepository($id);
            $obj->action($action);
            echo $obj->getOutput();
            if($obj->getStatus() === 1){
                $exit = 0;
            }else{
                $exit = 1;
            }
        }else{
            \Centreon\Internal\Utils\CommandLine\InputOutput::display("Error : Poller not in Database", true, 'red');
            $exit = 1;
        }
        exit($exit);

    }
}
