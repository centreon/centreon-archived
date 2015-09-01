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
use CentreonConfiguration\Events\RunTest as RunTestEvent;

class RunTest
{
    /**
     *
     * @param \CentreonConfiguration\Events\RunTest $event
     */
    public static function execute(RunTestEvent $event)
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $tmpdir = $di->get('config')->get('global', 'centreon_generate_tmp_dir');

        $pollerId = $event->getPollerId();
        $enginePath = '/usr/sbin/centengine';
        $path = "{$tmpdir}/engine/generate/{$pollerId}/centengine-testing.cfg";
        $command = "sudo {$enginePath} -v $path 2>&1";
        exec($command, $output, $status);
        if ($status == 0) {
            // We are only selecting warning/errors here
            // Colors/formatting is performed either in Command (centreonConsole) or JS (web => API thru JSON)
            foreach ($output as $out) {
                if (preg_match("/warning|error/i", $out)) {
                    $out = preg_replace("/\[\d+\] /", "", $out);
                    $event->setOutput($out);
                }
            }
        } else {
            $event->setOutput('Error while executing test command');
            foreach ($output as $out) {
                $event->setOutput($out);
            }
        }
    }
}
