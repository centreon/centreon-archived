<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

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
	
?>
<SCRIPT LANGUAGE="JavaScript">

function getXhrTrap(){
	if(window.XMLHttpRequest) // Firefox et autres
	   var xhrT = new XMLHttpRequest();
	else if(window.ActiveXObject){ // Internet Explorer
	   try {
                var xhrT = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                var xhrT = new ActiveXObject("Microsoft.XMLHTTP");
            }
	}
	else { // XMLHttpRequest non support2 par le navigateur
	   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
	   var xhrT = false;
	}
	return xhrT;
}

function getTrap(mnftr_id) {


	var arg = 'oreonPath=<?=$oreon->optGen["oreon_path"]?>&mnftr_id='+mnftr_id;

	var xhrT = getXhrTrap();
	 		  	
	xhrT.open("POST","./include/configuration/configObject/traps/GetXMLTrapsForVendor.php",true);
	xhrT.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhrT.send(arg);

	
	// On defini ce qu'on va faire quand on aura la reponse
	xhrT.onreadystatechange = function()
	{	
		// On ne fait quelque chose que si on a tout recu et que le serveur est ok
		if(xhrT && xhrT.readyState == 4 && xhrT.status == 200 && xhrT.responseXML)
		{		
			reponseT = xhrT.responseXML.documentElement;	
			var _traps = reponseT.getElementsByTagName("trap");

			var _selbox = document.getElementById("__service_traps");
			while ( _selbox.options.length > 0 )
			{
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