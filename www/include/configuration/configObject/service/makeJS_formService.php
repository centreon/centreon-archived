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
?>

<script type="text/javascript">

var _o='<?php echo $o;?>';
var _p='<?php echo $p;?>';

/*
**  This block is the javascript code for the multi macro creation 
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

function displayExistingMacroSvc(max){
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
var globalMacroTabSvcId = new Array();

</script>