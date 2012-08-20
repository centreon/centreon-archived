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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

	if (!isset($oreon))
		exit();

	function display_copying_file($filename = NULL, $status){
		if (!isset($filename))
			return ;
		$str = "<tr><td>- ".$filename."</td>";
		$str .= "<td>".$status."</td></tr>";
		return $str;
	}

	/*
	 * Init Centcore Pipre
	 */
	$centcore_pipe = "@CENTREON_VARLIB@/centcore.cmd";
	if ($centcore_pipe == "/centcore.cmd")
		$centcore_pipe = "/var/lib/centreon/centcore.cmd";

	/*
	 *  Get Poller List
	 */
	$DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `name` ASC");
	$n = $DBRESULT->numRows();
	/*
	 * Display null option
	 */
	if ($n > 1) {
		$tab_nagios_server = array(-1 => "");
	}

	/*
	 * Display all servers list
	 */
	for ($i = 0; $nagios = $DBRESULT->fetchRow(); $i++) {
		$tab_nagios_server[$nagios['id']] = $nagios['name'];
	}
	$DBRESULT->free();

	/*
	 * Display all server options
	 */
	if ($n > 1) {
		$tab_nagios_server[0] = _("All Pollers");
	}

	/*
	 * Form begin
	 */
	$attrSelect = array("style" => "width: 220px;");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', 	_("Configuration Files Export"));
	$form->addElement('header', 'infos', 	_("Implied Server"));
	$form->addElement('header', 'opt', 		_("Export Options"));
	$form->addElement('header', 'result', 	_("Actions"));

    $form->addElement('select', 'host', 	_("Poller"), $tab_nagios_server, array("id" => "nhost", "style" => "width: 220px;"));

	$form->addElement('checkbox', 'comment', _("Include Comments"), null, array('id' => 'ncomment'));

	$form->addElement('checkbox', 'debug', _("Run monitoring engine debug (-v)"), null, array('id' => 'ndebug'));
	$form->setDefaults(array('debug' => '1'));

	$form->addElement('checkbox', 'gen', _("Generate Configuration Files"), null, array('id' => 'ngen'));
	$form->setDefaults(array('gen' => '1'));
	$form->addElement('checkbox', 'move', _("Move Export Files"), null, array('id' => 'nmove'));
	$form->addElement('checkbox', 'restart', _("Restart Monitoring Engine"), null, array('id' => 'nrestart'));

	$tab_restart_mod = array(2 => _("Restart"), 1 => _("Reload"), 3 => _("External Command"));
	$form->addElement('select', 'restart_mode', _("Method"), $tab_restart_mod, array('id' => 'nrestart_mode', 'style' => 'width: 220px;'));
	$form->setDefaults(array('restart_mode' => '2'));

	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$sub = $form->addElement('button', 'submit', _("Export"), array('id' => 'exportBtn', 'onClick' => 'generationProcess();'));
	$msg = NULL;
	$stdout = NULL;

	if ($form->validate()) {
		$ret = $form->getSubmitValues();

		if (!isset($ret["comment"]))
			$ret["comment"] = 0;

		$host_list = array();
		foreach ($tab_nagios_server as $key => $value) {
			if ($key && ($res["host"] == 0 || $res["host"] == $key))
				$host_list[$key] = $value;
		}

		/*
		 * Create Server List to restart
		 */

		$tab_server = array();
		$DBRESULT_Servers = $pearDB->query("SELECT `name`, `id`, `localhost` FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `name` ASC");
		while ($tab = $DBRESULT_Servers->fetchRow()) {
			if (isset($ret["host"]) && ($ret["host"] == 0 || $ret["host"] == $tab['id'])) {
				$tab_server[$tab["id"]] = array("id" => $tab["id"], "name" => $tab["name"], "localhost" => $tab["localhost"]);
			}
		}

		/*
		 * If debug needed
		 */
		if (isset($ret["debug"]) && $ret["debug"])	{
			$DBRESULT_Servers = $pearDB->query("SELECT `nagios_bin` FROM `nagios_server` WHERE `ns_activate` = '1' AND `localhost` = '1' LIMIT 1");
			$nagios_bin = $DBRESULT_Servers->fetchRow();
			$DBRESULT_Servers->free();
			$msg_debug = array();
			foreach ($tab_server as $host) {
				$stdout = shell_exec("sudo ".$nagios_bin["nagios_bin"] . " -v ".$nagiosCFGPath.$host["id"]."/nagiosCFG.DEBUG");
				$stdout = htmlentities($stdout, ENT_QUOTES, "UTF-8");
				$msg_debug[$host['id']] = str_replace ("\n", "<br />", $stdout);
				$msg_debug[$host['id']] = str_replace ("Warning:", "<font color='orange'>Warning</font>", $msg_debug[$host['id']]);
				$msg_debug[$host['id']] = str_replace ("Error:", "<font color='red'>Error</font>", $msg_debug[$host['id']]);
				$msg_debug[$host['id']] = str_replace ("Total Warnings: 0", "<font color='green'>Total Warnings: 0</font>", $msg_debug[$host['id']]);
				$msg_debug[$host['id']] = str_replace ("Total Errors: 0", "<font color='green'>Total Errors: 0</font>", $msg_debug[$host['id']]);
				$msg_debug[$host['id']] = str_replace ("<br />License:", " - License:", $msg_debug[$host['id']]);

				$lines = preg_split("/\<br\ \/\>/", $msg_debug[$host['id']]);
				$msg_debug[$host['id']] = "";
				$i = 0;
				foreach ($lines as $line) {
					if (strncmp($line, "Processing object config file", strlen("Processing object config file")) && $i
						&& strncmp($line, "Website: http://www.nagios.org", strlen("Website: http://www.nagios.org")))
						$msg_debug[$host['id']] .= $line . "<br>";
					$i++;
				}

			}
		}


		/*
		 * Move Configuration Files and Images
		 */
		if (isset($ret["move"]) && $ret["move"]) {

			/*
			 * Copying image in logos directory
			 */
			$DBRESULT_imgs = $pearDB->query("SELECT `dir_alias`, `img_path` FROM `view_img`, `view_img_dir`, `view_img_dir_relation` WHERE dir_dir_parent_id = dir_id AND img_img_id = img_id");
			while ($images = $DBRESULT_imgs->fetchrow()){
				if (!is_dir($oreon->optGen["nagios_path_img"]."/".$images["dir_alias"]))
					mkdir($oreon->optGen["nagios_path_img"]."/".$images["dir_alias"]);
				copy($centreon_path."www/img/media/".$images["dir_alias"]."/".$images["img_path"], $oreon->optGen["nagios_path_img"]."/".$images["dir_alias"]."/".$images["img_path"]);
			}
			$msg_copy = array();
			foreach ($tab_server as $host) {
				if (isset($ret["host"]) && ($ret["host"] == 0 || $ret["host"] == $host['id'])) {
					if (isset($host['localhost']) && $host['localhost'] == 1) {
						$msg_copy[$host["id"]] = "";
						if (!is_dir($oreon->Nagioscfg["cfg_dir"])) {
							$msg_copy[$host["id"]] .= sprintf(_("Monitoring engine config directory %s does not exist!")."<br>", $oreon->Nagioscfg["cfg_dir"]);
						}
						if (!is_writable($oreon->Nagioscfg["cfg_dir"])) {
							$msg_copy[$host["id"]] .= sprintf(_("Monitoring engine config directory %s is not writable for webserver's user!")."<br>", $oreon->Nagioscfg["cfg_dir"]);
						}
						foreach (glob($nagiosCFGPath.$host["id"]."/*.cfg") as $filename) {
							$bool = @copy($filename, $oreon->Nagioscfg["cfg_dir"].basename($filename));
							$filename = array_pop(explode("/", $filename));
							if (!$bool) {
								$msg_copy[$host["id"]] .= display_copying_file($filename, " - "._("movement")." <font color='res'>KO</font>");
							}
						}
						/*
						 * Centreon Broker
						 */
						$listBrokerFile = glob($centreonBrokerPath . $host['id'] . "/*.xml");
						if (count($listBrokerFile) > 0) {
						    $centreonBrokerDirCfg = getCentreonBrokerDirCfg($host['id']);
						    if (!is_null($centreonBrokerDirCfg)) {
    						    if (!is_dir($centreonBrokerDirCfg)) {
    						        $msg_copy[$host['id']] .= sprintf(_("Centreon Broker config directory %s does not exists!") . "<br>", $centreonBrokerDirCfg);
    						    } elseif (!is_writable($centreonBrokerDirCfg)) {
    						        $msg_copy[$host['id']] .= sprintf(_("Centreon Broker config directory %s is not writable for webserver's user!") . "<br>", $centreonBrokerDirCfg);
    						    } else {
    						        foreach ($listBrokerFile as $fileCfg) {
    						            $bool = @copy($fileCfg, $centreonBrokerDirCfg . '/' . basename($fileCfg));
    						            $filename = array_pop(explode("/", $fileCfg));
        						        if (!$bool) {
            								$msg_copy[$host["id"]] .= display_copying_file($filename, " - "._("movement")." <font color='res'>KO</font>");
            							}
    						        }
    						    }
						    }
						}

					    if (strlen($msg_copy[$host["id"]])) {
							$msg_copy[$host["id"]] = "<table border=0 width=300>".$msg_copy[$host["id"]]."</table>";
						} else {
							$msg_copy[$host["id"]] .= _("<br><b>Centreon : </b>All configuration files copied with success.");
						}
					} else {
						passthru("echo 'SENDCFGFILE:".$host['id']."' >> $centcore_pipe", $return);
						if (!isset($msg_restart[$host["id"]])) {
							$msg_restart[$host["id"]] = "";
						}
						if (count(glob($centreonBrokerPath . $host['id'] . "/*.xml")) > 0) {
						    passthru("echo 'SENDCBCFG:".$host['id']."' >> $centcore_pipe", $return);
						}
						$msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>All configuration will be send to ".$host['name']." by centcore in several minutes.");
					}
				}
			}
		}

		/*
		 * Restart Nagios Poller
		 */
		if (isset($ret["restart"]) && $ret["restart"])	{
			$stdout = "";
			if (!isset($msg_restart)) {
				$msg_restart = array();
			}

			/*
			 * Get Init Script
			 */
			$DBRESULT = $pearDB->query("SELECT id, init_script FROM nagios_server WHERE localhost = '1' AND ns_activate = '1'");
			$serveurs = $DBRESULT->fetchrow();
			unset($DBRESULT);
			(isset($serveurs["init_script"])) ? $nagios_init_script = $serveurs["init_script"] : $nagios_init_script = "/etc/init.d/nagios";
			unset($serveurs);

			foreach ($tab_server as $host) {
				if ($ret["restart_mode"] == 1) {
					if (isset($host['localhost']) && $host['localhost'] == 1) {
						$msg_restart[$host["id"]] = shell_exec("sudo " . $nagios_init_script . " reload");
					} else {
						system("echo 'RELOAD:".$host["id"]."' >> $centcore_pipe", $return);
						if (!isset($msg_restart[$host["id"]])) {
							$msg_restart[$host["id"]] = "";
						}
						if ($return != 0) {
							$msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>A reload signal has been sent to ".$host["name"]."\n");
						} else {
							$msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>Cannot send signal to ".$host["name"].". Check $centcore_pipe properties.\n");
						}
					}
				} else if ($ret["restart_mode"] == 2) {
					if (isset($host['localhost']) && $host['localhost'] == 1) {
						$msg_restart[$host["id"]] = shell_exec("sudo " . $nagios_init_script . " restart");
					} else {
						system("echo \"RESTART:".$host["id"]."\" >> $centcore_pipe", $return);

						if (!isset($msg_restart[$host["id"]])) {
							$msg_restart[$host["id"]] = "";
						}
						if ($return != 0) {
							$msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>A restart signal has been sent to ".$host["name"]."\n");
						} else {
							$msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>Cannot send signal to ".$host["name"].". Check $centcore_pipe properties.\n");
						}
					}
				} else if ($ret["restart_mode"] == 3) {
					/*
					 * Require external function files.
					 */
					require_once "./include/monitoring/external_cmd/functions.php";
					write_command(" RESTART_PROGRAM", $host["id"]);
					if (!isset($msg_restart[$host["id"]])) {
						$msg_restart[$host["id"]] = "";
					}
					$msg_restart[$host["id"]] .= _("<br><b>Centreon : </b>A restart signal has been sent to ".$host["name"]."\n");
				}
				$DBRESULT = $pearDB->query("UPDATE `nagios_server` SET `last_restart` = '".time()."' WHERE `id` = '".$host["id"]."'");
			}

			foreach ($msg_restart as $key => $str) {
				$msg_restart[$key] = str_replace("\n", "<br>", $str);
			}
		}
	}

	$form->addElement('header', 'status', _("Status"));
	if (isset($msg_restart) && $msg_restart)
		$tpl->assign('msg_restart', $msg_restart);
	if (isset($msg_debug) && $msg_debug)
		$tpl->assign('msg_debug', $msg_debug);
	if (isset($msg_copy) && $msg_copy)
		$tpl->assign('msg_copy', $msg_copy);
	if (isset($tab_server) && $tab_server)
		$tpl->assign('tab_server', $tab_server);
	if (isset($host_list) && $host_list)
		$tpl->assign('host_list', $host_list);

	$tpl->assign("consoleLabel", _("Console"));
	$tpl->assign("progressLabel", _("Progress"));
	$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"' );
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) {
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);

	/*
	 * Apply a template definition
	 */

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("formGenerateFiles.ihtml");
?>
<script type='text/javascript'>
var selectedPoller;
var debugOption;
var commentOption;
var generateOption;
var moveOption;
var restartOption;
var restartMode;
var exportBtn;

