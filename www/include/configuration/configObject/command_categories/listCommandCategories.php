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
		
	include_once "./include/common/autoNumLimit.php";

	/*
	 * start quickSearch form
	 */
	$advanced_search = 0;
	include_once "./include/common/quickSearch.php";
		
	$SearchTool = NULL;
	if (isset($search) && $search)
		$SearchTool = "WHERE (`category_name` LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%' OR `category_alias` LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%')";
	$DBRESULT = $pearDB->query("SELECT COUNT(*) FROM `command_categories` $SearchTool");

	$tmp = $DBRESULT->fetchRow();
	$DBRESULT->free();
	$rows = $tmp["COUNT(*)"];

	include_once "./include/common/checkPagination.php";
	
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
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_desc", _("Description"));
	$tpl->assign("headerMenu_linked_cmd", _("Number of linked commands"));
	$tpl->assign("headerMenu_options", _("Options"));
	
	/*
	 * Services Categories Lists
	 */ 
	
	$DBRESULT = $pearDB->query("SELECT * FROM `command_categories` $SearchTool ORDER BY `category_order`, `category_name` LIMIT ".$num * $limit.", ".$limit);

	$search = tidySearchKey($search, $advanced_search);

	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
	
	/*
	 * Different style between each lines
	 */
	$style = "one";
	
	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr = array();
	for ($i = 0; $cc = $DBRESULT->fetchRow(); $i++) {
		$moptions = "";
		
		/*
		 * $DBRESULT2 = $pearDB->query("SELECT COUNT(*) FROM `command_categories_relation` WHERE `cmd_category_id` = '".$cc['cmd_category_id']."'");$nb_svc = $DBRESULT2->fetchRow();
		 */
		
		$selectedElements = $form->addElement('checkbox', "select[".$cc['cmd_category_id']."]");
		$moptions .= "&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$cc['cmd_category_id']."]'></input>";
				
		$elemArr[$i] = array(	"MenuClass"=>"list_".$style,
								"RowMenu_select"=>$selectedElements->toHtml(),
								"category_name"=>htmlentities($cc["category_name"]),
								"category_link"=>"?p=".$p."&o=c&cc_id=".$cc['cmd_category_id'],
								"category_alias"=>htmlentities($cc["category_alias"]),
								"RowMenu_options"=>$moptions);
			$style != "two" ? $style = "two" : $style = "one";	
	}
	$DBRESULT->free();

	$tpl->assign("elemArr", $elemArr);
	
	/*
	 * Different messages we put in the template
	 */
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add")));
	
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3 || this.form.elements['o1'].selectedIndex == 4 ||this.form.elements['o1'].selectedIndex == 5){" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "mc"=>_("Massive Change"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs1);
	$form->setDefaults(array('o1' => NULL));
		
	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3 || this.form.elements['o2'].selectedIndex == 4 ||this.form.elements['o2'].selectedIndex == 5){" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "mc"=>_("Massive Change"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs2);
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
	
	$tpl->display("listCommandCategories.ihtml");
?>