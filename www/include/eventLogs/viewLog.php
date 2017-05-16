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

/*
 * Pear library
 */
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/select2.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

$user_params = array(
    "log_filter_host" => true,
    "log_filter_svc" => true,
    "log_filter_host_down" => true,
    "log_filter_host_up" => true,
    "log_filter_host_unreachable" => true,
    "log_filter_svc_ok" => true,
    "log_filter_svc_warning" => true,
    "log_filter_svc_critical" => true,
    "log_filter_svc_unknown" => true,
    "log_filter_notif" => false,
    "log_filter_error" => true,
    "log_filter_alert" => true,
    "log_filter_oh" => false,
    "search_H" => "",
    "search_S" => "",
    'log_filter_period' => "",
    'output' => ""
);

/*
 * Add QuickSearch ToolBar
 */
$FlagSearchService = 1;

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl("./include/eventLogs/template", $tpl);

$engine = 'false';
if (isset($_GET["engine"]) && $_GET["engine"] == 'true') {
    $engine = 'true';
}

$output = "";
if (isset($_GET["output"])) {
    $output = $_GET["output"];
}

$openid = '0';
if (isset($_GET["openid"])) {
    $openid = $_GET["openid"];
}

if (isset($_GET["id"])) {
    $id = $_GET["id"];
} else {
    $id = 1;
}

if (isset($_POST["id"])) {
    $id = $_POST["id"];
}

$serviceGrpArray = array();
$pollerArray = array();

$defaultHosts = array();
if (isset($_GET['h'])) {
    $h = explode(",", $_GET['h']);
    $hostObj = new CentreonHost($pearDB);
    $hostArray = $hostObj->getHostsNames($h);
    foreach ($hostArray as $defaultHost) {
        $defaultHosts[$defaultHost['name']] = $defaultHost['id'];
    }
}

$defaultHostgroups = array();
if (isset($_GET['hg'])) {
    $hg = explode(",", $_GET['hg']);
    $hostGrpObj = new CentreonHostgroups($pearDB);
    $hostGrpArray = $hostGrpObj->getHostsgroups($hg);
    foreach ($hostGrpArray as $defaultHostgroup) {
        $defaultHostgroups[$defaultHostgroup['name']] = $defaultHostgroup['id'];
    }
}

$defaultServices = array();
if (isset($_GET['svc'])) {
    $svc = explode(",", $_GET['svc']);
    $serviceObj = new CentreonService($pearDB);
    $serviceArray = $serviceObj->getServicesDescr($svc);
    foreach ($serviceArray as $defaultService) {
        if ($defaultService['host_name'] == '_Module_Meta'
            && preg_match('/^meta_(\d+)/', $defaultService['description'], $matches)
        ) {
            $defaultService['host_name'] = 'Meta';
            $serviceParameters = $serviceObj->getParameters($defaultService['service_id'], array('display_name'));
            $defaultService['description'] = $serviceParameters['display_name'];
        }
        $defaultServices[$defaultService['host_name'] . ' - '
        . $defaultService['description']] = $defaultService['host_id'] . '_' . $defaultService['service_id'];
    }
}

$defaultServicegroups = array();
if (isset($_GET['svcg'])) {
    $svcg = explode(",", $_GET['svcg']);
    $serviceGrpObj = new CentreonServicegroups($pearDB);
    $serviceGrpArray = $serviceGrpObj->getServicesGroups($svcg);
    foreach ($serviceGrpArray as $defaultServicegroup) {
        $defaultServicegroups[$defaultServicegroup['name']] = $defaultServicegroup['id'];
    }
}

$defaultPollers = array();
if (isset($_GET['poller'])) {
    $poller = explode(",", $_GET['poller']);
    $pollerObj = new CentreonInstance($pearDB, $pearDBO);
    $pollerArray = $pollerObj->getInstancesMonitoring($poller);
    foreach ($pollerArray as $defaultPoller) {
        $defaultPollers[$defaultPoller['name']] = $defaultPoller['id'];
    }
}

/*
 * Form begin
 */
$form = new HTML_QuickForm('FormPeriod', 'get', "?p=" . $p);
$form->addElement('header', 'title', _("Choose the source"));

