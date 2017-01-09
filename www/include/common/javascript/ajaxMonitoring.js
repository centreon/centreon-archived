/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of Centreon choice, provided that 
 * Centreon also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

_debug = 0;
_nb = 0;

// ********************************************
// Récupération de paramètre d'une requête HTTP
// ou récupération des données d'un formulaire.
// Auteur : Oznog (www.trucsweb.com)
// ********************************************

// NE PAS MODIFIER CE CODE
var paramOk = true;

function FaitTableau(n) {
  // Création d'un tableau (array)
  // aux dimensions du nombre de paramètres.
  this.length = n;
  for (var i = 0; i <= n; i++) {
    this[i] = 0
  }
  return this
}

function ParamValeur(nValeur) {
  // Récupération de la valeur d'une variable
  // Pour créer la variable en Javascript.
  var nTemp = "";
  for (var i=0;i<(param.length+1);i++) {
    if (param[i].substring(0,param[i].indexOf("=")) == nValeur)
      nTemp = param[i].substring(param[i].indexOf("=")+1,param[i].length)
  }
  return Decode(nTemp)
}

// Extraction des paramètres de la requête HTTP
// et initialise la variable "paramOk" à false
// s'il n'y a aucun paramètre.
if (!location.search) {
  paramOk = false;
}
else {
  // Éliminer le "?"
  nReq = location.search.substring(1,location.search.length)
  // Extrait les différents paramètres avec leur valeur.
  nReq = nReq.split("&");
  param = new FaitTableau(nReq.length-1)
  for (var i=0;i<(nReq.length);i++) {
    param[i] = nReq[i]
  }
}

// Décoder la requête HTTP
// manuellement pour le signe (+)
function Decode(tChaine) {
  while (true) {
    var i = tChaine.indexOf('+');
    if (i < 0) break;
    tChaine = tChaine.substring(0,i) + '%20' + tChaine.substring(i + 1, tChaine.length);
  }
  return unescape(tChaine)
}
// End -->


// JavaScript Document

 function getVar (nomVariable)
 {
	 var infos = location.href.substring(location.href.indexOf("?")+1, location.href.length)+"&"
	 if (infos.indexOf("#")!=-1)
	 infos = infos.substring(0,infos.indexOf("#"))+"&"
	 var variable='none'
	 {
	 nomVariable = nomVariable + "="
	 var taille = nomVariable.length
	 if (infos.indexOf(nomVariable)!=-1)
	 variable = infos.substring(infos.indexOf(nomVariable)+taille,infos.length).substring(0,infos.substring(infos.indexOf(nomVariable)+taille,infos.length).indexOf("&"))
	 }
	 return variable
 }


//var xhrM = null;
var _addrSearchM = "./include/monitoring/engine/MakeXML.php" //l'adresse   interroger pour trouver les suggestions
var _timeoutID =	0;
var _on = 1;

function getXhrM(){
	if(window.XMLHttpRequest) // Firefox et autres
	   var xhrM = new XMLHttpRequest();
	else if(window.ActiveXObject){ // Internet Explorer
	   try {
                var xhrM = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                var xhrM = new ActiveXObject("Microsoft.XMLHTTP");
            }
	}
	else { // XMLHttpRequest non supportÃ¯Â¿Âœ par le navigateur
	   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
	   var xhrM = false;
	}
	return xhrM;
}

function take_value(_type)
{
	var myArray=new Array();

	j=0
	for(i=0; document.getElementById('host'+i) ;i++)
	{
		myArray[j++] = _documentDiv=document.getElementById('host'+i).innerHTML;
		if(_type == "service" || _type == "service_problem")
			myArray[j++] = _documentDiv=document.getElementById('service'+i).innerHTML;
	}
return myArray;
}

function DelAllLine(i)
{
	for(i; document.getElementById('trStatus'+i) ;i++)
	{
		var _del = document.getElementById('trStatus'+i);
		_del.parentNode.removeChild(_del);
	}
}

function removeAllLine(i)
{
	for(i=0; document.getElementById('trStatus'+i) ;i++)
	{
		var _del = document.getElementById('trStatus'+i);
		_del.parentNode.removeChild(_del);
	}

}

function DelOneLine(i)
{
	if(document.getElementById('trStatus'+i))
	{
		var _del = document.getElementById('trStatus'+i);
		_del.parentNode.removeChild(_del);
	}
}

