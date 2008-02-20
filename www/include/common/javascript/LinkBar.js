
	function getCheckedList(_input_name)
	{
		var mesinputs = document.getElementsByTagName("input" );
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
	
	
	function goToLog(_input_name)
	{
		var tab = getCheckedList(_input_name);
		document.location.href='oreon.php?p=203&mode=0&id_svc=' +tab;  
	}
	function goToGraph(_input_name)
	{
		var tab = getCheckedList(_input_name);
		document.location.href='oreon.php?p=40211&mode=0&id_svc=' +tab;  	
	}
	function goToReport(_input_name)
	{
		var tab = getCheckedList(_input_name);
		document.location.href='oreon.php?p=p=30702&period=today&svctab=' +tab;  	
	}
	function goToIDCard(_input_name)
	{
		var tab = getCheckedList(_input_name);
		document.location.href='oreon.php?p=70102&mode=0&id_svc=' +tab;  	
	}
	function goToMonitoring(_input_name)
	{
		var tab = getCheckedList(_input_name);
		document.location.href='oreon.php?p=20201&o=svc&mode=0&id_svc=' +tab;
	}

	function create_report_link(_input_name){
		var _img_report = mk_img('./img/icones/24x24/column-chart.png', "Reporting for the first svc selected");
		var _linkaction_report = document.createElement("a");
		_linkaction_report.href = '#';
		_linkaction_report.onclick=function(){goToReport(_input_name)}
		_linkaction_report.appendChild(_img_report);
		return(_linkaction_report);
	
	}

	function create_graph_link(_input_name){	
		var _img_graph = mk_img('./img/icones/24x24/chart.png', "Graph");
		var _linkaction_graph = document.createElement("a");
		_linkaction_graph.href = '#';
		_linkaction_graph.onclick=function(){goToGraph(_input_name)}
		_linkaction_graph.appendChild(_img_graph);
		return(_linkaction_graph);	
	}

	function create_log_link(_input_name){
		var _img_log = mk_img('./img/icones/24x24/text_find.png', "Event Log");
		var _linkaction_log = document.createElement("a");
		_linkaction_log.href = '#';
		_linkaction_log.onclick=function(){goToLog(_input_name)}
		_linkaction_log.appendChild(_img_log);
		return(_linkaction_log);
	
	}
	function create_IDCard_link(_input_name){
		var _img_IDCard = mk_img('./img/icones/24x24/text_marked.png', "IDCard for the first host selected");
		var _linkaction_IDCard = document.createElement("a");
		_linkaction_IDCard.href = '#';
		_linkaction_IDCard.onclick=function(){goToIDCard(_input_name)}
		_linkaction_IDCard.appendChild(_img_IDCard);
		return(_linkaction_IDCard);
	
	}
	function create_monitoring_link(_input_name){
		var _img_Monitoring = mk_img('./img/icones/24x24/text_marked.png', "IDCard for the first host selected");
		var _linkaction_Monitoring = document.createElement("a");
		_linkaction_Monitoring.href = '#';
		_linkaction_Monitoring.onclick=function(){goToMonitoring(_input_name)}
		_linkaction_Monitoring.appendChild(_img_Monitoring);
		return(_linkaction_Monitoring);
	
	}