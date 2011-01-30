<?php
/*
 * Copyright 2005-2010 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */
	if (!isset($oreon))
		exit;

	$compo = array();
	if (($o == "c" || $o == "w") && $compo_id)	{
		$res = $pearDB->query("SELECT * FROM giv_components_template WHERE compo_id = '".$compo_id."' LIMIT 1");
		/*
		 * Set base value
		 */
		$compo = array_map("myDecode", $res->fetchRow());
	}


	/*
	 * Graphs comes from DB -> Store in $graphs Array
	 */
	$graphs = array();
	$res = $pearDB->query("SELECT graph_id, name FROM giv_graphs_template ORDER BY name");
	while ($graph = $res->fetchRow())
		$graphs[$graph["graph_id"]] = $graph["name"];
	$res->free();

	/*
	 * List of known data sources
	 */

	$datasources = array();
	$DBRESULT = $pearDBO->query("SELECT DISTINCT `metric_name`, `unit_name` FROM `metrics` ORDER BY `metric_name`");
	while ($row = $DBRESULT->fetchRow()){
		$datasources[$row["metric_name"]] = $row["metric_name"];
		if (isset($row["unit_name"]) && $row["unit_name"] != "") {
			 $datasources[$row["metric_name"]] .= " (".$row["unit_name"].")";
		}
	}
	unset($row);
	$DBRESULT->free();

	/*
	 * Define Styles
	 */
	$attrsText 		= array("size"=>"30");
	$attrsText2 	= array("size"=>"10");
	$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"4", "cols"=>"60");
	$template	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'ftitle', _("Add a Data Source Template"));
	else if ($o == "c")
		$form->addElement('header', 'ftitle', _("Modify a Data Source Template"));
	else if ($o == "w")
		$form->addElement('header', 'ftitle', _("View a Data Source Template"));

	/*
	 *  Basic information
	 */
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('header', 'color', _("Colors"));
	$form->addElement('header', 'legend', _("Legend"));
	$form->addElement('text', 'name', _("Template Name"), $attrsText);

	for ($cpt = 1; $cpt <= 100; $cpt++) {
		$orders[$cpt] = $cpt;
	}

	$form->addElement('select', 'ds_order', _("Order"), $orders);
	$form->addElement('text', 'ds_name', _("Data Source Name"), $attrsText);
	$form->addElement('select', 'datasources', null, $datasources);

	$TabColorNameAndLang = array("ds_color_line"=>_("Line color"),"ds_color_area"=>_("Area color"));

	while (list($nameColor, $val) = each($TabColorNameAndLang))	{
		$nameLang = $val;
		if ($nameColor == "ds_color_area") {
			isset($compo[$nameColor]) ?	$codeColor = $compo[$nameColor] : $codeColor = "#FFFFFF";
		} else if ($nameColor == "ds_color_line")
			isset($compo[$nameColor]) ?	$codeColor = $compo[$nameColor] : $codeColor = "#0000FF";

		$title = _("Pick a color");
		$attrsText3 	= array("value"=>$codeColor,"size"=>"9","maxlength"=>"7");
		$form->addElement('text', $nameColor, $nameLang,  $attrsText3);

		$attrsText4 	= array("style"=>"width:50px; height:18px; background-color: ".$codeColor."; border-color:".$codeColor.";");
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

	/*
	 * Components linked with
	 */
	$form->addElement('header', 'graphs', _("Graph Choice"));
	$ams1 = $form->addElement('advmultiselect', 'compo_graphs', array(_("Graph List"),_("Available"), _("Selected")), $graphs, $attrsAdvSelect, SORT_ASC);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'compo_id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	function testFilled() {

	}

	/*
	 * Form Rules
	 */
	$form->registerRule('exist', 'callback', 'testExistence');

	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('ds_name', _("Compulsory Name"), 'required');
	$form->addRule('ds_name', _("Name is already in use"), 'exist');
	$form->addRule('ds_name', _("Required Field"), 'required');
	$form->addRule('ds_legend', _("Required Field"), 'required');
	$form->addRule('ds_color_line', _("Required Field"), 'required');

	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	if ($o == "w")	{
		/*
		 * Just watch
		 */
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&compo_id=".$compo_id."'"));
		$form->setDefaults($compo);
		$form->freeze();
	} else if ($o == "c")	{
		/*
		 * Modify
		 */
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Delete"));
		$form->setDefaults($compo);
	} else if ($o == "a")	{
		/*
		 * Add
		 */
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Delete"));
		$form->setDefaults(array("ds_color_area" => "#FFFFFF", "ds_color_line" => "#0000FF", "ds_transparency" => "80", "ds_average" => true, "ds_last" => true));
	}
	$tpl->assign('msg', array ("changeL"=>"?p=".$p."&o=c&compo_id=".$compo_id, "changeT"=>_("Modify")));

	$tpl->assign("sort1", _("Properties"));
	$tpl->assign("sort2", _("Graphs"));

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
		function insertValueQuery(elem)
		{
		    var myQuery = document.Form.ds_name;
		    if(elem == 1)	{
			var myListBox = document.Form.datasources;
			document.Form.ds_name.value = myListBox.value;
		    }
		}
	</script>
    "
    );

	$valid = false;
	if ($form->validate())	{
		$compoObj = $form->getElement('compo_id');
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
		/*
		 * Apply a template definition
		 */
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("formComponentTemplate.ihtml");
	}
?>
