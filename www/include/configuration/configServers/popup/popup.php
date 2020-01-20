<?php
/*
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

require_once realpath(__DIR__ . "/../../../../../config/centreon.config.php");
require_once _CENTREON_PATH_ . 'www/class/centreonDB.class.php';
require_once _CENTREON_PATH_ . 'bootstrap.php';
require_once _CENTREON_PATH_ . "www/include/common/common-Func.php";

$pearDB = $dependencyInjector['configuration_db'];

$tpl = new Smarty();
$tpl = initSmartyTpl(null, $tpl);

// get remote server ip
$query = 'SELECT ip FROM remote_servers';
$dbResult = $pearDB->query($query);
$remotesServerIPs = $dbResult->fetchAll(PDO::FETCH_COLUMN);
$dbResult->closeCursor();

//get poller informations
$query = '
SELECT ng.`id`, ng.`name`, ng.`gorgone_port`, ng.`ns_ip_address`, ng.`localhost`, cn.`command_file`
FROM  `nagios_server` ng, cfg_nagios cn
WHERE cn.`nagios_id` = ng.`id` 
AND ng.`id` =' . (int)$_GET['id'];

$dbResult = $pearDB->query($query);
$server = $dbResult->fetch();

$tpl->assign('serverIp', $server['ns_ip_address']);

if (in_array($server['ns_ip_address'], $remotesServerIPs)) {
    //config for remote
    $config = 'name: gorgoned-' . $server['name'] . '
description: Configuration for remote server ' . $server['name'] . '
gorgonecore:
  id: ' . $server['id'] . '
  external_com_type: tcp
  external_com_path: "*:' . $server['gorgone_port'] . '"
  authorized_clients:
    - key: cS4B3lZq96qcP4FTMhVMuwAhztqRBQERKyhnEitnTFM 
  privkey: "/var/spool/centreon/.gorgone/rsakey.priv.pem"
  pubkey: "/var/spool/centreon/.gorgone/rsakey.pub.pem"
modules:
  - name: action
    package: gorgone::modules::core::action::hooks
    enable: true

  - name: proxy
    package: gorgone::modules::core::proxy::hooks
    enable: true

  - name: legacycmd
    package: gorgone::modules::centreon::legacycmd::hooks
    enable: true
    cmd_file: "/var/lib/centreon/centcore.cmd"
    cache_dir: "/var/cache/centreon/"
    cache_dir_trap: "/etc/snmp/centreon_traps/"
    remote_dir: "/var/lib/centreon/remote-data/"

  - name: engine
    package: gorgone::modules::centreon::engine::hooks
    enable: true
    command_file: "' . $server['command_file'] . '"

';
} else {
    //config for poller
    $config = 'name:  gorgoned-' . $server['name'] . '
description: Configuration for poller ' . $server['name'] . '
gorgonecore:
  id: ' . $server['id'] . '
  external_com_type: tcp
  external_com_path: "*:' . $server['gorgone_port'] . '"
  authorized_clients:
    - key: cS4B3lZq96qcP4FTMhVMuwAhztqRBQERKyhnEitnTFM 
  privkey: "/var/spool/centreon/.gorgone/rsakey.priv.pem"
  pubkey: "/var/spool/centreon/.gorgone/rsakey.pub.pem"
modules:
  - name: action
    package: gorgone::modules::core::action::hooks
    enable: true

  - name: engine
    package: gorgone::modules::centreon::engine::hooks
    enable: true
    command_file: "' . $server['command_file'] . '"
';
}

$args = json_encode($server, JSON_PRETTY_PRINT);

$tpl->assign('args', $config);
$tpl->display("popup.ihtml");
