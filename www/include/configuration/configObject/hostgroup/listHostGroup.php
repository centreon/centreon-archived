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
 */

if (!isset($centreon)) {
    exit();
}

require_once "./class/centreonUtils.class.php";

require_once "./include/common/autoNumLimit.php";

/*
 * Object init
 */
$mediaObj = new CentreonMedia($pearDB);

// Search
$searchFilterQuery = null;
$mainQueryParameters = [];

$search = filter_var(
    $_POST['searchHg'] ?? $_GET['searchHg'] ?? null,
    FILTER_SANITIZE_STRING
);

if (isset($_POST['searchHg']) || isset($_GET['searchHg'])) {
    //saving chosen filters values
    $centreon->historySearch[$url] = array();
    $centreon->historySearch[$url]['search'] = $search;
} else {
    //restoring saved values
    $search = $centreon->historySearch[$url]['search'] ?? null;
}

if ($search) {
    $mainQueryParameters[':search_string'] = "%{$search}%";
    $searchFilterQuery = " (hg_name LIKE :search_string OR hg_alias LIKE :search_string) AND ";
}

/*
 *  Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

// Access level
$lvl_access = ($centreon->user->access->page($p) == 1) ? 'w' : 'r';
$tpl->assign('mode_access', $lvl_access);

// start header menu
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_desc", _("Alias"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_hostAct", _("Enabled Hosts"));
$tpl->assign("headerMenu_hostDeact", _("Disabled Hosts"));
$tpl->assign("headerMenu_options", _("Options"));

// Hostgroup list
$rq = "SELECT SQL_CALC_FOUND_ROWS hg_id, hg_name, hg_alias, hg_activate, hg_icon_image " .
    "FROM hostgroup " .
    "WHERE {$searchFilterQuery} hg_id NOT IN (SELECT hg_child_id FROM hostgroup_hg_relation) " .
    $acl->queryBuilder('AND', 'hg_id', $hgString) .
    " ORDER BY hg_name LIMIT " . $num * $limit . ", " . $limit;
$dbResult = $pearDB->query($rq, $mainQueryParameters);

// Pagination
$rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();
require_once "./include/common/checkPagination.php";

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
$centreonToken = createCSRFToken();

for ($i = 0; $hg = $dbResult->fetch(); $i++) {
    $selectedElements = $form->addElement('checkbox', "select[" . $hg['hg_id'] . "]");
    $moptions = "";
    if ($hg["hg_activate"]) {
        $moptions .= "<a href='main.php?p=" . $p . "&hg_id=" . $hg['hg_id'] . "&o=u&limit=" . $limit
            . "&num=" . $num . "&search=" . $search . "&centreon_token=" . $centreonToken
            . "'><img src='img/icons/disabled.png' class='ico-14 margin_right' border='0' alt='"
            . _("Disabled") . "'></a>";
    } else {
        $moptions .= "<a href='main.php?p=" . $p . "&hg_id=" . $hg['hg_id'] . "&o=s&limit=" . $limit
            . "&num=" . $num . "&search=" . $search . "&centreon_token=" . $centreonToken
            . "'><img src='img/icons/enabled.png' class='ico-14 margin_right' border='0' alt='"
            . _("Enabled") . "'></a>";
    }
    $moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57))"
        . " event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;"
        . "\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\""
        . " name='dupNbr[" . $hg['hg_id'] . "]'></input>";

    /*
     * Check Nbr of Host / hg
     */
    $nbrhostAct = array();
    $nbrhostDeact = array();
    $nbrhostgroupAct = array();
    $nbrhostgroupDeact = array();

    $aclFrom = "";
    $aclCond = "";
    if (!$centreon->user->admin) {
        $aclFrom = ", $aclDbName.centreon_acl acl ";
        $aclCond = " AND h.host_id = acl.host_id AND acl.group_id IN (" . $acl->getAccessGroupsString() . ") ";
    }
    $rq = "SELECT h.host_id, h.host_activate
               FROM hostgroup_relation hgr, host h $aclFrom
               WHERE hostgroup_hg_id = '" . $hg['hg_id'] . "'
               AND h.host_id = hgr.host_host_id
               AND h.host_register = '1' $aclCond";
    $dbResult2 = $pearDB->query($rq);
    $nbrhostActArr = array();
    $nbrhostDeactArr = array();
    while ($row = $dbResult2->fetch()) {
        if ($row['host_activate']) {
            $nbrhostActArr[$row['host_id']] = true;
        } else {
            $nbrhostDeactArr[$row['host_id']] = true;
        }
    }
    $nbrhostAct = count($nbrhostActArr);
    $nbrhostDeact = count($nbrhostDeactArr);

    if ($hg['hg_icon_image'] != "") {
        $hgIcone = "./img/media/" . $mediaObj->getFilename($hg['hg_icon_image']);
    } else {
        $hgIcone = "./img/icons/host_group.png";
    }
    $elemArr[$i] = array(
        "MenuClass" => "list_" . $style,
        "RowMenu_select" => $selectedElements->toHtml(),
        "RowMenu_name" => CentreonUtils::escapeSecure($hg["hg_name"]),
        "RowMenu_link" => "main.php?p=" . $p . "&o=c&hg_id=" . $hg['hg_id'],
        "RowMenu_desc" => ($hg["hg_alias"] == ''
            ? '-'
            : CentreonUtils::escapeSecure(html_entity_decode($hg["hg_alias"]))),
        "RowMenu_status" => $hg["hg_activate"] ? _("Enabled") : _("Disabled"),
        "RowMenu_badge" => $hg["hg_activate"] ? "service_ok" : "service_critical",
        "RowMenu_hostAct" => $nbrhostAct,
        "RowMenu_icone" => $hgIcone,
        "RowMenu_hostDeact" => $nbrhostDeact,
        "RowMenu_options" => $moptions
    );

    // Switch color line
    $style != "two" ? $style = "two" : $style = "one";
}
$tpl->assign("elemArr", $elemArr);