$periods = array(
    "" => "",
    "10800" => _("Last 3 Hours"),
    "21600" => _("Last 6 Hours"),
    "43200" => _("Last 12 Hours"),
    "86400" => _("Last 24 Hours"),
    "172800" => _("Last 2 Days"),
    "302400" => _("Last 4 Days"),
    "604800" => _("Last 7 Days"),
    "1209600" => _("Last 14 Days"),
    "2419200" => _("Last 28 Days"),
    "2592000" => _("Last 30 Days"),
    "2678400" => _("Last 31 Days"),
    "5184000" => _("Last 2 Months"),
    "10368000" => _("Last 4 Months"),
    "15552000" => _("Last 6 Months"),
    "31104000" => _("Last Year")
);

$lang = array(
    "ty" => _("Message Type"),
    "n" => _("Notifications"),
    "a" => _("Alerts"),
    "e" => _("Errors"),
    "s" => _("Status"),
    "do" => _("Down"),
    "up" => _("Up"),
    "un" => _("Unreachable"),
    "w" => _("Warning"),
    "ok" => _("Ok"),
    "cr" => _("Critical"),
    "uk" => _("Unknown"),
    "oh" => _("Hard Only"),
    "sch" => _("Search")
);

$form->addElement('select', 'period', _("Log Period"), $periods);
$form->addElement('text', 'StartDate', '', array("id" => "StartDate", "class" => "datepicker", "size" => 8));
$form->addElement('text', 'StartTime', '', array("id" => "StartTime", "class" => "timepicker", "size" => 5));
$form->addElement('text', 'EndDate', '', array("id" => "EndDate", "class" => "datepicker", "size" => 8));
$form->addElement('text', 'EndTime', '', array("id" => "EndTime", "class" => "timepicker", "size" => 5));
$form->addElement(
    'text',
    'output',
    _("Output"),
    array("id" => "output", "style" => "width: 203px;", "size" => 15, "value" => $user_params['output'])
);

if ($engine == "false") {
    $form->addElement(
        'button',
        'graph',
        _("Apply period"),
        array("onclick" => "apply_period()", "class" => "btc bt_success")
    );
} else {
    $form->addElement(
        'button',
        'graph',
        _("Apply period"),
        array("onclick" => "apply_period_engine()", "class" => "btc bt_success")
    );
}

$hostRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_host&action=list';
$attrHost1 = array(
    'datasourceOrigin' => 'ajax',
    'allowClear' => false,
    'availableDatasetRoute' => $hostRoute,
    'multiple' => true,
    'defaultDataset' => $defaultHosts
);
$form->addElement('select2', 'host_filter', _("Hosts"), array(), $attrHost1);

$serviceGroupRoute = './include/common/webServices/rest/'
    .'internal.php?object=centreon_configuration_servicegroup&action=list';
$attrServicegroup1 = array(
    'datasourceOrigin' => 'ajax',
    'allowClear' => false,
    'availableDatasetRoute' => $serviceGroupRoute,
    'multiple' => true,
    'defaultDataset' => $defaultServicegroups
);
$form->addElement('select2', 'service_group_filter', _("Services Groups"), array(), $attrServicegroup1);

$serviceRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_service&action=list';
$attrService1 = array(
    'datasourceOrigin' => 'ajax',
    'allowClear' => false,
    'availableDatasetRoute' => $serviceRoute,
    'multiple' => true,
    'defaultDataset' => $defaultServices
);
$form->addElement('select2', 'service_filter', _("Services"), array(), $attrService1);

$hostGroupRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup&action=list';
$attrHostGroup1 = array(
    'datasourceOrigin' => 'ajax',
    'allowClear' => false,
    'availableDatasetRoute' => $hostGroupRoute,
    'multiple' => true,
    'defaultDataset' => $defaultHostgroups
);
$form->addElement('select2', 'host_group_filter', _("Hosts Groups"), array(), $attrHostGroup1);

