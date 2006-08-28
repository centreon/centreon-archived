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

	#
	## Database retrieve information for Command
	#
	
	function myDecodeCommand($arg)	{
		$arg = html_entity_decode($arg, ENT_QUOTES);
		$arg = str_replace('#BR#', "\\n", $arg);
		$arg = str_replace('#T#', "\\t", $arg);
		$arg = str_replace('#R#', "\\r", $arg);
		$arg = str_replace('#S#', "/", $arg);
		$arg = str_replace('#BS#', "\\", $arg);
		return($arg);
	}

	$plugins_list = return_plugin($oreon->optGen["nagios_path_plugins"]);
		
	$cmd = array();
	if (($o == "c" || $o == "w") && $command_id)	{
		
		$res =& $pearDB->query("SELECT * FROM command WHERE command_id = '".$command_id."' LIMIT 1");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		# Set base value
		$cmd = array_map("myDecodeCommand", $res->fetchRow());
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Resource Macro
	$resource = array();
	$res =& $pearDB->query("SELECT DISTINCT resource_line FROM cfg_resource ORDER BY resource_line");
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	while($res->fetchInto($row))	{
		$row["resource_line"] = explode("=", $row["resource_line"]);
		$resource[$row["resource_line"][0]] = $row["resource_line"][0];
	}
	$res->free();
	# Nagios Macro
	$macros = array();
	$res =& $pearDB->query("SELECT macro_name FROM nagios_macro ORDER BY macro_name");
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	while($res->fetchInto($row))
		$macros[$row["macro_name"]] = $row["macro_name"];
	$res->free();
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"35");
	$attrsTextarea 	= array("rows"=>"9", "cols"=>"65");

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang["cmd_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["cmd_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["cmd_view"]);

	#
	## Command information
	#
	if ($type == "1")
		$form->addElement('header', 'information', $lang['cmd_notif']);
	else if ($type == "2")
		$form->addElement('header', 'information', $lang['cmd_check']);
	else
		$form->addElement('header', 'information', $lang['cmd_infos']);
	$cmdType[] = &HTML_QuickForm::createElement('radio', 'command_type', null, $lang['cmd_notif'], '1');
	$cmdType[] = &HTML_QuickForm::createElement('radio', 'command_type', null, $lang['cmd_check'], '2');
	$form->addGroup($cmdType, 'command_type', $lang['cmd_type'], '&nbsp;&nbsp;');
	$form->setDefaults(array('command_type' => '2'));
	$form->addElement('text', 'command_name', $lang["cmd_name"], $attrsText);
	$form->addElement('text', 'command_example', $lang["cmd_example"], $attrsText);
	$form->addElement('textarea', 'command_line', $lang["cmd_line"], $attrsTextarea);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action' => '1'));
	
	$form->addElement('select', 'resource', null, $resource);
	$form->addElement('select', 'macros', null, $macros);
	
	ksort($plugins_list);
	$form->addElement('select', 'plugins', null, $plugins_list);
	
	#
	## Further informations
	#
	$form->addElement('hidden', 'command_id');
	$redirectType = $form->addElement('hidden', 'type');
	$redirectType->setValue($type);
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	## Form Rules
	#
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["command_name"]));
	}
	$form->applyFilter('_ALL_', 'trim');
	$form->applyFilter('command_name', 'myReplace');
	$form->applyFilter('_ALL_', 'trim');
	$form->addRule('command_name', $lang['ErrName'], 'required');
	$form->addRule('command_line', $lang['ErrCmdLine'], 'required');
	$form->registerRule('exist', 'callback', 'testCmdExistence');
	$form->addRule('command_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Just watch a Command information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&command_id=".$command_id."&type=".$type."'"));
	    $form->setDefaults($cmd);
		$form->freeze();
	}
	# Modify a Command information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($cmd);
	}
	# Add a Command information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	$tpl->assign('msg', array ("comment"=>$lang['cmd_comment']));
	$tpl->assign('cmd_help',$lang['cmd_help']);
	$tpl->assign("insertValueQuery","
	<script type='text/javascript'>
	<!--
	function insertValueQuery(elem) {
    var myQuery = document.Form.command_line;
	if(elem == 1)	{
		var myListBox = document.Form.resource;
	}
	else if (elem == 2)	{
		var myListBox = document.Form.plugins;
	}
	else if (elem == 3)	{
		var myListBox = document.Form.macros;
	}
    if(myListBox.options.length > 0) {
        var chaineAj = '';
        var NbSelect = 0;
        for(var i=0; i<myListBox.options.length; i++) {
            if (myListBox.options[i].selected){
                NbSelect++;
                if (NbSelect > 1)
                    chaineAj += ', ';
                chaineAj += myListBox.options[i].value;
            }
        }
        //IE support
        if (document.selection) {
            myQuery.focus();
            sel = document.selection.createRange();
            sel.text = chaineAj;
            document.Form.insert.focus();
        }
        //MOZILLA/NETSCAPE support
        else if (document.Form.command_line.selectionStart || document.Form.command_line.selectionStart == '0') {
            var startPos = document.Form.command_line.selectionStart;
            var endPos = document.Form.command_line.selectionEnd;
            var chaineSql = document.Form.command_line.value;

            myQuery.value = chaineSql.substring(0, startPos) + chaineAj + chaineSql.substring(endPos, chaineSql.length);
        } else {
            myQuery.value += chaineAj;
        }
    }
}
	//-->
	</script>");
	$valid = false;
	if ($form->validate())	{
		$cmdObj =& $form->getElement('command_id');
		if ($form->getSubmitValue("submitA"))
			$cmdObj->setValue(insertCommandInDB());
		else if ($form->getSubmitValue("submitC"))
			updateCommandInDB($cmdObj->getValue());
		$o = "w";
		$cmdObj =& $form->getElement('command_id');
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&command_id=".$cmdObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listCommand.php");
	else	{
		##Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formCommand.ihtml");
	}
?>