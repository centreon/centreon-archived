
	function _mk_img(_src, _alt)
	{
		var _img = document.createElement("img");
	  	_img.src = _src;
	  	_img.alt = _alt;
	  	_img.title = _alt;
		return _img;
	}
		
	function goToLog(_input_name, _id)
	{
		var tab = getCheckedList(_input_name, _id);
//		document.location.href='oreon.php?p=203&mode=0&'+_id+'=' +tab;  

		myHeader = document.getElementById("header");

		var _form = document.createElement("form");
		_form.setAttribute('id', 'goToLogByPost');
		_form.setAttribute('name', 'goToLogByPost');
		_form.setAttribute('method', 'POST');
		_form.setAttribute('action', 'oreon.php?p=203&mode=0');
		var _idValue = document.createElement("input");
		_idValue.type ='text';
		_idValue.value = tab;
		_idValue.name = 'svc_id';
		_form.appendChild(_idValue);
		myHeader.appendChild(_form);

		if(document.forms['goToLogByPost']){
			document.forms['goToLogByPost'].submit();	
		}
	}
	function goToGraph(_input_name, _id)
	{
		var tab = getCheckedList(_input_name, _id);
		document.location.href='oreon.php?p=40211&mode=0&'+_id+'=' +tab;  	
	}
	function goToReport(_input_name, _id)
	{
		var tab = getCheckedList(_input_name, _id);
		document.location.href='oreon.php?p=p=30702&period=today&'+_id+'=' +tab;  	
	}
	function goToIDCard(_input_name, _id)
	{
		var tab = getCheckedList(_input_name, _id);
		document.location.href='oreon.php?p=70102&mode=0&'+_id+'=' +tab;  	
	}
	function goToMonitoring(_input_name, _id)
	{
		var tab = getCheckedList(_input_name, _id);
		document.location.href='oreon.php?p=20201&o=svc&mode=0&'+_id+'=' +tab;
	}

	function create_report_link(_input_name, _id){
		var _img_report = _mk_img('./img/icones/24x24/column-chart.png', "Reporting for the first svc selected");
		var _linkaction_report = document.createElement("a");
		_linkaction_report.href = '#';
		_linkaction_report.onclick=function(){goToReport(_input_name, _id)}
		_linkaction_report.appendChild(_img_report);
		return(_linkaction_report);
	
	}

	function create_graph_link(_input_name, _id){	
		var _img_graph = _mk_img('./img/icones/24x24/chart.png', "Graph");
		var _linkaction_graph = document.createElement("a");
		_linkaction_graph.href = '#';
		_linkaction_graph.onclick=function(){goToGraph(_input_name, _id)}
		_linkaction_graph.appendChild(_img_graph);
		return(_linkaction_graph);	
	}

	function create_log_link(_input_name, _id){
		var _img_log = _mk_img('./img/icones/24x24/text_find.png', "Event Log");
		var _linkaction_log = document.createElement("a");
		_linkaction_log.href = '#';
		_linkaction_log.onclick=function(){goToLog(_input_name, _id); false; }
		_linkaction_log.appendChild(_img_log);
		return(_linkaction_log);
	}
	function create_IDCard_link(_input_name, _id){
		var _img_IDCard = _mk_img('./img/icones/24x24/text_marked.png', "IDCard for the first host selected");
		var _linkaction_IDCard = document.createElement("a");
		_linkaction_IDCard.href = '#';
		_linkaction_IDCard.onclick=function(){goToIDCard(_input_name, _id)}
		_linkaction_IDCard.appendChild(_img_IDCard);
		return(_linkaction_IDCard);
	
	}
	function create_monitoring_link(_input_name, _id){
		var _img_Monitoring = _mk_img('./img/icones/24x24/table_selection_row.png', "Monitoring for the all services selected");
		var _linkaction_Monitoring = document.createElement("a");
		_linkaction_Monitoring.href = '#';
		_linkaction_Monitoring.onclick=function(){goToMonitoring(_input_name, _id)}
		_linkaction_Monitoring.appendChild(_img_Monitoring);
		return(_linkaction_Monitoring);
	
	}