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

/*
 * nagios servers comes from DB
 */
$nagios_servers = array();
$dbResult = $pearDB->query("SELECT * FROM nagios_server ORDER BY name");
while ($nagios_server = $dbResult->fetch()) {
    $nagios_servers[$nagios_server["id"]] = $nagios_server["name"];
}
$dbResult->closeCursor();

// Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

// Access level
$lvl_access = ($centreon->user->access->page($p) == 1) ? 'w' : 'r';
$tpl->assign('mode_access', $lvl_access);

// start header menu
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_desc", _("Requester"));
$tpl->assign("headerMenu_outputs", _("Outputs"));
$tpl->assign("headerMenu_inputs", _("Inputs"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_options", _("Options"));

/*
 * Centreon Broker config list
 */

$search = filter_var(
    $_POST['searchCB'] ?? $_GET['searchCB'] ?? null,
    FILTER_SANITIZE_STRING
);

if (isset($_POST['searchCB']) || isset($_GET['searchCB'])) {
    //saving filters values
    $centreon->historySearch[$url] = array();
    $centreon->historySearch[$url]['search'] = $search;
} else {
    //restoring saved values
    $search = $centreon->historySearch[$url]['search'] ?? null;
}

$aclCond = "";
if (!$centreon->user->admin && count($allowedBrokerConf)) {
    if ($search) {
        $aclCond = " AND ";
    } else {
        $aclCond = " WHERE ";
    }
    $aclCond .= "config_id IN (" . implode(',', array_keys($allowedBrokerConf)) . ") ";
}

if ($search) {
    $rq = "SELECT SQL_CALC_FOUND_ROWS config_id, config_name, ns_nagios_server, config_activate " .
        "FROM cfg_centreonbroker " .
        "WHERE config_name LIKE '%" . $search . "%'" . $aclCond .
        " ORDER BY config_name " .
        "LIMIT " . $num * $limit . ", " . $limit;
} else {
    $rq = "SELECT SQL_CALC_FOUND_ROWS config_id, config_name, ns_nagios_server, config_activate " .
        "FROM cfg_centreonbroker " . $aclCond .
        " ORDER BY config_name " .
        "LIMIT " . $num * $limit . ", " . $limit;
}
$dbResult = $pearDB->query($rq);

// Get results numbers
$rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();

include "./include/common/checkPagination.php";

$form = new HTML_QuickFormCustom('select_form', 'POST', "?p=" . $p);

// Different style between each lines
$style = "one";

// Fill a tab with a multidimensional Array we put in $tpl
$elemArr = array();
$centreonToken = createCSRFToken();


for ($i = 0; $config = $dbResult->fetch(); $i++) {
    $moptions = "";
    $selectedElements = $form->addElement('checkbox', "select[" . $config['config_id'] . "]");

    if ($config["config_activate"]) {
        $moptions .= "<a href='main.php?p=" . $p . "&id=" . $config['config_id'] . "&o=u&limit=" . $limit . "&num="
            . $num . "&search=" . $search . "&centreon_token=" . $centreonToken .
            "'>
                <svg xmlns='http://www.w3.org/2000/svg' class='ico-14-disabled margin_right' viewBox='0 0 22 22' >
                    <path d='M0 0h24v24H0z' fill='none'/>
                    <path d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8
                     0-1.85.63-3.55 1.69-4.9L16.9 18.31C15.55 19.37 13.85 20 12 20zm6.31-3.1L7.1 5.69C8.45 4.63 10.15 4
                      12 4c4.42 0 8 3.58 8 8 0 1.85-.63 3.55-1.69 4.9z'/>
                </svg>
            </a>&nbsp;&nbsp;";
    } else {
        $moptions .= "<a href='main.php?p=" . $p . "&id=" . $config['config_id'] . "&o=s&limit=" . $limit . "&num=" .
            $num . "&search=" . $search . "&centreon_token=" . $centreonToken .
            "'>
                <svg xmlns='http://www.w3.org/2000/svg' class='ico-14-enabled margin_right' viewBox='0 0 24 24' >
                    <path d='M0 0h24v24H0z' fill='none'/>
                    <path d='M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z'/>
                </svg>
            </a>&nbsp;&nbsp;";
    }
    $moptions .= "&nbsp;<input onKeypress=\"if(event.keyCode > 31 "
        . "&& (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; "
        . "if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\""
        . "  maxlength=\"3\" size=\"3\" value='1' "
        . "style=\"margin-bottom:0px;\" name='dupNbr[" . $config['config_id'] . "]'></input>";

    // Number of output
    $res = $pearDB->query(
        "SELECT COUNT(DISTINCT(config_group_id)) as num " .
        "FROM cfg_centreonbroker_info " .
        "WHERE config_group = 'output' " .
        "AND config_id = " . $config['config_id']
    );
    $row = $res->fetch();
    $outputNumber = $row["num"];

    // Number of input
    $res = $pearDB->query(
        "SELECT COUNT(DISTINCT(config_group_id)) as num " .
        "FROM cfg_centreonbroker_info " .
        "WHERE config_group = 'input' " .
        "AND config_id = " . $config['config_id']
    );
    $row = $res->fetch();
    $inputNumber = $row["num"];

    $elemArr[$i] = array(
        "MenuClass" => "list_" . $style,
        "RowMenu_select" => $selectedElements->toHtml(),
        "RowMenu_name" => CentreonUtils::escapeSecure($config["config_name"]),
        "RowMenu_link" => "main.php?p=" . $p . "&o=c&id=" . $config['config_id'],
        "RowMenu_desc" => CentreonUtils::escapeSecure(
            substr(
                $nagios_servers[$config["ns_nagios_server"]],
                0,
                40
            )
        ),
        "RowMenu_inputs" => $inputNumber,
        "RowMenu_outputs" => $outputNumber,
        "RowMenu_status" => $config["config_activate"] ? _("Enabled") : _("Disabled"),
        "RowMenu_badge" => $config["config_activate"] ? "service_ok" : "service_critical",
        "RowMenu_options" => $moptions
    );
    $style != "two" ? $style = "two" : $style = "one";
}
$tpl->assign("elemArr", $elemArr);

// Different messages we put in the template
$tpl->assign(
    'msg',
    array(
        "addL" => "main.php?p=" . $p . "&o=a",
        "addT" => _("Add"),
        "addWizard" => _('Add with wizard'),
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
$attrs = array(
    'onchange' => "javascript: " .
        " var bChecked = isChecked(); " .
        " if (this.form.elements['o1'].selectedIndex != 0 && !bChecked) {" .
        " alert('" . _("Please select one or more items") . "'); return false;} " .
        "if (this.form.elements['o1'].selectedIndex == 1 && confirm('"
        . _("Do you confirm the duplication ?") . "')) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"
        . _("Do you confirm the deletion ?") . "')) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        "else if (this.form.elements['o1'].selectedIndex == 3) {" .
        " 	setO(this.form.elements['o1'].value); submit();} " .
        ""
);
$form->addElement(
    'select',
    'o1',
    null,
    array(null => _("More actions..."), "m" => _("Duplicate"), "d" => _("Delete")),
    $attrs
);
$form->setDefaults(array('o1' => null));
$o1 = $form->getElement('o1');
$o1->setValue(null);

$attrs = array(
    'onchange' => "javascript: " .
        " var bChecked = isChecked(); " .
        " if (this.form.elements['o2'].selectedIndex != 0 && !bChecked) {" .
        " alert('" . _("Please select one or more items") . "'); return false;} " .
        "if (this.form.elements['o2'].selectedIndex == 1 && confirm('"
        . _("Do you confirm the duplication ?") . "')) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"
        . _("Do you confirm the deletion ?") . "')) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        "else if (this.form.elements['o2'].selectedIndex == 3) {" .
        " 	setO(this.form.elements['o2'].value); submit();} " .
        ""
);
$form->addElement(
    'select',
    'o2',
    null,
    array(null => _("More actions..."), "m" => _("Duplicate"), "d" => _("Delete")),
    $attrs
);
$form->setDefaults(array('o2' => null));
$o2 = $form->getElement('o2');
$o2->setValue(null);

$tpl->assign('limit', $limit);
$tpl->assign('searchCB', $search);

// Apply a template definition
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listCentreonBroker.ihtml");
