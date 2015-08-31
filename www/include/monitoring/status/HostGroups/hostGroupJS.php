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

?>
<script type="text/javascript">
var _debug = 0;


var _addrXML = "./include/monitoring/status/HostGroups/xml/<?php print $centreon->broker->getBroker(); ?>/hostGroupXML.php?";
var _addrXSL = "./include/monitoring/status/HostGroups/xsl/hostGroup.xsl";

<?php include_once "./include/monitoring/status/Common/commonJS.php"; ?>

function set_header_title(){
	var _img_asc = mk_imgOrder('./img/icones/7x7/sort_asc.gif', "asc");
	var _img_desc = mk_imgOrder('./img/icones/7x7/sort_desc.gif', "desc");

	if(document.getElementById('hostGroup_name')){
		var h = document.getElementById('hostGroup_name');
		h.innerHTML = "<?php echo _("Host Group")?>";
	  	h.indice = 'hostGroup_name';
	  	h.onclick=function(){change_type_order(this.indice)};
		h.style.cursor = "pointer";

		var h = document.getElementById('host_status');
		h.innerHTML = '<?php echo addslashes(_("Hosts Status"))?>';
	  	h.indice = 'host_status';

		var h = document.getElementById('service_status');
		h.innerHTML = '<?php echo addslashes(_("Services Status"))?>';
	  	h.indice = 'service_status';

		var h = document.getElementById(_sort_type);
		var _linkaction_asc = document.createElement("a");
		if (_order == 'ASC') {
			_linkaction_asc.appendChild(_img_asc);
		} else {
			_linkaction_asc.appendChild(_img_desc);
		}
		_linkaction_asc.href = '#' ;
		_linkaction_asc.onclick=function(){change_order()};
		h.appendChild(_linkaction_asc);
	}
}

function goM(_time_reload, _sid ,_o) {
	_lock = 1;
	var proc = new Transformation();

	proc.setXml(_addrXML+"?"+'&sid='+_sid+'&search='+_search+'&num='+_num+'&limit='+_limit+'&sort_type='+_sort_type+'&order='+_order+'&date_time_format_status='+_date_time_format_status+'&o='+_o+'&p='+_p+'&instance='+_instance+'&time=<?php print time(); ?>')
	proc.setXslt(_addrXSL);
	proc.setCallback(monitoringCallBack);
	proc.transform("forAjax");
	_lock = 0;
	_timeoutID = setTimeout('goM("'+ _time_reload +'","'+ _sid +'","'+_o+'")', _time_reload);
	_time_live = _time_reload;
	_on = 1;
	set_header_title();
}
</SCRIPT>