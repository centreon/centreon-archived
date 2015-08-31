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
		exit;

	include("./include/common/autoNumLimit.php");

	/*
	 * start quickSearch form
	 */
	include_once("./include/common/quickSearch.php");

	$SearchTool = NULL;
	if (isset($search) && $search) {
		$SearchTool = " WHERE name LIKE '%".$search."%'";
	}

	$DBRESULT = $pearDB->query("SELECT COUNT(*) FROM giv_components_template".$SearchTool);

	$tmp = $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];

	include("./include/common/checkPagination.php");

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/*
	 * start header menu
	 */
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_desc", _("Data Source Name"));
	$tpl->assign("headerMenu_legend", _("Legend"));
	$tpl->assign("headerMenu_stack", _("Stacked"));
	$tpl->assign("headerMenu_order", _("Order"));
	$tpl->assign("headerMenu_Transp", _("Transparency"));
	$tpl->assign("headerMenu_tickness", _("Thickness"));
	$tpl->assign("headerMenu_fill", _("Filling"));
	$tpl->assign("headerMenu_options", _("Options"));

	if ( $SearchTool != NULL ) {
		$ClWh1 = "AND host_id IS NULL";
		$ClWh2 = "AND gct.host_id = h.host_id";
	} else {
		$ClWh1 = "WHERE host_id IS NULL";
		$ClWh2 = "WHERE gct.host_id = h.host_id";
	}
	$rq = "( SELECT compo_id, NULL as host_name, host_id, service_id, name, ds_stack, ds_order, ds_name, ds_color_line, ds_color_area, ds_filled, ds_legend, default_tpl1, ds_tickness, ds_transparency FROM giv_components_template $SearchTool $ClWh1 ) UNION ( SELECT compo_id, host_name, gct.host_id, gct.service_id, name, ds_stack, ds_order, ds_name, ds_color_line, ds_color_area, ds_filled, ds_legend, default_tpl1, ds_tickness, ds_transparency FROM giv_components_template AS gct, host AS h $SearchTool $ClWh2 ) ORDER BY host_name, name LIMIT ".$num * $limit.", ".$limit;
	$DBRESULT = $pearDB->query($rq);

	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);

	/*
	 * Different style between each lines
	 */
	$style = "one";

	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$yesOrNo = array(NULL => _("No"), 0 => _("No"), 1 => _("Yes"));
	$elemArr = array();
	for ($i = 0; $compo = $DBRESULT->fetchRow(); $i++) {
		$selectedElements = $form->addElement('checkbox', "select[".$compo['compo_id']."]");
		$moptions = "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$compo['compo_id']."]'></input>";
		$titles = $pearDB->query("SELECT h.host_name FROM giv_components_template AS gct, host AS h WHERE gct.host_id = '".$compo["host_id"]."' AND gct.host_id = h.host_id");
		if ($titles->numRows()) {
			$title = $titles->fetchRow();
		} else {
			$title = array("host_name"=>"Global");
		}
		$titles->free();
		$elemArr[$i] = array("MenuClass"=>"list_".$style,
                            "title"=>$title["host_name"],
                            "RowMenu_select"=>$selectedElements->toHtml(),
                            "RowMenu_name"=>$compo["name"],
                            "RowMenu_link"=>"?p=".$p."&o=c&compo_id=".$compo['compo_id'],
                            "RowMenu_desc"=>$compo["ds_name"],
                            "RowMenu_legend"=>$compo["ds_legend"],
                            "RowMenu_stack"=>$yesOrNo[$compo["ds_stack"]],
                            "RowMenu_order"=>$compo["ds_order"],
                            "RowMenu_transp"=>$compo["ds_transparency"],
                            "RowMenu_clrLine"=>$compo["ds_color_line"],
                            "RowMenu_clrArea"=>$compo["ds_color_area"],
                            "RowMenu_fill"=>$yesOrNo[$compo["ds_filled"]],
                            "RowMenu_tickness"=>$compo["ds_tickness"],
                            "RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";
	}
	$tpl->assign("elemArr", $elemArr);

	/*
	 * Different messages we put in the template
	 */
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));

	/*
	 * Toolbar select
	 */
	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</script>
	<?php
	$attrs1 = array(
		'onchange'=>"javascript: " .
                    "if (this.form.elements['o1'].selectedIndex === 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
                    " 	setO(this.form.elements['o1'].value); submit();} " .
                    "else if (this.form.elements['o1'].selectedIndex === 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
                    " 	setO(this.form.elements['o1'].value); submit();} ".
                    "this.form.elements['o1'].selectedIndex = 0;".
                    "");
    $form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs1);
	$o1 = $form->getElement('o1');
	$o1->setValue(NULL);

	$attrs = array(
		'onchange'=>"javascript: " .
                    "if (this.form.elements['o2'].selectedIndex === 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
                    " 	setO(this.form.elements['o2'].value); submit();} " .
                    "else if (this.form.elements['o2'].selectedIndex === 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
                    " 	setO(this.form.elements['o2'].value); submit();} " .
                    "this.form.elements['o2'].selectedIndex = 0;".
                    "");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs);
	$o2 = $form->getElement('o2');
	$o2->setValue(NULL);
    
	$tpl->assign('limit', $limit);

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listComponentTemplates.ihtml");
?>
