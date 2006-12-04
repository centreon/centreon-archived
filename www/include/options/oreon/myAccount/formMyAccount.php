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
		if (PEAR::isError($DBRESULT))
			print $DBRESULT->getDebugInfo()."<br>";
		# Set base value
		$cct = array_map("myDecode", $DBRESULT->fetchRow());
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Langs -> $langs Array
	$langs = array();
	 $chemintotal = "./lang/";
	if ($handle  = opendir($chemintotal))   {
	    while ($file = readdir($handle))
	    	if (!is_dir("$chemintotal/$file") && strcmp($file, "index.php")) {
				$tab = split('\.', $file);
	      		$langs[$tab[0]] = $tab[0];
	      	}
		closedir($handle);
	}
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
	$form->addElement('header', 'title', $lang["myAcc_change"]);

	#
	## Basic information
	#
	$form->addElement('header', 'information', $lang['cct_infos']);
	$form->addElement('text', 'contact_name', $lang["cct_name"], $attrsText);
	$form->addElement('text', 'contact_alias', $lang["alias"], $attrsText);
	$form->addElement('text', 'contact_email', $lang["cct_mail"], $attrsText);
	$form->addElement('text', 'contact_pager', $lang["cct_pager"], $attrsText);
	$form->addElement('password', 'contact_passwd', $lang['cct_passwd'], $attrsText);
	$form->addElement('password', 'contact_passwd2', $lang['cct_passwd2'], $attrsText);
    $form->addElement('select', 'contact_lang', $lang["cct_lang"], $langs);

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
	$form->applyFilter('_ALL_', 'trim');
	$form->applyFilter('contact_name', 'myReplace');
	$form->addRule('contact_name', $lang['ErrName'], 'required');
	$form->addRule('contact_alias', $lang['ErrAlias'], 'required');
	$form->addRule('contact_email', $lang['ErrEmail'], 'required');
//	$form->addRule('contact_passwd', $lang['ErrRequired'], 'required');
//	$form->addRule('contact_passwd2', $lang['ErrRequired'], 'required');
	$form->addRule(array('contact_passwd', 'contact_passwd2'), $lang['ErrCctPasswd'], 'compare');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('contact_name', $lang['ErrAlreadyExist'], 'exist');
	$form->registerRule('existAlias', 'callback', 'testAliasExistence');
	$form->addRule('contact_alias', $lang['ErrAlreadyExist'], 'existAlias');
	$form->setRequiredNote($lang['requiredFields']);

	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Modify a contact information
	if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($cct);
	}
	
	if ($form->validate())	{
		updateContactInDB($oreon->user->get_id());
		if ($form->getSubmitValue("contact_passwd"))
			$oreon->user->passwd = md5($form->getSubmitValue("contact_passwd"));
		$o = NULL;
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c'"));
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