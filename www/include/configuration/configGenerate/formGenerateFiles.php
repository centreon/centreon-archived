<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

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

	# Get Poller List
	$tab_nagios_server = array("0" => "All Nagios Servers");
	$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` ORDER BY `name`");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while ($nagios =& $DBRESULT->fetchRow())
		$tab_nagios_server[$nagios['id']] = $nagios['name'];
	
	#
	## Form begin
	#
	$attrSelect = array("style" => "width: 220px;");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', $lang["gen_name"]);

	$form->addElement('header', 'infos', $lang["gen_infos"]);
	
    $form->addElement('select', 'host', $lang["gen_host"], $tab_nagios_server, $attrSelect);

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
	$form->addElement('header', 'result', $lang["gen_result"]);
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'debug', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'debug', null, $lang["no"], '0');
	$form->addGroup($tab, 'debug', $lang["gen_debug"], '&nbsp;');
	$form->setDefaults(array('debug' => '1'));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'optimize', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'optimize', null, $lang["no"], '0');
	$form->addGroup($tab, 'optimize', $lang["gen_optimize"], '&nbsp;');
	$form->setDefaults(array('optimize' => '0'));
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
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'restart_mode', null, $lang["gen_restart_load"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'restart_mode', null, $lang["gen_restart_start"], '2');
	$tab[] = &HTML_QuickForm::createElement('radio', 'restart_mode', null, $lang["gen_restart_extcmd"], '3');
	$form->addGroup($tab, 'restart_mode', $lang["gen_restart"], '&nbsp;');
	$form->setDefaults(array('restart_mode' => '1'));

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
	$stdout = NULL;
	if ($form->validate())	{
		$ret = $form->getSubmitValues();
		if ($ret["generate"]["generate"])	{
			$gbArr = manageDependencies();
			$DBRESULT_Servers =& $pearDB->query("SELECT `id`, `localhost` FROM `nagios_server` ORDER BY `name`");
			if (PEAR::isError($DBRESULT_Servers))
				print "DB Error : ".$DBRESULT_Servers->getDebugInfo()."<br>";
			while ($tab =& $DBRESULT_Servers->fetchRow()){
				if (isset($ret["host"]) && $ret["host"] == 0 || $ret["host"] == $tab['id']){	
					unset($DBRESULT2);
					require($path."genCGICFG.php");
					require($path."genNagiosCFG.php");
					require($path."genNdomod.php");
					require($path."genNagiosCFG-DEBUG.php");
					require($path."genResourceCFG.php");
					require($path."genPerfparseCFG.php");
					require($path."genTimeperiods.php");
					require($path."genCommands.php");
					require($path."genContacts.php");
					require($path."genContactGroups.php");
					require($path."genHosts.php");
					require($path."genExtendedInfos.php");
					require($path."genHostGroups.php");
					require($path."genServices.php");
					if ($oreon->user->get_version() == 2)
						require($path."genServiceGroups.php");
					require($path."genEscalations.php");
					require($path."genDependencies.php");
					require($path."centreon_pm.php");
				}
				unset($generatedHG);
				unset($generatedSG);
				unset($generatedS);
			}
			
			/*
			 * Meta Module Generator engine
			 */
			$DBRESULT_Servers =& $pearDB->query("SELECT `id`, `localhost` FROM `nagios_server` ORDER BY `name`");
			if (PEAR::isError($DBRESULT_Servers))
				print "DB Error : ".$DBRESULT_Servers->getDebugInfo()."<br>";
			while ($tab =& $DBRESULT_Servers->fetchRow()){
				if (isset($tab['localhost']) && $tab['localhost'])
					if ($files = glob("./include/configuration/configGenerate/metaService/*.php"))
						foreach ($files as $filename)
							require_once($filename);
			}
			
			/*
			 *  Centreon Modules generator engine
			 */
			 
			if (isset($tab['localhost']) && $tab['localhost'])
				foreach ($oreon->modules as $key=>$value)
					if ($value["gen"] && $files = glob("./modules/".$key."/generate_files/*.php"))
						foreach ($files as $filename)
							require_once($filename);
	
			if ($ret["xml"]["xml"])	{
				require_once($path."genXMLList.php");
				$DBRESULT =& $pearDB->query("UPDATE `nagios_server` SET `last_restart` = '".time()."' WHERE `id` =1 LIMIT 1");
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			}
		}
		
		/*
		 * If debug needed
		 */
		
		if ($ret["debug"]["debug"])	{
			$DBRESULT_Servers =& $pearDB->query("SELECT `id` FROM `nagios_server` ORDER BY `name`");
			if (PEAR::isError($DBRESULT_Servers))
				print "DB Error : ".$DBRESULT_Servers->getDebugInfo()."<br>";
			while ($tab =& $DBRESULT_Servers->fetchRow()){
				if (isset($ret["host"]) && $ret["host"] == 0 || $ret["host"] == $tab['id']){		
					$stdout = shell_exec($oreon->optGen["nagios_path_bin"] . " -v ".$nagiosCFGPath.$tab['id']."/nagiosCFG.DEBUG");
					$msg .= str_replace ("\n", "<br>", $stdout);
				}
			}
		}
		
		if ($ret["optimize"]["optimize"]){
			$DBRESULT_Servers =& $pearDB->query("SELECT `id` FROM `nagios_server` ORDER BY `name`");
			if (PEAR::isError($DBRESULT_Servers))
				print "DB Error : ".$DBRESULT_Servers->getDebugInfo()."<br>";
			while ($tab =& $DBRESULT_Servers->fetchRow()){
				if (isset($ret["host"]) && $ret["host"] == 0 || $ret["host"] == $tab['id']){		
					$stdout = shell_exec($oreon->optGen["nagios_path_bin"] . " -s ".$nagiosCFGPath.$tab['id']."/nagiosCFG.DEBUG");
					$msg .= str_replace ("\n", "<br>", $stdout);
				}
			}
		}
		
		if ($ret["move"]["move"])	{
			$DBRESULT_Servers =& $pearDB->query("SELECT `id`, `localhost` FROM `nagios_server` ORDER BY `name`");
			if (PEAR::isError($DBRESULT_Servers))
				print "DB Error : ".$DBRESULT_Servers->getDebugInfo()."<br>";
			while ($tab =& $DBRESULT_Servers->fetchRow()){
				if (isset($ret["host"]) && $ret["host"] == 0 || $ret["host"] == $tab['id']){
					if (isset($tab['localhost']) && $tab['localhost'] == 1){
						$msg .= "<br>";
						foreach (glob($nagiosCFGPath.$tab['id']."/*.cfg") as $filename) {
							$bool = @copy($filename , $oreon->Nagioscfg["cfg_dir"].basename($filename));
							$filename = array_pop(explode("/", $filename));
							$bool ? $msg .= $filename.$lang['gen_mvOk']."<br>" :  $msg .= $filename.$lang['gen_mvKo']."<br>";
						}
					} else {
						passthru ("echo 'SENDCFGFILE:".$tab['id']."' >> /srv/oreon/var/centcore", $return);	
						print $return;
					}
				}
			}
		}
		
		if (isset($ret["genTraps"]["genTraps"]) && $ret["genTraps"]["genTraps"])	{
			$stdout = shell_exec($oreon->optGen["nagios_path_plugins"]."/traps/centGenSnmpttConfFile 2>&1");
			$msg .= "<br>".str_replace ("\n", "<br>", $stdout);
		}
		
		if ($ret["restart"]["restart"])	{
			$nagios_init_script = (isset($oreon->optGen["nagios_init_script"]) ? $oreon->optGen["nagios_init_script"]   : "/etc/init.d/nagios" );
			if ($ret["restart_mode"]["restart_mode"] == 1)
				if (isset($ret["host"]) && $ret["host"] == 0 || $ret["host"] == $tab['id'])
					$stdout = shell_exec("sudo " . $nagios_init_script . " reload");
				else 
					system("echo 'RELOAD:".$tab['id']."' >> /srv/oreon/var/centcore");
			else if ($ret["restart_mode"]["restart_mode"] == 2)
				if (isset($ret["host"]) && $ret["host"] == 0 || $ret["host"] == $tab['id'])
					$stdout = shell_exec("sudo " . $nagios_init_script . " restart");
				else
					system("echo 'RESTART:".$tab['id']."' >> /srv/oreon/var/centcore");
			else if ($ret["restart_mode"]["restart_mode"] == 3)	{
				require_once("./include/monitoring/external_cmd/functions.php");
				$_GET["select"] = array(0 => 1);
				$_GET["cmd"] = 25;
				require_once("./include/monitoring/external_cmd/cmd.php");
				$stdout = "EXTERNAL COMMAND: RESTART_PROGRAM;\n";
			}
			$DBRESULT =& $pearDB->query("UPDATE `nagios_server` SET `last_restart` = '".time()."' WHERE `id` = '".$tab['id']."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$msg .= "<br>".str_replace ("\n", "<br>", $stdout);
		}
	}

	$form->addElement('header', 'status', $lang["gen_status"]);
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