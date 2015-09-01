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

namespace Centreon\Internal\Install;

use Centreon\Internal\Utils\CommandLine\Colorize;
use Centreon\Internal\Install\Migrate;
use Centreon\Internal\Install\AbstractInstall;
use Centreon\Internal\Utils\Dependency\PhpDependencies;
use Centreon\Internal\Module\Dependency;
use Centreon\Internal\Installer\Migration\Manager;
use Centreon\Internal\Di;

class Install extends AbstractInstall
{
    /**
     * 
     */
    public static function installCentreon()
    {
        if (Migrate::checkForMigration()) {
            Migrate::migrateCentreon();
        } else {
            // Initialize configuration
            $di = Di::getDefault();
            $config = $di->get('config');
            $centreonPath = $config->get('global', 'centreon_path');
            $dbName = $config->get('db_centreon', 'dbname');
            
            // Check Php Dependencies
            $phpDependencies = json_decode(file_get_contents(rtrim($centreonPath, '/') . '/install/dependencies.json'));
            PhpDependencies::checkDependencies($phpDependencies);
            
            echo Colorize::colorizeMessage("Starting to install Centreon 3.0", "info") . "\n";
            echo "Creating " . Colorize::colorizeText('centreon', 'blue', 'black', true) . " database... ";

            // Install DB
            $migrationManager = new Manager('core', 'production');
            $migrationManager->generateConfiguration();
            $cmd = self::getPhinxCallLine() .'migrate ';
            $cmd .= '-c '. $migrationManager->getPhinxConfigurationFile();
            $cmd .= ' -e core';
            shell_exec($cmd);

            //Db::update('core');
            echo Colorize::colorizeText('Done', 'green', 'black', true) . "\n";
            
            $modulesToInstall = self::getCoreModules();
            
            $dependencyResolver = new Dependency($modulesToInstall['modules']);
            $installOrder = $dependencyResolver->resolve();
            
            foreach($installOrder as $moduleName) {
                $currentModule = $modulesToInstall['modules'][$moduleName];
                $moduleInstaller = new $currentModule['classCall']($currentModule['directory'], $currentModule['infos'], 'console');
                $moduleInstaller->install();
            }
            echo Colorize::colorizeMessage("Centreon 3.0 has been successfully installed", "success") . "\n";
        }
    }
    
    /**
     * 
     * @param boolean $removeDb
     */
    public static function uninstallCentreon($removeDb = false)
    {
        
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
