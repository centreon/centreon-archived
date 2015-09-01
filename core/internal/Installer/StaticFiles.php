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
