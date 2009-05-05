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
	while ($res =& $DBRESULT->fetchRow())
		$ldap_auth[$res["key"]] = html_entity_decode($res["value"]);
	$DBRESULT->free();

	$attrsText 	= array("size"=>"80");
	$attrsText2	= array("size"=>"5");

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p );
	$form->addElement('header', 'title',_("Search Options"));

	/*
	 * Command information
	 */
	$form->addElement('header', 'options', _("Search Options"));
	$form->addElement('text', 'ldap_search_filter', _("Search Filter"), $attrsText );
	$form->addElement('text', 'ldap_base_dn', _("LDAP Base DN"), $attrsText);
	$form->addElement('text', 'ldap_search_timeout', _("LDAP search timeout"), $attrsText2);
	$form->addElement('text', 'ldap_search_limit', _("LDAP Search Size Limit"), $attrsText2);
	$form->addElement('header', 'result', _("Search Result"));
	$form->addElement('header', 'ldap_search_result_output', _("Result"));

	$link = "LdapSearch()";
	$form->addElement("button", "ldap_search_button", _("Search"), array("onClick"=>$link));

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'contact_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$tpl->assign('ldap_search_filter_help', _("Active Directory : (&(objectClass=user)(samaccounttype=805306368)(objectCategory=person)(cn=*))<br />Lotus Domino : (&(objectClass=person)(cn=*))<br />OpenLDAP : (&(objectClass=person)(cn=*))"));
	$tpl->assign('ldap_search_filter_help_title', _("Filter Examples"));
	$tpl->assign('javascript', '<script type="text/javascript" src="./include/common/javascript/ContactAjaxLDAP/ajaxLdapSearch.js"></script>');

	/*
	 * Just watch a contact information
	 */
	if ($o == "li")	{
		$subA =& $form->addElement('submit', 'submitA', _("Import"));
		$form->setDefaults($ldap_auth);
	}

	$valid = false;
	if ($form->validate())	{
		if (isset($_POST["contact_select"]["select"]) ) {
			if ($form->getSubmitValue("submitA"))
				insertLdapContactInDB($_POST["contact_select"]);
		}
		$form->freeze();
		$valid = true;
	}
	
	$action = $form->getSubmitValue("action");
	
	if ($valid && $action["action"]["action"])
		require_once($path."listContact.php");
	else	{
		/*
		 * Apply a template definition
		 */
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("ldapImportContact.ihtml");
	}
?>