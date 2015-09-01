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
use Centreon\Internal\Install\Install;
use Centreon\Internal\Install\Db;
use Centreon\Internal\Install\AbstractInstall;
use Centreon\Internal\Informations;
use Centreon\Internal\Di;
use Centreon\Internal\Module\Dependency;

class Migrate extends AbstractInstall
{
    /**
     * 
     * @return boolean
     */
    public static function checkForMigration()
    {
        $migrationNeeded = false;
        try {
            if (version_compare(Informations::getCentreonVersion(), '3.0.0', '<')) {
                $migrationNeeded = true;
            }
        } catch (\Exception $e) {
            $migrationNeeded = false;
        }
        
        return $migrationNeeded;
    }
    
    /**
     * 
     */
    public static function migrateCentreon()
    {
        if (!self::checkForMigration()) {
            Install::installCentreon();
        } else {
            $di = Di::getDefault();
            $config = $di->get('config');
            $dbName = $config->get('db_centreon', 'dbname');
            
            echo Colorize::colorizeMessage("Starting to migrate to Centreon 3.0", "info") . "\n";
            
            echo "Preparing Migration... ";
            self::prepareDb();
            echo Colorize::colorizeText('Done', 'green', 'black', true) . "\n";
            
            echo "Migrating " . Colorize::colorizeText('centreon', 'blue', 'black', true) . " database... ";
            Db::update($dbName);
            echo Colorize::colorizeText('Done', 'green', 'black', true) . "\n";

            $modulesToInstall = self::getCoreModules();

            $dependencyResolver = new Dependency($modulesToInstall['modules']);
            $installOrder = $dependencyResolver->resolve();

            foreach($installOrder as $moduleName) {
                $currentModule = $modulesToInstall['modules'][$moduleName];
                $moduleInstaller = new $currentModule['classCall']($currentModule['directory'], $currentModule['infos'], 'console');
                echo "Installing ". Colorize::colorizeText($moduleName, 'purple', 'black', true) . " module... ";
                $moduleInstaller->install(false);
                echo Colorize::colorizeText('Done', 'green', 'black', true) . "\n";
            }
            
            echo Colorize::colorizeMessage("Your Centreon has been successfully migrated to Centreon 3.0", "success") . "\n";
        }
    }
    
    /**
     * 
     */
    private static function prepareDb()
    {
        
    }
}
