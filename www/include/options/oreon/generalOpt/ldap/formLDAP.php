<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	if (!isset($oreon))
		exit();

	#
	## Database retrieve information for LCA
	#
	$DBRESULT =& $pearDB->query("SELECT * FROM general_opt LIMIT 1");
	# Set base value
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());
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
	$form->addElement('header', 'title', $lang["genOpt_change"]);


    #
	## LDAP information
	#
	$form->addElement('header', 'ldap', $lang["genOpt_ldap"]);
	$form->addElement('text', 'ldap_host', $lang["genOpt_ldap_host"], $attrsText );
	$form->addElement('text', 'ldap_port', $lang["genOpt_ldap_port"],  $attrsText2);
	$form->addElement('text', 'ldap_base_dn', $lang["genOpt_ldap_base_dn"], $attrsText);
	$form->addElement('text', 'ldap_login_attrib', $lang["genOpt_ldap_login_attrib"], $attrsText);
	$ldapUseSSL[] = &HTML_QuickForm::createElement('radio', 'ldap_ssl', null, $lang["yes"], '1');
	$ldapUseSSL[] = &HTML_QuickForm::createElement('radio', 'ldap_ssl', null, $lang["no"], '0');
	$form->addGroup($ldapUseSSL, 'ldap_ssl', $lang["genOpt_ldap_ssl"], '&nbsp;');
	$ldapEnable[] = &HTML_QuickForm::createElement('radio', 'ldap_auth_enable', null, $lang["yes"], '1');
	$ldapEnable[] = &HTML_QuickForm::createElement('radio', 'ldap_auth_enable', null, $lang["no"], '0');
	$form->addGroup($ldapEnable, 'ldap_auth_enable', $lang["genOpt_ldap_auth_enable"], '&nbsp;');
	$form->addElement('header', 'searchldap', $lang["genOpt_searchldap"]);
	$form->addElement('text', 'ldap_search_user', $lang["genOpt_ldap_search_user"], $attrsText );
	$form->addElement('password', 'ldap_search_user_pwd', $lang["genOpt_ldap_search_user_pwd"],  $attrsText);
	$form->addElement('text', 'ldap_search_filter', $lang["genOpt_ldap_search_filter"], $attrsText);
	$form->addElement('text', 'ldap_search_timeout', $lang["genOpt_ldap_search_timeout"], $attrsText2);
	$form->addElement('text', 'ldap_search_limit', $lang["genOpt_ldap_search_limit"], $attrsText2);

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

	$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
	$DBRESULT =& $form->addElement('reset', 'reset', $lang["reset"]);


    $valid = false;
	if ($form->validate())	{

		# Update in DB
		updateLdapConfigData($form->getSubmitValue("gopt_id"));
		# Update in Oreon Object
		$oreon->optGen = array();
		$DBRESULT2 =& $pearDB->query("SELECT * FROM `general_opt` LIMIT 1");
		$oreon->optGen = $DBRESULT2->fetchRow();
		$o = "w";
   		$valid = true;
		$form->freeze();

	}
	if (!$form->validate() && isset($_POST["gopt_id"]))	{
	    print("<div class='msg' align='center'>".$lang["quickFormError"]."</div>");
	}

	$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=ldap'"));


	#
	##Apply a template definition
	#

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('valid', $valid);
	$tpl->display("formLDAP.ihtml");
?>
