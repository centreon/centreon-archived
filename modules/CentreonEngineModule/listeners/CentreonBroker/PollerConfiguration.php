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

namespace CentreonEngine\Listeners\CentreonBroker;

use CentreonEngine\Models\Engine;
use CentreonMain\Events\Generic as GenericEvent;

/**
 * Listeners for event
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 * @subpackage CentreonBroker
 */
class PollerConfiguration
{

    /**
     * Execute the event
     *
     * @param \CentreonMain\Events\Generic $event The object for event
     */
    public static function execute(GenericEvent $event)
    {
        $input = $event->getInput();
        if (false === isset($input['poller_id'])) {
            throw new \InvalidArgumentException();
        }
        
        $pollerInformation = Engine::get($input['poller_id']);
        $delimiter = '';
        if (isset($input['delimiter'])) {
            $delimiter = $input['delimiter'];
        }
        $keys = array_map(
            function ($name) use ($delimiter) {
                return $delimiter . 'engine_' . $name . $delimiter;
            },
            array_keys($pollerInformation)
        );
        $values = array_combine($keys, array_values($pollerInformation));
        $event->setOutput($values);
    }
}
