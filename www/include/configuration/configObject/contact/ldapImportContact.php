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

	if (!isset($oreon))
		exit();

	require_once $centreon_path . 'www/class/centreonLDAP.class.php';

	$DBRESULT = $pearDB->query("SELECT * FROM `options`");
	while ($res = $DBRESULT->fetchRow()) {
		$ldap_auth[$res["key"]] = html_entity_decode($res["value"]);
	}
	$DBRESULT->free();

	$attrsText 	= array("size"=>"80");
	$attrsText2	= array("size"=>"5");

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p );
	$form->addElement('header', 'title',_("LDAP Import"));

	/*
	 * Command information
	 */
	$form->addElement('header', 'options', _("LDAP Servers"));
	/*
	$form->addElement('text', 'ldap_search_filter', _("Search Filter"), $attrsText);
	$form->addElement('text', 'ldap_base_dn', _("LDAP Base DN"), $attrsText);
	$form->addElement('text', 'ldap_search_timeout', _("LDAP search timeout"), $attrsText2);
	$form->addElement('text', 'ldap_search_limit', _("LDAP Search Size Limit"), $attrsText2);
	*/
	$form->addElement('header', 'result', _("Search Result"));
	$form->addElement('header', 'ldap_search_result_output', _("Result"));

	$link = "LdapSearch()";
	$form->addElement("button", "ldap_search_button", _("Search"), array("onClick"=>$link));

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

	$form->addElement('hidden', 'contact_id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$tpl->assign('ldap_search_filter_help', _("Active Directory :")." (&(objectClass=user)(samaccounttype=805306368)(objectCategory=person)(cn=*))<br />"._("Lotus Domino :")." (&(objectClass=person)(cn=*))<br />"._("OpenLDAP :")." (&(objectClass=person)(cn=*))");
	$tpl->assign('ldap_search_filter_help_title', _("Filter Examples"));
	$tpl->assign('javascript', '<script type="text/javascript" src="./include/common/javascript/ContactAjaxLDAP/ajaxLdapSearch.js"></script>');

	$query = "SELECT i.ar_id, i.ari_value
			  FROM auth_ressource_info i, auth_ressource ar
			  WHERE i.ari_name = 'host'
			  AND i.ar_id = ar.ar_id
			  ORDER BY ar.ar_order";
	$res = $pearDB->query($query);
	$ldapServerList = "";
	while ($row = $res->fetchRow()) {
	    $ldapServerList .= "<input type='checkbox' name='ldapServer[".$row['ar_id']."]'/> " . $row['ari_value'] . "<br/>";
	}



	/*
	 * Just watch a contact information
	 */
	if ($o == "li")	{
	    /*
	     * Get information from template
	     */
	    $ldapAdmin = new CentreonLdapAdmin($pearDB);
	    $tmpOptions = $ldapAdmin->getTemplate();
	    $ldap_auth['ldap_base_dn'] = $tmpOptions['user_base_search'];
	    $ldap_auth['ldap_search_filter'] = sprintf($tmpOptions['user_filter'], '*');
		$subA = $form->addElement('submit', 'submitA', _("Import"));
		$form->setDefaults($ldap_auth);
	}

	$valid = false;
	if ($form->validate())	{
		if (isset($_POST["contact_select"]["select"]) ) {
			if ($form->getSubmitValue("submitA")) {
			    $ldap = new CentreonLdap($pearDB);
			    $ldap->connect();
				insertLdapContactInDB($ldap, $_POST["contact_select"]);
			}
		}
		$form->freeze();
		$valid = true;
	}

	$action = $form->getSubmitValue("action");

	if ($valid && $action["action"]["action"])
		require_once($path."listContact.php");
	else {
		/*
		 * Apply a template definition
		 */
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$form->accept($renderer);
		$tpl->assign('ldapServers', _('Import from LDAP servers'));
		$tpl->assign('ldapServerList', $ldapServerList);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("ldapImportContact.ihtml");
	}
?>