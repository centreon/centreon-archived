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
		exit();
		
	include("./include/common/autoNumLimit.php");

	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	# end quickSearch form
	
	if ($search)
		$DBRESULT = & $pearDB->query("SELECT COUNT(*) FROM cfg_perfparse WHERE perfparse_name LIKE '%".htmlentities($search, ENT_QUOTES)."%'");
	else
		$DBRESULT = & $pearDB->query("SELECT COUNT(*) FROM cfg_perfparse");

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
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_options", _("Options"));
	# end header menu
	#Nagios list
	if ($search)
		$rq = "SELECT perfparse_id, perfparse_name, perfparse_comment, perfparse_activate FROM cfg_perfparse WHERE perfparse_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' ORDER BY perfparse_name LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT perfparse_id, perfparse_name, perfparse_comment, perfparse_activate FROM cfg_perfparse ORDER BY perfparse_name LIMIT ".$num * $limit.", ".$limit;
	$DBRESULT =& $pearDB->query($rq);
	
	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();
	for ($i = 0; $perfparse =& $DBRESULT->fetchRow(); $i++) {		
		$selectedElements =& $form->addElement('checkbox', "select[".$perfparse['perfparse_id']."]");	
		$moptions = "<a href='main.php?p=".$p."&perfparse_id=".$perfparse['perfparse_id']."&o=w&search=".$search."'><img src='img/icones/16x16/view.gif' border='0' alt='"._("View")."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='main.php?p=".$p."&perfparse_id=".$perfparse['perfparse_id']."&o=c&search=".$search."'><img src='img/icones/16x16/document_edit.gif' border='0' alt='"._("Modify")."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='main.php?p=".$p."&perfparse_id=".$perfparse['perfparse_id']."&o=d&select[".$perfparse['perfparse_id']."]=1&num=".$num."&limit=".$limit."&search=".$search."' onclick=\"return confirm('"._("Do you confirm the deletion ?")."')\"><img src='img/icones/16x16/delete.gif' border='0' alt='"._("Delete")."'></a>&nbsp;&nbsp;";
		if ($perfparse["perfparse_activate"])
			$moptions .= "<a href='main.php?p=".$p."&perfparse_id=".$perfparse['perfparse_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='main.php?p=".$p."&perfparse_id=".$perfparse['perfparse_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
		$moptions .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$perfparse['perfparse_id']."]'></input>";
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$perfparse["perfparse_name"],
						"RowMenu_link"=>"?p=".$p."&o=c&perfparse_id=".$perfparse['perfparse_id'],
						"RowMenu_desc"=>substr($perfparse["perfparse_comment"], 0, 40),
						"RowMenu_status"=>$perfparse["perfparse_activate"] ? _("Enabled") : _("Disabled"),
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
	$tpl->display("listPerfparse.ihtml");
?>