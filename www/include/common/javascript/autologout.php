<?php
/*
 * Copyright 2005-2011 MERETHIS
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
?>

function check_session() {
	call_XMLHttpReq2();
}

function call_XMLHttpReq2() {
  	var xhr2=null;

    if (window.XMLHttpRequest) {
        xhr2 = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        xhr2 = new ActiveXObject("Microsoft.XMLHTTP");
    }
    //on d�finit l'appel de la fonction au retour serveur
    if (xhr2==null)
     alert("Le web browser ne supporte pas l'AJAX.");
    xhr2.onreadystatechange = function() { change_status(xhr2); };

    //on appelle le fichier XMLresponse.php
    xhr2.open("GET", "./include/common/javascript/autologoutXMLresponse.php?sid=" + sid, true);
    xhr2.send(null);
}

function change_status(xhr2) {
	if (xhr2.readyState != 4 && xhr2.readyState != "complete")
		return(0);
	var docXML= xhr2.responseXML;
	var items_state = docXML.getElementsByTagName("state");
	var items_time = docXML.getElementsByTagName("time");
	var state = items_state.item(0).firstChild.data;
	var currentTime = items_time.item(0).firstChild.data;

	if (state == "ok") {
		if (document.getElementById('date')) {
			document.getElementById('date').innerHTML = currentTime;
		}
	} else if (state == "nok") {
		window.location.replace("./index.php");
	}
	setTimeout("check_session()", tm_out);
}

var sid = '<?php echo session_id(); ?>';
var tm_out = <?php echo $tM; ?>;
