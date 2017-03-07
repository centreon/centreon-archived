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

if (!isset($centreon)) {
    exit();
}

if (!isset($centreon->optGen["AjaxFirstTimeReloadMonitoring"]) || $centreon->optGen["AjaxFirstTimeReloadMonitoring"] == 0) {
    $tFM = 10;
} else {
    $tFM = $centreon->optGen["AjaxFirstTimeReloadMonitoring"] * 1000;
}

if (!isset($centreon->optGen["AjaxFirstTimeReloadStatistic"]) || $centreon->optGen["AjaxFirstTimeReloadStatistic"] == 0) {
    $tFS = 10;
} else {
    $tFS = $centreon->optGen["AjaxFirstTimeReloadStatistic"] * 1000;
}

$sid = session_id();
$time = time();

$obis = $o;
if (isset($_GET["problem"])) {
    $obis .= '_pb';
}
if (isset($_GET["acknowledge"])) {
    $obis .= '_ack_' . $_GET["acknowledge"];
}

?>
<script type="text/javascript">

var _debug = 0;

var _addrXML = "./include/monitoring/status/Hosts/xml/hostXML.php";
var _addrXSL = "./include/monitoring/status/Hosts/xsl/host.xsl";
var _criticality_id = 0;

<?php include_once "./include/monitoring/status/Common/commonJS.php"; ?>

var _selectedElem = new Array();

function set_header_title() {

    var _img_asc = mk_imgOrder('./img/icones/7x7/sort_asc.gif', "asc");
    var _img_desc = mk_imgOrder('./img/icones/7x7/sort_desc.gif', "desc");

    if (document.getElementById('host_name')){
        var h = document.getElementById('host_name');
        h.innerHTML = '<?php echo addslashes(_("Hosts"))?>';
        h.indice = 'host_name';
        h.onclick=function(){change_type_order(this.indice)};
        h.style.cursor = "pointer";

        var h = document.getElementById('current_state');
        h.innerHTML = '<?php echo addslashes(_("Status"))?>';
        h.indice = 'current_state';
        h.onclick=function(){change_type_order(this.indice)};
        h.style.cursor = "pointer";

        var h = document.getElementById('ip');
        h.innerHTML = '<?php echo addslashes(_("IP Address"))?>';
        h.indice = 'ip';
        h.onclick=function(){change_type_order(this.indice)};
        h.style.cursor = "pointer";

        var h = document.getElementById('last_state_change');
        h.innerHTML = '<?php echo addslashes(_("Duration"))?>';
        h.indice = 'last_state_change';
        h.onclick=function(){change_type_order(this.indice)};
        h.style.cursor = "pointer";

        var h = document.getElementById('last_hard_state_change');
        if (h) {
            h.innerHTML = '<?php echo addslashes(_("Hard State Duration"))?>';
            h.indice = 'last_hard_state_change';
            h.onclick=function(){change_type_order(this.indice)};
            h.style.cursor = "pointer";
        }

        var h = document.getElementById('last_check');
        h.innerHTML = '<?php echo addslashes(_("Last Check"))?>';
        h.indice = 'last_check';
        h.onclick=function(){change_type_order(this.indice)};
        h.style.cursor = "pointer";

        var h = document.getElementById('current_check_attempt');
        h.innerHTML = '<?php echo addslashes(_("Tries"))?>';
        h.indice = 'current_check_attempt';
        h.onclick=function(){change_type_order(this.indice)};
        h.style.cursor = "pointer";

        var h = document.getElementById('criticality_id');
        if (h) {
            h.innerHTML = '<?php echo addslashes("S"); ?>';
            h.indice = 'criticality_id';
            h.title = "<?php echo _("Sort by severity"); ?>";
            h.onclick=function(){change_type_order(this.indice)};
            h.style.cursor = "pointer";
        }

        var h = document.getElementById('plugin_output');
        h.innerHTML = '<?php echo addslashes(_("Status information"))?>';
        h.indice = 'plugin_output';
        h.onclick=function(){change_type_order(this.indice)};
        h.style.cursor = "pointer";

        if (_sort_type) {
            var h = document.getElementById(_sort_type);
            var _linkaction_asc = document.createElement("a");
            if (_order == 'ASC') {
                _linkaction_asc.appendChild(_img_asc);
            } else {
                _linkaction_asc.appendChild(_img_desc);
            }
            _linkaction_asc.href = '#' ;
            _linkaction_asc.onclick=function(){change_order()};
            if (h) {
                h.appendChild(_linkaction_asc);
            }
        }
    }
}

