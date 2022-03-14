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

if (isset($_POST['searchACLR'])) {
    $search = filter_var($_POST['searchACLR'], FILTER_SANITIZE_STRING);
    $centreon->historySearch[$url] = $search;
} elseif (isset($_GET['searchACLR'])) {
    $search = filter_var($_GET['searchACLR'], FILTER_SANITIZE_STRING);
    $centreon->historySearch[$url] = $search;
} elseif (isset($centreon->historySearch[$url])) {
    $search = $centreon->historySearch[$url];
}

if ($search) {
    $searchStr = "AND (acl_res_name LIKE :search OR acl_res_alias LIKE :search)";
}
$rq = "
    SELECT SQL_CALC_FOUND_ROWS acl_res_id, acl_res_name, acl_res_alias, all_hosts, all_hostgroups,
    all_servicegroups, acl_res_activate FROM acl_resources
    WHERE locked = 0
    $searchStr
    ORDER BY acl_res_name
    LIMIT :num, :limit
";
$statement = $pearDB->prepare($rq);
if ($search) {
    $statement->bindValue(':search', '%' . $search . '%', \PDO::PARAM_STR);
}
$statement->bindValue(':num', $num * $limit, \PDO::PARAM_INT);
$statement->bindValue(':limit', $limit, \PDO::PARAM_INT);
$statement->execute();

$rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();

include("./include/common/checkPagination.php");

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl(__DIR__, $tpl);

/*
 * start header menu
 */
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_alias", _("Description"));
$tpl->assign("headerMenu_contacts", _("Contacts"));
$tpl->assign("headerMenu_allH", _("All Hosts"));
$tpl->assign("headerMenu_allHG", _("All Hostgroups"));
$tpl->assign("headerMenu_allSG", _("All Servicegroups"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_options", _("Options"));

$search = tidySearchKey($search, $advanced_search);

$form = new HTML_QuickFormCustom('select_form', 'POST', "?p=" . $p);

/*
 * Different style between each lines
 */
$style = "one";

/*
 * Fill a tab with a mutlidimensionnal Array we put in $tpl
 */
$elemArr = array();
$centreonToken = createCSRFToken();

for ($i = 0; $resources = $statement->fetchRow(); $i++) {
    $selectedElements = $form->addElement('checkbox', "select[" . $resources['acl_res_id'] . "]");

    if ($resources["acl_res_activate"]) {
        $moptions = "<a href='main.php?p=" . $p . "&acl_res_id=" . $resources['acl_res_id']
            . "&o=u&limit=" . $limit . "&num=" . $num . "&search=" . $search . "&centreon_token=" . $centreonToken
            . "'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='ico-14-disabled margin_right' viewBox='0 0 22 22' >
                        <path d='M0 0h24v24H0z' fill='none'/>
                        <path d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8 0-1.85.63-3.55 1.69-4.9L16.9 18.31C15.55 19.37 13.85 20 12 20zm6.31-3.1L7.1 5.69C8.45 4.63 10.15 4 12 4c4.42 0 8 3.58 8 8 0 1.85-.63 3.55-1.69 4.9z'/>
                    </svg>
                </a>&nbsp;&nbsp;";
    } else {
        $moptions = "<a href='main.php?p=" . $p . "&acl_res_id=" . $resources['acl_res_id']
            . "&o=s&limit=" . $limit . "&num=" . $num . "&search=" . $search . "&centreon_token=" . $centreonToken
            . "'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='ico-14-enabled margin_right' viewBox='0 0 24 24' >
                        <path d='M0 0h24v24H0z' fill='none'/>
                        <path d='M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z'/>
                    </svg>
               </a>&nbsp;&nbsp;";
    }
    $moptions .= "&nbsp;";
    $moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57))"
        . " event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) "
        . "return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr["
        . $resources['acl_res_id'] . "]'></input>";

    /* Contacts */
    $ctNbr = array();
    $rq = "SELECT COUNT(*) AS nbr
          FROM acl_resources_host_relations
          WHERE acl_res_id = '" . $resources['acl_res_id'] . "'";
    $DBRESULT2 = $pearDB->query($rq);
    $ctNbr = $DBRESULT2->fetchRow();

    $allHostgroups = (isset($resources["all_hostgroups"]) && $resources["all_hostgroups"] == 1 ? _("Yes") : _("No"));
    $allServicegroups = (isset($resources["all_servicegroups"]) && $resources["all_servicegroups"] == 1 ?
        _("Yes") :
        _("No"));

    $elemArr[$i] = array(
        "MenuClass" => "list_" . $style,
        "RowMenu_select" => $selectedElements->toHtml(),
        "RowMenu_name" => $resources["acl_res_name"],
        "RowMenu_alias" => myDecode($resources["acl_res_alias"]),
        "RowMenu_all_hosts" => (isset($resources["all_hosts"]) && $resources["all_hosts"] == 1 ? _("Yes") : _("No")),
        "RowMenu_all_hostgroups" => $allHostgroups,
        "RowMenu_all_servicegroups" => $allServicegroups,
        "RowMenu_link" => "main.php?p=" . $p . "&o=c&acl_res_id=" . $resources['acl_res_id'],
        "RowMenu_status" => $resources["acl_res_activate"] ? _("Enabled") : _("Disabled"),
        "RowMenu_badge" => $resources["acl_res_activate"] ? "service_ok" : "service_critical",
        "RowMenu_options" => $moptions
    );

    $style != "two" ? $style = "two" : $style = "one";
}
$tpl->assign("elemArr", $elemArr);

/*
 * Different messages we put in the template
 */
$tpl->assign('msg', array(
    "addL" => "main.php?p=" . $p . "&o=a",
    "addT" => _("Add"),
    "testT" => _("Check User View"),
    "testL" => "main.php?p=" . $p . "&o=t&min=1",
    "delConfirm" => _("Do you confirm the deletion ?")
));

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
    $o1 = $form->getElement($option);
    $o1->setValue(null);
}

$tpl->assign('limit', $limit);
$tpl->assign('searchACLR', $search);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listsResourcesAccess.ihtml");
