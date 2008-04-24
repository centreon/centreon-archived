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

function set_arg(e, a) {
	var f = document.forms["Form"];
	var example    = f.elements[e];
	var argument    = f.elements[a];
	argument.value = example.value;
}

function setArgument(f, l, a) {
	var mlist    = f.elements[l];
	var argument    = f.elements[a];
	var index = mlist.selectedIndex;

	if (argument.value)
		argument.value = '';

	if (index >= 1) {
	   	var xhr_object = null; 
	     
		if (window.XMLHttpRequest) // Firefox 
	      	xhr_object = new XMLHttpRequest(); 
	   	else if (window.ActiveXObject) // Internet Explorer 
	      	xhr_object = new ActiveXObject("Microsoft.XMLHTTP"); 
	   	else { 
	   		// XMLHttpRequest non supporté par le navigateur 
	      	alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
	      	return; 
	   	} 
	   	xhr_object.open("POST", "./include/common/javascript/commandGetArgs/cmdGetExample.php", true);
	   	xhr_object.onreadystatechange = function() { 
	    	if (xhr_object.readyState == 4) {
	          	argument.value = xhr_object.responseText; 
	        }
	   	}	 
	 	xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
	   	xhr_object.send("index=" + mlist.value); 	   	
	} 
}