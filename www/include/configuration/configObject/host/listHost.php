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

include_once('./class/centreonUtils.class.php');
require_once('./include/common/autoNumLimit.php');
require_once(_CENTREON_PATH_ . '/www/class/centreonHost.class.php');

/*
 * Init Host Method
 */
$host_method = new CentreonHost($pearDB);

/*
 * Object init
 */
$mediaObj = new CentreonMedia($pearDB);

/*
 * Get Extended informations
 */
$ehiCache = array();
$DBRESULT = $pearDB->query('SELECT ehi_icon_image, host_host_id FROM extended_host_information');

while ($ehi = $DBRESULT->fetch()) {
    $ehiCache[$ehi['host_host_id']] = $ehi['ehi_icon_image'];
}

$DBRESULT->closeCursor();
$mainQueryParameters = [];
$search = null;
$poller = 0;
$hostgroup = 0;
$template = 0;
$status = -1;

if (isset($_POST['SearchB'])) {
    $num = 0;
    $centreon->historySearch[$url] = array();
    $search = $_POST["searchH"];
    $centreon->historySearch[$url]["searchH"] = $search;
    $poller = $_POST["poller"];
    $centreon->historySearch[$url]["poller"] = $poller;
    $hostgroup = $_POST["hostgroup"];
    $centreon->historySearch[$url]["hostgroup"] = $hostgroup;
    $template = $_POST["template"];
    $centreon->historySearch[$url]["template"] = $template;
    $status = $_POST["status"];
    $centreon->historySearch[$url]["status"] = $status;
} elseif (isset($_GET['SearchB'])) {
    $centreon->historySearch[$url] = array();
    $search = $_GET['searchH'];
    $centreon->historySearch[$url]['searchH'] = $search;
    $poller = $_GET["poller"];
    $centreon->historySearch[$url]["poller"] = $poller;
    $hostgroup = $_GET["hostgroup"];
    $centreon->historySearch[$url]["hostgroup"] = $hostgroup;
    $template = $_GET["template"];
    $centreon->historySearch[$url]["template"] = $template;
    $status = $_GET["status"];
    $centreon->historySearch[$url]["status"] = $status;
} else {
    if (isset($centreon->historySearch[$url]['searchH'])) {
        $search = $centreon->historySearch[$url]['searchH'];
    }
    if (isset($centreon->historySearch[$url]["poller"])) {
        $poller = $centreon->historySearch[$url]["poller"];
    }
    if (isset($centreon->historySearch[$url]["hostgroup"])) {
        $hostgroup = $centreon->historySearch[$url]["hostgroup"];
    }
    if (isset($centreon->historySearch[$url]["template"])) {
        $template = $centreon->historySearch[$url]["template"];
    }
    if (isset($centreon->historySearch[$url]["status"])) {
        $status = $centreon->historySearch[$url]["status"];
    }
}

// Security fix
$hostgroup = (int)$hostgroup;
$poller = (int)$poller;
$template = (int)$template;
$status = (int)(($status != '') ? $status : -1);

/*
 * set object history
 */
$centreon->poller = $poller;
$centreon->hostgroup = $hostgroup;
$centreon->template = $template;

/*
 * Status Filter
 */
$statusFilter = "<option value=''" .
    (($status == -1) ? " selected" : "") . "> </option>";

$statusFilter .= "<option value='1'" .
    (($status == 1) ? " selected" : "") . ">" . _("Enabled") . "</option>";

$statusFilter .= "<option value='0'" .
    (($status == 0) ? " selected" : "") . ">" . _("Disabled") . "</option>";

$sqlFilterCase = '';
if ($status == 1) {
    $sqlFilterCase = " AND host_activate = '1' ";
} elseif ($status == 0) {
    $sqlFilterCase = " AND host_activate = '0' ";
}

/*
 * Search active
 */
$searchFilterQuery = '';
if (isset($search) &&
    !empty($search)
) {
    $search = str_replace('_', "\_", $search);
    $mainQueryParameters[':search_string'] = "%{$search}%";
    $searchFilterQuery = '(h.host_name LIKE :search_string
                        OR host_alias LIKE :search_string
                        OR host_address LIKE :search_string) AND ';
}

