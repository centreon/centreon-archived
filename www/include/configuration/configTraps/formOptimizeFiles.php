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

	# Get Poller List
	$tab_nagios_server = array("0" => "All Pollers");
	$DBRESULT = $pearDB->query("SELECT * FROM `nagios_server` ORDER BY `name`");
	while ($nagios = $DBRESULT->fetchRow())
		$tab_nagios_server[$nagios['id']] = $nagios['name'];

	#
	## Form begin
	#
	$attrSelect = array("style" => "width: 220px;");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Monitoring Engine Configuration Files Export"));

	$form->addElement('header', 'infos', _("Implied Server"));

    $form->addElement('select', 'host', _("Poller"), $tab_nagios_server, $attrSelect);

	$form->addElement('header', 'opt', _("Export Options"));

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'generate', null, _("Yes"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'generate', null, _("No"), '0');
	$form->addGroup($tab, 'generate', _("Generate Files"), '&nbsp;');
	$form->setDefaults(array('generate' => '1'));

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'level', null, _("Dependencies Management"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'level', null, _("Current Activation"), '2');
	$tab[] = HTML_QuickForm::createElement('radio', 'level', null, _("None"), '3');
	$form->addGroup($tab, 'level', _("Relations between Elements"), '<br />');
	$form->setDefaults(array('level' => '1'));

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'comment', null, _("Yes"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'comment', null, _("No"), '0');
	$form->addGroup($tab, 'comment', _("Include Comments"), '&nbsp;');
	$form->setDefaults(array('comment' => '0'));

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'xml', null, _("Yes"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'xml', null, _("No"), '0');
	$form->addGroup($tab, 'xml', _("Export in XML too"), '&nbsp;');
	$form->setDefaults(array('xml' => '0'));
	$form->addElement('header', 'traps', _("SNMP Traps"));

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'genTraps', null, _("Yes"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'genTraps', null, _("No"), '0');
	$form->addGroup($tab, 'genTraps', _("Export configuration files"), '&nbsp;');
	$form->setDefaults(array('genTraps' => '0'));
	$form->addElement('header', 'result', _("Result"));
	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'debug', null, _("Yes"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'debug', null, _("No"), '0');
	$form->addGroup($tab, 'debug', _("Run debug (-v)"), '&nbsp;');
	$form->setDefaults(array('debug' => '1'));
	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'optimize', null, _("Yes"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'optimize', null, _("No"), '0');
	$form->addGroup($tab, 'optimize', _("Run Optimisation test (-s)"), '&nbsp;');
	$form->setDefaults(array('optimize' => '0'));
	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'move', null, _("Yes"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'move', null, _("No"), '0');
	$form->addGroup($tab, 'move', _("Move Export Files"), '&nbsp;');
	$form->setDefaults(array('move' => '0'));
	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'restart', null, _("Yes"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'restart', null, _("No"), '0');
	$form->addGroup($tab, 'restart', _("Restart Monitoring Engine"), '&nbsp;');
	$form->setDefaults(array('restart' => '0'));

	$tab_restart_mod = array(2 => _("Restart"), 1 => _("Reload"), 3 => _("External Command"));
	$form->addElement('select', 'restart_mode', _("Restart Monitoring Engine"), $tab_restart_mod, $attrSelect);
	$form->setDefaults(array('restart_mode' => '2'));

	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$sub = $form->addElement('submit', 'submit', _("Export"));
	$msg = NULL;
	$stdout = NULL;
	if ($form->validate()) {
		if ($ret["optimize"]["optimize"]){
			$DBRESULT_Servers = $pearDB->query("SELECT `id` FROM `nagios_server` ORDER BY `name`");
			while ($tab = $DBRESULT_Servers->fetchRow()){
				if (isset($ret["host"]) && $ret["host"] == 0 || $ret["host"] == $tab['id']){
					$stdout = shell_exec($oreon->optGen["nagios_path_bin"] . " -s ".$nagiosCFGPath.$tab['id']."/nagiosCFG.DEBUG");
					$msg .= str_replace ("\n", "<br />", $stdout);
				}
			}
		}
	}

	$form->addElement('header', 'status', _("Status"));
	if ($msg)
		$tpl->assign('msg', $msg);

	# Apply a template definition
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("formGenerateFiles.ihtml");
?>