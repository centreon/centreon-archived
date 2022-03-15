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

include_once "./class/centreonUtils.class.php";

include "./include/common/autoNumLimit.php";

// Search engine

$search = filter_var(
    $_POST['searchR'] ?? $_GET['searchR'] ?? null,
    FILTER_SANITIZE_STRING
);

if (isset($_POST['searchR']) || isset($_GET['searchR'])) {
    //saving filters values
    $centreon->historySearch[$url] = array();
    $centreon->historySearch[$url]['search'] = $search;
} else {
    //restoring saved values
    $search = $centreon->historySearch[$url]['search'] ?? null;
}

$SearchTool = '';
if ($search) {
    $SearchTool .= " WHERE resource_name LIKE '%" . htmlentities($search, ENT_QUOTES, "UTF-8") . "%'";
}

$aclCond = "";
if (!$oreon->user->admin && count($allowedResourceConf)) {
    if (isset($search) && $search) {
        $aclCond = " AND ";
    } else {
        $aclCond = " WHERE ";
    }
    $aclCond .= "resource_id IN (" . implode(',', array_keys($allowedResourceConf)) . ") ";
}

// resources list
$dbResult = $pearDB->query(
    "SELECT SQL_CALC_FOUND_ROWS * FROM cfg_resource " . $SearchTool . $aclCond .
    " ORDER BY resource_name LIMIT " . $num * $limit . ", " . $limit
);

$rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();

include "./include/common/checkPagination.php";

// Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

