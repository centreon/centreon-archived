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

	if (!isset($centreon)) {
		exit();
	}

	include("./include/common/autoNumLimit.php");

	/*
	 * start quickSearch form
	 */
	include_once("./include/common/quickSearch.php");

	/* Search clause */
	if (isset($search) && $search) {
		$downtime->setSearch($search);
	}

	/*
	 *  Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/* Access level */
	($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';
	$tpl->assign('mode_access', $lvl_access);

	/*
	 * start header menu
	 */
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_desc", _("Description"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_options", _("Options"));

	/*
	 * Nagios list
	 */
	$rows = $downtime->getNbRows();

	include("./include/common/checkPagination.php");

	$listDowntime = $downtime->getList($num, $limit, $type);

	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p."&type=$type");

	/*
	 * Different style between each lines
	 */
	$style = "one";

	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr = array();
	foreach ($listDowntime as $dt) {
		$moptions = "";
		$selectedElements = $form->addElement('checkbox', "select[".$dt['dt_id']."]");
		if ($dt["dt_activate"]) {
			$moptions .= "<a href='main.php?p=".$p."&dt_id=".$dt['dt_id']."&o=u&type=$type&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		} else {
			$moptions .= "<a href='main.php?p=".$p."&dt_id=".$dt['dt_id']."&o=e&type=$type&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
		}
		$moptions .= "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$dt['dt_id']."]'></input>";
		$elemArr[] = array(
						"MenuClass" 		=> "list_".$style,
						"RowMenu_select" 	=> $selectedElements->toHtml(),
						"RowMenu_name" 		=> $dt["dt_name"],
						"RowMenu_link" 		=> "?p=".$p."&o=c&dt_id=".$dt['dt_id']."&type=$type",
						"RowMenu_desc" 		=> $dt["dt_description"],
						"RowMenu_status" 	=> $dt["dt_activate"] ? _("Enabled") : _("Disabled"),
						"RowMenu_options" 	=> $moptions);
		$style != "two" ? $style = "two" : $style = "one";
	}
	$tpl->assign("elemArr", $elemArr);

	/*
	 * Different messages we put in the template
	 */
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a&type=$type", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));

	/*
	 * Toolbar select
	 */

	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?php
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._('Do you confirm the duplication ?')."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._('Do you confirm the deletion ?')."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3 || this.form.elements['o1'].selectedIndex == 4) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"");
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs1);
	$form->setDefaults(array('o1' => NULL));

	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._('Do you confirm the duplication ?')."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._('Do you confirm the deletion ?')."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3 || this.form.elements['o2'].selectedIndex == 4) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"");
	$form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs2);
	$form->setDefaults(array('o2' => NULL));

	$o1 = $form->getElement('o1');
	$o1->setValue(NULL);
	$o1->setSelected(NULL);

	$o2 = $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);

	$tpl->assign('limit', $limit);

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listDowntime.ihtml");
?>