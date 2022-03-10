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

?>
<script type="text/javascript" src="./include/common/javascript/jquery/plugins/qtip/jquery-qtip.js"></script>
<script type="text/javascript" src="./lib/HTML/QuickForm/qfamsHandler-min.js"></script>
<script type="text/javascript" src="./include/common/javascript/jquery/plugins/centreon/jquery.centreonValidate.js"></script>
<script type="text/javascript">

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
        keyElem.className = 'v_required v_regex';
        keyElem.setAttribute('data-validator', '^((([0-9]{4}-[0-9]{2}-[0-9]{2})|(day ([0-9]{1,2}|-[0-9]{1,2})( - ([0-9]{1,2}|-[0-9]{1,2}))?)|((sunday|monday|tuesday|wednesday|thursday|friday|saturday) ([0-9]{1,2}|-[0-9]{1,2})( (january|february|march|april|may|june|july|august|september|october|november|december))?)|((january|february|march|april|may|june|july|august|september|october|november|december) ([0-9]{1,2}|-[0-9]{1,2})( - ([0-9]{1,2}|-[0-9]{1,2}))?))( - )?( \/ [0-9]{1,2})?)+$');
        tdElem1.appendChild(keyElem);

        valueElem.id = 'exceptionTimerange_' + globalj;
        valueElem.name = 'exceptionTimerange_' + globalj;
        valueElem.value = "";
        valueElem.className = 'v_required v_regex';
        valueElem.setAttribute('data-validator', '^([0-9]{2}:[0-9]{2}-[0-9]{2}:[0-9]{2}(,)?)+$');
        tdElem2.appendChild(valueElem);

        imgElem.src = "./img/icons/circle-cross.svg";
        imgElem.class = 'ico-14-circle-cross';
        imgElem.id = globalj;
        imgElem.onclick = function () {
            var response = window.confirm('<?php echo _("Do you confirm this deletion?"); ?>');
            if (response) {
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
    function displayExistingExceptions(max) {
        for (var i = 0; i < max; i++) {
            var keyElem = document.createElement('input');
            var valueElem = document.createElement('input');
            var imgElem = document.createElement('img');
            var tabElem = document.getElementById('exceptionTable');
            var trElem = document.createElement('tr');
            var tbodyElem = document.createElement('tbody');
            var _o = '<?php echo $o; ?>';


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

            imgElem.src = "./img/icons/circle-cross.svg";
            imgElem.class = 'ico-14-circle-cross';
            imgElem.id = globalj;
            imgElem.onclick = function () {
                var response = window.confirm('<?php echo _("Do you confirm this deletion?"); ?>');
                if (response) {
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
     * Dynamic validation of Time range exceptions fileds
     */
    function purgeHideInput(tab) {
        jQuery('.tab').each(function(idx, el){
            if (el.id != tab) {
                jQuery(el).find(':input').each(function(idx, input){
                    jQuery(input).qtip('destroy');
                });
            }
        });
    }

    function formValidate() {
        jQuery('#Form').centreonValidate();
        jQuery('#Form').centreonValidate('validate');

        if (jQuery('#Form').centreonValidate('hasError')) {
            var activeTab = jQuery('.tab').filter(function(index) { return jQuery(this).css('display') === 'block'; })[0];
            purgeHideInput(activeTab.id);

            return false;
        }

        return true;
    }

    /*
     * Global variables
     */

    var globalj = 0;
    var trExceptionClassFlag = 1;
    var globalExceptionTabId = new Array();
    var globalExceptionTabName = new Array();
    var globalExceptionTabTimerange = new Array();
    var globalExceptionTabTimeperiodId = new Array();

</script>