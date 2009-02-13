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
 
 	/*
	 * Form Rules
	 */
	
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["command_name"]));
	}
	
	/*
	 * Database retrieve information for Command
	 */
	function myDecodeCommand($arg) {
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
		$DBRESULT =& $pearDB->query("SELECT * FROM `command` WHERE `command_id` = '".$command_id."' LIMIT 1");
		# Set base value
		$cmd = array_map("myDecodeCommand", $DBRESULT->fetchRow());
	}
	
	/*
	 * Resource Macro
	 */

	$resource = array();
	$DBRESULT =& $pearDB->query("SELECT DISTINCT `resource_name`, `resource_comment` FROM `cfg_resource` ORDER BY `resource_line`");
	while ($row = $DBRESULT->fetchRow()){
		$resource[$row["resource_name"]] = $row["resource_name"];
		if (isset($row["resource_comment"]) && $row["resource_comment"] != "")
			 $resource[$row["resource_name"]] .= " (".$row["resource_comment"].")";
	}
	$DBRESULT->free();
	
	/*
	 * Graphs Template comes from DB -> Store in $graphTpls Array
	 */
	$graphTpls = array(NULL=>NULL);
	$DBRESULT =& $pearDB->query("SELECT `graph_id`, `name` FROM `giv_graphs_template` ORDER BY `name`");
	while ($graphTpl =& $DBRESULT->fetchRow())
		$graphTpls[$graphTpl["graph_id"]] = $graphTpl["name"];
	$DBRESULT->free();
	
	/*
	 * Nagios Macro
	 */
	$macros = array();
	$DBRESULT =& $pearDB->query("SELECT `macro_name` FROM `nagios_macro` ORDER BY `macro_name`");
	while ($row =& $DBRESULT->fetchRow())
		$macros[$row["macro_name"]] = $row["macro_name"];
	$DBRESULT->free();
	
	$attrsText 		= array("size"=>"35");
	$attrsTextarea 	= array("rows"=>"9", "cols"=>"65");

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Command"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Command"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Command"));

	/*
	 * Command information
	 */
	if ($type == "1")
		$form->addElement('header', 'information', _("Notification"));
	else if ($type == "2")
		$form->addElement('header', 'information', _("Check"));
	else if ($type == "3")
		$form->addElement('header', 'information', _("Check"));
	else
		$form->addElement('header', 'information', _("Information"));
	
	$cmdType[] = &HTML_QuickForm::createElement('radio', 'command_type', null, _("Notification"), '1');
	$cmdType[] = &HTML_QuickForm::createElement('radio', 'command_type', null, _("Check"), '2');
	$cmdType[] = &HTML_QuickForm::createElement('radio', 'command_type', null, _("Misc"), '3');
	
	$form->addGroup($cmdType, 'command_type', _("Command Type"), '&nbsp;&nbsp;');
	$form->setDefaults(array('command_type' => '2'));
	$form->addElement('text', 'command_name', _("Command Name"), $attrsText);
	$form->addElement('text', 'command_example', _("Argument Example"), $attrsText);
	$form->addElement('text', 'command_hostaddress', _("\$HOSTADDRESS\$"), $attrsText);
	$form->addElement('textarea', 'command_line', _("Command Line"), $attrsTextarea);
	$form->addElement('select', 'graph_id', _("Graph template"), $graphTpls);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action' => '1'));
	
	$form->addElement('select', 'resource', null, $resource);
	$form->addElement('select', 'macros', null, $macros);
	
	ksort($plugins_list);
	$form->addElement('select', 'plugins', null, $plugins_list);
	
	/*
	 * Further informations
	 */
	$form->addElement('hidden', 'command_id');
	$redirectType = $form->addElement('hidden', 'type');
	$redirectType->setValue($type);
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('command_name', 'myReplace');
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('command_name', _("Compulsory Name"), 'required');
	$form->addRule('command_line', _("Compulsory Command Line"), 'required');
	$form->registerRule('exist', 'callback', 'testCmdExistence');
	$form->addRule('command_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/*
	 * Just watch a Command information
	 */
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&command_id=".$command_id."&type=".$type."'"));
	    $form->setDefaults($cmd);
		$form->freeze();
	} else if ($o == "c")	{
		/*
		 * Modify a Command information
		 */
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($cmd);
	} else if ($o == "a")	{
		/*
		 * Add a Command information
		 */
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}
	
	$tpl->assign('msg', array ("comment"=>_("Commands definitions can contain Macros but they have to be valid.")));
	$tpl->assign('cmd_help',_("Plugin Help"));
	$tpl->assign('cmd_play',_("Test the plugin"));
	
	$valid = false;
	if ($form->validate())	{
		$cmdObj =& $form->getElement('command_id');
		if ($form->getSubmitValue("submitA"))
			$cmdObj->setValue(insertCommandInDB());
		else if ($form->getSubmitValue("submitC"))
			updateCommandInDB($cmdObj->getValue());
		$o = NULL;
		$cmdObj =& $form->getElement('command_id');
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&command_id=".$cmdObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	
	?><script type='text/javascript'>
	<!--
	function insertValueQuery(elem) {
	    var myQuery = document.Form.command_line;
		if(elem == 1)	{
			var myListBox = document.Form.resource;
		} else if (elem == 2)	{
			var myListBox = document.Form.plugins;
		} else if (elem == 3)	{
			var myListBox = document.Form.macros;
		}
	    if (myListBox.options.length > 0) {
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
	
	        if (document.selection) {
	        	// IE support
	            myQuery.focus();
	            sel = document.selection.createRange();
	            sel.text = chaineAj;
	            document.Form.insert.focus();
	        } else if (document.Form.command_line.selectionStart || document.Form.command_line.selectionStart == '0') {
	        	// MOZILLA/NETSCAPE support
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
	</script><?php
	
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listCommand.php");
	else	{
		/*
		 * Apply a template definition
		 */
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formCommand.ihtml");
	}
?>