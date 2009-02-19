<?php 
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
 
 ?>


function check_session()
{
	call_XMLHttpReq2();
}

function call_XMLHttpReq2()
{
  	var xhr2=null;
    
    if (window.XMLHttpRequest) { 
        xhr2 = new XMLHttpRequest();
    }
    else if (window.ActiveXObject) 
    {
        xhr2 = new ActiveXObject("Microsoft.XMLHTTP");
    }
    //on définit l'appel de la fonction au retour serveur
    if(xhr2==null)
     alert("Le web browser ne supporte pas l'AJAX.");
    xhr2.onreadystatechange = function() { change_status(xhr2); };
    
    //on appelle le fichier XMLresponse.php
    xhr2.open("GET", "./include/common/javascript/autologoutXMLresponse.php?sid=" + sid, true);
    xhr2.send(null);
}

function change_status(xhr2)
{
	if (xhr2.readyState != 4 && xhr2.readyState != "complete")
		return(0);
	var docXML= xhr2.responseXML;
	var items_state = docXML.getElementsByTagName("state");
	var state = items_state.item(0).firstChild.data;
			
	if(state == "ok") {
		;
	}
	else if(state == "nok") {
		window.location.replace("./main.php");	
	}
	setTimeout("check_session()", tm_out);
}
var sid = '<?php echo session_id(); ?>';
var tm_out = <?php echo $tM; ?>;