function mainLoopHost() {

    _currentInputField = document.getElementById('host_search');
    if (document.getElementById('host_search') && document.getElementById('host_search').value) {
        _currentInputFieldValue = document.getElementById('host_search').value;
    } else {
        _currentInputFieldValue = "";
    }

    if ((_currentInputFieldValue.length >= 3 || _currentInputFieldValue.length == 0) && _oldInputFieldValue != _currentInputFieldValue){
        if (!_lock) {
            set_search_host(escapeURI(_currentInputFieldValue));
            _host_search = _currentInputFieldValue;

            monitoring_refresh();

            if (_currentInputFieldValue.length >= 3) {
                _currentInputField.className = "search_input_active";
            } else {
                _currentInputField.className = "search_input";
            }
        }
    }
    _oldInputFieldValue = _currentInputFieldValue;

    setTimeout("mainLoopHost()", 250);
}

function initM(_time_reload, _sid, _o ){

    // INIT Select objects
    construct_selecteList_ndo_instance('instance_selected');
    construct_HostGroupSelectList('hostgroups_selected');

    if (document.getElementById("host_search") && document.getElementById("host_search").value) {
        _host_search = document.getElementById("host_search").value;
        viewDebugInfo('service search: '+document.getElementById("host_search").value);
    } else if (document.getElementById("host_search").length == 0) {
        _host_search = "";
    }

    if (document.getElementById("critFilter") && document.getElementById("critFilter").value) {
        _criticality_id = document.getElementById("critFilter").value;
        viewDebugInfo('Host criticality: '+document.getElementById("critFilter").value);
    }

    if (_first){
        mainLoopHost();
        _first = 0;
    }

    _time=<?php echo $time; ?>;
    if (_on) {
        goM(_time_reload,_sid,_o);
    }
}

function goM(_time_reload, _sid, _o) {

    _lock = 1;
    var proc = new Transformation();

    // INIT search informations
    if (_counter == 0) {
        document.getElementById("host_search").value = _host_search;
        _counter += 1;
    }

    var statusHost = jQuery.trim(jQuery('#statusHost').val());
    var statusFilter = jQuery.trim(jQuery('#statusFilter').val());

    proc.setCallback(monitoringCallBack);
    proc.setXml(_addrXML+"?"+'search='+_host_search+'&num='+_num+'&limit='+_limit+'&sort_type='+_sort_type+'&order='+_order+'&date_time_format_status='+_date_time_format_status+'&o='+_o+'&p='+_p+'&time=<?php print time(); ?>&criticality='+_criticality_id+'&statusHost='+statusHost+'&statusFilter='+statusFilter+"&sSetOrderInMemory="+sSetOrderInMemory);
    proc.setXslt(_addrXSL);
    proc.transform("forAjax");

    _lock = 0;
    _timeoutID = setTimeout('goM("'+ _time_reload +'","'+ _sid +'","'+_o+'")', _time_reload);
    _time_live = _time_reload;
    _on = 1;

    set_header_title();
}

function unsetCheckboxes() {
    for (keyz in _selectedElem) {
        if (keyz == _selectedElem[keyz]) {
            removeFromSelectedElem(decodeURIComponent(keyz));
                if (document.getElementById(decodeURIComponent(keyz))) {
                    document.getElementById(decodeURIComponent(keyz)).checked = false;
                }
        }
    }
}

