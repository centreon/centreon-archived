<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
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

	if($num < 0)
		$num =0;
?>
<script type="text/javascript" src="./include/common/javascript/LinkBar.js"></script>
<script type="text/javascript">
	var _debug = 0;
	var _addrXSL = "./include/monitoring/status/Meta/xsl/metaService.xsl";

<?php include_once "./include/monitoring/status/Common/commonJS.php"; ?>

function set_header_title(){

	var _img_asc  = mk_imgOrder('./img/icones/7x7/sort_asc.gif', "<?php echo _("Sort results (ascendant)"); ?>");
	var _img_desc = mk_imgOrder('./img/icones/7x7/sort_desc.gif', "<?php echo _("Sort results (descendant)"); ?>");

	if (document.getElementById('service_description')){

		var h = document.getElementById('service_description');
		h.innerHTML = '<?php echo addslashes(_("Meta Services")); ?>';
	  	h.indice = 'service_description';
	  	h.title = '<?php echo addslashes(_("Sort by Name")); ?>';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('current_state');
		h.innerHTML = '<?php echo addslashes(_("Status")); ?>';
	  	h.indice = 'current_state';
	  	h.title = '<?php echo addslashes(_("Sort by status")); ?>';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('last_state_change');
		h.innerHTML = '<?php echo addslashes(_("Duration")); ?>';
	  	h.indice = 'last_state_change';
	  	h.title = '<?php echo addslashes(_("Sort by last change date")); ?>';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('last_check');
		h.innerHTML = '<?php echo addslashes(_("Last Check")); ?>';
	  	h.indice = 'last_check';
	  	h.title = '<?php echo addslashes(_("Sort by last check")); ?>';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('current_attempt');
		h.innerHTML = '<?php echo addslashes(_("Tries")); ?>';
	  	h.indice = 'current_attempt';
	  	h.title = '<?php echo addslashes(_("Sort by retries number")); ?>';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";
		var _selectedElem = new Array();
		var h = document.getElementById('plugin_output');
		h.innerHTML = '<?php echo addslashes(_("Status information")); ?>';
	  	h.indice = 'plugin_output';
	  	h.title = '<?php echo addslashes(_("Sort by plugin output")); ?>';
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

function goM(_time_reload,_sid,_o){

	_lock = 1;
	var proc = new Transformation();

	if (_counter == 0) {
		document.getElementById("input_search").value = _search;
		_counter++;
	}

	var _addrXML = "./include/monitoring/status/Meta/xml/<?php print $centreon->broker->getBroker(); ?>/metaServiceXML.php?"+'&sid='+_sid+'&search='+_search+'&num='+_num+'&limit='+_limit+'&sort_type='+_sort_type+'&order='+_order+'&date_time_format_status='+_date_time_format_status+'&o='+_o+'&p='+_p+'&host_name=<?php echo $host_name; ?>'+'&instance='+_instance+'&nc='+_nc;
	proc.setCallback(monitoringCallBack);
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
</SCRIPT>