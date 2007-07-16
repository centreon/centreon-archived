<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
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

	$cmd = array("command_type"=>null, "command_name"=>null, "command_line"=>null);
	if (isset($_POST["command_id1"]) && $_POST["command_id1"])
		$command_id = $_POST["command_id1"];
	else if (isset($_POST["command_id2"]) && $_POST["command_id2"])
		$command_id = $_POST["command_id2"];

	if ($o == "w" && $command_id)	{
		function myDecodeCommand($arg)	{
			$arg = html_entity_decode($arg, ENT_QUOTES);
			$arg = str_replace('#BR#', "\\n", $arg);
			$arg = str_replace('#T#', "\\t", $arg);
			$arg = str_replace('#R#', "\\r", $arg);
			$arg = str_replace('#S#', "/", $arg);
			$arg = str_replace('#BS#', "\\", $arg);
			return($arg);
		}
		$DBRESULT =& $pearDB->query("SELECT * FROM command WHERE command_id = '".$command_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		# Set base value
		if ($DBRESULT->numRows())
			$cmd = array_map("myDecodeCommand", $DBRESULT->fetchRow());
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Notification commands comes from DB -> Store in $notifCmds Array
	$notifCmds = array(null=>null);
	$DBRESULT =& $pearDB->query("SELECT command_id, command_name FROM command WHERE command_type = '1' ORDER BY command_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($notifCmd))
		$notifCmds[$notifCmd["command_id"]] = $notifCmd["command_name"];
	$DBRESULT->free();
	# Check commands comes from DB -> Store in $checkCmds Array
	$checkCmds = array(null=>null);
	$DBRESULT =& $pearDB->query("SELECT command_id, command_name FROM command WHERE command_type = '2' ORDER BY command_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($checkCmd))
		$checkCmds[$checkCmd["command_id"]] = $checkCmd["command_name"];
	$DBRESULT->free();

	$attrsText 		= array("size"=>"35");
	$attrsTextarea 	= array("rows"=>"9", "cols"=>"80");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', $lang["cmd_view"]);

	#
	## Command information
	#
	if ($cmd["command_type"] == "1")
		$form->addElement('header', 'information', $lang['cmd_notif']);
	else if ($cmd["command_type"] == "2")
		$form->addElement('header', 'information', $lang['cmd_check']);
	else
		$form->addElement('header', 'information', $lang['cmd_infos']);
	$cmdType[] = &HTML_QuickForm::createElement('radio', 'command_type', null, $lang['cmd_notif'], '1');
	$cmdType[] = &HTML_QuickForm::createElement('radio', 'command_type', null, $lang['cmd_check'], '2');
	$v1 =& $form->addGroup($cmdType, 'command_type', $lang['cmd_type'], '&nbsp;&nbsp;');
	$v1->freeze();
	$v2 =& $form->addElement('text', 'command_name', $lang["cmd_name"], $attrsText);
	$v2->freeze();
	$v3 =& $form->addElement('textarea', 'command_line', $lang["cmd_line"], $attrsTextarea);
	$v3->freeze();
	#
	## Command Select
	#
    $form->addElement('select', 'command_id1', $lang['cmd_check'], $checkCmds, array("onChange"=>"this.form.submit()"));
    $form->addElement('select', 'command_id2', $lang['cmd_notif'], $notifCmds, array("onChange"=>"this.form.submit()"));
    
    $form->setConstants(array("command_name"=>$cmd["command_name"], "command_line"=>$cmd["command_line"], "command_type"=>$cmd["command_type"]["command_type"]));
    
	#
	## Further informations
	#	
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	$min =& $form->addElement('hidden', 'min');
	$min->setValue(1);
	

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	#
	##Apply a template definition
	#	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('command_id', $command_id);
	$tpl->assign('command_name', $cmd["command_name"]);
	$tpl->display("minCommand.ihtml");
?>