$pollerRoute = './include/common/webServices/rest/internal.php?object=centreon_monitoring_poller&action=list';
$attrPoller1 = array(
    'datasourceOrigin' => 'ajax',
    'allowClear' => false,
    'availableDatasetRoute' => $pollerRoute,
    'multiple' => true,
    'defaultDataset' => $defaultPollers
);
$form->addElement('select2', 'poller_filter', _("Pollers"), array(), $attrPoller1);

$form->setDefaults(array("period" => $user_params['log_filter_period']));

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('user_params', $user_params);
$tpl->assign('lang', $lang);

if ($engine == 'false') {
    $tpl->display("viewLog.ihtml");
} else {
    $tpl->display("viewLogEngine.ihtml");
}

?>
<script language='javascript' src='./include/common/javascript/tool.js'></script>
<script>

function apply_period() {
    var openid = getArgsForHost();
    logs(openid[0],'','');
}

function apply_period_engine() {
    logsEngine();
}

var _limit = 30;
function setL(_this) {
    _limit = _this;
}

var _num = 0;
function log_4_host_page(id, formu, num)	{
    _num = num;
    logs(id, formu, '');
}

function log_4_engine_page(id,formu,num) {
    _num = num;
    logsEngine();
}

var _host = <?php echo !empty($user_params["log_filter_host"]) ? $user_params["log_filter_host"] : 'false'; ?>;
var _service = <?php echo !empty($user_params["log_filter_svc"]) ? $user_params["log_filter_svc"] : 'false'; ?>;
var _engine = <?php echo $engine; ?>;

var _down = <?php echo $user_params["log_filter_host_down"]; ?>;
<?php echo !empty($user_params["log_filter_notif"]) ? $user_params["log_filter_notif"] : 'false'; ?>;
var _up = <?php echo $user_params["log_filter_host_up"]; ?>;
<?php echo !empty($user_params["log_filter_notif"]) ? $user_params["log_filter_notif"] : 'false'; ?>;
var _unreachable = <?php echo $user_params["log_filter_host_unreachable"]; ?>;
<?php echo !empty($user_params["log_filter_notif"]) ? $user_params["log_filter_notif"] : 'false'; ?>;

var _ok = <?php echo $user_params["log_filter_svc_ok"]; ?>;
<?php echo !empty($user_params["log_filter_notif"]) ? $user_params["log_filter_notif"] : 'false'; ?>;
var _warning = <?php echo $user_params["log_filter_svc_warning"]; ?>;
<?php echo !empty($user_params["log_filter_notif"]) ? $user_params["log_filter_notif"] : 'false'; ?>;
var _critical = <?php echo $user_params["log_filter_svc_critical"]; ?>;
<?php echo !empty($user_params["log_filter_notif"]) ? $user_params["log_filter_notif"] : 'false'; ?>;

var _unknown = <?php echo $user_params["log_filter_svc_unknown"]; ?>;
<?php echo !empty($user_params["log_filter_notif"]) ? $user_params["log_filter_notif"] : 'false'; ?>;

<?php $filterNotif = $user_params["log_filter_notif"];?>
var _notification = <?php echo !empty($filterNotif) ? $user_params["log_filter_notif"] : 'false';?>;
var _error = <?php echo !empty($user_params["log_filter_error"]) ? $user_params["log_filter_error"] : 'false'; ?>;
var _alert = <?php echo !empty($user_params["log_filter_alert"]) ? $user_params["log_filter_alert"] : 'false'; ?>;

var _oh = <?php echo !empty($user_params["log_filter_oh"]) ? $user_params["log_filter_oh"] : 'false'; ?>;

var _search_H = "<?php echo $user_params["search_H"]; ?>";
var _search_S = "<?php echo $user_params["search_S"]; ?>";
var _output = "<?php $output; ?>";
// Period
var currentTime = new Date();
var period = '';

var _zero_hour = '';
var _zero_min = '';
var StartDate='';
var EndDate='';
var StartTime='';
var EndTime='';
var opid='';

if (document.FormPeriod && document.FormPeriod.period.value != "")	{
    period = document.FormPeriod.period.value;
}

if (document.FormPeriod && document.FormPeriod.period.value == "") {
    document.FormPeriod.StartDate.value = StartDate;
    document.FormPeriod.EndDate.value = EndDate;
    document.FormPeriod.StartTime.value = StartTime;
    document.FormPeriod.EndTime.value = EndTime;
}