// Access level
($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';
$tpl->assign('mode_access', $lvl_access);

// start header menu
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_values", _("Values"));
$tpl->assign("headerMenu_comment", _("Description"));
$tpl->assign("headerMenu_associated_poller", _("Associated pollers"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_options", _("Options"));

$form = new HTML_QuickFormCustom('select_form', 'POST', "?p=" . $p);

// Different style between each lines
$style = "one";

// Fill a tab with a multidimensional Array we put in $tpl
$elemArr = array();
$centreonToken = createCSRFToken();

for ($i = 0; $resource = $dbResult->fetch(); $i++) {
    preg_match("\$USER([0-9]*)\$", $resource["resource_name"], $tabResources);
    $selectedElements = $form->addElement('checkbox', "select[" . $resource['resource_id'] . "]");
    $moptions = "";
    if ($resource["resource_activate"]) {
        $moptions .= "<a href='main.php?p=" . $p . "&resource_id=" . $resource['resource_id'] . "&o=u&limit=" .
            $limit . "&num=" . $num . "&search=" . $search . "&centreon_token=" . $centreonToken .
            "'><svg xmlns='http://www.w3.org/2000/svg' class='ico-14-disabled margin_right' viewBox='0 0 22 22' >
                <path d='M0 0h24v24H0z' fill='none'/>
        <path d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8 0-1.85.63-3.55 1.69-4.9L16.9 18.31C15.55 19.37 13.85 20 12 20zm6.31-3.1L7.1 5.69C8.45 4.63 10.15 4 12 4c4.42 0 8 3.58 8 8 0 1.85-.63 3.55-1.69 4.9z'/>
        </svg></a>&nbsp;&nbsp;";
    } else {
        $moptions .= "<a href='main.php?p=" . $p . "&resource_id=" . $resource['resource_id'] . "&o=s&limit=" .
            $limit . "&num=" . $num . "&search=" . $search . "&centreon_token=" . $centreonToken .
            "'><svg xmlns='http://www.w3.org/2000/svg' class='ico-14-enabled margin_right' viewBox='0 0 24 24' >
    <path d='M0 0h24v24H0z' fill='none'/>
    <path d='M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z'/></svg></a>&nbsp;&nbsp;";
    }
    $moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) " .
        "event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" " .
        "maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[" .
        $resource['resource_id'] . "]' />";
    $elemArr[$i] = array(
        "order" => isset($tabResources[1]) ? $tabResources[1] : null,
        "MenuClass" => "list_" . $style,
        "RowMenu_select" => $selectedElements->toHtml(),
        "RowMenu_name" => CentreonUtils::escapeSecure(
            $resource["resource_name"],
            CentreonUtils::ESCAPE_ALL_EXCEPT_LINK
        ),
        "RowMenu_link" => "main.php?p=" . $p . "&o=c&resource_id=" . $resource['resource_id'],
        "RowMenu_values" => CentreonUtils::escapeSecure(
            substr($resource["resource_line"], 0, 40),
            CentreonUtils::ESCAPE_ALL_EXCEPT_LINK
        ),
        "RowMenu_comment" => CentreonUtils::escapeSecure(
            substr(
                html_entity_decode(
                    $resource["resource_comment"],
                    ENT_QUOTES,
                    "UTF-8"
                ),
                0,
                40
            ),
            CentreonUtils::ESCAPE_ALL_EXCEPT_LINK
        ),
        "RowMenu_associated_poller" => getLinkedPollerList($resource['resource_id']),
        "RowMenu_status" => $resource["resource_activate"] ? _("Enabled") : _("Disabled"),
        "RowMenu_badge" => $resource["resource_activate"] ? "service_ok" : "service_critical",
        "RowMenu_options" => $moptions
    );
    $style != "two" ? $style = "two" : $style = "one";
}

$flag = 1;
while ($flag) {
    $flag = 0;
    foreach ($elemArr as $key => $value) {
        $key1 = $key + 1;
        if (isset($elemArr[$key + 1]) && $value["order"] > $elemArr[$key + 1]["order"]) {
            $swmapTab = $elemArr[$key + 1];
            $elemArr[$key + 1] = $elemArr[$key];
            $elemArr[$key] = $swmapTab;
            $flag = 1;
        } elseif (!isset($elemArr[$key + 1]) && isset($elemArr[$key - 1]["order"])) {
            if ($value["order"] < $elemArr[$key - 1]["order"]) {
                $swmapTab = $elemArr[$key - 1];
                $elemArr[$key - 1] = $elemArr[$key];
                $elemArr[$key] = $swmapTab;
                $flag = 1;
            }
        }
    }
}

$tpl->assign("elemArr", $elemArr);
// Different messages we put in the template
$tpl->assign(
    'msg',
    array(
        "addL" => "main.php?p=" . $p . "&o=a",
        "addT" => _("Add"),
        "delConfirm" => _("Do you confirm the deletion ?")
    )
);

// Toolbar select
?>
<script type="text/javascript">
    function setO(_i) {
        document.forms['form'].elements['o'].value = _i;
    }
</script>
<?php
foreach (array('o1', 'o2') as $option) {
    $attrs1 = array(
        'onchange' => "javascript: " .
            "if (this.form.elements['" . $option . "'].selectedIndex == 1 && confirm('" .
            _("Do you confirm the duplication ?") . "')) {" .
            " 	setO(this.form.elements['" . $option . "'].value); submit();} " .
            "else if (this.form.elements['" . $option . "'].selectedIndex == 2 && confirm('" .
            _("Do you confirm the deletion ?") . "')) {" .
            " 	setO(this.form.elements['" . $option . "'].value); submit();} " .
            "else if (this.form.elements['" . $option . "'].selectedIndex == 3) {" .
            " 	setO(this.form.elements['" . $option . "'].value); submit();} " .
            ""
    );
    $form->addElement(
        'select',
        $option,
        null,
        array(null => _("More actions"), "m" => _("Duplicate"), "d" => _("Delete")),
        $attrs1
    );
    $form->setDefaults(array($option => null));
    $o1 = $form->getElement($option);
    $o1->setValue(null);
    $o1->setSelected(null);
}

$tpl->assign('limit', $limit);
$tpl->assign('searchR', $search);

// Apply a template definition
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listResources.ihtml");