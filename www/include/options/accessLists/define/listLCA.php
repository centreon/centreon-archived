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
 * For information : contact@oreon-project.org
 */

	if (!isset($oreon))
		exit();
		
	include("./include/common/autoNumLimit.php");
	
	# start quickSearch form
	$advanced_search = 1;
	include_once("./include/common/quickSearch.php");
	# end quickSearch form
	
	$lca_reg = NULL;
	# Not list the LCA the user is registered by || is admin
	if (!$oreon->user->get_admin())	{
		$DBRESULT =& $pearDB->query("SELECT contactgroup_cg_id FROM contactgroup_contact_relation WHERE contact_contact_id = '".$oreon->user->get_id()."'");
		while($DBRESULT->fetchInto($contactGroup))	{
		 	$DBRESULT2 =& $pearDB->query("SELECT lca.lca_id FROM lca_define_contactgroup_relation ldcgr, lca_define lca WHERE ldcgr.contactgroup_cg_id = '".$contactGroup["contactgroup_cg_id"]."' AND ldcgr.lca_define_lca_id = lca.lca_id");	
			while ($DBRESULT2->fetchInto($lca))
				$lca_reg ? $lca_reg .= ", ".$lca["lca_id"] : $lca_reg = $lca["lca_id"];
		}
	}
	$lca_reg ? $lca_reg = $lca_reg : $lca_reg =  '\'\'';
	if (isset($search))
		$DBRESULT =& $pearDB->query("SELECT COUNT(*) FROM lca_define WHERE lca_id NOT IN (".$lca_reg.") AND (lca_name LIKE '".$search."' OR lca_alias LIKE '".$search."')");
	else
		$DBRESULT =& $pearDB->query("SELECT COUNT(*) FROM lca_define WHERE lca_id NOT IN (".$lca_reg.")");
	$tmp =& $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_alias", _("Alias"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_options", _("Options"));
	# end header menu

	#List
	if ($search)
		$rq = "SELECT lca_id, lca_name, lca_alias, lca_activate  FROM lca_define WHERE (lca_name LIKE '".$search."' OR lca_alias LIKE '".$search."') AND lca_id NOT IN (".$lca_reg.") ORDER BY lca_name LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT lca_id, lca_name, lca_alias, lca_activate FROM lca_define WHERE lca_id NOT IN (".$lca_reg.") ORDER BY lca_name LIMIT ".$num * $limit.", ".$limit;
	$DBRESULT =& $pearDB->query($rq);

	$search = tidySearchKey($search, $advanced_search);
		
	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();
	for ($i = 0; $DBRESULT->fetchInto($lca); $i++) {		
		$moptions = "";
		$selectedElements =& $form->addElement('checkbox', "select[".$lca['lca_id']."]");	
		if ($lca["lca_activate"])
			$moptions .= "<a href='main.php?p=".$p."&lca_id=".$lca['lca_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='main.php?p=".$p."&lca_id=".$lca['lca_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
		$moptions .= "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$lca['lca_id']."]'></input>";

		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$lca["lca_name"],
						"RowMenu_link"=>"?p=".$p."&o=c&lca_id=".$lca['lca_id'],
						"RowMenu_alias"=>$lca["lca_alias"],
						"RowMenu_status"=>$lca["lca_activate"] ? _("Enabled") : _("Disabled"),
						"RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";
	}
	$tpl->assign("elemArr", $elemArr);
	#Different messages we put in the template
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));

	#
	##Toolbar select lgd_more_actions
	#
	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?php
	$attrs = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm  the duplication ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"");
        $form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs);
		$form->setDefaults(array('o1' => NULL));
			$o1 =& $form->getElement('o1');
			$o1->setValue(NULL);

	$attrs = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs);
	$form->setDefaults(array('o2' => NULL));

	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	
	$tpl->assign('limit', $limit);

	#
	##Apply a template definition
	#
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listLCA.ihtml");	
?>