var tooltip = new CentreonToolTip();
var session_id = "<?php echo session_id();?>";
tooltip.render();
var progressBar;
var msgTab = new Array();
msgTab['start'] = "<?php echo _("Preparing environment");?>";
msgTab['gen'] = "<?php echo _("Generating files");?>";
msgTab['move'] = "<?php echo _("Moving files");?>";
msgTab['restart'] = "<?php echo _("Restarting engine");?>";
msgTab['abort'] = "<?php echo _("Aborted.");?>";
msgTab['noPoller'] = "<?php echo _("No poller selected");?>";

document.onLoad = initProgressBar();

/**
 * Init Progress bar
 *
 * @return void
 */
function initProgressBar()
{
	progressBar = new JS_BRAMUS.jsProgressBar(
					'progress_bar',
					0,
					{
                        animate         : false,
                        showText        : false,
                        barImage        : Array(
                                                'include/common/javascript/scriptaculous/images/bramus/percentImage_back4.png',
                                                'include/common/javascript/scriptaculous/images/bramus/percentImage_back3.png',
                                                'include/common/javascript/scriptaculous/images/bramus/percentImage_back2.png',
                                                'include/common/javascript/scriptaculous/images/bramus/percentImage_back1.png'
                                        ),
                        boxImage        : 'include/common/javascript/scriptaculous/images/bramus/percentImage.png'
                	}
	);
}

