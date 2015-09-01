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
use Centreon\Internal\Exception;
use CentreonEngine\Repository\ConfigGenerateMainRepository;
use CentreonConfiguration\Events\CopyFiles as CopyFilesEvent;
use CentreonEngine\Repository\EngineRepository;

class CopyFiles
{
    /**
     * Execute action 
     *
     * @param \CentreonConfiguration\Events\CopyFiles $event
     * @throws Exception
     */
    public static function execute(CopyFilesEvent $event)
    {
        $config = Di::getDefault()->get('config');
        $tmpdir = $config->get('global', 'centreon_generate_tmp_dir');

        $output = array();
        exec("rm -rf {$tmpdir}/engine/apply/{$event->getPollerId()}/* 2>&1", $output, $statusDelete);
        if ($statusDelete) {
            $event->setOutput(_('Error while deleting Engine configuration files') . "\n" . implode("\n", $output));
        }

        if (false === is_dir($tmpdir . '/engine/apply/' . $event->getPollerId())) {
            if (false === mkdir($tmpdir . '/engine/apply/' . $event->getPollerId())) {
                throw new Exception("Error while prepare copy of Engine configuration files\n");
            }
        }
        $output = array();
        exec("cp -Rpf $tmpdir/engine/generate/{$event->getPollerId()}/* {$tmpdir}/engine/apply/{$event->getPollerId()}/ 2>&1", $output, $statusCopy);
        if ($statusCopy) {
            $event->setOutput(_('Error while copying Engine configuration files') . "\n" . implode("\n", $output));
        }

        if (!$statusDelete && !$statusCopy) {
            $event->setOutput(_('Successfully copied files for Engine.'));
        }
    }
}
