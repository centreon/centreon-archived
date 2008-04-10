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

	function display_copying_file($filename = NULL, $status){
		if (!isset($filename))
			return ;
		$str = "<tr><td>- ".$filename."</td>";
		$str .= "<td>".$status."</td></tr>";
		return $str;
	}

	# Get Poller List
	$tab_nagios_server = array("0" => "All Nagios Servers");
	$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` ORDER BY `localhost` DESC");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	while ($nagios =& $DBRESULT->fetchRow())
		$tab_nagios_server[$nagios['id']] = $nagios['name'];
	
	/*
	 * Form begin
	 */
	$attrSelect = array("style" => "width: 220px;");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Nagios Configuration Files Export"));
	$form->addElement('header', 'infos', _("Implied Server"));
	$form->addElement('header', 'opt', _("Export Options"));
	$form->addElement('header', 'result', _("Actions"));	
    
    $form->addElement('select', 'host', _("Nagios Server"), $tab_nagios_server, $attrSelect);
	
	$form->addElement('checkbox', 'comment', _("Include Comments"));
	$form->addElement('checkbox', 'debug', _("Run Nagios debug (-v)"));
	$form->setDefaults(array('debug' => '1'));	
	
	$form->addElement('checkbox', 'optimize', _("Run Optimization test (-s)"));
	$form->addElement('checkbox', 'move', _("Move Export Files"));
	$form->addElement('checkbox', 'restart', _("Restart Nagios"));
	
	$tab_restart_mod = array(2 => _("Restart"), 1 => _("Reload"), 3 => _("External Command"));
	$form->addElement('select', 'restart_mode', _("Method"), $tab_restart_mod, $attrSelect);
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
		$gbArr = manageDependencies();
		
		if (!isset($ret["comment"]))
			$ret["comment"] = 0;
		
		$host_list = array();
		foreach ($tab_nagios_server as $key => $value)
			if ($key && ($res["host"] == 0 || $res["host"] == $key))
				$host_list[$key] = $value;
		
		$DBRESULT_Servers =& $pearDB->query("SELECT `id`, `localhost` FROM `nagios_server` ORDER BY `name`");
		if (PEAR::isError($DBRESULT_Servers))
			print "DB Error : ".$DBRESULT_Servers->getDebugInfo()."<br />";
		while ($tab =& $DBRESULT_Servers->fetchRow()){
			if (isset($ret["host"]) && $ret["host"] == 0 || $ret["host"] == $tab['id']){	
				unset($DBRESULT2);
				require($path."genCGICFG.php");
				require($path."genNagiosCFG.php");
				require($path."genNdo2db.php");
				require($path."genNdomod.php");
				require($path."genNagiosCFG-DEBUG.php");
				require($path."genResourceCFG.php");
				require($path."genTimeperiods.php");
				require($path."genCommands.php");
				require($path."genContacts.php");
				require($path."genContactGroups.php");
				require($path."genHosts.php");
				require($path."genExtendedInfos.php");
				require($path."genHostGroups.php");
				require($path."genServices.php");
				if ($oreon->user->get_version() >= 2)
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
		$DBRESULT_Servers =& $pearDB->query("SELECT `id`, `localhost` FROM `nagios_server` ORDER BY `localhost` DESC");
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
			foreach ($oreon->modules as $key => $value)
				if ($value["gen"] && $files = glob("./modules/".$key."/generate_files/*.php"))
					foreach ($files as $filename)
						require_once($filename);
		
		/*
		 * Create Server List to restart
		 */
		 
		$tab_server = array();
		$DBRESULT_Servers =& $pearDB->query("SELECT `name`, `id`, `localhost` FROM `nagios_server` ORDER BY `localhost` DESC");
		if (PEAR::isError($DBRESULT_Servers))
			print "DB Error : ".$DBRESULT_Servers->getDebugInfo()."<br />";
		while ($tab =& $DBRESULT_Servers->fetchRow()){
			if (isset($ret["host"]) && $ret["host"] == 0 || $ret["host"] == $tab['id'])
				$tab_server[$tab["id"]] = array("id" => $tab["id"], "name" => $tab["name"], "localhost" => $tab["localhost"]);
		}
		
		/*
		 * If debug needed
		 */
				
		if (isset($ret["debug"]) && $ret["debug"])	{
			$msg_debug = array();
			foreach ($tab_server as $host) {
				$stdout = shell_exec($oreon->optGen["nagios_path_bin"] . " -v ".$nagiosCFGPath.$host["id"]."/nagiosCFG.DEBUG");
				$msg_debug[$host['id']] = str_replace ("\n", "<br />", $stdout);
				$msg_debug[$host['id']] = str_replace ("Warning:", "<font color='orange'>Warning</font>", $msg_debug[$host['id']]);
				$msg_debug[$host['id']] = str_replace ("Error:", "<font color='red'>Error</font>", $msg_debug[$host['id']]);
				$msg_debug[$host['id']] = str_replace ("Total Warnings: 0", "<font color='green'>Total Warnings: 0</font>", $msg_debug[$host['id']]);
				$msg_debug[$host['id']] = str_replace ("Total Errors: 0", "<font color='green'>Total Errors: 0</font>", $msg_debug[$host['id']]);		
			}
		}

		/*
		 * Move File
		 */

		if (isset($ret["move"]) && $ret["move"])	{
			$msg_copy = array();
			foreach ($tab_server as $host)
				if (isset($host['localhost']) && $host['localhost'] == 1){
					$msg_copy[$host["id"]] = "<table border=0 width=300>";
					foreach (glob($nagiosCFGPath.$host["id"]."/*.cfg") as $filename) {
						$bool = @copy($filename , $oreon->Nagioscfg["cfg_dir"].basename($filename));
						$filename = array_pop(explode("/", $filename));
						if ($bool)
							;
						else
							$msg_copy[$host["id"]] .= display_copying_file($filename, _(" - movement <font color='res'>KO</font>"));
					}
					$msg_copy[$host["id"]] .= "</table>";
				} else {
					passthru ("echo 'SENDCFGFILE:".$host['id']."' >> /srv/oreon/var/centcore.cmd", $return);
					//print("echo 'SENDCFGFILE:".$host['id']."' >> /srv/oreon/var/centcore.cmd");	
				}
		}
		
		/*
		 * Restart Nagios Poller
		 */
		
		if (isset($ret["restart"]) && $ret["restart"])	{
			$stdout = "";
			$msg_restart = array();
			foreach ($tab_server as $host)
				$nagios_init_script = (isset($oreon->optGen["nagios_init_script"]) ? $oreon->optGen["nagios_init_script"]   : "/etc/init.d/nagios" );
				if ($ret["restart_mode"] == 1){
					if (isset($host['localhost']) && $host['localhost'] == 1){
						$stdout = shell_exec("sudo " . $nagios_init_script . " reload");
					} else { 
						system("echo 'RELOAD:".$host["id"]."' >> /srv/oreon/var/centcore.cmd");
					}
				} else if ($ret["restart_mode"] == 2) {
					if (isset($host['localhost']) && $host['localhost'] == 1){
						$stdout = shell_exec("sudo " . $nagios_init_script . " restart");
					} else {
						system("echo \"RESTART:".$host["id"]."\" >> /srv/oreon/var/centcore.cmd", $return);
					}
				} else if ($ret["restart_mode"] == 3)	{
					require_once("./include/monitoring/external_cmd/functions.php");
					$_GET["select"] = array(0 => 1);
					$_GET["cmd"] = 25;
					require_once("./include/monitoring/external_cmd/cmd.php");
					$stdout = "EXTERNAL COMMAND: RESTART_PROGRAM;\n";
					system("echo \"EXTERNALCMD:RESTART_PROGRAM:".$host["id"]."\" >> /srv/oreon/var/centcore.cmd");
				}
				$DBRESULT =& $pearDB->query("UPDATE `nagios_server` SET `last_restart` = '".time()."' WHERE `id` = '".$host["id"]."' LIMIT 1");
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
				$msg_restart[$host["id"]] = "<br />".str_replace ("\n", "<br />", $stdout);	
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
	
	/*
	 * Apply a template definition
	 */
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("formGenerateFiles.ihtml");
?>