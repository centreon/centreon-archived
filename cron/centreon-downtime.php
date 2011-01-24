#!/usr/bin/php
<?php
/**
 * Copyright 2005-2011 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL: $
 * SVN : $Id: $
 *
 */

/* Configuration file */
$centreonConf = "@CENTREON_ETC@/centreon.conf.php";
$delay = 600; /* Default 10 minutes */

/* Does not modified after */

/**
 * Send external command to nagios or centcore 
 * 
 * @param CentreonDb $db The database connection to centreon
 * @param int $host_id The host id for command
 * @param string $cmd The command to send
 * @return The command return code
 */
function sendCommand($db, $host_id, $cmd)
{
	$varlib = "@CENTREON_VARLIB@";
 	if ($varlib == "" || $varlib == "@CENTREON_VARLIB@") {
 		$varlib = "/var/lib/centreon";
 	}
	
	$query = "SELECT ns.localhost, ns.id, cn.command_file
		FROM cfg_nagios cn, nagios_server ns, ns_host_relation nsh
		WHERE cn.nagios_server_id = ns.id AND nsh.nagios_server_id = ns.id AND host_host_id = " .$host_id;
	$res = $db->query($query);
	if (PEAR::isError($res)) {
		return false;
	}
	$row = $res->fetchRow();
	if ($row['localhost'] == 1) {
		$str_cmd = "echo '" . $cmd . "' >> " . $row['command_file'];
	} else {
		$str_cmd = "echo 'EXTERNALCMD:" . $row['id'] . ":" . $cmd . "' >> " . $varlib . "/centcore.cmd";
	}
	passthru(trim($str_cmd), $return);
	return $return;
}

/* Test if Centreon configuration file exists */ 
if (false === file_exists($centreonConf)) {
	file_put_contents('php://stderr', "The configuration file does not exists.");
	exit(1);
}

require_once $centreonConf;

$centreonClasspath = $centreon_path . 'www/class';

/* Include class */
require_once $centreonClasspath . '/centreonDB.class.php';
require_once $centreonClasspath . '/centreonGMT.class.php';
require_once $centreonClasspath . '/centreonHost.class.php';
require_once $centreonClasspath . '/centreonHostgroups.class.php';
require_once $centreonClasspath . '/centreonServicegroups.class.php';
require_once $centreonClasspath . '/centreonDowntime.class.php';
/* Connector to centreon DB*/
$pearDB = new CentreonDB();
/* GMT Management*/
$gmt = new CentreonGMT($pearDB);

/* Get broker */
$res = $pearDB->query("SELECT `value` FROM options WHERE `key` = 'broker'");
if (PEAR::isError($res)) {
	file_put_contents('php://stderr', "Error to connection in database.");
	exit(1);
}
$row = $res->fetchRow();
$broker = $row['value'];
/* Cache path */
$res = $pearDB->query("SELECT `value` FROM options WHERE `key` = 'batch_cache_path'");
if (PEAR::isError($res)) {
	file_put_contents('php://stderr', "Error to connection in database.");
	exit(1);
}
$row = $res->fetchRow();
$cache_path = $row['value'];

/* Initialize the downtime class with broker */
if (!file_exists($centreonClasspath . '/centreonDowntime.' . $broker . '.class.php')) {
	file_put_contents('php://stderr', "The broker class does not exists.\n");
	exit(1);
}
require_once $centreonClasspath . '/centreonDowntime.' . $broker . '.class.php';
$classname = "CentreonDowntime" . $broker;
$downtime = new $classname($pearDB);

/* Get the list of all downtime*/
$list = $downtime->getDowntime();
/* Get the list of scheduled donwtime */
$downtime_scheduled = $downtime->getSchedDowntime();

/* Template for external command by object type */
$ext_cmd_add['host'][] = '[%u] SCHEDULE_HOST_DOWNTIME;%s;%u;%u;%u;0;%u;Downtime cycle;[Downtime cycle #%u]';
$ext_cmd_add['host'][] = '[%u] SCHEDULE_HOST_SVC_DOWNTIME;%s;%u;%u;%u;0;%u;Downtime cycle;[Downtime cycle #%u]';
$ext_cmd_del['host'][] = '[%u] DEL_HOST_DOWNTIME;%u';
/*$ext_cmd_add['hostgrp'][] = '[%u] SCHEDULE_HOSTGROUP_HOST_DOWNTIME;%s;%u;%u;%u;0;%u;Downtime cycle;[Downtime cycle #%u]';
$ext_cmd_add['hostgrp'][] = '[%u] SCHEDULE_HOSTGROUP_SVC_DOWNTIME;%s;%u;%u;%u;0;%u;Downtime cycle;[Downtime cycle #%u]';
$ext_cmd_del['hostgrp'][] = '[%u] DEL_HOST_DOWNTIME;%u';
$ext_cmd_del['hostgrp'][] = '[%u] DEL_SVC_DOWNTIME;%u';*/
$ext_cmd_add['svc'][] = '[%u] SCHEDULE_SVC_DOWNTIME;%s;%s;%u;%u;%u;0;%u;Downtime cycle;[Downtime cycle #%u]';
$ext_cmd_del['svc'][] = '[%u] DEL_SVC_DOWNTIME;%u';
/*$ext_cmd_add['svcgrp'][] = '[%u] SCHEDULE_SVCGROUP_SVC_DOWNTIME;%s;%u;%u;%u;0;%u;Downtime cycle;[Downtime cycle #%u]';
$ext_cmd_del['svcgrp'][] = '[%u] DEL_HOST_DOWNTIME;%u';
$ext_cmd_del['svcgrp'][] = '[%u] DEL_SVC_DOWNTIME;%u';*/