/**
 * Generation process
 *
 * @return void
 */
function generationProcess()
{
	updateProgress(0);
	cleanErrorPhp();
	document.getElementById('console').style.visibility = 'visible';
	$('consoleContent').update(msgTab['start'] + "... ");
	$('consoleDetails').update("");
	initEnvironment();
	if (selectedPoller != "-1") {
    	updateProgress(10);
    	if (generateOption) {
    		generateFiles();
    	} else if (moveOption) {
    		moveFiles();
    	} else if (restartOption) {
    		restartPollers();
    	} else {
    		updateProgress(100);
    	}
	}
}

/**
 * Initializes generation options
 */
function initEnvironment()
{
	selectedPoller = document.getElementById('nhost').value;
	commentOption  = document.getElementById('ncomment').checked;
	debugOption = document.getElementById('ndebug').checked;
	generateOption = document.getElementById('ngen').checked;
	moveOption = document.getElementById('nmove').checked;
	restartOption = document.getElementById('nrestart').checked;
	restartMode = document.getElementById('nrestart_mode').value;
	exportBtn = document.getElementById('exportBtn');
	exportBtn.disabled = true;
	if (selectedPoller == "-1") {
		$('consoleContent').insert("<b><font color='red'>NOK</font></b> ("+ msgTab['noPoller'] +")<br/>");
		abortProgress();
		return null;
	}
	$('consoleContent').insert("<b><font color='green'>OK</font></b><br/>");
}

