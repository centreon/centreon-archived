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

	/*
	 * Path to the configuration dir
	 */
	$path = "./include/views/graphs/";

	/*
	 * Include Pear Lib
	 */
	 
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	/*
	 * Add Quick Search
	 */
	$FlagSearchService = 1;
	require_once "./include/common/quickSearch.php";

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

	(isset($_GET["host_id"]) && $open_id_type == "HH") ? $_GET["host_id"] = $open_id_sub : $_GET["host_id"] = null;
	
	$id = 1;

	function getGetPostValue($str){
		$value = NULL;
		if (isset($_GET[$str]) && $_GET[$str])
			$value = htmlentities($_GET[$str], ENT_QUOTES);
		if (isset($_POST[$str]) && $_POST[$str])
			$value = htmlentities($_POST[$str], ENT_QUOTES);
		return $value;
	}
	
	/*
	 * Get Arguments
	 */
	
	$id 	= getGetPostValue("id");
	$id_svc = getGetPostValue("svc_id");
	
	if (isset($id_svc) && $id_svc){
		$id = "";
		$tab_svcs = explode(",", $id_svc);
		foreach($tab_svcs as $svc){
			$tmp = explode(";", $svc);
			if (!isset($tmp[1])) {
				$id .= "HH_" . getMyHostID($tmp[0]).",";
			} else {
				$id .= "HS_" . getMyServiceID($tmp[1], getMyHostID($tmp[0]))."_".getMyHostID($tmp[0]).",";
			}
		}
	}
	
	$id_log = "'RR_0'";
	$multi = 0;
	if (isset($_GET["mode"]) && $_GET["mode"] == "0"){
		$mode = 0;
		$id_log = "'".$id."'";
		$multi = 1;
	} else {
		$mode = 1;
		$id = 1;
	}

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('FormPeriod', 'get', "?p=".$p);
	$form->addElement('header', 'title', _("Choose the source to graph"));

	$periods = array(	""=>"",
						"10800"		=> _("Last 3 Hours"),
						"21600"		=> _("Last 6 Hours"),
						"43200"		=> _("Last 12 Hours"),
						"86400"		=> _("Last 24 Hours"),
						"172800"	=> _("Last 2 Days"),
						"259200"	=> _("Last 3 Days"),
						"302400"	=> _("Last 4 Days"),
						"432000"	=> _("Last 5 Days"),
						"604800"	=> _("Last 7 Days"),
						"1209600"	=> _("Last 14 Days"),
						"2419200"	=> _("Last 28 Days"),
						"2592000"	=> _("Last 30 Days"),
						"2678400"	=> _("Last 31 Days"),
						"5184000"	=> _("Last 2 Months"),
						"10368000"	=> _("Last 4 Months"),
						"15552000"	=> _("Last 6 Months"),
						"31104000"	=> _("Last Year"));
	$sel =& $form->addElement('select', 'period', _("Graph Period"), $periods, array("onchange"=>"resetFields([this.form.StartDate, this.form.StartTime, this.form.EndDate, this.form.EndTime])"));
	$form->addElement('text', 'StartDate', '', array("id"=>"StartDate", "onclick"=>"displayDatePicker('StartDate', this)", "size"=>10));
	$form->addElement('text', 'StartTime', '', array("id"=>"StartTime", "onclick"=>"displayTimePicker('StartTime', this)", "size"=>5));
	$form->addElement('text', 'EndDate', '', array("id"=>"EndDate", "onclick"=>"displayDatePicker('EndDate', this)", "size"=>10));
	$form->addElement('text', 'EndTime', '', array("id"=>"EndTime", "onclick"=>"displayTimePicker('EndTime', this)", "size"=>5));
	$form->addElement('button', 'graph', _("Apply"), array("onclick"=>"apply_period()"));	
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
		
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('periodORlabel', _("or"));
	$tpl->assign('from', _("From"));
	$tpl->assign('to', _("to"));	
	$tpl->assign('Apply', _("Apply"));	

	$tpl->display("graphs.ihtml");
