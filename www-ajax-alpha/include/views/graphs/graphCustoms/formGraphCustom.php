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
	## Database retrieve information
	#
	$graph = array();
	if (($o == "c" || $o == "w") && $graph_id)	{	
		$res =& $pearDB->query("SELECT * FROM giv_graphs WHERE graph_id = '".$graph_id."' LIMIT 1");
		# Set base value
		$graph = array_map("myDecode", $res->fetchRow());	
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Components comes from DB -> Store in $compos Array
	$graphTs = array(NULL=>NULL);
	$res =& $pearDB->query("SELECT graph_id, name FROM giv_graphs_template ORDER BY name");
	while($res->fetchInto($graphT))
		$graphTs[$graphT["graph_id"]] = $graphT["name"];
	$res->free();
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsText2 	= array("size"=>"6");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"3", "cols"=>"30");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'ftitle', $lang["giv_gg_add"]);
	else if ($o == "c")
		$form->addElement('header', 'ftitle', $lang["giv_gg_change"]);
	else if ($o == "w")
		$form->addElement('header', 'ftitle', $lang["giv_gg_view"]);

	#
	## Basic information
	#
	$form->addElement('header', 'information', $lang['giv_gg_infos']);
	$form->addElement('text', 'name', $lang["giv_gg_name"], $attrsText);	
	$form->addElement('select', 'grapht_graph_id', $lang["giv_gg_tpl"], $graphTs);
	$form->addElement('textarea', 'comment', $lang["giv_gg_comment"], $attrsTextarea);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'graph_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	$form->applyFilter('_ALL_', 'trim');
	$form->addRule('name', $lang['ErrName'], 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);
	
	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Just watch
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&graph_id=".$graph_id."'"));
	    $form->setDefaults($graph);
		$form->freeze();
	}
	# Modify
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["delete"]);
	    $form->setDefaults($graph);
	}
	# Add
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["delete"]);
	}
	$tpl->assign('msg', array ("changeL"=>"?p=".$p."&o=c&graph_id=".$graph_id, "changeT"=>$lang['modify']));
	
	#
	##End of Picker Color
	#
	
	$valid = false;
	if ($form->validate())	{
		$graphObj =& $form->getElement('graph_id');
		if ($form->getSubmitValue("submitA"))
			$graphObj->setValue(insertGraphInDB());
		else if ($form->getSubmitValue("submitC"))
			updateGraphInDB($graphObj->getValue());
		$o = "w";
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&graph_id=".$graphObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once("listGraphCustoms.php");
	else	{
		#Apply a template definition	
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formGraphCustom.ihtml");
	}
?>