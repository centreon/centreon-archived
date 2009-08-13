/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * safety, contents, performance, merchantability, non-infringement or suitability for
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
*/

function _mk_img(_src, _alt){
	var _img = document.createElement("img");
  	_img.src = _src;
  	_img.alt = _alt;
  	_img.title = _alt;

	return _img;
}
	
function goToLog(_input_name, _id){
	var tab = getCheckedList(_input_name, _id);
	myHeader = document.getElementById("header");

	var _form = document.createElement("form");
	_form.setAttribute('id', 'goToLogByPost');
	_form.setAttribute('name', 'goToLogByPost');
	_form.setAttribute('method', 'POST');
	_form.setAttribute('action', 'main.php?p=203&mode=0');
	var _idValue = document.createElement("input");
	_idValue.type ='text';
	_idValue.value = tab;
	_idValue.name = _id;
	_form.appendChild(_idValue);
	myHeader.appendChild(_form);

	if (document.forms['goToLogByPost']){
		document.forms['goToLogByPost'].submit();	
	}
}

function goToGraph(_input_name, _id){
	var tab = getCheckedList(_input_name, _id); 	
	myHeader = document.getElementById("header");

	var _form = document.createElement("form");
	_form.setAttribute('id', 'goToGraphByPost');
	_form.setAttribute('name', 'goToGraphByPost');
	_form.setAttribute('method', 'POST');
	_form.setAttribute('action', 'main.php?p=40201&mode=0');
	var _idValue = document.createElement("input");
	_idValue.type ='text';
	_idValue.value = tab;
	_idValue.name = _id;
	_form.appendChild(_idValue);
	myHeader.appendChild(_form);

	if (document.forms['goToGraphByPost']){
		document.forms['goToGraphByPost'].submit();	
	}
}
function goToReport(_input_name, _id){
	var tab = getCheckedList(_input_name, _id);
	document.location.href='main.php?p=p=30702&period=today&'+_id+'=' +tab;  	
}

function goToIDCard(_input_name, _id){
	var tab = getCheckedList(_input_name, _id);
	document.location.href='main.php?p=70102&mode=0&'+_id+'=' +tab;  	
}

function goToMonitoring(_input_name, _id){
	var tab = getCheckedList(_input_name, _id);
	document.location.href='main.php?p=20201&o=svc&mode=0&'+_id+'=' +tab;
}

function create_report_link(_input_name, _id){
	var _img_report = _mk_img('./img/icones/16x16/column-chart.gif', "Reporting for the first svc selected");
	var _linkaction_report = document.createElement("a");
	_linkaction_report.href = '#';
	_linkaction_report.onclick=function(){goToReport(_input_name, _id)}
	_linkaction_report.appendChild(_img_report);
	return(_linkaction_report);
}

function create_graph_link(_input_name, _id){	
	var _img_graph = _mk_img('./img/icones/16x16/column-chart.gif', "Graph");
	var _linkaction_graph = document.createElement("a");
	_linkaction_graph.href = '#';
	_linkaction_graph.onclick=function(){goToGraph(_input_name, _id); false;}
	_linkaction_graph.appendChild(_img_graph);
	return(_linkaction_graph);	
}

function create_log_link(_input_name, _id){
	var _img_log = _mk_img('./img/icones/16x16/text_code_colored.gif', "Event Log");
	var _linkaction_log = document.createElement("a");
	_linkaction_log.href = '#';
	_linkaction_log.onclick=function(){goToLog(_input_name, _id); false; }
	_linkaction_log.appendChild(_img_log);
	return(_linkaction_log);
}

function create_IDCard_link(_input_name, _id){
	var _img_IDCard = _mk_img('./img/icones/16x16/text_code_c.gif', "IDCard for the first host selected");
	var _linkaction_IDCard = document.createElement("a");
	_linkaction_IDCard.href = '#';
	_linkaction_IDCard.onclick=function(){goToIDCard(_input_name, _id)}
	_linkaction_IDCard.appendChild(_img_IDCard);
	return(_linkaction_IDCard);
}

function create_monitoring_link(_input_name, _id){
	var _img_Monitoring = _mk_img('./img/icones/16x16/row.gif', "Monitoring for the all services selected");
	var _linkaction_Monitoring = document.createElement("a");
	_linkaction_Monitoring.href = '#';
	_linkaction_Monitoring.onclick=function(){goToMonitoring(_input_name, _id)}
	_linkaction_Monitoring.appendChild(_img_Monitoring);
	return(_linkaction_Monitoring);
}