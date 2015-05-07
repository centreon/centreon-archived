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
namespace Centreon\Internal\Installer;

use Centreon\Internal\Di;
use Centreon\Internal\Module\Informations;
use Centreon\Internal\Utils\Filesystem\Directory;

/**
 * 
 * Centreon Static Files deployment
 */
class StaticFiles
{
    /**
     * 
     * @param string $moduleName Module slug from which the static files will be deploy
     */
    public static function deploy($moduleName)
    {
        // Building static path
        $path = rtrim(Di::getDefault()->get('config')->get('global', 'centreon_path'), '/');
        $sourceModuleStaticFilesPath = Informations::getModulePath($moduleName) . '/static/' . $moduleName;
        $targetModuleStaticFilesPath = $path . '/www/static/' . $moduleName;
        
        // 
        if (file_exists($sourceModuleStaticFilesPath)) {
            Directory::copy($sourceModuleStaticFilesPath, $targetModuleStaticFilesPath);
        }
    }
    
    /**
     * 
     * @param type $moduleName
     */
    public static function remove($moduleName)
    {
        $path = rtrim(Di::getDefault()->get('config')->get('global', 'centreon_path'), '/');
        $sourceModuleStaticFilesPath = $path . '/www/static/' . $moduleName;
        Directory::delete($sourceModuleStaticFilesPath, true);
    }
}