function logsEngine(type) {
    _output = jQuery( "#output" ).val();
    var poller_value = jQuery("#poller_filter").val();
    var args = "";
    var urlargs = "";
    if (poller_value !== null) {
        urlargs += "&poller=";
        var flagfirst = true;
        poller_value.each(function(val) {
            if (val !== " " && val !== "") {
                if (args !== "") {
                    args += ",";
                }
                if (!flagfirst) {
                    urlargs += ",";
                } else {
                    flagfirst = false;
                }
                urlargs += val;
                args += val;
            }
        });
    }
    
    if (window.history.pushState) {
        window.history.pushState("", "", "main.php?p=20302&engine=true"+urlargs);
    }
    
    controlTimePeriod();
    var proc = new Transformation();
    var _addrXSL = "./include/eventLogs/xsl/logEngine.xsl";
    
    if (!type) {
        var _addr = './include/eventLogs/xml/data.php?engine=true&output=' + _output +
            '&error=true&alert=false&ok=false&unreachable=false&down=false&up=false' +
            '&unknown=false&critical=false&warning=false&period=' + period + '&StartDate=' + StartDate +
            '&EndDate=' + EndDate + '&StartTime=' + StartTime + '&EndTime=' + EndTime + '&num=' + _num +
            '&limit=' + _limit + '&id=' + args;
        proc.setXml(_addr)
        proc.setXslt(_addrXSL)
        proc.transform("logView4xml");
    } else {
        if (type == 'CSV') {
            var _addr = './include/eventLogs/export/data.php?engine=true&output=' + _output +
                '&error=true&alert=false&ok=false&unreachable=false&down=false&up=false' +
                '&unknown=false&critical=false&warning=false&period=' + period + '&StartDate=' + StartDate +
                '&EndDate=' + EndDate + '&StartTime=' + StartTime + '&EndTime=' + EndTime + '&num=' + _num +
                '&limit=' + _limit + '&id=' + args + '&export=1';
        } else if (type == 'XML') {
            var _addr = './include/eventLogs/xml/data.php?engine=true&output=' + _output +
                '&error=true&alert=false&ok=false&unreachable=false&down=false&up=false' +
                '&unknown=false&critical=false&warning=false&period=' + period + '&StartDate=' + StartDate +
                '&EndDate=' + EndDate + '&StartTime=' + StartTime + '&EndTime=' + EndTime + '&num=' + _num +
                '&limit=' + _limit + '&id=' + args + '&export=1';
        }
        document.location.href = _addr;
    }

}

function controlTimePeriod() {
    if (document.FormPeriod) {
        if (document.FormPeriod.period.value!="")   {
            period = document.FormPeriod.period.value;
        } else {
            period = '';
            StartDate = document.FormPeriod.StartDate.value;
            EndDate = document.FormPeriod.EndDate.value;
            StartTime = document.FormPeriod.StartTime.value;
            EndTime = document.FormPeriod.EndTime.value;
        }
    }
    if (document.FormPeriod && document.FormPeriod.StartDate.value != "")
        StartDate = document.FormPeriod.StartDate.value;
    if (document.FormPeriod && document.FormPeriod.EndDate.value != "")
        EndDate = document.FormPeriod.EndDate.value;

    if (document.FormPeriod && document.FormPeriod.StartTime.value != "")
        StartTime = document.FormPeriod.StartTime.value;
    if (document.FormPeriod && document.FormPeriod.EndTime.value != "")
        EndTime = document.FormPeriod.EndTime.value;
}

