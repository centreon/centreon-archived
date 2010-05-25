<?php
/*
 * Copyright 2005-2010 MERETHIS
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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configObject/traps/ajaxTrap_js.php $
 * SVN : $Id: ajaxTrap_js.php 8144 2009-05-20 21:11:21Z jmathis $
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
** Transform our div
*/
function transformForm()
{
    var params;
    var proc;
    var addrXML;
    var addrXSL;
    
    nbOfInitialRows = '<?php echo $nbOfInitialRows; ?>';    
    
    if (trapId || o == 'a') {
        params = '?trapId=' + trapId;
    	proc = new Transformation();
    	addrXML = './include/configuration/configObject/traps/xml/trapXml.php' + params;
    	addrXSL = './include/configuration/configObject/traps/xsl/trap.xsl';
    	proc.setXml(addrXML);
    	proc.setXslt(addrXSL);
    	proc.transform("dynamicDiv");
        trapId = 0;
        o = 0;
    }
    else {
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
** called when the 'Advanced matching options' checkbox is clicked
*/
function toggleParams(checkValue)
{
    if (checkValue == true) {
        transformForm();
        Effect.Appear('dynamicDiv', { duration : 0 });
        document.getElementById('trapStatus').disabled = true;
    }
    else {
        Effect.Fade('dynamicDiv', { duration : 0 });
        document.getElementById('trapStatus').disabled = false;
    }
}

/*
** Initialises advanced parameters
*/
function initParams()
{
    if (document.getElementById('traps_advanced_treatment')) {
        toggleParams(document.getElementById('traps_advanced_treatment').checked);
    }
}

/*
** Function is called when the '+' button is pressed
*/
function addNewRow()
{    
    counter++;
    nextRowId = 'additionalRow_' + counter;
    transformForm();
}

/*
** function that is called when the 'x' button is pressed
*/
function removeTr(trId)
{
    if (document.getElementById(trId)) {
        document.getElementById(trId).innerText = '<td></td>';
        Effect.Fade(trId, { duration : 0 });
    }
}
</script>