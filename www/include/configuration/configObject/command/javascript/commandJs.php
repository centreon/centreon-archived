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
?>
<script type="text/javascript">
var listArea;

function goPopup() {
	var cmd_line;
	var tmpStr;
	var reg = new RegExp("(\n)", "g");

	listArea = document.getElementById('listOfArg');
	tmpStr = listArea.value;
	tmpStr = tmpStr.replace(reg, ";;;");
	cmd_line = document.getElementById('command_line').value;

	Modalbox.show('./include/configuration/configObject/command/formArguments.php?cmd_line=' + cmd_line + '&textArea=' + tmpStr, {title: 'Argument description', width:800});
}

function setDescriptions() {
	var i;
	var tmpStr2;
	var listDiv;

	tmpStr2 = "";
	listArea = document.getElementById('listOfArg');
	listDiv = document.getElementById('listOfArgDiv');
	for (i=1; document.getElementById('desc_'+i); i++) {
		tmpStr2 += "ARG" + document.getElementById('macro_'+i).value + " : " + document.getElementById('desc_'+i).value + "\n";
	}
	listArea.cols=100;
	listArea.rows=i;
	listArea.value = tmpStr2;
	listDiv.style.visibility = "visible";
	Modalbox.hide();
}

function closeBox() {
	Modalbox.hide();
}

function clearArgs() {
	listArea = document.getElementById('listOfArg');
	listArea.value = "";
}

</script>