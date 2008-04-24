/**
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * safety, contents, performance, merchantability, non-infringement or suitability for
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
*/

// JavaScript Document

function getXMLHTTP(){
  var xhr=null;
  if(window.XMLHttpRequest) // Firefox et autres
 	 xhr = new XMLHttpRequest();
  else if(window.ActiveXObject){ // Internet Explorer
    try {
      xhr = new ActiveXObject('Msxml2.XMLHTTP');
    } catch (e) {
      try {
        xhr = new ActiveXObject('Microsoft.XMLHTTP');
      } catch (e1) {
        xhr = null;
      }
    }
  }
  else { // XMLHttpRequest non supporté par le navigateur
    alert('Votre navigateur ne supporte pas les objets XMLHTTPRequest...');
  }
  return xhr;
}

var _xmlHttp = null; //l'objet xmlHttpRequest utilisé pour contacter le serveur

function loadXMLDoc(url,div) {
	if(_xmlHttp&&_xmlHttp.readyState!=0){
		_xmlHttp.abort()
	}
  	_xmlHttp=getXMLHTTP();
  	if(_xmlHttp){
    	//appel à l'url distante
    	_xmlHttp.open('GET',url,true);
    	_xmlHttp.onreadystatechange=function() {
      	if(_xmlHttp.readyState==4&&_xmlHttp.responseText) {
	      	display(_xmlHttp.responseText,div)
      	}
    	};
    	// envoi de la requete
    	_xmlHttp.send(null)
  	}
}
function display(str,div){
	if (document.layers) {
		document.layers[div].document.write(str);
	}
	if (document.all) {
		document.all[div].innerHTML=str;
	} else if (document.getElementById) {
		document.getElementById(div).innerHTML=str;
	}
}