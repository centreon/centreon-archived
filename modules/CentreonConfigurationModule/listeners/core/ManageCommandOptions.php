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

namespace CentreonConfiguration\Listeners\Core;

use Centreon\Events\ManageCommandOptions as ManageCommandOptionsEvent;
use CentreonConfiguration\Repository\PollerRepository;
use CentreonConfiguration\Models\Poller;

class ManageCommandOptions
{
    /**
     * @param Core\Events\ManageCommandOptions $event
     */
    public static function execute(ManageCommandOptionsEvent $event)
    {
        $options = $event->getOptions();
        $args = $event->getArgs();
        $newInfos = array();
        if ($event->getObjectName() == 'poller') {
            if (($event->getAction() == 'createAction') && isset($args['template'])) {
                $newInfos = PollerRepository::addCommandTemplateInfos($args['template']);
            } else if (($event->getAction() == 'updateAction') && isset($args['poller'])) {
                $pollerIds = Poller::getIdByParameter('slug', array($args['poller']));
                if (isset($pollerIds[0])) {
                    $pollerId = $pollerIds[0];
                    $templateName = Poller::getParameters($pollerId, 'tmpl_name');
                    $newInfos = PollerRepository::addCommandTemplateInfos($templateName['tmpl_name']);
                }
            }
        }

        foreach ($newInfos as $newInfo) {
            $newOption = array(
                $newInfo['name'] => array(
                    'functionParams' => 'params',
                    'help' => '',
                    'type' => 'string',
                    'toTransform' => $newInfo['name'],
                    'multiple' => '',
                    'required' => '0',
                )
            );
            if (isset($newInfo['help'])) {
                $newOption[$newInfo['name']]['help'] = $newInfo['help'];
            }
            if (isset($newInfo['require']) && ($newInfo['require'] == true) && ($event->getAction() == 'createAction')) {
                $newOption[$newInfo['name']]['required'] = '1';
            }
            $event->addOption($newOption);
        }
    }
}
