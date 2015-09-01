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

/* Define the path to configuration files */
if (!defined('CENTREON_ETC')) {
    define('CENTREON_ETC', realpath(__DIR__ . '/../../config/'));
}

$centreon_path = realpath(__DIR__ . '/../..');

/* Add classpath to include path */
set_include_path($centreon_path . PATH_SEPARATOR . get_include_path());

require_once realpath(__DIR__ . '/../../vendor/autoload.php');

// Core
spl_autoload_register(function ($classname) use ($centreon_path) {
    $filename = $centreon_path;
    $fullClassPath = explode('\\', $classname);
    
    $mainScope = array_shift($fullClassPath);
    if ($mainScope == 'Centreon') {
        $secondScope = array_shift($fullClassPath);
        switch (strtolower($secondScope)) {
            default:
            case 'internal':
                $filename .= '/core/internal/'.  implode('/', $fullClassPath);
                break;
            case 'api':
                $thirdScope = array_shift($fullClassPath);
                if (strtolower($thirdScope) === 'internal') {
                    $filename .= '/core/api/internal/'.  implode('/', $fullClassPath);
                } elseif (strtolower($thirdScope) === 'rest') {
                    $filename .= '/core/api/rest/'.  implode('/', $fullClassPath);
                } elseif (strtolower($thirdScope) === 'soap') {
                    $filename .= '/core/api/soap/'.  implode('/', $fullClassPath);
                }
                break;
            case 'commands':
                $filename .= '/core/commands/'.  implode('/', $fullClassPath);
                break;
            case 'repository':
                $filename .= '/core/repositories/'.  implode('/', $fullClassPath);
                break;
            case 'models':
                $filename .= '/core/models/'.  implode('/', $fullClassPath);
                break;
            case 'custom':
                $filename .= '/core/custom/'.  implode('/', $fullClassPath);
                break;
            case 'events':
                $filename .= '/core/events/'.  implode('/', $fullClassPath);
                break;
        }
    }
    
    $filename .= '.php';
    if (file_exists($filename)) {
        require_once $filename;
    }
});


// Module
spl_autoload_register(function ($classname) use ($centreon_path) {
    $filename = $centreon_path . '/modules/';
    $fullClassPath = explode('\\', $classname);
    
    $filename .= array_shift($fullClassPath).'Module';
    $secondScope = array_shift($fullClassPath);
    switch(strtolower($secondScope)) {
        default:
            $filename .= implode('/', $fullClassPath);
            break;
        case 'commands':
            $filename .= '/commands/'.  implode('/', $fullClassPath);
            break;
        case 'models':
            $filename .= '/models/'.  implode('/', $fullClassPath);
            break;
        case 'repository':
            $filename .= '/repositories/'.  implode('/', $fullClassPath);
            break;
        case 'internal':
            $filename .= '/internal/'.  implode('/', $fullClassPath);
            break;
        case 'install':
            $filename .= '/install/'.  implode('/', $fullClassPath);
            break;
        case 'forms':
            $filename .= '/forms/'.  implode('/', $fullClassPath);
            break;
        case 'events':
            $filename .= '/events/'.  implode('/', $fullClassPath);
            break;
        case 'listeners':
            $filename .= '/listeners/'.  implode('/', $fullClassPath);
            break;
        case 'api':
            $thirdScope = array_shift($fullClassPath);
            if (strtolower($thirdScope) === 'internal') {
                $filename .= '/api/internal/'.  implode('/', $fullClassPath);
            } elseif (strtolower($thirdScope) === 'rest') {
                $filename .= '/api/rest/'.  implode('/', $fullClassPath);
            } elseif (strtolower($thirdScope) === 'soap') {
                $filename .= '/api/soap/'.  implode('/', $fullClassPath);
            }
            break;
    }
    
    $filename .= '.php';
    if (file_exists($filename)) {
        require_once $filename;
    }
});
