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

 // JavaScript Document

var _adrrsearchC = "./include/monitoring/status/TopCounter/xml/statusCounter.php";

function getXhrC(){
	if (window.XMLHttpRequest) {
		// Firefox and others
	   	var xhrC = new XMLHttpRequest();
	} else if(window.ActiveXObject) {
		// Internet Explorer
	   	try {
            var xhrC = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            var xhrC = new ActiveXObject("Microsoft.XMLHTTP");
        }
	} else {
		// XMLHttpRequest non support2 par le navigateur
		alert("Your Browser doesn't support XMLHTTPRequest object...");
	   	var xhrC = false;
	}
	return xhrC;
}

function reloadStatusCounter(_reload_time) {

	var xhrC = getXhrC();
	// On defini ce qu'on va faire quand on aura la reponse
	xhrC.onreadystatechange = function() {
		// On ne fait quelque chose que si on a tout recu et que le serveur est ok
		if (xhrC && xhrC.readyState == 4 && xhrC.status == 200 && xhrC.responseXML) {
			reponseC = xhrC.responseXML.documentElement;

			// Get stats
			var stats = reponseC.getElementsByTagName("s");
			for (var i = 0 ; i < stats.length ; i++) {
				var stat = stats[i];
				var _statistic_total_service 	= stat.getElementsByTagName("ts")[0].firstChild.nodeValue;
				var _statistic_total_host 		= stat.getElementsByTagName("th")[0].firstChild.nodeValue;
				var _statistic_service_ok 		= stat.getElementsByTagName("o")[0].firstChild.nodeValue;
				var _statistic_service_warning 	= stat.getElementsByTagName("w")[0].firstChild.nodeValue;
				var _statistic_service_critical = stat.getElementsByTagName("c")[0].firstChild.nodeValue;
				var _statistic_service_unknown 	= stat.getElementsByTagName("un1")[0].firstChild.nodeValue;
				var _statistic_service_warningU	= stat.getElementsByTagName("wU")[0].firstChild.nodeValue;
				var _statistic_service_criticalU = stat.getElementsByTagName("cU")[0].firstChild.nodeValue;
				var _statistic_service_unknownU	= stat.getElementsByTagName("un1U")[0].firstChild.nodeValue;
				var _statistic_service_pending 	= stat.getElementsByTagName("p1")[0].firstChild.nodeValue;
				var _statistic_host_up 			= stat.getElementsByTagName("up")[0].firstChild.nodeValue;
				var _statistic_host_down 		= stat.getElementsByTagName("d")[0].firstChild.nodeValue;
				var _statistic_host_unreachable = stat.getElementsByTagName("un2")[0].firstChild.nodeValue;
				var _statistic_host_pending 	= stat.getElementsByTagName("p2")[0].firstChild.nodeValue;

				/*
				 * host
				 */
				document.getElementById('hosts').innerHTML = '';
				var _text_total_host = document.createTextNode(_statistic_total_host);
				var _linkHosttotal = document.createElement("a");
			  	_linkHosttotal.href = 'main.php?p=20202&o=h&search=';
				_linkHosttotal.appendChild(_text_total_host);
				document.getElementById('hosts').appendChild(_linkHosttotal);

				document.getElementById('host_up').innerHTML = '';
				var _text_host_up = document.createTextNode(_statistic_host_up);
				var _linkHostup = document.createElement("a");
			  	_linkHostup.href = 'main.php?p=20202&o=h_up&search=';
				_linkHostup.appendChild(_text_host_up);
				document.getElementById('host_up').appendChild(_linkHostup);

				document.getElementById('host_down').innerHTML = '';
				var _text_host_down = document.createTextNode(_statistic_host_down);
				var _linkHostProblem = document.createElement("a");
			  	_linkHostProblem.href = 'main.php?p=20202&o=h_down&search=';
				_linkHostProblem.appendChild(_text_host_down);
				document.getElementById('host_down').appendChild(_linkHostProblem);

				document.getElementById('host_unreachable').innerHTML = '';
				var _text_host_unreachable = document.createTextNode(_statistic_host_unreachable);
				var _linkHostunreachable = document.createElement("a");
			  	_linkHostunreachable.href = 'main.php?p=20202&o=h_unreachable&search=';
				_linkHostunreachable.appendChild(_text_host_unreachable);
				document.getElementById('host_unreachable').appendChild(_linkHostunreachable);

				document.getElementById('host_pending').innerHTML = '';
				var _text_host_pending = document.createTextNode(_statistic_host_pending);
				var _linkHostpending = document.createElement("a");
			  	_linkHostpending.href = 'main.php?p=20202&o=h_pending&search=';
				_linkHostpending.appendChild(_text_host_pending);
				document.getElementById('host_pending').appendChild(_linkHostpending);

				/*
				 * svc
				 */
				document.getElementById('service_total').innerHTML = '';
				var _text_total_service = document.createTextNode(_statistic_total_service);
				var _linkservice_total = document.createElement("a");
			  	_linkservice_total.href = 'main.php?p=20201&o=svc&search=';
				_linkservice_total.appendChild(_text_total_service);
				document.getElementById('service_total').appendChild(_linkservice_total);

				// Ok service Stats
				document.getElementById('service_ok').innerHTML = '';
				var _text_service_ok = document.createTextNode(_statistic_service_ok);
				var _linkservice_ok = document.createElement("a");
			  	_linkservice_ok.href = 'main.php?p=20201&o=svc_ok&search=';
				_linkservice_ok.appendChild(_text_service_ok);
				document.getElementById('service_ok').appendChild(_linkservice_ok);

				// Warning service stats
				document.getElementById('service_warning').innerHTML = '';
				var _text_service_warning = document.createTextNode(_statistic_service_warningU+"/"+_statistic_service_warning);
				var _linkservice_warning = document.createElement("a");
			  	_linkservice_warning.href = 'main.php?p=20201&o=svc_warning&search=';
				_linkservice_warning.appendChild(_text_service_warning);
				document.getElementById('service_warning').appendChild(_linkservice_warning);

				// Critcal Service Stats
				document.getElementById('service_critical').innerHTML = '';
				var _text_service_critical = document.createTextNode(_statistic_service_criticalU+"/"+_statistic_service_critical);
				var _linkservice_critical = document.createElement("a");
			  	_linkservice_critical.href = 'main.php?p=20201&o=svc_critical&search=';
				_linkservice_critical.appendChild(_text_service_critical);
				document.getElementById('service_critical').appendChild(_linkservice_critical);

				// Unknown Service Stats
				document.getElementById('service_unknown').innerHTML = '';
				var _text_service_unknown = document.createTextNode(_statistic_service_unknownU+"/"+_statistic_service_unknown);
				var _linkservice_unknown = document.createElement("a");
			  	_linkservice_unknown.href = 'main.php?p=20201&o=svc_unknown&search=';
				_linkservice_unknown.appendChild(_text_service_unknown);
				document.getElementById('service_unknown').appendChild(_linkservice_unknown);

				// Pending Services Stats
				document.getElementById('service_pending').innerHTML = '';
				var _text_service_pending = document.createTextNode(_statistic_service_pending);
				var _linkservice_pending = document.createElement("a");
			  	_linkservice_pending.href = 'main.php?p=20201&o=svc_pending&search=';
				_linkservice_pending.appendChild(_text_service_pending);
				document.getElementById('service_pending').appendChild(_linkservice_pending);
			}

			// Get Poller Statistics
			if (document.getElementById('img_pollingState')) {
				var statPoller = reponseC.getElementsByTagName("m");
				for (var i = 0 ; i < statPoller.length ; i++) {
					var statp = statPoller[i];
					var _statistic_pollingState = statp.getElementsByTagName("pstt")[0].firstChild.nodeValue;
					var _statistic_latency = statp.getElementsByTagName("ltc")[0].firstChild.nodeValue;
					var _statistic_activity = statp.getElementsByTagName("act")[0].firstChild.nodeValue;
					var _error_pollingState = statp.getElementsByTagName("errorPstt")[0].firstChild.nodeValue;
					var _error_latency = statp.getElementsByTagName("errorLtc")[0].firstChild.nodeValue;
					var _error_activity = statp.getElementsByTagName("errorAct")[0].firstChild.nodeValue;

					document.getElementById("img_pollingState").title = _error_pollingState;
					document.getElementById("img_latency").title = _error_latency;
					document.getElementById("img_activity").title = _error_activity;
                                        
					if (_statistic_latency === '0') {
                        document.getElementById("latency").style.backgroundColor = "#88b917";
					} else if (_statistic_latency === '1') {
                        document.getElementById("latency").style.backgroundColor = "#ff9a13";
					} else if (_statistic_latency === '2') {
                        document.getElementById("latency").style.backgroundColor = "#e00b3d";
					}

					if (_statistic_activity === '0') {
                        document.getElementById("activity").style.backgroundColor = "#88b917";
					} else if (_statistic_activity === '1') {
                        document.getElementById("activity").style.backgroundColor = "#ff9a13";
					} else if (_statistic_activity === '2') {
                        document.getElementById("activity").style.backgroundColor = "#e00b3d";
					}

					if (_statistic_pollingState === '0') {
                        document.getElementById("pollingState").style.backgroundColor = "#88b917";
					} else if (_statistic_pollingState === '1') {
                        document.getElementById("pollingState").style.backgroundColor = "#ff9a13";
					} else if (_statistic_pollingState === '2') {
                        document.getElementById("pollingState").style.backgroundColor = "#e00b3d";
					}
				}
			}
		}
	}

	xhrC.open("POST", _adrrsearchC, true);
	xhrC.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhrC.send("&session_expire="+(_reload_time/1000));
	setTimeout('reloadStatusCounter("'+ _reload_time +'")', _reload_time);
}
