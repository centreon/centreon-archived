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

	function display_copying_file($filename = NULL, $status){
		if (!isset($filename))
			return ;
		$str = "<tr><td>- ".$filename."</td>";
		$str .= "<td>".$status."</td></tr>";
		return $str;
	}

	/*
	 *  Get Poller List
	 */
	$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` WHERE `ns_activate` = '1' ORDER BY `localhost` DESC");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$n = $DBRESULT->numRows();
	/*
	 * Display null option
	 */
	if ($n > 1)
		$tab_nagios_server = array(-1 => "");
	/*
	 * Display all servers list
	 */
	for ($i = 0; $nagios =& $DBRESULT->fetchRow(); $i++)
		$tab_nagios_server[$nagios['id']] = $nagios['name'];
	$DBRESULT->free();
	/*
	 * Display all server options
	 */
	if ($n > 1)
		$tab_nagios_server[0] = _("All Nagios Servers");
	
	/*
	 * Form begin
	 */
	$attrSelect = array("style" => "width: 220px;");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', 	_("Nagios Configuration Files Export"));
	$form->addElement('header', 'infos', 	_("Implied Server"));
	$form->addElement('header', 'opt', 		_("Export Options"));
	$form->addElement('header', 'result', 	_("Actions"));	
    
    $form->addElement('select', 'host', 	_("Nagios Server"), $tab_nagios_server, $attrSelect);
	
	$form->addElement('checkbox', 'comment', _("Include Comments"));

	$form->addElement('checkbox', 'debug', _("Run Nagios debug (-v)"));
	$form->setDefaults(array('debug' => '1'));	
	
	$form->addElement('checkbox', 'gen', _("Generate Configuration Files"));
	$form->setDefaults(array('gen' => '1'));	
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
		
		if (!isset($ret["comment"]))
			$ret["comment"] = 0;
		
		$host_list = array();
		foreach ($tab_nagios_server as $key => $value)
			if ($key && ($res["host"] == 0 || $res["host"] == $key))
				$host_list[$key] = $value;

		if (isset($ret["gen"]) && $ret["gen"] && ($ret["host"] == 0 || $ret["host"])){
			/*
			 * Get commands
			 */
			$commands = array();
			$DBRESULT2 =& $pearDB->query("SELECT command_id, command_name FROM command");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
			while ($command = $DBRESULT2->fetchRow())
				$commands[$command["command_id"]] = $command["command_name"] ;
			$DBRESULT2->free();
			
			/*
			 * Get timeperiods
			 */
			$timeperiods = array();
			$DBRESULT2 =& $pearDB->query("SELECT tp_name, tp_id FROM timeperiod");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
			while ($timeperiod =& $DBRESULT2->fetchRow())
				$timeperiods[$timeperiod["tp_id"]] = $timeperiod["tp_name"];
			$DBRESULT2->free();
			
			/*
			 * Check dependancies
			 */
			$gbArr = manageDependencies();
			$DBRESULT_Servers =& $pearDB->query("SELECT `id`, `localhost` FROM `nagios_server` ORDER BY `name`");
			if (PEAR::isError($DBRESULT_Servers))
				print "DB Error : ".$DBRESULT_Servers->getDebugInfo()."<br />";
			while ($tab =& $DBRESULT_Servers->fetchRow()){
				if (isset($ret["host"]) && ($ret["host"] == 0 || $ret["host"] == $tab['id'])) {
					/*
					 * Check temporary files access
					 */
					if (!is_dir($nagiosCFGPath.$tab['id']."/"))
						mkdir($nagiosCFGPath.$tab['id']."/");
					
					unset($DBRESULT2);
					require $path."genCGICFG.php";
					require $path."genNagiosCFG.php";
					require $path."genNdomod.php";
					require $path."genNdo2db.php";
					require $path."genNagiosCFG-DEBUG.php";
					require $path."genResourceCFG.php";
					require $path."genTimeperiods.php";
					require $path."genCommands.php";
					require $path."genContacts.php";
					require $path."genContactGroups.php";
					require $path."genHosts.php";
					require $path."genExtendedInfos.php";
					require $path."genHostGroups.php";
					require $path."genServices.php";
					require $path."genServiceGroups.php";
					require $path."genEscalations.php";
					require $path."genDependencies.php";
					require $path."centreon_pm.php";
				}
				unset($generatedHG);
				unset($generatedSG);
				unset($generatedS);
			}
		}
		
		$flag_localhost = 0;			
		/*
		 * Meta Module Generator engine
		 */
		 
		$DBRESULT_Servers =& $pearDB->query("SELECT `id`, `localhost` FROM `nagios_server` ORDER BY `localhost` DESC");
		if (PEAR::isError($DBRESULT_Servers))
			print "DB Error : ".$DBRESULT_Servers->getDebugInfo()."<br />";
		while ($tab =& $DBRESULT_Servers->fetchRow()){
			if (isset($tab['localhost']) && $tab['localhost']) {
				$flag_localhost = $tab['localhost'];
				if ($files = glob("./include/configuration/configGenerate/metaService/*.php"))
					foreach ($files as $filename)
						require_once($filename);
			}
		}
		
		/*
		 *  Centreon Modules generator engine
		 */
		 
		if ($flag_localhost)
			foreach ($oreon->modules as $key=>$value)
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
			$DBRESULT_Servers =& $pearDB->query("SELECT `nagios_bin` FROM `nagios_server` LIMIT 1");
			$nagios_bin = $DBRESULT_Servers->fetchRow();
			$DBRESULT_Servers->free();
			$msg_debug = array();
			foreach ($tab_server as $host) {
				$stdout = shell_exec("sudo ".$nagios_bin["nagios_bin"] . " -v ".$nagiosCFGPath.$host["id"]."/nagiosCFG.DEBUG");
				$msg_debug[$host['id']] = str_replace ("\n", "<br />", $stdout);
				$msg_debug[$host['id']] = str_replace ("Warning:", "<font color='orange'>Warning</font>", $msg_debug[$host['id']]);
				$msg_debug[$host['id']] = str_replace ("Error:", "<font color='red'>Error</font>", $msg_debug[$host['id']]);
				$msg_debug[$host['id']] = str_replace ("Total Warnings: 0", "<font color='green'>Total Warnings: 0</font>", $msg_debug[$host['id']]);
				$msg_debug[$host['id']] = str_replace ("Total Errors: 0", "<font color='green'>Total Errors: 0</font>", $msg_debug[$host['id']]);		
			}
		}
		

		/*
		 * Move Configuration Files and Images
		 */

		if (isset($ret["move"]) && $ret["move"])	{
			
			/*
			 * Copying image in logos directory
			 * 
			 */
			$DBRESULT_imgs =& $pearDB->query("SELECT `dir_alias`, `img_path` FROM `view_img`, `view_img_dir`, `view_img_dir_relation` WHERE dir_dir_parent_id = dir_id AND img_img_id = img_id");
			while ($images =& $DBRESULT_imgs->fetchrow()){
				if (!is_dir($oreon->optGen["nagios_path_img"]."/".$images["dir_alias"]))
					mkdir($oreon->optGen["nagios_path_img"]."/".$images["dir_alias"]);
				copy($centreon_path."www/img/media/".$images["dir_alias"]."/".$images["img_path"], $oreon->optGen["nagios_path_img"]."/".$images["dir_alias"]."/".$images["img_path"]);
			}
			
			$msg_copy = array();
			foreach ($tab_server as $host)
				if (isset($host['localhost']) && $host['localhost'] == 1){
					$msg_copy[$host["id"]] = "";
					foreach (glob($nagiosCFGPath.$host["id"]."/*.cfg") as $filename) {
						$bool = @copy($filename , $oreon->Nagioscfg["cfg_dir"].basename($filename));
						$filename = array_pop(explode("/", $filename));
						if (!$bool)
							$msg_copy[$host["id"]] .= display_copying_file($filename, _(" - movement <font color='res'>KO</font>"));
					}
					if (strlen($msg_copy[$host["id"]])){
						$msg_copy[$host["id"]] = "<table border=0 width=300>".$msg_copy[$host["id"]]."</table>";
					} else {
						$msg_copy[$host["id"]] = "<br>"._("Centreon : All configuration files copied with success.");
					}
				} else {
					passthru("echo 'SENDCFGFILE:".$host['id']."' >> @CENTREON_VARLIB@/centcore.cmd", $return);
				}
		}
		
		/*
		 * Restart Nagios Poller
		 */
		
		if (isset($ret["restart"]) && $ret["restart"])	{
			$stdout = "";
			$msg_restart = array();
			foreach ($tab_server as $host){
				$nagios_init_script = (isset($oreon->optGen["nagios_init_script"]) ? $oreon->optGen["nagios_init_script"]   : "/etc/init.d/nagios" );
				if ($ret["restart_mode"] == 1){
					if (isset($host['localhost']) && $host['localhost'] == 1){
						$stdout = shell_exec("sudo " . $nagios_init_script . " reload");
					} else { 
						system("echo 'RELOAD:".$host["id"]."' >> @CENTREON_VARLIB@/centcore.cmd");
					}
				} else if ($ret["restart_mode"] == 2) {
					if (isset($host['localhost']) && $host['localhost'] == 1){
						$stdout = shell_exec("sudo " . $nagios_init_script . " restart");
					} else {
						system("echo \"RESTART:".$host["id"]."\" >> @CENTREON_VARLIB@/centcore.cmd", $return);
					}
				} else if ($ret["restart_mode"] == 3)	{
					require_once("./include/monitoring/external_cmd/functions.php");
					$_GET["select"] = array(0 => 1);
					$_GET["cmd"] = 25;
					require_once("./include/monitoring/external_cmd/cmd.php");
					$stdout = "EXTERNAL COMMAND: RESTART_PROGRAM;\n";
					system("echo \"EXTERNALCMD:RESTART_PROGRAM:".$host["id"]."\" >> /usr/local/nagios/var/rw/nagios.cmd");
				}
				$DBRESULT =& $pearDB->query("UPDATE `nagios_server` SET `last_restart` = '".time()."' WHERE `id` = '".$host["id"]."' LIMIT 1");
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
				$msg_restart[$host["id"]] = "<br />".str_replace ("\n", "<br />", $stdout);	
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