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

	$tS = $oreon->optGen["AjaxTimeReloadStatistic"] * 1000;
	$tM = $oreon->optGen["AjaxTimeReloadMonitoring"] * 1000;
	$oreon->optGen["AjaxFirstTimeReloadStatistic"] == 0 ? $tFS = 10 : $tFS = $oreon->optGen["AjaxFirstTimeReloadStatistic"] * 1000;
	$oreon->optGen["AjaxFirstTimeReloadMonitoring"] == 0 ? $tFM = 10 : $tFM = $oreon->optGen["AjaxFirstTimeReloadMonitoring"] * 1000;
	$sid = session_id();
	$time = time();
	
?>
<script type="text/javascript">
var _debug = 0;

var _search = '<?php echo $search?>';
var _sid='<?php echo $sid?>';
var _num='<?php echo $num?>';
var _limit='<?php echo $limit?>';
var _sort_type='<?php echo $sort_type?>';
var _order='<?php echo $order?>';
var _date_time_format_status='<?php echo _("d/m/Y H:i:s")?>';
var _o='<?php echo $o?>';
var _p='<?php echo $p?>';

var _addrXML = "./include/monitoring/status/xml/hostXML.php?";
var _addrXSL = "./include/monitoring/status/Hosts/xsl/host.xsl";
var _timeoutID = 0;
var _on = 1;
var _time_reload = <?php echo $tM?>;
var _time_live = <?php echo $tFM?>;
var _nb = 0;
var _oldInputFieldValue = '<?php echo $search?>';
var _currentInputFieldValue="";
var _resultCache=new Object();
var _first = 1;
var _lock = 0;
var _instance = 'ALL';
var _default_instance = '<?php echo $default_poller?>';

<?php include_once "./include/monitoring/status/Common/commonJS.php"; ?>

var _selectedElem = new Array();

function set_header_title(){

	var _img_asc = mk_imgOrder('./img/icones/7x7/sort_asc.gif', "asc");
	var _img_desc = mk_imgOrder('./img/icones/7x7/sort_desc.gif', "desc");

	if(document.getElementById('host_name')){
		var h = document.getElementById('host_name');
		h.innerHTML = '<?php echo _("Hosts")?>';
	  	h.indice = 'host_name';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";
	
		var h = document.getElementById('current_state');
		h.innerHTML = '<?php echo _("Status")?>';
	  	h.indice = 'current_state';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('ip');
		h.innerHTML = '<?php echo _("IP Address")?>';
	  	h.indice = 'ip';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";
	
		var h = document.getElementById('last_state_change');
		h.innerHTML = '<?php echo _("Duration")?>';
	  	h.indice = 'last_state_change';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";
		
		var h = document.getElementById('last_check');
		h.innerHTML = '<?php echo _("Last Check")?>';
	  	h.indice = 'last_check';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";
	
		var h = document.getElementById('current_check_attempt');
		h.innerHTML = '<?php echo _("Tries")?>';
	  	h.indice = 'current_check_attempt';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";
	
		var h = document.getElementById('plugin_output');
		h.innerHTML = '<?php echo _("Status information")?>';
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
		viewDebugInfo('--INIT--');
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
	 _addrXML = "./include/monitoring/status/Hosts/xml/hostXML.php?"+'&sid='+_sid+'&search='+_search+'&num='+_num+'&limit='+_limit+'&sort_type='+_sort_type+'&order='+_order+'&date_time_format_status='+_date_time_format_status+'&o='+_o+'&p='+_p+'&instance='+_instance+'&time=<?php print time(); ?>';
	proc.setXml(_addrXML)
	proc.setXslt(_addrXSL)
	proc.transform("forAjax");
	_lock = 0;	
	_timeoutID = setTimeout('goM("'+ _time_reload +'","'+ _sid +'","'+_o+'")', _time_reload);
	_time_live = _time_reload;
	_on = 1;
	set_header_title();
}

function putInSelectedElem(id) {
	_selectedElem[id] = id;
}

function removeFromSelectedElem(id) {
	if (typeof(_selectedElem[id]) != 'undefined') {
    	_selectedElem[id] = undefined;
    }
}

function cmdCallback(cmd) {
	var keyz;

	_cmd = cmd;
    _getVar = "";
    if (cmd != '72') {
		return 1;
    }
    else {
    	for (keyz in _selectedElem) {
        	if (keyz == _selectedElem[keyz]) {
            	_getVar += '&select[' + keyz + ']=1';
            }
        }
        Modalbox.show('./include/monitoring/external_cmd/popup/popup.php?sid='+ _sid + '&o=' + _o + '&p='+ _p +'&cmd='+ cmd + _getVar, {title: 'Acknowledgement'});
        return 0;
    }
}

function send_the_command() {
	if (window.XMLHttpRequest) {
    	xhr_cmd = new XMLHttpRequest();
    }
    else if (window.ActiveXObject)
    {
    	xhr_cmd = new ActiveXObject("Microsoft.XMLHTTP");
    }
    var comment = document.getElementById('popupComment').value;
    var sticky = document.getElementById('sticky').checked;
    var persistent = document.getElementById('persistent').checked;
    var notify = document.getElementById('notify').checked;
    var ackhostservice = 0;
    if (document.getElementById('ackhostservice')) {
    	ackhostservice = document.getElementById('ackhostservice').checked;
    }
    var author = document.getElementById('author').value;

    xhr_cmd.open("GET", "./include/monitoring/external_cmd/cmdPopup.php?cmd=" + _cmd + "&comment=" + comment + "&sticky=" + sticky + "&persistent=" + persistent + "&notify=" + notify + "&ackhostservice=" + ackhostservice + "&author=" + author  + "&sid=" + _sid + _getVar, true);
    xhr_cmd.send(null);
	Modalbox.hide();
}

</SCRIPT>