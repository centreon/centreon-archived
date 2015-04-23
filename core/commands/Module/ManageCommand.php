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

namespace Centreon\Commands\Module;

use Centreon\Internal\Module\Informations;
use Centreon\Internal\Command\AbstractCommand;

/**
 * COmmand Line to manage
 *
 * @author Lionel Assepo
 * @version 3.0.0
 * @package Centreon
 * @subpackage Core
 */
class ManageCommand extends AbstractCommand
{
    /**
     * List module names
     * @param string $type
     * @param string $status
     */
    public function simpleListAction($onlyActivated = 1)
    {
        $moduleList = Informations::getModuleList($onlyActivated);
        foreach ($moduleList as $module) {
            echo $module."\n";
        }
    }

    /**
     * List all information about modules
     * @param string $type
     * @param int $onlyActivated
     * @param int $header
     */
    public function extendedListAction($onlyActivated = 1, $header = 1)
    {
        $moduleList = Informations::getModuleExtendedList($onlyActivated);
        if ($header) {
            echo 'name;alias;description;version;author;isactivated;isinstalled' . "\n";
        }
        foreach ($moduleList as $module) {
            echo $module['name'].';'.
                $module['alias'].';'.
                $module['description'].';'.
                $module['version'].';'.
                $module['author'].';'.
                $module['isactivated'].';'.
                $module['isinstalled']."\n";
        }
    }

    /**
     * 
     * @param string $moduleName
     */
    public function showAction($moduleName)
    {
        
    }
    
    /**
     * 
     * @param string $moduleName
     */
    public function installAction($moduleName)
    {
        $moduleInstaller = Informations::getModuleInstaller($moduleName);
        $moduleInstaller->install();
    }
    
    /**
     * 
     * @param string $moduleName
     */
    public function reinstallAction($moduleName)
    {
        $moduleId = Informations::getModuleIdByName($moduleName);
        $moduleInstaller = Informations::getModuleInstaller($moduleName, $moduleId);
        $moduleInstaller->remove();
        unset($moduleInstaller);
        $moduleInstaller = Informations::getModuleInstaller($moduleName);
        $moduleInstaller->install(false);
    }
    
    /**
     * 
     * @param string $moduleName
     */
    public function upgradeAction($moduleName)
    {
        echo "Not implemented yet";
    }
    
    /**
     * 
     * @param string $moduleName
     */
    public function removeAction($moduleName)
    {
        $moduleInstaller = Informations::getModuleInstaller($moduleName);
        $moduleInstaller->remove();
    }
}
