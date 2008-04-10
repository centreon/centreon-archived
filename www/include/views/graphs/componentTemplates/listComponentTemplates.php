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
		exit;

	include("./include/common/autoNumLimit.php");
	
	/*
	 * start quickSearch form
	 */
	include_once("./include/common/quickSearch.php");
	
	$SearchTool = NULL;
	if (isset($search) && $search)
		$SearchTool = " WHERE name LIKE '%".$search."%'";
	
	$DBRESULT =& $pearDB->query("SELECT COUNT(*) FROM giv_components_template".$SearchTool);
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo();
	$DBRESULT->fetchInto($tmp);
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
	$tpl->assign("headerMenu_Transp", _("Transparency"));
	$tpl->assign("headerMenu_tickness", _("Thickness"));
	$tpl->assign("headerMenu_options", _("Options"));


	$rq = "SELECT compo_id, name, ds_name, ds_color_line, ds_color_area, default_tpl1, ds_tickness, ds_transparency FROM giv_components_template gc $SearchTool ORDER BY name LIMIT ".$num * $limit.", ".$limit;
	$DBRESULT = & $pearDB->query($rq);
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
		
	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
	
	/*
	 * Different style between each lines
	 */
	$style = "one";
	
	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr = array();
	for ($i = 0; $DBRESULT->fetchInto($compo); $i++) {		
		$selectedElements =& $form->addElement('checkbox', "select[".$compo['compo_id']."]");	
		$moptions = "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$compo['compo_id']."]'></input>";
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$compo["name"],
						"RowMenu_link"=>"?p=".$p."&o=c&compo_id=".$compo['compo_id'],
						"RowMenu_desc"=>$compo["ds_name"],
						"RowMenu_transp"=>$compo["ds_transparency"],
						"RowMenu_clrLine"=>$compo["ds_color_line"],
						"RowMenu_clrArea"=>$compo["ds_color_area"],
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

	/*
	 * Apply a template definition
	 */	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listComponentTemplates.ihtml");

	$tpl = new Smarty();
	$tpl = initSmartyTpl("./", $tpl);
	
	$tpl->assign('enable', _("Enable"));
	$tpl->assign('disable', _("Disable"));
	$tpl->assign('duplicate', _("Duplicate"));
	$tpl->assign('sign', _("Sign"));
	$tpl->assign('delete', _("Delete"));
	$tpl->assign('deleteall', _("Delete all"));
	$tpl->assign('view', _("View"));
	$tpl->assign('edit', _("Edit"));
	$tpl->assign('previous', _("Previous"));
	$tpl->assign('next', _("Next"));
	$tpl->assign('force', _("Force"));
	$tpl->assign('graph', _("Graph"));
	$tpl->assign('notification', _("Notification"));
	$tpl->assign('passive', _("Passive"));
	$tpl->assign('work', _("Work"));
	$tpl->assign('refresh', _("Refresh"));
	$tpl->assign('play', _("Play"));
	$tpl->assign('pause', _("Pause"));
	$tpl->assign('inactive', _("Inactive"));
	
	$tpl->display("include/common/legend.ihtml");	
?>