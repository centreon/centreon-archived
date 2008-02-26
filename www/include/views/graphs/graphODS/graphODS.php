<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
**/
	if (!isset($oreon))
		exit();

	#Path to the configuration dir
	$path = "./include/views/graphs/graphODS/";

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

	if(isset($_GET["host_id"]) && $open_id_type == "HH"){
		$_GET["host_id"] = $open_id_sub;
	}
	else
		$_GET["host_id"] = null;



	if(isset($_GET["id"])){
		$id = $_GET["id"];
	}
	else
		$id = 1;


	
	if(isset($_POST["svc_id"]) && $_POST["svc_id"]){
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
	$multi = 0;
	if(isset($_GET["mode"]) && $_GET["mode"] == "0"){
		$mode = 0;
		$id_log = "'".$id."'";
		$multi = 1;
	}
	else{
		$mode = 1;
		$id = 1;
	}



//<div id="graphView4xml">..</div>
	## Form begin
	$form = new HTML_QuickForm('Form', 'get', "?p=".$p);
	$form->addElement('header', 'title', $lang["giv_sr_infos"]);

	$periods = array(	""=>"",
						"10800"=>$lang["giv_sr_p3h"],
						"21600"=>$lang["giv_sr_p6h"],
						"43200"=>$lang["giv_sr_p12h"],
						"86400"=>$lang["giv_sr_p24h"],
						"172800"=>$lang["giv_sr_p2d"],
						"302400"=>$lang["giv_sr_p4d"],
						"604800"=>$lang["giv_sr_p7d"],
						"1209600"=>$lang["giv_sr_p14d"],
						"2419200"=>$lang["giv_sr_p28d"],
						"2592000"=>$lang["giv_sr_p30d"],
						"2678400"=>$lang["giv_sr_p31d"],
						"5184000"=>$lang["giv_sr_p2m"],
						"10368000"=>$lang["giv_sr_p4m"],
						"15552000"=>$lang["giv_sr_p6m"],
						"31104000"=>$lang["giv_sr_p1y"]);

	$sel =& $form->addElement('select', 'period', $lang["giv_sr_period"], $periods);

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());

	$tpl->assign('lang', $lang);
	$tpl->display("graphODS.ihtml");


?>
<script type="text/javascript" src="./include/common/javascript/LinkBar.js"></script>
<link href="./include/common/javascript/datePicker.css" rel="stylesheet" type="text/css"/>
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
 
 		  	var _menu_div = document.getElementById("menu_40211");
		 
 
 
    		tree=new dhtmlXTreeObject("menu_40211","100%","100%","1");
            tree.setImagePath("./img/icones/csh_vista/");
			//tree.setImagePath("./include/common/javascript/codebase/imgs/csh_vista/");


            //link tree to xml
            tree.setXMLAutoLoading("./include/views/graphs/graphODS/GetODSXmlTree.php");
            
            //load first level of tree
            tree.loadXML("./include/views/graphs/graphODS/GetODSXmlTree.php?id=<?php echo $id; ?>&mode=<?php echo $mode; ?>");

			// system to reload page after link with new url
			tree.attachEvent("onClick",onNodeSelect)//set function object to call on node select 
			tree.attachEvent("onDblClick",onDblClick)//set function object to call on node select 
			tree.attachEvent("onCheck",onCheck)//set function object to call on node select 
			//see other available event handlers in API documentation 

			tree.enableDragAndDrop(0);
			tree.enableTreeLines(false);	
			tree.enableCheckBoxes(true);
			tree.enableThreeStateCheckboxes(true);


// linkBar to log/reporting/graph/ID_card

function getCheckedList(tree)
{
	return tree.getAllChecked();
}


