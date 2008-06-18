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
  	
 	$error_flag = 0;
	$command = $_GET["command_line"];
	$example = $_GET["command_example"];		
	$args = split("!", $example);

	$resource_def = str_replace('$', '@DOLLAR@', $command);
	while (preg_match("/@DOLLAR@USER([0-9]+)@DOLLAR@/", $resource_def, $matches))	{
			$DBRESULT =& $pearDB->query("SELECT resource_line FROM cfg_resource WHERE resource_name = '\$USER".$matches[1]."\$' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$resource = $DBRESULT->fetchRow();
			$resource_def = str_replace("@DOLLAR@USER". $matches[1] ."@DOLLAR@", $resource["resource_line"], $resource_def);
	}		
	while (preg_match("/@DOLLAR@HOSTADDRESS@DOLLAR@/", $resource_def, $matches))	{			
			if (isset($_GET["command_hostaddress"]))
				$resource_def = str_replace("@DOLLAR@HOSTADDRESS@DOLLAR@", $_GET["command_hostaddress"], $resource_def);
			else
				$error_flag = 1;
	}
	while (preg_match("/@DOLLAR@ARG([0-9]+)@DOLLAR@/", $resource_def, $matches))	{
			$match_id = $matches[1];
			if (isset($args[$match_id])){
				$resource_def = str_replace("@DOLLAR@ARG". $match_id ."@DOLLAR@", $args[$match_id], $resource_def);
				$resource_def = str_replace('$', '@DOLLAR@', $resource_def);
				if(preg_match("/@DOLLAR@USER([0-9]+)@DOLLAR@/", $resource_def, $matches)) {
					$DBRESULT =& $pearDB->query("SELECT resource_line FROM cfg_resource WHERE resource_name = '\$USER".$matches[1]."\$' LIMIT 1");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
					$resource = $DBRESULT->fetchRow();
				$resource_def = str_replace("@DOLLAR@USER". $matches[1] ."@DOLLAR@", $resource["resource_line"], $resource_def);
				}
				if (preg_match("/@DOLLAR@HOSTADDRESS@DOLLAR@/", $resource_def, $matches)) {
					if (isset($_GET["command_hostaddress"]))
						$resource_def = str_replace("@DOLLAR@HOSTADDRESS@DOLLAR@", $_GET["command_hostaddress"], $resource_def);
					else
						$error_flag = 1;
				}	
			}
			else
				$error_flag = 1;
	}
	
	$command = $resource_def;
	$stdout = array();
	unset($stdout);
	
	//$command = "/usr/local/nagios/libexec/check_centreon_dummy -s 1 -o \"OK my ass\"";	
	exec($command, $stdout, $status);	
	$msg = join(" ",$stdout);
	$msg = str_replace("\n", "<br />", $msg);
	
	if ($status == 1)
		$status = "WARNING";
	else if ($status == 2)
		$status = "CRITICAL";
	else if ($status == 0)
		$status = "OK";	
	else
		$status = "UN" .
				"KNOWN";
	$attrsText 	= array("size"=>"25");
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title',_("Plugin Test"));
	#
	## Command information
	#
	$form->addElement('header', 'information', _("Plugin test"));
	$form->addElement('text', 'command_line', _("Command Line"), $attrsText);
	$form->addElement('text', 'command_help', _("Output"), $attrsText);
	$form->addElement('text', 'command_status', _("Status"), $attrsText);
	

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	$tpl->assign('command_line', $command);
	if (isset($msg) && $msg)
		$tpl->assign('msg', $msg);
	if (isset($status))
		$tpl->assign('status', $status);

	#
	##Apply a template definition
	#
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("minPlayCommand.ihtml");
?>