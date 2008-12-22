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

	$tS = $oreon->optGen["AjaxTimeReloadStatistic"] * 1000;
	$tM = $oreon->optGen["AjaxTimeReloadMonitoring"] * 1000;
	$oreon->optGen["AjaxFirstTimeReloadStatistic"] == 0 ? $tFS = 10 : $tFS = $oreon->optGen["AjaxFirstTimeReloadStatistic"] * 1000;
	$oreon->optGen["AjaxFirstTimeReloadMonitoring"] == 0 ? $tFM = 10 : $tFM = $oreon->optGen["AjaxFirstTimeReloadMonitoring"] * 1000;
	$sid = session_id();
	$time = time();

	$obis = $o;
	if(isset($_GET["problem"]))
	$obis .= '_pb';
	if(isset($_GET["acknowledge"]))
	$obis .= '_ack_' . $_GET["acknowledge"];
		
?>
<script type="text/javascript">
var _debug = 0;

var _search = '<?php echo $search?>';
var _sid='<?php echo $sid?>';
var _search_type_host='<?php echo $search_type_host?>';
var _search_type_service='<?php echo $search_type_service?>';
var _num='<?php echo $num?>';
var _limit='<?php echo $limit?>';
var _sort_type='<?php echo $sort_type?>';
var _order='<?php echo $order?>';
var _date_time_format_status='<?php echo _("d/m/Y H:i:s")?>';
var _o='<?php echo $o?>';
var _p='<?php echo $p?>';

var _addrXSL = "./include/monitoring/status/ServicesHostGroups/xsl/serviceSummaryByHG.xsl";

var _timeoutID = 0;
var _on = 1;
var _time_reload = <?php echo $tM?>;
var _time_live = <?php echo $tFM?>;
var _nb = 0;
var _oldInputFieldValue = '<?php echo $search?>';
var _currentInputFieldValue=""; // valeur actuelle du champ texte
var _resultCache=new Object();
var _first = 1;
var _lock = 0;
var _instance = 'ALL';
var _default_instance = '0';

<?php include_once "./include/monitoring/status/Common/commonJS.php"; ?>

function set_header_title(){
	var _img_asc = mk_imgOrder('./img/icones/7x7/sort_asc.gif', "asc");
	var _img_desc = mk_imgOrder('./img/icones/7x7/sort_desc.gif', "desc");

	if(document.getElementById('host_name')){
		var h = document.getElementById('host_name');
		h.innerHTML = '<?php echo _("Hosts")?>';
	  	h.indice = 'host_name';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";
	
		var h = document.getElementById('services');
		h.innerHTML = '<?php echo _("Services")?>';
	  	h.indice = 'services';
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

	initM(<?php echo $tM?>,"<?php echo $sid?>","<?php echo $o?>");
	_on = _tmp_on;

	viewDebugInfo('refresh');
}

function monitoring_play()	{
	document.getElementById('JS_monitoring_play').style.display = 'none';
	document.getElementById('JS_monitoring_pause').style.display = 'block';	
	document.getElementById('JS_monitoring_pause_gray').style.display = 'none';
	document.getElementById('JS_monitoring_play_gray').style.display = 'block';
	_on = 1;
	initM(<?php echo $tM?>,"<?php echo $sid?>","<?php echo $o?>");
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
//		viewDebugInfo('--INIT Debug--');
	}

	if(_first){
		mainLoop();
		_first = 0;
	}

	_time=<?php echo $time?>;
	
	if(_on)
	goM(_time_reload,_sid,_o);
}

function goM(_time_reload,_sid,_o){
	_lock = 1;
	var proc = new Transformation();
	var _addrXML = "./include/monitoring/status/ServicesHostGroups/xml/serviceSummaryByHGXML.php?"+'&sid='+_sid+'&search='+_search+'&search_type_host='+_search_type_host+'&search_type_service='+_search_type_service+'&num='+_num+'&limit='+_limit+'&sort_type='+_sort_type+'&order='+_order+'&date_time_format_status='+_date_time_format_status+'&o=<?php echo $obis?>&p='+_p+'&instance='+_instance+'&time=<?php print time(); ?>';
	proc.setXml(_addrXML);
	proc.setXslt(_addrXSL);
	proc.transform("forAjax");
	_lock = 0;	
	_timeoutID = setTimeout('goM("'+ _time_reload +'","'+ _sid +'","'+_o+'")', _time_reload);
	_time_live = _time_reload;
	_on = 1;	
	set_header_title();
}
</SCRIPT>