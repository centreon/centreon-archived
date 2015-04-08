<?php
/*
 * Copyright 2005-2014 CENTREON
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

namespace CentreonEngine\Listeners\CentreonConfiguration;

use CentreonConfiguration\Events\EngineProcess as EngineProcessEvent;
use Centreon\Internal\Exception;
use CentreonEngine\Models\Engine;

class EngineProcess
{
    /**
     * @param \CentreonConfiguration\Events\EngineProcess $event
     * @throws \Centreon\Internal\Exception
     * @todo send command to centreon.d
     */
    public static function execute(EngineProcessEvent $event)
    {
        $action = $event->getAction();
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
        );
    }
}
