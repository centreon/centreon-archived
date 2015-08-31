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
	
	isset($_GET["list"]) ? $list = $_GET["list"] : $list = NULL;

	/*
	 * start quickSearch form
	 */
	$advanced_search = 0;
	include_once("./include/common/quickSearch.php");
	
        $aclFrom = "";
        $aclCond = array('h'  => '',
                         'sv' => '',
                         'hg' => '',
                         'sg' => '',
                         'ms' => '');
        if (!$oreon->user->admin) {
            $aclFrom = ", $dbmon.centreon_acl acl ";
            $aclCond['h'] = " AND ehr.host_host_id = acl.host_id 
                              AND acl.group_id IN (".$acl->getAccessGroupsString().") ";
            $aclCond['sv'] = " AND esr.host_host_id = acl.host_id 
                               AND esr.service_service_id = acl.service_id
                               AND acl.group_id IN (".$acl->getAccessGroupsString().") ";
            $aclCond['hg'] = $acl->queryBuilder('AND', 'hostgroup_hg_id', $hgString);
            $aclCond['sg'] = $acl->queryBuilder('AND', 'servicegroup_sg_id', $sgString);
            $aclCond['ms'] = $acl->queryBuilder('AND', 'meta_service_meta_id', $acl->getMetaServiceString());
        }
	$rq = "SELECT COUNT(*) FROM escalation esc";	
	if ($list && $list == "h"){
		$rq .= " WHERE (SELECT COUNT(DISTINCT host_host_id) 
                                FROM escalation_host_relation ehr $aclFrom
                                WHERE ehr.escalation_esc_id = esc.esc_id ".$aclCond['h'].") > 0 ";
	} else if ($list && $list == "sv") {
		$rq .= " WHERE (SELECT DISTINCT COUNT(*) 
                                FROM escalation_service_relation esr $aclFrom
                                WHERE esr.escalation_esc_id = esc.esc_id ".$aclCond['sv'].") > 0 ";
	} else if ($list && $list == "hg") {
		$rq .= " WHERE (SELECT DISTINCT COUNT(*) 
                                FROM escalation_hostgroup_relation ehgr 
                                WHERE ehgr.escalation_esc_id = esc.esc_id ".$aclCond['hg'].") > 0 ";
	} else if ($list && $list == "sg") {
		$rq .= " WHERE (SELECT DISTINCT COUNT(*) 
                                FROM escalation_servicegroup_relation esgr 
                                WHERE esgr.escalation_esc_id = esc.esc_id ".$aclCond['sg'].") > 0 ";
	} else if ($list && $list == "ms"){
		$rq .= " WHERE (SELECT DISTINCT COUNT(*) 
                                FROM escalation_meta_service_relation emsr 
                                WHERE emsr.escalation_esc_id = esc.esc_id ".$aclCond['ms'].") > 0 ";
	}
	
	if (isset($search) && $list)
		$rq .= " AND (esc.esc_name LIKE '".$search."' OR esc.esc_alias LIKE '%".$search."%')";
	else if (isset($search))
		$rq .= " WHERE (esc.esc_name LIKE '".$search."' OR esc.esc_alias LIKE '%".$search."%')";
	$DBRESULT = $pearDB->query($rq);
	$tmp = $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];

	include("./include/common/checkPagination.php");

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
	$tpl->assign("headerMenu_alias", _("Alias"));
	$tpl->assign("headerMenu_options", _("Options"));
	
	/*
	 * Escalation list
	 */
	$rq = "SELECT esc_id, esc_name, esc_alias FROM escalation esc";
	if ($list && $list == "h")
		$rq .= " WHERE (SELECT DISTINCT COUNT(host_host_id) 
                                FROM escalation_host_relation ehr $aclFrom
                                WHERE ehr.escalation_esc_id = esc.esc_id ".$aclCond['h'].") > 0 ";
        else if ($list && $list == "sv")
		$rq .= " WHERE (SELECT DISTINCT COUNT(*) 
                                FROM escalation_service_relation esr $aclFrom
                                WHERE esr.escalation_esc_id = esc.esc_id ".$aclCond['sv'].") > 0 ";
	else if ($list && $list == "hg")
		$rq .= " WHERE (SELECT DISTINCT COUNT(*) 
                                FROM escalation_hostgroup_relation ehgr 
                                WHERE ehgr.escalation_esc_id = esc.esc_id ".$aclCond['hg'].") > 0 ";
	else if ($list && $list == "sg")
		$rq .= " WHERE (SELECT DISTINCT COUNT(*) 
                                FROM escalation_servicegroup_relation esgr 
                                WHERE esgr.escalation_esc_id = esc.esc_id ".$aclCond['sg'].") > 0 ";
	else if ($list && $list == "ms")
		$rq .= " WHERE (SELECT DISTINCT COUNT(*) 
                                FROM escalation_meta_service_relation emsr 
                                WHERE emsr.escalation_esc_id = esc.esc_id ".$aclCond['ms'].") > 0 ";
	
	/*
	 * Check if $search is init
	 */
	if ($search && $list)
		$rq .= " AND (esc.esc_name LIKE '%".$search."%' OR esc.esc_alias LIKE '%".$search."%')";
	else if ($search)
		$rq .= " WHERE (esc.esc_name LIKE '%".$search."%' OR esc.esc_alias LIKE '%".$search."%')";
	
	/*
	 * Set Order and limits
	 */
	$rq .= " ORDER BY esc_name LIMIT ".$num * $limit.", ".$limit;
	
	$DBRESULT = $pearDB->query($rq);
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
	for ($i = 0; $esc = $DBRESULT->fetchRow(); $i++) {		
		$moptions = "";
		$selectedElements = $form->addElement('checkbox', "select[".$esc['esc_id']."]");	
		$moptions .= "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$esc['esc_id']."]'></input>";
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>myDecode($esc["esc_name"]),
						"RowMenu_alias"=>myDecode($esc["esc_alias"]),
						"RowMenu_link"=>"?p=".$p."&o=c&esc_id=".$esc['esc_id'],
						"RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";	}
	$tpl->assign("elemArr", $elemArr);
	
	/*
	 * Different messages we put in the template
	 */
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));

	/*
	 * Toolbar select more_actions
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
	
	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listEscalation.ihtml");
?>