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

use CentreonConfiguration\Events\EngineProcess as EngineProcessEvent;
use Centreon\Internal\Exception;
use CentreonEngine\Models\Engine;
use CentreonRealtime\Events\ExternalCommand;
use Centreon\Internal\Di;

class EngineProcess
{
    /**
     * @param \CentreonConfiguration\Events\EngineProcess $event
     * @throws \Centreon\Internal\Exception
     * @todo send command to centreon.d
     */
    public static function execute(EngineProcessEvent $event)
    {
        /*$action = $event->getAction();
        if (!in_array($action, array('reload', 'restart', 'forcereload'))) {
            throw new Exception(sprintf('Invalid action for Engine: %s', $action));
        }
        $engineParams = Engine::get($event->getPollerId(), 'init_script');
        if (!isset($engineParams['init_script'])) {
            throw new Exception(sprintf("Could not find init script for poller %s", $event->getPollerId()));
        }
        $initScript = $engineParams['init_script'];
        $command = "sudo {$initScript} {$action} 2>&1";
        $status = 0;
        $output = array();
        exec($command, $output, $status);
        foreach ($output as $line) {
            $event->setOutput($line);
        }
        $event->setStatus(
            $status ? false : true
        );*/
        $action = $event->getAction();
        if ($action == "restart") {
            $cmd = "[%u] RESTART_PROGRAM\n";
        } else if ($action == "reload") {
            $cmd = "[%u] RELOAD_PROGRAM\n";
        } else {
            throw new \Exception("Bad type of command.");
        }
        $extCommand = new ExternalCommand($event->getPollerId(), sprintf($cmd, time()), 'engine');
        Di::getDefault()->get('events')->emit('centreon-realtime.command.send', array($extCommand));
    }
}
