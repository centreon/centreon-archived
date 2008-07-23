<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */
 
	if (!isset($oreon))
		exit();

	function get_user_param($user_id, $pearDB){
		$tab_row = array();
		$DBRESULT =& $pearDB->query("SELECT * FROM contact_param where cp_contact_id = '".$user_id."'");
		if (PEAR::isError($DBRESULT)){
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			return null;		
		}
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
	
	#Path to the configuration dir
	$path = "./include/eventLogs/";
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$openid = '0';
	$open_id_sub = '0';
	if(isset($_GET["openid"])){
		$openid = $_GET["openid"];
		$open_id_type = substr($openid, 0, 2);
		$open_id_sub = substr($openid, 3, strlen($openid));
	}

	if(isset($_GET["id"])){
		$id = $_GET["id"];
	} else
		$id = 1;
	if(isset($_POST["id"])){
		$id = $_POST["id"];
	} else
		$id = 1;

	if (isset($_POST["svc_id"]) && $_POST["svc_id"]){
		$id = "";
		$id_svc = $_POST["svc_id"];
		$tab_svcs = explode(",", $id_svc);
		foreach($tab_svcs as $svc)
		{
			$tmp = explode(";", $svc);
			$id .= "HS_" . getMyServiceID($tmp[1], getMyHostID($tmp[0])).",";
		}
	}
	$id_log = "'RR_0'";
	$multi =0;
	if(isset($_GET["mode"]) && $_GET["mode"] == "0"){
		$mode = 0;
		$id_log = "'".$id."'";
		$multi =1;
	} else{
		$mode = 1;
		$id = 1;
	}

	## Form begin
	$form = new HTML_QuickForm('Form', 'get', "?p=".$p);
	$form->addElement('header', 'title', _("Choose the source to graph"));

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

	$sel =& $form->addElement('select', 'period', _("Graph Period"), $periods);

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());

	$tpl->assign('From', _("From"));
	$tpl->assign('To', _("To"));
	$tpl->display("viewLog.ihtml");

