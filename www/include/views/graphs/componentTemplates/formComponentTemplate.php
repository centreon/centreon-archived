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
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'ftitle', _("Add a Data Source Template"));
	else if ($o == "c")
		$form->addElement('header', 'ftitle', _("Modify a Data Source Template"));
	else if ($o == "w")
		$form->addElement('header', 'ftitle', _("View a Data Source Template"));

	# Basic information
	
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('header', 'color', _("Colors"));
	$form->addElement('header', 'legend', _("Legend"));
	$form->addElement('text', 'name', _("Template Name"), $attrsText);
	
	for ($cpt = 1; $cpt <= 100; $cpt++)
		$orders[$cpt] = $cpt;
	
	$form->addElement('select', 'ds_order', _("Order"), $orders);
	$form->addElement('text', 'ds_name', _("Data Source Name"), $attrsText);
	
	$TabColorNameAndLang = array("ds_color_line"=>_("Line color"),"ds_color_area"=>_("Area color"));

	while (list($nameColor, $val) = each($TabColorNameAndLang))	{
		$nameLang = $val;
		isset($compo[$nameColor]) ?	$codeColor = $compo[$nameColor] : $codeColor = NULL;
		$title = _("Pick a color");
		$attrsText3 	= array("value"=>$codeColor,"size"=>"9","maxlength"=>"7");
		$form->addElement('text', $nameColor, $nameLang,  $attrsText3);
		
		$attrsText4 	= array("style"=>"width:50px; height:18px; background: ".$codeColor." url() left repeat-x 0px; border-color:".$codeColor.";");
		$attrsText5 	= array("onclick"=>"popup_color_picker('$nameColor','$nameLang','$title');");
		$form->addElement('button', $nameColor.'_color', "", $attrsText4);
		
		if ($o == "c" || $o == "a")	{
			$form->addElement('button', $nameColor.'_modify', _("Modify"), $attrsText5);
		}
	}

	$form->addElement('text', 'ds_transparency', _("Transparency"), $attrsText3);

	$form->addElement('checkbox', 'ds_filled', _("Filling"));
	$form->addElement('checkbox', 'ds_max', _("Print Max value"));
	$form->addElement('checkbox', 'ds_min', _("Print Min value"));
	$form->addElement('checkbox', 'ds_average', _("Print Average"));
	$form->addElement('checkbox', 'ds_last', _("Print Last Value"));
	$form->addElement('checkbox', 'ds_invert', _("Invert"));
	$form->addElement('checkbox', 'default_tpl1', _("Default Centreon Graph Template"));
	
	$form->addElement('select', 'ds_tickness', _("Thickness"), array("1"=>"1", "2"=>"2", "3"=>"3"));

	$form->addElement('textarea', 'comment', _("Comments"), $attrsTextarea);

	#
	## Components linked with
	#
	$form->addElement('header', 'graphs', _("Graph Choice"));
    $ams1 =& $form->addElement('advmultiselect', 'compo_graphs', _("Graph List"), $graphs, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'compo_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	## Form Rules
	#
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('name', _("Compulsory Name"), 'required');
	$form->addRule('ds_name', _("Required Field"), 'required');
	$form->addRule('ds_legend', _("Required Field"), 'required');
	$form->addRule('ds_color_line', _("Required Field"), 'required');
    $form->addRule('ds_color_area', _("Required Field"), 'required');

	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Just watch
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&compo_id=".$compo_id."'"));
	    $form->setDefaults($compo);
		$form->freeze();
	}
	# Modify
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Delete"));
	    $form->setDefaults($compo);
	}
	# Add
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Delete"));
	}
	$tpl->assign('msg', array ("changeL"=>"?p=".$p."&o=c&compo_id=".$compo_id, "changeT"=>_("Modify")));

	$tpl->assign("sort1", _("Properties"));
	$tpl->assign("sort2", _("Graphs"));

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
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&compo_id=".$compoObj->getValue()."'"));
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