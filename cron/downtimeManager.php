#!/usr/bin/php
<?php
/**
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

define('_DELAY_', '600'); /* Default 10 minutes */

require_once realpath(dirname(__FILE__) . "/../config/centreon.config.php");
require_once _CENTREON_PATH_ . '/www/class/centreonDB.class.php';
require_once _CENTREON_PATH_ . '/www/class/centreonDowntime.Broker.class.php';

$unix_time = time();

$ext_cmd_add['host'] = array(
    '[%u] SCHEDULE_HOST_DOWNTIME;%s;%u;%u;%u;0;%u;Downtime cycle;[Downtime cycle #%u]',
    '[%u] SCHEDULE_HOST_SVC_DOWNTIME;%s;%u;%u;%u;0;%u;Downtime cycle;[Downtime cycle #%u]'
);

$ext_cmd_del['host'] = array(
    '[%u] DEL_HOST_DOWNTIME;%u'
);

$ext_cmd_add['svc'] = array(
    '[%u] SCHEDULE_SVC_DOWNTIME;%s;%s;%u;%u;%u;0;%u;Downtime cycle;[Downtime cycle #%u]'
);

$ext_cmd_del['svc'] = array(
    '[%u] DEL_SVC_DOWNTIME;%u'
);

/* Connector to centreon DB */
$pearDB = new CentreonDB();
$downtimeObj = new CentreonDowntimeBroker($pearDB, _CENTREON_VARLIB_);

/* Get approaching downtimes */
$downtimes = $downtimeObj->getApproachingDowntimes(_DELAY_);

foreach ($downtimes as $downtime) {

    $isScheduled = $downtimeObj->isScheduled($downtime);

    if (!$isScheduled && $downtime['dt_activate'] == '1') {
        $downtimeObj->insertCache($downtime);
        if ($downtime['service_id'] != '') {
            foreach ($ext_cmd_add['svc'] as $cmd) {
                $cmd = sprintf(
                    $cmd,
                    $unix_time,
                    $downtime['host_name'],
                    $downtime['service_description'],
                    $downtime['start_timestamp'],
                    $downtime['end_timestamp'],
                    $downtime['fixed'],
                    $downtime['duration'],
                    $downtime['dt_id']
                );
                $downtimeObj->setCommand($downtime['host_id'], $cmd);
            }
        } else {
            foreach ($ext_cmd_add['host'] as $cmd) {
                $cmd = sprintf(
                    $cmd,
                    $unix_time,
                    $downtime['host_name'],
                    $downtime['start_timestamp'],
                    $downtime['end_timestamp'],
                    $downtime['fixed'],
                    $downtime['duration'],
                    $downtime['dt_id']
                );
                $downtimeObj->setCommand($downtime['host_id'], $cmd);
            }
        }
    } else if ($isScheduled && $downtime['dt_activate'] == '0') {
        if ($downtime['service_id'] != '') {
            foreach ($ext_cmd_del['svc'] as $cmd) {
                $cmd = sprintf(
                    $cmd,
                    $unix_time,
                    $downtime['dt_id']
                );
                $downtimeObj->setCommand($downtime['host_id'], $cmd);
            }
        } else {
            foreach ($ext_cmd_del['host'] as $cmd) {
                $cmd = sprintf(
                    $cmd,
                    $unix_time,
                    $downtime['dt_id']
                );
                $downtimeObj->setCommand($downtime['host_id'], $cmd);
            }
        }
    }
}

# Send the external commands
$downtimeObj->sendCommands();

# Purge downtime cache
$downtimeObj->purgeCache();

