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

namespace CentreonEngine\Listeners\CentreonConfiguration;

use Centreon\Internal\Di;
use CentreonEngine\Repository\ConfigGenerateMainRepository;
use CentreonEngine\Repository\CommandRepository;
use CentreonEngine\Repository\TimePeriodRepository;
use CentreonEngine\Repository\HostRepository;
use CentreonEngine\Repository\HostTemplateRepository;
use CentreonEngine\Repository\ConnectorRepository;
use CentreonEngine\Repository\ServicetemplateRepository;
use CentreonEngine\Repository\UserRepository;
use CentreonEngine\Repository\UsergroupRepository;
use CentreonEngine\Repository\ConfigGenerateResourcesRepository;
use CentreonEngine\Repository\ConfigGenerateModulesRepository;
use CentreonConfiguration\Events\GenerateEngine as GenerateEngineEvent;
use CentreonEngine\Events\GetMacroHost as HostMacroEvent;
use CentreonEngine\Events\GetMacroService as ServiceMacroEvent;
use CentreonEngine\Events\NotificationRuleEvent;

class GenerateEngine
{
    private static $event;
    private static $path;
    private static $fileList; 

    /**
     *
     * @param \CentreonConfiguration\Events\GenerateEngine
     */
    public static function execute(GenerateEngineEvent $event)
    {
        static::$event = $event;
        $config = Di::getDefault()->get('config');
        static::$path = $config->get('global', 'centreon_generate_tmp_dir');
        static::$path = rtrim(static::$path, '/') . '/engine/generate/';
        static::$fileList = array();

        $output = array();
        exec("rm -rf " . static::$path . $event->getPollerId() . "/* 2>&1", $output, $statusDelete);
        if ($statusDelete) {
            $event->setOutput(_('Error while deleting Engine temporary configuration files') . "\n" . implode("\n", $output));
        }

        $event->setOutput(
            sprintf(
                _('Generating temporary configuration files for poller %s:'), $event->getPollerId()
            )
        );

        static::generateObjectsFiles();
        static::generateMainFiles();
    }

    /**
     * Generate main configuration files
     */
    public static function generateMainFiles()
    {
        $event = static::$event;
        /* Generate Main File */
        ConfigGenerateMainRepository::generate(
            static::$fileList, 
            $event->getPollerId(), 
            static::$path, 
            "centengine.cfg"
        );
        $event->setOutput('centengine.cfg');

        /* Generate Debugging Main File */
        ConfigGenerateMainRepository::generate(
            static::$fileList,
            $event->getPollerId(),
            static::$path,
            "centengine-testing.cfg",
            1
        );
        $event->setOutput('centengine-testing.cfg');
    }

    /**
     * Generate all object files (host, service, etc...)
     *
     */
    public static function generateObjectsFiles()
    {
        $event = static::$event;
        /* Generate Configuration files */
        CommandRepository::generate(
            static::$fileList, 
            $event->getPollerId(), 
            static::$path, 
            "check-command.cfg",
            CommandRepository::CHECK_TYPE
        );
        $event->setOutput('check-command.cfg');

        CommandRepository::generate(
            static::$fileList, 
            $event->getPollerId(), 
            static::$path, 
            "misc-command.cfg",
            CommandRepository::NOTIF_TYPE
        );
        $event->setOutput('misc-command.cfg');

        ConfigGenerateResourcesRepository::generate(
            static::$fileList, 
            $event->getPollerId(), 
            static::$path, 
            "resources.cfg"
        );
        $event->setOutput('resources.cfg');

        ConfigGenerateModulesRepository::generate(
            static::$fileList,
            $event->getPollerId(),
            static::$path,
            $event
        );

        TimePeriodRepository::generate(static::$fileList, $event->getPollerId(), static::$path, "timeperiods.cfg");
        $event->setOutput('timeperiods.cfg');

        ConnectorRepository::generate(static::$fileList, $event->getPollerId(), static::$path, "connectors.cfg");
        $event->setOutput('connectors.cfg');

        /* Retrieve all extra macros by emitting event */
        $events = Di::getDefault()->get('events');
        $hostMacroEvent = new HostMacroEvent($event->getPollerId());
        $events->emit('centreon-engine.get.macro.host', array($hostMacroEvent));

        $serviceMacroEvent = new ServiceMacroEvent($event->getPollerId());
        $events->emit('centreon-engine.get.macro.service', array($serviceMacroEvent));

        $notificationRulesEvent = new NotificationRuleEvent($event->getPollerId());
        $events->emit('centreon-engine.set.notifications.rules', array($notificationRulesEvent));
        
        /* Templates config files */
        HostTemplateRepository::generate(
            static::$fileList,
            $event->getPollerId(),
            static::$path,
            "hostTemplates.cfg",
            $hostMacroEvent
        );
        $event->setOutput('hostTemplates.cfg');

        ServicetemplateRepository::generate(
            static::$fileList,
            $event->getPollerId(),
            static::$path,
            "serviceTemplates.cfg",
            $serviceMacroEvent
        );
        $event->setOutput('serviceTemplate.cfg');

        /* Monitoring Resources files */
        HostRepository::generate(
            static::$fileList, 
            $event->getPollerId(), 
            static::$path, 
            "resources/",
            $hostMacroEvent,
            $serviceMacroEvent
        );
        $event->setOutput('host configuration files');
    } 
}
