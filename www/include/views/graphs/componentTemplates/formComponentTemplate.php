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
		exit;

	#
	## Database retrieve information
	#
	$compo = array();
	if (($o == "c" || $o == "w") && $compo_id)	{
		$res =& $pearDB->query("SELECT * FROM giv_components_template WHERE compo_id = '".$compo_id."' LIMIT 1");
		# Set base value
		$compo = array_map("myDecode", $res->fetchRow());
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Graphs comes from DB -> Store in $graphs Array
	$graphs = array();
	$res =& $pearDB->query("SELECT graph_id, name FROM giv_graphs_template ORDER BY name");
	while($res->fetchInto($graph))
		$graphs[$graph["graph_id"]] = $graph["name"];
	$res->free();
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	
	$attrsText 		= array("size"=>"30");
	$attrsText2 	= array("size"=>"10");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"4", "cols"=>"60");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'ftitle', $lang["giv_ct_add"]);
	else if ($o == "c")
		$form->addElement('header', 'ftitle', $lang["giv_ct_change"]);
	else if ($o == "w")
		$form->addElement('header', 'ftitle', $lang["giv_ct_view"]);

	# Basic information
	
	$form->addElement('header', 'information', $lang['giv_ct_infos']);
	$form->addElement('header', 'color', $lang['giv_ct_legend']);
	$form->addElement('header', 'legend', $lang['giv_ct_color']);
	$form->addElement('text', 'name', $lang["giv_ct_name"], $attrsText);
	
	for ($cpt = 1; $cpt <= 100; $cpt++)
		$orders[$cpt] = $cpt;
	
	$form->addElement('select', 'ds_order', $lang['giv_ct_order'], $orders);
	$form->addElement('text', 'ds_name', $lang["giv_ct_dsName"], $attrsText);
	
	$TabColorNameAndLang = array("ds_color_line"=>"giv_ct_lineClr","ds_color_area"=>"giv_ct_areaClr",);

	while (list($nameColor, $val) = each($TabColorNameAndLang))	{
		$nameLang = $lang[$val];
		isset($compo[$nameColor]) ?	$codeColor = $compo[$nameColor] : $codeColor = NULL;
		$title = $lang["genOpt_colorPicker"];
		$attrsText3 	= array("value"=>$codeColor,"size"=>"9","maxlength"=>"7");
		$form->addElement('text', $nameColor, $nameLang,  $attrsText3);
		
		$attrsText4 	= array("style"=>"width:50px; height:18px; background: ".$codeColor." url() left repeat-x 0px; border-color:".$codeColor.";");
		$attrsText5 	= array("onclick"=>"popup_color_picker('$nameColor','$nameLang','$title');");
		$form->addElement('button', $nameColor.'_color', "", $attrsText4);
		
		if ($o == "c" || $o == "a")	{
			$form->addElement('button', $nameColor.'_modify', $lang['modify'], $attrsText5);
		}
	}

	$form->addElement('text', 'ds_transparency', $lang["giv_ct_transparency"], $attrsText3);

	$form->addElement('checkbox', 'ds_filled', $lang["giv_ct_filled"]);
	$form->addElement('checkbox', 'ds_max', $lang["giv_ct_max"]);
	$form->addElement('checkbox', 'ds_min', $lang["giv_ct_min"]);
	$form->addElement('checkbox', 'ds_average', $lang["giv_ct_avg"]);
	$form->addElement('checkbox', 'ds_last', $lang["giv_ct_last"]);
	$form->addElement('checkbox', 'ds_invert', $lang["giv_ct_invert"]);
	$form->addElement('checkbox', 'default_tpl1', $lang["giv_gt_defaultTpl1"]);
	
	$form->addElement('select', 'ds_tickness', $lang["giv_ct_tickness"], array("1"=>"1", "2"=>"2", "3"=>"3"));

	$form->addElement('textarea', 'comment', $lang["giv_gt_comment"], $attrsTextarea);

	#
	## Components linked with
	#
	$form->addElement('header', 'graphs', $lang["giv_graphChoice"]);
    $ams1 =& $form->addElement('advmultiselect', 'compo_graphs', $lang["giv_graphList"], $graphs, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'compo_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	## Form Rules
	#
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('name', $lang['ErrName'], 'required');
	$form->addRule('ds_name', $lang['ErrRequired'], 'required');
	$form->addRule('ds_legend', $lang['ErrRequired'], 'required');
	$form->addRule('ds_color_line', $lang['ErrRequired'], 'required');
    $form->addRule('ds_color_area', $lang['ErrRequired'], 'required');

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
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&compo_id=".$compo_id."'"));
	    $form->setDefaults($compo);
		$form->freeze();
	}
	# Modify
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["delete"]);
	    $form->setDefaults($compo);
	}
	# Add
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["delete"]);
	}
	$tpl->assign('msg', array ("changeL"=>"?p=".$p."&o=c&compo_id=".$compo_id, "changeT"=>$lang['modify']));

	$tpl->assign("sort1", $lang['giv_ct_properties']);
	$tpl->assign("sort2", $lang["giv_graphs"]);

	#
	##Picker Color JS
	#
	$tpl->assign('colorJS',"
	<script type='text/javascript'>
		function popup_color_picker(t,name,title)
		{
			var width = 400;
			var height = 300;
			window.open('./include/common/javascript/color_picker.php?n='+t+'&name='+name+'&title='+title, 'cp', 'resizable=no, location=no, width='
						+width+', height='+height+', menubar=no, status=yes, scrollbars=no, menubar=no');
		}
	</script>
    "
    );
	#
	##End of Picker Color
	#

	$valid = false;
	if ($form->validate())	{
		$compoObj =& $form->getElement('compo_id');
		if ($form->getSubmitValue("submitA"))
			$compoObj->setValue(insertComponentTemplateInDB());
		else if ($form->getSubmitValue("submitC"))
			updateComponentTemplateInDB($compoObj->getValue());
		$o = "w";
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&compo_id=".$compoObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once("listComponentTemplates.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formComponentTemplate.ihtml");
	}
?>