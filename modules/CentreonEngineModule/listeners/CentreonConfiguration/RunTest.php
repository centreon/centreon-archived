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
        $path = "{$tmpdir}/engine/{$pollerId}/centengine-testing.cfg";
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
