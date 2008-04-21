<?php
/** 
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	if (!isset($oreon))
		exit();
		
	include("./include/common/autoNumLimit.php");
	
	/*
	 * start quickSearch form
	 */
	include_once("./include/common/quickSearch.php");
	
	if (isset($search))
		$DBRESULT = & $pearDB->query("SELECT COUNT(*) FROM meta_service WHERE meta_name LIKE '%".htmlentities($search, ENT_QUOTES)."%'");
	else
		$DBRESULT = & $pearDB->query("SELECT COUNT(*) FROM meta_service");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$tmp = & $DBRESULT->fetchRow();
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
	$tpl->assign("headerMenu_name", _("Meta Service Name"));
	$tpl->assign("headerMenu_type", _("Calculation Type"));
	$tpl->assign("headerMenu_levelw", _("Warning Level"));
	$tpl->assign("headerMenu_levelc", _("Critical Level"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_options", _("Options"));
	
	$calcType = array("AVE"=>_("Average"), "SOM"=>_("Sum"), "MIN"=>_("Min"), "MAX"=>_("Max"));
	
	/*
	 * Meta Service list
	 */
	if ($search)
		$rq = "SELECT *  FROM meta_service WHERE meta_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' ORDER BY meta_name LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT * FROM meta_service ORDER BY meta_name LIMIT ".$num * $limit.", ".$limit;
	$DBRESULT = & $pearDB->query($rq);
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	
	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
	
	/*
	 * Different style between each lines
	 */
	$style = "one";

	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr = array();
	for ($i = 0; $DBRESULT->fetchInto($ms); $i++) {
		$moptions = "";
		$selectedElements =& $form->addElement('checkbox', "select[".$ms['meta_id']."]");	
		if ($ms["meta_select_mode"] == 1)
			$moptions = "<a href='main.php?p=".$p."&meta_id=".$ms['meta_id']."&o=ci&search=".$search."'><img src='img/icones/16x16/signpost.gif' border='0' alt='"._("View")."'></a>&nbsp;&nbsp;";
		else
			$moptions = "";
		
		if ($ms["meta_activate"])
			$moptions .= "<a href='main.php?p=".$p."&meta_id=".$ms['meta_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='main.php?p=".$p."&meta_id=".$ms['meta_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
		$moptions .= "&nbsp;";
		
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$ms['meta_id']."]'></input>";

		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$ms["meta_name"],
						"RowMenu_link"=>"?p=".$p."&o=c&meta_id=".$ms['meta_id'],
						"RowMenu_type"=>$calcType[$ms["calcul_type"]],
						"RowMenu_levelw"=>$ms["warning"],
						"RowMenu_levelc"=>$ms["critical"],
						"RowMenu_status"=>$ms["meta_activate"] ? _("Enabled") : _("Disabled"),
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
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions"), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs1);
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

	/*
	 * Apply a template definition
	 */
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listMetaService.ihtml");
?>