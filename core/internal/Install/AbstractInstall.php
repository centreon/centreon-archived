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

/**
 * Description of AbstractInstall
 *
 * @author lionel
 */
class AbstractInstall
{
    /**
     *
     * @var array Core Modules of Centreon
     */
    private static $coreModules = array(
        'centreon-main',
        'centreon-security',
        'centreon-administration',
        'centreon-configuration',
        'centreon-realtime',
        'centreon-customview',
    );
    
    /**
     * 
     * @return array
     * @throws \Exception
     */
    protected static function getCoreModules()
    {
        $result = array('moduleCheck' => true, 'errorMessages' => '', 'modules' => array());
        $centreonPath = rtrim(\Centreon\Internal\Di::getDefault()->get('config')->get('global', 'centreon_path'), '/');

        foreach (self::$coreModules as $coreModule) {
            $commonName = str_replace(' ', '', ucwords(str_replace('-', ' ', $coreModule)));
            $moduleDirectory = $centreonPath . '/modules/' . $commonName . 'Module/';

            if (!file_exists(realpath($moduleDirectory . 'install/config.json'))) {
                throw new \Exception("The module $commonName is not valid because of a missing configuration file");
            }
            $moduleInfo = json_decode(file_get_contents($moduleDirectory . 'install/config.json'), true);
            $classCall = '\\'.$commonName.'\\Install\\Installer';

            // Check if all dependencies are satisfied
            try {
                $result['modules'][$coreModule] = array(
                    'classCall' => $classCall,
                    'directory' => $moduleDirectory,
                    'infos' => $moduleInfo
                );
            } catch (\Exception $e) {
                $result['moduleCheck'] = false;
                $result['errorMessages'] = $e->getMessage() . "\n";
            }
        }
        
        return $result;
    }
}
