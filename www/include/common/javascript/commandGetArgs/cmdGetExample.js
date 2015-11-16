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

function set_arg(e, a) {
	var f = document.forms["Form"];
	var example    = f.elements[e];
	var argument    = f.elements[a];
	argument.value = example.value;
}

function setArgument(f, l, a) 
{   
    var mlist    = f.elements[l];
    var argument = f.elements[a];
    var index    = mlist.selectedIndex;

    if (typeof argument != 'undefined') {
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
}