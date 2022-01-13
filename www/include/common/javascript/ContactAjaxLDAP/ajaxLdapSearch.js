/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

var _addrSearchM = "include/configuration/configObject/contact/ldapsearch.php"

function getXhrM() {
    if (window.XMLHttpRequest) // Firefox and others
        var xhrM = new XMLHttpRequest();
    else if (window.ActiveXObject) { // Internet Explorer
        try {
            var xhrM = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            var xhrM = new ActiveXObject("Microsoft.XMLHTTP");
        }
    } else { // if XMLHttpRequest isn't supported by the browser
        alert("Your browser doesn't support XMLHTTPRequest objects");
        var xhrM = false;
    }
    return xhrM;
}

jQuery(() => {
    jQuery('#Form').submit(() => {
        const selectedContacts = jQuery('input[type="checkbox"][name^="contact"]:not(:checked)');
        selectedContacts.each((_, element) => {
            const contactId = jQuery(element).val();
            jQuery('input[type="hidden"][name$="[' + contactId + ']"]').remove();
        });
        return true;
    });
});


function LdapSearch() {
    var confList = [];
    var ldap_search_filters = '';
    jQuery('input[name^=ldapConf]:checked').each(function () {
        var el = jQuery(this);
        if (el.is(':checked')) {
            key = el.attr('name');
            var matches = key.match(/ldapConf\[(\d+)\]/);
            if (matches[1] != undefined) {
                confList.push(matches[1]);
                var filterVal = jQuery('input[name^="ldap_search_filter\[' + matches[1] + '\]"]').first().val();
                if (filterVal) {
                    ldap_search_filters += '&ldap_search_filter[' + matches[1] + ']=';
                    filterVal = encodeURIComponent(filterVal);
                    ldap_search_filters += filterVal;
                }
            }
        }
    });
    if (confList.length == 0) {
        alert("You must select a LDAP server");
    } else {
        var xhrM = getXhrM();

        xhrM.open("POST", _addrSearchM, true);
        xhrM.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhrM.send("confList=" + confList + ldap_search_filters);

        document.getElementById('ldap_search_result_output').
            innerHTML = "<img src='./img/icones/16x16/spinner_blue.gif'>";

        // defining what we should do when we got a reply
        xhrM.onreadystatechange = function () {
            // doing nothing until we got everything and a status 200
            document.getElementById('ldap_search_result_output').innerHTML = xhrM.responseText;
            if (xhrM && xhrM.readyState == 4 && xhrM.status == 200 && xhrM.responseXML) {
                document.getElementById('ldap_search_result_output').innerHTML = '';

                response = xhrM.responseXML.documentElement;

                var _tab = document.createElement('table');

                _tab.setAttribute("style", "text-align:center;");
                _tab.setAttribute("class", "ListTable");
                var _tbody = document.createElement('tbody');

                var _tr = null;
                var _td0 = null;
                var _td1 = null;
                var _td2 = null;
                var _td3 = null;
                var _td4 = null;
                var _td5 = null;
                var _td6 = null;
                var _td7 = null;
                var _dntext = null;
                var _tr = null;

                _tr = document.createElement('tr');
                _tr.className = "ListHeader";
                _td0 = document.createElement('td');
                _td1 = document.createElement('td');
                _td2 = document.createElement('td');
                _td3 = document.createElement('td');
                _td4 = document.createElement('td');
                _td5 = document.createElement('td');
                _td6 = document.createElement('td');
                _td7 = document.createElement('td');

                _td0.className = "ListColHeaderPicker";
                _td1.className = "ListColHeaderCenter";
                _td2.className = "ListColHeaderCenter";
                _td3.className = "ListColHeaderCenter";
                _td4.className = "ListColHeaderCenter";
                _td5.className = "ListColHeaderCenter";
                _td6.className = "ListColHeaderCenter";
                _td7.className = "ListColHeaderCenter";

                var cbx = document.createElement("input");
                cbx.type = "checkbox";
                cbx.id = "checkall";
                cbx.name = "checkall";
                cbx.value = "checkall";
                cbx.setAttribute("onclick", "checkUncheckAll(this);");
                cbx.onclick = function () {
                    checkUncheckAll(this);
                };

                _td0.appendChild(cbx);
                _td1.appendChild(document.createTextNode('DN'));
                _td2.appendChild(document.createTextNode('UID'));
                _td3.appendChild(document.createTextNode('Givenname'));
                _td4.appendChild(document.createTextNode('SN'));
                _td5.appendChild(document.createTextNode('CN'));
                _td6.appendChild(document.createTextNode('Email'));
                _td7.appendChild(document.createTextNode('Pager'));

                _tr.appendChild(_td0);
                _tr.appendChild(_td1);
                _tr.appendChild(_td2);
                _tr.appendChild(_td3);
                _tr.appendChild(_td4);
                _tr.appendChild(_td5);
                _tr.appendChild(_td6);
                _tr.appendChild(_td7);
                _tbody.appendChild(_tr);

                var infos = response.getElementsByTagName("user");
                var serverName = '';

                for (var i = 0; i < infos.length; i++) {

                    var info = infos[i];
                    if (info.getAttribute('server') != serverName) {
                        var htr = document.createElement('tr');
                        htr.setAttribute('class', 'list_lvl_1');
                        var htd = document.createElement('td');
                        htd.appendChild(document.createTextNode(info.getAttribute('server')));
                        htd.setAttribute('colspan', '8')
                        htd.setAttribute('style', 'text-align:left');
                        htr.appendChild(htd);
                        _tbody.appendChild(htr);
                        serverName = info.getAttribute('server');
                    }

                    if (info.getElementsByTagName("dn")[0].getAttribute('isvalid') == 1)
                        var _dn = info.getElementsByTagName("dn")[0].firstChild.nodeValue;
                    else
                        var _dn = "-";

                    if (info.getElementsByTagName("sn")[0].getAttribute('isvalid') == 1)
                        var _sn = info.getElementsByTagName("sn")[0].firstChild.nodeValue;
                    else
                        var _sn = "-";

                    if (info.getElementsByTagName("mail")[0].getAttribute('isvalid') == 1)
                        var _mail = info.getElementsByTagName("mail")[0].firstChild.nodeValue;
                    else
                        var _mail = "-";

                    if (info.getElementsByTagName("pager")[0].getAttribute('isvalid') == 1)
                        var _pager = info.getElementsByTagName("pager")[0].firstChild.nodeValue;
                    else
                        var _pager = "-";

                    if (info.getElementsByTagName("uid")[0].getAttribute('isvalid') == 1)
                        var _uid = info.getElementsByTagName("uid")[0].firstChild.nodeValue;
                    else
                        var _uid = "-";

                    if (info.getElementsByTagName("givenname")[0].getAttribute('isvalid') == 1)
                        var _givenname = info.getElementsByTagName("givenname")[0].firstChild.nodeValue;
                    else
                        var _givenname = "-";

                    if (info.getElementsByTagName("cn")[0].getAttribute('isvalid') == 1)
                        var _cn = info.getElementsByTagName("cn")[0].firstChild.nodeValue;
                    else
                        var _cn = "-";

                    _tr = document.createElement('tr');

                    var ClassName = "list_one";
                    if (i % 2) {
                        ClassName = "list_two";
                    }
                    _tr.className = ClassName;
                    _td0 = document.createElement('td');
                    _td1 = document.createElement('td');
                    _td2 = document.createElement('td');
                    _td3 = document.createElement('td');
                    _td4 = document.createElement('td');
                    _td5 = document.createElement('td');
                    _td6 = document.createElement('td');
                    _td7 = document.createElement('td');
                    _td0.className = "ListColHeaderPicker";
                    _td1.className = "ListColHeaderCenter";
                    _td2.className = "ListColHeaderCenter";
                    _td3.className = "ListColHeaderCenter";
                    _td4.className = "ListColHeaderCenter";
                    _td5.className = "ListColHeaderCenter";
                    _td6.className = "ListColHeaderCenter";
                    _td7.className = "ListColHeaderCenter";

                    var cbx = document.createElement("input");
                    cbx.type = "checkbox";
                    if (info.getAttribute('isvalid') == 0 || info.getElementsByTagName('in_database')[0].firstChild.nodeValue == 1) {
                        cbx.disabled = "1";
                    }
                    cbx.id = "contact_select" + i;
                    cbx.name = "contact_select[select][" + i + "]";
                    cbx.value = i;

                    var arId = document.createElement("input");
                    arId.type = 'hidden';
                    arId.name = 'contact_select[ar_id][' + i + ']';
                    arId.value = info.getAttribute('ar_id');

                    var h_dn = document.createElement("input");
                    h_dn.type = "hidden";
                    h_dn.id = "user_dn" + i;
                    h_dn.name = "contact_select[dn][" + i + "]";
                    h_dn.value = _dn;

                    var h_uid = document.createElement("input");
                    h_uid.type = "hidden";
                    h_uid.id = "contact_alias" + i;
                    h_uid.name = "contact_select[contact_alias][" + i + "]";
                    h_uid.value = _uid;

                    var h_givenname = document.createElement("input");
                    h_givenname.type = "hidden";
                    h_givenname.id = "user_givenname" + i;
                    h_givenname.name = "contact_select[givenname][" + i + "]";
                    h_givenname.value = _givenname;

                    var h_sn = document.createElement("input");
                    h_sn.type = "hidden";
                    h_sn.id = "user_sn" + i;
                    h_sn.name = "contact_select[sn][" + i + "]";
                    h_sn.value = _sn;

                    var h_cn = document.createElement("input");
                    h_cn.type = "hidden";
                    h_cn.id = "contact_name" + i;
                    h_cn.name = "contact_select[contact_name][" + i + "]";
                    h_cn.value = _cn;

                    var h_mail = document.createElement("input");
                    h_mail.type = "hidden";
                    h_mail.id = "contact_email" + i;
                    h_mail.name = "contact_select[contact_email][" + i + "]";
                    h_mail.value = _mail;

                    var h_pager = document.createElement("input");
                    h_pager.type = "hidden";
                    h_pager.id = "contact_pager" + i;
                    h_pager.name = "contact_select[contact_pager][" + i + "]";
                    h_pager.value = _pager;

                    _td0.appendChild(cbx);
                    _td0.appendChild(arId);
                    _td1.appendChild(document.createTextNode(_dn));
                    _td1.appendChild(h_dn);
                    _td2.appendChild(document.createTextNode(_uid));
                    _td2.appendChild(h_uid);
                    _td3.appendChild(document.createTextNode(_givenname));
                    _td3.appendChild(h_givenname);
                    _td4.appendChild(document.createTextNode(_sn));
                    _td4.appendChild(h_sn);
                    _td5.appendChild(document.createTextNode(_cn));
                    _td5.appendChild(h_cn);
                    _td6.appendChild(document.createTextNode(_mail));
                    _td6.appendChild(h_mail);
                    _td7.appendChild(document.createTextNode(_pager));
                    _td7.appendChild(h_pager);

                    _tr.appendChild(_td0);
                    _tr.appendChild(_td1);
                    _tr.appendChild(_td2);
                    _tr.appendChild(_td3);
                    _tr.appendChild(_td4);
                    _tr.appendChild(_td5);
                    _tr.appendChild(_td6);
                    _tr.appendChild(_td7);
                    _tbody.appendChild(_tr);
                }
                _tab.appendChild(_tbody);
                document.getElementById('ldap_search_result_output').appendChild(_tab);
            }
        }
    }
}