?>
<link href="./include/common/javascript/datePicker.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="./include/common/javascript/LinkBar.js"></script>
<script language='javascript' src='./include/common/javascript/tool.js'></script>
<script>

	var css_file = './include/common/javascript/codebase/dhtmlxtree.css';
	var headID = document.getElementsByTagName("head")[0];  
	var cssNode = document.createElement('link');
	cssNode.type = 'text/css';
	cssNode.rel = 'stylesheet';
	cssNode.href = css_file;
	cssNode.media = 'screen';headID.appendChild(cssNode);
 
 	var multi = <?php echo $multi; ?>;
 
    tree=new dhtmlXTreeObject("menu_20301","100%","100%","1");
    tree.setImagePath("./img/icones/csh_vista/");
    
    //link tree to xml
    tree.setXMLAutoLoading("./include/common/XmlTree/GetODSXmlTree.php"); 
            
    //load first level of tree
    tree.loadXML("./include/common/XmlTree/GetODSXmlTree.php?sid=<?php print session_id(); ?>&id=<?php echo $id; ?>&mode=<?php echo $mode; ?>");

	// system to reload page after link with new url
	tree.attachEvent("onClick",onNodeSelect)//set function object to call on node select 
	tree.attachEvent("onDblClick",onDblClick)//set function object to call on node select 
	tree.attachEvent("onCheck",onCheck)//set function object to call on node select 

	tree.enableDragAndDrop(0);
	tree.enableTreeLines(false);
	tree.enableCheckBoxes(true);
	tree.enableThreeStateCheckboxes(true);

	// linkBar to log/reporting/graph/ID_card
	function getCheckedList(tree){
		return tree.getAllChecked();
	}
	if(document.getElementById('linkBar')){
		var _menu_2 = document.getElementById('linkBar')
		var _divBar = document.createElement("div");
		
		_divBar.appendChild(create_graph_link(tree,'id'));
		_divBar.appendChild(create_monitoring_link(tree,'id'));
	//	_divBar.appendChild(create_report_link(tree,'id'));
	//	_divBar.appendChild(create_IDCard_link(tree,'id'));
		_divBar.setAttribute('style','float:right; margin-right:110px;' );
		_menu_2.appendChild(_divBar);
	}
	//end for linkBar

	function onDblClick(nodeId){
		tree.openAllItems(nodeId);
		return(false);
	}
	
	function onNodeSelect(nodeId){
		var logView4xml = document.getElementById('logView4xml');
		logView4xml.innerHTML="Waiting XML log";
	
		tree.openItem(nodeId);
		multi = 0;
		log_4_host(nodeId,'');
	}
	
	function onCheck(){
		multi = 1;
		
		if(tree.getAllChecked()){
			log_4_host(tree.getAllChecked(),'','');
		} else {
			//		var logView4xml = document.getElementById('logView4xml').innerHTML = '<- Check or select an item or more !';		
		}
	}
		
	// it's fake methode for using ajax system by default
	function mk_pagination(){;}
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
	
	// Period
	var currentTime = new Date();
	var period ='';
	
	var _zero_hour = '';
	var _zero_min = '';
	var StartDate='';
	var EndDate='';
	var StartTime='';
	var EndTime='';

	if (document.formu && !document.formu.period_choice[1].checked)	{
		period = document.formu.period.value;
	} else {
		if(currentTime.getMinutes() <= 9){
			_zero_min = '0';
		}

		if (currentTime.getHours() >= 12){
			StartDate= currentTime.getMonth()+1+"/"+currentTime.getDate()+"/"+currentTime.getFullYear();
			EndDate= currentTime.getMonth()+1+"/"+ currentTime.getDate()+"/"+currentTime.getFullYear();						
	
			if ((currentTime.getHours()- 12) <= 9){
				_zero_hour = '0';					
			} else {
				_zero_hour = '';											
			}
			StartTime = _zero_hour + (currentTime.getHours() - 12) +":" + _zero_min + currentTime.getMinutes();
			if (currentTime.getHours() <= 9){
				_zero_hour = '0';					
			} else {
				_zero_hour = '';											
			}	
			EndTime   = _zero_hour + currentTime.getHours() + ":" + _zero_min + currentTime.getMinutes();
		} else {
			StartDate= currentTime.getMonth()+1+"/"+(currentTime.getDate()-1)+"/"+currentTime.getFullYear();
			EndDate=   currentTime.getMonth()+1+"/"+ currentTime.getDate()+"/"+currentTime.getFullYear();
	
			StartTime=  (24 -(12 - currentTime.getHours()))+ ":00";
			if (currentTime.getHours() <= 9){
				_zero_hour = '0';					
			} else {
				_zero_hour = '';											
			}		
			EndTime = _zero_hour + currentTime.getHours() + ":" + _zero_min + currentTime.getMinutes();
		}	
	}

	if (document.formu){
		document.formu.StartDate.value = StartDate;
		document.formu.EndDate.value = EndDate;
		document.formu.StartTime.value = StartTime;
		document.formu.EndTime.value = EndTime;
	}

	function log_4_host(id, formu, type){	
		if(document.formu && !document.formu.period_choice[1].checked)	{
			period = document.formu.period.value;
		} else if(document.formu)	{
			period = '';
			StartDate = document.formu.StartDate.value;
			EndDate = document.formu.EndDate.value;
			StartTime = document.formu.StartTime.value;
			EndTime = document.formu.EndTime.value;
		}
	
		// type
		if(document.formu2 && document.formu2.notification)
			_notification = document.formu2.notification.checked;
		if(document.formu2 && document.formu2.error)
			_error = document.formu2.error.checked;
		if(document.formu2 && document.formu2.alert)
			_alert = document.formu2.alert.checked;
	
		if(document.formu2 && document.formu2.up)
			_up = document.formu2.up.checked;
		if(document.formu2 && document.formu2.down)
			_down = document.formu2.down.checked;
		if(document.formu2 && document.formu2.unreachable)
			_unreachable = document.formu2.unreachable.checked;
	
		if(document.formu2 && document.formu2.ok)
			_ok = document.formu2.ok.checked;
	
		if(document.formu2 && document.formu2.warning)
			_warning = document.formu2.warning.checked;
	
		if(document.formu2 && document.formu2.critical)
			_critical = document.formu2.critical.checked;
	
		if(document.formu2 && document.formu2.unknown)
			_unknown = document.formu2.unknown.checked;
	
		if(document.formu && document.formu.StartDate.value != "")
			StartDate = document.formu.StartDate.value;
		if(document.formu && document.formu.EndDate.value != "")
			EndDate = document.formu.EndDate.value;
	
		if(document.formu && document.formu.StartTime.value != "")
			StartTime = document.formu.StartTime.value;
		if(document.formu && document.formu.EndTime.value != "")
			EndTime = document.formu.EndTime.value;
	
		tree.selectItem(id);
	
		var proc = new Transformation();
		var _addrXSL = "./include/eventLogs/log.xsl";

		if(!type)
		{		
		var _addr = './include/eventLogs/GetODSXmlLog.php?multi='+multi+'&warning='+_warning+'&unknown='+_unknown+'&critical='+_critical+'&ok='+_ok+'&unreachable='+_unreachable+'&down='+_down+'&up='+_up+'&num='+_num+'&error='+_error+'&alert='+_alert+'&notification='+_notification+'&period='+period+'&StartDate='+StartDate+'&EndDate='+EndDate+'&StartTime='+StartTime+'&EndTime='+EndTime+'&id='+id+'&sid=<?php echo $sid;?>';
		proc.setXml(_addr)
		proc.setXslt(_addrXSL)
		proc.transform("logView4xml");
		}
		else{
		var _addr = './include/eventLogs/GetODS'+type+'Log.php?multi='+multi+'&warning='+_warning+'&unknown='+_unknown+'&critical='+_critical+'&ok='+_ok+'&unreachable='+_unreachable+'&down='+_down+'&up='+_up+'&num='+_num+'&error='+_error+'&alert='+_alert+'&notification='+_notification+'&period='+period+'&StartDate='+StartDate+'&EndDate='+EndDate+'&StartTime='+StartTime+'&EndTime='+EndTime+'&id='+id+'&sid=<?php echo $sid;?>';
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