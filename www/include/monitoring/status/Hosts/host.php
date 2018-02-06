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

$filterParameters = array(
    'sort_types' => FILTER_SANITIZE_STRING,
    'order' => FILTER_SANITIZE_STRING,
    'num' => FILTER_SANITIZE_STRING,
    'host_search' => FILTER_SANITIZE_STRING,
    'sort_type' => FILTER_SANITIZE_STRING,
    'hostgroups' => FILTER_SANITIZE_STRING,
    'criticality_id' => FILTER_SANITIZE_NUMBER_INT,
    'reset_filter' => FILTER_SANITIZE_NUMBER_INT
);

$myinputsGet = filter_input_array(INPUT_GET, $filterParameters);
$myinputsPost = filter_input_array(INPUT_POST, $filterParameters);

$resetFilter = (isset($myinputsGet['reset_filter']) && $myinputsGet['reset_filter'] == 1) ? true : false;

if ($resetFilter) {
    $centreon->historySearch[$url] = '';
    $centreon->historySearchService[$url] = '';
    $centreon->historySearchOutput[$url] = '';
    $_SESSION['filters'][$url] = array();
    $_SESSION['monitoring_default_hostgroups'] = '';
    $_SESSION['monitoring_default_poller'] = '';
    $_SESSION['monitoring_host_status'] = '';
    $_SESSION['monitoring_host_status_filter'] = '';
    $_SESSION['criticality_id'] = '';
}

foreach ($myinputsGet as $key => $value) {
    if (!empty($value)) {
        $filters[$key] = $value;
    } else if (!empty($myinputsPost[$key])) {
        $filters[$key] = $myinputsPost[$key];
    } else if ($resetFilter && isset($_SESSION['filters'][$url][$key]) && !empty($_SESSION['filters'][$url][$key])) {
        $filters[$key] = $_SESSION['filters'][$url][$key];
    } else {
        $filters[$key] = '';
    }
}

if (empty($filters['host_search']) && isset($centreon->historySearch[$url])) {
    $filters['host_search'] = $centreon->historySearch[$url];
} else {
    $centreon->historySearch[$url] = $filters['host_search'];
}

/*
 * ACL Actions
 */
$GroupListofUser = array();
$GroupListofUser = $centreon->user->access->getAccessGroups();

$allActions = false;
/*
 * Get list of actions allowed for user
 */
if (count($GroupListofUser) > 0 && $is_admin == 0) {
    $authorized_actions = array();
    $authorized_actions = $centreon->user->access->getActions();
} else {
    /*
     * if user is admin, or without ACL, he cans perform all actions
     */
    $allActions = true;
}

include("./include/common/autoNumLimit.php");

$sort_types = empty($filters["sort_types"]) ? 0 : $filters["sort_types"];
$order = empty($filters["order"]) ? 'ASC' : $filters["order"];
$num = empty($filters["num"]) ? 0 : $filters["num"];
$search_host = empty($filters["host_search"]) ? "" : $filters["host_search"];
$sort_type = empty($filters["sort_type"]) ? "" : $filters["sort_type"];

if (!empty($filters['hostgroups'])) {
    $_SESSION['monitoring_default_hostgroups'] = $filters['hostgroups'];
}

if (!empty($filters['criticality_id'])) {
    $_SESSION['criticality_id'] = $filters['criticality_id'];
}

$problem_sort_type = 'host_name';
if (!empty($centreon->optGen["problem_sort_type"])) {
    $problem_sort_type = $centreon->optGen["problem_sort_type"];
}
$problem_sort_order = 'asc';
if (!empty($centreon->optGen["problem_sort_type"])) {
    $problem_sort_order = $centreon->optGen["problem_sort_order"];
}
$global_sort_type = 'host_name';
if (!empty($centreon->optGen["global_sort_type"])) {
    $global_sort_type = $centreon->optGen["global_sort_type"];
}
$global_sort_order = 'asc';
if (!empty($centreon->optGen["global_sort_order"])) {
    $global_sort_order = $centreon->optGen["global_sort_order"];
}


