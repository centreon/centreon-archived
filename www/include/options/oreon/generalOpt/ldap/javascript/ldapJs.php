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
function mk_pagination(){}
function mk_paginationFF(){}
function set_header_title(){}

var nextRowId;
var counter = '<?php echo $maxHostId;?>';
var nbOfInitialRows = '<?php echo $nbOfInitialRows; ?>';
var o = '<?php echo $o;?>';
var arId = '<?php echo $arId;?>';
var templates;

/*
 * Transform our div
 */
function transformForm()
{
    var params;
    var proc;
    var addrXML;
    var addrXSL;

    var params = '?sid=' + sid;

    if (o == 'w' || o == 'ldap') {
        params = params+'&arId='+arId;
        proc = new Transformation();
        addrXML = './include/options/oreon/generalOpt/ldap/xml/ldap_host.php' + params;
        addrXSL = './include/options/oreon/generalOpt/ldap/xsl/ldap_host.xsl';
        proc.setXml(addrXML);
        proc.setXslt(addrXSL);
        proc.transform("dynamicDiv");
        o = 0;
    } else {
        params = params + '&id=' + counter + '&nbOfInitialRows=' + nbOfInitialRows;
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
        Effect.Fade('ldap_dns_use_ssl', { duration : 0 });
        Effect.Fade('ldap_dns_use_tls', { duration : 0 });
        Effect.Fade('ldap_dns_use_domain', { duration : 0 });
        Effect.Appear('ldap_header_tr', { duration : 0 });        
        Effect.Appear('ldap_tr', { duration : 0 });
    } else {
        Effect.Fade('ldap_header_tr', { duration : 0 });
        Effect.Fade('ldap_tr', { duration : 0 });
        if (document.getElementById('ldap_dns_use_ssl')) {
            Effect.Appear('ldap_dns_use_ssl', { duration : 0 });
        }
	if (document.getElementById('ldap_dns_use_tls')) {
            Effect.Appear('ldap_dns_use_tls', { duration : 0 });
	}
	if (document.getElementById('ldap_dns_use_domain')) {
            Effect.Appear('ldap_dns_use_domain', { duration : 0 });
	}
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
		Effect.Appear('ldap_user_filter', { duration : 0 });
		Effect.Appear('ldap_user_uid_attr', { duration : 0 });
		Effect.Appear('ldap_user_group', { duration : 0 });
		Effect.Appear('ldap_user_name', { duration : 0 });
		Effect.Appear('ldap_user_firstname', { duration : 0 });
		Effect.Appear('ldap_user_lastname', { duration : 0 });
		Effect.Appear('ldap_user_email', { duration : 0 });
		Effect.Appear('ldap_user_pager', { duration : 0 });
		Effect.Appear('ldap_group_filter', { duration : 0 });
		Effect.Appear('ldap_group_gid_attr', { duration : 0 });
		Effect.Appear('ldap_group_member', { duration : 0 });
	} else {
		Effect.Fade('ldap_user_filter', { duration : 0 });
		Effect.Fade('ldap_user_uid_attr', { duration : 0 });
		Effect.Fade('ldap_user_group', { duration : 0 });
		Effect.Fade('ldap_user_name', { duration : 0 });
		Effect.Fade('ldap_user_firstname', { duration : 0 });
		Effect.Fade('ldap_user_lastname', { duration : 0 });
		Effect.Fade('ldap_user_email', { duration : 0 });
		Effect.Fade('ldap_user_pager', { duration : 0 });
		Effect.Fade('ldap_group_filter', { duration : 0 });
		Effect.Fade('ldap_group_gid_attr', { duration : 0 });
		Effect.Fade('ldap_group_member', { duration : 0 });
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
    	Effect.Fade(trId, { duration : 0 });
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
	ldapTemplates['Active Directory']['user_filter'] = '(&(samAccountName=%s)(objectClass=user)(samAccountType=805306368))';
	ldapTemplates['Active Directory']['alias'] = 'samaccountname';
	ldapTemplates['Active Directory']['user_group'] = 'memberOf';
	ldapTemplates['Active Directory']['user_name'] = 'name';
	ldapTemplates['Active Directory']['user_firstname'] = 'givenname';
	ldapTemplates['Active Directory']['user_lastname'] = 'sn';
	ldapTemplates['Active Directory']['user_email'] = 'mail';
	ldapTemplates['Active Directory']['user_pager'] = 'mobile';
	ldapTemplates['Active Directory']['group_filter'] = '(&(samAccountName=%s)(objectClass=group)(samAccountType=268435456))';
	ldapTemplates['Active Directory']['group_name'] = 'samaccountname';
	ldapTemplates['Active Directory']['group_member'] = 'member';
}

/*
 * Apply template is called from the template selectbox
 */
function applyTemplate(templateValue) {	
    $$('input[type^=text]').each(function(el) {
        key = el.getAttribute('name');	
        var attr = key;

        if (typeof(ldapTemplates[templateValue]) != 'undefined') {
            if (typeof(ldapTemplates[templateValue][attr]) != 'undefined') {
                el.setValue(ldapTemplates[templateValue][attr]);
            }
        }
    });
}
Event.observe(window, "load", function() { 
    initParams();
});
</script>
