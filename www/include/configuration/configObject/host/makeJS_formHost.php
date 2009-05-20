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
 
?><script type="text/javascript">

var _o = '<?php echo $o;?>';
var _p = '<?php echo $p;?>';

/*
 *  This first block is the javascript code for the multi template creation
 *  There is also a second block for the multi macro creation 
 */

/*
 * Function for displaying selected template
 */
function displaySelectedTp(){
    var docXML = xhr.responseXML;
    
    var host_entry = docXML.getElementsByTagName("template");
        
    var tp_id = docXML.getElementsByTagName("tp_id");
    var id;
    
    var tp_alias = docXML.getElementsByTagName("tp_alias");
    var alias;
    	    	    
    var i;
    var imgElem;
    var tbodyElem = document.createElement('tbody');
    tbodyElem.id = "tbody_" + globalk;
    var tabElem = document.createElement('table');	    
    tabElem.id = "multiTpTable";
    tabElem.className = "ListTableMultiTp";	    
    	    
    for (globalk=0; tab[globalk]; globalk++){
	    var selectElem = document.createElement('select');
		var trElem = document.createElement('tr');			
		trElem.id = "trElem_" + globalk;
		if (trClassFlag) {
			trClassFlag = 0;
			trElem.className = "list_one";
		}
		else {
			trClassFlag = 1;
			trElem.className = "list_two";
		}
		var tdElem = document.createElement('td');
	    
	    selectElem.id = 'tpSelect_' + globalk;
		selectElem.name = 'tpSelect_' + globalk;
		selectElem.value = 'tpSelect_' + globalk;			
		for(i=0; i<host_entry.length;i++)
		{
		   	id = tp_id.item(i).firstChild.data;  	
		   	alias = tp_alias.item(i).firstChild.data;
		   	var optionElem = document.createElement('option');    	
		   	
		    optionElem.value = id;
		    if (i == 0) {
		    	optionElem.text = " ";
		    }
		    else {
		    	optionElem.text = alias;
		    }	
		    if (navigator.appName == "Microsoft Internet Explorer") {					
		    	selectElem.add(optionElem);
		    }
		    else {
		    	selectElem.appendChild(optionElem);
		    }
		    if (tab[globalk] == id) {
				optionElem.selected = true;
		  	}			    
		}			
		
		tabElem.className = "ListTableMultiTp";		
		tdElem.className = "FormRowValue";
		
		tdElem.appendChild(selectElem);
		
		imgElem = document.createElement("img");
		imgElem.id = globalk;
		imgElem.src = "./img/icones/16x16/delete.gif";			
		imgElem.title = '<?php echo _("Delete");?>';
		imgElem.onclick = function(){				
			var response = window.confirm('<?php echo _("Do you confirm this deletion?"); ?>');
			if (response) {										
		    	if (navigator.appName == "Microsoft Internet Explorer") {
					document.getElementById('trElem_' + this.id).innerText = "";
				}
				else {
					document.getElementById('trElem_' + this.id).innerHTML = "";
				}
			}
		}
		tdElem.appendChild(imgElem);
		trElem.appendChild(tdElem);
		tbodyElem.appendChild(trElem);
	}			
	tabElem.appendChild(tbodyElem)
	var divElem = document.getElementById("parallelTemplate");		
	divElem.appendChild(tabElem);
	
	//We create a hidden input so that the php sided code can retrieve the globalk variable
	var hidElem = document.getElementById("hiddenInput");
	hidElem.value = globalk;
}

/*
 * Function called when user clicks on add button
 */
function addBlankSelect() {	
	var docXML = xhr.responseXML;	
	var host_entry = docXML.getElementsByTagName("template");
	
	var tp_id = docXML.getElementsByTagName("tp_id");
	var id;    
	
	var tp_alias = docXML.getElementsByTagName("tp_alias");
	var alias;
	
	var selectElem = document.createElement('select');
	tabElem = document.getElementById('multiTpTable');
	var tbodyElem = document.createElement('tbody');
	var trElem = document.createElement('tr');	
	var tdElem = document.createElement('td');
	var divElem = document.getElementById('parallelTemplate');;
	var i;
	
	
	selectElem.id = 'tpSelect_' + globalk;	
	selectElem.name = 'tpSelect_' + globalk;
	selectElem.value = 'tpSelect_' + globalk;	
	globalk++;
	for (i=0; i < host_entry.length;i++){
	  	id = tp_id.item(i).firstChild.data; 	
	   	alias = tp_alias.item(i).firstChild.data;
	   	var optionElem = document.createElement('option');
	   	
	    optionElem.value = id;
	    if (i==0) {
	    	optionElem.text = " ";
	    } else {
	    	optionElem.text = alias;
	    }
	    if (navigator.appName == "Microsoft Internet Explorer") {					
			selectElem.add(optionElem);
		} else {
	    	selectElem.appendChild(optionElem);
	    }	
	}

	tdElem.appendChild(selectElem);
	
	var imgElem = document.createElement("img");
	imgElem.src = "./img/icones/16x16/delete.gif";	
	imgElem.onclick = function(){				
		tabElem.removeChild(tbodyElem);
	}
	tdElem.appendChild(imgElem);
	
	trElem.appendChild(tdElem);
	if (trClassFlag) {
		trClassFlag = 0;
		trElem.className = "list_one";
	} else {
		trClassFlag = 1;
		trElem.className = "list_two";
	}
	tbodyElem.appendChild(trElem);
	tabElem.appendChild(tbodyElem);	
	divElem.appendChild(tabElem);
	
	//We create a hidden input so that the php sided code can retrieve the globalk variable
	var hidElem = document.getElementById("hiddenInput");
	hidElem.value = globalk;	
}

/*
 * Create the select after the reception of XML data
 */