function logs(id, formu, type) {
    opid = id;
    if (jQuery( "#output" ) !== "undefined") {
        _output = jQuery( "#output" ).val();
    }
    
    controlTimePeriod();

    if (document.formu2 && document.formu2.notification) _notification = document.formu2.notification.checked;
    if (document.formu2 && document.formu2.error) _error = document.formu2.error.checked;
    if (document.formu2 && document.formu2.alert) _alert = document.formu2.alert.checked;
    if (document.formu2 && document.formu2.up) _up = document.formu2.up.checked;
    if (document.formu2 && document.formu2.down) _down = document.formu2.down.checked;
    if (document.formu2 && document.formu2.unreachable) _unreachable = document.formu2.unreachable.checked;
    if (document.formu2 && document.formu2.ok) _ok = document.formu2.ok.checked;
    if (document.formu2 && document.formu2.warning) _warning = document.formu2.warning.checked;
    if (document.formu2 && document.formu2.critical) _critical = document.formu2.critical.checked;
    if (document.formu2 && document.formu2.unknown) _unknown = document.formu2.unknown.checked;
    if (document.formu2 && document.formu2.oh) _oh = document.formu2.oh.checked;
    if (document.formu2 && document.formu2.search_H) _search_H = document.formu2.search_H.checked;
    if (document.formu2 && document.formu2.search_S) _search_S = document.formu2.search_S.checked;

    var proc = new Transformation();
    var _addrXSL = "./include/eventLogs/xsl/log.xsl";

    if (!type) {
        var _addr = './include/eventLogs/xml/data.php?output=' + _output + '&oh=' + _oh + '&warning=' + _warning +
            '&unknown=' + _unknown + '&critical=' + _critical + '&ok=' + _ok + '&unreachable=' + _unreachable +
            '&down=' + _down + '&up=' + _up + '&num=' + _num + '&error=' + _error + '&alert=' + _alert +
            '&notification=' + _notification + '&search_H=' + _search_H + '&search_S=' + _search_S +
            '&period=' + period + '&StartDate=' + StartDate + '&EndDate=' + EndDate + '&StartTime=' + StartTime +
            '&EndTime=' + EndTime + '&limit=' + _limit + '&id=' + id +
            '<?php
            if (isset($search) && $search) {
                print "&search_host=" . $search;
            } if (isset($search_service) && $search_service) {
                print "&search_service=" . $search_service;
            } ?>';
        proc.setXml(_addr)
        proc.setXslt(_addrXSL)
        proc.transform("logView4xml");
    } else {
        var openid = document.getElementById('openid').innerHTML;
        if (_engine == 0) {
            if (type == 'CSV') {
                var _addr = './include/eventLogs/export/data.php?output=' + _output + '&oh=' + _oh +
                    '&warning=' + _warning + '&unknown=' + _unknown + '&critical=' + _critical +
                    '&ok=' + _ok + '&unreachable=' + _unreachable + '&down=' + _down + '&up=' + _up + '&num=' + _num +
                    '&error=' + _error + '&alert=' + _alert + '&notification=' + _notification +
                    '&search_H=' + _search_H + '&search_S=' + _search_S + '&period=' + period +
                    '&StartDate=' + StartDate + '&EndDate=' + EndDate + '&StartTime=' + StartTime +
                    '&EndTime=' + EndTime + '&limit=' + _limit + '&id=' + openid +
                    '<?php
                    if (isset($search) && $search) {
                        print "&search_host=" . $search;
                    } if (isset($search_service) && $search_service) {
                        print "&search_service=" . $search_service;
                    } ?>&export=1';
            } else if (type == 'XML') {
                var _addr = './include/eventLogs/xml/data.php?output=' + _output + '&oh=' + _oh +
                    '&warning=' + _warning + '&unknown=' + _unknown + '&critical=' + _critical +
                    '&ok=' + _ok + '&unreachable=' + _unreachable + '&down=' + _down + '&up=' + _up +
                    '&num=' + _num + '&error=' + _error + '&alert=' + _alert + '&notification=' + _notification +
                    '&search_H=' + _search_H + '&search_S=' + _search_S + '&period=' + period +
                    '&StartDate=' + StartDate + '&EndDate=' + EndDate + '&StartTime=' + StartTime +
                    '&EndTime=' + EndTime + '&limit=' + _limit + '&id=' + openid +
                    '<?php
                    if (isset($search) && $search) {
                            print "&search_host=" . $search;
                            print "&search_host=" . $search;
                    } if (isset($search_service) && $search_service) {
                        print "&search_service=" . $search_service;
                    }?>&export=1';
            }
        } else {
            var poller_value = jQuery("#poller_filter").val();
            var args = "";
            if (poller_value !== null) {
                poller_value.each(function(val) {
                    if (val !== " " && val !== "") {
                        if (args !== "") {
                            args += ",";
                        }
                        args += val;
                    }
                });
            }

            if (type == 'CSV') {
                var _addr = './include/eventLogs/export/data.php?engine=true&output=' + _output +
                    '&error=true&alert=false&ok=false&unreachable=false&down=false&up=false' +
                    '&unknown=false&critical=false&warning=false&period=' + period + '&StartDate=' + StartDate +
                    '&EndDate=' + EndDate + '&StartTime=' + StartTime + '&EndTime=' + EndTime +
                    '&num=' + _num + '&limit=' + _limit + '&id=' + args + '&export=1'
            } else if (type == 'XML') {
                var _addr = './include/eventLogs/xml/data.php?engine=true&output=' + _output +
                    '&error=true&alert=false&ok=false&unreachable=false&down=false&up=false' +
                    '&unknown=false&critical=false&warning=false&period=' + period + '&StartDate=' + StartDate +
                    '&EndDate=' + EndDate + '&StartTime=' + StartTime + '&EndTime=' + EndTime +
                    '&num=' + _num + '&limit=' + _limit + '&id=' + args + '&export=1';
            }
        }
        document.location.href = _addr;
    }
}

