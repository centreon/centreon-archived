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
	if (!isset($oreon))
		exit();

	if($num < 0)
		$num =0;
?>

<script type="text/javascript">

var _o='<?php echo $o;?>';
var _p='<?php echo $p;?>';

/*
** Function called when user clicks on add button
*/
function addBlankSelect() {
	var docXML = xhr.responseXML;
	
	var host_entry = docXML.getElementsByTagName("template");
	    
	var tp_id = docXML.getElementsByTagName("tp_id");
	var id;    
	
	var tp_alias = docXML.getElementsByTagName("tp_alias");
	var alias;
	
	var selectElem = document.createElement('select');
	var tabElem = document.createElement('table');
	var trElem = document.createElement('tr');
	var tdElem = document.createElement('td');
	var i;
	
	
	selectElem.id = 'tpSelect_' + globalk;	
	selectElem.name = 'tpSelect_' + globalk;
	selectElem.value = 'tpSelect_' + globalk;	
	globalk++;
	for(i=0; i<host_entry.length;i++)
	{
	  	id = tp_id.item(i).firstChild.data;    	
	   	alias = tp_alias.item(i).firstChild.data;
	   	var optionElem = document.createElement('option');
	   	
	    optionElem.value = id;
	    optionElem.text = alias;
	    selectElem.appendChild(optionElem);
	}
			    		
	
	divElem = document.getElementById('parallelTemplate');			
	tabElem.class = "ListTableMultiTp";		
	tdElem.class = "FormRowValue";
	
	tdElem.appendChild(selectElem);
	trElem.appendChild(tdElem);
	tabElem.appendChild(trElem);	
	divElem.appendChild(tabElem);
}

/*
** Function for displaying selected template
*/
function displaySelectedTp(){
	    var docXML = xhr.responseXML;
	    
	    var host_entry = docXML.getElementsByTagName("template");
	        
	    var tp_id = docXML.getElementsByTagName("tp_id");
	    var id;
	        
	    var tp_alias = docXML.getElementsByTagName("tp_alias");
	    var alias;
	    
	    var i;
	    
	    for (globalk=0; tab[globalk]; globalk++){
		    var selectElem = document.createElement('select');
			var tabElem = document.createElement('table');
			var trElem = document.createElement('tr');
			var tdElem = document.createElement('td');
		    
		    selectElem.id = 'tpSelect_' + globalk;
			selectElem.name = 'tpSelect_' + globalk;
			selectElem.value = 'tpSelect_' + globalk;			
			for(i=0; i<host_entry.length;i++)
			{
			   	id = tp_id.item(i).firstChild.data;    	
			   	alias = tp_alias.item(i).firstChild.data;
			   	var optionElem = document.createElement('option');    	
			   	if (tab[globalk] == id) {
					optionElem.selected = true;
			  	}
			    optionElem.value = id;
			    optionElem.text = alias;    
			    selectElem.appendChild(optionElem);
			}
			    		
			divElem = document.getElementById('parallelTemplate');
			
			tabElem.class = "ListTableMultiTp";		
			tdElem.class = "FormRowValue";
			
			tdElem.appendChild(selectElem);
			trElem.appendChild(tdElem);
			tabElem.appendChild(trElem);	
			divElem.appendChild(tabElem);			
		}
}

/*
** Create the select after the reception of XML data
*/
function get_select_options() {
	if (xhr.readyState != 4 && xhr.readyState != "complete")		
    	return(0);
    if (xhr.status == 200)
    {
    	displaySelectedTp();
    }	
}

/*
** This function is called when user clicks on the 'add' button
*/
function add_select_template(){	
	xhr = null;
	if (window.XMLHttpRequest) {
        xhr = new XMLHttpRequest();
    }
    else if (window.ActiveXObject)
    {
        xhr = new ActiveXObject("Microsoft.XMLHTTP");
    }
        
    if(xhr==null)
     	alert("AJAX is not supported");
    
    xhr.onreadystatechange = function() { get_select_options(); };
    xhr.open("GET", "./include/configuration/configObject/host/makeXMLhost.php", true);
    xhr.send(null);
}

/*
** Global variables
*/
var tab = new Array();
var globalk;
var xhr;
var divElem;
</script>