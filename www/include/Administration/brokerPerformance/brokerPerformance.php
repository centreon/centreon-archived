<?php
/*
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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/Administration/corePerformance/processInfo.php $
 * SVN : $Id: processInfo.php 12790 2011-12-15 15:32:42Z shotamchay $
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
    $fd = fopen($statfile, 'r');
    $lineBlock = null;
    $failover = null;
    $result = array(
	        'lastmodif' => $lastmodif,
	        'modules' => array(),
	        'io' => array()
    );
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
                if ($line == 'failover') {
                    $failover = $ioName;
                    $lineBlock = null;
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
    fclose($fd);
    return $result;
}

/*
 * Init GMT class
 */
$centreonGMT = new CentreonGMT($pearDB);
$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);

$form = new HTML_QuickForm('form', 'post', "?p=".$p);

/*
 * Get Poller List
 */
$pollerList = array();
$DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` WHERE `ns_activate` = 1 ORDER BY `localhost` DESC");
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

$form->addElement('select', 'pollers', _("Poller :"), $pollerList, array("onChange" =>"this.form.submit();"));
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
$lang['queued_events'] = _('Queued events');

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
                $statsfile = '@CENTREON_VARLIB@/broker-stats/broker-stats-' . $selectedPoller . '.dat';
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