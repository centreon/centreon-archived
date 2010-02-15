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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/configuration/configObject/host/makeJS_formHost.php $
 * SVN : $Id: makeJS_formHost.php 8881 2009-08-13 10:43:05Z nfilus $
 * 
 */
 
?><script type="text/javascript">

/*
 *  This second block is the javascript code for the multi exception creation 
 */
function addBlankInput() {
	var tabElem = document.getElementById('exceptionTable');
	var keyElem = document.createElement('input');
	var valueElem = document.createElement('input');
	var imgElem = document.createElement('img');			
	var trElem = document.createElement('tr');
	var tbodyElem = document.createElement('tbody');
	
	trElem.id = "trElem_" + globalj;
	if (trExceptionClassFlag) {
		trElem.className = "list_one";
		trExceptionClassFlag = 0;
	} else {
		trElem.className = "list_two";
		trExceptionClassFlag = 1;
	}
	
	trElem.id = "trExceptionInput_" + globalj;	
	var tdElem1 = document.createElement('td');
	tdElem1.className = "ListColCenter";
	var tdElem2 = document.createElement('td');
	tdElem2.className = "ListColLeft";
	var tdElem3 = document.createElement('td');
	tdElem3.className = "ListColCenter";
	keyElem.id = 'exceptionInput_' + globalj;	
	keyElem.name = 'exceptionInput_' + globalj;
	keyElem.value = '';
	tdElem1.appendChild(keyElem);	
				
	valueElem.id = 'exceptionTimerange_' + globalj;
	valueElem.name = 'exceptionTimerange_' + globalj;
	valueElem.value = "";	
	tdElem2.appendChild(valueElem);	
		
	imgElem.src = "./img/icones/16x16/delete.gif";
	imgElem.id = globalj;	
	imgElem.onclick = function(){
		var response = window.confirm('<?php echo _("Do you confirm this deletion?"); ?>');
		if (response){			
			if (navigator.appName == "Microsoft Internet Explorer") {
				document.getElementById('trExceptionInput_' + this.id).innerText = "";
			} else {
				document.getElementById('trExceptionInput_' + this.id).innerHTML = "";
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
	document.getElementById('hiddenExInput').value = globalj;
}


/*
 * Function for displaying existing exceptions
 */
function displayExistingExceptions(max){	
	for (var i = 0; i < max; i++) {	
		var keyElem = document.createElement('input');
		var valueElem = document.createElement('input');
		var imgElem = document.createElement('img');	
		var tabElem = document.getElementById('exceptionTable');
		var trElem = document.createElement('tr');
		var tbodyElem = document.createElement('tbody');
		
		trElem.id = "trElem_" + globalj;
		if (trExceptionClassFlag) {
			trElem.className = "list_one";
			trExceptionClassFlag = 0;
		} else {
			trElem.className = "list_two";
			trExceptionClassFlag = 1;
		}		
		trElem.id = "trExceptionInput_" + globalj;
		
		var tdElem1 = document.createElement('td');
		tdElem1.className = "ListColCenter";
		var tdElem2 = document.createElement('td');
		tdElem2.className = "ListColLeft";
		var tdElem3 = document.createElement('td');
		tdElem3.className = "ListColCenter";	
			
		keyElem.id = 'exceptionInput_' + globalj;	
		keyElem.name = 'exceptionInput_' + globalj;
		keyElem.value = globalExceptionTabName[globalj];		
		tdElem1.appendChild(keyElem);	
					
		valueElem.id = 'exceptionTimerange_' + globalj;
		valueElem.name = 'exceptionTimerange_' + globalj;
		valueElem.value = globalExceptionTabTimerange[globalj];		
		tdElem2.appendChild(valueElem);	
		
		if (_o == "w") {
			keyElem.disabled = true;
			valueElem.disabled = true;
		}
		
		imgElem.src = "./img/icones/16x16/delete.gif";
		imgElem.id = globalj;
		imgElem.onclick = function(){
			var response = window.confirm('<?php echo _("Do you confirm this deletion?"); ?>');
			if (response){
				if (navigator.appName == "Microsoft Internet Explorer") {
					document.getElementById('trExceptionInput_' + this.id).innerText = "";
				}
				else {
					document.getElementById('trExceptionInput_' + this.id).innerHTML = "";
				}
			}
		}
		tdElem3.appendChild(imgElem);
		trElem.appendChild(tdElem1);
		trElem.appendChild(tdElem2);
		if (_o != "w") {
			trElem.appendChild(tdElem3);
		}			
		globalj++;
		tbodyElem.appendChild(trElem);
		tabElem.appendChild(tbodyElem);
	}
	document.getElementById('hiddenExInput').value = globalj;
}

/*
 * Global variables
 */

var globalj=0;
var trExceptionClassFlag = 1;
var globalExceptionTabId = new Array();
var globalExceptionTabName = new Array();
var globalExceptionTabTimerange = new Array();
var globalExceptionTabTimeperiodId = new Array();

</script>