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

//var xhrC = null; 
var _adrrsearchC = "./include/monitoring/engine/MakeXML4statusCounter.php" //l'adresse   interroger pour trouver les suggestions

function getXhrC(){
	if(window.XMLHttpRequest) // Firefox et autres
	   var xhrC = new XMLHttpRequest();
	else if(window.ActiveXObject){ // Internet Explorer
	   try {
                var xhrC = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                var xhrC = new ActiveXObject("Microsoft.XMLHTTP");
            }
	}
	else { // XMLHttpRequest non support2 par le navigateur
	   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
	   var xhrC = false;
	}
	return xhrC;
}

function reloadStatusCounter(_relaod_time,_sid){


	_form=document.getElementById('AjaxBankBasic');		       
	_version=_form.version.value;
	_fileStatus=_form.fileStatus.value;
	_fileOreonConf=_form.fileOreonConf.value;

	var xhrC = getXhrC();
	// On defini ce qu'on va faire quand on aura la reponse
	xhrC.onreadystatechange = function()
	{	
		// On ne fait quelque chose que si on a tout recu et que le serveur est ok
		if(xhrC && xhrC.readyState == 4 && xhrC.status == 200 && xhrC.responseXML)
		{		
			reponseC = xhrC.responseXML.documentElement;
			
			// ici je recupere les statistiques
			var stats = reponseC.getElementsByTagName("stats");
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


				/*
				 * host
				 */
				document.getElementById('host_up').innerHTML = '';//_statistic_host_up;
				var _text_host_up = document.createTextNode(_statistic_host_up);
				var _linkHostup = document.createElement("a");
			  	_linkHostup.href = 'oreon.php?p=20103&o=h';
				_linkHostup.appendChild(_text_host_up);
				document.getElementById('host_up').appendChild(_linkHostup);

				document.getElementById('host_down').innerHTML = '';
				var _text_host_down = document.createTextNode(_statistic_host_down);
				var _linkHostProblem = document.createElement("a");
			  	_linkHostProblem.href = 'oreon.php?p=20103&o=hpb';
				_linkHostProblem.appendChild(_text_host_down);
				document.getElementById('host_down').appendChild(_linkHostProblem);

				document.getElementById('host_unreachable').innerHTML = '';//_statistic_host_unreachable;
				var _text_host_unreachable = document.createTextNode(_statistic_host_unreachable);
				var _linkHostunreachable = document.createElement("a");
			  	_linkHostunreachable.href = 'oreon.php?p=20103&o=hpb';
				_linkHostunreachable.appendChild(_text_host_unreachable);
				document.getElementById('host_unreachable').appendChild(_linkHostunreachable);

				document.getElementById('host_pending').innerHTML = '';//_statistic_host_pending;
				var _text_host_pending = document.createTextNode(_statistic_host_pending);
				var _linkHostpending = document.createElement("a");
			  	_linkHostpending.href = 'oreon.php?p=20103&o=hpb';
				_linkHostpending.appendChild(_text_host_pending);
				document.getElementById('host_pending').appendChild(_linkHostpending);


				/* 
				 * svc
				 */
				document.getElementById('service_ok').innerHTML = '';//_statistic_service_ok;
				var _text_service_ok = document.createTextNode(_statistic_service_ok);
				var _linkservice_ok = document.createElement("a");
			  	_linkservice_ok.href = 'oreon.php?p=20201&o=svc';
				_linkservice_ok.appendChild(_text_service_ok);
				document.getElementById('service_ok').appendChild(_linkservice_ok);


				document.getElementById('service_warning').innerHTML = ''//_statistic_service_warning;
				var _text_service_warning = document.createTextNode(_statistic_service_warning);
				var _linkservice_warning = document.createElement("a");
			  	_linkservice_warning.href = 'oreon.php?p=20202&o=svc_warning';
				_linkservice_warning.appendChild(_text_service_warning);
				document.getElementById('service_warning').appendChild(_linkservice_warning);


				document.getElementById('service_critical').innerHTML = '';//_statistic_service_critical;
				var _text_service_critical = document.createTextNode(_statistic_service_critical);
				var _linkservice_critical = document.createElement("a");
			  	_linkservice_critical.href = 'oreon.php?p=20202&o=svc_critical';
				_linkservice_critical.appendChild(_text_service_critical);
				document.getElementById('service_critical').appendChild(_linkservice_critical);


				document.getElementById('service_unknown').innerHTML = ''//_statistic_service_unknown;
				var _text_service_unknown = document.createTextNode(_statistic_service_unknown);
				var _linkservice_unknown = document.createElement("a");
			  	_linkservice_unknown.href = 'oreon.php?p=20202&o=svc_unknown';
				_linkservice_unknown.appendChild(_text_service_unknown);
				document.getElementById('service_unknown').appendChild(_linkservice_unknown);

				document.getElementById('service_pending').innerHTML = ''//_statistic_service_pending;
				var _text_service_pending = document.createTextNode(_statistic_service_pending);
				var _linkservice_pending = document.createElement("a");
			  	_linkservice_pending.href = 'oreon.php?p=20202&o=svcpb';
				_linkservice_pending.appendChild(_text_service_pending);
				document.getElementById('service_pending').appendChild(_linkservice_pending);
			}
		}
	}

	xhrC.open("POST",_adrrsearchC,true);
	xhrC.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhrC.send("sid="+_sid+"&version="+_version+"&fileStatus="+_fileStatus+"&fileOreonConf="+_fileOreonConf+"&session_expire="+(_relaod_time/1000));

	setTimeout('reloadStatusCounter("'+ _relaod_time +'","'+ _sid +'")', _relaod_time);


}