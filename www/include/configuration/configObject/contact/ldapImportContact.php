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

	if (!isset($oreon))
		exit();

	require_once $centreon_path . 'www/class/centreonLDAP.class.php';

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
	$form->addElement('text', 'ldap_base_dn', _("LDAP Base DN"), $attrsText);
	$form->addElement('text', 'ldap_search_timeout', _("LDAP search timeout"), $attrsText2);
	$form->addElement('text', 'ldap_search_limit', _("LDAP Search Size Limit"), $attrsText2);
	*/
        $form->addElement('text', 'ldap_search_filter', _("Search Filter"), $attrsText);
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

	$query = "SELECT ar.ar_id, ar_name, REPLACE(ari_value, '%s', '*') as filter
                  FROM auth_ressource ar
                  LEFT JOIN auth_ressource_info ari ON ari.ar_id = ar.ar_id
                  WHERE ari.ari_name = 'user_filter'
                  ORDER BY ar_name";
	$res = $pearDB->query($query);
	$ldapConfList = "";
	while ($row = $res->fetchRow()) {
	    $ldapConfList .= "<input type='checkbox' name='ldapConf[".$row['ar_id']."]'/> " . $row['ar_name'];
            $ldapConfList .= "<br/>";
            $ldapConfList .= _('Filter'). ": <input size='80' type='text' value='".$row['filter']."' name='ldap_search_filter[".$row['ar_id']."]'/>";
            $ldapConfList .= "<br/><br/>";
	}


	/*
	 * Just watch a contact information
	 */
	if ($o == "li")	{
            $subA = $form->addElement('submit', 'submitA', _("Import"));
	}

	$valid = false;
	if ($form->validate())	{
            if (isset($_POST["contact_select"]["select"]) ) {
                if ($form->getSubmitValue("submitA")) {
                    insertLdapContactInDB($_POST["contact_select"]);
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
		$tpl->assign('ldapConfList', $ldapConfList);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		$tpl->display("ldapImportContact.ihtml");
	}
?>