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


	/*
	 * Display all server options
	 */
	$tab_nagios_server = array(0 => _("All Nagios Servers"));

	/*
	 * Get Poller List
	 */
	$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `name`");
	while ($nagios =& $DBRESULT->fetchRow()) {
		$tab_nagios_server[$nagios['id']] = $nagios['name'];
	}
	$DBRESULT->free();
	
	/*
	 * Display all servers list
	 */
	$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `name` DESC");
	$n = $DBRESULT->numRows();

	/*
	 * create all servers list
	 */
	for ($i = 0; $nagios =& $DBRESULT->fetchRow(); $i++) {
		$host_list[$nagios['id']] = $nagios['name'];
	}
	$DBRESULT->free();
	

	/*
	 * Form begin
	 */
	$attrSelect = array("style" => "width: 220px;");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Nagios Configuration Files Export"));
	$form->addElement('header', 'infos', _("Implied Server"));
	$form->addElement('select', 'host', _("Nagios Server"), $tab_nagios_server, $attrSelect);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'optimize', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'optimize', null, _("No"), '0');
	$form->addGroup($tab, 'optimize', _("Run Optimisation test (-s)"), '&nbsp;');
	$form->setDefaults(array('optimize' => '0'));
	
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$sub =& $form->addElement('submit', 'submit', _("Export"));
	$msg = NULL;
	$stdout = NULL;
	
	if ($form->validate())	{
		$ret = $form->getSubmitValues();		

		$DBRESULT_Servers =& $pearDB->query("SELECT `nagios_bin` FROM `nagios_server` WHERE `ns_activate` = '1' LIMIT 1");
		$nagios_bin =& $DBRESULT_Servers->fetchRow();
		$DBRESULT_Servers->free();

		$msg_optimize = array();
		
		$cpt = 1;
		$DBRESULT_Servers =& $pearDB->query("SELECT `id` FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `name`");
		while ($tab =& $DBRESULT_Servers->fetchRow()){
			if (isset($ret["host"]) && ($ret["host"] == 0 || $ret["host"] == $tab['id'])){		
				$stdout = shell_exec("sudo ".$nagios_bin["nagios_bin"] . " -s ".$nagiosCFGPath.$tab['id']."/nagiosCFG.DEBUG");
				$stdout = htmlentities($stdout, ENT_QUOTES);
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
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("formOptimizeFiles.ihtml");
?>