?>
<script type="text/javascript" src="./include/common/javascript/LinkBar.js"></script>
<script type="text/javascript">

	var css_file 	= './include/common/javascript/codebase/dhtmlxtree.css';
    var headID 		= document.getElementsByTagName("head")[0];  
    var cssNode 	= document.createElement('link');
    cssNode.type 	= 'text/css';
    cssNode.rel 	= 'stylesheet';
    cssNode.href 	= css_file;
    cssNode.media 	= 'screen';
    
    headID.appendChild(cssNode);

	var multi 	= <?php echo $multi; ?>;
  	var _menu_div = document.getElementById("menu_40201");

	tree = new dhtmlXTreeObject("menu_40201","100%","100%","1");
    tree.setImagePath("./img/icones/csh_vista/");
    
    //link tree to xml
    tree.setXMLAutoLoading("./include/views/graphs/GetXmlTree.php");
        
    //load first level of tree
    tree.loadXML("./include/views/graphs/GetXmlTree.php?<?php if (isset($search) && $search) print "search=$search"."&"; ?><?php if (isset($search_service) && $search_service) print "search_service=$search_service"."&"; ?>id=<?php echo $id; ?>&mode=<?php echo $mode; ?>&sid=<?php echo session_id(); ?>");

	// system to reload page after link with new url
	//set function object to call on node select 
	tree.attachEvent("onClick", onNodeSelect)
	
	//set function object to call on node select 
	tree.attachEvent("onDblClick", onDblClick)
	
	//set function object to call on node select 		
	tree.attachEvent("onCheck",onCheck)
	
	//see other available event handlers in API documentation 
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
	
		_divBar.appendChild(create_log_link(tree,'id'));
		_divBar.appendChild(create_monitoring_link(tree,'id'));
		_divBar.setAttribute('style','float:right; margin-right:10px;' );
		_menu_2.appendChild(_divBar);
	}

	function onDblClick(nodeId){
		tree.openAllItems(nodeId);
		return(false);
	}
	
	function onCheck(nodeId){
		multi = 1;
		if (document.getElementById('openid'))
			document.getElementById('openid').innerHTML = tree.getAllChecked();
		graph_4_host(tree.getAllChecked(),1);
	}
	
	function onNodeSelect(nodeId){
		multi = 0;

		tree.openItem(nodeId);
		if (nodeId.substring(0,2) == 'HS' || nodeId.substring(0,2) == 'MS'){
			var graphView4xml = document.getElementById('graphView4xml');
			graphView4xml.innerHTML="";
			graph_4_host(nodeId);
		}
	}
	
	// it's fake methode for using ajax system by default
	function mk_pagination(){;}
	function mk_paginationFF(){;}
	function set_header_title(){;}
	function apply_period()	{
		var openid = document.getElementById('openid').innerHTML;
		graph_4_host(openid, multi);
	}

	// Period
	var currentTime = new Date();
	var period ='';

	var _zero_hour = '';
	var _zero_min = '';
	var StartDate = '';
	var EndDate = '';
	var StartTime = '';
	var EndTime = '';

	if (document.FormPeriod && !document.FormPeriod.period_choice[1].checked)	{
		period = document.FormPeriod.period.value;
	} else {
		if (currentTime.getMinutes() <= 9){
			_zero_min = '0';
		}

		if (currentTime.getHours() >= 12){
			StartDate= currentTime.getMonth()+1+"/"+currentTime.getDate()+"/"+currentTime.getFullYear();
			EndDate= currentTime.getMonth()+1+"/"+ currentTime.getDate()+"/"+currentTime.getFullYear();						

			if ((currentTime.getHours()- 12) <= 9){
				_zero_hour = '0';					
			} else{
				_zero_hour = '';											
			}
			
			StartTime = _zero_hour + (currentTime.getHours() - 12) +":" + _zero_min + currentTime.getMinutes();
			if(currentTime.getHours() <= 9){
				_zero_hour = '0';					
			} else{
				_zero_hour = '';											
			}	
			EndTime   = _zero_hour + currentTime.getHours() + ":" + _zero_min + currentTime.getMinutes();
		} else {
			StartDate = currentTime.getMonth()+1+"/"+(currentTime.getDate()-1)+"/"+currentTime.getFullYear();
			EndDate   = currentTime.getMonth()+1+"/"+ currentTime.getDate()+"/"+currentTime.getFullYear();
			
			StartTime =  (24 -(12 - currentTime.getHours()))+ ":00";
			if (currentTime.getHours() <= 9){
				_zero_hour = '0';					
			} else {
				_zero_hour = '';											
			}
			EndTime = _zero_hour + currentTime.getHours() + ":" + _zero_min + currentTime.getMinutes();
		}
	}

	if (document.FormPeriod){
		document.FormPeriod.StartDate.value = StartDate;
		document.FormPeriod.EndDate.value = EndDate;
		document.FormPeriod.StartTime.value = StartTime;
		document.FormPeriod.EndTime.value = EndTime;
	}

	function graph_4_host(id, multi)	{
		if (!multi)
			multi = 0;
		
		if (document.FormPeriod && !document.FormPeriod.period_choice[1].checked){
			period = document.FormPeriod.period.value;
		} else if(document.FormPeriod) {
			period = '';
			StartDate = document.FormPeriod.StartDate.value;
			EndDate = document.FormPeriod.EndDate.value;
			StartTime = document.FormPeriod.StartTime.value;
			EndTime = document.FormPeriod.EndTime.value;
		}

		// Metrics
		var _metrics ="";
		var _checked = "0";
		if (document.formu2 && document.formu2.elements["metric"]){
			for (i=0; i < document.formu2.elements["metric"].length; i++) {
				_checked = "0";
				if(document.formu2.elements["metric"][i].checked)	{
					_checked = "1";
				}
				_metrics += '&metric['+document.formu2.elements["metric"][i].value+']='+_checked ;
			}
		}

		// Templates
		var _tpl_id = 1;
		if (document.formu2 && document.formu2.template_select && document.formu2.template_select.value != ""){
			_tpl_id = document.formu2.template_select.value;
		}
		
		// Split metric
		var _split = 0;
		if (document.formu2 && document.formu2.split && document.formu2.split.checked)	{
			_split = 1;
		}

		var _status = 0;
		if (document.formu2 && document.formu2.status && document.formu2.status.checked)	{
			_status = 1;
		}
		
		var _warning = 0;
		if (document.formu2 && document.formu2.warning && document.formu2.warning.checked)	{
			_warning = 1;
		}
		
		var _critical = 0;
		if (document.formu2 && document.formu2.critical && document.formu2.critical.checked)	{
			_critical = 1;
		}
		
		tree.selectItem(id);
		var proc = new Transformation();
		var _addrXSL = "./include/views/graphs/GraphService.xsl";
		var _addrXML = './include/views/graphs/GetXmlGraph.php?multi='+multi+'&split='+_split+'&status='+_status+'&warning='+_warning+'&critical='+_critical+_metrics+'&template_id='+_tpl_id +'&period='+period+'&StartDate='+StartDate+'&EndDate='+EndDate+'&StartTime='+StartTime+'&EndTime='+EndTime+'&id='+id+'&sid=<?php echo $sid;?><?php if (isset($search_service) && $search_service) print "&search_service=".$search_service; ?>';

		proc.setXml(_addrXML)
		proc.setXslt(_addrXSL)
		proc.transform("graphView4xml");
	}

	// Let's save the existing assignment, if any
	var nowOnload = window.onload;
	window.onload = function () {
	    // Here is your precious function
	    // You can call as many functions as you want here;
	    myOnloadFunction1();
		
		graph_4_host(<?php echo $id_log;?>, <?php echo $multi;?>);
	
	    // Now we call old function which was assigned to onLoad, thus playing nice
	    if (nowOnload != null && typeof(nowOnload) == 'function') {
	        nowOnload();
	    }
	}

    // Your precious function
    function myOnloadFunction1() {}
</script>