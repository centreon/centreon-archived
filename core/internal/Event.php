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
namespace Centreon\Internal;

use Centreon\Internal\Module\Informations;
use Centreon\Internal\Utils\Filesystem\File;
use Centreon\Internal\Utils\String\CamelCaseTransformation;

/**
 * Description of Event
 *
 * @author lionel
 */
class Event
{
    /**
     * Init event listeners of modules
     */
    public static function initEventListeners()
    {
        $moduleList = Informations::getModuleList();
        foreach ($moduleList as $module) {
            $listenersPath = Informations::getModulePath($module) . '/listeners/';
            if (file_exists($listenersPath)) {
                $ModuleListenersList = glob($listenersPath . '*');
                foreach ($ModuleListenersList as $moduleListenersPath) {   
                    $mTarget = substr($moduleListenersPath, strlen($listenersPath));
                    $mSource = CamelCaseTransformation::customToCamelCase($module, '-');
                    self::attachModuleEventListeners($mSource, $mTarget, $moduleListenersPath);
                }
            }
        }
    }
    
    /**
     * 
     * @param type $moduleName
     * @param type $moduleListenersPath
     */
    private static function attachModuleEventListeners($moduleSource, $moduleTarget, $moduleListenersPath)
    {
        $emitter = Di::getDefault()->get('events');
        $myListeners = File::getFiles($moduleListenersPath, 'php');

        foreach ($myListeners as $myListener) {
            $listener = (basename($myListener, '.php'));
            
            $eventName = CamelCaseTransformation::camelCaseToCustom($moduleTarget, '-')
                . '.'
                . CamelCaseTransformation::camelCaseToCustom($listener, '.');
            $emitter->on(
                strtolower($eventName),
                function ($params) use ($listener, $moduleSource, $moduleTarget) {
                    call_user_func(
                        array($moduleSource . "\\Listeners\\".$moduleTarget."\\".$listener, "execute"),
                        $params
                    );
                }
            );
        }
    }
}