function cmdCallback(cmd) {
    jQuery('.centreon-popin').remove();
    var keyz;

    _cmd = cmd;
    _getVar = "";
    if (cmd != '72' && cmd != '75') {
        return 1;
    } else {
        for (keyz in _selectedElem) {
            if ((keyz == _selectedElem[keyz]) && typeof(document.getElementById(decodeURIComponent(keyz)) != 'undefined') &&
                document.getElementById(decodeURIComponent(keyz))) {
                if (document.getElementById(decodeURIComponent(keyz)).checked) {
                    _getVar += '&select[' + encodeURIComponent(keyz) + ']=1';
                }
            }
        }

        var url = './include/monitoring/external_cmd/popup/popup.php?sid='+ _sid + '&o=' + _o + '&p='+ _p +'&cmd='+ cmd + _getVar;

        var popin = jQuery('<div>');
        popin.centreonPopin({open:true,url:url});
        window.currentPopin = popin;
        return 0;
    }
}

function send_the_command() {
    if (window.XMLHttpRequest) {
        xhr_cmd = new XMLHttpRequest();
    }
    else if (window.ActiveXObject)
    {
        xhr_cmd = new ActiveXObject("Microsoft.XMLHTTP");
    }
    var comment = encodeURIComponent(document.getElementById('popupComment').value.trim());
    if (comment == "") {
        alert(_popup_no_comment_msg);
        return 0;
    }
    if (_cmd == '70' || _cmd == '72') {

        if (document.getElementById('sticky')) {
            var sticky = document.getElementById('sticky').checked;
        } else
            var sticky = 1;

        if (document.getElementById('persistent')) {
            var persistent = document.getElementById('persistent').checked;
        } else {
            var persistent = 1;
        }

        if (document.getElementById('notify')) {
            var notify = document.getElementById('notify').checked;
        } else {
            var notify = 0;
        }

        if (document.getElementById('force_check')) {
            var force_check = document.getElementById('force_check').checked;
        } else {
            var force_check = 0;
        }

        var ackhostservice = 0;
        if (document.getElementById('ackhostservice')) {
            ackhostservice = document.getElementById('ackhostservice').checked;
        }

        var author = document.getElementById('author').value;

        xhr_cmd.open("GET", "./include/monitoring/external_cmd/cmdPopup.php?cmd=" + _cmd + "&comment=" + comment + "&sticky=" + sticky + "&persistent=" + persistent + "&notify=" + notify + "&ackhostservice=" + ackhostservice + "&force_check=" + force_check + "&author=" + author  + _getVar, true);
    }
    else if (_cmd == '74' || _cmd == '75') {
        var downtimehostservice = 0;
        if (document.getElementById('downtimehostservice')) {
            downtimehostservice = document.getElementById('downtimehostservice').checked;
        }
        if (document.getElementById('fixed')) {
            var fixed = document.getElementById('fixed').checked;
        }
        else {
            var fixed = 0;
        }
        var start = document.getElementById('start').value+' '+document.getElementById('start_time').value;
                var end = document.getElementById('end').value+' '+document.getElementById('end_time').value;
        var author = document.getElementById('author').value;
        var duration = document.getElementById('duration').value;
        var duration_scale = document.getElementById('duration_scale').value;
        var tmp = document.querySelector('input[name="host_or_centreon_time[host_or_centreon_time]"]:checked');
        var host_or_centreon_time = "0";
        if(tmp !== null && typeof tmp !== "undefined" ){
            host_or_centreon_time = tmp.value;
        }
        xhr_cmd.open("GET", "./include/monitoring/external_cmd/cmdPopup.php?cmd=" + _cmd + "&duration=" + duration + "&duration_scale=" + duration_scale + "&start=" + start + "&end=" + end +  "&comment=" + comment + "&fixed=" + fixed + "&host_or_centreon_time=" + host_or_centreon_time + "&downtimehostservice=" + downtimehostservice + "&author=" + author  + _getVar, true);
    }
    xhr_cmd.send(null);
    window.currentPopin.centreonPopin("close");
    //Modalbox.hide();
    unsetCheckboxes();
}

function toggleFields(fixed)
{
    var dur;
    dur = document.getElementById('duration');
    if (fixed.checked) {
        dur.disabled = true;
    }
    else {
        dur.disabled = false;
    }
}
</script>
