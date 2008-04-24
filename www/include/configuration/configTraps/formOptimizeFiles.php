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
 * For information : contact@oreon-project.org
 */

	if (!isset($oreon))
		exit();

	# Get Poller List
	$tab_nagios_server = array("0" => "All Nagios Servers");
	$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` ORDER BY `name`");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
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
	if ($form->validate())	{
		
		$ret = $form->getSubmitValues();
		
		print "HOST : ".$ret["host"]."\n";
		
		if ($ret["generate"]["generate"])	{
			$gbArr = manageDependencies();
			$DBRESULT_Servers =& $pearDB->query("SELECT `id`, `localhost` FROM `nagios_server` ORDER BY `name`");
			if (PEAR::isError($DBRESULT_Servers))
				print "DB Error : ".$DBRESULT_Servers->getDebugInfo()."<br />";
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
				print "DB Error : ".$DBRESULT_Servers->getDebugInfo()."<br />";
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
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			}
		}
		
		/*
		 * If debug needed
		 */
		
		if ($ret["debug"]["debug"])	{
			$DBRESULT_Servers =& $pearDB->query("SELECT `id` FROM `nagios_server` ORDER BY `name`");
			if (PEAR::isError($DBRESULT_Servers))
				print "DB Error : ".$DBRESULT_Servers->getDebugInfo()."<br />";
			while ($tab =& $DBRESULT_Servers->fetchRow()){
				if (isset($ret["host"]) && $ret["host"] == 0 || $ret["host"] == $tab['id']){		
					print "OK";
					$stdout = shell_exec($oreon->optGen["nagios_path_bin"] . " -v ".$nagiosCFGPath.$tab['id']."/nagiosCFG.DEBUG");
					$msg .= str_replace ("\n", "<br />", $stdout);
				}
			}
		}
		
		if ($ret["optimize"]["optimize"]){
			$DBRESULT_Servers =& $pearDB->query("SELECT `id` FROM `nagios_server` ORDER BY `name`");
			if (PEAR::isError($DBRESULT_Servers))
				print "DB Error : ".$DBRESULT_Servers->getDebugInfo()."<br />";
			while ($tab =& $DBRESULT_Servers->fetchRow()){
				if (isset($ret["host"]) && $ret["host"] == 0 || $ret["host"] == $tab['id']){		
					$stdout = shell_exec($oreon->optGen["nagios_path_bin"] . " -s ".$nagiosCFGPath.$tab['id']."/nagiosCFG.DEBUG");
					$msg .= str_replace ("\n", "<br />", $stdout);
				}
			}
		}
		
		if ($ret["move"]["move"])	{
			$DBRESULT_Servers =& $pearDB->query("SELECT `id`, `localhost` FROM `nagios_server` ORDER BY `name`");
			if (PEAR::isError($DBRESULT_Servers))
				print "DB Error : ".$DBRESULT_Servers->getDebugInfo()."<br />";
			while ($tab =& $DBRESULT_Servers->fetchRow()){
				if (isset($ret["host"]) && $ret["host"] == 0 || $ret["host"] == $tab['id']){
					if (isset($tab['localhost']) && $tab['localhost'] == 1){
						$msg .= "<br />";
						foreach (glob($nagiosCFGPath.$tab['id']."/*.cfg") as $filename) {
							$bool = @copy($filename , $oreon->Nagioscfg["cfg_dir"].basename($filename));
							$filename = array_pop(explode("/", $filename));
							$bool ? $msg .= $filename._(" - movement <font color='green'>OK</font>")."<br />" :  $msg .= $filename._(" - movement <font color='res'>KO</font>")."<br />";
						}
					} else {
						passthru ("echo 'SENDCFGFILE:".$tab['id']."' >> /srv/oreon/var/centcore", $return);	
					}
				}
			}
		}
		
		if (isset($ret["genTraps"]["genTraps"]) && $ret["genTraps"]["genTraps"])	{
			$stdout = shell_exec($oreon->optGen["nagios_path_plugins"]."/traps/centGenSnmpttConfFile 2>&1");
			$msg .= "<br />".str_replace ("\n", "<br />", $stdout);
		}
		
		if ($ret["restart"]["restart"])	{
			/*
			 * Restart Nagios Poller
			 */
			print_r($tab);
			$stdout = "";
			$DBRESULT_Servers =& $pearDB->query("SELECT `id`, `localhost` FROM `nagios_server` ORDER BY `name`");
			if (PEAR::isError($DBRESULT_Servers))
				print "DB Error : ".$DBRESULT_Servers->getDebugInfo()."<br />";
			while ($tab =& $DBRESULT_Servers->fetchRow()){
				$nagios_init_script = (isset($oreon->optGen["nagios_init_script"]) ? $oreon->optGen["nagios_init_script"]   : "/etc/init.d/nagios" );
				if ($ret["restart_mode"] == 1){
					if (isset($ret["host"]) && $ret["host"] == 0 || $ret["host"] == $tab['id']){
						$stdout = shell_exec("sudo " . $nagios_init_script . " reload");
						print "SUDO";
					} else { 
						print "ECHO";
						system("echo 'RELOAD:".$tab['id']."' >> /srv/oreon/var/centcore");
					}
				} else if ($ret["restart_mode"] == 2)
					if (isset($ret["host"]) && $ret["host"] == 0 || $ret["host"] == $tab['id'])
						$stdout = shell_exec("sudo " . $nagios_init_script . " restart");
					else
						system("echo 'RESTART:".$tab['id']."' >> /srv/oreon/var/centcore");
				else if ($ret["restart_mode"] == 3)	{
					require_once("./include/monitoring/external_cmd/functions.php");
					$_GET["select"] = array(0 => 1);
					$_GET["cmd"] = 25;
					require_once("./include/monitoring/external_cmd/cmd.php");
					$stdout = "EXTERNAL COMMAND: RESTART_PROGRAM;\n";
				}
				$DBRESULT =& $pearDB->query("UPDATE `nagios_server` SET `last_restart` = '".time()."' WHERE `id` = '".$tab['id']."' LIMIT 1");
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			}
			$msg .= "<br />".str_replace ("\n", "<br />", $stdout);
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