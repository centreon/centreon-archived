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

namespace Centreon\Internal\Install;

use \Centreon\Internal\Utils\CommandLine\Colorize;

class Install extends \Centreon\Internal\Install\AbstractInstall
{
    /**
     * 
     */
    public static function installCentreon()
    {
        if (\Centreon\Internal\Install\Migrate::checkForMigration()) {
            \Centreon\Internal\Install\Migrate::migrateCentreon();
        } else {
            echo Colorize::colorizeMessage("Starting to install Centreon 3.0", "info") . "\n";
            echo "Creating " . Colorize::colorizeText('centreon', 'blue', 'black', true) . " database... ";
            \Centreon\Internal\Install\Db::update('centreon');
            echo Colorize::colorizeText('Done', 'green', 'black', true) . "\n";
            
            $modulesToInstall = self::getCoreModules();
            
            $dependencyResolver = new \Centreon\Internal\Module\Dependency($modulesToInstall['modules']);
            $installOrder = $dependencyResolver->resolve();
            
            foreach($installOrder as $moduleName) {
                $currentModule = $modulesToInstall['modules'][$moduleName];
                $moduleInstaller = new $currentModule['classCall']($currentModule['directory'], $currentModule['infos']);
                echo "Installing ". Colorize::colorizeText($moduleName, 'purple', 'black', true) . " module\n";
                $moduleInstaller->install();
                echo Colorize::colorizeText('Installation of module ' .$moduleName . ' done', 'green', 'black', true) . "\n";
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
}
