<?php
/*
 * Copyright 2005-2010 MERETHIS
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

	# Get Poller List
	$tab_nagios_server = array("0" => "All Nagios Servers");
	$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` ORDER BY `name`");
	while ($nagios =& $DBRESULT->fetchRow())
		$tab_nagios_server[$nagios['id']] = $nagios['name'];
	
	#
	## Form begin
	#
	$attrSelect = array("style" => "width: 220px;");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Nagios Configuration Files Export"));

	$form->addElement('header', 'infos', _("Implied Server"));
	
    $form->addElement('select', 'host', _("Nagios Server"), $tab_nagios_server, $attrSelect);

	$form->addElement('header', 'opt', _("Export Options"));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'generate', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'generate', null, _("No"), '0');
	$form->addGroup($tab, 'generate', _("Generate Files"), '&nbsp;');
	$form->setDefaults(array('generate' => '1'));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'level', null, _("Dependencies Management"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'level', null, _("Current Activation"), '2');
	$tab[] = &HTML_QuickForm::createElement('radio', 'level', null, _("None"), '3');
	$form->addGroup($tab, 'level', _("Relations between Elements"), '<br />');
	$form->setDefaults(array('level' => '1'));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'comment', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'comment', null, _("No"), '0');
	$form->addGroup($tab, 'comment', _("Include Comments"), '&nbsp;');
	$form->setDefaults(array('comment' => '0'));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'xml', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'xml', null, _("No"), '0');
	$form->addGroup($tab, 'xml', _("Export in XML too"), '&nbsp;');
	$form->setDefaults(array('xml' => '0'));
	$form->addElement('header', 'traps', _("SNMP Traps"));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'genTraps', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'genTraps', null, _("No"), '0');
	$form->addGroup($tab, 'genTraps', _("Export configuration files"), '&nbsp;');
	$form->setDefaults(array('genTraps' => '0'));
	$form->addElement('header', 'result', _("Result"));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'debug', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'debug', null, _("No"), '0');
	$form->addGroup($tab, 'debug', _("Run Nagios debug (-v)"), '&nbsp;');
	$form->setDefaults(array('debug' => '1'));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'optimize', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'optimize', null, _("No"), '0');
	$form->addGroup($tab, 'optimize', _("Run Optimisation test (-s)"), '&nbsp;');
	$form->setDefaults(array('optimize' => '0'));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'move', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'move', null, _("No"), '0');
	$form->addGroup($tab, 'move', _("Move Export Files"), '&nbsp;');
	$form->setDefaults(array('move' => '0'));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'restart', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'restart', null, _("No"), '0');
	$form->addGroup($tab, 'restart', _("Restart Nagios"), '&nbsp;');
	$form->setDefaults(array('restart' => '0'));
	
	$tab_restart_mod = array(2 => _("Restart"), 1 => _("Reload"), 3 => _("External Command"));
	$form->addElement('select', 'restart_mode', _("Restart Nagios"), $tab_restart_mod, $attrSelect);
	$form->setDefaults(array('restart_mode' => '2'));
	
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$sub =& $form->addElement('submit', 'submit', _("Export"));
	$msg = NULL;
	$stdout = NULL;
	if ($form->validate()) {	
		if ($ret["optimize"]["optimize"]){
			$DBRESULT_Servers =& $pearDB->query("SELECT `id` FROM `nagios_server` ORDER BY `name`");
			while ($tab =& $DBRESULT_Servers->fetchRow()){
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
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("formGenerateFiles.ihtml");
?>