if ($o == "hpb" || $o == "h_unhandled" || empty($o)) {
    if (!isset($filters["sort_type"])) {
        $sort_type = $centreon->optGen["problem_sort_type"];
    } else {
        $sort_type = $filters["sort_type"];
    }
    if (!isset($filters["order"])) {
        $order = $centreon->optGen["problem_sort_order"];
    } else {
        $order = $filters["order"];
    }
} else {
    if (!isset($filters["sort_type"])) {
        if (isset($centreon->optGen["global_sort_type"]) && $centreon->optGen["global_sort_type"] != "host_name") {
            $sort_type = CentreonDB::escape($centreon->optGen["global_sort_type"]);
        } else {
            $sort_type = "host_name";
        }
    } else {
        $sort_type = $filters["sort_type"];
    }
    if (!isset($filters["order"])) {
        if (isset($centreon->optGen["global_sort_order"]) && $centreon->optGen["global_sort_order"] == "") {
            $order = "ASC";
        } else {
            $order = $centreon->optGen["global_sort_order"];
        }
    } else {
        $order = $filters["order"];
    }
}

$tab_class = array("0" => "list_one", "1" => "list_two");
$rows = 10;

$aStatusHost = array(
    "h_unhandled" => _("Unhandled Problems"),
    "hpb" => _("Host Problems"),
    "h" => _("All")
);

include_once("./include/monitoring/status/Common/default_poller.php");
include_once("./include/monitoring/status/Common/default_hostgroups.php");

include_once("hostJS.php");

/*
 *  Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl, "/templates/");

$tpl->assign("p", $p);
$tpl->assign('o', $o);
$tpl->assign("sort_type", $sort_type);
$tpl->assign("num", $num);
$tpl->assign("limit", $limit);
$tpl->assign("mon_host", _("Hosts"));
$tpl->assign("mon_status", _("Status"));
$tpl->assign('statusHost', _('Host Status'));
$tpl->assign("mon_ip", _("IP"));
$tpl->assign("mon_tries", _("Tries"));
$tpl->assign("mon_last_check", _("Last Check"));
$tpl->assign("mon_duration", _("Duration"));
$tpl->assign("mon_status_information", _("Status information"));

if (!isset($_GET['o'])) {
    $sSetOrderInMemory = "1";
} else {
    $sSetOrderInMemory = "0";
}

$sDefaultOrder = "0";

$form = new HTML_QuickForm('select_form', 'GET', "?p=" . $p);

$form->addElement('select', 'statusHost', _('Host Status'), $aStatusHost, array('id' => 'statusHost', 'onChange' => "statusHosts(this.value);"));

/* Get default host status by GET */
if (isset($_GET['o']) && in_array($_GET['o'], array_keys($aStatusHost))) {
    $form->setDefaults(array('statusHost' => $_GET['o']));
/* Get default host status in SESSION */
} elseif ((!isset($_GET['o']) || empty($_GET['o'])) && isset($_SESSION['monitoring_host_status'])) {
    $form->setDefaults(array('statusHost' => $_SESSION['monitoring_host_status']));
    $sDefaultOrder = "1";
}

$tpl->assign("order", strtolower($order));
$tab_order = array("sort_asc" => "sort_desc", "sort_desc" => "sort_asc");
$tpl->assign("tab_order", $tab_order);
?>
<script type="text/javascript">
    function setO(_i) {
        document.forms['form'].elements['cmd'].value = _i;
        document.forms['form'].elements['o1'].selectedIndex = 0;
        document.forms['form'].elements['o2'].selectedIndex = 0;
    }
</script>
<?php
$action_list = array();
$action_list[] = _("More actions...");

/*
 * Showing actions allowed for current user
 */
