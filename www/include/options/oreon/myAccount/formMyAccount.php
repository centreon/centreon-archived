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
	if (!isset ($oreon))
		exit ();
	
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	require_once "./include/common/common-Func.php";
	
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
		
	#Path to the configuration dir
	$path = "./include/options/oreon/myAccount/";
	
	#PHP Functions
	require_once $path."DB-Func.php";
	
	#
	## Database retrieve information for the User
	#
	$cct = array();
	if ($o == "c")	{	
		$DBRESULT =& $pearDB->query("SELECT contact_id, contact_name, contact_alias, contact_lang, contact_email, contact_pager FROM contact WHERE contact_id = '".$oreon->user->get_id()."' LIMIT 1");
		# Set base value
		$cct = array_map("myDecode", $DBRESULT->fetchRow());
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Langs -> $langs Array
	$langs = array();
	$langs = getLangs();	
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"35");

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Change my settings"));

	#
	## Basic information
	#
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'contact_name', _("Name"), $attrsText);
	$form->addElement('text', 'contact_alias', _("Alias / Login"), $attrsText);
	$form->addElement('text', 'contact_email', _("Email"), $attrsText);
	$form->addElement('text', 'contact_pager', _("Pager"), $attrsText);
	$form->addElement('password', 'contact_passwd', _("Password"), $attrsText);
	$form->addElement('password', 'contact_passwd2', _("Confirm password"), $attrsText);
    $form->addElement('select', 'contact_lang', _("Language"), $langs);

	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["contact_name"]));
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('contact_name', 'myReplace');
	$form->addRule('contact_name', _("Compulsory name"), 'required');
	$form->addRule('contact_alias', _("Compulsory alias"), 'required');
	$form->addRule('contact_email', _("Valid Email"), 'required');
	$form->addRule(array('contact_passwd', 'contact_passwd2'), _("Passwords do not match"), 'compare');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('contact_name', _("Name already in use"), 'exist');
	$form->registerRule('existAlias', 'callback', 'testAliasExistence');
	$form->addRule('contact_alias', _("Name already in use"), 'existAlias');
	$form->setRequiredNote("<font style='color: red;'>*</font>"._("Required fields"));

	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Modify a contact information
	if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($cct);
	}
	
	if ($form->validate())	{
		updateContactInDB($oreon->user->get_id());
		if ($form->getSubmitValue("contact_passwd"))
			$oreon->user->passwd = md5($form->getSubmitValue("contact_passwd"));
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c'"));
		$form->freeze();
	}
	#Apply a template definition	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());	
	$tpl->assign('o', $o);		
	$tpl->display("formMyAccount.ihtml");
?>