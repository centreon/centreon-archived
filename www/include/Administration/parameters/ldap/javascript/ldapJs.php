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
<script type="text/javascript">
    function mk_pagination() {
    }
    function mk_paginationFF() {
    }
    function set_header_title() {
    }

    let ldapTemplates = [];

    /*
     * called when the use _dns is to set at no is clicked
     */
    function toggleParams(checkValue) {
        if (checkValue === true) {
            Effect.Fade('ldap_dns_use_ssl', {duration: 0});
            Effect.Fade('ldap_dns_use_tls', {duration: 0});
            Effect.Fade('ldap_dns_use_domain', {duration: 0});
            Effect.Appear('ldap_header_tr', {duration: 0});
            Effect.Appear('ldap_tr', {duration: 0});
        } else {
            Effect.Fade('ldap_header_tr', {duration: 0});
            Effect.Fade('ldap_tr', {duration: 0});
            if (document.getElementById('ldap_dns_use_ssl')) {
                Effect.Appear('ldap_dns_use_ssl', {duration: 0});
            }
            if (document.getElementById('ldap_dns_use_tls')) {
                Effect.Appear('ldap_dns_use_tls', {duration: 0});
            }
            if (document.getElementById('ldap_dns_use_domain')) {
                Effect.Appear('ldap_dns_use_domain', {duration: 0});
            }
        }
    }

    /**
     * Display or hide custom options
     */
    function toggleCustom(select) {
        if (typeof(select) === 'undefined' || typeof(select.selectedIndex) === 'undefined') {
            return null;
        }
        let value = select.options[select.selectedIndex].value;
        if (value === 0) {
            Effect.Appear('ldap_user_filter', {duration: 0});
            Effect.Appear('ldap_user_uid_attr', {duration: 0});
            Effect.Appear('ldap_user_group', {duration: 0});
            Effect.Appear('ldap_user_name', {duration: 0});
            Effect.Appear('ldap_user_firstname', {duration: 0});
            Effect.Appear('ldap_user_lastname', {duration: 0});
            Effect.Appear('ldap_user_email', {duration: 0});
            Effect.Appear('ldap_user_pager', {duration: 0});
            Effect.Appear('ldap_group_filter', {duration: 0});
            Effect.Appear('ldap_group_gid_attr', {duration: 0});
            Effect.Appear('ldap_group_member', {duration: 0});
        } else {
            Effect.Fade('ldap_user_filter', {duration: 0});
            Effect.Fade('ldap_user_uid_attr', {duration: 0});
            Effect.Fade('ldap_user_group', {duration: 0});
            Effect.Fade('ldap_user_name', {duration: 0});
            Effect.Fade('ldap_user_firstname', {duration: 0});
            Effect.Fade('ldap_user_lastname', {duration: 0});
            Effect.Fade('ldap_user_email', {duration: 0});
            Effect.Fade('ldap_user_pager', {duration: 0});
            Effect.Fade('ldap_group_filter', {duration: 0});
            Effect.Fade('ldap_group_gid_attr', {duration: 0});
            Effect.Fade('ldap_group_member', {duration: 0});
        }
    }

    /*
     * Initialises advanced parameters
     */
    function initParams() {
        initTemplates();
        let noDns = false;
        if (document.getElementById('ldap_srv_dns_n')) {
            if (document.getElementById('ldap_srv_dns_n').type === 'radio') {
                if (document.getElementById('ldap_srv_dns_n').checked) {
                    noDns = true;
                }
            }
        }
        toggleParams(noDns);
    }

    /*
     * Initializes templates
     */
    function initTemplates() {
        ldapTemplates = [];

        ldapTemplates['Posix'] = [];
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

        ldapTemplates['Active Directory'] = [];
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
        $$('input[type^=text]').each(function (el) {
            let attr = el.getAttribute('name');

            if (typeof(ldapTemplates[templateValue]) !== 'undefined') {
                if (typeof(ldapTemplates[templateValue][attr]) !== 'undefined') {
                    el.setValue(ldapTemplates[templateValue][attr]);
                }
            }
        });
    }
    
    Event.observe(window, "load", function () {
        initParams();
    });
</script>
