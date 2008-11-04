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

	if ($num < 0)
		$num = 0;
?>
<script type="text/javascript" src="./include/common/javascript/LinkBar.js"></script>
<script type="text/javascript">
	var _debug = 0;
	
	var _search = '<?php echo $search; ?>';
	var _sid='<?php echo $sid ?>';
	var _search_type_host='<?php echo $search_type_host ?>';
	var _search_type_service='<?php echo $search_type_service ?>';
	var _num='<?php echo $num ?>';
	var _limit='<?php echo $limit ?>';
	var _sort_type='<?php echo $sort_type ?>';
	var _order='<?php echo $order ?>';
	var _date_time_format_status='<?php echo _("d/m/Y H:i:s") ?>';
	var _o='<?php echo $o ?>';
	var _p='<?php echo $p ?>';
	
	var _addrXSL = "./include/monitoring/status/Services/xsl/service.xsl";
	var _timeoutID = 0;
	var _on = 1;
	var _time_reload = <?php echo $tM ?>;
	var _time_live = <?php echo $tFM ?>;
	var _nb = 0;
	var _oldInputFieldValue = '<?php echo $search?>';
	var _currentInputFieldValue=""; // valeur actuelle du champ texte
	var _resultCache=new Object();
	var _first = 1;
	var _lock = 0;
	var _instance = 'ALL';
	var _default_instance = '0';
	var _nc = 0;
	
<?php include_once "./include/monitoring/status/Common/commonJS.php"; ?>

// linkBar to log/reporting/graph/ID_card

function getCheckedList(_input_name){
	var mesinputs = document.getElementsByTagName("input");
	var tab = new Array();
	var nb = 0;

	for (var i = 0; i < mesinputs.length; i++) {
  		if (mesinputs[i].type.toLowerCase() == 'checkbox' && mesinputs[i].checked && mesinputs[i].name.substr(0,6) == _input_name) {
			var name = mesinputs[i].name;
			var l = name.length;
			tab[nb] = name.substr(7,l-8);
			nb++;
  		}
	}
	return tab;
}

if (document.getElementById('linkBar'))	{
	var _linkBar = document.getElementById('linkBar')
	var _divBar = document.createElement("div");
	
	_divBar.appendChild(create_graph_link('select','svc_id'));
	_divBar.appendChild(create_log_link('select','svc_id'));
	_divBar.setAttribute('style','float:right; margin-right:10px;' );
	_linkBar.appendChild(_divBar);
}

//end for linkBar

var tempX = 0;
var tempY = 0;

function position(e){
	tempX = (navigator.appName.substring(0,3) == "Net") ? e.pageX : event.x + document.body.scrollLeft;
	tempY = (navigator.appName.substring(0,3) == "Net") ? e.pageY : event.y + document.body.scrollTop;
}

 if (navigator.appName.substring(0,3) == "Net")
	document.captureEvents(Event.MOUSEMOVE);
document.onmousemove = position;

