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

namespace Centreon\Commands\Database;

use Centreon\Internal\Command\AbstractCommand;
use Centreon\Internal\Exception;
use Centreon\Internal\Di;
use Centreon\Internal\Utils\CommandLine\InputOutput;
use Centreon\Internal\Database\GenerateDiff;
use Centreon\Internal\Database\Migrate;
use Centreon\Internal\Installer\Migration\Manager;

/**
 * Description of MigrateCommand
 *
 * @author Lionel Assepo <lassepo@centreon.com>
 */
class MigrateCommand extends AbstractCommand
{
    /**
     * 
     * @cmdObject string module the module name
     */
    public function statusAction($object)
    {
        $module = $object['module'];
        InputOutput::display(_("Lists the migrations yet to be executed"));
        $migrationManager = new Manager($module, 'production');
        $cmd = $this->getPhinxCallLine() .'status ';
        $cmd .= '-c '. $migrationManager->getPhinxConfigurationFile();
        $cmd .= '-e '. $module;
        shell_exec($cmd);
    }
    
    /**
     * 
     * @cmdObject string module the module name
     */
    public function migrateAction($object)
    {
        $module = $object['module'];
        InputOutput::display(_("Executes all migrations"));
        $migrationManager = new Manager($module, 'production');
        $cmd = $this->getPhinxCallLine() .'migrate ';
        $cmd .= '-c '. $migrationManager->getPhinxConfigurationFile();
        $cmd .= ' -e '. $module;
        shell_exec($cmd);
    }
    
    /**
     * 
     * @cmdObject string module the module name
     */
    public function rollbackAction($object)
    {
        $module = $object['module'];
        InputOutput::display(_("Revert all migrations"));
        $migrationManager = new Manager($module, 'production');
        $cmd = $this->getPhinxCallLine() .'rollback ';
        $cmd .= '-c '. $migrationManager->getPhinxConfigurationFile();
        $cmd .= '-e '. $module;
        shell_exec($cmd);
    }
    
    /**
     * 
     * @cmdObject string module the module name
     */
    public function initAction($object)
    {
        $module = $object['module'];
        $migrationManager = new Manager($module, 'development');
        $migrationManager->generateConfiguration();
    }
    
    /**
     * 
     * @cmdObject string module the module name
     * @cmdParam string class required 
     */
    public function createAction($object, $param)
    {
        $module = $object['module'];
        $class = $param['class'];
        $migrationManager = new Manager($module, 'development');
        $cmd = $this->getPhinxCallLine() .'create ';
        $cmd .= '-c '. $migrationManager->getPhinxConfigurationFile();
        $cmd .= ' ' . $class;
        shell_exec($cmd);
    }
    
    /**
     * 
     * @return string
     */
    private function getPhinxCallLine()
    {
        $di = Di::getDefault();
        $centreonPath = $di->get('config')->get('global', 'centreon_path');
        $callCmd = 'php ' . $centreonPath . '/vendor/robmorgan/phinx/bin/phinx ';
        return $callCmd;
    }
}
