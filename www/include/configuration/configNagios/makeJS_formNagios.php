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
 * 
 * File : makeJS_formNagios.php D.Porte
 * 
 */
 
?><script type="text/javascript">

var _o = '<?php echo $o;?>';


function delBroker(tid){
	var confirm = 1;

	var inp_field = document.getElementById('in_broker_' + tid);
	if (inp_field.value != "") {
	    confirm = window.confirm('<?php echo _("Do you confirm this deletion?"); ?>');
	}
	if (confirm){
		var p = document.getElementById('tabBroker');
		var oldtb = document.getElementById('tbody_broker_' + tid);
		p.removeChild(oldtb);
		var nb = document.getElementById('hiNbBroker').value;
		nb--;
		if ( nb != 0 ){
			var td = document.getElementById('tabBroker').getElementsByTagName('td')[0];
			td.style.borderTopWidth = "2px";
		}
		document.getElementById('hiNbBroker').value = nb;
	}
}

/*
 *  Add a new broker module
 */
function addBroker() {

	var gNbBk = document.getElementById('hiNbBroker').value;

	var tdPElem = document.getElementById('multipleBroker');
	var tabElem = document.getElementById('tabBroker');

	var tbodyElem = document.createElement('tbody');
	tbodyElem.id = "tbody_broker_" + gListBk;

	var trElem = document.createElement('tr');
	trElem.id = "tr_broker_" + gListBk;

	if (trClassFlag) {
		trElem.className = "list_one";
		trClassFlag = 0;
	} else {
		trElem.className = "list_two";
		trClassFlag = 1;
	}		

	var tdFElem = document.createElement('td');
	tdFElem.className = "FormRowValue";
	if ( gNbBk == 0 )
		tdFElem.style.borderTopWidth = "2px";
	tdFElem.style.borderLeftWidth = "0px";

	var valueElem = document.createElement('input');
	valueElem.type = 'text';
	valueElem.id = 'in_broker_' + gListBk;
	valueElem.name = 'in_broker_' + gListBk;
	valueElem.size = '100';
	tdFElem.appendChild(valueElem);
		
	var imgElem = document.createElement('img');
	imgElem.src = "./img/icones/16x16/delete.gif";
	imgElem.id = gListBk;
	imgElem.title = "Delete this broker module";
	imgElem.onclick = function(){
		delBroker(this.id);	
	}
	tdFElem.appendChild(imgElem);
	trElem.appendChild(tdFElem);
	tbodyElem.appendChild(trElem);
	tabElem.appendChild(tbodyElem);

	tdPElem.appendChild(tabElem);
	gNbBk++;
	gListBk++;
	document.getElementById('hiLsBroker').value = gListBk;
	document.getElementById('hiNbBroker').value = gNbBk;

}

/*
 * Displaying existing broker module
 */
function displayBroker(o){	

	_o = o
	var gNbBk = 0;

	var tdPElem = document.getElementById('multipleBroker');
	var tabElem = document.createElement('table');
	tabElem.id = "tabBroker";
	tabElem.className = "ListTableMultiTp";
	for (var i = 0; i < gBkId.length; i++) {	
		var tbodyElem = document.createElement('tbody');
		tbodyElem.id = "tbody_broker_" + gListBk; 

		var trElem = document.createElement('tr');
		trElem.id = "tr_broker_" + gListBk;
		
		if (trClassFlag) {
			trElem.className = "list_one";
			trClassFlag = 0;
		} else {
			trElem.className = "list_two";
			trClassFlag = 1;
		}		
		
		var tdFElem = document.createElement('td');
		tdFElem.className = "FormRowValue";

		if ( i == 0 ) {
			if ( (_o == "a") || (_o == "c") )
				tdFElem.style.borderTopWidth = "2px";
			else
				tdFElem.style.borderTopWidth = "0px";
		}
		tdFElem.style.borderLeftWidth = "0px";

		var valueElem = document.createElement('input');
		valueElem.type = 'text';
		valueElem.id = 'in_broker_' + gListBk;
		valueElem.name = 'in_broker_' + gListBk;
		valueElem.size = '100';
		valueElem.value = gBkValue[gListBk];
		valueElem.defaultValue = gBkValue[gListBk];
		
		if ( (_o == "a") || (_o == "c") ) {
			tdFElem.appendChild(valueElem);
			var imgElem = document.createElement('img');
			imgElem.src = "./img/icones/16x16/delete.gif";
			imgElem.id = gListBk;
			imgElem.title = "Delete this broker module";
			imgElem.onclick = function(){
				delBroker(this.id);
			}
			tdFElem.appendChild(imgElem);
		} else {
			valueElem.disabled = true;
			tdFElem.appendChild(valueElem);
		}
		trElem.appendChild(tdFElem);
		gListBk++;
		gNbBk++;
		tbodyElem.appendChild(trElem);
		tabElem.appendChild(tbodyElem);
	}
	tdPElem.appendChild(tabElem);
	document.getElementById('hiLsBroker').value = gListBk;
	document.getElementById('hiNbBroker').value = gNbBk;
	if (gNbBk == 0)
		addBroker();
}

function resetBroker(o){
	var node = document.getElementById("tabBroker");
	node.parentNode.removeChild(node);
	gListBk=0;
	displayBroker(o);
}

/*
 * Global variables
 */

var gListBk=0;
var trClassFlag = 0;
var gBkId = new Array();
var gBkValue = new Array();

</script>
