<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

	if (!isset($oreon))
		exit();

	# Get Poller List
	$tab_nagios_server = array("0" => "All Nagios Servers");
	$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` ORDER BY `name`");
	while ($nagios =& $DBRESULT->fetchRow())
		$tab_nagios_server[$nagios['id']] = $nagios['name'];
	
	$host_list = array();
	$tab_server = array();
	$cpt = 0;
	foreach ($tab_nagios_server as $key => $value)
		if ($key && ($res["host"] == 0 || $res["host"] == $key)){
			$host_list[$key] = $value;
			$tab_server[$cpt] = $value;
			$cpt++;
		}
	
	#
	## Form begin
	#
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

		$DBRESULT_Servers =& $pearDB->query("SELECT `nagios_bin` FROM `nagios_server` LIMIT 1");
		$nagios_bin = $DBRESULT_Servers->fetchRow();
		$DBRESULT_Servers->free();

		$DBRESULT_Servers =& $pearDB->query("SELECT `id` FROM `nagios_server` ORDER BY `name`");
		$msg_optimize = array();
		$cpt = 1;
		while ($tab =& $DBRESULT_Servers->fetchRow()){
			if (isset($ret["host"]) && $ret["host"] == 0 || $ret["host"] == $tab['id']){		
				$stdout = shell_exec("sudo ".$nagios_bin["nagios_bin"] . " -s ".$nagiosCFGPath.$tab['id']."/nagiosCFG.DEBUG");
				$msg_optimize[$cpt] = str_replace ("\n", "<br />", $stdout);
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

	# Apply a template definition
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("formOptimizeFiles.ihtml");
?>