/**
 * Generate files
 */
function generateFiles()
{
	$('consoleContent').insert(msgTab['gen'] + "... ");
	new Ajax.Request('./include/configuration/configGenerate/xml/generateFiles.php', {
		method: 'post',
		parameters: {
						sid: session_id,
						poller: selectedPoller,
						comment: commentOption,
						debug: debugOption
					},
		onSuccess: function (response) {
						displayStatusMessage(response.responseXML);
						displayDetails(response.responseXML);
						displayPhpErrorMsg('generate', response.responseXML);
						if (isError(response.responseXML) == "1") {
							abortProgress();
							return null;
						}
						if (moveOption) {
							updateProgress(33);
							moveFiles();
						} else if (restartOption) {
							updateProgress(50);
							restartPollers();
						} else {
							updateProgress(100);
							exportBtn.disabled = false;
						}
		}
	});
}

/**
 * Move files
 */
function moveFiles()
{
	$('consoleContent').insert(msgTab['move'] + "... ");
	new Ajax.Request('./include/configuration/configGenerate/xml/moveFiles.php', {
		method: 'post',
		parameters: {
						sid: session_id,
						poller: selectedPoller
					},
		onSuccess: function (response) {
						displayStatusMessage(response.responseXML);
						displayDetails(response.responseXML);
						displayPhpErrorMsg('move', response.responseXML);
						if (restartOption) {
							updateProgress(67);
							restartPollers();
						} else {
							updateProgress(100);
							exportBtn.disabled = false;
						}
		}
	});
}

/**
 * Restart Pollers
 */