/**
 * Javascript action depending on the status checkboxes 
 *
 * @param bool isChecked 
 * @return void
 */
function checkStatusCheckbox(isChecked) {
        var alertCb = document.getElementById('alertId');

        if (isChecked == true) {
            alertCb.checked = true;
        }
}

/**
 * Javascript action depending on the alert/notif checkboxes
 *
 * @return void
 */
function checkAlertNotifCheckbox() {
    if (document.getElementById('alertId').checked == false && document.getElementById('notifId').checked == false) {
        document.getElementById('cb_up').checked = false;
        document.getElementById('cb_down').checked = false;
        document.getElementById('cb_unreachable').checked = false;
        document.getElementById('cb_ok').checked = false;
        document.getElementById('cb_warning').checked = false;
        document.getElementById('cb_critical').checked = false;
        document.getElementById('cb_unknown').checked = false;
    }
}

function getArgsForHost() {
    var host_value = jQuery("#host_filter").val();
    var service_value = jQuery("#service_filter").val();
    var hg_value = jQuery("#host_group_filter").val();
    var sg_value = jQuery("#service_group_filter").val();
    
    var args = "";
    var urlargs = "";
     if (host_value !== null) {
         urlargs += "&h=";
         var flagfirst = true;
         host_value.each(function(val) {
            if (val !== " " && val !== "") {
                if (args !== "") {
                     args += ",";
                 }
                 if (!flagfirst) {
                     urlargs += ",";
                 } else {
                     flagfirst = false;
                 }
                 urlargs += val;
                 args += "HH_" + val;
             }
         });
     }
     if (service_value !== null) {
         urlargs += "&svc=";
         var flagfirst = true;
         service_value.each(function(val) {
             if (val !== " " && val !== "") {
                 if (args !== "") {
                     args += ",";
                 }
                 if (!flagfirst) {
                     urlargs += ",";
                 } else {
                     flagfirst = false;
                 }
                 urlargs += val.replace("-","_");
                 args += "HS_" + val.replace("-","_");
             }
         });
     }
     if (hg_value !== null) {
         urlargs += "&hg=";
         var flagfirst = true;
         hg_value.each(function(val) {
             if (val !== " " && val !== "") {
                 if (args !== "") {
                     args += ",";
                 }
                 if (!flagfirst) {
                     urlargs += ",";
                 } else {
                     flagfirst = false;
                 }
                 urlargs += val;
                 args += "HG_" + val;
             }
         });
     }
     if (sg_value !== null) {
         urlargs += "&svcg=";
         var flagfirst = true;
         sg_value.each(function(val) {
             if (val !== " " && val !== "") {
                 if (args !== "") {
                     args += ",";
                 }
                 if (!flagfirst) {
                     urlargs += ",";
                 } else {
                     flagfirst = false;
                 }
                 urlargs += val;
                 args += "SG_" + val;
             }
         });
     }
    return new Array(args,urlargs);
}

