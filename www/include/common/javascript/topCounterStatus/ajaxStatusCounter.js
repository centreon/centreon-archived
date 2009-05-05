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

// JavaScript Document

var _adrrsearchC = "./include/monitoring/status/TopCounter/statusCounter.php";

function getXhrC(){
	if (window.XMLHttpRequest) // Firefox and others
	   	var xhrC = new XMLHttpRequest();
	else if(window.ActiveXObject){ // Internet Explorer
	   	try {
            	var xhrC = new ActiveXObject("Msxml2.XMLHTTP");
           	} catch (e) {
                var xhrC = new ActiveXObject("Microsoft.XMLHTTP");
           	}
	} else { // XMLHttpRequest non support2 par le navigateur
		alert("Your Browser doesn't support XMLHTTPRequest object...");
	   	var xhrC = false;
	}
	return xhrC;
}

function reloadStatusCounter(_relaod_time,_sid){

	_form=document.getElementById('AjaxBankBasic');		       
	_version=_form.version.value;

	var xhrC = getXhrC();
	// On defini ce qu'on va faire quand on aura la reponse
	xhrC.onreadystatechange = function()
	{	
		// On ne fait quelque chose que si on a tout recu et que le serveur est ok
		if(xhrC && xhrC.readyState == 4 && xhrC.status == 200 && xhrC.responseXML)
		{		
			reponseC = xhrC.responseXML.documentElement;
			
			// ici je recupere les statistiques
			var stats = reponseC.getElementsByTagName("s");
			for (var i = 0 ; i < stats.length ; i++) {
				var stat = stats[i];
				var _statistic_service_ok = stat.getElementsByTagName("o")[0].firstChild.nodeValue;
				var _statistic_service_warning = stat.getElementsByTagName("w")[0].firstChild.nodeValue;
				var _statistic_service_critical = stat.getElementsByTagName("c")[0].firstChild.nodeValue;
				var _statistic_service_unknown = stat.getElementsByTagName("un1")[0].firstChild.nodeValue;
				var _statistic_service_pending = stat.getElementsByTagName("p1")[0].firstChild.nodeValue;
				var _statistic_host_up = stat.getElementsByTagName("up")[0].firstChild.nodeValue;
				var _statistic_host_down = stat.getElementsByTagName("d")[0].firstChild.nodeValue;
				var _statistic_host_unreachable = stat.getElementsByTagName("un2")[0].firstChild.nodeValue;
				var _statistic_host_pending = stat.getElementsByTagName("p2")[0].firstChild.nodeValue;

				/*
				 * host
				 */
				document.getElementById('host_up').innerHTML = '';//_statistic_host_up;
				var _text_host_up = document.createTextNode(_statistic_host_up);
				var _linkHostup = document.createElement("a");
			  	_linkHostup.href = 'main.php?p=20103&o=h';
				_linkHostup.appendChild(_text_host_up);
				document.getElementById('host_up').appendChild(_linkHostup);

				document.getElementById('host_down').innerHTML = '';
				var _text_host_down = document.createTextNode(_statistic_host_down);
				var _linkHostProblem = document.createElement("a");
			  	_linkHostProblem.href = 'main.php?p=20103&o=hpb';
				_linkHostProblem.appendChild(_text_host_down);
				document.getElementById('host_down').appendChild(_linkHostProblem);

				document.getElementById('host_unreachable').innerHTML = '';//_statistic_host_unreachable;
				var _text_host_unreachable = document.createTextNode(_statistic_host_unreachable);
				var _linkHostunreachable = document.createElement("a");
			  	_linkHostunreachable.href = 'main.php?p=20103&o=hpb';
				_linkHostunreachable.appendChild(_text_host_unreachable);
				document.getElementById('host_unreachable').appendChild(_linkHostunreachable);

				document.getElementById('host_pending').innerHTML = '';//_statistic_host_pending;
				var _text_host_pending = document.createTextNode(_statistic_host_pending);
				var _linkHostpending = document.createElement("a");
			  	_linkHostpending.href = 'main.php?p=20103&o=hpb';
				_linkHostpending.appendChild(_text_host_pending);
				document.getElementById('host_pending').appendChild(_linkHostpending);

				/* 
				 * svc
				 */
				
				// Ok service Stats
				document.getElementById('service_ok').innerHTML = '';
				var _text_service_ok = document.createTextNode(_statistic_service_ok);
				var _linkservice_ok = document.createElement("a");
			  	_linkservice_ok.href = 'main.php?p=20201&o=svc_ok';
				_linkservice_ok.appendChild(_text_service_ok);
				document.getElementById('service_ok').appendChild(_linkservice_ok);
				
				// Warning service stats
				document.getElementById('service_warning').innerHTML = '';
				var _text_service_warning = document.createTextNode(_statistic_service_warning);
				var _linkservice_warning = document.createElement("a");
			  	_linkservice_warning.href = 'main.php?p=20201&o=svc_warning';
				_linkservice_warning.appendChild(_text_service_warning);
				document.getElementById('service_warning').appendChild(_linkservice_warning);
				
				// Critcal Service Stats
				document.getElementById('service_critical').innerHTML = '';
				var _text_service_critical = document.createTextNode(_statistic_service_critical);
				var _linkservice_critical = document.createElement("a");
			  	_linkservice_critical.href = 'main.php?p=20201&o=svc_critical';
				_linkservice_critical.appendChild(_text_service_critical);
				document.getElementById('service_critical').appendChild(_linkservice_critical);
				
				// Unknwon Service Stats
				document.getElementById('service_unknown').innerHTML = '';
				var _text_service_unknown = document.createTextNode(_statistic_service_unknown);
				var _linkservice_unknown = document.createElement("a");
			  	_linkservice_unknown.href = 'main.php?p=20201&o=svc_unknown';
				_linkservice_unknown.appendChild(_text_service_unknown);
				document.getElementById('service_unknown').appendChild(_linkservice_unknown);
				
				// Pending Services Stats
				document.getElementById('service_pending').innerHTML = '';
				var _text_service_pending = document.createTextNode(_statistic_service_pending);
				var _linkservice_pending = document.createElement("a");
			  	_linkservice_pending.href = 'main.php?p=20202&o=svcpb';
				_linkservice_pending.appendChild(_text_service_pending);
				document.getElementById('service_pending').appendChild(_linkservice_pending);
			}
		}
	}

	xhrC.open("POST",_adrrsearchC,true);
	xhrC.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhrC.send("sid="+_sid+"&version="+_version+"&session_expire="+(_relaod_time/1000));
	setTimeout('reloadStatusCounter("'+ _relaod_time +'","'+ _sid +'")', _relaod_time);
}