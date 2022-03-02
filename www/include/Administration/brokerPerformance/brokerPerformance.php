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

if (!isset($centreon)) {
    exit();
}

require_once "./include/monitoring/common-Func.php";
require_once "./class/centreonDB.class.php";
require_once "./class/centreonGMT.class.php";
require_once realpath(dirname(__FILE__) . "/../../../../config/centreon.config.php");

function createArrayStats($arrayFromJson)
{
    $io = array('class' => 'stats_lv1');

    if (isset($arrayFromJson['state'])) {
        $io[_('State')]['value'] = $arrayFromJson['state'];
        if ($arrayFromJson['state'] == "disconnected") {
            $io[_('State')]['class'] = "badge service_critical";
        } elseif (
            $arrayFromJson['state'] == "listening"
            || $arrayFromJson['state'] == "connected"
            || $arrayFromJson['state'] == "connecting"
        ) {
            $io[_('State')]['class'] = "badge service_ok";
        } elseif ($arrayFromJson['state'] == "sleeping" || $arrayFromJson['state'] == "blocked") {
            $io[_('State')]['class'] = "badge service_warning";
        }
    }

    if (isset($arrayFromJson['status']) && $arrayFromJson['status']) {
        $io[_('Status')] = array('value' => $arrayFromJson['status'], 'isTimestamp' => false);
    }

    if (isset($arrayFromJson['last_event_at']) && $arrayFromJson['last_event_at'] != -1) {
        $io[_('Last event at')] = array('value' => $arrayFromJson['last_event_at'], 'isTimestamp' => true);
    }

    if (isset($arrayFromJson['last_connection_attempt']) && $arrayFromJson['last_connection_attempt'] != -1) {
        $io[_('Last connection attempt')] = array(
            'value' => $arrayFromJson['last_connection_attempt'],
            'isTimestamp' => true
        );
    }

    if (isset($arrayFromJson['last_connection_success']) && $arrayFromJson['last_connection_success'] != -1) {
        $io[_('Last connection success')] = array(
            'value' => $arrayFromJson['last_connection_success'],
            'isTimestamp' => true
        );
    }

    if (isset($arrayFromJson['one_peer_retention_mode'])) {
        $io[_('One peer retention mode')] = array(
            'value' => $arrayFromJson['one_peer_retention_mode'],
            'isTimestamp' => false
        );
    }

    if (isset($arrayFromJson['event_processing_speed'])) {
        $io[_('Event processing speed')] = array(
            'value' => sprintf("%.2f events/s", $arrayFromJson['event_processing_speed']),
            'isTimestamp' => false
        );
    }

    if (
        isset($arrayFromJson['queue file'])
        && isset($arrayFromJson['queue file enabled'])
        && $arrayFromJson['queue file enabled'] != "no"
    ) {
        $io[_('Queue file')] = array(
            'value' => $arrayFromJson['queue file'],
            'isTimestamp' => false
        );
    }

    if (isset($arrayFromJson['queue file enabled'])) {
        $io[_('Queued file enabled')] = array('value' => $arrayFromJson['queue file enabled'], 'isTimestamp' => false);
    }

    if (isset($arrayFromJson['queued_events'])) {
        $io[_('Queued events')] = array('value' => $arrayFromJson['queued_events'], 'isTimestamp' => false);
    }

    if (isset($arrayFromJson['memory file'])) {
        $io[_('Memory file')] = array('value' => $arrayFromJson['memory file'], 'isTimestamp' => false);
    }

    if (isset($arrayFromJson['read_filters']) && $arrayFromJson['read_filters']) {
        if ($arrayFromJson['read_filters'] != 'all') {
            $io[_('Input accepted events type')] = array(
                'value' => substr($arrayFromJson['read_filters'], 22),
                'isTimestamp' => false
            );
        } else {
            $io[_('Input accepted events type')] = array(
                'value' => $arrayFromJson['read_filters'],
                'isTimestamp' => false
            );
        }
    }

    if (isset($arrayFromJson['write_filters']) && $arrayFromJson['write_filters']) {
        if ($arrayFromJson['write_filters'] != 'all') {
            $io[_('Output accepted events type')] = array(
                'value' => substr($arrayFromJson['write_filters'], 2),
                'isTimestamp' => false
            );
        } else {
            $io[_('Output accepted events type')] = array(
                'value' => $arrayFromJson['write_filters'],
                'isTimestamp' => false
            );
        }
    }

    return $io;
}

