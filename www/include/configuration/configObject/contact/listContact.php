<?php
/*
 * Copyright 2005-2011 MERETHIS
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


	if (isset($search)) {
		$DBRESULT = $pearDB->query("SELECT COUNT(*) FROM contact WHERE (contact_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%' OR contact_alias LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%')");
	} else {
		$DBRESULT = $pearDB->query("SELECT COUNT(*) FROM contact");
	}
	$tmp = $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];

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
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_desc", _("Alias/Login"));
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
	if ($search) {
		$rq = "SELECT contact_id, timeperiod_tp_id, timeperiod_tp_id2, contact_name, contact_alias, contact_lang, contact_oreon, contact_host_notification_options, contact_service_notification_options, contact_activate, contact_email, contact_admin, contact_register FROM contact WHERE (contact_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%' OR contact_alias LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%') ORDER BY contact_name LIMIT ".$num * $limit.", ".$limit;
	} else {
		$rq = "SELECT contact_id, timeperiod_tp_id, timeperiod_tp_id2, contact_name, contact_alias, contact_lang, contact_oreon, contact_host_notification_options, contact_service_notification_options, contact_activate, contact_email, contact_admin, contact_register FROM contact ORDER BY contact_name LIMIT ".$num * $limit.", ".$limit;
	}
	$DBRESULT = $pearDB->query($rq);

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
	for ($i = 0; $contact = $DBRESULT->fetchRow(); $i++) {
		$selectedElements = $form->addElement('checkbox', "select[".$contact['contact_id']."]");

		$moptions = "";
		if ($contact["contact_activate"]) {
			$moptions .= "<a href='main.php?p=".$p."&contact_id=".$contact['contact_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		} else {
			$moptions .= "<a href='main.php?p=".$p."&contact_id=".$contact['contact_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
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
		
		$elemArr[$i] = array("MenuClass" => "list_".$style,
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