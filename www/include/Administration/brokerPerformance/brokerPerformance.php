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


if (!isset($oreon)) {
    exit();
}

require_once "./include/monitoring/common-Func.php";
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
require_once "./class/centreonDB.class.php";
require_once "./class/centreonGMT.class.php";

function getCentreonBrokerModulesList()
{
    global $pearDB;
    $list = array();
    $query = 'SELECT name, libname, loading_pos
	    	FROM cb_module
	    	WHERE is_activated = "1"
	    		AND libname IS NOT NULL
	    	ORDER BY loading_pos, libname';
    $res = $pearDB->query($query);
    if (PEAR::isError($res)) {
        return $list;
    }
    while ($row = $res->fetchRow()) {
        $file = $row['libname'];
        $list[$file] = $row['name'];
    }
    return $list;
}

function parseStatsFile($statfile)
{
    $fieldDate = array('last event at', 'last connection attempt', 'last connection success');
    $listModules = getCentreonBrokerModulesList();
    $lastmodif = date('Y-m-d H:i:s', filemtime($statfile));

    if (!($fd = fopen($statfile, 'r+'))) {
        $fd = fopen($statfile, 'r');
    }
    $lineBlock = null;
    $failover = null;
	$acceptedEvents = null;
    $result = array(
        'lastmodif' => $lastmodif,
        'modules' => array(),
        'io' => array()
    );
    stream_set_blocking($fd, false);
    $read = array($fd);
    $write = null;
    $except= null;
    $nbChanged = stream_select($read, $write, $except, 2);
    if ($nbChanged) {
        while ($line = fgets($fd)) {
            $line = trim($line);
            if ($line == '') {
                $lineBlock = null;
            } elseif (is_null($lineBlock)) {
                if (strncmp('module ', $line, 7) == 0) {
                    $lineBlock = 'module';
                    list($tag, $module) = explode(' ', $line);
                    $baseModuleFile = preg_replace('/^[0-9]+\-/', '', basename($module));
                    if (isset($listModules[$baseModuleFile])) {
                        $moduleName = $listModules[$baseModuleFile];
                    } else {
                        $moduleName = $baseModuleFile;
                    }
                } elseif (strncmp('input ', $line, 6) == 0 || strncmp('output ', $line, 7) == 0) {
                    $lineBlock = 'io';
                    list($tag, $ioName) = explode(' ', $line);
                    $result['io'][$ioName] = array(
                        'type' => $tag
                    );
                    if (!is_null($failover)) {
                        $result['io'][$failover]['failover'] = '<a href="javascript:toggleInfoBlock(\'' . $ioName . '\')">' . $ioName . '</a>';
                        $failover = null;
                    }
                }
            } else {
                if ($lineBlock == 'peers') {
                    if (strstr($line, '=') === false) {
                        $result['io'][$ioName]['peers'][] = $line;
                    } else {
                        $lineBlock = 'io';
                    }
                }
                if ($lineBlock == 'module') {
                    list($tag, $status) = explode('=', $line);
                    if ($tag == 'state') {
                        $result['modules'][$moduleName] = $status;
                    }
                    $lineBlock = null;
                    $moduleName = null;
                } elseif ($lineBlock == 'io') {
                    if (!is_null($acceptedEvents) && ((preg_match('/=/', $line) == 1) || (preg_match('/output/', $line) == 1))) {
                        $acceptedEvents = null;
                    }
                    if ($line == 'failover') {
                        $failover = $ioName;
                        $lineBlock = null;
                    } elseif (!is_null($acceptedEvents)) {
                        $result['io'][$ioName]['filters'][] = trim($line);
                    } elseif (preg_match('/accepted events/', $line) == 1) {
                        $acceptedEvents = 1;
                        $result['io'][$ioName]['filters'] = array();
                    } else {
                        list($key, $value) = explode('=', $line);
                        if ($key != 'peers') {
                            if (in_array($key, $fieldDate) && $value != 0) {
                                $result['io'][$ioName][$key] = date('Y-m-d H:i:s', $value);
                            } else {
                                $result['io'][$ioName][$key] = $value;
                            }
                        } else {
                            $result['io'][$ioName][$key] = array();
                            $lineBlock = 'peers';
                        }
                    }
                }
            }
        }
    }
    fclose($fd);
    return $result;
}

/*
 * Init GMT class
 */
$centreonGMT = new CentreonGMT($pearDB);
$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);

$form = new HTML_QuickForm('form', 'post', "?p=" . $p);

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
$DBRESULT->free();

/*
 * Get poller ID
 */
isset($_POST['pollers']) && $_POST['pollers'] != "" ? $selectedPoller = $_POST['pollers'] : $selectedPoller = $defaultPoller;
if (!isset($selectedPoller)) {
    $tmpKeys = array_keys($pollerList);
    $selectedPoller = $tmpKeys[0];
    unset($tmpKeys);
}

$form->addElement('select', 'pollers', _("Poller :"), $pollerList, array("onChange" => "this.form.submit();"));
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
$lang['loaded'] = _('Loaded');
$lang['state'] = _('State');
$lang['peers'] = _('Peers');
$lang['last event at'] = _('Last event at');
$lang['event processing speed'] = _('Event processing speed');
$lang['last connection attempt'] = _('Last connection attempt');
$lang['last connection success'] = _('Last connection success');
$lang['input'] = _('Input');
$lang['output'] = _('Output');
$lang['failover'] = _('Failover');
$lang['filters'] = _('Accepted events type');
$lang['queued events'] = _('Queued events');
$lang['file_read_path'] = _('File read path');
$lang['file_read_offset'] = _('File read offset');
$lang['file_write_path'] = _('File write path');
$lang['file_write_offset'] = _('File write offset');
$lang['file_max_size'] = _('File max size');
$lang['temporary recovery mode'] = _('Temporary recovery mode');

$tpl->assign('lang', $lang);
$tpl->assign('poller_name', $pollerName);
$tpl->assign('broker', $oreon->broker->getBroker());

/*
 * If broker is Centreon Broker
 */
if ($oreon->broker->getBroker() == 'broker') {
    /*
     * Get the stats file name
     */
    $queryStatName = 'SELECT cbi.config_value, cb.config_name
    	    	FROM cfg_centreonbroker_info as cbi, cfg_centreonbroker as cb
    	    	WHERE cb.config_id = cbi.config_id
    	    		AND cbi.config_group = "stats"
    	    		AND cbi.config_key = "fifo"
    	    		AND cb.ns_nagios_server = ' . $selectedPoller;
    $res = $pearDB->query($queryStatName);
    if (PEAR::isError($res)) {
        $tpl->assign('msg_err', _('Error in getting stats filename'));
    } else {
        if (!$res->numRows()) {
            $tpl->assign('msg_err', _('No statistics file defined for this poller'));
        }
        $perf_info = array();
        $perf_err = array();
        while ($row = $res->fetchRow()) {
            $statsfile = $row['config_value'];
            if ($defaultPoller != $selectedPoller) {
                $statsfile = '/var/lib/centreon/broker-stats/broker-stats-' . $selectedPoller . '.dat';
            }
            if (!file_exists($statsfile) || !is_readable($statsfile)) {
                $perf_err[$row['config_name']] = _('Cannot open statistics file');
            } else {
                $perf_info[$row['config_name']] = parseStatsFile($statsfile);
            }
        }
        $tpl->assign('perf_err', $perf_err);
        $tpl->assign('perf_info_array', $perf_info);
    }
} else {
    $tpl->assign('msg_err', _('Performance broker page work only with Centreon Broker.'));
}

$tpl->display('brokerPerformance.ihtml');
