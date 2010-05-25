<?php
/*
 * Copyright 2005-2010 MERETHIS
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

	if (!isset($oreon))
		exit();

	
	$DBRESULT =& $pearDB->query("SELECT * FROM `options`");
	
	while ($opt =& $DBRESULT->fetchRow()) {
		$gopt[$opt["key"]] = myDecode($opt["value"]);
	}
	
	#
	## Database retrieve information for differents elements list we need on the page
	#
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#

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
	$form->addElement('text', 'ldap_host', _("LDAP Server"), $attrsText );
	$form->addElement('text', 'ldap_port', _("LDAP Port"),  $attrsText2);
	$form->addElement('text', 'ldap_base_dn', _("LDAP Base DN"), $attrsText);
	$form->addElement('text', 'ldap_login_attrib', _("LDAP Login Attribute"), $attrsText);
	$ldapUseSSL[] = &HTML_QuickForm::createElement('radio', 'ldap_ssl', null, _("Yes"), '1');
	$ldapUseSSL[] = &HTML_QuickForm::createElement('radio', 'ldap_ssl', null, _("No"), '0');
	$form->addGroup($ldapUseSSL, 'ldap_ssl', _("Enable LDAP over SSL"), '&nbsp;');
	$ldapEnable[] = &HTML_QuickForm::createElement('radio', 'ldap_auth_enable', null, _("Yes"), '1');
	$ldapEnable[] = &HTML_QuickForm::createElement('radio', 'ldap_auth_enable', null, _("No"), '0');
	$form->addGroup($ldapEnable, 'ldap_auth_enable', _("Enable LDAP authentification"), '&nbsp;');
	$form->addElement('header', 'searchldap', _("LDAP Search Information"));
	$form->addElement('text', 'ldap_search_user', _("User to search (anonymous if empty)"), $attrsText );
	$form->addElement('password', 'ldap_search_user_pwd', _("Password"),  $attrsText);
	$form->addElement('text', 'ldap_search_filter', _("Default LDAP filter"), $attrsText);
	$form->addElement('text', 'ldap_search_timeout', _("LDAP search timeout"), $attrsText2);
	$form->addElement('text', 'ldap_search_limit', _("LDAP Search Size Limit"), $attrsText2);
	$form->addElement('select', 'ldap_protocol_version', _("Protocol version"), array("1" => " 1 ", "2" => " 2 ", "3" => " 3 "));

	$form->addElement('hidden', 'gopt_id');
	$redirect =& $form->addElement('hidden', 'o');
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

	$form->setDefaults($gopt);

	$subC =& $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT =& $form->addElement('reset', 'reset', _("Reset"));


    $valid = false;
	if ($form->validate())	{

		# Update in DB
		updateLdapConfigData($form->getSubmitValue("gopt_id"));
		
		# Update in Oreon Object
		$oreon->initOptGen($pearDB);
		
		$o = "w";
   		$valid = true;
		$form->freeze();

	}
	if (!$form->validate() && isset($_POST["gopt_id"]))	{
	    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");
	}

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=ldap'"));


	#
	##Apply a template definition
	#

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign("optGen_ldap_properties", _("LDAP Properties"));
	$tpl->assign('valid', $valid);
	$tpl->display("form.ihtml");
?>
