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
		
	include("./include/common/autoNumLimit.php");
	$mnftr_id = NULL;
	
	$tabStatus = array(0 => _("OK"), 1 => _("Warning"), 2 => _("Critical"), 3 => _("Unknown"), 4 => _("Pending"));
	
	/*
	 * start quickSearch form
	 */
	include_once("./include/common/quickSearch.php");
	
	$SearchTool = NULL;
	if (isset($search) && $search)
		$SearchTool = "WHERE traps_oid LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%' OR traps_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%' OR manufacturer_id IN (SELECT id FROM traps_vendor WHERE alias LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%')";
	
	$DBRESULT = $pearDB->query("SELECT COUNT(*) FROM traps $SearchTool");
	$tmp = $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];

	include("./include/common/checkPagination.php");

	/*
	 * Smarty template Init
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
	$tpl->assign("headerMenu_desc", _("OID"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_manufacturer", _("Vendor Name"));
	$tpl->assign("headerMenu_args", _("Output Message"));
	$tpl->assign("headerMenu_options", _("Options"));
	
	/*
	 * List of elements - Depends on different criteria
	 */
	$rq = "SELECT * FROM traps $SearchTool ORDER BY manufacturer_id, traps_name LIMIT ".$num * $limit.", ".$limit;
	$DBRESULT = $pearDB->query($rq);
	$form = new HTML_QuickForm('form', 'POST', "?p=".$p);

	/*
	 * Different style between each lines
	 */
	$style = "one";
	
	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr = array();
	for ($i = 0; $trap = $DBRESULT->fetchRow(); $i++) {
		$moptions = "";
		$selectedElements = $form->addElement('checkbox', "select[".$trap['traps_id']."]");
		$moptions .= "&nbsp;&nbsp;&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$trap['traps_id']."]'></input>";
		$DBRESULT2 = $pearDB->query("select alias from traps_vendor where id='".$trap['manufacturer_id']."' LIMIT 1");
		$mnftr = $DBRESULT2->fetchRow();
		$DBRESULT2->free();
		$elemArr[$i] = array("MenuClass" => "list_".$style,
						"RowMenu_select" => $selectedElements->toHtml(),
						"RowMenu_name" => myDecode($trap["traps_name"]),
						"RowMenu_link" => "?p=".$p."&o=c&traps_id=".$trap['traps_id'],
						"RowMenu_desc" => myDecode(substr($trap["traps_oid"], 0, 40)),
						"RowMenu_status" => $tabStatus[$trap["traps_status"]],
						"RowMenu_args" => myDecode($trap["traps_args"]),
						"RowMenu_manufacturer" => myDecode($mnftr["alias"]),
						"RowMenu_options" => $moptions);
		$style != "two" ? $style = "two" : $style = "one";
	}
	$tpl->assign("elemArr", $elemArr);
	#Different messages we put in the template
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));

	#
	##Toolbar select 
	#
	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?php
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"");
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs1);
	$form->setDefaults(array('o1' => NULL));
		
	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs2);
	$form->setDefaults(array('o2' => NULL));

	$o1 = $form->getElement('o1');
	$o1->setValue(NULL);
	$o1->setSelected(NULL);

	$o2 = $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);
	
	$tpl->assign('limit', $limit);
	
	#
	##Apply a template definition
	#
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listTraps.ihtml");
?>
