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

	if (!isset($oreon))
		exit();


	/*
	 * Display all server options
	 */
	$tab_nagios_server = array(0 => _("All Pollers"));

	/*
	 * Get Poller List
	 */
	$DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `name`");
	while ($nagios = $DBRESULT->fetchRow()) {
		$tab_nagios_server[$nagios['id']] = $nagios['name'];
	}
	$DBRESULT->free();

	/*
	 * Display all servers list
	 */
	$DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `name` DESC");
	$n = $DBRESULT->numRows();

	/*
	 * create all servers list
	 */
	for ($i = 0; $nagios = $DBRESULT->fetchRow(); $i++) {
		$host_list[$nagios['id']] = $nagios['name'];
	}
	$DBRESULT->free();


	/*
	 * Form begin
	 */
	$attrSelect = array("style" => "width: 220px;");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Monitoring Engine Configuration Files Export"));
	$form->addElement('header', 'infos', _("Implied Server"));
	$form->addElement('select', 'host', _("Poller"), $tab_nagios_server, $attrSelect);

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'optimize', null, _("Yes"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'optimize', null, _("No"), '0');
	$form->addGroup($tab, 'optimize', _("Run Optimisation test (-s)"), '&nbsp;');
	$form->setDefaults(array('optimize' => '0'));

	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$sub = $form->addElement('submit', 'submit', _("Export"));
	$msg = NULL;
	$stdout = NULL;

	if ($form->validate())	{
		$ret = $form->getSubmitValues();

		$DBRESULT_Servers = $pearDB->query("SELECT `nagios_bin` FROM `nagios_server` WHERE `ns_activate` = '1' LIMIT 1");
		$nagios_bin = $DBRESULT_Servers->fetchRow();
		$DBRESULT_Servers->free();

		$msg_optimize = array();

		$cpt = 1;
		$DBRESULT_Servers = $pearDB->query("SELECT `id` FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `name`");
		while ($tab = $DBRESULT_Servers->fetchRow()){
			if (isset($ret["host"]) && ($ret["host"] == 0 || $ret["host"] == $tab['id'])){
				$stdout = shell_exec($nagios_bin["nagios_bin"] . " -s ".$nagiosCFGPath.$tab['id']."/nagiosCFG.DEBUG");
				$stdout = htmlentities($stdout, ENT_QUOTES, "UTF-8");
				$msg_optimize[$tab['id']] = str_replace ("\n", "<br />", $stdout);
				$cpt++;
			}
		}
	}

	$form->addElement('header', 'status', _("Status"));
	if (isset($msg_optimize) && $msg_optimize)
		$tpl->assign('msg_optimize', $msg_optimize);
	if (isset($host_list) && $host_list)
		$tpl->assign('host_list', $host_list);
	if (isset($tab_server) && $tab_server)
		$tpl->assign('tab_server', $tab_server);

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("formOptimizeFiles.ihtml");
?>