function set_header_title(){

	var _img_asc  = mk_imgOrder('./img/icones/7x7/sort_asc.gif', "<?php echo _("Sort results (ascendant)"); ?>");
	var _img_desc = mk_imgOrder('./img/icones/7x7/sort_desc.gif', "<?php echo _("Sort results (descendant)"); ?>");

	if (document.getElementById('host_name')){
		var h = document.getElementById('host_name');

		h.innerHTML = '<?php echo _("Hosts"); ?>';
	  	h.indice = 'host_name';
	  	h.title = '<?php echo _("Sort by host name"); ?>';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('service_description');
		h.innerHTML = '<?php echo _("Services"); ?>';
	  	h.indice = 'service_description';
	  	h.title = '<?php echo _("Sort by service description"); ?>';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('current_state');
		h.innerHTML = '<?php echo _("Status"); ?>';
	  	h.indice = 'current_state';
	  	h.title = '<?php echo _("Sort by status"); ?>';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('last_state_change');
		h.innerHTML = '<?php echo _("Duration"); ?>';
	  	h.indice = 'last_state_change';
	  	h.title = '<?php echo _("Sort by last change date"); ?>';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('last_check');
		h.innerHTML = '<?php echo _("Last Check"); ?>';
	  	h.indice = 'last_check';
	  	h.title = '<?php echo _("Sort by last check"); ?>';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('current_attempt');
		h.innerHTML = '<?php echo _("Tries"); ?>';
	  	h.indice = 'current_attempt';
	  	h.title = '<?php echo _("Sort by retries number"); ?>';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('plugin_output');
		h.innerHTML = '<?php echo _("Status information"); ?>';
	  	h.indice = 'plugin_output';
	  	h.title = '<?php echo _("Sort by plugin output"); ?>';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById(_sort_type);
		var _linkaction_asc = document.createElement("a");

		if (_order == 'ASC')
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

	initM(<?php echo $tM; ?>,"<?php echo $sid; ?>","<?php echo $o; ?>");
	_on = _tmp_on;

	viewDebugInfo('refresh');
}

function monitoring_play()	{
	document.getElementById('JS_monitoring_play').style.display = 'none';
	document.getElementById('JS_monitoring_pause').style.display = 'block';
	document.getElementById('JS_monitoring_pause_gray').style.display = 'none';
	document.getElementById('JS_monitoring_play_gray').style.display = 'block';
	_on = 1;
	initM(<?php echo $tM; ?>,"<?php echo $sid; ?>","<?php echo $o; ?>");
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
	if (!document.getElementById('debug')){
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

	if (_first){
		viewDebugInfo('--loop--');
		mainLoop();
		_first = 0;
	}
	_time=<?php echo $time; ?>;
	if (_on)
		goM(_time_reload,_sid,_o);
}

function goM(_time_reload, _sid, _o){
	//viewDebugInfo('--Begin--');
	_lock = 1;
	var proc = new Transformation();
	var _addrXML = "./include/monitoring/status/Services/xml/serviceXML.php?"+'&sid='+_sid+'&search='+_search+'&search_type_host='+_search_type_host+'&search_type_service='+_search_type_service+'&num='+_num+'&limit='+_limit+'&sort_type='+_sort_type+'&order='+_order+'&date_time_format_status='+_date_time_format_status+'&o='+_o+'&p='+_p+'&host_name=<?php echo $host_name; ?>'+'&instance='+_instance+'&nc='+_nc;
	proc.setXml(_addrXML);
	proc.setXslt(_addrXSL);
	proc.transform("forAjax");

	_lock = 0;
	//viewDebugInfo('--End--');
	
	_timeoutID = setTimeout('goM("'+ _time_reload +'","'+ _sid +'","'+_o+'")', _time_reload);
	_time_live = _time_reload;
	_on = 1;
	
	set_header_title();
}

function displayPOPUP(id) {
	if (window.ActiveXObject) {
		viewDebugInfo('Internet Explorer');
	} else {
		viewDebugInfo('Recup span_'+id);
		var span = document.getElementById('span_'+id);
		var proc_popup = new Transformation();
		var _addrXMLSpan = "./include/monitoring/status/Services/xml/makeXMLForOneHost.php?"+'&sid='+_sid+'&host_id='+id;
		var _addrXSLSpan = "./include/monitoring/status/Services/xsl/popupForHost.xsl";
		proc_popup.setXml(_addrXMLSpan);
		proc_popup.setXslt(_addrXSLSpan);
		proc_popup.transform('span_'+id);
	
		//calcul auto de la largeur de l'ecran client
		var l = screen.availWidth;
		
		//calcul auto de la hauteur de l'ecran client
		var h = screen.availHeight;
		
		if (h - tempY < span.offsetHeight){
			span.style.top = '-'+ span.offsetHeight +'px';
		}
		viewDebugInfo('Display span_'+id);
	}
}

function displayPOPUP_svc(id){
	if (window.ActiveXObject) {
		viewDebugInfo('Internet Explorer');
	} else {
		viewDebugInfo('Recup span_'+id);
		var span = document.getElementById('span_'+id);
		var proc_popup = new Transformation();
		var _addrXMLSpan = "./include/monitoring/status/Services/xml/makeXMLForOneService.php?"+'&sid='+_sid+'&svc_id='+id;
		var _addrXSLSpan = "./include/monitoring/status/Services/xsl/popupForService.xsl";
		proc_popup.setXml(_addrXMLSpan);
		proc_popup.setXslt(_addrXSLSpan);
		proc_popup.transform('span_'+id);
	
		// calcul auto de la largeur de l'ecran client
		var l = screen.availWidth;
		
		//calcul auto de la hauteur de l'ecran client
		var h = screen.availHeight;
	
		if (h - tempY < span.offsetHeight){
			span.style.top = '-'+ span.offsetHeight +'px';
		}
		viewDebugInfo('Display span_'+id);
	}
}

function hiddenPOPUP(id){
	if (window.ActiveXObject) {
		viewDebugInfo('Internet Explorer');
	} else {	
		var span = document.getElementById('span_'+id);
		span.innerHTML = '';
		viewDebugInfo('Hidde span_'+id);
	}
}

function displayIMG(index, s_id, id) {
	viewDebugInfo('Display IMG');
    if ( document.getElementById && document.getElementById( 'div_img' ) ){
    	// Pour les navigateurs recents
        Pdiv = document.getElementById( 'div_img' );
        PcH = true;
    } else if ( document.all && document.all[ 'div_img' ] ){
	    // Pour les veilles versions
        Pdiv = document.all[ 'div_img' ];
        PcH = true;
    } else if ( document.layers && document.layers[ 'div_img' ] ){
    	// Pour les tres veilles versions
        Pdiv = document.layers[ 'div_img' ];
        PcH = true;
    } else {
        PcH = false;
    }
    if (PcH){
		_img = mk_img('include/views/graphs/graphODS/generateImages/generateODSImage.php?session_id='+s_id+'&index='+index, 'graph popup'+'&index='+index+'&time=<?php print time(); ?>');
		Pdiv.appendChild(_img);
		var l=screen.availWidth; //calcul auto de la largeur de l'ecran client 
		var h=screen.availHeight; //calcul auto de la hauteur de l'ecran client 		
		var posy = tempY + 10;
		if (h - tempY < 420){
			posy = tempY-310;
		}
		Pdiv.style.display = "block";
		Pdiv.style.left = tempX +'px';
		Pdiv.style.top = posy +'px';
    }
    viewDebugInfo('End Display IMG');
}

function hiddenIMG(id) {
	viewDebugInfo('Hidde IMG');
    if (document.getElementById && document.getElementById( 'div_img' ) ){
		// Pour les navigateurs recents
        Pdiv = document.getElementById('div_img');
        PcH = true;
    } else if (document.all && document.all['div_img']){
    	// Pour les veilles versions
        Pdiv = document.all['div_img'];
        PcH = true;
    } else if (document.layers && document.layers['div_img']){
    	// Pour les tres veilles versions
        Pdiv = document.layers['div_img'];
        PcH = true;
    } else {
        PcH = false;
    }
    if (PcH) {
		Pdiv.style.display = "none";
		Pdiv.innerHTML = '';
	}
	viewDebugInfo('End : Hidde IMG');
}
</SCRIPT>