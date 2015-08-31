<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
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
	tdElem1.className = "ListColLeft";
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
		tdElem1.className = "ListColLeft";
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