<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Cedrick Facon

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	if (!isset($oreon))
		exit();

	$tS = $oreon->optGen["AjaxTimeReloadStatistic"] * 1000;
	$tM = $oreon->optGen["AjaxTimeReloadMonitoring"] * 1000;
	$oreon->optGen["AjaxFirstTimeReloadStatistic"] == 0 ? $tFS = 10 : $tFS = $oreon->optGen["AjaxFirstTimeReloadStatistic"] * 1000;
	$oreon->optGen["AjaxFirstTimeReloadMonitoring"] == 0 ? $tFM = 10 : $tFM = $oreon->optGen["AjaxFirstTimeReloadMonitoring"] * 1000;
	$sid = session_id();
	$time = time();

	if($num < 0)
		$num =0;
?>
<SCRIPT LANGUAGE="JavaScript">
var _debug = 0;

var _search = '<?=$search?>';
var _sid='<?=$sid?>';
var _search_type_host='<?=$search_type_host?>';
var _search_type_service='<?=$search_type_service?>';
var _num='<?=$num?>';
var _limit='<?=$limit?>';
var _sort_type='<?=$sort_type?>';
var _order='<?=$order?>';
var _date_time_format_status='<?=$lang["date_time_format_status"]?>';
var _o='<?=$o?>';
var _p='<?=$p?>';

var _addrXSL = "./include/monitoring/status/status-ndo/templates/service.xsl";
var _timeoutID = 0;
var _on = 1;
var _time_reload = <?=$tM?>;
var _time_live = <?=$tFM?>;
var _nb = 0;
var _oldInputFieldValue = '<?=$search?>';
var _currentInputFieldValue=""; // valeur actuelle du champ texte
var _resultCache=new Object();
var _first = 1;
var _lock = 0;
var _instance = 'ALL';
var _default_instance = '0';
var _nc = 0;
<?php
include_once("makeJS_Common.php");
?>


// linkBar to log/reporting/graph/ID_card

	function getCheckedList()
	{
		var mesinputs = document.getElementsByTagName("input" );
		var tab = new Array();
		var nb = 0;
	
		for (var i = 0; i < mesinputs.length; i++) {
	  		if (mesinputs[i].type.toLowerCase() == 'checkbox' && mesinputs[i].checked && mesinputs[i].name.substr(0,6) == 'select') {
				var name = mesinputs[i].name;
				var l = name.length;
				tab[nb] = name.substr(7,l-8);
				nb++;
	  		}
		}
		return tab;
	}
	
	
	function goToLog()
	{
		var tab = getCheckedList();
		document.location.href='oreon.php?p=203&mode=0&id_svc=' +tab;  
	}
	function goToGraph()
	{
		var tab = getCheckedList();
		document.location.href='oreon.php?p=40211&mode=0&id_svc=' +tab;  	
	}
	function goToReport()
	{
		var tab = getCheckedList();
		document.location.href='oreon.php?p=p=30702&period=today&svctab=' +tab;  	
	}
	function goToIDCard()
	{
		var tab = getCheckedList();
		document.location.href='oreon.php?p=70102&mode=0&id_svc=' +tab;  	
	}
	
	if(document.getElementById('menu_2'))
	{
		var _menu_2 = document.getElementById('menu_2')
		var _divBar = document.createElement("div");
	
		var _img_graph = mk_img('./img/icones/24x24/chart.png', "Graph");
		var _linkaction_graph = document.createElement("a");
		_linkaction_graph.href = '#';
		_linkaction_graph.onclick=function(){goToGraph()}
		_linkaction_graph.appendChild(_img_graph);
		_divBar.appendChild(_linkaction_graph);
	
		var _img_log = mk_img('./img/icones/24x24/text_find.png', "Event Log");
		var _linkaction_log = document.createElement("a");
		_linkaction_log.href = '#';
		_linkaction_log.onclick=function(){goToLog()}
		_linkaction_log.appendChild(_img_log);
		_divBar.appendChild(_linkaction_log);
	
		var _img_graph = mk_img('./img/icones/24x24/chart.png', "Reporting for the first svc selected");
		var _linkaction_graph = document.createElement("a");
		_linkaction_graph.href = '#';
		_linkaction_graph.onclick=function(){goToGraph()}
		_linkaction_graph.appendChild(_img_graph);
		_divBar.appendChild(_linkaction_graph);

		var _img_graph = mk_img('./img/icones/24x24/chart.png', "Reporting for the first host selected");
		var _linkaction_graph = document.createElement("a");
		_linkaction_graph.href = '#';
		_linkaction_graph.onclick=function(){goToGraph()}
		_linkaction_graph.appendChild(_img_graph);
		_divBar.appendChild(_linkaction_graph);

		_divBar.setAttribute('style','float:right; margin-right:110px;' );
		_menu_2.appendChild(_divBar);
	}