// Different messages put in the template
$tpl->assign(
    'msg',
    array(
        "addL" => "main.php?p=" . $p . "&o=a",
        "addT" => _("Add"),
        "delConfirm" => _("Do you confirm the deletion ?")
    )
);

?>
<script type="text/javascript">
    function setO(_i) {
        document.forms['form'].elements['o'].value = _i;
    }
</script>
<?php
foreach (array('o1', 'o2') as $option) {
    $attrs1 = array(
        'onchange' => "javascript: "
            . " var bChecked = isChecked(); "
            . " if (this.form.elements['$option'].selectedIndex != 0 && !bChecked) {"
            . " alert('" . _("Please select one or more items") . "'); return false;} "
            . "if (this.form.elements['$option'].selectedIndex == 1 && confirm('"
            . _("Do you confirm the duplication ?") . "')) {"
            . "   setO(this.form.elements['$option'].value); submit();} "
            . "else if (this.form.elements['$option'].selectedIndex == 2 && confirm('"
            . _("Do you confirm the deletion ?") . "')) {"
            . "   setO(this.form.elements['$option'].value); submit();} "
            . "else if (this.form.elements['$option'].selectedIndex == 3) {"
            . "   setO(this.form.elements['$option'].value); submit();} "
            . "else if (this.form.elements['$option'].selectedIndex == 4) {"
            . "   setO(this.form.elements['$option'].value); submit();} "
            . "this.form.elements['$option'].selectedIndex = 0"
    );
    $form->addElement(
        'select',
        $option,
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
    $form->setDefaults(array($option => null));
    $o1 = $form->getElement($option);
    $o1->setValue(null);
    $o1->setSelected(null);
}

$tpl->assign('searchHg', $search);
$tpl->assign('limit', $limit);

// Apply a template definition
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listHostGroup.ihtml");
