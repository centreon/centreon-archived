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

include("./include/common/autoNumLimit.php");

$searchStr = '';
$search = null;

if (isset($_POST['searchACLG'])) {
    $search = filter_var($_POST['searchACLG'], FILTER_SANITIZE_STRING);
    $centreon->historySearch[$url] = $search;
} elseif (isset($_GET['searchACLG'])) {
    $search = filter_var($_GET['searchACLG'], FILTER_SANITIZE_STRING);
    $centreon->historySearch[$url] = $search;
} elseif (isset($centreon->historySearch[$url])) {
    $search = $centreon->historySearch[$url];
}

if ($search) {
    $searchStr .= "WHERE (acl_group_name LIKE :search OR acl_group_alias LIKE :search)";
}

$rq = "
    SELECT SQL_CALC_FOUND_ROWS acl_group_id, acl_group_name, acl_group_alias, acl_group_activate
    FROM acl_groups
    $searchStr
    ORDER BY acl_group_name LIMIT :num, :limit
";
$statement = $pearDB->prepare($rq);
if ($search) {
    $statement->bindValue(':search', '%' . $search . '%', \PDO::PARAM_STR);
}
$statement->bindValue(':num', $num * $limit, \PDO::PARAM_INT);
$statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
$statement->execute();

$search = tidySearchKey($search, $advanced_search);
$rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();

include("./include/common/checkPagination.php");

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

//start header menu
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_desc", _("Description"));
$tpl->assign("headerMenu_contacts", _("Contacts"));
$tpl->assign("headerMenu_contactgroups", _("Contact Groups"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_options", _("Options"));


$form = new HTML_QuickFormCustom('select_form', 'POST', "?p=" . $p);

/*
 * Different style between each lines
 */
$style = "one";

/*
 * Fill a tab with a mutlidimensionnal Array we put in $tpl
 */
$elemArr = array();
$form->createSecurityToken();
$centreonToken = is_array($form->getElementValue('centreon_token')) ?
    end($form->getElementValue('centreon_token')) :
    $form->getElementValue('centreon_token');

for ($i = 0; $group = $statement->fetchRow(); $i++) {
    $selectedElements = $form->addElement('checkbox', "select[" . $group['acl_group_id'] . "]");
    if ($group["acl_group_activate"]) {
        $moptions = "<a href='main.php?p=" . $p . "&acl_group_id=" . $group['acl_group_id'] . "&o=u&limit=" . $limit .
            "&num=" . $num . "&search=" . $search . "&centreon_token=" . $centreonToken .
            "'><img src='img/icons/disabled.png' class='ico-14 margin_right' " .
            "border='0' alt='" . _("Disabled") . "'></a>&nbsp;&nbsp;";
    } else {
        $moptions = "<a href='main.php?p=" . $p . "&acl_group_id=" . $group['acl_group_id'] . "&o=s&limit=" . $limit .
            "&num=" . $num . "&search=" . $search . "&centreon_token=" . $centreonToken .
            "'><img src='img/icons/enabled.png' class='ico-14 margin_right' " .
            "border='0' alt='" . _("Enabled") . "'></a>&nbsp;&nbsp;";
    }

    $moptions .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) " .
        "event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) " .
        "return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[" .
        $group['acl_group_id'] . "]' />";

    /* Contacts */
    $ctNbr = array();
    $rq2 = "SELECT COUNT(*) AS nbr FROM acl_group_contacts_relations " .
        "WHERE acl_group_id = '" . $group['acl_group_id'] . "'";
    $dbResult2 = $pearDB->query($rq2);
    $ctNbr = $dbResult2->fetchRow();
    $dbResult2->closeCursor();

    $cgNbr = array();
    $rq3 = "SELECT COUNT(*) AS nbr FROM acl_group_contactgroups_relations " .
        "WHERE acl_group_id = '" . $group['acl_group_id'] . "'";
    $dbResult3 = $pearDB->query($rq3);
    $cgNbr = $dbResult3->fetchRow();
    $dbResult3->closeCursor();

    $elemArr[$i] = array(
        "MenuClass" => "list_" . $style,
        "RowMenu_select" => $selectedElements->toHtml(),
        "RowMenu_name" => CentreonUtils::escapeAll(
            $group["acl_group_name"],
            CentreonUtils::ESCAPE_ALL
        ),
        "RowMenu_link" => "main.php?p=" . $p . "&o=c&acl_group_id=" . $group['acl_group_id'],
        "RowMenu_desc" => CentreonUtils::escapeAll(
            $group["acl_group_alias"],
            CentreonUtils::ESCAPE_ALL
        ),
        "RowMenu_contacts" => $ctNbr["nbr"],
        "RowMenu_contactgroups" => $cgNbr["nbr"],
        "RowMenu_status" => $group["acl_group_activate"] ? _("Enabled") : _("Disabled"),
        "RowMenu_badge" => $group["acl_group_activate"] ? "service_ok" : "service_critical",
        "RowMenu_options" => $moptions
    );

    $style != "two" ? $style = "two" : $style = "one";
}
$tpl->assign("elemArr", $elemArr);

/*
 * Different messages we put in the template
 */
$tpl->assign(
    'msg',
    array("addL" => "main.php?p=" . $p . "&o=a", "addT" => _("Add"), "delConfirm" => _("Do you confirm the deletion ?"))
);

/*
 * Toolbar select lgd_more_actions
 */
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
            . "if (this.form.elements['$option'].selectedIndex == 1 && confirm('"
            . _("Do you confirm the duplication ?") . "')) {"
            . "setO(this.form.elements['$option'].value); submit();} "
            . "else if (this.form.elements['$option'].selectedIndex == 2 && confirm('"
            . _("Do you confirm the deletion ?") . "')) {"
            . "setO(this.form.elements['$option'].value); submit();} "
            . "else if (this.form.elements['$option'].selectedIndex == 3 || "
            . "this.form.elements['$option'].selectedIndex == 4) {"
            . "setO(this.form.elements['$option'].value); submit();}"
    );
    $form->addElement('select', $option, null, array(
        null => _("More actions..."),
        "m" => _("Duplicate"),
        "d" => _("Delete"),
        "ms" => _("Enable"),
        "mu" => _("Disable")
    ), $attrs1);
    $o1 = $form->getElement($option);
    $o1->setValue(null);
}

$tpl->assign('limit', $limit);
$tpl->assign('searchACLG', $search);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());

$tpl->display("listGroupConfig.ihtml");

?>
