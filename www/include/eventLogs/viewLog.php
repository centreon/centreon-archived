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

function get_user_param($user_id, $pearDB) {
	$list_param = array('log_filter_host', 'log_filter_svc', 'log_filter_host_down',
		'log_filter_host_up', 'log_filter_host_unreachable', 'log_filter_svc_ok',
		'log_filter_svc_warning', 'log_filter_svc_critical', 'log_filter_svc_unknown',
		'log_filter_notif', 'log_filter_error', 'log_filter_alert', 'log_filter_oh',
		'search_H', 'search_S', 'log_filter_period');
	$tab_row = array();
	$cache = null;
	foreach ($list_param as $param) {
		if (isset($_SESSION[$param])) {
			$tab_row[$param] = $_SESSION[$param];
		} else {
			if (is_null($cache)) {
				$cache = array();
				$query = "SELECT cp_key, cp_value FROM contact_param WHERE cp_key in ('" . join("', '", $list_param) . "') AND cp_contact_id = " . $user_id;
				$DBRESULT = $pearDB->query($query);
				while ($row = $DBRESULT->fetchRow()) {
					$cache[$row['cp_key']] = $row['cp_value'];
				}
			}
			if (isset($cache[$param])) {
				$tab_row[$param] = $cache[$param];
			}
		}
	}
	return $tab_row;
}

$user_params = get_user_param($oreon->user->user_id, $pearDB);

if (!isset($user_params["log_filter_host"]))
	$user_params["log_filter_host"] = 1;
if (!isset($user_params["log_filter_svc"]))
	$user_params["log_filter_svc"] = 1;
if (!isset($user_params["log_filter_host_down"]))
	$user_params["log_filter_host_down"] = 1;
if (!isset($user_params["log_filter_host_up"]))
	$user_params["log_filter_host_up"] = 1;
if (!isset($user_params["log_filter_host_unreachable"]))
	$user_params["log_filter_host_unreachable"] = 1;
if (!isset($user_params["log_filter_svc_ok"]))
	$user_params["log_filter_svc_ok"] = 1;
if (!isset($user_params["log_filter_svc_warning"]))
	$user_params["log_filter_svc_warning"] = 1;
if (!isset($user_params["log_filter_svc_critical"]))
	$user_params["log_filter_svc_critical"] = 1;
if (!isset($user_params["log_filter_svc_unknown"]))
	$user_params["log_filter_svc_unknown"] = 1;
if (!isset($user_params["log_filter_notif"]))
	$user_params["log_filter_notif"] = 1;
if (!isset($user_params["log_filter_error"]))
	$user_params["log_filter_error"] = 1;
if (!isset($user_params["log_filter_alert"]))
	$user_params["log_filter_alert"] = 1;
if (!isset($user_params["log_filter_oh"]))
	$user_params["log_filter_oh"] = 1;

if (!isset($user_params["search_H"]))
	$user_params["search_H"] = "";
if (!isset($user_params["search_S"]))
	$user_params["search_S"] = "";

if (!isset($user_params['log_filter_period']))
	$user_params['log_filter_period'] = "";

if (!isset($user_params['output']))
	$user_params['output'] = "";
/*
 * Pear library
 */
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/select2.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

/*
 * Add QuickSearch ToolBar
 */
$FlagSearchService = 1;

/*
 * Path to the configuration dir
 */
$path = "./include/eventLogs/";

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

if(isset($_GET["engine"])){
    if($_GET["engine"] == "true"){
        $engine = 'true';
    }else{
        $engine = 'false';
    }
}else{
    $engine = 'false';
}

$output = "";
if(isset($_GET["output"])){
    $output = $_GET["output"];
}



$openid = '0';
$open_id_sub = '0';
if (isset($_GET["openid"])){
	$openid = $_GET["openid"];
	$open_id_type = substr($openid, 0, 2);
	$open_id_sub = substr($openid, 3, strlen($openid));
}