if (isset($authorized_actions) && $allActions == false) {
    if (isset($authorized_actions) && $allActions == false) {
        $action_list[94] = _("Hosts : Schedule immediate check");
    }
    if (isset($authorized_actions["host_schedule_forced_check"])) {
        $action_list[95] = _("Hosts : Schedule immediate check (Forced)");
    }
    if (isset($authorized_actions["host_acknowledgement"])) {
        $action_list[72] = _("Hosts : Acknowledge");
    }
    if (isset($authorized_actions["host_disacknowledgement"])) {
        $action_list[73] = _("Hosts : Disacknowledge");
    }
    if (isset($authorized_actions["host_notifications"])) {
        $action_list[82] = _("Hosts : Enable Notification");
    }
    if (isset($authorized_actions["host_notifications"])) {
        $action_list[83] = _("Hosts : Disable Notification");
    }
    if (isset($authorized_actions["host_checks"])) {
        $action_list[92] = _("Hosts : Enable Check");
    }
    if (isset($authorized_actions["host_checks"])) {
        $action_list[93] = _("Hosts : Disable Check");
    }
    if (isset($authorized_actions["host_schedule_downtime"])) {
        $action_list[75] = _("Hosts : Set Downtime");
    }
} else {
    $action_list[94] = _("Hosts : Schedule immediate check");
    $action_list[95] = _("Hosts : Schedule immediate check (Forced)");
    $action_list[72] = _("Hosts : Acknowledge");
    $action_list[73] = _("Hosts : Disacknowledge");
    $action_list[82] = _("Hosts : Enable Notification");
    $action_list[83] = _("Hosts : Disable Notification");
    $action_list[92] = _("Hosts : Enable Check");
    $action_list[93] = _("Hosts : Disable Check");
    $action_list[75] = _("Hosts : Set Downtime");
}

$attrs = array('onchange' => "javascript: ".
    " var bChecked = isChecked(); ".
    " if (this.form.elements['o1'].selectedIndex != 0 && !bChecked) {".
    " alert('"._("Please select one or more items")."'); return false;} " .
    " if (this.form.elements['o1'].selectedIndex == 0) {".
    " return false;} ".
    "if (cmdCallback(this.value)) { setO(this.value); submit();} else { setO(this.value); }");
$form->addElement('select', 'o1', null, $action_list, $attrs);
$form->setDefaults(array('o1' => null));
$o1 = $form->getElement('o1');
$o1->setValue(null);

$attrs = array('onchange' => "javascript: ".
    " var bChecked = isChecked(); ".
    " if (this.form.elements['o2'].selectedIndex != 0 && !bChecked) {".
    " alert('"._("Please select one or more items")."'); return false;} ".
    " if (this.form.elements['o2'].selectedIndex == 0) {".
    " return false;} ".
    "if (cmdCallback(this.value)) { setO(this.value); submit();} else { setO(this.value); }");
$form->addElement('select', 'o2', null, $action_list, $attrs);
$form->setDefaults(array('o2' => null));
$o2 = $form->getElement('o2');
$o2->setValue(null);
$o2->setSelected(null);

$keyPrefix = "";
$statusList = array("" => "",
    "up" => _("Up"),
    "down" => _("Down"),
    "unreachable" => _("Unreachable"),
    "pending" => _("Pending"));
if ($o == "h") {
    $keyPrefix = "h";
} elseif ($o == "hpb") {
    $keyPrefix = "h";
    unset($statusList["up"]);
} elseif ($o == "h_unhandled") {
    $keyPrefix = "h_unhandled";
    unset($statusList["up"]);
    unset($statusList["pending"]);
} elseif (preg_match("/h_([a-z]+)/", $o, $matches)) {
    if (isset($matches[1])) {
        $keyPrefix = "h";
        $defaultStatus = $matches[1];
    }
}

$form->addElement('select', 'statusFilter', _('Status'), $statusList, array('id' => 'statusFilter', 'onChange' => "filterStatus(this.value);"));
if (!isset($_GET['o']) && isset($_SESSION['monitoring_host_status_filter'])) {
    $form->setDefaults(array('statusFilter' => $_SESSION['monitoring_host_status_filter']));
    $sDefaultOrder = "1";
}

$criticality = new CentreonCriticality($pearDB);
$crits = $criticality->getList();
$critArray = array(0 => "");
foreach ($crits as $critId => $crit) {
    $critArray[$critId] = $crit['hc_name'] . " ({$crit['level']})";
}
$form->addElement('select', 'criticality', _('Severity'), $critArray, array('id' => 'critFilter', 'onChange' => "filterCrit(this.value);"));
$form->setDefaults(array('criticality' => isset($_SESSION['criticality_id']) ? $_SESSION['criticality_id'] : "0"));

