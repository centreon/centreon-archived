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

/**
 * Configuration file
 */
require_once realpath(dirname(__FILE__) . "/../config/centreon.config.php");
define('_DELAY_', '600'); /* Default 10 minutes */

/* Does not modified after */

/* ***********************************************
 * Test if Centreon configuration file exists
 */
if (!defined("_CENTREON_PATH_")) {
    file_put_contents('php://stderr', "The configuration file does not exists.");
    exit(1);
}

$varlib = _CENTREON_VARLIB_;

$centreonClasspath = _CENTREON_PATH_ . '/www/class';

/* Include class */
require_once $centreonClasspath . '/centreonDB.class.php';
require_once $centreonClasspath . '/centreonGMT.class.php';
require_once $centreonClasspath . '/centreonHost.class.php';
require_once $centreonClasspath . '/centreonService.class.php';
require_once $centreonClasspath . '/centreonHostgroups.class.php';
require_once $centreonClasspath . '/centreonServicegroups.class.php';
require_once $centreonClasspath . '/centreonDowntime.class.php';

/*
 * Connector to centreon DB
 */
$pearDB = new CentreonDB();

/*
 * GMT Management
 */
$gmt = new CentreonGMT($pearDB);

/*
 * Delete empty Downtimes
 */
$pearDB->query("DELETE FROM `downtime` WHERE `dt_id` NOT IN (SELECT dt_id FROM downtime_host_relation)  AND `dt_id` NOT IN (SELECT dt_id FROM downtime_hostgroup_relation) AND `dt_id` NOT IN (SELECT dt_id FROM downtime_service_relation) AND `dt_id` NOT IN (SELECT dt_id FROM downtime_servicegroup_relation)");

/*
 * Initialize the downtime class with broker
 */
require_once $centreonClasspath . '/centreonDowntime.Broker.class.php';
$downtime = new CentreonDowntimeBroker($pearDB, $varlib);

/*
 * Get the list of all downtime
 */
$list = $downtime->getDowntime();

/*
 * Template for external command by object type
 */
$ext_cmd_add['host'][] = '[%u] SCHEDULE_HOST_DOWNTIME;%s;%u;%u;%u;0;%u;Downtime cycle;[Downtime cycle #%u]';
$ext_cmd_add['host'][] = '[%u] SCHEDULE_HOST_SVC_DOWNTIME;%s;%u;%u;%u;0;%u;Downtime cycle;[Downtime cycle #%u]';
$ext_cmd_del['host'][] = '[%u] DEL_HOST_DOWNTIME;%u';
$ext_cmd_add['svc'][] = '[%u] SCHEDULE_SVC_DOWNTIME;%s;%s;%u;%u;%u;0;%u;Downtime cycle;[Downtime cycle #%u]';
$ext_cmd_del['svc'][] = '[%u] DEL_SVC_DOWNTIME;%u';
$unix_time = time();