if ($template) {
    $templateFROM = ', host_template_relation htr ';
    $templateWHERE = " htr.host_host_id = h.host_id "
        . "AND htr.host_tpl_id = '{$template}' AND ";
} else {
    $templateFROM = '';
    $templateWHERE = '';
}
/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

/* Access level */
$lvl_access = ($centreon->user->access->page($p) == 1)
    ? 'w'
    : 'r';

$tpl->assign('mode_access', $lvl_access);

/*
 * start header menu
 */
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_desc", _("Alias"));
$tpl->assign("headerMenu_address", _("IP Address / DNS"));
$tpl->assign("headerMenu_poller", _("Poller"));
$tpl->assign("headerMenu_parent", _("Templates"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_options", _("Options"));

/*
 * Host list
 */
$nagios_server = array();
$DBRESULT = $pearDB->query('SELECT ns.name, ns.id FROM nagios_server ns ' .
    ($aclPollerString != "''" ? $acl->queryBuilder('WHERE', 'ns.id', $aclPollerString) : '') .
    ' ORDER BY ns.name');

while ($relation = $DBRESULT->fetch()) {
    $nagios_server[$relation['id']] = $relation['name'];
}
$DBRESULT->closeCursor();
unset($relation);

$tab_relation = array();
$tab_relation_id = array();
$DBRESULT = $pearDB->query(
    'SELECT nhr.host_host_id, nhr.nagios_server_id FROM ns_host_relation nhr'
);
while ($relation = $DBRESULT->fetchRow()) {
    $tab_relation[$relation['host_host_id']] =
        $nagios_server[$relation['nagios_server_id']];
    
    $tab_relation_id[$relation['host_host_id']] = $relation['nagios_server_id'];
}
$DBRESULT->closeCursor();

/*
 * Init Formulary
 */

$form = new HTML_QuickFormCustom('select_form', 'POST', "?p={$p}");

/*
 * Different style between each lines
 */

$style = 'one';

/*
 * Fill a tab with a mutlidimensionnal Array we put in $tpl
 */

/*
 * Select hosts
 */
$aclFrom = '';
$aclCond = '';
if (!$centreon->user->admin) {
    $aclFrom = ", {$aclDbName}.centreon_acl acl";
    $aclCond =
        ' AND h.host_id = acl.host_id AND acl.service_id IS NULL '
        . 'AND acl.group_id IN (' . $acl->getAccessGroupsString() . ') ';
}

if ($hostgroup) {
    if ($poller) {
        $DBRESULT = $pearDB->query(
            "SELECT SQL_CALC_FOUND_ROWS DISTINCT h.host_id, h.host_name, host_alias,
            host_address, host_activate, host_template_model_htm_id
            FROM host h, ns_host_relation, hostgroup_relation hr $templateFROM $aclFrom
            WHERE $searchFilterQuery $templateWHERE host_register = '1'
            AND h.host_id = ns_host_relation.host_host_id
            AND ns_host_relation.nagios_server_id = '$poller'
            AND h.host_id = hr.host_host_id
            AND hr.hostgroup_hg_id = '$hostgroup' $sqlFilterCase $aclCond
            ORDER BY h.host_name LIMIT " . $num * $limit . ", " . $limit,
            $mainQueryParameters
        );
    } else {
        $DBRESULT = $pearDB->query(
            "SELECT SQL_CALC_FOUND_ROWS DISTINCT h.host_id, h.host_name, host_alias,
            host_address, host_activate, host_template_model_htm_id
            FROM host h, hostgroup_relation hr $templateFROM $aclFrom
            WHERE $searchFilterQuery $templateWHERE host_register = '1'
            AND h.host_id = hr.host_host_id
            AND hr.hostgroup_hg_id = '$hostgroup' $sqlFilterCase $aclCond
            ORDER BY h.host_name LIMIT " . $num * $limit . ", " . $limit,
            $mainQueryParameters
        );
    }
} else {
    if ($poller) {
        $DBRESULT = $pearDB->query(
            "SELECT SQL_CALC_FOUND_ROWS DISTINCT h.host_id, h.host_name, host_alias,
            host_address, host_activate, host_template_model_htm_id
            FROM host h, ns_host_relation $templateFROM $aclFrom
            WHERE $searchFilterQuery $templateWHERE host_register = '1'
            AND h.host_id = ns_host_relation.host_host_id
            AND ns_host_relation.nagios_server_id = '$poller' $sqlFilterCase $aclCond
            ORDER BY h.host_name LIMIT " . $num * $limit . ", " . $limit,
            $mainQueryParameters
        );
    } else {
        $DBRESULT = $pearDB->query(
            "SELECT SQL_CALC_FOUND_ROWS DISTINCT h.host_id, h.host_name, host_alias,
            host_address, host_activate, host_template_model_htm_id
            FROM host h $templateFROM $aclFrom
            WHERE $searchFilterQuery $templateWHERE host_register = '1' $sqlFilterCase $aclCond
            ORDER BY h.host_name LIMIT " . $num * $limit . ", " . $limit,
            $mainQueryParameters
        );
    }
}

$rows = $pearDB->query("SELECT FOUND_ROWS()")->fetchColumn();
include('./include/common/checkPagination.php');

$search = tidySearchKey($search, $advanced_search);

$elemArr = array();
$search = str_replace('\_', "_", $search);
for ($i = 0; $host = $DBRESULT->fetchRow(); $i++) {
    if (!isset($poller) ||
        $poller == 0 ||
        ($poller != 0 && $poller == $tab_relation_id[$host["host_id"]])
    ) {
        $selectedElements = $form->addElement('checkbox', "select[" . $host['host_id'] . "]");

        if ($host["host_activate"]) {
            $moptions = "<a href='main.php?p=$p&host_id={$host['host_id']}"
            . "&o=u&limit=$limit&num=$num&searchH=$search'>"
                . "<img src='img/icons/disabled.png' class='ico-14 margin_right' "
                . "border='0' alt='" . _("Disabled") . "'></a>";
        } else {
            $moptions = "<a href='main.php?p=$p&host_id={$host['host_id']}"
            . "&o=s&limit=$limit&num=$num&searchH=$search'>"
                . "<img src='img/icons/enabled.png' class='ico-14 margin_right' "
                . "border='0' alt='" . _("Enabled") . "'></a>";
        }

        $moptions .= "<input onKeypress=\"if(event.keyCode > 31 && "
            . "(event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; "
            . "if(event.which > 31 && (event.which < 45 || event.which > 57)) "
            . "return false;\" maxlength=\"3\" size=\"3\" value='1' "
            . "style=\"margin-bottom:0px;\" name='dupNbr[{$host['host_id']}]'></input>";

        if (!$host["host_name"]) {
            $host["host_name"] = getMyHostField($host['host_id'], "host_name");
        }

        /*
         * TPL List
         */
        $tplArr = array();
        $tplStr = "";

        /*
         * Create Template topology
         */
        $tplArr = getMyHostMultipleTemplateModels($host['host_id']);
        if (count($tplArr)) {
            $firstTpl = 1;
            foreach ($tplArr as $key => $value) {
                if ($firstTpl) {
                    $tplStr .= "<a href='main.php?p=60103&o=c&host_id=$key'>$value</a>";
                    $firstTpl = 0;
                } else {
                    $tplStr .= "&nbsp;|&nbsp;<a href='main.php?p=60103&o=c&host_id=$key'>$value</a>";
                }
            }
        }

        /*
         * Check icon
         */
        $host_icone = "./img/icons/host.png";
        if (isset($ehiCache[$host["host_id"]]) &&
            $ehiCache[$host["host_id"]]
        ) {
            $host_icone = "./img/media/" . $mediaObj->getFilename($ehiCache[$host["host_id"]]);
        } else {
            $icone = $host_method->replaceMacroInString(
                $host["host_id"],
                getMyHostExtendedInfoImage(
                    $host["host_id"],
                    "ehi_icon_image",
                    1
                )
            );
            if ($icone) {
                $host_icone = "./img/media/" . $icone;
            }
        }

        /*
         * Create Array Data for template list
         */
        $elemArr[$i] = array(
            "MenuClass" => "list_" . $style,
            "RowMenu_select" => $selectedElements->toHtml(),
            "RowMenu_name" => CentreonUtils::escapeSecure($host["host_name"]),
            "RowMenu_id" => $host["host_id"],
            "RowMenu_icone" => $host_icone,
            "RowMenu_link" => "main.php?p=" . $p . "&o=c&host_id=" . $host['host_id'],
            "RowMenu_desc" => CentreonUtils::escapeSecure($host["host_alias"]),
            "RowMenu_address" => CentreonUtils::escapeSecure($host["host_address"]),
            "RowMenu_poller" => isset($tab_relation[$host["host_id"]])
                ? $tab_relation[$host["host_id"]]
                : "",
            "RowMenu_parent" => CentreonUtils::escapeSecure($tplStr),
            "RowMenu_status" => $host["host_activate"] ? _("Enabled") : _("Disabled"),
            "RowMenu_badge" => $host["host_activate"] ? "service_ok" : "service_critical",
            "RowMenu_options" => $moptions
        );
        
        $style != "two"
            ? $style = "two"
            : $style = "one";
    }
}
$tpl->assign("elemArr", $elemArr);

/*
 * Different messages we put in the template
 */
$tpl->assign(
    'msg',
    array(
        "addL" => "main.php?p=" . $p . "&o=a",
        "addT" => _("Add"),
        "delConfirm" => _("Do you confirm the deletion ?")
    )
);

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
            . "else if (this.form.elements['$option'].selectedIndex == 3 || 
                        this.form.elements['$option'].selectedIndex == 4 || 
                        this.form.elements['$option'].selectedIndex == 5 || 
                        this.form.elements['$option'].selectedIndex == 6){"
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
            "mc" => _("Massive Change"),
            "ms" => _("Enable"),
            "mu" => _("Disable"),
            "dp" => _("Deploy Service")
        ),
        $attrs1
    );
    $o1 = $form->getElement($option);
    $o1->setValue(null);
}

