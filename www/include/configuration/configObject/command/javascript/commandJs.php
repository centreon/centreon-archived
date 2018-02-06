<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */
?><script type="text/javascript">

var listArea;
var macroSvc  = new Array();
var macroHost = new Array();

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
    for (i = 1; document.getElementById('desc_'+i); i++) {
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

function manageMacros() {
    var commandLine = document.Form.command_line.value;
    var commandId = document.Form.command_id.value;
    var tmpStr = "";

    Modalbox.show('./include/configuration/configObject/command/formMacros.php?cmd_line=' + commandLine + '&cmdId=' + commandId + '&textArea=' + tmpStr, {title: 'Macro description', width:800});
}

function setMacrosDescriptions() {
    var i;
    var tmpStr2;
    var listDiv;

    tmpStr2 = "";
    listArea = document.getElementById('listOfMacros');
    listDiv = document.getElementById('listOfMacros');
    for (i = 0; document.getElementById('desc_'+i); i++) {
            var type = "HOST";
            if (document.getElementById('type_'+i).value == 2) {
                type = "SERVICE";
            }
            tmpStr2 += "MACRO ("+ type +") "+ document.getElementById('macro_'+i).value + " : " + document.getElementById('desc_'+i).value + "\n";
    }
        
    listArea.cols= 100;
    listArea.rows= i;
        
    listArea.value = tmpStr2;
    listDiv.style.visibility = "visible";
    Modalbox.hide();
}

function checkType(value) {
    var action = jQuery('form#Form').attr('action');
    switch(value) {
        case '1':
            action = action.replace(/p=\d+/,'p=60802');
            break;
        case '2':
            action = action.replace(/p=\d+/,'p=60801');
            break;
        case '3':
            action = action.replace(/p=\d+/,'p=60803');
            break;
        case '4':
            action = action.replace(/p=\d+/,'p=60807');
            break;
        default:
            action = action.replace(/p=\d+/,'p=60801');
            break;
    }

    if (action.match(/&type=/)) {
        action = action.replace(/&type=\d+/, '&type=' + value);
    } else {
        action += '&type=' + value;
    }

    jQuery('form#Form').attr('action', action);
}

</script>