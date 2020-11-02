<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

if (!isset($oreon)) {
    exit();
}

include "./include/common/autoNumLimit.php";

$search = filter_var(
    $_POST['searchSC'] ?? $_GET['searchSC'] ?? null,
    FILTER_SANITIZE_STRING
);

if (isset($_POST['searchSC']) || isset($_GET['searchSC'])) {
    //saving filters values
    $centreon->historySearch[$url] = array();
    $centreon->historySearch[$url]["search"] = $search;
} else {
    //restoring saved values
    $search = $centreon->historySearch[$url]["search"] ?? null;
}

$searchTool = '';
if ($search) {
    $searchTool .= "WHERE (sc_name LIKE '%" . $search . "%' ".
    "OR sc_description LIKE '%" . $search . "%') ";
}

$aclCond = "";
if (!$oreon->user->admin && $scString != "''") {
    if (is_null($searchTool)) {
        $clause = " WHERE ";
    } else {
        $clause = " AND ";
    }
    $aclCond .= $acl->queryBuilder($clause, "sc_id", $scString);
}

/*
 * Services Categories Lists
 */
$dbResult = $pearDB->query(
    "SELECT SQL_CALC_FOUND_ROWS * FROM service_categories " . $searchTool . $aclCond .
    "ORDER BY sc_name LIMIT " . $num * $limit . ", " . $limit
);
$rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();

include "./include/common/checkPagination.php";

// Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

// Access level
$lvl_access = ($centreon->user->access->page($p) == 1) ? 'w' : 'r';
$tpl->assign('mode_access', $lvl_access);

/*
 * start header menu
 */
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_desc", _("Description"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_linked_svc", _("Number of linked services"));
$tpl->assign("headerMenu_sc_type", _("Type"));
$tpl->assign("headerMenu_options", _("Options"));

$search = tidySearchKey($search, $advanced_search);

$form = new HTML_QuickFormCustom('select_form', 'POST', "?p=" . $p);

// Different style between each lines
$style = "one";

$attrBtnSuccess = array(
    "class" => "btc bt_success",
    "onClick" => "window.history.replaceState('', '', '?p=" . $p . "');"
);
$form->addElement('submit', 'Search', _("Search"), $attrBtnSuccess);

// Fill a tab with a multidimensional Array we put in $tpl
$elemArr = array();
for ($i = 0; $sc = $dbResult->fetch(); $i++) {
    $moptions = "";
    $dbResult2 = $pearDB->query(
        "SELECT COUNT(*) FROM `service_categories_relation` WHERE `sc_id` = '" . $sc['sc_id'] . "'"
    );
    $nb_svc = $dbResult2->fetch();

    $selectedElements = $form->addElement('checkbox', "select[" . $sc['sc_id'] . "]");

    if ($sc["sc_activate"]) {
        $moptions .= "<a href='main.php?p=" . $p . "&sc_id=" . $sc['sc_id'] . "&o=u&limit=" . $limit .
            "&num=" . $num . "&search=" . $search . "'><img src='img/icons/disabled.png' class='ico-14 margin_right' "
            . "border='0' alt='" . _("Disabled") . "'></a>&nbsp;&nbsp;";
    } else {
        $moptions .= "<a href='main.php?p=" . $p . "&sc_id=" . $sc['sc_id'] . "&o=s&limit=" . $limit .
            "&num=" . $num . "&search=" . $search . "'><img src='img/icons/enabled.png' class='ico-14 margin_right' "
            . "border='0' alt='" . _("Enabled") . "'></a>&nbsp;&nbsp;";
    }
    $moptions .= "&nbsp;";
    $moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) " .
        "event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) " .
        "return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" " .
        "name='dupNbr[" . $sc['sc_id'] . "]' />";

    $elemArr[$i] = array(
        "MenuClass" => "list_" . $style,
        "RowMenu_select" => $selectedElements->toHtml(),
        "sc_name" => htmlentities($sc["sc_name"], ENT_QUOTES, "UTF-8"),
        "sc_link" => "main.php?p=" . $p . "&o=c&sc_id=" . $sc['sc_id'],
        "sc_description" => htmlentities($sc["sc_description"], ENT_QUOTES, "UTF-8"),
        "svc_linked" => $nb_svc["COUNT(*)"],
        "sc_type" => ($sc['level'] ? _('Severity') . ' (' . $sc['level'] . ')' : _('Regular')),
        "sc_activated" => $sc["sc_activate"] ? _("Enabled") : _("Disabled"),
        "RowMenu_badge" => $sc["sc_activate"] ? "service_ok" : "service_critical",
        "RowMenu_options" => $moptions
    );
    $style != "two" ? $style = "two" : $style = "one";
}
$tpl->assign("elemArr", $elemArr);

// Different messages we put in the template
$tpl->assign('msg', array("addL" => "main.php?p=" . $p . "&o=a", "addT" => _("Add")));

?>
<script type="text/javascript">
    function setO(_i) {
        document.forms['form'].elements['o'].value = _i;
    }
</script>
<?php
$attrs1 = array(
    'onchange' => "javascript: " .
        " var bChecked = isChecked(); " .
        " if (this.form.elements['o1'].selectedIndex != 0 && !bChecked) {" .
        " alert('" . _("Please select one or more items") . "'); return false;} " .
        "if (this.form.elements['o1'].selectedIndex == 1 && confirm('" .
        _("Do you confirm the duplication ?") . "')) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 2 && confirm('" .
        _("Do you confirm the deletion ?") . "')) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 3 || this.form.elements['o1'].selectedIndex == 4 " .
        "||this.form.elements['o1'].selectedIndex == 5){" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "this.form.elements['o1'].selectedIndex = 0"
);
$form->addElement(
    'select',
    'o1',
    null,
    array(
        null => _("More actions..."),
        "m" => _("Duplicate"),
        "d" => _("Delete"),
        "ms" => _("Enable"),
        "mu" => _("Disable")
    ),
    $attrs1
);
$form->setDefaults(array('o1' => null));

$attrs2 = array(
    'onchange' => "javascript: " .
        " var bChecked = isChecked(); " .
        " if (this.form.elements['o2'].selectedIndex != 0 && !bChecked) {" .
        " alert('" . _("Please select one or more items") . "'); return false;} " .
        "if (this.form.elements['o2'].selectedIndex == 1 && confirm('" .
        _("Do you confirm the duplication ?") . "')) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 2 && confirm('" .
        _("Do you confirm the deletion ?") . "')) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 3 || " .
        "this.form.elements['o2'].selectedIndex == 4 ||this.form.elements['o2'].selectedIndex == 5){" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "this.form.elements['o2'].selectedIndex = 0"
);
$form->addElement(
    'select',
    'o2',
    null,
    array(
        null => _("More actions"),
        "m" => _("Duplicate"),
        "d" => _("Delete"),
        "ms" => _("Enable"),
        "mu" => _("Disable")
    ),
    $attrs2
);
$form->setDefaults(array('o2' => null));

$o1 = $form->getElement('o1');
$o1->setValue(null);
$o1->setSelected(null);

$o2 = $form->getElement('o2');
$o2->setValue(null);
$o2->setSelected(null);

$tpl->assign('limit', $limit);
$tpl->assign('searchSC', $search);

// Apply a template definition
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listServiceCategories.ihtml");