function mk_img(_src, _alt)
{
	var _img = document.createElement("img");
  	_img.src = _src;
  	_img.alt = _alt;
  	_img.title = _alt;
	return _img;
}


function addLineToTab_Service(tableCheckbox, _tableAjax, line, i, _form, _formBasic, _previous_host_name, _o){

	var _host_name = line.getElementsByTagName("host_name")[0].firstChild.nodeValue;
	var _host_color = line.getElementsByTagName("host_color")[0].firstChild.nodeValue;
	var _last_check = line.getElementsByTagName("last_check")[0].firstChild.nodeValue;
	var _last_state_change = line.getElementsByTagName("last_state_change")[0].firstChild.nodeValue;
	var _status = line.getElementsByTagName("current_state")[0].firstChild.nodeValue;
	var _plugin_output = line.getElementsByTagName("plugin_output")[0].firstChild.nodeValue;
	var _service_description = line.getElementsByTagName("service_description")[0].firstChild.nodeValue;
	var _notifications_enabled = line.getElementsByTagName("notifications_enabled")[0].firstChild.nodeValue;
	var _problem_has_been_acknowledged = line.getElementsByTagName("problem_has_been_acknowledged")[0].firstChild.nodeValue;
	var _is_flapping = line.getElementsByTagName("is_flapping")[0].firstChild.nodeValue;
	var _order = line.getElementsByTagName("order")[0].firstChild.nodeValue;
	var _current_attempt = line.getElementsByTagName("current_attempt")[0].firstChild.nodeValue;
	var _accept_passive_check = line.getElementsByTagName("accept_passive_check")[0].firstChild.nodeValue;
	var _accept_active_check = line.getElementsByTagName("accept_active_check")[0].firstChild.nodeValue;
	var _event_handler_enabled = line.getElementsByTagName("event_handler_enabled")[0].firstChild.nodeValue;
	var _host_status = line.getElementsByTagName("host_status")[0].firstChild.nodeValue;
	var _host_has_been_acknowledged = line.getElementsByTagName("host_has_been_acknowledged")[0].firstChild.nodeValue;
    var _host_active_checks_enabled = line.getElementsByTagName("host_active_checks_enabled")[0].firstChild.nodeValue;
    var _host_passive_checks_enabled = line.getElementsByTagName("host_passive_checks_enabled")[0].firstChild.nodeValue;
    var _host_notifications_enabled = line.getElementsByTagName("host_notifications_enabled")[0].firstChild.nodeValue;
    var _host_downtime_depth = line.getElementsByTagName("host_downtime_depth")[0].firstChild.nodeValue;
    var _service_downtime_depth = line.getElementsByTagName("service_downtime_depth")[0].firstChild.nodeValue;

	if(_form.search && _form.search.value)
		_search=_form.search.value;
	else
	_search = "";

	if(_form.search_type_service && _form.search_type_service.value)
	var _search_type_service=_form.search_type_service.value;
	else
	var _search_type_service= "";

	if(_form.search_type_host && _form.search_type_host.value)
	var _search_type_host=_form.search_type_host.value;
	else
	var _search_type_host="";

	if(_form.num && _form.num.value)
	var _num=_form.num.value;
	else
	var _num="";

	if(_form.limit && _form.limit.value)
	var _limit=_form.limit.value;
	else
	var _limit="";

	if(_form.order && _form.order.value)
	var _order=_form.order.value;
	else
	var _order="";

	if(_form.sort_types && _form.sort_types.value)
	var _sort_types=_form.sort_types.value;
	else
	var _sort_types="";

	var _ligne = document.createElement('tr');
	_ligne.id = 'trStatus'+ i;
	var ClassName = "list_one";
	if(i%2)
	{
		ClassName = "list_two";
	}
//	_ligne.className = ClassName;



/*
 * checkbox
 */
	var _case_checkbox = document.createElement('td');
	_case_checkbox.className = 'ListColPicker';
	var cbx = document.createElement("input");
  	cbx.type = "checkbox";
  	cbx.id = "myCBX";
  	cbx.value = "1";

	if(tableCheckbox['select['+_host_name+';'+_service_description+']'])
		cbx.checked = tableCheckbox['select['+_host_name+';'+_service_description+']'];
	cbx.name = 'select['+_host_name+';'+_service_description+']';
	_case_checkbox.appendChild(cbx);

/*
 * host_name
 */
	var _case_host_name = document.createElement('td');
	_case_host_name.className = 'ListColLeft';
	if(_host_status == "DOWN" && _host_color != 'normal')
	{
		_case_host_name.style.backgroundColor = _host_color;
	}


/*
 * service description
 */
	var _case_service_description = document.createElement('td');
	_case_service_description.className = 'ListColLeft';

/*
 * actions
 */
	var _case_actions = document.createElement('td');
	_case_actions.className = 'ListColRight';

/*
 * infos
 */
	var _case_infos = document.createElement('td');
	_case_infos.className = 'ListColRight';


/*
 * status
 */
	var _case_status = document.createElement('td');
	_case_status.className = 'ListColCenter';
	switch (_status)
	{
	  case "OK":
	    _case_status.style.backgroundColor = _formBasic.color_OK.value;
	   break;
	  case "WARNING":
	    _case_status.style.backgroundColor = _formBasic.color_WARNING.value;
	   break;
	  case "CRITICAL":
	    _case_status.style.backgroundColor = _formBasic.color_CRITICAL.value;
		_ligne.className = "list_down";
	   break;
	  case "UNDETERMINATED":
	    _case_status.style.backgroundColor = _formBasic.color_UNDETERMINATED.value;
	   break;
	  default:
	    _case_status.style.backgroundColor = _formBasic.color_UNKNOWN.value;
	   break;
	}

/*
 * laste check
 */
	var _case_lastcheck = document.createElement('td');
	_case_lastcheck.className = 'ListColRight';
	_case_lastcheck.noWrap = true;

/*
 * last change
 */
	var _case_time = document.createElement('td');
	_case_time.className = 'ListColRight';
	_case_time.noWrap = true;

/*
 * current_state
 */
	var _case_current_attempt = document.createElement('td');
	_case_current_attempt.className = 'ListColCenter';

/*
 * plugin_output
 */
	var _case_plugin_output = document.createElement('td');
	_case_plugin_output.className = 'ListColNoWrap';

	var _img1 = mk_img(_formBasic.icone_problem_has_been_acknowledged.value, "problem_has_been_acknowledged");
	var _img2 = mk_img(_formBasic.icone_notifications_enabled.value, "notification_enable");
	var _img3 = mk_img(_formBasic.icone_is_flapping.value, "is_flapping");
	var _img4 = mk_img(_formBasic.icone_accept_passive_check1.value, "accept_active_check");
	var _img5 = mk_img(_formBasic.icone_accept_passive_check0.value, "accept_pasive_check");
	var _img6 = mk_img(_formBasic.icone_graph.value, "graph");
	var _img7 = mk_img(_formBasic.icone_undo.value, "re-check");
	var _img8 = mk_img(_formBasic.icone_host_has_been_acknowledged.value, "host_has_been_acknowledged");
	var _img9 = mk_img(_formBasic.icon_downtime.value, "downtime_depth");


	if(_status == "CRITICAL"){
		ClassName = "list_down";
	}

	if(_problem_has_been_acknowledged == 1)
	{
	ClassName = "list_four";
	_case_infos.appendChild(_img1);
	}
	if(_is_flapping == 1)
	_case_infos.appendChild(_img3);

	if(_accept_passive_check == 1)
	_case_infos.appendChild(_img5);

	if(_accept_active_check == 1)
	_case_infos.appendChild(_img4);

	if(_notifications_enabled == 0)
	_case_infos.appendChild(mk_img(_formBasic.icone_notifications_enabled.value, "notification_enable"));

	if(_service_downtime_depth != 0)
	_case_infos.appendChild(_img9);


_case_infos.id = 'infos' + i;

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!p!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
var _p = 20201;

/*
 * actions
 */

	var _linkaction_graph = document.createElement("a");
  	_linkaction_graph.href = './main.php?p=40207&host_name=' + _host_name + '&service_description=' + _service_description + '&submitC=Grapher';
	_linkaction_graph.appendChild(_img6);

	_case_actions.appendChild(_linkaction_graph);
	_case_actions.id = 'action' + i;


/*
 * host name
 */

	var _text_host_name = document.createTextNode(_host_name);

	var _divhost_name = document.createElement("div");
	_divhost_name.id = 'host_name'+i;
	_divhost_name.className = 'cachediv';
	_divhost_name.appendChild(_text_host_name);

	_case_host_name.appendChild(_divhost_name);


	if(_previous_host_name != _host_name)
	{
		var _text_host_name = document.createTextNode(_host_name+' ');
		var _linkhost_name = document.createElement("a");
  		_linkhost_name.href = './main.php?p=201&o=hd&host_name=' + _host_name;
		_linkhost_name.appendChild(_text_host_name);
		_case_host_name.appendChild(_linkhost_name);

		if(_host_has_been_acknowledged == 1)
		{
			_case_host_name.appendChild(_img8);
		}
        if(_host_active_checks_enabled == 0 && _host_passive_checks_enabled == 0)
        {
                _case_host_name.appendChild(_img4);
        }
        if(_host_active_checks_enabled == 0 && _host_passive_checks_enabled == 1)
        {
                _case_host_name.appendChild(_img5);
        }
        if(_host_downtime_depth != 0)
        {
                _case_host_name.appendChild(_img9);
        }
	}
	else
	{
		var _text_host_name = document.createTextNode(' ');
	}


/*
 * service description
 */
	var _text_service_description = document.createTextNode(_service_description);
	var _text_service_description2 = document.createTextNode(_service_description);
	var _linkservice = document.createElement("a");
  	_linkservice.href = './main.php?p=202&o=svcd&host_name=' + _host_name + '&service_description=' +_service_description;
	_linkservice.appendChild(_text_service_description);

	var _divservice = document.createElement("div");
	_divservice.id = 'service'+i;
	_divservice.className = 'cachediv';
	_divservice.appendChild(_text_service_description2);

	_case_service_description.appendChild(_linkservice);
	_case_service_description.appendChild(_divservice);




	var _text_status = document.createTextNode(_status);
	var _text_last_check = document.createTextNode(_last_check);
	var _text_last_state_change = document.createTextNode(_last_state_change);
	var _text_current_attempt = document.createTextNode(_current_attempt);
	var _text_plugin_output = document.createTextNode(_plugin_output);



	var _divstatus = document.createElement("div");
	_divstatus.id = 'status'+i;
	_divstatus.appendChild(_text_status);

	_case_status.appendChild(_divstatus);

	_case_status.id = 'tdStatus' + i;
	_case_lastcheck.appendChild(_text_last_check);
	_case_lastcheck.id = 'last_check' + i;
	_case_time.appendChild(_text_last_state_change);
	_case_time.id = 'last_state_change' + i;
	_case_current_attempt.appendChild(_text_current_attempt);
	_case_current_attempt.id = '_current_attempt' + i;
	_case_plugin_output.appendChild(_text_plugin_output);
	_case_plugin_output.id = 'plugin_output' + i;

	_ligne.className = ClassName;
	_ligne.appendChild(_case_checkbox);
	_ligne.appendChild(_case_host_name);
	_ligne.appendChild(_case_service_description);
	_ligne.appendChild(_case_actions);
	_ligne.appendChild(_case_infos);
	_ligne.appendChild(_case_status);
	_ligne.appendChild(_case_lastcheck);
	_ligne.appendChild(_case_time);
	_ligne.appendChild(_case_current_attempt);
	_ligne.appendChild(_case_plugin_output);

	_tableAjax.appendChild(_ligne);

}

