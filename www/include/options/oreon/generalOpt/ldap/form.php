<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

	if (!isset($oreon)) {
		exit();
	}

	require_once $centreon_path . 'www/class/centreonLDAP.class.php';

	$attrsText 		= array("size"=>"40");
	$attrsText2		= array("size"=>"5");
	$attrsAdvSelect = null;

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Modify General Options"));


    #
	## LDAP information
	#
	$form->addElement('header', 'ldap', _("LDAP information"));
	$ldapEnable[] = HTML_QuickForm::createElement('radio', 'ldap_auth_enable', null, _("Yes"), '1');
	$ldapEnable[] = HTML_QuickForm::createElement('radio', 'ldap_auth_enable', null, _("No"), '0');
	$form->addGroup($ldapEnable, 'ldap_auth_enable', _("Enable LDAP authentification"), '&nbsp;');

	$ldapStorePassword[] = HTML_QuickForm::createElement('radio', 'ldap_store_password', null, _("Yes"), '1');
	$ldapStorePassword[] = HTML_QuickForm::createElement('radio', 'ldap_store_password', null, _("No"), '0');
	$form->addGroup($ldapStorePassword, 'ldap_store_password', _("Store LDAP password"), '&nbsp;');

	$ldapAutoImport[] = HTML_QuickForm::createElement('radio', 'ldap_auto_import', null, _("Yes"), '1');
	$ldapAutoImport[] = HTML_QuickForm::createElement('radio', 'ldap_auto_import', null, _("No"), '0');
	$form->addGroup($ldapAutoImport, 'ldap_auto_import', _("Auto import users"), '&nbsp;');

	$ldapUseDns[] = HTML_QuickForm::createElement('radio', 'ldap_srv_dns', null, _("Yes"), '1', array('id' => 'ldap_srv_dns_y', 'onclick' => "toggleParams(false);"));
	$ldapUseDns[] = HTML_QuickForm::createElement('radio', 'ldap_srv_dns', null, _("No"), '0', array('id' => 'ldap_srv_dns_n', 'onclick' => "toggleParams(true);"));
	$form->addGroup($ldapUseDns, 'ldap_srv_dns', _("Use service DNS"), '&nbsp;');

	$ldapDnsUseSsl[] = HTML_QuickForm::createElement('radio', 'ldap_dns_use_ssl', null, _("Yes"), '1');
	$ldapDnsUseSsl[] = HTML_QuickForm::createElement('radio', 'ldap_dns_use_ssl', null, _("No"), '0');
	$form->addGroup($ldapDnsUseSsl, 'ldap_dns_use_ssl', _("Use SSL connection"), '&nbsp;');
	$ldapDnsUseTls[] = HTML_QuickForm::createElement('radio', 'ldap_dns_use_tls', null, _("Yes"), '1');
	$ldapDnsUseTls[] = HTML_QuickForm::createElement('radio', 'ldap_dns_use_tls', null, _("No"), '0');
	$form->addGroup($ldapDnsUseTls, 'ldap_dns_use_tls', _("Use TLS connection"), '&nbsp;');
	$form->addElement('text', 'ldap_dns_use_domain', _("Alternative domain for ldap"), $attrsText);

	$form->addElement('text', 'ldap_search_limit', _('LDAP search size limit'), $attrsText2);
	$form->addElement('text', 'ldap_search_timeout', _('LDAP search timeout'), $attrsText2);

	$query = "SELECT contact_id, contact_name FROM contact WHERE contact_register = '0'";
	$res = $pearDB->query($query);
	$tmplList = array();
	while ($row = $res->fetchRow()) {
	    $tmplList[$row['contact_id']] = $row['contact_name'];
	}
	$res->free();

	$form->addElement('select', 'ldap_contact_tmpl', _('Contact template'), $tmplList, array('id' => 'ldap_contact_tmpl'));

	$form->addElement('header', 'ldapinfo', _("LDAP Information"));

	$form->addElement('text', 'ldap_binduser', _("Bind user"), $attrsText);
	$form->addElement('password', 'ldap_bindpass', _("Bind password"), $attrsText);
	$form->addElement('select', 'ldap_version_protocol', _("Protocol version"), array('2' => 2, '3' => 3));
	$form->addElement('select', 'ldap_template', _("Template"), array('0' => _("Custom"), '1' => _("Posix"), '2' => _("Active Directory")), array('id' => 'ldap_template', 'onchange' => 'toggleCustom(this);'));
	$form->addElement('text', 'ldap_user_basedn', _("Search user base DN"), $attrsText);
	$form->addElement('text', 'ldap_group_basedn', _("Search group base DN"), $attrsText);
	$form->addElement('text', 'ldap_user_filter', _("User filter"), $attrsText);
	$form->addElement('text', 'ldap_user_uid_attr', _("Login attribute"), $attrsText);
	$form->addElement('text', 'ldap_user_group', _("User group attribute"), $attrsText);
	$form->addElement('text', 'ldap_user_name', _("User displayname attribute"), $attrsText);
	$form->addElement('text', 'ldap_user_firstname', _("User firstname attribute"), $attrsText);
	$form->addElement('text', 'ldap_user_lastname', _("User lastname attribute"), $attrsText);
	$form->addElement('text', 'ldap_user_email', _("User email attribute"), $attrsText);
	$form->addElement('text', 'ldap_user_pager', _("User pager attribute"), $attrsText);
	$form->addElement('text', 'ldap_group_filter', _("Group filter"), $attrsText);
	$form->addElement('text', 'ldap_group_gid_attr', _("Group attribute"), $attrsText);
	$form->addElement('text', 'ldap_group_member', _("Group member attribute"), $attrsText);

	$form->addElement('hidden', 'gopt_id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	## Form Rules
	#
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path.'ldap/', $tpl);

	$ldapAdmin = new CentreonLdapAdmin($pearDB);

	$defaultOpt = array('ldap_auth_enable' => '0',
		'ldap_store_password' => '1',
		'ldap_auto_import' => '0',
		'ldap_srv_dns' => '0',
		'ldap_template' => '1',
	    'ldap_version_protocol' => '3',
		'ldap_dns_use_ssl' => '0',
	    'ldap_dns_use_tls' => '0',
	    'ldap_contact_tmpl' => '0',
	    'ldap_search_limit' => '60',
	    'ldap_search_timeout' => '60');

	$query = "SELECT `key`, `value` FROM `options`
		WHERE `key` IN ('ldap_auth_enable', 'ldap_store_password', 'ldap_auto_import', 'ldap_srv_dns', 'ldap_dns_use_ssl', 'ldap_dns_use_tls', 'ldap_dns_use_domain', 'ldap_contact_tmpl', 'ldap_search_limit', 'ldap_search_timeout')";
	$res = $pearDB->query($query);
	while ($row = $res->fetchRow()) {
	    $gopt[$row['key']] = $row['value'];
	}

	$gopt = array_merge($defaultOpt, $gopt);

	$tmpOptions = $ldapAdmin->getTemplate();
        $gopt['ldap_version_protocol'] = 3;
        $gopt['ldap_user_filter'] = "";
        if (count($tmpOptions)) {
            $gopt['ldap_template'] = $tmpOptions['tmpl'];
            if (isset($tmpOptions['protocol_version'])) {
 		$gopt['ldap_version_protocol'] = $tmpOptions['protocol_version'];
            }
            $gopt['ldap_binduser'] = $tmpOptions['bind_dn'];
            $gopt['ldap_bindpass'] = $tmpOptions['bind_pass'];
            $gopt['ldap_user_basedn'] = $tmpOptions['user_base_search'];
            $gopt['ldap_group_basedn'] = $tmpOptions['group_base_search'];
            if (isset($tmpOptions['user_filter'])) {
                $gopt['ldap_user_filter'] = $tmpOptions['user_filter'];
            }
            $gopt['ldap_user_uid_attr'] = $tmpOptions['alias'];
            $gopt['ldap_user_group'] = $tmpOptions['user_group'];
            $gopt['ldap_user_name'] = $tmpOptions['user_name'];
            $gopt['ldap_user_firstname'] = $tmpOptions['user_firstname'];
            $gopt['ldap_user_lastname'] = $tmpOptions['user_lastname'];
            $gopt['ldap_user_email'] = $tmpOptions['user_email'];
            $gopt['ldap_user_pager'] = $tmpOptions['user_pager'];
            $gopt['ldap_group_filter'] = $tmpOptions['group_filter'];
            $gopt['ldap_group_gid_attr'] = $tmpOptions['group_name'];
            $gopt['ldap_group_member'] = $tmpOptions['group_member'];
            unset($tmpOptions);
        }
	$form->setDefaults($gopt);

	$subC = $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT = $form->addElement('reset', 'reset', _("Reset"));

	$query = "SELECT count(*) as nb FROM auth_ressource WHERE ar_enable = '1'";
	$res = $pearDB->query($query);
	$row = $res->fetchRow();
	$nbOfInitialRows = $row['nb'];

	$query = "SELECT MAX(ar_id) as cnt FROM auth_ressource WHERE ar_enable = '1'";
	$res = $pearDB->query($query);
	$maxArId = 1;
	if ($res->numRows()) {
    	$row = $res->fetchRow();
    	$maxArId = $row['cnt'];
	}

	require_once $path.'ldap/javascript/ldapJs.php';

    $valid = false;
    $filterValid = true;
	if ($form->validate())	{

	    $values = $form->getSubmitValues();

		/*
         * Test is filter string is validate
         */
	    if (isset($_POST['ldapHosts'])) {
	        foreach ($_POST['ldapHosts'] as $ldapHost) {
    	        foreach ($ldapHost as $k => $v) {
                    if ($k == 'ldap_user_filter' || $k == 'ldap_group_filter') {
                        if (false === CentreonLDAP::validateFilterPattern($v)) {
                            $filterValid = false;
                        }
                    }
    	        }
	        }
	    }

	    if ($filterValid) {
            if (!isset($values['ldap_contact_tmpl'])) {
            	$values['ldap_contact_tmpl'] = "";
            }

            /* Set the general options for ldap */
            $options = array('ldap_auth_enable' => $values['ldap_auth_enable']['ldap_auth_enable'],
            	'ldap_store_password' => $values['ldap_store_password']['ldap_store_password'],
                'ldap_auto_import' => $values['ldap_auto_import']['ldap_auto_import'],
            	'ldap_srv_dns' => $values['ldap_srv_dns']['ldap_srv_dns'],
                'ldap_dns_use_ssl' => $values['ldap_dns_use_ssl']['ldap_dns_use_ssl'],
            	'ldap_dns_use_tls' => $values['ldap_dns_use_tls']['ldap_dns_use_tls'],
            	'ldap_dns_use_domain' => $values['ldap_dns_use_domain'],
                'ldap_contact_tmpl' => $values['ldap_contact_tmpl'],
                'ldap_search_limit' => $values['ldap_search_limit'],
                'ldap_search_timeout' => $values['ldap_search_timeout']
                );
            $ldapAdmin->setGeneralOptions($options);

            $hostOld = array();
            if (isset($_POST['ldapHosts'])) {
                $sortArray = array();
                foreach ($_POST['ldapHosts'] as $ldapHost) {
        	        if (isset($ldapHost['id'])) {
        	            $hostOld[] = $ldapHost['id'];
        	        }
        	        foreach ($ldapHost as $k => $v) {
        	            if (!isset($sortArray[$k])) {
        	                $sortArray[$k] = array();
        	            }
        	            $sortArray[$k][] = $v;
        	        }
        	    }
        	    $query = "DELETE FROM auth_ressource WHERE ar_type = 'ldap' AND ar_id NOT IN (" . join(', ', $hostOld) . ")";
        	    $pearDB->query($query);

        	    array_multisort($sortArray['order'], SORT_ASC, $_POST['ldapHosts']);
        	    $counter = 1;
        	    foreach ($_POST['ldapHosts'] as $ldapHost) {
        	        $ldapHost['order'] = $counter;
        	        if (!isset($ldapHost['use_ssl'])) {
        	            $ldapHost['use_ssl'] = '0';
        	        } else {
        	            $ldapHost['use_ssl'] = '1';
        	        }
        	        if (!isset($ldapHost['use_tls'])) {
        	            $ldapHost['use_tls'] = '0';
        	        } else {
        	            $ldapHost['use_tls'] = '1';
        	        }
        	        if (isset($ldapHost['id'])) {
        	            $ldapAdmin->modifyServer($ldapHost);
        	        } else {
        	            $ldapAdmin->addServer($ldapHost);
        	        }
        	        $counter++;
        	    }
            } else {
                $query = "DELETE FROM auth_ressource WHERE ar_type = 'ldap'";
                $pearDB->query($query);
            }

        	$o = "w";
        	$valid = true;

            if (!isset($values['ldap_srv_dns']['ldap_srv_dns']) || !$values['ldap_srv_dns']['ldap_srv_dns']) {
        	    $tpl->assign("hideDnsOptions", 1);
        	} else {
        	    $tpl->assign("hideDnsOptions", 0);
        	}
        	$form->freeze();
	    }
	}

	if (!$form->validate() && isset($_POST["gopt_id"]))	{
	    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");
	} elseif (false === $filterValid) {
	    print("<div class='msg' align='center'>"._("Bad ldap filter : missing %s pattern. Check user or group filter")."</div>");
	}

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=ldap'"));

	/*
	 * Prepare help texts
	 */
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) {
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign("optGen_ldap_properties", _("LDAP Properties"));
	$tpl->assign('addNewHostLabel', _('Add a LDAP server'));
	$tpl->assign('manualImport', _('Import users manually'));
	$tpl->assign('valid', $valid);
	$tpl->display("form.ihtml");
?>