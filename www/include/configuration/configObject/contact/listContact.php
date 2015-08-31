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
	 * Create Timeperiod Cache
	 */
	$tpCache = array("" => "");
	$DBRESULT = $pearDB->query("SELECT tp_name, tp_id FROM timeperiod");
	while ($data = $DBRESULT->fetchRow())
		$tpCache[$data["tp_id"]] = $data["tp_name"];
	unset($data);
	$DBRESULT->free();

    $clauses = array();
    if (isset($search) && $search) {
        $clauses = array('contact_name'  => array('LIKE', '%'.$search.'%'),
                         'contact_alias' => array('OR', 'LIKE', '%'.$search.'%'));
    }

    $aclOptions = array('fields' => array('contact_id',
                                          'timeperiod_tp_id',
                                          'timeperiod_tp_id2',
                                          'contact_name',
                                          'contact_alias',
                                          'contact_lang',
                                          'contact_oreon',
                                          'contact_host_notification_options',
                                          'contact_service_notification_options',
                                          'contact_activate',
                                          'contact_email',
                                          'contact_admin',
                                          'contact_register'),
                        'keys'  => array('contact_id'),
                        'order' => array('contact_name'),
                        'conditions' => $clauses);
    $contacts = $acl->getContactAclConf($aclOptions);
	$rows = count($contacts);

	include("./include/common/checkPagination.php");

	/*
	 * Smarty template Init
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
	$tpl->assign("headerMenu_name", _("Full Name"));
	$tpl->assign("headerMenu_desc", _("Alias / Login"));
	$tpl->assign("headerMenu_email", _("Email"));
	$tpl->assign("headerMenu_hostNotif", _("Host Notification Period"));
	$tpl->assign("headerMenu_svNotif", _("Services Notification Period"));
	$tpl->assign("headerMenu_lang", _("Language"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_access", _("Access"));
	$tpl->assign("headerMenu_admin", _("Admin"));
	$tpl->assign("headerMenu_options", _("Options"));

	/*
	 * Contact list
	 */
    $aclOptions['pages'] = $num * $limit.", ".$limit;
    $contacts = $acl->getContactAclConf($aclOptions);

	$search = tidySearchKey($search, $advanced_search);

	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);

	/*
	 * Different style between each lines
	 */
	$style = "one";
	$contactTypeIcone = array(1 => "./img/icones/16x16/guard.gif", 2 => "./img/icones/16x16/user1.gif", 3 => "./img/icones/16x16/user1_information.png");
	$contactTypeIconeTitle = array(1 => _("This user is an administrator."), 2 => _("This user is a simple user."), 3 => _("This is a contact template."));

	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr = array();
	foreach ($contacts as $contact) {
		$selectedElements = $form->addElement('checkbox', "select[".$contact['contact_id']."]");

		$moptions = "";
		if ($contact["contact_id"] != $centreon->user->get_id()) {
			if ($contact["contact_activate"]) {
				$moptions .= "<a href='main.php?p=".$p."&contact_id=".$contact['contact_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
			} else {
				$moptions .= "<a href='main.php?p=".$p."&contact_id=".$contact['contact_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
			}
		} else {
			$moptions .= "<img src='img/icones/16x16/element_next_grey.gif' border='0' alt='"._("Enabled")."'>&nbsp;&nbsp;";
		}
		$moptions .= "&nbsp;&nbsp;&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$contact['contact_id']."]'></input>";

		$contact_type = 0;
		if ($contact["contact_register"]) {
			if ($contact["contact_admin"] == 1) {
				$contact_type = 1;
			} else {
				$contact_type = 2;
			}
		} else {
			$contact_type = 3;
		}

		$elemArr[] = array("MenuClass" => "list_".$style,
						"RowMenu_select" => $selectedElements->toHtml(),
						"RowMenu_name" => html_entity_decode($contact["contact_name"], ENT_QUOTES, "UTF-8"),
						"RowMenu_ico" => isset($contactTypeIcone[$contact_type]) ? $contactTypeIcone[$contact_type] : "",
						"RowMenu_ico_title" => isset($contactTypeIconeTitle[$contact_type]) ? $contactTypeIconeTitle[$contact_type] : "",
						"RowMenu_type" => $contact_type,
						"RowMenu_link" => "?p=".$p."&o=c&contact_id=".$contact['contact_id'],
						"RowMenu_desc" => html_entity_decode($contact["contact_alias"], ENT_QUOTES, "UTF-8"),
						"RowMenu_email" => $contact["contact_email"],
						"RowMenu_hostNotif" => html_entity_decode($tpCache[(isset($contact["timeperiod_tp_id"]) ? $contact["timeperiod_tp_id"] : "")], ENT_QUOTES, "UTF-8")." (".(isset($contact["contact_host_notification_options"]) ? $contact["contact_host_notification_options"] : "").")",
						"RowMenu_svNotif" => html_entity_decode($tpCache[(isset($contact["timeperiod_tp_id2"]) ? $contact["timeperiod_tp_id2"] : "")], ENT_QUOTES, "UTF-8")." (".(isset($contact["contact_service_notification_options"]) ? $contact["contact_service_notification_options"] : "").")",
						"RowMenu_lang" => $contact["contact_lang"],
						"RowMenu_access" => $contact["contact_oreon"] ? _("Enabled") : _("Disabled"),
						"RowMenu_admin" => $contact["contact_admin"] ? _("Yes") : _("No"),
						"RowMenu_status" => $contact["contact_activate"] ? _("Enabled") : _("Disabled"),
						"RowMenu_options" => $moptions);
		$style != "two" ? $style = "two" : $style = "one";
	}
	$tpl->assign("elemArr", $elemArr);

	/*
	 * Different messages we put in the template
	 */
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"),"ldap_importL"=>"?p=".$p."&o=li", "ldap_importT"=>_("LDAP Import"), "view_notif" => _("View contact notifications")));
	if ($centreon->optGen['ldap_auth_enable']) {
		$tpl->assign('ldap', $centreon->optGen['ldap_auth_enable'] );
	}

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
				"else if (this.form.elements['o1'].selectedIndex == 3 || this.form.elements['o1'].selectedIndex == 4 ||this.form.elements['o1'].selectedIndex == 5){" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "mc"=>_("Massive Change"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs1);
	$form->setDefaults(array('o1' => NULL));

	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3 || this.form.elements['o2'].selectedIndex == 4 ||this.form.elements['o2'].selectedIndex == 5){" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "mc"=>_("Massive Change"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs2);
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
	$tpl->display("listContact.ihtml");
?>
