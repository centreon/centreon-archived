<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

if (!isset($centreon)) {
    exit();
}

include_once("./class/centreonUtils.class.php");

include("./include/common/autoNumLimit.php");

/*
 * Create Timeperiod Cache
 */
$tpCache = array("" => "");
$DBRESULT = $pearDB->query("SELECT tp_name, tp_id FROM timeperiod");
while ($data = $DBRESULT->fetchRow()) {
    $tpCache[$data["tp_id"]] = $data["tp_name"];
}
unset($data);
$DBRESULT->free();

$clauses = array();
$search = '';
if (isset($_POST['searchC']) && $_POST['searchC']) {
    $search = $_POST['searchC'];
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
$contactTypeIcone = array(1 => "./img/icons/admin.png", 2 => "./img/icons/user.png", 3 => "./img/icons/user_template.png");
$contactTypeIconeTitle = array(1 => _("This user is an administrator."), 2 => _("This user is a simple user."), 3 => _("This is a contact template."));

/*
 * Fill a tab with a mutlidimensionnal Array we put in $tpl
 */
$elemArr = array();
foreach ($contacts as $contact) {
    if ($centreon->user->get_id() == $contact['contact_id']) {
        $selectedElements = $form->addElement('checkbox', "select[".$contact['contact_id']."]", '', '', 'disabled');
    } else {
        $selectedElements = $form->addElement('checkbox', "select[".$contact['contact_id']."]");
    }
    $moptions = "";
    if ($contact["contact_id"] != $centreon->user->get_id()) {
        if ($contact["contact_activate"]) {
            $moptions .= "<a href='main.php?p=".$p."&contact_id=".$contact['contact_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icons/disabled.png' class='ico-14 margin_right' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
        } else {
            $moptions .= "<a href='main.php?p=".$p."&contact_id=".$contact['contact_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icons/enabled.png' class='ico-14 margin_right' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
        }
    } else {
        $moptions .= "&nbsp;&nbsp;";
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
                    "RowMenu_desc" => CentreonUtils::escapeSecure(html_entity_decode($contact["contact_alias"], ENT_QUOTES, "UTF-8")),
                    "RowMenu_email" => $contact["contact_email"],
                    "RowMenu_hostNotif" => html_entity_decode($tpCache[(isset($contact["timeperiod_tp_id"]) ? $contact["timeperiod_tp_id"] : "")], ENT_QUOTES, "UTF-8")." (".(isset($contact["contact_host_notification_options"]) ? $contact["contact_host_notification_options"] : "").")",
                    "RowMenu_svNotif" => html_entity_decode($tpCache[(isset($contact["timeperiod_tp_id2"]) ? $contact["timeperiod_tp_id2"] : "")], ENT_QUOTES, "UTF-8")." (".(isset($contact["contact_service_notification_options"]) ? $contact["contact_service_notification_options"] : "").")",
                    "RowMenu_lang" => $contact["contact_lang"],
                    "RowMenu_access" => $contact["contact_oreon"] ? _("Enabled") : _("Disabled"),
                    "RowMenu_admin" => $contact["contact_admin"] ? _("Yes") : _("No"),
                    "RowMenu_status" => $contact["contact_activate"] ? _("Enabled") : _("Disabled"),
                    "RowMenu_badge" => $contact["contact_activate"] ? "service_ok" : "service_critical",
                    "RowMenu_options" => $moptions);
    $style != "two" ? $style = "two" : $style = "one";
}
$tpl->assign("elemArr", $elemArr);

/*
 * Different messages we put in the template
 */
$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"),"ldap_importL"=>"?p=".$p."&o=li", "ldap_importT"=>_("LDAP Import"), "view_notif" => _("View contact notifications")));

# Display import ldap users button if ldap is configured
$query = "SELECT count(ar_id) as count_ldap "
    . "FROM auth_ressource ";
$res = $pearDB->query($query);
$row = $res->fetchRow();
if ($row['count_ldap'] > 0) {
    $tpl->assign('ldap', '1');
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

/* Manage options */
foreach (array('o1', 'o2') as $option) {
    $attrs1 = array(
        'onchange'=>"javascript: " .
                " var bChecked = isChecked(); ".
                " if (this.form.elements['".$option."'].selectedIndex != 0 && !bChecked) {".
                " alert('"._("Please select one or more items")."'); return false;} " .
                "if (this.form.elements['".$option."'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
                "   setO(this.form.elements['".$option."'].value); submit();} " .
                "else if (this.form.elements['".$option."'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
                "   setO(this.form.elements['".$option."'].value); submit();} " .
                "else if (this.form.elements['".$option."'].selectedIndex == 3 || this.form.elements['".$option."'].selectedIndex == 4 ||this.form.elements['".$option."'].selectedIndex == 5){" .
                "   setO(this.form.elements['".$option."'].value); submit();} " .
                "this.form.elements['".$option."'].selectedIndex = 0");
    $form->addElement('select', $option, null, array(null=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "mc"=>_("Massive Change"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs1);
    $form->setDefaults(array($option => null));
    
    $o1 = $form->getElement($option);
    $o1->setValue(null);
    $o1->setSelected(null);
}

$tpl->assign('limit', $limit);
$tpl->assign('searchC', $search);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listContact.ihtml");