function parseStatsFile($statfile)
{
    $jsonc_content = file_get_contents($statfile);
    $json_stats = json_decode($jsonc_content, true);

    $lastmodif = $json_stats['now'];

    $result = array(
        'lastmodif' => $lastmodif,
        'modules' => array(),
        'io' => array()
    );

    foreach ($json_stats as $key => $value) {
        if (preg_match('/endpoint \(?(.*[^()])\)?/', $key, $matches)) {
            if (preg_match('/.*external commands.*/', $matches[1])) {
                $matches[1] = "external-commands";
            }

            if (
                (preg_match('/.*external commands.*/', $key) && $json_stats[$key]['state'] != "disconnected")
                || !preg_match('/.*external commands.*/', $key)
            ) {
                $keySepByDash = explode('-', $key);
                $keySepBySpace = explode(' ', $key);
                $result['io'][$matches[1]] = createArrayStats($json_stats[$key]);
                $result['io'][$matches[1]]['type'] = end($keySepByDash);
                $result['io'][$matches[1]]['id'] = end($keySepBySpace);
                $result['io'][$matches[1]]['id'] = rtrim($result['io'][$matches[1]]['id'], ')');


                /* force type of io  */
                if (preg_match('/.*external commands.*/', $key)) {
                    $result['io'][$matches[1]]['type'] = 'input';
                } elseif (
                    preg_match(
                        '/.*(central-broker-master-sql|centreon-broker-master-rrd|central-broker-master-perfdata|central-broker-master-unified-sql).*/',
                        $key
                    )
                ) {
                    $result['io'][$matches[1]]['type'] = 'output';
                } elseif (preg_match('/.*(centreon-bam-monitoring|centreon-bam-reporting).*/', $key)) {
                    $result['io'][$matches[1]]['type'] = 'output';
                }

                /* manage failover output */
                if (isset($json_stats[$key]['failover'])) {
                    $result['io'][$matches[1].'-failover'] = createArrayStats($json_stats[$key]['failover']);
                    $result['io'][$matches[1].'-failover']['type'] = 'output';
                    $result['io'][$matches[1].'-failover']['class'] = 'stats_lv2';
                    $result['io'][$matches[1].'-failover']['id'] = $matches[1].'-failover';
                }

                /* manage peers input */
                if (isset($json_stats[$key]['peers'])) {
                    $arrayPeers = explode(',', $json_stats[$key]['peers']);
                    for ($i = 1; $i < count($arrayPeers); $i++) {
                        $peerName = trim($arrayPeers[$i]);
                        $id = str_replace(':', '_', $peerName);
                        $id = str_replace('.', '_', $id);
                        $result['io'][$matches[1]]['peers'][$i] = $peerName;
                        $result['io'][$peerName] = createArrayStats($json_stats[$key][$matches[1].'-'.$i]);
                        $result['io'][$peerName]['type'] = 'input';
                        $result['io'][$peerName]['class'] = 'stats_lv2';
                        $result['io'][$peerName]['id'] = $id . '-peers';
                    }
                }
            }
        }

        /* Create list of loaded modules */
        if (preg_match('/module\s*\/.*\/\d+\-(.*)\.so/', $key, $matches)) {
            $result['modules'][$matches[1]] = $json_stats[$key]['state'];
        }
    }
    return $result;
}

/*
 * Init GMT class
 */
$centreonGMT = new CentreonGMT($pearDB);
$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);

$form = new HTML_QuickFormCustom('form', 'post', "?p=" . $p);

/*
 * Get Poller List
 */
$pollerList = array();
$DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` WHERE `ns_activate` = 1 ORDER BY `name`");
while ($data = $DBRESULT->fetchRow()) {
    if ($data['localhost']) {
        $defaultPoller = $data['id'];
    }
    $pollerList[$data["id"]] = $data["name"];
}
$DBRESULT->closeCursor();

/*
 * Get poller ID
 */
isset($_POST['pollers']) && $_POST['pollers'] != ""
    ? $selectedPoller = $_POST['pollers']
    : $selectedPoller = $defaultPoller;
if (!isset($selectedPoller)) {
    $tmpKeys = array_keys($pollerList);
    $selectedPoller = $tmpKeys[0];
    unset($tmpKeys);
}

$form->addElement('select', 'pollers', _("Poller"), $pollerList, array("onChange" => "this.form.submit();"));
$form->setDefaults(array('pollers' => $selectedPoller));
$pollerName = $pollerList[$selectedPoller];

$path = "./include/Administration/brokerPerformance/";

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl, "./");

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());

/*
 * Message
 */
$lang = array();
$lang['modules'] = _('Modules');
$lang['updated'] = _('Last update');
$lang['peers'] = _('Peers');
$lang['input'] = _('Input');
$lang['output'] = _('Output');
$tpl->assign('lang', $lang);
$tpl->assign('poller_name', $pollerName);

/*
 * Get the stats file name
 */
$queryStatName = "SELECT config_name, cache_directory "
    . "FROM cfg_centreonbroker "
    . "WHERE stats_activate = '1' "
    . "AND ns_nagios_server = :id";
try {
    $stmt = $pearDB->prepare($queryStatName);
    $stmt->bindParam(':id', $selectedPoller, PDO::PARAM_INT);
    $stmt->execute();
    if (!$stmt->rowCount()) {
        $tpl->assign('msg_err', _('No statistics file defined for this poller'));
    }
    $perf_info = array();
    $perf_err = array();
    while ($row = $stmt->fetch()) {
        $statsfile = $row['cache_directory'] . '/' . $row['config_name'] . '-stats.json';
        if ($defaultPoller != $selectedPoller) {
            $statsfile = _CENTREON_CACHEDIR_ . '/broker-stats/' . $selectedPoller . '/' . $row['config_name'] . '.json';
        }

        /**
         * check if file exists, is readable and inside proper folder
         */
        if (
            !file_exists($statsfile)
            || !is_readable($statsfile)
            || ((substr(realpath($statsfile), 0, strlen(_CENTREON_VARLIB_)) !== _CENTREON_VARLIB_ )
            && (substr(realpath($statsfile), 0, strlen(_CENTREON_CACHEDIR_)) !== _CENTREON_CACHEDIR_ ))
        ) {
            $perf_err[$row['config_name']] = _('Cannot open statistics file');
        } else {
            $perf_info[$row['config_name']] = parseStatsFile($statsfile);
        }
    }
    $tpl->assign('perf_err', $perf_err);
    $tpl->assign('perf_info_array', $perf_info);
} catch (\PDOException $e) {
    $tpl->assign('msg_err', _('Error in getting stats filename'));
}

$tpl->display('brokerPerformance.ihtml');
