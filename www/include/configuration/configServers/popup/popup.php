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
require_once _CENTREON_PATH_ . "/www/class/centreonRestHttp.class.php";

$pearDB = $dependencyInjector['configuration_db'];

$tpl = new Smarty();
$tpl = initSmartyTpl(null, $tpl);

// get remote server ip
$query = 'SELECT ip FROM remote_servers';
$dbResult = $pearDB->query($query);
$remotesServerIPs = $dbResult->fetchAll(PDO::FETCH_COLUMN);
$dbResult->closeCursor();

//get poller informations
$query = "
SELECT ns.`id`, ns.`name`, ns.`gorgone_port`, ns.`ns_ip_address`, ns.`localhost`,ns.remote_id, 
cn.`command_file`, GROUP_CONCAT( pr.`remote_server_id` ) AS list_remote_server_id , 
CASE
    WHEN (ns.localhost = '1') THEN 'central'
    WHEN (rs.id > '0') THEN 'remote'
    ELSE 'poller' 
END  AS poller_type 
FROM nagios_server AS ns 
LEFT JOIN remote_servers AS rs ON (rs.ip = ns.ns_ip_address) 
LEFT JOIN cfg_nagios AS cn ON (cn.`nagios_id` = ns.`id` ) 
LEFT JOIN rs_poller_relation AS pr ON (pr.`poller_server_id` = ns.`id` ) 
WHERE ns.ns_activate = '1' 
AND ns.`id` =" . (int)$_GET['id'];

$dbResult = $pearDB->query($query);
$server = $dbResult->fetch();

//get gorgone api informations
$gorgoneApi = array();
$dbResult = $pearDB->query('SELECT * from options WHERE `key` LIKE "gorgone%"');
while ( $row = $dbResult->fetch()){
    $gorgoneApi[$row['key']] = $row['value'];
}

$tpl->assign('serverIp', $server['ns_ip_address']);
if (empty($server['remote_id']) && empty($server['list_remote_server_id'])) {
    //parent is the central
    $query = "SELECT `id` FROM nagios_server WHERE ns_activate = '1' AND localhost = '1'";
    $dbResult = $pearDB->query($query);
    $parents = $dbResult->fetchAll(\PDO::FETCH_COLUMN);
} else {
    $parents = array($server['remote_id']);
    if (!empty($server['list_remote_server_id'])) {
        $remote = explode(',', $server['list_remote_server_id']);
        $parents = array_merge($parents, $remote);
    }
}

$tokens = array();
foreach ($parents as $serverId) {

    $url = $gorgoneApi['gorgone_api_ssl'] == 1 ? 'https' : 'http' . '://' .
        $gorgoneApi['gorgone_api_address'] . ':' . $gorgoneApi['gorgone_api_port'] .
        '/api/nodes/' . $serverId . '/internal/thumbprint';
    try {
        $curl = new \CentreonRestHttp;
        $tokens[$serverId] = $curl->call(
            $url
        );
    } catch (\Exception $e) {
        echo date("Y-m-d H:i:s") . " - ERROR - Error while creating parent task on "
            . $url . ".\n";
        echo date("Y-m-d H:i:s") . " - ERROR - Error message: " . $e->getMessage() . "\n";
    }
}

$chrono = 60;
$thumbprintsData = array();

foreach ($tokens as $serverId => $token) {
    $url = $gorgoneApi['gorgone_api_ssl'] == 1 ? 'https' : 'http' . '://' .
        $gorgoneApi['gorgone_api_address'] . ':' . $gorgoneApi['gorgone_api_port'] .
        '/api/nodes/' . $serverId . '/log/' . $token["token"];
    $error = true;
    while (($chrono > 0) && $error) {
        try {
            $curl = new \CentreonRestHttp;
            $log = $curl->call(
                $url
            );
            if (!isset($log["error"])) {
                $thumbprintsData[] = $log;
                $error = false;
            }
        } catch (\Exception $e) {
            echo date("Y-m-d H:i:s") . " - ERROR - Error while creating parent task on " . $url . ".\n";
            echo date("Y-m-d H:i:s") . " - ERROR - Error message: " . $e->getMessage() . "\n";
        }
        sleep(2);
        $chrono -= 2;
    }
}

$thumbprints = '';
$dataError = '';
$gorgoneError = false;
foreach ($thumbprintsData as $data) {
    if (json_decode($data["data"][0]["data"])->message == 'ok') {
        $thumbprints .= '
    - key: ' . json_decode($data["data"][0]["data"])->data->thumbprint;
    } else {
        $gorgoneError = true;
        $dataError .= '
    - error : ' . json_decode($data["data"][0]["data"])->message;
    }
}

if (!empty($dataError)) {
    $config = $dataError;
} elseif (in_array($server['ns_ip_address'], $remotesServerIPs)) {
    //config for remote
    $config = 'name: gorgoned-' . $server['name'] . '
description: Configuration for remote server ' . $server['name'] . '
gorgonecore:
  id: ' . $server['id'] . '
  external_com_type: tcp
  external_com_path: "*:' . $server['gorgone_port'] . '"
  authorized_clients: ' . $thumbprints . '
  privkey: "/var/spool/centreon/.gorgone/rsakey.priv.pem"
  pubkey: "/var/spool/centreon/.gorgone/rsakey.pub.pem"
modules:
  - name: action
    package: gorgone::modules::core::action::hooks
    enable: true
    
  - name: nodes
    package: gorgone::modules::core::nodes::hooks
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
  authorized_clients: ' . $thumbprints . '
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
$tpl->assign('gorgoneError', $gorgoneError);
$tpl->display("popup.ihtml");