function restartPollers()
{
	$('consoleContent').insert(msgTab['restart'] + "... ");
	new Ajax.Request('./include/configuration/configGenerate/xml/restartPollers.php', {
		method: 'post',
		parameters: {
						sid: session_id,
						poller: selectedPoller,
						mode: restartMode
					},
		onSuccess: function (response) {
						displayStatusMessage(response.responseXML);
						displayDetails(response.responseXML);
						displayPhpErrorMsg('restart', response.responseXML);
						updateProgress(100);
						exportBtn.disabled = false;
		}
	});
}

/**
 * Display status message
 */
function displayStatusMessage(responseXML)
{
	var status = responseXML.getElementsByTagName("status");
	var error = responseXML.getElementsByTagName("error");
	var str;
	str = status.item(0).firstChild.data;
	if (error.length && error.item(0).firstChild.data) {
		str += " (" + error.item(0).firstChild.data + ")";
	}
	str += "<br/>";
	$('consoleContent').insert(str);
}

/**
 * Display details
 */
function displayDetails(responseXML)
{
	var debug = responseXML.getElementsByTagName("debug");
	var str;

	str = "";
	if (debug.length && debug.item(0).firstChild.data) {
		str = debug.item(0).firstChild.data;
	}
	str += "<br/>";
	$('consoleDetails').insert(str);
}

/**
 * Returns 1 if is error
 * Returns 0 otherwise
 */
function isError(responseXML)
{
	var statuscode = responseXML.getElementsByTagName("statuscode");
	if (statuscode.length) {
		return statuscode.item(0).firstChild.data;
	}
	return 0;
}

/**
 * Action (generate, move, restart)
 */
var errorClass = 'list_two';
function displayPhpErrorMsg(action, responseXML)
{
	var errors = responseXML.getElementsByTagName('errorPhp');
	var titleError;
	if (errorClass == 'list_one') {
		errorClass = 'list_two';
	} else {
		errorClass = 'list_one';
	}
	if (errors.length == 0) {
		return;
	}
	switch (action) {
		case 'generate':
			titleError = '<b>Errors/warnings in generate</b>';
			break;
		case 'move':
			titleError = '<b>Errors/warnings in move files</b>';
			break;
		case 'restart':
			titleError = '<b>Errors/warnings in restart</b>';
			break;
	}

	var bodyErrors = document.getElementById('error_log');
	var trEl = document.createElement('tr');
	trEl.setAttribute('class', errorClass);
	bodyErrors.appendChild(trEl);
	var tdEl1 = document.createElement('td');
	tdEl1.setAttribute('class', 'FormRowField');
	tdEl1.innerHTML = titleError;
	trEl.appendChild(tdEl1);
	var tdEl2 = document.createElement('td');
	tdEl2.setAttribute('class', 'FormRowValue');
	tdEl2.innerHTML = '<span style="position: relative; float: left; margin-right: 5px;"><a href="javascript:toggleErrorPhp(\'' + action + '\');" id="expend_' + action + '">[ + ]</a></span>';
	trEl.appendChild(tdEl2);
	var divErrors = document.createElement('div');
	divErrors.setAttribute('id', 'errors_' + action);
	divErrors.setAttribute('style', 'position: relative; float: left;');
	divErrors.style.visibility = 'hidden';
	tdEl2.appendChild(divErrors);
	for (var i = 0; i < errors.length; i++) {
		divErrors.innerHTML +=  errors.item(i).firstChild.data;
	}
}

function toggleErrorPhp(action) {
	var linkEl = document.getElementById('expend_' + action);
	var divErrors = document.getElementById('errors_' + action);
	if (linkEl.innerHTML == '[ + ]') {
		linkEl.innerHTML = '[ - ]';
		divErrors.style.visibility = 'visible';
	} else {
		linkEl.innerHTML = '[ + ]';
		divErrors.style.visibility = 'hidden';
	}
}

function cleanErrorPhp() {
	var bodyErrors = document.getElementById('error_log');
	while (bodyErrors.hasChildNodes()) {
		bodyErrors.removeChild(bodyErrors.firstChild);
	}
}

/**
 * Updates progress
 */
function updateProgress(val)
{
	progressBar.setPercentage(val);
	$('progressPct').update(val + "%");
}

/**
 * Toggle debug
 */
function toggleDebug(pollerId)
{
	if (pollerId) {
		Effect.toggle('debug_' + pollerId, 'blind', { duration: 0.1 });
	}
	$('togglerp_' + pollerId, 'togglerm_' + pollerId).invoke('toggle');
}

function abortProgress()
{
	$('consoleContent').insert(msgTab['abort']);
	exportBtn.disabled = false;
}
</script>