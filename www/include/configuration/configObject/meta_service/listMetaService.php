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


$search = '';
if (isset($_POST['searchMS']) && $_POST['searchMS'] != "") {
    $search = $_POST['searchMS'];
    $DBRESULT = $pearDB->query("SELECT COUNT(*)
                                FROM meta_service
                                WHERE meta_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%' ".
                                $acl->queryBuilder('AND', 'meta_id', $metaStr));
} else {
    $DBRESULT = $pearDB->query("SELECT COUNT(*)
                                FROM meta_service ".
                                $acl->queryBuilder('WHERE', 'meta_id', $metaStr));
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
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_type", _("Calculation Type"));
$tpl->assign("headerMenu_levelw", _("Warning Level"));
$tpl->assign("headerMenu_levelc", _("Critical Level"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_options", _("Options"));

$calcType = array("AVE"=>_("Average"), "SOM"=>_("Sum"), "MIN"=>_("Min"), "MAX"=>_("Max"));

/*
 * Meta Service list
 */
if ($search) {
    $rq = "SELECT *
           FROM meta_service
           WHERE meta_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%' ".
           $acl->queryBuilder("AND", "meta_id", $metaStr).
          "ORDER BY meta_name
           LIMIT ".$num * $limit.", ".$limit;
} else {
    $rq = "SELECT *
           FROM meta_service ".
           $acl->queryBuilder("WHERE", "meta_id", $metaStr).
          "ORDER BY meta_name
           LIMIT ".$num * $limit.", ".$limit;
}
$DBRESULT = $pearDB->query($rq);

$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);

/*
 * Different style between each lines
 */
$style = "one";

/*
 * Fill a tab with a mutlidimensionnal Array we put in $tpl
 */
$elemArr = array();
for ($i = 0; $ms = $DBRESULT->fetchRow(); $i++) {
    $moptions = "";
    $selectedElements = $form->addElement('checkbox', "select[".$ms['meta_id']."]");
    if ($ms["meta_select_mode"] == 1) {
        $moptions = "<a href='main.php?p=".$p."&meta_id=".$ms['meta_id']."&o=ci&search=".$search."'><img src='img/icons/redirect.png' class='ico-16' border='0' alt='"._("View")."'></a>&nbsp;&nbsp;";
    } else {
        $moptions = "";
    }

    if ($ms["meta_activate"]) {
        $moptions .= "<a href='main.php?p=".$p."&meta_id=".$ms['meta_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icons/disabled.png' class='ico-14 margin_right' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
    } else {
        $moptions .= "<a href='main.php?p=".$p."&meta_id=".$ms['meta_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icons/enabled.png' class='ico-14 margin_right' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
    }
    $moptions .= "&nbsp;";

    $moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$ms['meta_id']."]'></input>";

    $elemArr[$i] = array("MenuClass"=>"list_".$style,
                    "RowMenu_select"=>$selectedElements->toHtml(),
                    "RowMenu_name"=>CentreonUtils::escapeSecure($ms["meta_name"]),
                    "RowMenu_link"=>"?p=".$p."&o=c&meta_id=".$ms['meta_id'],
                    "RowMenu_type"=>CentreonUtils::escapeSecure($calcType[$ms["calcul_type"]]),
                    "RowMenu_levelw"=>isset($ms["warning"]) && $ms["warning"] ? $ms["warning"] : "-",
                    "RowMenu_levelc"=>isset($ms["critical"]) && $ms["critical"] ? $ms["critical"] : "-",
                    "RowMenu_status"=>$ms["meta_activate"] ? _("Enabled") : _("Disabled"),
                    "RowMenu_badge"     => $ms["meta_activate"] ? "service_ok" : "service_critical",
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
</script>
<?php

$attrs1 = array(
    'onchange'=>"javascript: " .
                            " var bChecked = isChecked(); ".
                            " if (this.form.elements['o1'].selectedIndex != 0 && !bChecked) {".
                            " alert('"._("Please select one or more items")."'); return false;} " .
            "if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "else if (this.form.elements['o1'].selectedIndex == 3) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "");
$form->addElement('select', 'o1', null, array(null=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs1);
$form->setDefaults(array('o1' => null));

$attrs2 = array(
    'onchange'=>"javascript: " .
                            " var bChecked = isChecked(); ".
                            " if (this.form.elements['o2'].selectedIndex != 0 && !bChecked) {".
                            " alert('"._("Please select one or more items")."'); return false;} " .
            "if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "else if (this.form.elements['o2'].selectedIndex == 3) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "");
$form->addElement('select', 'o2', null, array(null=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs2);
$form->setDefaults(array('o2' => null));

$o1 = $form->getElement('o1');
$o1->setValue(null);
$o1->setSelected(null);

$o2 = $form->getElement('o2');
$o2->setValue(null);
$o2->setSelected(null);

$tpl->assign('limit', $limit);
$tpl->assign('searchMS', $search);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listMetaService.ihtml");