//end for linkBar





var tempX = 0;
var tempY = 0;

function position(e)
	{
	tempX = (navigator.appName.substring(0,3) == "Net") ? e.pageX : event.x+document.body.scrollLeft;
	tempY = (navigator.appName.substring(0,3) == "Net") ? e.pageY : event.y+document.body.scrollTop;
	}
if(navigator.appName.substring(0,3) == "Net")
	document.captureEvents(Event.MOUSEMOVE);
document.onmousemove = position;

function set_header_title(){
	var _img_asc = mk_img('./img/icones/7x7/sort_asc.gif', "asc");
	var _img_desc = mk_img('./img/icones/7x7/sort_desc.gif', "desc");

	if(document.getElementById('host_name')){

		var h = document.getElementById('host_name');
		h.innerHTML = '<?=$lang['m_mon_hosts']?>';
	  	h.indice = 'host_name';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('service_description');
		h.innerHTML = '<?=$lang['m_mon_services']?>';
	  	h.indice = 'service_description';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('current_state');
		h.innerHTML = '<?=$lang['mon_status']?>';
	  	h.indice = 'current_state';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";


		var h = document.getElementById('last_state_change');
		h.innerHTML = '<?=$lang['mon_duration']?>';
	  	h.indice = 'last_state_change';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('last_check');
		h.innerHTML = '<?=$lang['mon_last_check']?>';
	  	h.indice = 'last_check';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('current_attempt');
		h.innerHTML = '<?=$lang['m_mon_try']?>';
	  	h.indice = 'current_attempt';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('plugin_output');
		h.innerHTML = '<?=$lang['mon_status_information']?>';
	  	h.indice = 'plugin_output';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";


		var h = document.getElementById(_sort_type);
		var _linkaction_asc = document.createElement("a");
		if(_order == 'ASC')
			_linkaction_asc.appendChild(_img_asc);
		else
			_linkaction_asc.appendChild(_img_desc);
		_linkaction_asc.href = '#' ;
		_linkaction_asc.onclick=function(){change_order()};
		h.appendChild(_linkaction_asc);

	}

}



function monitoring_refresh()	{
	_tmp_on = _on;
	_time_live = _time_reload;
	_on = 1;
	window.clearTimeout(_timeoutID);

	initM(<?=$tM?>,"<?=$sid?>","<?=$o?>");
	_on = _tmp_on;

	viewDebugInfo('refresh');
}

function monitoring_play()	{
	document.getElementById('JS_monitoring_play').style.display = 'none';
	document.getElementById('JS_monitoring_pause').style.display = 'block';
	document.getElementById('JS_monitoring_pause_gray').style.display = 'none';
	document.getElementById('JS_monitoring_play_gray').style.display = 'block';
	_on = 1;
	initM(<?=$tM?>,"<?=$sid?>","<?=$o?>");
}

function monitoring_pause()	{
	document.getElementById('JS_monitoring_play').style.display = 'block';
	document.getElementById('JS_monitoring_pause_gray').style.display = 'block';
	document.getElementById('JS_monitoring_play_gray').style.display = 'none';
	document.getElementById('JS_monitoring_pause').style.display='none';
	_on = 0;
	window.clearTimeout(_timeoutID);
}

function initM(_time_reload,_sid,_o){
	construct_selecteList_ndo_instance('instance_selected');
	if(!document.getElementById('debug')){
		var _divdebug = document.createElement("div");
		_divdebug.id = 'debug';
		var _debugtable = document.createElement("table");
		_debugtable.id = 'debugtable';
		var _debugtr = document.createElement("tr");
		_debugtable.appendChild(_debugtr);
		_divdebug.appendChild(_debugtable);
		_header = document.getElementById('header');
		_header.appendChild(_divdebug);
	}

	if(_first){
		viewDebugInfo('--loop--');

		mainLoop();
		_first = 0;
	}
	_time=<?=$time?>;
	if(_on)
	goM(_time_reload,_sid,_o);
}

