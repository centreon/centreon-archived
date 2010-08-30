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
		exit();
		
	include("./include/common/autoNumLimit.php");
	
	# start quickSearch form
	$advanced_search = 0;
	include_once("./include/common/quickSearch.php");
	# end quickSearch form
		
	if (isset($search))
		$DBRESULT =& $pearDB->query("SELECT COUNT(*) FROM purge_policy WHERE purge_policy_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%'");
	else
		$DBRESULT =& $pearDB->query("SELECT COUNT(*) FROM purge_policy");
		
	$tmp = & $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];

	include("./include/common/checkPagination.php");

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_desc", _("Description"));
	$tpl->assign("headerMenu_raw", _("Raw"));
	$tpl->assign("headerMenu_bin", _("Bin"));
	$tpl->assign("headerMenu_metric", _("Metric"));
	$tpl->assign("headerMenu_service", _("Service"));
	$tpl->assign("headerMenu_host", _("Host"));
	$tpl->assign("headerMenu_options", _("Options"));
	# end header menu
	#Contact list
	if ($search)
		$rq = "SELECT *  FROM purge_policy WHERE purge_policy_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%' ORDER BY purge_policy_name LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT * FROM purge_policy ORDER BY purge_policy_name LIMIT ".$num * $limit.", ".$limit;
	$DBRESULT =& $pearDB->query($rq);
	
	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();
	for ($i = 0; $purgePolicy =& $DBRESULT->fetchRow(); $i++) {		
		$selectedElements =& $form->addElement('checkbox', "select[".$purgePolicy['purge_policy_id']."]");	
		$moptions = "<a href='main.php?p=".$p."&purge_policy_id=".$purgePolicy['purge_policy_id']."&o=w&search=".$search."'><img src='img/icones/16x16/view.gif' border='0' alt='"._("View")."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='main.php?p=".$p."&purge_policy_id=".$purgePolicy['purge_policy_id']."&o=c&search=".$search."'><img src='img/icones/16x16/document_edit.gif' border='0' alt='"._("Modify")."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='main.php?p=".$p."&purge_policy_id=".$purgePolicy['purge_policy_id']."&o=d&select[".$purgePolicy['purge_policy_id']."]=1&num=".$num."&limit=".$limit."&search=".$search."' onclick=\"return confirm('"._("Do you confirm the deletion ?")."')\"><img src='img/icones/16x16/delete.gif' border='0' alt='"._("Delete")."'></a>&nbsp;&nbsp;";
		$moptions .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$purgePolicy['purge_policy_id']."]'></input>";
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$purgePolicy["purge_policy_name"],
						"RowMenu_link"=>"?p=".$p."&o=c&purge_policy_id=".$purgePolicy['purge_policy_id'],
						"RowMenu_desc"=>$purgePolicy["purge_policy_alias"],
						"RowMenu_host"=>$purgePolicy["purge_policy_host"] ? "x" : NULL,
						"RowMenu_service"=>$purgePolicy["purge_policy_service"] ? "x" : NULL,
						"RowMenu_metric"=>$purgePolicy["purge_policy_metric"] ? "x" : NULL,
						"RowMenu_raw"=>$purgePolicy["purge_policy_raw"] ? "x" : NULL,
						"RowMenu_bin"=>$purgePolicy["purge_policy_bin"] ? "x" : NULL,
						"RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";	}
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

	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);
	$o1->setSelected(NULL);

	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);
	
	$tpl->assign('limit', $limit);
	
	#
	##Apply a template definition
	#
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listPurgePolicy.ihtml");
?>