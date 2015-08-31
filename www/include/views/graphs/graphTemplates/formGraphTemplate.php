<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */
	if (!isset($oreon))
		exit();

	$graph = array();
	if (($o == "c" || $o == "w") && $graph_id)	{
		$res = $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$graph_id."' LIMIT 1");
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
	$res = $pearDB->query("SELECT compo_id, name FROM giv_components_template ORDER BY name");
	while ($compo = $res->fetchRow())
		$compos[$compo["compo_id"]] = $compo["name"];
	$res->free();

	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#

	$attrsText 	= array("size"=>"30");
	$attrsText2 	= array("size"=>"6");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"3", "cols"=>"30");
	$eTemplate	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'ftitle', _("Add a Graph Template"));
	else if ($o == "c")
		$form->addElement('header', 'ftitle', _("Modify a Graph Template"));
	else if ($o == "w")
		$form->addElement('header', 'ftitle', _("View a Graph Template"));

	#
	## Basic information
	#
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('header', 'color', _("Legend"));
	$form->addElement('text', 'name', _("Template Name"), $attrsText);

	$form->addElement('select', 'img_format', _("Image Type"), array("PNG"=>"PNG", "GIF"=>"GIF"));
	$form->addElement('text', 'vertical_label', _("Vertical Label"), $attrsText);
	$form->addElement('text', 'width', _("Width"), $attrsText2);
	$form->addElement('text', 'height', _("Height"), $attrsText2);
	$form->addElement('text', 'lower_limit', _("Lower Limit"), $attrsText2);
	$form->addElement('text', 'upper_limit', _("Upper Limit"), array('id'     => 'upperLimitTxt',
																	 'size'   => '6'));
	$form->addElement('checkbox', 'size_to_max', _("Size to max"), '', array('id'      => 'sizeToMax',
																		     'onClick' => 'sizeToMaxx();'));
	$form->addElement('text', 'ds_name', _("Data Source Name"), $attrsText);
	$form->addElement('select', 'base', _("Base"), array("1000"=>"1000", "1024"=>"1024"));

	$periods = array(	"10800"=>_("Last 3 Hours"),
						"21600"=>_("Last 6 Hours"),
						"43200"=>_("Last 12 Hours"),
						"86400"=>_("Last 24 Hours"),
						"172800"=>_("Last 2 Days"),
						"302400"=>_("Last 4 Days"),
						"604800"=>_("Last 7 Days"),
						"1209600"=>_("Last 14 Days"),
						"2419200"=>_("Last 28 Days"),
						"2592000"=>_("Last 30 Days"),
						"2678400"=>_("Last 31 Days"),
						"5184000"=>_("Last 2 Months"),
						"10368000"=>_("Last 4 Months"),
						"15552000"=>_("Last 6 Months"),
						"31104000"=>_("Last Year"));

	$sel = $form->addElement('select', 'period', _("Graph Period"), $periods);
	$steps = array(	"0"=>_("No Step"),
					"2"=>"2",
					"6"=>"6",
					"10"=>"10",
					"20"=>"20",
					"50"=>"50",
					"100"=>"100");

	$sel = $form->addElement('select', 'step', _("Recovery Step"), $steps);

	$TabColorNameAndLang 	= array(	"bg_grid_color"=>_("Grid background color"),
                                    	"grid_main_color"=>_("Main grid color"),
                                    	"grid_sec_color"=>_("Secondary grid color"),
                                    	"contour_cub_color"=>_("Outline color"),
                                    	"bg_color"=>_("Background color"),
                                    	"police_color"=>_("Text color"),
                                    	"col_arrow"=>_("Arrow color"),
                                    	"col_top"=>_("Top color"),
                                    	"col_bot"=>_("Bottom color"),
					);

	while (list($nameColor, $val) = each($TabColorNameAndLang))	{
		$nameLang = $val;
		isset($graph[$nameColor]) ?	$codeColor = $graph[$nameColor] : $codeColor = NULL;
		$attrsText3 	= array("value"=>$codeColor,"size"=>"8","maxlength"=>"7");
		$attrsText4 	= array("style"=>"width:50px; height:18px; background-color:".$codeColor."; border-color:".$codeColor.";");
		$attrsText5 	= array("onclick"=>"popup_color_picker('$nameColor','".addslashes($nameLang)."');");

		$form->addElement('text', $nameColor, $nameLang,  $attrsText3);
		$form->addElement('button', $nameColor.'_color', "", $attrsText4);
		if ($o == "c" || $o == "a")	{
			$form->addElement('button', $nameColor.'_modify', _("Modify"), $attrsText5);
		}
	}


	$form->addElement('checkbox', 'stacked', _("Stacking"));
	$form->addElement('checkbox', 'split_component', _("Split Components"));
	$form->addElement('checkbox', 'scaled', _("Scale Graph Values"));
	$form->addElement('textarea', 'comment', _("Comments"), $attrsTextarea);
	$form->addElement('checkbox', 'default_tpl1', _("Default Centreon Graph Template"));

	/*
	 * Components linked with
	 */
	$form->addElement('header', 'compos', _("Data Source Choice"));
	$ams1 = $form->addElement('advmultiselect', 'graph_compos', array(_("Data Source List"),_("Available"), _("Selected")), $compos, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($eTemplate);
	echo $ams1->getElementJs(false);

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'graph_id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Form Rules
	 */
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('name', _("Compulsory Name"), 'required');
	$form->addRule('vertical_label', _("Required Field"), 'required');
	$form->addRule('width', _("Required Field"), 'required');
	$form->addRule('height', _("Required Field"), 'required');
	$form->addRule('title', _("Required Field"), 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));


	/*
	 * Smarty template Init
	 */

	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Just watch
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&graph_id=".$graph_id."'"));
	    $form->setDefaults($graph);
		$form->freeze();
	} else if ($o == "c")	{
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Delete"));
	    $form->setDefaults($graph);
	} else if ($o == "a")	{
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Delete"));
	}
	$tpl->assign('msg', array ("changeL"=>"?p=".$p."&o=c&graph_id=".$graph_id, "changeT"=>_("Modify")));

	$tpl->assign("sort1", _("Properties"));
	$tpl->assign("sort2", _("Data Sources"));
                // prepare help texts
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) {
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);
        

	/*
	 * Picker Color JS
	 */

	$tpl->assign('colorJS',"
	<script type='text/javascript'>
		function popup_color_picker(t,name) {
			var width = 318;
			var height = 314;
			var hcolor = '000000';
			var i_elem = document.getElementsByName(t+'_color').item(0);
			if ( i_elem != null ) {
				var bckcolor = i_elem.style.backgroundColor;
				var exp = new RegExp('rgb','g');
				if (exp.test(bckcolor)) {
					exp = new RegExp('[0-9]+','g');
					var tab_rgb = bckcolor.match(exp);
					hcolor = dechex(parseInt(tab_rgb[0]))+dechex(parseInt(tab_rgb[1]))+dechex(parseInt(tab_rgb[2]));
				} else {
					hcolor = bckcolor.substr(1,6);
				}
			}
			Modalbox.show('./include/common/javascript/color_picker_mb.php?name='+name, { title: '" . _('Pick a color') . "', width: width, height: height , afterLoad: function(){cp_init(t, hcolor);} });
                }
	</script>
    "
    );

	/*
	 * End of Picker Color
	 */

	$valid = false;
	if ($form->validate())	{
		$graphObj = $form->getElement('graph_id');
		if ($form->getSubmitValue("submitA"))
			$graphObj->setValue(insertGraphTemplateInDB());
		else if ($form->getSubmitValue("submitC"))
			updateGraphTemplateInDB($graphObj->getValue());
		$o = "w";
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&graph_id=".$graphObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}

	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once("listGraphTemplates.php");
	else	{
		#Apply a template definition
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formGraphTemplate.ihtml");
	}
?>
<script type='text/javascript'>
document.onReady = sizeToMaxx();

function sizeToMaxx()
{
	var upperLimitTxt = document.getElementById('upperLimitTxt');
	var sizeToMax = document.getElementById('sizeToMax');

	if (sizeToMax.checked == true) {
		upperLimitTxt.disabled = true;
	} else {
		upperLimitTxt.disabled = false;
	}
}
</script>