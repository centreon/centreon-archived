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
<script language='javascript' type='text/javascript'>
function mk_pagination(){}
function mk_paginationFF(){}
function set_header_title(){}

var trapId = '<?php echo $traps_id;?>';
var nextRowId;
var counter = 0;
var nbOfInitialRows = 0;
var o = '<?php echo $o;?>';

/*
 * Transform our div
 */
function transformForm()
{
    var params;
    var proc;
    var addrXML;
    var addrXSL;

    nbOfInitialRows = '<?php echo $nbOfInitialRows; ?>';

	if (trapId && o == 'w') {
		params = '?trapId=' + trapId;
    	proc = new Transformation();
    	addrXML = './include/configuration/configObject/traps/xml/trapXml.php' + params;
    	addrXSL = './include/configuration/configObject/traps/xsl/trap-ro.xsl';
    	proc.setXml(addrXML);
    	proc.setXslt(addrXSL);
    	proc.transform("dynamicDiv");
        trapId = 0;
        o = 0;
	} else if (trapId || o == 'a') {
        params = '?trapId=' + trapId;
    	proc = new Transformation();
    	addrXML = './include/configuration/configObject/traps/xml/trapXml.php' + params;
    	addrXSL = './include/configuration/configObject/traps/xsl/trap.xsl';
    	proc.setXml(addrXML);
    	proc.setXslt(addrXSL);
    	proc.transform("dynamicDiv");
        trapId = 0;
        o = 0;
    } else {
    	params = '?id=' + counter + '&nbOfInitialRows=' + nbOfInitialRows;
        proc = new Transformation();
    	addrXML = './include/configuration/configObject/traps/xml/additionalRowXml.php' + params;
    	addrXSL = './include/configuration/configObject/traps/xsl/additionalRow.xsl';
    	proc.setXml(addrXML);
    	proc.setXslt(addrXSL);
    	proc.transform(nextRowId);
    }
}

/*
 * called when the 'Advanced matching options' checkbox is clicked
 */
function toggleParams(checkValue) {

    if (checkValue == true) {
        transformForm();
        Effect.Appear('dynamicDiv', { duration : 0 });
        //document.getElementById('trapStatus').disabled = true;
    } else {
        Effect.Fade('dynamicDiv', { duration : 0 });
        //document.getElementById('trapStatus').disabled = false;
    }

}

/*
 * Initialises advanced parameters
 */
function initParams() {
	if (document.getElementById('traps_advanced_treatment')) {
		var adv = false;
		if (document.getElementById('traps_advanced_treatment').type == 'checkbox') {
			adv = document.getElementById('traps_advanced_treatment').checked;
		}
		if (document.getElementById('traps_advanced_treatment').type == 'hidden') {
			if (document.getElementById('traps_advanced_treatment').value == 1) {
				adv = true;
			}
		}
    	toggleParams(adv);
	}
}

/*
 * Function is called when the '+' button is pressed
 */
function addNewRow() {
    counter++;
    nextRowId = 'additionalRow_' + counter;
    transformForm();
}

/*
 * function that is called when the 'x' button is pressed
 */
function removeTr(trId) {
    if (document.getElementById(trId)) {
    	if (navigator.appName == "Microsoft Internet Explorer") {
			document.getElementById(trId).innerText = "";
    	} else {
    		document.getElementById(trId).innerHTML = "";
        }
    	Effect.Fade(trId, { duration : 0 });
    }
}
</script>