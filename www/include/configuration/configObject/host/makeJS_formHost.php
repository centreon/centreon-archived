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

	if (isset($num) && $num < 0)
		$num = 0;
?>

<script type="text/javascript">

var _o='<?php echo $o;?>';
var _p='<?php echo $p;?>';

/*
**  This first block is the javascript code for the multi template creation
**  There is also a second block for the multi macro creation 
*/


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
	tabElem = document.getElementById('multiTpTable');
	var trElem = document.createElement('tr');	
	var tdElem = document.createElement('td');
	var divElem = document.getElementById('parallelTemplate');;
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

	tdElem.appendChild(selectElem);
	
	var imgElem = document.createElement("img");
	imgElem.src = "./img/icones/16x16/delete.gif";	
	imgElem.onclick = function(){		
		tabElem.removeChild(trElem);
	}
	tdElem.appendChild(imgElem);
	
	trElem.appendChild(tdElem);
	if (trClassFlag) {
		trClassFlag = 0;
		trElem.className = "list_one";
	}
	else {
		trClassFlag = 1;
		trElem.className = "list_two";
	}
	tabElem.appendChild(trElem);	
	divElem.appendChild(tabElem);
	
	//We create a hidden input so that the php sided code can retrieve the globalk variable
	var hidElem = document.getElementById("hiddenInput");
	hidElem.value = globalk;	
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
	    var imgElem;	    
	    
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
			   	if (tab[globalk] == id) {
					optionElem.selected = true;
			  	}
			    optionElem.value = id;
			    optionElem.text = alias;    
			    selectElem.appendChild(optionElem);
			}			
			
			tabElem.class = "ListTableMultiTp";		
			tdElem.class = "FormRowValue";
			
			tdElem.appendChild(selectElem);
			
			imgElem = document.createElement("img");
			imgElem.id = globalk;
			imgElem.src = "./img/icones/16x16/delete.gif";			
			imgElem.title = '<?php echo _("Delete");?>';
			imgElem.onclick = function(){				
				var response = window.confirm('<?php echo _("Do you confirm this deletion?"); ?>');
				if (response) {
					document.getElementById('trElem_' + this.id).innerHTML = "";
				}
			}
			tdElem.appendChild(imgElem);			
			trElem.appendChild(tdElem);
			tabElem.appendChild(trElem);	
		}	
		var divElem = document.getElementById('parallelTemplate');
		
		divElem.appendChild(tabElem)
		
		//We create a hidden input so that the php sided code can retrieve the globalk variable
		var hidElem = document.getElementById("hiddenInput");
		hidElem.value = globalk;
}

/*
** Create the select after the reception of XML data
*/
function get_select_options() {
	if (xhr.readyState != 4 && xhr.readyState != "complete")		
    	return(0);
    if (xhr.status == 200)
    {    	
    	displaySelectedTp(xhr);
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
    <?php
    if ($o == "a" || $o == "mc")
    	$host_id = -1; 
    ?>
    xhr.open("GET", "./include/configuration/configObject/host/makeXMLhost.php?host_id="+<?php echo $host_id;?>, true);
    xhr.send(null);    
}

/*
** Global variables
*/
var tab = new Array();
var xhr;
var globalk;
var trClassFlag = 1;

/*
**  This second block is the javascript code for the multi macro creation 
*/
function addBlankInput() {
	var tabElem = document.getElementById('macroTable');
	var keyElem = document.createElement('input');
	var valueElem = document.createElement('input');
	var imgElem = document.createElement('img');			
	var trElem = document.createElement('tr');
	trElem.id = "trElem_" + globalj;
	if (trMacroClassFlag) {
		trElem.className = "list_one";
		trMacroClassFlag = 0;
	}
	else {
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
			document.getElementById('trMacroInput_' + this.id).innerHTML = "";
		}
	}	
	tdElem3.appendChild(imgElem);	
	trElem.appendChild(tdElem1);
	trElem.appendChild(tdElem2);
	trElem.appendChild(tdElem3);		
	tabElem.appendChild(trElem);	
	globalj++;
	document.getElementById('hiddenMacInput').value = globalj;
}

/*
** Function for displaying existing macro
*/

function displayExistingMacroHost(max){
	for (var i=0; i < max; i++) {
		var keyElem = document.createElement('input');
		var valueElem = document.createElement('input');
		var imgElem = document.createElement('img');	
		var tabElem = document.getElementById('macroTable');
		var trElem = document.createElement('tr');
		
		trElem.id = "trElem_" + globalj;
		if (trMacroClassFlag) {
			trElem.className = "list_one";
			trMacroClassFlag = 0;
		}
		else {
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
				document.getElementById('trMacroInput_' + this.id).innerHTML = "";
			}
		}
		tdElem3.appendChild(imgElem);		
		trElem.appendChild(tdElem1);
		trElem.appendChild(tdElem2);
		trElem.appendChild(tdElem3);			
		globalj++;
		tabElem.appendChild(trElem);
	}
	document.getElementById('hiddenMacInput').value = globalj;
}

/*
** Global variables
*/
var globalj=0;
var trMacroClassFlag = 1;
var globalMacroTabId = new Array();
var globalMacroTabName = new Array();
var globalMacroTabValue = new Array();
var globalMacroTabHostId = new Array();

</script>