<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

	if (!isset($oreon))
		exit();

	
	$DBRESULT =& $pearDB->query("SELECT * FROM `options`");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
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
		$oreon->optGen = array();
		$DBRESULT2 =& $pearDB->query("SELECT * FROM `general_opt` LIMIT 1");
		$oreon->optGen =& $DBRESULT2->fetchRow();
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
