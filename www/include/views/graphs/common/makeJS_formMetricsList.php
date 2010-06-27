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
 * File makeJS_formMetricsList.php D.Porte
 * 
 */
 
?><script type="text/javascript">

var _o = '<?php echo $o;?>';
var _vdef = '<?php echo $vdef;?>';

function resetLists(db_id, def_id){
	update_select_list(0, db_id, def_id);
	update_select_list(1,def_id);
}

/* Function for displaying selected template */
function display_select_list(xhr, xml_id, def_id){

    var id;
    var alias;

	/* get select data from xml */
    var docXML = xhr.responseXML;
	var s_id = docXML.getElementsByTagName("select_id").item(0).firstChild.data;
	var td_id = docXML.getElementsByTagName("td_id").item(0).firstChild.data;
    var options = docXML.getElementsByTagName("option");
    var o_id = docXML.getElementsByTagName("o_id");
    var o_alias = docXML.getElementsByTagName("o_alias");

	if ( _o == "a" || _o == "c") {
		/* init new select element */
		var c_elem = document.createElement('select');
		c_elem.id = s_id;
		if ( xml_id == 0 ){
			c_elem.name = "index_id";
			c_elem.onchange= function(){ update_select_list(1,this.value);};
		}
	}

	for(i=0; i<options.length; i++) {
		id = o_id.item(i).firstChild.data;  	
		alias = o_alias.item(i).firstChild.data;

		if ( _o == "a" || _o == "c") {
			var o_elem = document.createElement('option');    	
			o_elem.value = id;
			o_elem.text = alias;
		}
		if ( def_id != null && def_id == id ) {
			if ( _o == "w" ) {
				service_val = o_alias.item(i).firstChild.data;
			} else {
				o_elem.selected = true;
			}
		}
		if ( i == 0) {
			if ( _o == "w" ) {
				service_val = "Services list";
			} else {
				o_elem.selected = true;
			}
		}
		if ( _o == "a" || _o == "c") {
    		if (navigator.appName == "Microsoft Internet Explorer") {					
    			c_elem.add(o_elem);
    		} else {
	   			c_elem.appendChild(o_elem);
			}
		}
	}			
	var td_elem = document.getElementById(td_id);		
	if ( td_elem != null ) {
		if ( _o == "w" ) {
			var inHTML = td_elem.innerHTML;
			var pattern = "(&nbsp;)+";
			var re = new RegExp(pattern);
			inHTML = inHTML.replace(re, "");
			td_elem.innerHTML = inHTML + service_val;
			/* init new input element */	
			var c_elem = document.createElement('input');
			c_elem.type = "hidden";
			if ( def_id != null )
				c_elem.value = def_id;
			c_elem.name = "index_id";
		} else {
			/* Remove old select if exist */
   			var s_old = document.getElementById(s_id);
			if ( s_old != null )
				td_elem.removeChild(s_old);
		}
		td_elem.appendChild(c_elem);
	}
}

/*
 * Create the select after the reception of XML data
 */
function get_select_options(xhr, xml_id, def_id) {
	if (xhr.readyState != 4 && xhr.readyState != "complete")		
    	return(0);
    if (xhr.status == 200) {    	    	
   		display_select_list(xhr, xml_id, def_id);
    }	
}

/*
 * This function is called when user clicks on the 'add' button
 */
function update_select_list(xml_id, db_id, def_id){	
	var xhr = null;
	if (window.XMLHttpRequest) {     
        xhr = new XMLHttpRequest();
    } else if (window.ActiveXObject) {        
        xhr = new ActiveXObject("Microsoft.XMLHTTP");
    }
        
    if (xhr == null)
     	alert("AJAX is not supported");
    xhr.onreadystatechange = function() { get_select_options(xhr, xml_id, def_id); };
    xhr.open("GET", "./include/views/graphs/common/" + xmlFile[xml_id]+ "=" + db_id + "&vdef=" +_vdef, true);
    xhr.send(null);
}

/*
 * Global variables
 */

var xmlFile = new Array();
xmlFile[0] = "makeXML_ListServices.php?host_id";
xmlFile[1] = "makeXML_ListMetrics.php?index_id";

</script>