jQuery(function () {
    if (_engine == 0) {
        // Here is your precious function
        // You can call as many functions as you want here;

        jQuery("#service_group_filter, #host_filter, #service_filter, #host_group_filter").change(
            function (event, infos) {
                var argArray = getArgsForHost();
                args = argArray[0];
                urlargs = argArray[1];
                if (typeof infos !== "undefined" && infos.origin === "select2defaultinit") {
                    return false;
                }
                if (window.history.pushState) {
                    window.history.pushState("", "", "main.php?p=20301" + urlargs);
                }
                document.getElementById('openid').innerHTML = args;
                logs(args, '', false);
            });

        //setServiceGroup
        jQuery("#setHostGroup").click(function() {
            var hg_value = jQuery("#host_group_filter").val();
            var host_value = jQuery("#host_filter").val();
            if (host_value === null) {
                host_value = new Array();
            }
            jQuery.ajax({
                url: "./api/internal.php?object=centreon_configuration_hostgroup&action=hostList",
                type: "GET",
                dataType : "json",
                data: "hgid="+hg_value,
                success : function(json) {
                    json.items.each(function(elem) {
                        if (jQuery.inArray(elem.id,host_value) === -1) {
                            var existingOptions = jQuery("#host_filter").find('option');
                            var existFlag = false;
                            existingOptions.each(function(el) {
                                if (jQuery(this).val() == elem.id) {
                                    existFlag = true;
                                }
                            });
                            if (!existFlag) {
                                jQuery("#host_filter").append(jQuery('<option>').val(elem.id).html(elem.text));
                            }
                            host_value.push(elem.id);
                        }    
                    });
                    jQuery("#host_filter").val(host_value).trigger("change",[{origin:"select2defaultinit"}]);
                    jQuery("#host_group_filter").val('');
                    jQuery("#host_group_filter").empty().append(jQuery('<option>'));
                    jQuery("#host_group_filter").trigger("change",[{origin:"select2defaultinit"}]);
                }
            });    

        });

        jQuery("#setServiceGroup").click(function() {
           var service_value = jQuery("#service_filter").val();
           var sg_value = jQuery("#service_group_filter").val();
            if (service_value === null) {
                service_value = new Array();
            }
            jQuery.ajax({
                url: "./api/internal.php?object=centreon_configuration_servicegroup&action=serviceList",
                type: "GET",
                dataType : "json",
                data: "sgid="+sg_value,
                success : function(json) {
                    json.items.each(function(elem) {
                        if (jQuery.inArray(elem.id,service_value) === -1) {
                            var existingOptions = jQuery("#service_filter option");
                            var existFlag = false;
                            existingOptions.each(function() {
                                if (jQuery(this).val() == elem.id) {
                                    existFlag = true;
                                }
                            });
                            if (!existFlag) {
                                jQuery("#service_filter").append(jQuery('<option>').val(elem.id).html(elem.text));
                            }
                            service_value.push(elem.id);
                        }    
                    });
                    jQuery("#service_filter").val(service_value).trigger("change",[{origin:"select2defaultinit"}]);
                    jQuery("#service_group_filter").val('');
                    jQuery("#service_group_filter").empty().append(jQuery('<option>'));
                    jQuery("#service_group_filter").trigger("change",[{origin:"select2defaultinit"}]);
                }
            });    
        });

        jQuery( "#output" ).keypress(function(  event ) {
            if ( event.which == 13 ) {
                var argArray = getArgsForHost();
                args = argArray[0];
                urlargs = argArray[1];
                logs(args, '', false);
               event.preventDefault();
            }
        });
        
    } else {
        jQuery("#poller_filter").change(function(event,infos) {
            if (typeof infos !== "undefined" && infos.origin === "select2defaultinit") {
                return false;
            }
            logsEngine();
        });
        jQuery( "#output" ).keypress(function(  event ) {
            if ( event.which == 13 ) {
                logsEngine();
                event.preventDefault();
            }
        });
    }
    });
</script>
