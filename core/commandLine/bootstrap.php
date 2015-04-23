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

/* Define the path to configuration files */
define('CENTREON_ETC', realpath(__DIR__ . '/../../config/'));

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
