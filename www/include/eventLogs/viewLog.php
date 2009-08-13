<?php
/*
 * Copyright 2005-2009 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
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
  
	if (!isset($oreon))
		exit();

	function get_user_param($user_id, $pearDB){
		$tab_row = array();
		$DBRESULT =& $pearDB->query("SELECT * FROM `contact_param` WHERE `cp_contact_id` = '".$user_id."'");		
		while( $row = $DBRESULT->fetchRow())
			$tab_row[$row["cp_key"]] = $row["cp_value"];
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
	
	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	/*
	 * Add QuickSearch ToolBar
	 */
	$FlagSearchService = 1;
	include_once("./include/common/quickSearch.php");

	/*
	 * Path to the configuration dir
	 */
	$path = "./include/eventLogs/";
	

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);


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
	
	/*
	 * From Monitoring
	 */
	if (isset($_POST["svc_id"])) {
		$services = split(",", $_POST["svc_id"]);		
		foreach ($services as $str) {
			$buf_svc = split(";", $str);
			$id .= "HS_" . getMyServiceID($buf_svc[1], getMyHostID($buf_svc[0])).",";
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
				$tmp = explode(";", $svc);
				$id .= "HS_" . getMyServiceID($tmp[1], getMyHostID($tmp[0])).",";
			}
		}
	} else {
		$meta = 1;
	}

	$id_log = "'RR_0'";
	$multi =0;
	if (isset($_GET["mode"]) && $_GET["mode"] == "0"){
		$mode = 0;
		$id_log = "'".$id."'";
		$multi =1;
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

	$form->addElement('select', 'period', _("Log Period"), $periods, array("onchange"=>"resetFields([this.form.StartDate, this.form.StartTime, this.form.EndDate, this.form.EndTime])")); 
	$form->addElement('text', 'StartDate', '', array("id"=>"StartDate", "onclick"=>"displayDatePicker('StartDate', this)", "size"=>8)); 
	$form->addElement('text', 'StartTime', '', array("id"=>"StartTime", "onclick"=>"displayTimePicker('StartTime', this)", "size"=>5)); 
	$form->addElement('text', 'EndDate', '', array("id"=>"EndDate", "onclick"=>"displayDatePicker('EndDate', this)", "size"=>8)); 
	$form->addElement('text', 'EndTime', '', array("id"=>"EndTime", "onclick"=>"displayTimePicker('EndTime', this)", "size"=>5)); 
	$form->addElement('button', 'graph', _("Apply"), array("onclick"=>"apply_period()")); 

	$form->setDefaults(array("period"=>$user_params['log_filter_period']));
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('From', _("From"));
	$tpl->assign('To', _("To"));
	$tpl->assign('periodORlabel', _("or"));
	$tpl->display("viewLog.ihtml");

?><link href="./include/common/javascript/datePicker.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="./include/common/javascript/LinkBar.js"></script>
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
 
    tree=new dhtmlXTreeObject("menu_20301","100%","100%","1");
    tree.setImagePath("./img/icones/csh_vista/");
    
    //link tree to xml
    tree.setXMLAutoLoading("./include/eventLogs/XmlTree/GetXmlTree.php"); 
            
    //load first level of tree
    tree.loadXML("./include/eventLogs/XmlTree/GetXmlTree.php?<?php if (isset($meta) && $meta) print "meta=$meta"."&"; ?>search_host=<?php print $search; ?><?php if (isset($search_service) && $search_service) print "&search_service=$search_service"; ?>&sid=<?php print session_id(); ?>&id=<?php echo $id; ?>&mode=<?php echo $mode; ?>");

	// system to reload page after link with new url
	//set function object to call on node select 
	tree.attachEvent("onClick", onNodeSelect)
	
	//set function object to call on node select 
	tree.attachEvent("onDblClick", onDblClick)
	
	//set function object to call on node select 
	tree.attachEvent("onCheck", onCheck)

	tree.enableDragAndDrop(0);
	tree.enableTreeLines(false);
	tree.enableCheckBoxes(true);
	tree.enableThreeStateCheckboxes(true);

	// linkBar to log/reporting/graph/ID_card
	function getCheckedList(tree){
		return tree.getAllChecked();
	}
	
	if (document.getElementById('linkBar')){
		var _menu_2 = document.getElementById('linkBar')
		var _divBar = document.createElement("div");
		
		_divBar.appendChild(create_graph_link(tree,'id'));
		_divBar.appendChild(create_monitoring_link(tree,'id'));
		_divBar.setAttribute('style','float:right; margin-right:10px;' );
		_menu_2.appendChild(_divBar);
	}

	function onDblClick(nodeId){
		tree.openAllItems(nodeId);
		return(false);
	}
	
	function onNodeSelect(nodeId){
		var logView4xml = document.getElementById('logView4xml');
		
		logView4xml.innerHTML = "Waiting XML log";
		tree.openItem(nodeId);
		multi = 0;
		log_4_host(nodeId,'');
		
	}
	
	function onCheck(){
		multi = 1;
		log_4_host(tree.getAllChecked(),'');
	}
		
	// it's fake methode for using ajax system by default
	function mk_pagination(){;}
	function mk_paginationFF(){;}
	function set_header_title(){;}
	
	function apply_period(){
		var openid = document.getElementById('openid').innerHTML;
		log_4_host(openid,'','');
	}
	
	var _num = 0;
	function log_4_host_page(id, formu, num)	{
		_num = num;
		log_4_host(id, formu, '');
	}

	var _host 		= <?php echo $user_params["log_filter_host"]; ?>;
	var _service 	= <?php echo $user_params["log_filter_svc"]; ?>;
	
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
	
	// Period
	var currentTime = new Date();
	var period ='';
	
	var _zero_hour = '';
	var _zero_min = '';
	var StartDate='';
	var EndDate='';
	var StartTime='';
	var EndTime='';

	if (document.FormPeriod && document.FormPeriod.period.value!="")	{
		period = document.FormPeriod.period.value;
	}

	if (document.FormPeriod && document.FormPeriod.period.value==""){
		document.FormPeriod.StartDate.value = StartDate;
		document.FormPeriod.EndDate.value = EndDate;
		document.FormPeriod.StartTime.value = StartTime;
		document.FormPeriod.EndTime.value = EndTime;
	}


	function log_4_host(id, formu, type){			
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
	
		if (document.FormPeriod && document.FormPeriod.StartDate.value != "")
			StartDate = document.FormPeriod.StartDate.value;
		if (document.FormPeriod && document.FormPeriod.EndDate.value != "")
			EndDate = document.FormPeriod.EndDate.value;
	
		if (document.FormPeriod && document.FormPeriod.StartTime.value != "")
			StartTime = document.FormPeriod.StartTime.value;
		if (document.FormPeriod && document.FormPeriod.EndTime.value != "")
			EndTime = document.FormPeriod.EndTime.value;
		
		if (document.formu2 && document.formu2.oh)
			_oh = document.formu2.oh.checked;
	
		if (document.formu2 && document.formu2.search_H)
			_search_H = document.formu2.search_H.checked;
		if (document.formu2 && document.formu2.search_S)
			_search_S = document.formu2.search_S.checked;
			
		tree.selectItem(id);
	
		var proc = new Transformation();
		var _addrXSL = "./include/eventLogs/log.xsl";

		if (!type){		
			var _addr = './include/eventLogs/GetXmlLog.php?multi='+multi+'&oh='+_oh+'&warning='+_warning+'&unknown='+_unknown+'&critical='+_critical+'&ok='+_ok+'&unreachable='+_unreachable+'&down='+_down+'&up='+_up+'&num='+_num+'&error='+_error+'&alert='+_alert+'&notification='+_notification+'&search_H='+_search_H+'&search_S='+_search_S+'&period='+period+'&StartDate='+StartDate+'&EndDate='+EndDate+'&StartTime='+StartTime+'&EndTime='+EndTime+'&id='+id+'&sid=<?php echo $sid;?><?php if (isset($search_service) && $search_service) print "&search_service=".$search_service; ?>';
			proc.setXml(_addr)
			proc.setXslt(_addrXSL)
			proc.transform("logView4xml");
		} else{			
			var openid = document.getElementById('openid').innerHTML;
			var _addr = './include/eventLogs/Get'+type+'Log.php?multi='+multi+'&oh='+_oh+'&warning='+_warning+'&unknown='+_unknown+'&critical='+_critical+'&ok='+_ok+'&unreachable='+_unreachable+'&down='+_down+'&up='+_up+'&num='+_num+'&error='+_error+'&alert='+_alert+'&notification='+_notification+'&search_H='+_search_H+'&search_S='+_search_S+'&period='+period+'&StartDate='+StartDate+'&EndDate='+EndDate+'&StartTime='+StartTime+'&EndTime='+EndTime+'&id='+openid+'&sid=<?php echo $sid;?><?php if (isset($search_service) && $search_service) print "&search_service=".$search_service; ?>&export=1';
			document.location.href = _addr;
		}
	}
	
	var nowOnload = window.onload;
	window.onload = function () {
    // Here is your precious function
    // You can call as many functions as you want here;
	log_4_host(<?php echo $id_log;?>, '', '');

    // Now we call old function which was assigned to onLoad, thus playing nice
    if (nowOnload != null && typeof(nowOnload) == 'function') {
        nowOnload();
    }
}
</script>