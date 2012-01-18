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
	$gopt['ldap_template'] = $tmpOptions['tmpl'];
	if (isset($tmpOptions['protocol_version'])) {
 		$gopt['ldap_version_protocol'] = $tmpOptions['protocol_version'];
	} else {
		$gopt['ldap_version_protocol'] = 3;
	}
 	$gopt['ldap_binduser'] = $tmpOptions['bind_dn'];
    $gopt['ldap_bindpass'] = $tmpOptions['bind_pass'];
    $gopt['ldap_user_basedn'] = $tmpOptions['user_base_search'];
    $gopt['ldap_group_basedn'] = $tmpOptions['group_base_search'];
    if (isset($tmpOptions['user_filter'])) {
    	$gopt['ldap_user_filter'] = $tmpOptions['user_filter'];
    } else {
    	$gopt['ldap_user_filter'] = "";
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

	$form->setDefaults($gopt);

	$subC = $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT = $form->addElement('reset', 'reset', _("Reset"));

    $valid = false;
	if ($form->validate())	{

	    $values = $form->getSubmitValues();

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

	    /* Get the template ID */
	    $queryTemplate = "SELECT ar_id FROM auth_ressource WHERE ar_type = 'ldap_tmpl'";
	    $res = $pearDB->query($queryTemplate);

	    /* Prepare options */
	    $options = array();
	    $options['tmpl'] = $values['ldap_template'];
	    $options['protocol_version'] = $values['ldap_version_protocol'];
	    $options['bind_dn'] = $values['ldap_binduser'];
	    $options['bind_pass'] = $values['ldap_bindpass'];
	    $options['user_base_search'] = $values['ldap_user_basedn'];
	    $options['group_base_search'] = $values['ldap_group_basedn'];
	    if ($options['tmpl'] == '0') {
	        $options['user_filter'] = $values['ldap_user_filter'];
	        $options['alias'] = $values['ldap_user_uid_attr'];
	        $options['user_group'] = $values['ldap_user_group'];
	        $options['user_name'] = $values['ldap_user_name'];
	        $options['user_firstname'] = $values['ldap_user_firstname'];
	        $options['user_lastname'] = $values['ldap_user_lastname'];
	        $options['user_email'] = $values['ldap_user_email'];
            $options['user_pager'] = $values['ldap_user_pager'];
	        $options['group_filter'] = $values['ldap_group_filter'];
	        $options['group_name'] = $values['ldap_group_gid_attr'];
	        $options['group_member'] = $values['ldap_group_member'];
	    } elseif ($options['tmpl'] == '1') {
	        $tmplOptions = $ldapAdmin->getTemplateLdap();
	        $options['user_filter'] = $tmplOptions['user_filter'];
	        $options['alias'] = $tmplOptions['user_attr']['alias'];
	        $options['user_group'] = $tmplOptions['user_attr']['group'];
	        $options['user_name'] = $tmplOptions['user_attr']['name'];
	        $options['user_firstname'] = $tmplOptions['user_attr']['firstname'];
	        $options['user_lastname'] = $tmplOptions['user_attr']['lastname'];
	        $options['user_email'] = $tmplOptions['user_attr']['email'];
	        $options['user_pager'] = $tmplOptions['user_attr']['pager'];
	        $options['group_filter'] = $tmplOptions['group_filter'];
	        $options['group_name'] = $tmplOptions['group_attr']['group_name'];
	        $options['group_member'] = $tmplOptions['group_attr']['member'];
	    } elseif ($options['tmpl'] == '2') {
	        $tmplOptions = $ldapAdmin->getTemplateAd();
	        $options['user_filter'] = $tmplOptions['user_filter'];
	        $options['alias'] = $tmplOptions['user_attr']['alias'];
	        $options['user_group'] = $tmplOptions['user_attr']['group'];
	        $options['user_name'] = $tmplOptions['user_attr']['name'];
	        $options['user_firstname'] = $tmplOptions['user_attr']['firstname'];
	        $options['user_lastname'] = $tmplOptions['user_attr']['lastname'];
	        $options['user_email'] = $tmplOptions['user_attr']['email'];
	        $options['user_pager'] = $tmplOptions['user_attr']['pager'];
	        $options['group_filter'] = $tmplOptions['group_filter'];
	        $options['group_name'] = $tmplOptions['group_attr']['group_name'];
	        $options['group_member'] = $tmplOptions['group_attr']['member'];
	    }

	    if (false === PEAR::isError($res) && $res->numRows() == 1) {
	        $row = $res->fetchRow();
	        $idTmpl = $row['ar_id'];
	        $ldapAdmin->modifyTemplate($idTmpl, $options);
	    } else {
	        $ldapAdmin->addTemplate($options);
	    }

	    $hostOld = array();
	    if (isset($_POST['ldapHosts'])) {
    	    foreach ($_POST['ldapHosts'] as $ldapHost) {
    	        if (isset($ldapHost['id'])) {
    	            $hostOld[] = $ldapHost['id'];
    	        }
    	    }
    	    $query = "DELETE FROM auth_ressource WHERE ar_type = 'ldap' AND ar_id NOT IN (" . join(', ', $hostOld) . ")";
    	    $pearDB->query($query);

    	    foreach ($_POST['ldapHosts'] as $ldapHost) {
    	        if (!isset($ldapHost['use_ssl'])) {
    	            $ldapHost['use_ssl'] = '0';
    	        }
    	        if (!isset($ldapHost['use_tls'])) {
    	            $ldapHost['use_tls'] = '0';
    	        }
    	        if (isset($ldapHost['id'])) {
    	            $ldapAdmin->modifyServer($ldapHost['id'], $ldapHost['hostname'], $ldapHost['port'], $ldapHost['use_ssl'], $ldapHost['use_tls'], $ldapHost['order']);
    	        } else {
    	            $ldapAdmin->addServer($ldapHost['hostname'], $ldapHost['port'], $ldapHost['use_ssl'], $ldapHost['use_tls'], $ldapHost['order']);
    	        }
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

	if (!$form->validate() && isset($_POST["gopt_id"]))	{
	    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");
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
	$tpl->assign('valid', $valid);
	$tpl->display("form.ihtml");

	$nbOfInitialRows = 0;

	require_once $path.'ldap/javascript/ldapJs.php';
?>