// @todo factorize
foreach ($list as $type => $periods) {
	foreach ($periods as $period) {
		switch ($type) {
			case 'host':
				$currentHostDate = $gmt->getHostCurrentDatetime($period['obj_id'], 'U');
				$dts = $downtime->doSchedule($period['dt_id'], $currentHostDate, $delay);
				if (count($dts) != 0) {
					$listSchedDt = $downtime->isScheduled($period['dt_id'], $period['obj_name']);
					foreach ($dts as $dt) {
						if ($period['dt_activate'] == 1 && count($listSchedDt) == 0) {
							foreach ($ext_cmd_add['host'] as $cmd) {
								$cmd = sprintf($cmd, time(), $period['obj_name'], $dt[0], $dt[1], $period['dtp_fixed'], $period['dtp_duration'], $period['dt_id']);
								sendCommand($pearDB, $period['obj_id'], $cmd);
							}
						} elseif ($period['dt_activate'] == 0 && count($listSchedDt) != 0) {
							foreach ($listSchedDt as $schelDt) {
								if ($schelDt['downtime_type'] == 1) {
									$cmd = sprintf('[%u] DEL_HOST_DOWNTIME;%u', time(), $schelDt['internal_downtime_id']);
									sendCommand($pearDB, $period['obj_id'], $cmd);
								} elseif ($schelDt['downtime_type'] == 2) {
									$cmd = sprintf('[%u] DEL_SVC_DOWNTIME;%u', time(), $schelDt['internal_downtime_id']);
									sendCommand($pearDB, $period['obj_id'], $cmd);
								}
							}
						}
					}
				} 
				break;
			case 'hostgrp':
				$hg = new CentreonHostgroups($pearDB);
				$hostClass = new CentreonHost($pearDB);
				$hostlist = $hg->getHostGroupHosts($period['obj_id']);
				//if ($gmt->used()) {
				foreach ($hostlist as $host) {
					$currentHostDate = $gmt->getHostCurrentDatetime($host, 'U');
					$dts = $downtime->doSchedule($period['dt_id'], $currentHostDate, $delay);
					if (count($dts) != 0) {
						$listSchedDt = $downtime->isScheduled($period['dt_id'], $host);
						foreach ($dts as $dt) {
							if ($period['dt_activate'] == 1 && count($listSchedDt) == 0) {
								foreach ($ext_cmd_add['host'] as $cmd) {
									$cmd = sprintf($cmd, time(), $hostClass->getHostName($host), $dt[0], $dt[1], $period['dtp_fixed'], $period['dtp_duration'], $period['dt_id']);
									sendCommand($pearDB, $host, $cmd);
								}
							} elseif ($period['dt_activate'] == 0 && count($listSchedDt) != 0) {
								foreach ($listSchedDt as $schelDt) {
									if ($schelDt['downtime_type'] == 1) {
										$cmd = sprintf('[%u] DEL_HOST_DOWNTIME;%u', time(), $schelDt['internal_downtime_id']);
										sendCommand($pearDB, $host, $cmd);
									} elseif ($schelDt['downtime_type'] == 2) {
										$cmd = sprintf('[%u] DEL_SVC_DOWNTIME;%u', time(), $schelDt['internal_downtime_id']);
										sendCommand($pearDB, $host, $cmd);
									}
								}
							}
						}
					}		
				}
				/*} else {
					$dts = $downtime->doSchedule($period['dt_id'], time(), $delay);
					foreach ($dts as $dt) {
						foreach ($ext_cmd_add['hostgrp'] as $cmd) {
							$cmd = sprintf($cmd, time(), $period['obj_name'], $dt[0], $dt[1], $period['dtp_fixed'], $period['dtp_duration'], $period['dt_id']);
							sendCommand($pearDB, $host, $cmd);
						}
					}
				}*/
				break;
			case 'srv':
				$hostClass = new CentreonHost($pearDB);
				$hid = $hostClass->getHostId($period['host_name']);
				if ($gmt->used()) {
					$currentHostDate = $gmt->getHostCurrentDatetime($hid, 'U');
					$dts = $downtime->doSchedule($period['dt_id'], $currentHostDate, $delay);
					if (count($dts) != 0) {
						$listSchedDt = $downtime->isScheduled($period['dt_id'], $period['host_name'], $period['obj_name']);
						foreach ($dts as $dt) {
							if ($period['dt_activate'] == 1 && count($listSchedDt) == 0) {
								foreach ($ext_cmd_add['svc'] as $cmd) {
									$cmd = sprintf($cmd, time(), $period['host_name'], $period['obj_name'], $dt[0], $dt[1], $period['dtp_fixed'], $period['dtp_duration'], $period['dt_id']);
									sendCommand($pearDB, $hid, $cmd);
								}
							} elseif ($period['dt_activate'] == 0 && count($listSchedDt) != 0) {
								foreach ($listSchedDt as $schelDt) {
									if ($schelDt['downtime_type'] == 1) {
										$cmd = sprintf('[%u] DEL_HOST_DOWNTIME;%u', time(), $schelDt['internal_downtime_id']);
										sendCommand($pearDB, $hid, $cmd);
									} elseif ($schelDt['downtime_type'] == 2) {
										$cmd = sprintf('[%u] DEL_SVC_DOWNTIME;%u', time(), $schelDt['internal_downtime_id']);
										sendCommand($pearDB, $hid, $cmd);
									}
								}
							}
						}
					}
				} else {
					$dts = $downtime->doSchedule($period['dt_id'], time(), $delay);
					if (count($dts) != 0) {
						$listSchedDt = $downtime->isScheduled($period['dt_id'], $period['host_name'], $period['obj_name']);
						foreach ($dts as $dt) {
							if ($period['dt_activate'] == 1 && count($listSchedDt) == 0) {
								foreach ($ext_cmd_add['svc'] as $cmd) {
									$cmd = sprintf($cmd, time(), $period['host_name'], $period['obj_name'], $dt[0], $dt[1], $period['dtp_fixed'], $period['dtp_duration'], $period['dt_id']);
									sendCommand($pearDB, $hid, $cmd);
								}
							} elseif ($period['dt_activate'] == 0 && count($listSchedDt) != 0) {
								foreach ($listSchedDt as $schelDt) {
									if ($schelDt['downtime_type'] == 1) {
										$cmd = sprintf('[%u] DEL_HOST_DOWNTIME;%u', time(), $schelDt['internal_downtime_id']);
										sendCommand($pearDB, $hid, $cmd);
									} elseif ($schelDt['downtime_type'] == 2) {
										$cmd = sprintf('[%u] DEL_SVC_DOWNTIME;%u', time(), $schelDt['internal_downtime_id']);
										sendCommand($pearDB, $hid, $cmd);
									}
								}
							}
						}
					}
				}
				break;
			case 'svcgrp':
				$sg = new CentreonServicegroups($pearDB);
				$hostClass = new CentreonHost($pearDB);
				$serviceClass = new CentreonService($pearDB);
				$services = $sg->getServiceGroupServices($period['obj_id']);
				foreach ($services as $service){
					$currentHostDate = $gmt->getHostCurrentDatetime($service['host_host_id'], 'U');
					$dts = $downtime->doSchedule($period['dt_id'], $currentHostDate, $delay);
					if (count($dts) != 0) {
						$host_name = $hostClass->getHostName($service['host_host_id']);
						$service_name = $serviceClass->getServiceName($service['service_service_id']);
						$listSchedDt = $downtime->isScheduled($period['dt_id'], $host_name, $service_name);
						foreach ($dts as $dt) {
							if ($period['dt_activate'] == 1 && count($listSchedDt) == 0) {
								foreach ($ext_cmd_add['svc'] as $cmd) {
									$cmd = sprintf($cmd, time(), $host_name, $service_name, $dt[0], $dt[1], $period['dtp_fixed'], $period['dtp_duration'], $period['dt_id']);
									sendCommand($pearDB, $service['host_host_id'], $cmd);
								}
							} elseif ($period['dt_activate'] == 0 && count($listSchedDt) != 0) {
								foreach ($listSchedDt as $schelDt) {
									if ($schelDt['downtime_type'] == 1) {
										$cmd = sprintf('[%u] DEL_HOST_DOWNTIME;%u', time(), $schelDt['internal_downtime_id']);
										sendCommand($pearDB, $service['host_host_id'], $cmd);
									} elseif ($schelDt['downtime_type'] == 2) {
										$cmd = sprintf('[%u] DEL_SVC_DOWNTIME;%u', time(), $schelDt['internal_downtime_id']);
										sendCommand($pearDB, $service['host_host_id'], $cmd);
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
?>