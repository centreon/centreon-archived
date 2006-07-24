/**
Oreon is developped with Apache Licence 2.0 :
http://www.apache.org/licenses/LICENSE-2.0.txt
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
// JavaScript Document

var xhr = null; 
var _adresseRecherche = "./include/monitoring/status/readStatus.php" //l'adresse   interroger pour trouver les suggestions
	 
	 		function getXhr(){
				if(window.XMLHttpRequest) // Firefox et autres
				   xhr = new XMLHttpRequest(); 
				else if(window.ActiveXObject){ // Internet Explorer 
				   try {
			                xhr = new ActiveXObject("Msxml2.XMLHTTP");
			            } catch (e) {
			                xhr = new ActiveXObject("Microsoft.XMLHTTP");
			            }
				}
				else { // XMLHttpRequest non support� par le navigateur 
				   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
				   xhr = false; 
				} 
			}

function take_value(_type)
{
	var myArray=new Array();

	j=0
	for(i=0; document.getElementById('host'+i) ;i++)
	{
		myArray[j++] = _documentDiv=document.getElementById('host'+i).innerHTML;
		if(_type == "service")
			myArray[j++] = _documentDiv=document.getElementById('service'+i).innerHTML;
	}
return myArray;
}


function init(){

	_form=document.getElementById('fsave');
	_time=parseInt(_form.time.value);
	_form.time.value = _time - 1000;

	go();
}

function go(){
	// ici je recupere les couples host/service affiché sur ma page
	
	_form=document.getElementById('fsave');		       
	_form.len.value = parseInt(_form.len.value) + 1;
	_slastreload = parseInt(_form.slastreload.value);
	_smaxtime = parseInt(_form.smaxtime.value);
	_time=parseInt(_form.time.value);
	_sid=_form.sid.value;
	_type=_form.type.value;
	_version=_form.version.value;
	_lca=_form.lca.value;
	_fileStatus=_form.fileStatus.value;
	_fileOreonConf=_form.fileOreonConf.value;

//document.getElementById('test').innerHTML += '';
				
	var myArray = take_value(_type);


_log = document.getElementById('log');


var _del = document.getElementById('trStatus0');

			_del.parentNode.removeChild(_del);


var _test = document.getElementById('test2');

_log.innerHTML = "la<br>";






    var childrenNumber = _test.childNodes.length;
    for (var i = 0; i < childrenNumber; i++) {
      var element = _test.childNodes[i];
      var elementName = element.nodeName.toLowerCase();


	if (elementName == 'table')
	{
//		var premiere_ligne = element.getElementsByTagName("tr")[0];
//		element.removeChild(premiere_ligne);
	
//		_log.innerHTML += premiere_ligne.innerHTML + "<br>";
	
		_log.innerHTML += elementName + "<br>";



	    var childrenNumbertable = element.childNodes.length;
	    for (var j = 0; j < childrenNumbertable; j++) {
	      var elementtable = element.childNodes[j];
	      var elementNametable = elementtable.nodeName.toLowerCase();



	
//		_log.innerHTML += "------" + elementNametable + "<br>";


	if (elementNametable == 'tbody')
	{	

		  var mdiv = document.createElement('div');
          mdiv.appendChild(document.createTextNode('meuh'));

			var _ligne = document.createElement('tr');
			var _case1 = document.createElement('td');
			var _case2 = document.createElement('td');

			var _texte1 = document.createTextNode('-1-');
			var _texte2 = document.createTextNode('*2*');
			_case1.appendChild(_texte1);
			_case2.appendChild(_texte2);
			_ligne.appendChild(_case1);
			_ligne.appendChild(_case2);
			elementtable.appendChild(_ligne);
	
	

		
			    var childrenNumbertableb = elementtable.childNodes.length;
			    for (var j = 0; j < childrenNumbertableb; j++) 
			    {
			      var elementtableb = elementtable.childNodes[j];
			      var elementNametableb = elementtableb.nodeName.toLowerCase();
					if (elementNametableb == 'tr')
					{	
						_log.innerHTML += "------ ------" + elementNametableb + "<br>";

					}



				}




		
		}
		}
		
	}
}

//_table.innerHTML = "beep!";




	getXhr()
	// On defini ce qu'on va faire quand on aura la reponse
	xhr.onreadystatechange = function()
	{	
		// On ne fait quelque chose que si on a tout recu et que le serveur est ok
		if(xhr.readyState == 4 && xhr.status == 200 && xhr.responseXML)
		{		
			reponse = xhr.responseXML.documentElement;
			_test=document.getElementById('test');
			
			var infos = reponse.getElementsByTagName("infos");
			for (var i = 0 ; i < infos.length ; i++) {
				var info = infos[i];
				var _atime = info.getElementsByTagName("time")[0].firstChild.nodeValue;
				var _newreload = info.getElementsByTagName("flag")[0].firstChild.nodeValue;
				var _filetime = info.getElementsByTagName("filetime")[0].firstChild.nodeValue;
				_form.time.value = parseInt(_atime);
				_form.slastreload.value = parseInt(_newreload);
			}

			// ici je recupere les statistiques
			var stats = reponse.getElementsByTagName("stats");
			for (var i = 0 ; i < stats.length ; i++) {
				var stat = stats[i];
				var _statistic_service_ok = stat.getElementsByTagName("statistic_service_ok")[0].firstChild.nodeValue;
				var _statistic_service_warning = stat.getElementsByTagName("statistic_service_warning")[0].firstChild.nodeValue;
				var _statistic_service_critical = stat.getElementsByTagName("statistic_service_critical")[0].firstChild.nodeValue;
				var _statistic_service_unknown = stat.getElementsByTagName("statistic_service_unknown")[0].firstChild.nodeValue;
				var _statistic_service_pending = stat.getElementsByTagName("statistic_service_pending")[0].firstChild.nodeValue;
				var _statistic_host_up = stat.getElementsByTagName("statistic_host_up")[0].firstChild.nodeValue;
				var _statistic_host_down = stat.getElementsByTagName("statistic_host_down")[0].firstChild.nodeValue;
				var _statistic_host_unreachable = stat.getElementsByTagName("statistic_host_unreachable")[0].firstChild.nodeValue;
				var _statistic_host_pending = stat.getElementsByTagName("statistic_host_pending")[0].firstChild.nodeValue;

				if(_type != 'metaservice')
				{
					document.getElementById('host_up').innerHTML = _statistic_host_up;
					document.getElementById('host_down').innerHTML = _statistic_host_down;
					document.getElementById('host_unreachable').innerHTML = _statistic_host_unreachable;
					document.getElementById('host_pending').innerHTML = _statistic_host_pending;
					document.getElementById('service_ok').innerHTML = _statistic_service_ok;
					document.getElementById('service_warning').innerHTML = _statistic_service_warning;
					document.getElementById('service_critical').innerHTML = _statistic_service_critical;
					document.getElementById('service_unknown').innerHTML = _statistic_service_unknown;
					document.getElementById('service_pending').innerHTML = _statistic_service_pending;
				}
			}

//				document.getElementById('test').innerHTML += order;


			// a partir d'ici je recupere les informations principales
			var lines = reponse.getElementsByTagName("line");
			var order =0;
			for (var i = 0 ; i < lines.length ; i++) 
			{
				var line = lines[i];
				order = line.getElementsByTagName("order")[0].firstChild.nodeValue;



				if(_type == 'service')
				{
					var _host = line.getElementsByTagName("host")[0].firstChild.nodeValue;								
					var _last_check = line.getElementsByTagName("last_check")[0].firstChild.nodeValue;
					var _last_change = line.getElementsByTagName("last_change")[0].firstChild.nodeValue;
					var _status = line.getElementsByTagName("status")[0].firstChild.nodeValue;
					var _output = line.getElementsByTagName("output")[0].firstChild.nodeValue;	
					var _service = line.getElementsByTagName("service")[0].firstChild.nodeValue;
					var _not_en = line.getElementsByTagName("not_en")[0].firstChild.nodeValue;
					var _pb_aknowledged = line.getElementsByTagName("pb_aknowledged")[0].firstChild.nodeValue;
					var _svc_is_flapping = line.getElementsByTagName("svc_is_flapping")[0].firstChild.nodeValue;

					var _infohtml = '';	
					if(_pb_aknowledged == 1)
						_infohtml += '<img src=' + _form.icone_pb_aknowledged.value + ' alt=&pb_aknowledged>';
					if(_not_en == 0)
						_infohtml += '<img src=' + _form.icone_not_en.value + ' alt=notification_enable>';
					if(_svc_is_flapping == 1)
						_infohtml += '<img src=' + _form.icone_flapping.value + ' alt=svc_is_flapping>';
					var _retry = line.getElementsByTagName("retry")[0].firstChild.nodeValue;
					var _accept_passive_check = line.getElementsByTagName("accept_passive_check")[0].firstChild.nodeValue;
					var _accept_active_check = line.getElementsByTagName("accept_active_check")[0].firstChild.nodeValue;
					var _ev_handler_en = line.getElementsByTagName("ev_handler_en")[0].firstChild.nodeValue;
	
					if(_accept_passive_check == 1)					
						_infohtml += '<img src=' + _form.icone_accept_passive_check1.value + ' alt=accept_passive_check>';
					if(_accept_active_check == 1)
						_infohtml += '<img src=' + _form.icone_accept_passive_check0.value + ' alt=accept_active_check>';					

					document.getElementById('infos'+order).innerHTML = _infohtml;

					
	
										
					document.getElementById('status'+order).innerHTML = _status;

					//bg color
					_td=document.getElementById('tdStatus'+order);
					_tr=document.getElementById('trStatus'+order);
					var ClassName = "list_one";
					if(i % 2)
						ClassName = "list_two";

					switch (_status)
					{
					  case "OK":
					    _td.style.backgroundColor = _form.color_OK.value;
						_tr.className = ClassName;
					   break;
					  case "WARNING":
					    _td.style.backgroundColor = _form.color_WARNING.value;
						_tr.className = ClassName;
					   break;
					  case "CRITICAL":
					    _td.style.backgroundColor = _form.color_CRITICAL.value;
						_tr.className = "list_three";
					   break;
					  case "UNDETERMINATED":
					    _td.style.backgroundColor = _form.color_UNDETERMINATED.value;
						_tr.className = ClassName;
					   break;
					  default:
					    _td.style.backgroundColor = _form.color_UNKNOWN.value;
						_tr.className = ClassName;
					   break;
					}
					document.getElementById('retry'+order).innerHTML = _retry;
					document.getElementById('last_check'+order).innerHTML = _last_check;
					document.getElementById('last_change'+order).innerHTML = _last_change;
					document.getElementById('output'+order).innerHTML = _output;					
				}



				if(_type == 'host')
				{
					var _host = line.getElementsByTagName("host")[0].firstChild.nodeValue;

					var _last_check = line.getElementsByTagName("last_check")[0].firstChild.nodeValue;
					var _last_change = line.getElementsByTagName("last_change")[0].firstChild.nodeValue;
					var _status = line.getElementsByTagName("status")[0].firstChild.nodeValue;
					var _output = line.getElementsByTagName("output")[0].firstChild.nodeValue;
	
					document.getElementById('status'+order).innerHTML = _status;
					_td=document.getElementById('tdStatus'+order);
					_tr=document.getElementById('trStatus'+order);
					var ClassName = "list_one";
					if(i % 2)
						ClassName = "list_two";

					switch (_status)
					{
					  case "UP":
					    _td.style.backgroundColor = _form.color_UP.value;
						_tr.className = ClassName;
					   break;
					  case "DOWN":
					    _td.style.backgroundColor = _form.color_DOWN.value;
						_tr.className = ClassName;
					   break;
					  case "UNREACHABLE":
					    _td.style.backgroundColor = _form.color_UNREACHABLE.value;
						_tr.className = "list_three";
					   break;
					  default:
					    _td.style.backgroundColor = _form.color_UNDETERMINATED.value;
						_tr.className = ClassName;
					   break;
					}
					document.getElementById('last_check'+order).innerHTML = _last_check;
					document.getElementById('last_change'+order).innerHTML = _last_change;
					document.getElementById('output'+order).innerHTML = _output;				
				}

				if(_type == 'metaservice')
				{
					var _last_check = line.getElementsByTagName("last_check")[0].firstChild.nodeValue;
					var _last_change = line.getElementsByTagName("last_change")[0].firstChild.nodeValue;
					var _status = line.getElementsByTagName("status")[0].firstChild.nodeValue;
					var _output = line.getElementsByTagName("output")[0].firstChild.nodeValue;

					var _infohtml = '';

					var _retry = line.getElementsByTagName("retry")[0].firstChild.nodeValue;
					var _accept_passive_check = line.getElementsByTagName("accept_passive_check")[0].firstChild.nodeValue;
					var _accept_active_check = line.getElementsByTagName("accept_active_check")[0].firstChild.nodeValue;
					var _ev_handler_en = line.getElementsByTagName("ev_handler_en")[0].firstChild.nodeValue;

					if(_accept_passive_check == 1)
						_infohtml += '<img src=' + _form.icone_accept_passive_check1.value + ' alt=accept_passive_check>';
					if(_accept_active_check == 1)
						_infohtml += '<img src=' + _form.icone_accept_passive_check0.value + ' alt=accept_active_check>';					

					document.getElementById('infos'+order).innerHTML = _infohtml;
				
					document.getElementById('status'+order).innerHTML = _status;
					_td=document.getElementById('tdStatus'+order);
					_tr=document.getElementById('trStatus'+order);
					var ClassName = "list_one";
					if(i % 2)
						ClassName = "list_two";

					switch (_status)
					{
					  case "OK":
					    _td.style.backgroundColor = _form.color_OK.value;
						_tr.className = ClassName;
					   break;
					  case "WARNING":
					    _td.style.backgroundColor = _form.color_WARNING.value;
						_tr.className = ClassName;
					   break;
					  case "CRITICAL":
					    _td.style.backgroundColor = _form.color_CRITICAL.value;
						_tr.className = "list_three";
					   break;
					  case "UNDETERMINATED":
					    _td.style.backgroundColor = _form.color_UNDETERMINATED.value;
						_tr.className = ClassName;
					   break;
					  default:
					    _td.style.backgroundColor = _form.color_UNKNOWN.value;
						_tr.className = ClassName;
					   break;
					}
					document.getElementById('retry'+order).innerHTML = _retry;
					document.getElementById('last_check'+order).innerHTML = _last_check;
					document.getElementById('last_change'+order).innerHTML = _last_change;
					document.getElementById('output'+order).innerHTML = _output;
				}//fin metaservice
			}//fin du for pour les infos principale
		}
	}
	
//	document.getElementById('test').innerHTML = "fileStatus="+_fileStatus+"&fileOreonConf="+_fileOreonConf+"&lca="+_lca+"&version="+_version+"&type="+_type+"&smaxtime="+parseInt(_form.smaxtime.value)+"&slastreload="+parseInt(_form.slastreload.value)+"&sid="+_sid+"&time="+parseInt(_form.time.value)+"&arr="+myArray;

	xhr.open("POST",_adresseRecherche,true);
	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhr.send("fileStatus="+_fileStatus+"&fileOreonConf="+_fileOreonConf+"&lca="+_lca+"&version="+_version+"&type="+_type+"&smaxtime="+parseInt(_form.smaxtime.value)+"&slastreload="+parseInt(_form.slastreload.value)+"&sid="+_sid+"&time="+parseInt(_form.time.value)+"&arr="+myArray);

	setTimeout('go()', 5000);
	//ce timer correspond au tps entre chaque check de la date de modif du fichier
	//le fichier sera parser dans le .php ssi il vient a etre modifié par nagios
}