function get_select_options() {
	if (xhr.readyState != 4 && xhr.readyState != "complete")		
    	return(0);
    if (xhr.status == 200) {    	    	
   		displaySelectedTp();
    }	
}

/*
 * This function is called when user clicks on the 'add' button
 */
function add_select_template(){	
	xhr = null;
	if (window.XMLHttpRequest) {     
        xhr = new XMLHttpRequest();
    } else if (window.ActiveXObject) {        
        xhr = new ActiveXObject("Microsoft.XMLHTTP");
    }
        
    if (xhr == null)
     	alert("AJAX is not supported");
    
    xhr.onreadystatechange = function() { get_select_options(); };
    <?php
    if ($o == "a" || $o == "mc")
    	$host_id = -1; 
    ?>
    xhr.open("GET", "./include/configuration/configObject/host/makeXMLhost.php?host_id="+<?php echo $host_id;?>, true);
    xhr.send(null);
}

/*
 * Global variables
 */

var tab = new Array();
var xhr;
var globalk;
var trClassFlag = 1;

/*
 *  This second block is the javascript code for the multi macro creation 
 */
function addBlankInput() {
	var tabElem = document.getElementById('macroTable');
	var keyElem = document.createElement('input');
	var valueElem = document.createElement('input');
	var imgElem = document.createElement('img');			
	var trElem = document.createElement('tr');
	var tbodyElem = document.createElement('tbody');
	
	trElem.id = "trElem_" + globalj;
	if (trMacroClassFlag) {
		trElem.className = "list_one";
		trMacroClassFlag = 0;
	} else {
		trElem.className = "list_two";
		trMacroClassFlag = 1;
	}
	
	trElem.id = "trMacroInput_" + globalj;	
	var tdElem1 = document.createElement('td');
	tdElem1.className = "ListColCenter";
	var tdElem2 = document.createElement('td');
	tdElem2.className = "ListColLeft";
	var tdElem3 = document.createElement('td');
	tdElem3.className = "ListColCenter";
	keyElem.id = 'macroInput_' + globalj;	
	keyElem.name = 'macroInput_' + globalj;
	keyElem.value = '';
	tdElem1.appendChild(keyElem);	
				
	valueElem.id = 'macroValue_' + globalj;
	valueElem.name = 'macroValue_' + globalj;
	valueElem.value = "";	
	tdElem2.appendChild(valueElem);	
		
	imgElem.src = "./img/icones/16x16/delete.gif";
	imgElem.id = globalj;	
	imgElem.onclick = function(){
		var response = window.confirm('<?php echo _("Do you confirm this deletion?"); ?>');
		if (response){			
			if (navigator.appName == "Microsoft Internet Explorer") {
				document.getElementById('trMacroInput_' + this.id).innerText = "";
			} else {
				document.getElementById('trMacroInput_' + this.id).innerHTML = "";
			}
		}
	}	
	tdElem3.appendChild(imgElem);	
	trElem.appendChild(tdElem1);
	trElem.appendChild(tdElem2);
	trElem.appendChild(tdElem3);
	tbodyElem.appendChild(trElem);		
	tabElem.appendChild(tbodyElem);	
	globalj++;
	document.getElementById('hiddenMacInput').value = globalj;
}

/*
 * Function for displaying existing macro
 */
function displayExistingMacroHost(max){	
	for (var i = 0; i < max; i++) {	
		var keyElem = document.createElement('input');
		var valueElem = document.createElement('input');
		var imgElem = document.createElement('img');	
		var tabElem = document.getElementById('macroTable');
		var trElem = document.createElement('tr');
		var tbodyElem = document.createElement('tbody');
		
		trElem.id = "trElem_" + globalj;
		if (trMacroClassFlag) {
			trElem.className = "list_one";
			trMacroClassFlag = 0;
		} else {
			trElem.className = "list_two";
			trMacroClassFlag = 1;
		}		
		trElem.id = "trMacroInput_" + globalj;
		
		var tdElem1 = document.createElement('td');
		tdElem1.className = "ListColCenter";
		var tdElem2 = document.createElement('td');
		tdElem2.className = "ListColLeft";
		var tdElem3 = document.createElement('td');
		tdElem3.className = "ListColCenter";	
			
		keyElem.id = 'macroInput_' + globalj;	
		keyElem.name = 'macroInput_' + globalj;
		keyElem.value = globalMacroTabName[globalj];		
		tdElem1.appendChild(keyElem);	
					
		valueElem.id = 'macroValue_' + globalj;
		valueElem.name = 'macroValue_' + globalj;
		valueElem.value = globalMacroTabValue[globalj];		
		tdElem2.appendChild(valueElem);	
			
		imgElem.src = "./img/icones/16x16/delete.gif";
		imgElem.id = globalj;
		imgElem.onclick = function(){
			var response = window.confirm('<?php echo _("Do you confirm this deletion?"); ?>');
			if (response){
				if (navigator.appName == "Microsoft Internet Explorer") {
					document.getElementById('trMacroInput_' + this.id).innerText = "";
				}
				else {
					document.getElementById('trMacroInput_' + this.id).innerHTML = "";
				}
			}
		}
		tdElem3.appendChild(imgElem);		
		trElem.appendChild(tdElem1);
		trElem.appendChild(tdElem2);
		trElem.appendChild(tdElem3);			
		globalj++;
		tbodyElem.appendChild(trElem);
		tabElem.appendChild(tbodyElem);
	}
	document.getElementById('hiddenMacInput').value = globalj;
}

/*
 * Global variables
 */

var globalj=0;
var trMacroClassFlag = 1;
var globalMacroTabId = new Array();
var globalMacroTabName = new Array();
var globalMacroTabValue = new Array();
var globalMacroTabHostId = new Array();

</script>