$tpl->assign('limit', $limit);
$tpl->assign('hostStr', _('Host'));
$tpl->assign('pollerStr', _('Poller'));
$tpl->assign('poller_listing', $centreon->user->access->checkAction('poller_listing'));
$tpl->assign('hgStr', _('Hostgroup'));
$criticality = new CentreonCriticality($pearDB);
$tpl->assign('criticalityUsed', count($criticality->getList()));
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("host.ihtml");
?>
<script type='text/javascript'>
    var tabSortPb = [];
    tabSortPb['champ'] = '<?php echo $problem_sort_type;?>';
    tabSortPb['ordre'] = '<?php echo $problem_sort_order;?>';

    var tabSortAll = [];
    tabSortAll['champ'] = '<?php echo $global_sort_type;?>'; 
    tabSortAll['ordre'] = '<?php echo $global_sort_order;?>';
   
    var up = '<?php echo _("Up");?>';
    var down = '<?php echo _("Down");?>';
    var unreachable = '<?php echo _("Unreachable");?>';
    var pending = '<?php echo _("Pending");?>';
    
    var _keyPrefix;
    
    jQuery('#statusHost').change(function() {
        updateSelect();
    });
    
    function updateSelect()
    {
        var oldStatus = jQuery('#statusFilter').val();
        var opts = document.getElementById('statusFilter').options;
        if (jQuery('#statusHost').val() == 'hpb' || jQuery('#statusHost').val() == 'h_unhandled') {
            opts.length = 0;
            opts[opts.length] = new Option("", "");
            opts[opts.length] = new Option(down, "down");
            opts[opts.length] = new Option(unreachable, "unreachable");
            change_type_order(tabSortPb['champ']);
        } else {
            opts.length = 0;
            opts[opts.length] = new Option("", "");
            opts[opts.length] = new Option(up, "up");
            opts[opts.length] = new Option(down, "down");
            opts[opts.length] = new Option(unreachable, "unreachable");
            opts[opts.length] = new Option(pending, "pending");
            change_type_order(tabSortAll['champ']);
        }
        if (jQuery("#statusFilter option[value='"+oldStatus+"']").length > 0) {
            jQuery("#statusFilter option[value='"+oldStatus+"']").prop('selected', true);
        }
    }

    jQuery(function () {
        preInit();
    });

    function preInit()
    {
        _keyPrefix = '<?php echo $keyPrefix; ?>';
        _sid = '<?php echo $sid ?>';
        _tm = <?php echo $tM ?>;
        _o = '<?php echo $o; ?>';
        _sDefaultOrder = '<?php echo $sDefaultOrder; ?>';
        sSetOrderInMemory = '<?php echo $sSetOrderInMemory; ?>';
        
        if (_sDefaultOrder == "0") {
            if (_o == 'h') {
                jQuery("#statusHost option[value='h']").prop('selected', true);
                jQuery("#statusFilter option[value='']").prop('selected', true);
            } else if (_o == 'h_up') {
                jQuery("#statusHost option[value='h']").prop('selected', true);
                jQuery("#statusFilter option[value='up']").prop('selected', true);
            } else if (_o == 'h_down') {
                jQuery("#statusHost option[value='h']").prop('selected', true);
                jQuery("#statusFilter option[value='down']").prop('selected', true);
            } else if (_o == 'h_unreachable') {
                jQuery("#statusHost option[value='h']").prop('selected', true);
                jQuery("#statusFilter option[value='unreachable']").prop('selected', true);
            } else if (_o == 'h_pending') {
                jQuery("#statusHost option[value='h']").prop('selected', true);
                jQuery("#statusFilter option[value='pending']").prop('selected', true);
            } else {
                jQuery("#statusHost option[value='h_unhandled']").prop('selected', true);
                jQuery("#statusFilter option[value='']").prop('selected', true);
            }
        }
        filterStatus(document.getElementById('statusFilter').value, 1);

        window.clearTimeout(_timeoutID);
        initM(_tm, _sid, _o);
    }

    function filterStatus(value, isInit)
    {
        _o = jQuery('#statusHost').val();
        if (value) {
            _o = _keyPrefix + '_' + value;
        } else if (!isInit && _o != 'hpb') {
            _o = _keyPrefix;
        }
        window.clearTimeout(_timeoutID);
        initM(_tm, _sid, _o);
    }

    function filterCrit(value) {
        window.clearTimeout(_timeoutID);
        initM(_tm, _sid, _o);
    }
    function statusHosts(value, isInit)
    {
        _o = value;
        window.clearTimeout(_timeoutID);
        initM(_tm, _sid, _o);
    }
</script>
