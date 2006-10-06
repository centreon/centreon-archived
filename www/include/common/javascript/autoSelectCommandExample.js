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

/*
function set_arg(e, a) {
var f = document.forms["Form"];
	var example    = f.elements[e];
	var argument    = f.elements[a];
	argument.value = example.value;
}
*/

function setArgument(f, l, a) {
	
	var mlist    = f.elements[l];
	var argument    = f.elements[a];
	var index = mlist.selectedIndex;

	if(argument.value)
	argument.value = '';

	if(index > 1) {
	   var xhr_object = null; 
	     
	   if(window.XMLHttpRequest) // Firefox 
	      xhr_object = new XMLHttpRequest(); 
	   else if(window.ActiveXObject) // Internet Explorer 
	      xhr_object = new ActiveXObject("Microsoft.XMLHTTP"); 
	   else { // XMLHttpRequest non supporté par le navigateur 
	      alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
	      return; 
	   	} 
	 
	   xhr_object.open("POST", "./include/common/javascript/autoSelectCommandExample.php", true);


	   xhr_object.onreadystatechange = function() { 
	      if(xhr_object.readyState == 4) {
	          argument.value = xhr_object.responseText; 
	         }
	   	}
	 
	   xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
//	   var data = "family="+escape(l1.options[index].value)+"&form="+f.name+"&select=list2"; 

/*
	   	var data = ""; 
		var s1       = mlist.value; 
		if( s1 != "") 
	   		data = "s1="+s1; 
	   	xhr_object.send(data); 
*/
	   	xhr_object.send("index=" + mlist.value); 	   	
	} 
}