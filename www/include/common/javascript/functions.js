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