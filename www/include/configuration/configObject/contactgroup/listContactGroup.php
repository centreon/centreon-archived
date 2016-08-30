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

include_once "./include/common/autoNumLimit.php";

$SearchSTR = "";

$clauses = array();
$search = '';
if (isset($_POST['searchCG']) && $_POST['searchCG']) {
    $search = $_POST['searchCG'];
    $clauses = array('cg_name'  => array('LIKE', '%'.$search.'%'),
                     'cg_alias' => array('OR', 'LIKE', '%'.$search.'%'));
}

$aclOptions = array('fields' => array('cg_id', 'cg_name', 'cg_alias', 'cg_activate'),
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

/*
 * Different style between each lines
 */
$style = "one";

/*
 * Fill a tab with a mutlidimensionnal Array we put in $tpl
 */
$elemArr = array();
foreach ($cgs as $cg) {
    $selectedElements = $form->addElement('checkbox', "select[".$cg['cg_id']."]");
    if ($cg["cg_activate"]) {
        $moptions = "<a href='main.php?p=".$p."&cg_id=".$cg['cg_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icons/disabled.png' class='ico-14 margin_right' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
    } else {
        $moptions = "<a href='main.php?p=".$p."&cg_id=".$cg['cg_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icons/enabled.png' class='ico-14 margin_right' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
    }
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
                        "RowMenu_badge" => $cg["cg_activate"] ? "service_ok" : "service_critical",
                        "RowMenu_options"=>$moptions);
    $style != "two" ? $style = "two" : $style = "one";
}
$tpl->assign("elemArr", $elemArr);

/*
 * Different messages we put in the template
 */
$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?"), "view_notif" => _("View contact group notifications")));

foreach (array('o1', 'o2') as $option) {
    $attrs1 = array(
    'onchange'=>"javascript: " .
            " var bChecked = isChecked(); ".
            " if (this.form.elements['".$option."'].selectedIndex != 0 && !bChecked) {".
            " alert('"._("Please select one or more items")."'); return false;} " .
            "if (this.form.elements['".$option."'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
            " 	setO(this.form.elements['".$option."'].value); submit();} " .
            "else if (this.form.elements['".$option."'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
            " 	setO(this.form.elements['".$option."'].value); submit();} " .
            "else if (this.form.elements['".$option."'].selectedIndex == 3) {" .
            " 	setO(this.form.elements['".$option."'].value); submit();} " .
            "");
    $form->addElement('select', $option, null, array(null=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs1);
    $form->setDefaults(array($option => null));
    $o1 = $form->getElement($option);
    $o1->setValue(null);
    $o1->setSelected(null);
}

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
$tpl->assign('searchCG', $search);
$tpl->display("listContactGroup.ihtml");
