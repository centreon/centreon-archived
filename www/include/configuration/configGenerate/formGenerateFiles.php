<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	if (!isset($oreon))
		exit();	
	
	#
	## Form begin
	#
	$attrSelect = array("style" => "width: 100px;");
	
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', $lang["gen_name"]);
	
	$form->addElement('header', 'infos', $lang["gen_infos"]);
    $form->addElement('select', 'host', $lang["gen_host"], array(0=>"localhost"), $attrSelect);
    
	$form->addElement('header', 'opt', $lang["gen_opt"]);
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'generate', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'generate', null, $lang["no"], '0');
	$form->addGroup($tab, 'generate', $lang["gen_ok"], '&nbsp;');
	$form->setDefaults(array('generate' => '1'));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'level', null, $lang["gen_level1"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'level', null, $lang["gen_level2"], '2');
	$tab[] = &HTML_QuickForm::createElement('radio', 'level', null, $lang["gen_level3"], '3');
	$form->addGroup($tab, 'level', $lang["gen_level"], '<br>');
	$form->setDefaults(array('level' => '1'));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'comment', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'comment', null, $lang["no"], '0');
	$form->addGroup($tab, 'comment', $lang["gen_comment"], '&nbsp;');
	$form->setDefaults(array('comment' => '0'));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'xml', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'xml', null, $lang["no"], '0');
	$form->addGroup($tab, 'xml', $lang["gen_xml"], '&nbsp;');
	$form->setDefaults(array('xml' => '0'));
		
	$form->addElement('header', 'traps', $lang['gen_trapd']);
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'genTraps', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'genTraps', null, $lang["no"], '0');
	$form->addGroup($tab, 'genTraps', $lang['gen_genTrap'], '&nbsp;');
	$form->setDefaults(array('genTraps' => '0'));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'restartTrapd', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'restartTrapd', null, $lang["no"], '0');
	$form->addGroup($tab, 'restartTrapd', $lang['gen_trapRestart'], '&nbsp;');
	$form->setDefaults(array('restartTrapd' => '0'));
		
	$form->addElement('header', 'result', $lang["gen_result"]);
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'debug', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'debug', null, $lang["no"], '0');
	$form->addGroup($tab, 'debug', $lang["gen_debug"], '&nbsp;');
	$form->setDefaults(array('debug' => '1'));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'move', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'move', null, $lang["no"], '0');
	$form->addGroup($tab, 'move', $lang["gen_move"], '&nbsp;');
	$form->setDefaults(array('move' => '0'));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'restart', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'restart', null, $lang["no"], '0');
	$form->addGroup($tab, 'restart', $lang["gen_restart"], '&nbsp;');
	$form->setDefaults(array('restart' => '0'));
		
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	$sub =& $form->addElement('submit', 'submit', $lang["gen_butOK"]);
	$msg = NULL;
	if ($form->validate())	{
		$ret = $form->getSubmitValues();
		if ($ret["generate"]["generate"])	{
			$gbArr =& manageDependencies();
			require_once($path."genCGICFG.php");
			require_once($path."genNagiosCFG.php");
			require_once($path."genResourceCFG.php");
			if($oreon->optGen["perfparse_installed"])
				require_once($path."genPerfparseCFG.php");
			require_once($path."genTimeperiods.php");
			require_once($path."genCommands.php");
			require_once($path."genContacts.php");
			require_once($path."genContactGroups.php");
			require_once($path."genHosts.php");
			require_once($path."genExtendedInfos.php");
			require_once($path."genHostGroups.php");
			require_once($path."genServices.php");
			if ($oreon->user->get_version() == 2)
				require_once($path."genServiceGroups.php");
			require_once($path."genEscalations.php");
			require_once($path."genDependencies.php");
			require_once($path."oreon_pm.php");
			# Meta Module
			if($oreon->optGen["perfparse_installed"])
				if ($files = glob("./include/configuration/configGenerate/metaService/*.php"))
					foreach ($files as $filename)
						require_once($filename);
			# Oreon Modules
			foreach ($oreon->modules as $key=>$value)
				if ($value["gen"] && $files = glob("./modules/".$key."/generate_files/*.php"))
					foreach ($files as $filename)
						require_once($filename);
		}
		if ($ret["xml"]["xml"])	{
			require_once($path."genXMLList.php");
			$pearDB->query("UPDATE `nagios_server` SET `last_restart` = '".time()."' WHERE `id` =1 LIMIT 1");
		}
		if ($ret["debug"]["debug"])	{
			require_once($path."genNagiosCFG-DEBUG.php");
			$stdout = shell_exec($oreon->optGen["nagios_path_bin"] . " -v ".$nagiosCFGPath."nagiosCFG.DEBUG");
			$msg .= str_replace ("\n", "<br>", $stdout);
		}
		if ($ret["move"]["move"])	{
			$msg .= "<br>";
			foreach (glob($nagiosCFGPath ."*.cfg") as $filename) {
				$bool = copy($filename , $oreon->Nagioscfg["cfg_dir"].basename($filename));
				$filename = array_pop(explode("/", $filename));
				$bool ? $msg .= $filename.$lang['gen_mvOk']."<br>" :  $msg .= $filename.$lang['gen_mvKo']."<br>";
			}
		}
		if ($ret["genTraps"]["genTraps"])	{
			require_once($path."genTraps.php");
			$msg .= "<br>".$i." Traps generated<br>";
		}
		if ($ret["restartTrapd"]["restartTrapd"])	{
			$res =& $pearDB->query('SELECT snmp_trapd_path_daemon FROM `general_opt` LIMIT 1');
			if ($res->numRows())	{
				$trap_daemon = $res->fetchRow();
				$stdout = shell_exec("sudo ".$trap_daemon["snmp_trapd_path_daemon"]." restart");
				$msg .= "<br>".str_replace ("\n", "<br>", $stdout);
			}
		}
		if ($ret["restart"]["restart"])	{
			$stdout = shell_exec("sudo /etc/init.d/nagios restart");
			$pearDB->query("UPDATE `nagios_server` SET `last_restart` = '".time()."' WHERE `id` =1 LIMIT 1");
			$msg .= "<br>".str_replace ("\n", "<br>", $stdout);
		}
	}
	
	$form->addElement('header', 'status', $lang["gen_status"]);
	if ($msg)
		$tpl->assign('msg', $msg);
	
	#
	##Apply a template definition
	#
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());	
	$tpl->assign('o', $o);		
	$tpl->display("formGenerateFiles.ihtml");
?>