$tpl->assign('limit', $limit);

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);

$tpl->assign(
    "search",
    stripslashes(
        str_replace('"', "&quot;", $search)
    )
);

/*
 * create Poller Select
 */
$options = "<option value='0'>" . _("All Pollers") . "</option>";
foreach ($nagios_server as $key => $name) {
    $options .= "<option value='$key' "
        . (($poller == $key) ? 'selected' : "")
        . ">$name</option>";
}

$tpl->assign("poller", $options);
unset($options);

$options = "<option value='0'></options>";
foreach ($hgs as $hgId => $hgName) {
    $options .= "<option value='" . $hgId . "' "
        . (($hostgroup == $hgId) ? 'selected' : "")
        . ">" . $hgName . "</option>";
}

$tpl->assign('hostgroup', $options);
unset($options);

$DBRESULT = $pearDB->query(
    "SELECT host_id, host_name FROM host WHERE host_register = '0' ORDER BY host_name"
);

$options = "<option value='0'></options>";
while ($data = $DBRESULT->fetch()) {
    $options .= "<option value='" . $data["host_id"] . "' "
        . (($template == $data["host_id"]) ? 'selected' : "")
        . ">" . $data["host_name"] . "</option>";
}

$tpl->assign('template', $options);
unset($options);

$tpl->assign('form', $renderer->toArray());
$tpl->assign('Hosts', _("Name"));
$tpl->assign('Poller', _("Poller"));
$tpl->assign('Hostgroup', _("Hostgroup"));
$tpl->assign('HelpServices', _("Display all Services for this host"));
$tpl->assign('Template', _("Template"));
$tpl->assign('Search', _("Search"));

$tpl->assign("StatusFilter", $statusFilter);

$tpl->display("listHost.ihtml");