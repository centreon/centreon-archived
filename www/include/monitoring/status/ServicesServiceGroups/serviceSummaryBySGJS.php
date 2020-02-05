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

if (!isset($oreon->optGen["AjaxFirstTimeReloadStatistic"]) || $oreon->optGen["AjaxFirstTimeReloadStatistic"] == 0) {
    $tFS = 10;
} else {
    $tFS = $oreon->optGen["AjaxFirstTimeReloadStatistic"] * 1000;
}
if (!isset($oreon->optGen["AjaxFirstTimeReloadMonitoring"]) || $oreon->optGen["AjaxFirstTimeReloadMonitoring"] == 0) {
    $tFM = 10;
} else {
    $tFM = $oreon->optGen["AjaxFirstTimeReloadMonitoring"] * 1000;
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

    var _addrXML = "./include/monitoring/status/ServicesServiceGroups/xml/serviceSummaryBySGXML.php";
    var _addrXSL = "./include/monitoring/status/ServicesServiceGroups/xsl/serviceSummaryBySG.xsl";

    <?php include_once "./include/monitoring/status/Common/commonJS.php"; ?>

    // Service Groups view hasn't got HG filters
    _hostgroup_enable = 0;

    function set_header_title() {
        var _img_asc = mk_imgOrder('./img/icones/7x7/sort_asc.gif', "asc");
        var _img_desc = mk_imgOrder('./img/icones/7x7/sort_desc.gif', "desc");

        if (document.getElementById('host_name')) {
            var h = document.getElementById('host_name');
            h.innerHTML = '<?php echo addslashes(_("Servicegroups / Hosts"))?>';
            h.indice = 'host_name';
            h.onclick = function () {
                change_type_order(this.indice)
            };
            h.style.cursor = "pointer";

            var h = document.getElementById('services');
            h.innerHTML = '<?php echo addslashes(_("Services informations"))?>';
            h.indice = 'services';

            var h = document.getElementById(_sort_type);
            var _linkaction_asc = document.createElement("a");
            if (_order == 'ASC')
                _linkaction_asc.appendChild(_img_asc);
            else
                _linkaction_asc.appendChild(_img_desc);
            _linkaction_asc.href = '#';
            _linkaction_asc.onclick = function () {
                change_order()
            };
            h.appendChild(_linkaction_asc);
        }
    }

    function mainLoopLocal() {
        _currentInputField = document.getElementById('host_search');
        if (document.getElementById('host_search') && document.getElementById('host_search').value) {
            _currentInputFieldValue = document.getElementById('host_search').value;
        } else {
            _currentInputFieldValue = "";
        }

        if ((_currentInputFieldValue.length >= 3 || _currentInputFieldValue.length == 0) &&
            _oldInputFieldValue != _currentInputFieldValue
        ) {
            if (!_lock) {
                set_search_host(escapeURI(_currentInputFieldValue));
                _host_search = _currentInputFieldValue;
                _sg_search = _currentInputFieldValue;

                monitoring_refresh();

                if (_currentInputFieldValue.length >= 3) {
                    _currentInputField.className = "search_input_active";
                } else {
                    _currentInputField.className = "search_input";
                }
            }
        }
        _oldInputFieldValue = _currentInputFieldValue;
        setTimeout(mainLoopLocal, 250);
    }

    function initM(_time_reload, _o) {

        // INIT Select objects
        construct_selecteList_ndo_instance('instance_selected');
        construct_HostGroupSelectList('hostgroups_selected');

        if (document.getElementById("host_search") && document.getElementById("host_search").value) {
            _host_search = document.getElementById("host_search").value;
            viewDebugInfo('search: ' + document.getElementById("host_search").value);
        } else if (document.getElementById("host_search").value.length === 0) {
            _host_search = "";
        }

        if (document.getElementById("sg_search") && document.getElementById("sg_search").value) {
            _sg_search = document.getElementById("sg_search").value;
            viewDebugInfo('search: ' + document.getElementById("sg_search").value);
        } else if (document.getElementById("sg_search").value.length == 0) {
            _sg_search = "";
        }

        if (_first) {
            mainLoopLocal();
            _first = 0;
        }

        _time =<?php echo $time; ?>;
        if (_on) {
            goM(_time_reload, _o);
        }
    }

    function goM(_time_reload, _o) {
        _lock = 1;
        var proc = new Transformation();
        proc.setCallback(function(t){monitoringCallBack(t); proc = null;});
        proc.setXml(_addrXML + "?" + '&host_search=' + _host_search + '&sg_search=' + _sg_search + '&num=' + _num +
            '&limit=' + _limit + '&sort_type=' + _sort_type + '&order=' + _order +
            '&date_time_format_status=' + _date_time_format_status + '&o=' + _o +
            '&p=' + _p + '&time=<?php print time(); ?>'
        );
        proc.setXslt(_addrXSL);
        if (handleVisibilityChange()) {
            proc.transform("forAjax");
        }

        if (_counter == 0) {
            document.getElementById("host_search").value = _host_search;
            document.getElementById("sg_search").value = _sg_search;
            _counter += 1;
        }

        _lock = 0;
        _timeoutID = cycleVisibilityChange(function(){goM(_time_reload, _o)}, _time_reload);
        _time_live = _time_reload;
        _on = 1;
        set_header_title();
    }
</SCRIPT>