if (isset($_GET["id"])){
	$id = $_GET["id"];
} else {
	$id = 1;
}

if (isset($_POST["id"])){
	$id = $_POST["id"];
}

$hostArray = array();
$hostGrpArray = array();
$serviceArray = array();
$serviceGrpArray = array();
$pollerArray = array();
if(isset($_GET['h'])){
    $h = explode(",",$_GET['h']);
    $hostObj = new CentreonHost($pearDB);
    $hostArray = $hostObj->getHostsNames($h);
}
if(isset($_GET['hg'])){
    $hg = explode(",",$_GET['hg']);
    $hostGrpObj = new CentreonHostgroups($pearDB);
    $hostGrpArray = $hostGrpObj->getHostsgroups($hg);
}
if(isset($_GET['svc'])){
    $svc = explode(",",$_GET['svc']);
    $serviceObj = new CentreonService($pearDB);
    $serviceArray = $serviceObj->getServicesDescr($svc);
}
if(isset($_GET['svcg'])){
    $svcg = explode(",",$_GET['svcg']);
    $serviceGrpObj = new CentreonServicegroups($pearDB);
    $serviceGrpArray = $serviceGrpObj->getSerivcesGroups($svcg);
}
if(isset($_GET['poller'])){
    $poller = explode(",",$_GET['poller']);
    $pollerObj = new CentreonInstance($pearDB,$pearDBO);
    $pollerArray = $pollerObj->getInstancesMonitoring($poller);
}



/*
 * From Monitoring
 */
if (isset($_POST["svc_id"])) {
	$id = "";
    $services = preg_split("/\,/", $_POST["svc_id"]);
	foreach ($services as $str) {
		$buf_svc = preg_split("/\;/", urldecode($str));
        $lhost_id = getMyHostID($buf_svc[0]);
		$id .= "HS_" . getMyServiceID($buf_svc[1], $lhost_id)."_" . $lhost_id . ",";
	}
}

/*
 * From Graphs
 */
if (!strncmp("MS", $id, 2)) {
	$meta = 0;
	if (isset($id) && $id){
		$id = "";
		$id_svc = $id;
		$tab_svcs = explode(",", $id_svc);
		foreach ($tab_svcs as $svc){
			$tmp = explode(";", urldecode($svc));
             $lhost_id = getMyHostID($tmp[0]);
			$id .= "HS_" . getMyServiceID($tmp[1], $lhost_id)."_" . $lhost_id . ",";
		}
	}
} else {
	$meta = 1;
}

$id_log = "'RR_0'";
$multi = 0;
$lockTree = 0;
    $focusUrl = "";
if (isset($_GET["mode"]) && $_GET["mode"] == "0"){
	$mode = 0;
	$lockTree = 1;
	$id_log = "'".$id."'";
	$multi =1;
	$focusUrl = "?p=$p&id=$id&mode=0&lock_tree=0";
} else {
	$mode = 1;
	$id = 1;
}

/*
 * Form begin
 */
$form = new HTML_QuickForm('FormPeriod', 'get', "?p=".$p);
$form->addElement('header', 'title', _("Choose the source"));

$periods = array(	""=>"",
					"10800"=>_("Last 3 Hours"),
					"21600"=>_("Last 6 Hours"),
					"43200"=>_("Last 12 Hours"),
					"86400"=>_("Last 24 Hours"),
					"172800"=>_("Last 2 Days"),
					"302400"=>_("Last 4 Days"),
					"604800"=>_("Last 7 Days"),
					"1209600"=>_("Last 14 Days"),
					"2419200"=>_("Last 28 Days"),
					"2592000"=>_("Last 30 Days"),
					"2678400"=>_("Last 31 Days"),
					"5184000"=>_("Last 2 Months"),
					"10368000"=>_("Last 4 Months"),
					"15552000"=>_("Last 6 Months"),
					"31104000"=>_("Last Year"));

