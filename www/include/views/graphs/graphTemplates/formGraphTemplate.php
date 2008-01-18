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
		
	$graph = array();
	if (($o == "c" || $o == "w") && $graph_id)	{
		$res =& $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$graph_id."' LIMIT 1");
		/*
		 * Set base value
		 */
		$graph = array_map("myDecode", $res->fetchRow());
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Components comes from DB -> Store in $compos Array
	
	$compos = array();
	$res =& $pearDB->query("SELECT compo_id, name FROM giv_components_template ORDER BY name");
	while($res->fetchInto($compo))
		$compos[$compo["compo_id"]] = $compo["name"];
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
		$form->addElement('header', 'ftitle', $lang["giv_gt_add"]);
	else if ($o == "c")
		$form->addElement('header', 'ftitle', $lang["giv_gt_change"]);
	else if ($o == "w")
		$form->addElement('header', 'ftitle', $lang["giv_gt_view"]);

	#
	## Basic information
	#
	$form->addElement('header', 'information', $lang['giv_gt_infos']);
	$form->addElement('header', 'color', $lang['giv_gt_color']);
	$form->addElement('text', 'name', $lang["giv_gt_name"], $attrsText);

	$form->addElement('select', 'img_format', $lang["giv_gt_imgFormat"], array("PNG"=>"PNG", "GIF"=>"GIF"));
	$form->addElement('text', 'vertical_label', $lang["giv_gt_vLabel"], $attrsText);
	$form->addElement('text', 'width', $lang["giv_gt_width"], $attrsText2);
	$form->addElement('text', 'height', $lang["giv_gt_height"], $attrsText2);
	$form->addElement('text', 'lower_limit', $lang["giv_gt_lower_limit"], $attrsText2);
	$form->addElement('text', 'upper_limit', $lang["giv_gt_upper_limit"], $attrsText2);
	$form->addElement('text', 'ds_name', $lang["giv_ct_dsName"], $attrsText);
	$form->addElement('text', 'base', $lang["giv_ct_dsBase"], $attrsText2);
	
	$periods = array(	"10800"=>$lang["giv_sr_p3h"],
						"21600"=>$lang["giv_sr_p6h"],
						"43200"=>$lang["giv_sr_p12h"],
						"86400"=>$lang["giv_sr_p24h"],
						"172800"=>$lang["giv_sr_p2d"],
						"302400"=>$lang["giv_sr_p4d"],	
						"604800"=>$lang["giv_sr_p7d"],
						"1209600"=>$lang["giv_sr_p14d"],
						"2419200"=>$lang["giv_sr_p28d"],
						"2592000"=>$lang["giv_sr_p30d"],
						"2678400"=>$lang["giv_sr_p31d"],
						"5184000"=>$lang["giv_sr_p2m"],
						"10368000"=>$lang["giv_sr_p4m"],
						"15552000"=>$lang["giv_sr_p6m"],
						"31104000"=>$lang["giv_sr_p1y"]);	
	
	$sel =& $form->addElement('select', 'period', $lang["giv_sr_period"], $periods);
	$steps = array(	"0"=>$lang["giv_sr_noStep"],
					"2"=>"2",
					"6"=>"6",
					"10"=>"10",
					"20"=>"20",
					"50"=>"50",
					"100"=>"100");					
	
	$sel =& $form->addElement('select', 'step', $lang["giv_sr_step"], $steps);

	$TabColorNameAndLang 	= array(	"bg_grid_color"=>"giv_gt_bgGridClr",
                                    	"grid_main_color"=>"giv_gt_bgGridPClr",
                                    	"grid_sec_color"=>"giv_gt_bgGridSClr",
                                    	"contour_cub_color"=>"giv_gt_bgContClr",
                                    	"bg_color"=>"giv_gt_bgClr",
                                    	"police_color"=>"giv_gt_bgPol",
                                    	"col_arrow"=>"giv_gt_arrClr",
                                    	"col_top"=>"giv_gt_topClr",
                                    	"col_bot"=>"giv_gt_botClr",
					);

	while (list($nameColor, $val) = each($TabColorNameAndLang))	{
		$nameLang = $lang[$val];
		isset($graph[$nameColor]) ?	$codeColor = $graph[$nameColor] : $codeColor = NULL;
		$title = $lang["genOpt_colorPicker"];
		$attrsText3 	= array("value"=>$codeColor,"size"=>"8","maxlength"=>"7");
		$attrsText4 	= array("style"=>"width:50px; height:18px; background: ".$codeColor." url() left repeat-x 0px; border-color:".$codeColor.";");
		$attrsText5 	= array("onclick"=>"popup_color_picker('$nameColor','$nameLang','$title');");
		
		$form->addElement('text', $nameColor, $nameLang,  $attrsText3);
		$form->addElement('button', $nameColor.'_color', "", $attrsText4);
		if ($o == "c" || $o == "a")	{
			$form->addElement('button', $nameColor.'_modify', $lang['modify'], $attrsText5);
		}
	}

	
	$form->addElement('checkbox', 'stacked', $lang["giv_gt_stacked"]);
	$form->addElement('checkbox', 'split_component', $lang["giv_split_component"]);
	$form->addElement('textarea', 'comment', $lang["giv_gt_comment"], $attrsTextarea);
	$form->addElement('checkbox', 'default_tpl1', $lang["giv_gt_defaultTpl1"]);
	
	/*
	 * Components linked with
	 */
	$form->addElement('header', 'compos', $lang["giv_compoChoice"]);
    $ams1 =& $form->addElement('advmultiselect', 'graph_compos', $lang["giv_compoList"], $compos, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'graph_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Form Rules
	 */	
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('name', $lang['ErrName'], 'required');
	$form->addRule('vertical_label', $lang['ErrRequired'], 'required');
	$form->addRule('width', $lang['ErrRequired'], 'required');
	$form->addRule('height', $lang['ErrRequired'], 'required');
	/* 
	$form->addRule('bg_grid_color', $lang['ErrRequired'], 'required');
    $form->addRule('grid_main_color', $lang['ErrRequired'], 'required');
	$form->addRule('grid_sec_color', $lang['ErrRequired'], 'required');
    $form->addRule('contour_cub_color', $lang['ErrRequired'], 'required');
    $form->addRule('bg_color', $lang['ErrRequired'], 'required');
    $form->addRule('police_color', $lang['ErrRequired'], 'required');
    $form->addRule('col_arrow', $lang['ErrRequired'], 'required');
    $form->addRule('col_top', $lang['ErrRequired'], 'required');
    $form->addRule('col_bot', $lang['ErrRequired'], 'required');
	*/
	$form->addRule('title', $lang['ErrRequired'], 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);


	/*
	 * Smarty template Init
	 */

	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Just watch
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&graph_id=".$graph_id."'"));
	    $form->setDefaults($graph);
		$form->freeze();
	} else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["delete"]);
	    $form->setDefaults($graph);
	} else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["delete"]);
	}
	$tpl->assign('msg', array ("changeL"=>"?p=".$p."&o=c&graph_id=".$graph_id, "changeT"=>$lang['modify']));

	$tpl->assign("sort1", $lang['giv_gt_properties']);
	$tpl->assign("sort2", $lang["giv_compo"]);

	/*
	 * Picker Color JS
	 */

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
    
	/*
	 * End of Picker Color
	 */

	$valid = false;
	if ($form->validate())	{
		$graphObj =& $form->getElement('graph_id');
		if ($form->getSubmitValue("submitA"))
			$graphObj->setValue(insertGraphTemplateInDB());
		else if ($form->getSubmitValue("submitC"))
			updateGraphTemplateInDB($graphObj->getValue());
		$o = "w";
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&graph_id=".$graphObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once("listGraphTemplates.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formGraphTemplate.ihtml");
	}
?>