<?php
/*
 * Copyright 2005-2009 MERETHIS
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
		exit();
	
	include_once "./include/common/autoNumLimit.php";

	/*
	 * start quickSearch form
	 */
	$advanced_search = 0;
	include_once "./include/common/quickSearch.php";
	
	if ($type)
		$type_str = " `command_type` = '".$type."'";
	else
		$type_str = "";

	if (isset($search) && $search){
		$search = str_replace('#S#', "/", $search);
		$search = str_replace('#BS#', "\\", $search);		

		if ($type_str)
			$type_str = " AND " . $type_str;
		$req = "SELECT COUNT(*) FROM `command` WHERE `command_name` LIKE '%".htmlentities($search, ENT_QUOTES)."%' $type_str";
	} else if ($type) {
		$req = "SELECT COUNT(*) FROM `command` WHERE $type_str";
	} else {
		$req ="SELECT COUNT(*) FROM `command`";
	}
	
	$DBRESULT =& $pearDB->query($req);

	$tmp = & $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];

	include_once "./include/common/checkPagination.php";

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
	$tpl->assign("headerMenu_desc", _("Command Line"));
	$tpl->assign("headerMenu_type", _("Type"));
	$tpl->assign("headerMenu_options", _("Options"));

	/*
	 * List of elements - Depends on different criteria
	 */
	if (isset($search) && $search)
		$rq = "SELECT `command_id`, `command_name`, `command_line`, `command_type` FROM `command` WHERE `command_name` LIKE '%".htmlentities($search, ENT_QUOTES)."%' $type_str ORDER BY `command_name` LIMIT ".$num * $limit.", ".$limit;
	else if ($type)
		$rq = "SELECT `command_id`, `command_name`, `command_line`, `command_type` FROM `command` WHERE `command_type` = '".$type."' ORDER BY command_name LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT `command_id`, `command_name`, `command_line`, `command_type` FROM `command` ORDER BY `command_name` LIMIT ".$num * $limit.", ".$limit;

	$search = tidySearchKey($search, $advanced_search);

	$DBRESULT =& $pearDB->query($rq);
	
	$form = new HTML_QuickForm('form', 'POST', "?p=".$p);
	
	/*
	 * Different style between each lines
	 */
	$style = "one";
	
	/*
	 * Define command Type table
	 */
	
	$commandType = array("1" => _("Notification"), "2" => _("Check"), "3" => _("Miscellaneous"));
	
	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr = array();
	for ($i = 0; $cmd =& $DBRESULT->fetchRow(); $i++) {

		$selectedElements =& $form->addElement('checkbox', "select[".$cmd['command_id']."]");	
		$moptions = "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$cmd['command_id']."]'></input>";
		
		$elemArr[$i] = array("MenuClass" => "list_".$style, 
							"RowMenu_select" => $selectedElements->toHtml(),
							"RowMenu_name" => $cmd["command_name"],
							"RowMenu_link" => "?p=".$p."&o=c&command_id=".$cmd['command_id']."&type=".$cmd['command_type'],
							"RowMenu_desc" => substr(myDecodeCommand($cmd["command_line"]), 0, 50)."...",
							"RowMenu_type" => $commandType[$cmd["command_type"]],
							"RowMenu_options" => $moptions);
		$style != "two" ? $style = "two" : $style = "one";
	}
	
	/*
	 * Header title for same name - Ajust pattern lenght with (0, 6) param
	 */
	$pattern = NULL;
	$limitMatch = 20;
	for ($i = 0; $i < count($elemArr); $i++){
		
		/*
		 * Searching for a pattern wich n+1 elem
		 */
		
		if (isset($elemArr[$i+1]["RowMenu_name"]) && strstr($elemArr[$i+1]["RowMenu_name"], substr($elemArr[$i]["RowMenu_name"], 0, $limitMatch)) && !$pattern)	{
			for ($j = 0; isset($elemArr[$i]["RowMenu_name"][$j]); $j++)	{
				if (isset($elemArr[$i+1]["RowMenu_name"][$j]) && $elemArr[$i+1]["RowMenu_name"][$j] == $elemArr[$i]["RowMenu_name"][$j])
					;
				else
					break;
			}
			$pattern = substr($elemArr[$i]["RowMenu_name"], 0, $j);
		}
		
		if (strstr($elemArr[$i]["RowMenu_name"], $pattern))
			$elemArr[$i]["pattern"] = $pattern;
		else	{
			$elemArr[$i]["pattern"] = NULL;
			$pattern = NULL;
			if (isset($elemArr[$i+1]["RowMenu_name"]) && strstr($elemArr[$i+1]["RowMenu_name"], substr($elemArr[$i]["RowMenu_name"], 0, $limitMatch)) && !$pattern)	{
				for ($j = 0; isset($elemArr[$i]["RowMenu_name"][$j]); $j++)	{
					if (isset($elemArr[$i+1]["RowMenu_name"][$j]) && $elemArr[$i+1]["RowMenu_name"][$j] == $elemArr[$i]["RowMenu_name"][$j])
						;
					else
						break;
				}
				$pattern = substr($elemArr[$i]["RowMenu_name"], 0, $j);
				$elemArr[$i]["pattern"] = $pattern;
			}
		}
	}
	$tpl->assign("elemArr", $elemArr);
	
	/*
	 * Different messages we put in the template
	 */
	if (isset($_GET['type']) && $_GET['type'] != "")
		$type = htmlentities($_GET['type'], ENT_QUOTES);
	else if (!isset($_GET['type']))
		$type = 2;
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a&type=".$type, "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));

	$redirectType = $form->addElement('hidden', 'type');
	$redirectType->setValue($type);

	/*
	 * Toolbar select 
	 */
	
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");

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
				"this.form.elements['o2'].selectedIndex = 0");

    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs2);
	$form->setDefaults(array('o2' => NULL));

	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);
	$o1->setSelected(NULL);

	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);
	
	?><script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</script><?php

	/*
	 * Apply a template definition
	 */
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('limit', $limit);

	$tpl->display("listCommand.ihtml");
?>