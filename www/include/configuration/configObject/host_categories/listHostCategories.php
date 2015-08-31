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

	/*
	 * start quickSearch form
	 */
	$advanced_search = 0;
	include_once("./include/common/quickSearch.php");

	/*
	 * Search
	 */
	$SearchTool = NULL;
	if (isset($search) && $search) {
		$SearchTool = " WHERE (hc_name LIKE '%".$pearDB->escape($search)."%' OR hc_alias LIKE '%".$pearDB->escape($search)."%')";
    }

    $hcFilter = "";
    if (!$oreon->user->admin && $hcString != "''") {
        $hcFilter = $acl->queryBuilder(is_null($SearchTool) ? 'WHERE' : 'AND',
                                       'hc_id',
                                       $hcString);
    }

	$request = "SELECT COUNT(*) FROM hostcategories $SearchTool $hcFilter";

	$DBRESULT = $pearDB->query($request);
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
	$tpl->assign("headerMenu_desc", _("Description"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_hc_type", _("Type"));
	$tpl->assign("headerMenu_hostAct", _("Enabled Hosts"));
	$tpl->assign("headerMenu_hostDeact", _("Disabled Hosts"));
	$tpl->assign("headerMenu_options", _("Options"));

	/*
	 * Hostgroup list
	 */
    $rq = "SELECT hc_id, hc_name, hc_alias, level, hc_activate FROM hostcategories $SearchTool $hcFilter ORDER BY hc_name LIMIT ".$num * $limit .", $limit";
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
	for ($i = 0; $hc = $DBRESULT->fetchRow(); $i++) {
		$selectedElements = $form->addElement('checkbox', "select[".$hc['hc_id']."]");
		$moptions = "";
		if ($hc["hc_activate"])
			$moptions .= "<a href='main.php?p=".$p."&hc_id=".$hc['hc_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='main.php?p=".$p."&hc_id=".$hc['hc_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
		$moptions .= "&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$hc['hc_id']."]'></input>";

		/*
		 * Check Nbr of Host / hc
		 */
		$nbrhostAct = array();
		$nbrhostDeact = array();
		$nbrhostgroupAct = array();
		$nbrhostgroupDeact = array();

        $aclFrom = "";
        $aclCond = "";
        if (!$oreon->user->admin) {
            $aclFrom = ", $aclDbName.centreon_acl acl ";
            $aclCond = " AND h.host_id = acl.host_id
                         AND acl.group_id IN (".$acl->getAccessGroupsString().") ";
        }
		$rq = "SELECT h.host_id, h.host_activate
               FROM hostcategories_relation hcr, host h $aclFrom
               WHERE hostcategories_hc_id = '".$hc['hc_id']."'
               AND h.host_id = hcr.host_host_id $aclCond
               AND h.host_register = '1' ";
		$DBRESULT2 = $pearDB->query($rq);
        $nbrhostActArr = array();
        $nbrhostDeactArr = array();
        while ($row = $DBRESULT2->fetchRow()) {
            if ($row['host_activate']) {
                $nbrhostActArr[$row['host_id']] = true;
            } else {
                $nbrhostDeactArr[$row['host_id']] = true;
            }
        }
		$nbrhostAct = count($nbrhostActArr);
		$nbrhostDeact = count($nbrhostDeactArr);

		$elemArr[$i] = array("MenuClass"=>"list_".$style,
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$hc["hc_name"],
						"RowMenu_link"=>"?p=".$p."&o=c&hc_id=".$hc['hc_id'],
						"RowMenu_desc"=>$hc["hc_alias"],
                                                "RowMenu_hc_type"=>($hc["level"] ? _('Severity') . ' ('.$hc['level'].')' : _('Regular')),
						"RowMenu_status"=>$hc["hc_activate"] ? _("Enabled") : _("Disabled"),
						"RowMenu_hostAct"=>$nbrhostAct,
						"RowMenu_hostDeact"=>$nbrhostDeact,
						"RowMenu_options"=>$moptions);
		/*
		 * Switch color line
		 */
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
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 4) {" .
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
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 4) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
    $form->addElement('select', 'o2', NULL, array(NULL => _("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs2);
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
	$tpl->display("listHostCategories.ihtml");
?>
