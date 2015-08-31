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

	$SearchSTR = "";

        $clauses = array();
        if (isset($search) && $search) {
            $clauses = array('cg_name'  => array('LIKE', '%'.$search.'%'),
                             'cg_alias' => array('OR', 'LIKE', '%'.$search.'%'));
        }

        $aclOptions = array('fields' => array('cg_id',
                                              'cg_name',
                                              'cg_alias',
                                              'cg_activate'),
                            'keys'  => array('cg_id'),
                            'order' => array('cg_name'),
                            'conditions' => $clauses);
        $cgs = $acl->getContactGroupAclConf($aclOptions);
        $rows = count($cgs);

	include_once "./include/common/checkPagination.php";

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/* Access level */
	($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';
	$tpl->assign('mode_access', $lvl_access);

	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_desc", _("Description"));
	$tpl->assign("headerMenu_contacts", _("Contacts"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_options", _("Options"));

	/*
	 * Contactgroup list
	 */
        $aclOptions['pages'] = $num * $limit.", ".$limit;
        $cgs = $acl->getContactGroupAclConf($aclOptions);
        
	$search = tidySearchKey($search, $advanced_search);

	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);

	#Different style between each lines
	$style = "one";

	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr = array();
	foreach ($cgs as $cg) {
		$selectedElements = $form->addElement('checkbox', "select[".$cg['cg_id']."]");
		if ($cg["cg_activate"])
			$moptions = "<a href='main.php?p=".$p."&cg_id=".$cg['cg_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		else
			$moptions = "<a href='main.php?p=".$p."&cg_id=".$cg['cg_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
		$moptions .= "&nbsp;&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$cg['cg_id']."]'></input>";
		/*
		 * Contacts
		 */
		$ctNbr = array();
		$rq = "SELECT COUNT(DISTINCT contact_contact_id) AS `nbr` 
                       FROM `contactgroup_contact_relation` `cgr` 
                       WHERE `cgr`.`contactgroup_cg_id` = '".$cg['cg_id']."' ".
                       $acl->queryBuilder('AND', 'contact_contact_id', $contactstring);
		$DBRESULT2 = $pearDB->query($rq);
		$ctNbr = $DBRESULT2->fetchRow();
		$elemArr[] = array("MenuClass"=>"list_".$style,
							"RowMenu_select"=>$selectedElements->toHtml(),
							"RowMenu_name"=>$cg["cg_name"],
							"RowMenu_link"=>"?p=".$p."&o=c&cg_id=".$cg['cg_id'],
							"RowMenu_desc"=>$cg["cg_alias"],
							"RowMenu_contacts"=>$ctNbr["nbr"],
							"RowMenu_status"=>$cg["cg_activate"] ? _("Enabled") : _("Disabled"),
							"RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";
	}
	$tpl->assign("elemArr", $elemArr);

	/*
	 * Different messages we put in the template
	 */
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));

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

	?><script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</script><?php

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('limit', $limit);
	$tpl->display("listContactGroup.ihtml");
?>