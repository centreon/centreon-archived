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
	
?><script type="text/javascript">

function getXhrTrap(){
	if (window.XMLHttpRequest) // Firefox et autres
	   var xhrT = new XMLHttpRequest();
	else if(window.ActiveXObject){ // Internet Explorer
	   try {
                var xhrT = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                var xhrT = new ActiveXObject("Microsoft.XMLHTTP");
            }
	} else { // XMLHttpRequest non support2 par le navigateur
	   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
	   var xhrT = false;
	}
	return xhrT;
}

function getTrap(mnftr_id) {
	var arg = 'mnftr_id='+mnftr_id;
	var xhrT = getXhrTrap();
	 		  	
	xhrT.open("POST","./include/configuration/configObject/traps/GetXMLTrapsForVendor.php",true);
	xhrT.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhrT.send(arg);

	// On defini ce qu'on va faire quand on aura la reponse
	xhrT.onreadystatechange = function()
	{	
		// On ne fait quelque chose que si on a tout recu et que le serveur est ok
		if (xhrT && xhrT.readyState == 4 && xhrT.status == 200 && xhrT.responseXML){		
			reponseT = xhrT.responseXML.documentElement;	
			var _traps = reponseT.getElementsByTagName("trap");

			var _selbox = document.getElementById("service_traps-f");
			while ( _selbox.options.length > 0 ){
				_selbox.options[0] = null;
			}

			if (_traps.length == 0) {
				_selbox.setAttribute('disabled', 'disabled');
			} else {
				_selbox.removeAttribute('disabled');
			}

			for (var i = 0 ; i < _traps.length ; i++) {
				var _trap = _traps[i];
				var _id = _trap.getElementsByTagName("id")[0].firstChild.nodeValue;
				var _name = _trap.getElementsByTagName("name")[0].firstChild.nodeValue;

				new_elem = new Option(_name,_id);
				_selbox.options[_selbox.length] = new_elem;
			}
		}
	}
}
</SCRIPT>