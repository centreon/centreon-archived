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
 * SVN : $URL$
 * SVN : $Id$
 *
 */
?>
<script type="text/javascript">
    function mk_pagination() {
    }
    function mk_paginationFF() {
    }
    function set_header_title() {
    }

    var nextRowId;
    var counter = '<?php echo $maxHostId;?>';
    var nbOfInitialRows = '<?php echo $nbOfInitialRows; ?>';
    var o = '<?php echo $o;?>';
    var arId = '<?php echo $arId;?>';
    var templates;

    /*
     * Transform our div
     */
    function transformForm() {
        var params;
        var proc;
        var addrXML;
        var addrXSL;

        //var params = '?sid=' + sid;

        if (o == 'w' || o == 'ldap') {
            params = '?arId=' + arId;
            proc = new Transformation();
            addrXML = './include/options/oreon/generalOpt/ldap/xml/ldap_host.php' + params;
            addrXSL = './include/options/oreon/generalOpt/ldap/xsl/ldap_host.xsl';
            proc.setXml(addrXML);
            proc.setXslt(addrXSL);
            proc.transform("dynamicDiv");
            o = 0;
        } else {
            params = '?id=' + counter + '&nbOfInitialRows=' + nbOfInitialRows;
            proc = new Transformation();
            addrXML = './include/options/oreon/generalOpt/ldap/xml/additionalRowXml.php' + params;
            addrXSL = './include/options/oreon/generalOpt/ldap/xsl/additionalRow.xsl';
            proc.setXml(addrXML);
            proc.setXslt(addrXSL);
            proc.transform(nextRowId);
        }
    }

    /*
     * called when the use _dns is to set at no is clicked
     */
    function toggleParams(checkValue, isInit) {
        if (checkValue == true) {
            jQuery('#ldap_dns_use_ssl').fadeOut({duration: 0});
            jQuery('#ldap_dns_use_tls').fadeOut({duration: 0});
            jQuery('#ldap_dns_use_domain').fadeOut({duration: 0});
            jQuery('#ldap_header_tr').fadeIn({duration: 0});
            jQuery('#ldap_tr').fadeIn({duration: 0});
        } else {
            jQuery('#ldap_header_tr').fadeOut({duration: 0});
            jQuery('#ldap_tr').fadeOut({duration: 0});
            if (document.getElementById('ldap_dns_use_ssl')) {
                jQuery('#ldap_dns_use_ssl').fadeIn({duration: 0});
            }
            if (document.getElementById('ldap_dns_use_tls')) {
                jQuery('#ldap_dns_use_tls').fadeIn({duration: 0});
            }
            if (document.getElementById('ldap_dns_use_domain')) {
                jQuery('#ldap_dns_use_domain').fadeIn({duration: 0});
            }
        }
    }

    /*
     * called when LDAP is enabled or not
     */
    function toggleParamSync(checkValue, isInit) {
        if (checkValue == true) {
            jQuery('#ldap_sync_interval').fadeOut({duration: 0});
        } else {
            jQuery('#ldap_sync_interval').fadeIn({duration: 0});
        }
    }

    /**
     * Display or hide custom options
     */
    function toggleCustom(select) {
        if (typeof(select) == 'undefined' || typeof(select.selectedIndex) == 'undefined') {
            return null;
        }
        value = select.options[select.selectedIndex].value;
        if (value == 0) {
            jQuery('#ldap_user_filter').fadeIn({duration: 0});
            jQuery('#ldap_user_uid_attr').fadeIn({duration: 0});
            jQuery('#ldap_user_group').fadeIn({duration: 0});
            jQuery('#ldap_user_name').fadeIn({duration: 0});
            jQuery('#ldap_user_firstname').fadeIn({duration: 0});
            jQuery('#ldap_user_lastname').fadeIn({duration: 0});
            jQuery('#ldap_user_email').fadeIn({duration: 0});
            jQuery('#ldap_user_pager').fadeIn({duration: 0});
            jQuery('#ldap_group_filter').fadeIn({duration: 0});
            jQuery('#ldap_group_gid_attr').fadeIn({duration: 0});
            jQuery('#ldap_group_member').fadeIn({duration: 0});
        } else {
            jQuery('#ldap_user_filter').fadeOut({duration: 0});
            jQuery('#ldap_user_uid_attr').fadeOut({duration: 0});
            jQuery('#ldap_user_group').fadeOut({duration: 0});
            jQuery('#ldap_user_name').fadeOut({duration: 0});
            jQuery('#ldap_user_firstname').fadeOut({duration: 0});
            jQuery('#ldap_user_lastname').fadeOut({duration: 0});
            jQuery('#ldap_user_email').fadeOut({duration: 0});
            jQuery('#ldap_user_pager').fadeOut({duration: 0});
            jQuery('#ldap_group_filter').fadeOut({duration: 0});
            jQuery('#ldap_group_gid_attr').fadeOut({duration: 0});
            jQuery('#ldap_group_member').fadeOut({duration: 0});
        }
    }

    /*
     * Initialises advanced parameters
     */
    function initParams() {
        initTemplates();
        if (document.getElementById('ldap_srv_dns_n')) {
            var noDns = false;
            if (document.getElementById('ldap_srv_dns_n').type == 'radio') {
                if (document.getElementById('ldap_srv_dns_n').checked) {
                    noDns = true;
                }
            }
        }
        toggleParams(noDns, true);
    }

    /*
     * Function is called when the '+' button is pressed
     */
    function addNewHost() {
        nbOfInitialRows++;
        nextRowId = 'additionalRow_' + nbOfInitialRows;
        transformForm();
        counter++;
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
            jQuery('#'+trId).fadeOut({duration: 0});
        }
    }

    /*
     * Initializes templates
     */
    function initTemplates() {
        ldapTemplates = new Array();

        ldapTemplates['Posix'] = new Array();
        ldapTemplates['Posix']['user_filter'] = '(&(uid=%s)(objectClass=inetOrgPerson))';
        ldapTemplates['Posix']['alias'] = 'uid';
        ldapTemplates['Posix']['user_group'] = '';
        ldapTemplates['Posix']['user_name'] = 'cn';
        ldapTemplates['Posix']['user_firstname'] = 'givenname';
        ldapTemplates['Posix']['user_lastname'] = 'sn';
        ldapTemplates['Posix']['user_email'] = 'mail';
        ldapTemplates['Posix']['user_pager'] = 'mobile';
        ldapTemplates['Posix']['group_filter'] = '(&(cn=%s)(objectClass=groupOfNames))';
        ldapTemplates['Posix']['group_name'] = 'cn';
        ldapTemplates['Posix']['group_member'] = 'member';

        ldapTemplates['Active Directory'] = new Array();
        ldapTemplates['Active Directory']['user_filter'] =
            '(&(samAccountName=%s)(objectClass=user)(samAccountType=805306368))';
        ldapTemplates['Active Directory']['alias'] = 'samaccountname';
        ldapTemplates['Active Directory']['user_group'] = 'memberOf';
        ldapTemplates['Active Directory']['user_name'] = 'name';
        ldapTemplates['Active Directory']['user_firstname'] = 'givenname';
        ldapTemplates['Active Directory']['user_lastname'] = 'sn';
        ldapTemplates['Active Directory']['user_email'] = 'mail';
        ldapTemplates['Active Directory']['user_pager'] = 'mobile';
        ldapTemplates['Active Directory']['group_filter'] =
            '(&(samAccountName=%s)(objectClass=group)(samAccountType=268435456))';
        ldapTemplates['Active Directory']['group_name'] = 'samaccountname';
        ldapTemplates['Active Directory']['group_member'] = 'member';
    }

    /*
     * Apply template is called from the template selectbox
     */
    function applyTemplate(templateValue) {

        jQuery('input[type^=text]').each(function (index, el) {
            key = el.getAttribute('name');
            var attr = key;
            if (typeof(ldapTemplates[templateValue]) != 'undefined') {
                if (typeof(ldapTemplates[templateValue][attr]) != 'undefined') {
                    el.value = ldapTemplates[templateValue][attr];
                }
            }
        });
    }
    jQuery(document).ready(function () {
        initParams();
    });
</script>
