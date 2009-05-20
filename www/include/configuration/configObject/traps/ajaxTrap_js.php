<?php
/*
 * Copyright 2005-2009 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
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