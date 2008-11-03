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
	
	/*
	 * start quickSearch form
	 */
	$advanced_search = 0;
	include_once("./include/common/quickSearch.php");
	/*
	 * end quickSearch form
	 */	

	$SearchTool = NULL;
	if (isset($search) && $search)	
		$SearchTool = " WHERE (hg_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' OR hg_alias LIKE '%".htmlentities($search, ENT_QUOTES)."%')";
	
	$LcaTool = "";
	if (!isUserAdmin(session_id())){
		$SearchTool != "" ? $LcaTool .= " AND hg_id IN (".$lcaHostGroupstr.")" : $LcaTool .= " WHERE hg_id IN (".$lcaHostGroupstr.")";
	}
	
	$request = "SELECT COUNT(*) FROM hostgroup $SearchTool $LcaTool";
	
	$DBRESULT =& $pearDB->query($request);
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";	
	$tmp = & $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];

	include("./include/common/checkPagination.php");

	/*
	 *  Smarty template Init
	 */
	 
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	/*
	 * start header menu
	 */
	 
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_desc", _("Description"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_hostAct", _("Enabled Hosts"));
	$tpl->assign("headerMenu_hostDeact", _("Disabled Hosts"));
	$tpl->assign("headerMenu_options", _("Options"));
	
	/*
	 * Hostgroup list
	 */
	 
	 
	$rq = "SELECT hg_id, hg_name, hg_alias, hg_activate FROM hostgroup $SearchTool $LcaTool ORDER BY hg_name LIMIT ".$num * $limit .", $limit"; 
	$DBRESULT =& $pearDB->query($rq);
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";	
	
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
	for ($i = 0; $hg =& $DBRESULT->fetchRow(); $i++) {
		$selectedElements =& $form->addElement('checkbox', "select[".$hg['hg_id']."]");	
		$moptions = "";
		if ($hg["hg_activate"])
			$moptions .= "<a href='main.php?p=".$p."&hg_id=".$hg['hg_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='main.php?p=".$p."&hg_id=".$hg['hg_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
		$moptions .= "&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$hg['hg_id']."]'></input>";
		/* Nbr Host */
		$nbrhostAct = array();
		$nbrhostDeact = array();
		$rq = "SELECT COUNT(*) as nbr FROM hostgroup_relation hgr, host WHERE hostgroup_hg_id = '".$hg['hg_id']."' AND host.host_id = hgr.host_host_id AND host.host_register = '1' AND host.host_activate = '1'";
		$DBRESULT2 =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT2))
			print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";	
		$nbrhostAct = $DBRESULT2->fetchRow();
		$rq = "SELECT COUNT(*) as nbr FROM hostgroup_relation hgr, host WHERE hostgroup_hg_id = '".$hg['hg_id']."' AND host.host_id = hgr.host_host_id AND host.host_register = '1' AND host.host_activate = '0'";
		$DBRESULT2 =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT2))
			print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";	
		$nbrhostDeact = $DBRESULT2->fetchRow();
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$hg["hg_name"],
						"RowMenu_link"=>"?p=".$p."&o=c&hg_id=".$hg['hg_id'],
						"RowMenu_desc"=>$hg["hg_alias"],
						"RowMenu_status"=>$hg["hg_activate"] ? _("Enabled") : _("Disabled"),
						"RowMenu_hostAct"=>$nbrhostAct["nbr"],
						"RowMenu_hostDeact"=>$nbrhostDeact["nbr"],
						"RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";	
	}
	$tpl->assign("elemArr", $elemArr);
	/*
	 * Different messages we put in the template
	 */
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
	
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
				"this.form.elements['o1'].selectedIndex = 0");
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs1);
	$form->setDefaults(array('o1' => NULL));
		
	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
    $form->addElement('select', 'o2', NULL, array(NULL => _("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs2);
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
	$tpl->display("listHostGroup.ihtml");
?>