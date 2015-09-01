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

use Centreon\Internal\Install\Migrate;
use Centreon\Internal\Db\Installer;
use Centreon\Internal\Install\AbstractInstall;

class Upgrade extends AbstractInstall
{
    public static function upgradeCentreon()
    {
        if (Migrate::checkForMigration()) {
            Migrate::migrateCentreon();
        } else {
            Installer::updateDb('migrate');

            $modulesToUpgrade = self::getCoreModules();
            
            foreach($modulesToUpgrade as $module) {
                $moduleInstaller = new $module['classCall']($module['directory'], $module['infos']);
                $moduleInstaller->install();
            }
        }
    }
    
    /**
     * 
     */
    public static function checkForUpdate()
    {
        return false;
    }
}