function goM(_time_reload,_sid,_o){

	_lock = 1;
	var proc = new Transformation();
	var _addrXML = "./include/monitoring/engine/MakeXML_Ndo_service.php?"+'&sid='+_sid+'&search='+_search+'&search_type_host='+_search_type_host+'&search_type_service='+_search_type_service+'&num='+_num+'&limit='+_limit+'&sort_type='+_sort_type+'&order='+_order+'&date_time_format_status='+_date_time_format_status+'&o='+_o+'&p='+_p+'&host_name=<?=$host_name?>'+'&instance='+_instance+'&nc='+_nc;
	proc.setXml(_addrXML);
	proc.setXslt(_addrXSL);
	proc.transform("forAjax");


	_lock = 0;

	viewDebugInfo('--end--');


	_timeoutID = setTimeout('goM("'+ _time_reload +'","'+ _sid +'","'+_o+'")', _time_reload);
	_time_live = _time_reload;
	_on = 1;


	set_header_title();
}

function displayPOPUP(id){
	var span = document.getElementById('span_'+id);
	var proc_popup = new Transformation();
	var _addrXMLSpan = "./include/monitoring/engine/MakeXML_Ndo_for_one_host.php?"+'&sid='+_sid+'&host_id='+id;
	var _addrXSLSpan = "./include/monitoring/status/status-ndo/templates/host_popup.xsl";
	proc_popup.setXml(_addrXMLSpan);
	proc_popup.setXslt(_addrXSLSpan);
	proc_popup.transform('span_'+id);

	var l=screen.availWidth; //calcul auto de la largeur de l'ecran client 
	var h=screen.availHeight; //calcul auto de la hauteur de l'ecran client 		
	if(h - tempY < span.offsetHeight){
		span.style.top = '-'+ span.offsetHeight +'px';
	}
}

function displayPOPUP_svc(id){
	var span = document.getElementById('span_'+id);
	var proc_popup = new Transformation();
	var _addrXMLSpan = "./include/monitoring/engine/MakeXML_Ndo_for_one_svc.php?"+'&sid='+_sid+'&svc_id='+id;
	var _addrXSLSpan = "./include/monitoring/status/status-ndo/templates/svc_popup.xsl";
	proc_popup.setXml(_addrXMLSpan);
	proc_popup.setXslt(_addrXSLSpan);
	proc_popup.transform('span_'+id);

	var l=screen.availWidth; //calcul auto de la largeur de l'ecran client 
	var h=screen.availHeight; //calcul auto de la hauteur de l'ecran client 		

	if(h - tempY < span.offsetHeight){
		span.style.top = '-'+ span.offsetHeight +'px';
	}
}

function hiddenPOPUP(id){
		var span = document.getElementById('span_'+id);
		span.innerHTML = '';
}

function displayIMG(index, s_id, id)
{
	
	   // Pour les navigateurs récents
    if ( document.getElementById && document.getElementById( 'div_img' ) ){
        Pdiv = document.getElementById( 'div_img' );
        PcH = true;
    }
    // Pour les veilles versions
    else if ( document.all && document.all[ 'div_img' ] ){
        Pdiv = document.all[ 'div_img' ];
        PcH = true;
    }
    // Pour les très veilles versions
    else if ( document.layers && document.layers[ 'div_img' ] ){
        Pdiv = document.layers[ 'div_img' ];
        PcH = true;
    }
    else{
        PcH = false;
    }
    if ( PcH ){
			_img = mk_img('include/views/graphs/graphODS/generateImages/generateODSImage.php?session_id='+s_id+'&index='+index, 'graph popup'+'&index='+index);
			Pdiv.appendChild(_img);
			var l=screen.availWidth; //calcul auto de la largeur de l'ecran client 
			var h=screen.availHeight; //calcul auto de la hauteur de l'ecran client 		
			var posy = tempY + 10;
			if(h - tempY < 420){
				posy = tempY-310;
			}
			Pdiv.style.display = "block";
			Pdiv.style.left = tempX +'px';
			Pdiv.style.top = posy +'px';
    }
    else{
   // alert('ie ca pux');	
    }
}

function hiddenIMG(id){
	   // Pour les navigateurs récents
    if ( document.getElementById && document.getElementById( 'div_img' ) ){
        Pdiv = document.getElementById( 'div_img' );
        PcH = true;
    }
    // Pour les veilles versions
    else if ( document.all && document.all[ 'div_img' ] ){
        Pdiv = document.all[ 'div_img' ];
        PcH = true;
    }
    // Pour les très veilles versions
    else if ( document.layers && document.layers[ 'div_img' ] ){
        Pdiv = document.layers[ 'div_img' ];
        PcH = true;
    }
    else{
        PcH = false;
    }
    if ( PcH ){
		Pdiv.style.display = "none";
		Pdiv.innerHTML = '';
	}
}
</SCRIPT>