if(document.getElementById('menu_2'))
{
	var _menu_2 = document.getElementById('menu_2')
	var _divBar = document.createElement("div");
	
	_divBar.appendChild(create_log_link(tree,'id'));
	_divBar.appendChild(create_monitoring_link(tree,'id'));
//	_divBar.appendChild(create_report_link(tree,'id'));
//	_divBar.appendChild(create_IDCard_link(tree,'id'));

	_divBar.setAttribute('style','float:right; margin-right:110px;' );
	_menu_2.appendChild(_divBar);
}
//end for linkBar


			function onDblClick(nodeId)
			{
				tree.openAllItems(nodeId);
				return(false);
			}
			
			function onCheck(nodeId)
			{
				multi = 1;
				document.getElementById('openid').innerHTML = tree.getAllChecked();
				graph_4_host(tree.getAllChecked(),1);
			}
			
			function onNodeSelect(nodeId)
			{
				multi = 0;

				tree.openItem(nodeId);
				if(nodeId.substring(0,2) == 'HS')
				{
					var graphView4xml = document.getElementById('graphView4xml');
					graphView4xml.innerHTML="..graph.." + nodeId;
					graph_4_host(nodeId);
				}
			}
			
			// it's fake methode for using ajax system by default
			function mk_pagination(){;}
			function set_header_title(){;}


			function apply_period()
			{
				var openid = document.getElementById('openid').innerHTML;
				graph_4_host(openid, multi);
			}


			// Period
			var currentTime = new Date();
			var period ='';

			var _zero_hour = '';
			var _zero_min = '';
			var StartDate='';
			var EndDate='';
			var StartTime='';
			var EndTime='';

			if(document.formu && !document.formu.period_choice[1].checked)
			{
				period = document.formu.period.value;
			}
			else
			{
				if(currentTime.getMinutes() <= 9){
					_zero_min = '0';
				}
				if(currentTime.getHours() >= 12){
					StartDate= currentTime.getMonth()+1+"/"+currentTime.getDate()+"/"+currentTime.getFullYear();
					EndDate= currentTime.getMonth()+1+"/"+ currentTime.getDate()+"/"+currentTime.getFullYear();						

					if((currentTime.getHours()- 12) <= 12){
						_zero_hour = '0';					
					}
					StartTime = _zero_hour + (currentTime.getHours() - 12) +":" + _zero_min + currentTime.getMinutes();
					EndTime   = currentTime.getHours() + ":" + _zero_min + currentTime.getMinutes();
				}
				else
				{
					StartDate= currentTime.getMonth()+1+"/"+(currentTime.getDate()-1)+"/"+currentTime.getFullYear();
					EndDate=   currentTime.getMonth()+1+"/"+ currentTime.getDate()+"/"+currentTime.getFullYear();
					StartTime=  (24 -(12 - currentTime.getHours()))+ ":00";
					EndTime= "0" + currentTime.getHours()+":" + _zero_min + currentTime.getMinutes();
					EndTime   = "0" + currentTime.getHours() + ":" + _zero_min + currentTime.getMinutes();
				}
			}

				if(document.formu){
					document.formu.StartDate.value = StartDate;
					document.formu.EndDate.value = EndDate;
					document.formu.StartTime.value = StartTime;
					document.formu.EndTime.value = EndTime;
				}


			function graph_4_host(id, multi)
			{
				if(!multi)
				multi = 0;
				


				if(document.formu && !document.formu.period_choice[1].checked)
				{
					period = document.formu.period.value;
					
				}
				else if(document.formu)
				{
					period = '';
					StartDate = document.formu.StartDate.value;
					EndDate = document.formu.EndDate.value;
					StartTime = document.formu.StartTime.value;
					EndTime = document.formu.EndTime.value;
				}

				
				// Metrics
				var _metrics ="";
				var _checked = "0";
				if(document.formu2){
					for (i=0; i < document.formu2.elements["metric"].length; i++) {
							_checked = "0";
							if(document.formu2.elements["metric"][i].checked)
							{
								_checked = "1";
							}
							_metrics += '&metric['+document.formu2.elements["metric"][i].value+']='+_checked ;
					   }
				}

				// Templates
				var _tpl_id = 1;
				if(document.formu2 && document.formu2.template_select.value != "")
				{
					_tpl_id = document.formu2.template_select.value;
				}
				// Split metric
				var _split = 0;
				if(document.formu2 && document.formu2.split.checked)
				{
					_split = 1
				}

				
				tree.selectItem(id);
				
				var proc = new Transformation();
				var _addrXSL = "./include/views/graphs/graphODS/GraphService.xsl";
				var _addrXML = './include/views/graphs/graphODS/GetODSXmlGraph.php?multi='+multi+'&split='+_split+_metrics+'&template_id='+_tpl_id +'&period='+period+'&StartDate='+StartDate+'&EndDate='+EndDate+'&StartTime='+StartTime+'&EndTime='+EndTime+'&id='+id+'&sid=<?php echo $sid;?>';


//				var header = document.getElementById('header');
//				header.innerHTML += _addrXML;


				proc.setXml(_addrXML)
				proc.setXslt(_addrXSL)
				proc.transform("graphView4xml");

		}



				


graph_4_host(<?php echo $id_log;?>, <?php echo $multi;?>);

</script>