$lang = array("ty" => _("Message Type"),
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
$form->addElement('text', 'StartDate', '', array("id"=>"StartDate", "class" => "datepicker", "size"=>8));
$form->addElement('text', 'StartTime', '', array("id"=>"StartTime", "class"=>"timepicker", "size"=>5));
$form->addElement('text', 'EndDate', '', array("id"=>"EndDate", "class" => "datepicker", "size"=>8));
$form->addElement('text', 'EndTime', '', array("id"=>"EndTime", "class"=>"timepicker", "size"=>5));
$form->addElement('text', 'output', _("Output"),  array("id"=>"output", "style"=>"width: 203px;", "size"=>15, "value" => $user_params['output']));

if($engine == "false"){
    $form->addElement('button', 'graph', _("Apply"), array("onclick"=>"apply_period()","class"=>"btc bt_success"));
}else{
    $form->addElement('button', 'graph', _("Apply"), array("onclick"=>"apply_period_engine()","class"=>"btc bt_success"));
}


$attrHosts = array(
    'datasourceOrigin' => 'ajax',
    'allowClear' => false,
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_host&action=list',
    'multiple' => true
);
/* Host Parents */
$attrHost1 = array_merge(
    $attrHosts,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_host&action=defaultValues&target=host&field=host_parents&id=')
);
$form->addElement('select2', 'host_filter', _("Hosts"), array(), $attrHost1);


$attrServicegroups = array(
    'datasourceOrigin' => 'ajax',
    'allowClear' => false,
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_servicegroup&action=list',
    'multiple' => true
);

$attrServicegroup1 = array_merge(
    $attrServicegroups,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_servicegroup&action=defaultValues&target=service&field=service_sgs&id=')
);
$form->addElement('select2', 'service_group_filter', _("Services Groups"), array(), $attrServicegroup1);



$attrService = array(
    'datasourceOrigin' => 'ajax',
    'allowClear' => false,
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_service&action=list',
    'multiple' => true
);

$attrService1 = array_merge(
    $attrService,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_service&action=defaultValues&target=service&field=service_sgs&id=')
);
$form->addElement('select2', 'service_filter', _("Services"), array(), $attrService1);



$attrHostGroup = array(
    'datasourceOrigin' => 'ajax',
    'allowClear' => false,
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup&action=list',
    'multiple' => true
);
/* Host Parents */
$attrHostGroup1 = array_merge(
    $attrHostGroup,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup&action=defaultValues&target=host&field=host_parents&id=')
);
$form->addElement('select2', 'host_group_filter', _("Hosts Groups"), array(), $attrHostGroup1);

$attrPoller = array(
    'datasourceOrigin' => 'ajax',
    'allowClear' => false,
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_monitoring_poller&action=list',
    'multiple' => true
);
/* Host Parents */
$attrPoller1 = array_merge(
    $attrPoller,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_monitoring_poller&action=defaultValues&target=host&field=host_parents&id=')
);
$form->addElement('select2', 'poller_filter', _("Pollers"), array(), $attrPoller1);







$form->setDefaults(array("period" => $user_params['log_filter_period']));

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('From', _("From"));
$tpl->assign('To', _("To"));
$tpl->assign('periodORlabel', _("or"));
$tpl->assign('focusUrl', $focusUrl);
$tpl->assign('treeFocus', _('Tree Focus'));
$tpl->assign('user_params', $user_params);
$tpl->assign('lang', $lang);

if($engine == 'false'){
    $tpl->display("viewLog.ihtml");
}else{
    $tpl->display("viewLogEngine.ihtml");
}


?>
<script language='javascript' src='./include/common/javascript/tool.js'></script>
<script>

	var css_file = './include/common/javascript/codebase/dhtmlxtree.css';
	var headID = document.getElementsByTagName("head")[0];
	var cssNode = document.createElement('link');
	cssNode.type = 'text/css';
	cssNode.rel = 'stylesheet';
	cssNode.href = css_file;
	cssNode.media = 'screen';
	headID.appendChild(cssNode);

 	var multi = <?php echo $multi; ?>;



	// it's fake methode for using ajax system by default
	function mk_pagination(){;}
	function mk_paginationFF(){;}
	function set_header_title(){;}

	function apply_period(){
		var openid = document.getElementById('openid').innerHTML;
		log_4_host(openid,'','');
	}


    function apply_period_engine(){
		var openid = document.getElementById('openid').innerHTML;
		log_4_engine(openid,'','');
	}

    var _limit = 30;
    function setL(_this){
        _limit = _this;
    }

	var _num = 0;
	function log_4_host_page(id, formu, num)	{
		_num = num;
		log_4_host(id, formu, '');
	}
    
    function log_4_engine_page(id,formu,num){
		_num = num;
		log_4_engine(id, formu);
    }

	var _host 		= <?php echo $user_params["log_filter_host"]; ?>;
	var _service 	= <?php echo $user_params["log_filter_svc"]; ?>;
    var _engine     = <?php echo $engine; ?>;

	var _down 		= <?php echo $user_params["log_filter_host_down"]; ?>;
	var _up 		= <?php echo $user_params["log_filter_host_up"]; ?>;
	var _unreachable = <?php echo $user_params["log_filter_host_unreachable"]; ?>;

	var _ok 		= <?php echo $user_params["log_filter_svc_ok"]; ?>;
	var _warning 	= <?php echo $user_params["log_filter_svc_warning"]; ?>;
	var _critical 	= <?php echo $user_params["log_filter_svc_critical"]; ?>;
	var _unknown 	= <?php echo $user_params["log_filter_svc_unknown"]; ?>;

	var _notification = <?php echo $user_params["log_filter_notif"]; ?>;
	var _error 		= <?php echo $user_params["log_filter_error"]; ?>;
	var _alert 		= <?php echo $user_params["log_filter_alert"]; ?>;

	var _oh 		= <?php echo $user_params["log_filter_oh"]; ?>;

	var _search_H	= "<?php echo $user_params["search_H"]; ?>";
	var _search_S	= "<?php echo $user_params["search_S"]; ?>";
    var _output     = "<?php $output; ?>";
	// Period
	var currentTime = new Date();
	var period ='';

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

	if (document.FormPeriod && document.FormPeriod.period.value == ""){
		document.FormPeriod.StartDate.value = StartDate;
		document.FormPeriod.EndDate.value = EndDate;
		document.FormPeriod.StartTime.value = StartTime;
		document.FormPeriod.EndTime.value = EndTime;
	}
    
    function log_4_engine(){
        _output = jQuery( "#output" ).val();
        var poller_value = jQuery("#poller_filter").val();
        var args = "";
        var urlargs = "";
        if(poller_value !== null){
            urlargs += "&poller=";
            var flagfirst = true;
            poller_value.each(function(val){
                if(val !== " " && val !== ""){
                    if(args !== ""){
                        args += ",";
                    }
                    if(!flagfirst){
                        urlargs += ",";
                    }else{
                        flagfirst = false;
                    }
                    urlargs += val;
                    args += val;
                }
            });
        }
        
        if (window.history.pushState) {
            window.history.pushState("", "", "/centreon/main.php?p=203&engine=true"+urlargs);
        }
        
        
        controlTimePeriod();
        var proc = new Transformation();
		var _addrXSL = "./include/eventLogs/logEngine.xsl";
        var _addr = './include/eventLogs/GetXmlLog.php?engine=true&output='+_output+'&error=true&alert=false&ok=false&unreachable=false&down=false&up=false'+
                    '&unknown=false&critical=false&warning=false&period='+period+'&StartDate='+StartDate+'&EndDate='+EndDate+'&StartTime='+StartTime+'&EndTime='+EndTime+'&num='+_num+'&limit='+_limit+'&id='+args;
        proc.setXml(_addr)
        proc.setXslt(_addrXSL)
        proc.transform("logView4xml");
    }


    function controlTimePeriod(){
        if (document.FormPeriod) {
		    if (document.FormPeriod.period.value!="")	{
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

	function log_4_host(id, formu, type){
        opid = id;
        if(jQuery( "#output" ) !== "undefined"){
            _output = jQuery( "#output" ).val();
        }
        
        
        controlTimePeriod();
		// type
		if (document.formu2 && document.formu2.notification)
			_notification = document.formu2.notification.checked;
		if (document.formu2 && document.formu2.error)
			_error = document.formu2.error.checked;
		if (document.formu2 && document.formu2.alert)
			_alert = document.formu2.alert.checked;

		if (document.formu2 && document.formu2.up)
			_up = document.formu2.up.checked;
		if (document.formu2 && document.formu2.down)
			_down = document.formu2.down.checked;
		if (document.formu2 && document.formu2.unreachable)
			_unreachable = document.formu2.unreachable.checked;

		if (document.formu2 && document.formu2.ok)
			_ok = document.formu2.ok.checked;
		if (document.formu2 && document.formu2.warning)
			_warning = document.formu2.warning.checked;
		if (document.formu2 && document.formu2.critical)
			_critical = document.formu2.critical.checked;
		if (document.formu2 && document.formu2.unknown)
			_unknown = document.formu2.unknown.checked;

		if (document.formu2 && document.formu2.oh)
			_oh = document.formu2.oh.checked;

		if (document.formu2 && document.formu2.search_H)
			_search_H = document.formu2.search_H.checked;
		if (document.formu2 && document.formu2.search_S)
			_search_S = document.formu2.search_S.checked;

		//tree.selectItem(id);

		var proc = new Transformation();
		var _addrXSL = "./include/eventLogs/log.xsl";

		if (!type){
			var _addr = './include/eventLogs/GetXmlLog.php?multi='+multi+'&output='+_output+'&oh='+_oh+'&warning='+_warning+'&unknown='+_unknown+'&critical='+_critical+'&ok='+_ok+'&unreachable='+_unreachable+'&down='+_down+'&up='+_up+'&num='+_num+'&error='+_error+'&alert='+_alert+'&notification='+_notification+'&search_H='+_search_H+'&search_S='+_search_S+'&period='+period+'&StartDate='+StartDate+'&EndDate='+EndDate+'&StartTime='+StartTime+'&EndTime='+EndTime+'&limit='+_limit+'&id='+id+'<?php if (isset($search) && $search) print "&search_host=".$search; if (isset($search_service) && $search_service) print "&search_service=".$search_service; ?>';
			proc.setXml(_addr)
			proc.setXslt(_addrXSL)
			proc.transform("logView4xml");
		} else {
			var openid = document.getElementById('openid').innerHTML;
			var _addr = './include/eventLogs/Get'+type+'Log.php?multi='+multi+'&output='+_output+'&oh='+_oh+'&warning='+_warning+'&unknown='+_unknown+'&critical='+_critical+'&ok='+_ok+'&unreachable='+_unreachable+'&down='+_down+'&up='+_up+'&num='+_num+'&error='+_error+'&alert='+_alert+'&notification='+_notification+'&search_H='+_search_H+'&search_S='+_search_S+'&period='+period+'&StartDate='+StartDate+'&EndDate='+EndDate+'&StartTime='+StartTime+'&EndTime='+EndTime+'&limit='+_limit+'&id='+openid+'<?php if (isset($search) && $search) print "&search_host=".$search; if (isset($search_service) && $search_service) print "&search_service=".$search_service; ?>&export=1';
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
                if (document.getElementById('alertId').checked == false && 
                    document.getElementById('notifId').checked == false) {
                        document.getElementById('cb_up').checked = false;
                        document.getElementById('cb_down').checked = false;
                        document.getElementById('cb_unreachable').checked = false;
                        document.getElementById('cb_ok').checked = false;
                        document.getElementById('cb_warning').checked = false;
                        document.getElementById('cb_critical').checked = false;
                        document.getElementById('cb_unknown').checked = false;
                }
        }

        function getArgsForHost(){
            var host_value = jQuery("#host_filter").val();
            var service_value = jQuery("#service_filter").val();
            var hg_value = jQuery("#host_group_filter").val();
            var sg_value = jQuery("#service_group_filter").val();
            
            var args = "";
            var urlargs = "";
             if(host_value !== null){
                 urlargs += "&h=";
                 var flagfirst = true;
                 host_value.each(function(val){
                     if(val !== " " && val !== ""){
                         if(args !== ""){
                             args += ",";
                         }
                         if(!flagfirst){
                             urlargs += ",";
                         }else{
                             flagfirst = false;
                         }
                         urlargs += val;
                         args += "HH_" + val;
                     }
                 });
             }
             if(service_value !== null){
                 urlargs += "&svc=";
                 var flagfirst = true;
                 service_value.each(function(val){
                     if(val !== " " && val !== ""){
                         if(args !== ""){
                             args += ",";
                         }
                         if(!flagfirst){
                             urlargs += ",";
                         }else{
                             flagfirst = false;
                         }
                         urlargs += val.replace("-","_");
                         args += "HS_" + val;
                     }
                 });
             }
             if(hg_value !== null){
                 urlargs += "&hg=";
                 var flagfirst = true;
                 hg_value.each(function(val){
                     if(val !== " " && val !== ""){
                         if(args !== ""){
                             args += ",";
                         }
                         if(!flagfirst){
                             urlargs += ",";
                         }else{
                             flagfirst = false;
                         }
                         urlargs += val;
                         args += "HG_" + val;
                     }
                 });
             }
             if(sg_value !== null){
                 urlargs += "&svcg=";
                 var flagfirst = true;
                 sg_value.each(function(val){
                     if(val !== " " && val !== ""){
                         if(args !== ""){
                             args += ",";
                         }
                         if(!flagfirst){
                             urlargs += ",";
                         }else{
                             flagfirst = false;
                         }
                         urlargs += val;
                         args += "ST_" + val;
                     }
                 });
             }

            return new Array(args,urlargs);
        }


	jQuery(function () {
        if(!_engine){
            /// Initialise selection with Get params
            arrayHostValues = new Array();
            <?php 
                foreach($hostArray as $host){
                    ?>
                    arrayHostValues.push(<?php echo $host['id']; ?>);
                    jQuery("#host_filter").append(jQuery('<option>').val(<?php echo $host['id']; ?> ).html('<?php echo $host['name']; ?>'));        

                    <?php         
                }
            ?>        


            arrayServicesValues = new Array();
            <?php 
                foreach($serviceArray as $service){
                    ?>
                    arrayServicesValues.push('<?php echo $service['host_id'].'_'.$service['service_id']; ?>');
                    jQuery("#service_filter").append(jQuery('<option>').val('<?php echo $service['host_id'].'_'.$service['service_id']; ?>').html('<?php echo $service['host_name']. ' - ' .$service['description']; ?>'));        

                    <?php         
                }
            ?>
                   


            arrayServicesGrpValues = new Array();
            <?php 
                foreach($serviceGrpArray as $serviceGrp){
                    ?>
                    arrayServicesGrpValues.push('<?php echo $serviceGrp['id']; ?>');
                    jQuery("#service_group_filter").append(jQuery('<option>').val('<?php echo $serviceGrp['id']; ?>').html('<?php echo $serviceGrp['name']; ?>'));        

                    <?php         
                }
            ?>
            

            arrayHostsGrpValues = new Array();
            <?php 
                foreach($hostGrpArray as $hostGrp){
                    ?>
                    arrayHostsGrpValues.push('<?php echo $hostGrp['id']; ?>');
                    jQuery("#host_group_filter").append(jQuery('<option>').val('<?php echo $hostGrp['id']; ?>').html('<?php echo $hostGrp['name']; ?>'));        

                    <?php         
                }
            ?>

            
            
            
            // Here is your precious function
            // You can call as many functions as you want here;
            jQuery("#service_group_filter, #host_filter, #service_filter, #host_group_filter").change(function(event,infos){

               var argArray = getArgsForHost();
               args = argArray[0];
               urlargs = argArray[1];
               if(typeof infos !== "undefined" && infos.origin === "select2defaultinit"){
                return false;
               }

               if (window.history.pushState) {
                   window.history.pushState("", "", "/centreon/main.php?p=203"+urlargs);
               }
               document.getElementById('openid').innerHTML = args;
               log_4_host(args, '', false);
            });
            //setServiceGroup
            jQuery("#setHostGroup").click(function(){
                var hg_value = jQuery("#host_group_filter").val();
                var host_value = jQuery("#host_filter").val();
                if(host_value === null){
                    host_value = new Array();
                }
                jQuery.ajax({
                    url: "./include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup&action=hostList",
                    type: "GET",
                    dataType : "json",
                    data: "hgid="+hg_value,
                    success : function(json){
                        json.items.each(function(elem){
                            if(jQuery.inArray(elem.id,host_value) === -1){
                                var existingOptions = jQuery("#host_filter").find('option');
                                var existFlag = false;
                                existingOptions.each(function(el){
                                    if(jQuery(this).val() == elem.id){
                                        existFlag = true;
                                    }
                                });
                                if(!existFlag){
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

            jQuery("#setServiceGroup").click(function(){
               var service_value = jQuery("#service_filter").val();
               var sg_value = jQuery("#service_group_filter").val();
                if(service_value === null){
                    service_value = new Array();
                }
                jQuery.ajax({
                    url: "./include/common/webServices/rest/internal.php?object=centreon_configuration_servicegroup&action=serviceList",
                    type: "GET",
                    dataType : "json",
                    data: "sgid="+sg_value,
                    success : function(json){
                        json.items.each(function(elem){
                            if(jQuery.inArray(elem.id,service_value) === -1){
                                var existingOptions = jQuery("#service_filter option");
                                var existFlag = false;
                                existingOptions.each(function(){
                                    if(jQuery(this).val() == elem.id){
                                        existFlag = true;
                                    }
                                });
                                if(!existFlag){
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

            jQuery("#host_filter").val(arrayHostValues).trigger("change",[{origin:"select2defaultinit"}]);
            jQuery("#service_filter").val(arrayServicesValues).trigger("change",[{origin:"select2defaultinit"}]);
            jQuery("#service_group_filter").val(arrayServicesGrpValues).trigger("change",[{origin:"select2defaultinit"}]);
            jQuery("#host_group_filter").val(arrayHostsGrpValues).trigger("change");
            
            jQuery( "#output" ).keypress(function(  event ) {
                if ( event.which == 13 ) {
                    var argArray = getArgsForHost();
                    args = argArray[0];
                    urlargs = argArray[1];
                    log_4_host(args, '', false);
                   event.preventDefault();
                }
            });
            
        }else{
            
            arrayPollerValues = new Array();
            <?php 
                foreach($pollerArray as $pollers){
                    ?>
                    arrayPollerValues.push('<?php echo $pollers['id']; ?>');
                    jQuery("#poller_filter").append(jQuery('<option>').val('<?php echo $pollers['id']; ?>').html('<?php echo $pollers['name']; ?>'));        

                    <?php         
                }
            ?>         
            
            
            
            jQuery("#poller_filter").change(function(event,infos){
               if(typeof infos !== "undefined" && infos.origin === "select2defaultinit"){
                return false;
               }
               log_4_engine();
            });
            jQuery("#poller_filter").val(arrayPollerValues).trigger("change");
            jQuery( "#output" ).keypress(function(  event ) {
                if ( event.which == 13 ) {
                    log_4_engine();
                   event.preventDefault();
                }
            });


        }
    });


</script>