/*function viewDebugInfo(_str){
	if(_debug)
	{
		_nb = _nb + 1;
		var mytable=document.getElementById("debugtable")
		var newrow=mytable.insertRow(0) //add new row to end of table
		var newcell=newrow.insertCell(0) //insert new cell to row
		newcell.innerHTML='<td>line:' + _nb + ' ' + _str + '</td>';
	}
}*/

function initM(_time_reload,_sid,_o){
	_form=document.getElementById('fsave');
	_time=parseInt(_form.time.value);
	_form.time.value = _time - 1000;


	/*if(document.getElementById('debug'))
	{
		viewDebugInfo('--RESTART--');
		viewDebugInfo('');
	}
	else{
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
	}*/
	goM(_time_reload,_sid,_o);
}

function goM(_time_reload,_sid,_o){
	// ici je recupere les couples host_name/service affichÃ�Â© sur ma page
	viewDebugInfo('entre dans goM');
	if(_on)
	{
	_host_name = 'none';
	_host_name = getVar('host_name');
	_formBasic=document.getElementById('AjaxBankBasic');
	_version=_formBasic.version.value;
	_fileStatus=_formBasic.fileStatus.value;
	_fileCentreonConf=_formBasic.fileCentreonConf.value;
	_date_time_format_status = _formBasic.date_time_format_status.value;
	_form=document.getElementById('fsave');
	_form.len.value = parseInt(_form.len.value) + 1;
	_smaxtime = parseInt(_form.smaxtime.value);
	//_time=parseInt(_form.time.value);
	_order=_form.order.value;
	_sort_types=_form.sort_types.value;
	_type=_form.type.value;
	_version=_formBasic.version.value;
	_fileStatus=_formBasic.fileStatus.value;
	_fileCentreonConf=_formBasic.fileCentreonConf.value;
	_limit=_form.limit.value;
	if(_form.search && _form.search.value)
		_search=_form.search.value;
	else
	_search = "";
	_search_type_service=_form.search_type_service.value;
	_search_type_host=_form.search_type_host.value;
	_num=_form.num.value;
	_previous_host_name = '';

	_hg_name = '';
	if (paramOk) {
	  _hg_name = ParamValeur("hg_name");
	}
	var myArray = take_value(_type);
	_log = document.getElementById('log');
	var _tableforajax = document.getElementById('forajax');
	var _tableAjax = null;

    var childrenNumber = _tableforajax.childNodes.length;
    for (var i = 0; i < childrenNumber; i++)
    {
		var element = _tableforajax.childNodes[i];
      	var elementName = element.nodeName.toLowerCase();
		if (elementName == 'table')
		{
		    var childrenNumbertable = element.childNodes.length;
		    for (var j = 0; j < childrenNumbertable; j++)
		    {
				var elementtable = element.childNodes[j];
		  		var elementNametable = elementtable.nodeName.toLowerCase();
				if (elementNametable == 'tbody')
				{
					_tableAjax = elementtable;
				}
			}
		}
	}


	var table = document.getElementsByTagName("input");
	var tableCheckbox = new Array();
	for(var i=0;i<table.length;i++){
		if(table[i].type == 'checkbox'){
			tableCheckbox[table[i].name] = table[i].checked;
		}
}



	var xhrM = getXhrM();
	xhrM.open("POST",_addrSearchM,true);
	xhrM.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	_var = "hg_name="+_hg_name+"&host_name="+_host_name+"&date_time_format_status="+_date_time_format_status+"&search_type_service="+_search_type_service+"&search_type_host="+_search_type_host+"&order="+_order+"&sort_type="+_sort_types+"&arr="+myArray + "&num="+_num+"&search="+_search+"&limit="+_limit+"&fileStatus="+_fileStatus+"&fileCentreonConf="+_fileCentreonConf+"&version="+_version+"&type="+_o+"&smaxtime="+parseInt(_form.smaxtime.value)+"&time="+parseInt(_form.time.value);
	xhrM.send(_var);
//	document.getElementById('header').innerHTML = "-->"+_var;
	// On defini ce qu'on va faire quand on aura la reponse
	xhrM.onreadystatechange = function()
	{
		// On ne fait quelque chose que si on a tout recu et que le serveur est ok
		if(xhrM && xhrM.readyState && xhrM.readyState == 4 && xhrM.status == 200 && xhrM.responseXML)
		{
			reponse = xhrM.responseXML.documentElement;
			var infos = reponse.getElementsByTagName("infos");
			for (var i = 0 ; i < infos.length ; i++) {
				var info = infos[i];
				var _atime = info.getElementsByTagName("time")[0].firstChild.nodeValue;
				var _filetime = info.getElementsByTagName("filetime")[0].firstChild.nodeValue;
				if(_atime && _form.time)
					_form.time.value = parseInt(_atime);
			}
			// a partir d'ici je recupere les informations principales
			var lines = reponse.getElementsByTagName("line");
			var order =0;
//			if(lines.length > 0){
				DelAllLine(0);
//			}
			for (var i = 0 ; i < lines.length ; i++)
			{
				var line = lines[i];
				order = line.getElementsByTagName("order")[0].firstChild.nodeValue;
				var _flag = parseInt(line.getElementsByTagName("flag")[0].firstChild.nodeValue);
				if((_type == 'service' || _type == 'service_problem') )//&& _flag == 1)
				{
					addLineToTab_Service(tableCheckbox, _tableAjax, line, i, _form,_formBasic, _previous_host_name, _o);
					_previous_host_name = line.getElementsByTagName("host_name")[0].firstChild.nodeValue;
				}
			}//fin du for pour les infos principale
		}
			viewDebugInfo('readyState=' + xhrM.readyState + ' -- status=' + xhrM.status);
	}

	_timeoutID = setTimeout('goM("'+ _time_reload +'","'+ _sid +'","'+_o+'")', _time_reload);
	_time_live = _time_reload;
	_on = 1;
}
}