// @todo factorize
$existingDowntime = array();
foreach ($list as $type => $periods) {
    foreach ($periods as $period) {
        switch ($type) {
            case 'host':
                $currentHostDate = $gmt->getHostCurrentDatetime($period['obj_id']);

                $dts = $downtime->doSchedule(
                    $period['dt_id'],
                    $currentHostDate,
                    $period['dtp_start_time'],
                    $period['dtp_end_time']
                );

                if (count($dts) != 0) {
                    $listSchedDt = $downtime->isScheduled(
                        $period['dt_id'],
                        $period['obj_id'],
                        null,
                        $currentHostDate->getTimestamp()
                    );

                    foreach ($dts as $dt) {
                        if ($period['dt_activate'] == 1 && count($listSchedDt) == 0) {
                            foreach ($ext_cmd_add['host'] as $cmd) {
                                $cmd = sprintf(
                                    $cmd,
                                    $unix_time,
                                    $period['obj_name'],
                                    strtotime($dt[0]),
                                    strtotime($dt[1]),
                                    $period['dtp_fixed'],
                                    $period['dtp_duration'],
                                    $period['dt_id']
                                );

                                if (!in_array($cmd, $existingDowntime)) {
                                    $downtime->setCommand($period['obj_id'], $cmd);
                                    $existingDowntime[] = $cmd;
                                }
                            }
                        } elseif ($period['dt_activate'] == 0 && count($listSchedDt) != 0) {
                            foreach ($listSchedDt as $schelDt) {
                                if ($schelDt['downtime_type'] == 1) {
                                    $cmd = sprintf('[%u] DEL_HOST_DOWNTIME;%u', $unix_time, $schelDt['internal_downtime_id']);
                                } elseif ($schelDt['downtime_type'] == 2) {
                                    $cmd = sprintf('[%u] DEL_SVC_DOWNTIME;%u', $unix_time, $schelDt['internal_downtime_id']);
                                }

                                if (!in_array($cmd, $existingDowntime)) {
                                    $downtime->setCommand($period['obj_id'], $cmd);
                                    $existingDowntime[] = $cmd;
                                }
                            }
                        }
                    }
                }
                break;
            case 'hostgrp':
                if (!isset($hg)) {
                    $hg = new CentreonHostgroups($pearDB);
                }

                if (!isset($hostClass)) {
                    $hostClass = new CentreonHost($pearDB);
                }

                $hostlist = $hg->getHostGroupHosts($period['obj_id']);

                foreach ($hostlist as $host) {
                    $currentHostDate = $gmt->getHostCurrentDatetime($host);

                    $dts = $downtime->doSchedule(
                        $period['dt_id'],
                        $currentHostDate,
                        $period['dtp_start_time'],
                        $period['dtp_end_time']
                    );

                    if (count($dts) != 0) {
                        $listSchedDt = $downtime->isScheduled(
                            $period['dt_id'],
                            $host,
                            null,
                            $currentHostDate->getTimestamp()
                        );
                        foreach ($dts as $dt) {
                            if ($period['dt_activate'] == 1 && count($listSchedDt) == 0) {
                                foreach ($ext_cmd_add['host'] as $cmd) {
                                    $cmd = sprintf(
                                        $cmd,
                                        $unix_time,
                                        $hostClass->getHostName($host),
                                        strtotime($dt[0]),
                                        strtotime($dt[1]),
                                        $period['dtp_fixed'],
                                        $period['dtp_duration'],
                                        $period['dt_id']
                                    );

                                    if (!in_array($cmd, $existingDowntime)) {
                                        $downtime->setCommand($host, $cmd);
                                        $existingDowntime[] = $cmd;
                                    }
                                }
                            } elseif ($period['dt_activate'] == 0 && count($listSchedDt) != 0) {
                                foreach ($listSchedDt as $schelDt) {
                                    if ($schelDt['downtime_type'] == 1) {
                                        $cmd = sprintf('[%u] DEL_HOST_DOWNTIME;%u', $unix_time, $schelDt['internal_downtime_id']);
                                    } elseif ($schelDt['downtime_type'] == 2) {
                                        $cmd = sprintf('[%u] DEL_SVC_DOWNTIME;%u', $unix_time, $schelDt['internal_downtime_id']);
                                    }
                                    if (!in_array($cmd, $existingDowntime)) {
                                        $downtime->setCommand($host, $cmd);
                                        $existingDowntime[] = $cmd;
                                    }
                                }
                            }
                        }
                    }
                }
                break;
            case 'svc':
                if (!isset($hostClass)) {
                    $hostClass = new CentreonHost($pearDB);
                }

                $hid = $hostClass->getHostId($period['host_name']);
                $currentHostDate = $gmt->getHostCurrentDatetime($hid);

                $dts = $downtime->doSchedule(
                    $period['dt_id'],
                    $currentHostDate,
                    $period['dtp_start_time'],
                    $period['dtp_end_time']
                );

                if (count($dts) != 0) {
                    $listSchedDt = $downtime->isScheduled(
                        $period['dt_id'],
                        $period['host_id'],
                        $period['obj_id'],
                        $currentHostDate->getTimestamp()
                    );
                    foreach ($dts as $dt) {
                        if ($period['dt_activate'] == 1 && count($listSchedDt) == 0) {
                            foreach ($ext_cmd_add['svc'] as $cmd) {
                                $cmd = sprintf(
                                    $cmd,
                                    $unix_time,
                                    $period['host_name'],
                                    $period['obj_name'],
                                    strtotime($dt[0]),
                                    strtotime($dt[1]),
                                    $period['dtp_fixed'],
                                    $period['dtp_duration'],
                                    $period['dt_id']
                                );

                                if (!in_array($cmd, $existingDowntime)) {
                                    $downtime->setCommand($period['host_id'], $cmd);
                                    $existingDowntime[] = $cmd;
                                }
                            }
                        } elseif ($period['dt_activate'] == 0 && count($listSchedDt) != 0) {
                            foreach ($listSchedDt as $schelDt) {
                                if ($schelDt['downtime_type'] == 1) {
                                    $cmd = sprintf('[%u] DEL_HOST_DOWNTIME;%u', $unix_time, $schelDt['internal_downtime_id']);
                                } elseif ($schelDt['downtime_type'] == 2) {
                                    $cmd = sprintf('[%u] DEL_SVC_DOWNTIME;%u', $unix_time, $schelDt['internal_downtime_id']);
                                }
                                if (!in_array($cmd, $existingDowntime)) {
                                    $downtime->setCommand($period['host_id'], $cmd);
                                    $existingDowntime[] = $cmd;
                                }
                            }
                        }
                    }
                }
                break;
            case 'svcgrp':
                if (!isset($sg)) {
                    $sg = new CentreonServicegroups($pearDB);
                }

                if (!isset($hostClass)) {
                    $hostClass = new CentreonHost($pearDB);
                }

                if (!isset($serviceClass)) {
                    $serviceClass = new CentreonService($pearDB);
                }

                $services = $sg->getServiceGroupServices($period['obj_id']);
                foreach ($services as $service) {
                    if (!isset($service[0])) {
                        continue;
                    }

                    $currentHostDate = $gmt->getHostCurrentDatetime($service[0]);
                    $dts = $downtime->doSchedule(
                        $period['dt_id'],
                        $currentHostDate,
                        $period['dtp_start_time'],
                        $period['dtp_end_time']
                    );

                    if (count($dts) != 0) {
                        $host_name = $hostClass->getHostName($service[0]);
                        $service_name = $serviceClass->getServiceDesc($service[1]);
                        $listSchedDt = $downtime->isScheduled(
                            $period['dt_id'],
                            $service[0],
                            $service[1],
                            $currentHostDate->getTimestamp()
                        );
                        foreach ($dts as $dt) {
                            if ($period['dt_activate'] == 1 && count($listSchedDt) == 0) {
                                foreach ($ext_cmd_add['svc'] as $cmd) {
                                    $cmd = sprintf(
                                        $cmd,
                                        $unix_time,
                                        $host_name,
                                        $service_name,
                                        strtotime($dt[0]),
                                        strtotime($dt[1]),
                                        $period['dtp_fixed'],
                                        $period['dtp_duration'],
                                        $period['dt_id']
                                    );

                                    if (!in_array($cmd, $existingDowntime)) {
                                        $downtime->setCommand($service[0], $cmd);
                                        $existingDowntime[] = $cmd;
                                    }
                                }
                            } elseif ($period['dt_activate'] == 0 && count($listSchedDt) != 0) {
                                foreach ($listSchedDt as $schelDt) {
                                    if ($schelDt['downtime_type'] == 1) {
                                        $cmd = sprintf('[%u] DEL_HOST_DOWNTIME;%u', $unix_time, $schelDt['internal_downtime_id']);
                                    } elseif ($schelDt['downtime_type'] == 2) {
                                        $cmd = sprintf('[%u] DEL_SVC_DOWNTIME;%u', $unix_time, $schelDt['internal_downtime_id']);
                                    }

                                    if (!in_array($cmd, $existingDowntime)) {
                                        $downtime->setCommand($service[0], $cmd);
                                        $existingDowntime[] = $cmd;
                                    }
                                }
                            }
                        }
                    }
                }
                break;
        }
    }
}

# Send the external commands
$downtime->sendCommands();
