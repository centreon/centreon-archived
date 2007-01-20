<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

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

	#
	## Database retrieve information
	#
	if ($o == "c" || $o == "w")	{	
		$res =& $pearDB->query("SELECT * FROM view_country WHERE country_id = '".$country_id."' LIMIT 1");
		# Set base value
		$country = array_map("myDecode", $res->fetchRow());
		$res =& $pearDB->query("SELECT DISTINCT COUNT(city_name) AS nbr FROM view_city WHERE country_id = '".$country_id."'");
		$nbrCities =& $res->fetchRow();
		$country["nbr"] = $nbrCities["nbr"];
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
	$attrsText 		= array("size"=>"35");

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang['views_ct_add']);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["views_ct_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["views_ct_view"]);

	#
	## Basic information
	#
	$form->addElement('header', 'information', $lang['views_ct_infos']);
	$form->addElement('text', 'country_name', $lang["views_ct_name"], $attrsText);
	$form->addElement('text', 'country_alias', $lang["views_ct_alias"], $attrsText);
	$elem1 =& $form->addElement('text', 'nbr', $lang['views_ct_cty'], $attrsText);
	$elem1->freeze();
	
	#
	## Big new Def
	#
	/*
	$form->addElement('header', 'furtherInfos', $lang['further_infos']);
	$newCt[] = &HTML_QuickForm::createElement('radio', 'country_new', null, $lang["yes"], '1');
	$newCt[] = &HTML_QuickForm::createElement('radio', 'country_new', null, $lang["no"], '0');
	$form->addGroup($newCt, 'country_new', $lang['views_ct_init'], '&nbsp;');
	$form->setDefaults(array('country_new' => '1'));
	$form->addElement('file', 'country_cities', $lang["views_ct_cities"], $attrsText);
	*/
	
	#
	## Further informations
	#
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));
	
	$form->addElement('hidden', 'country_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["country_name"]));
	}
	$form->applyFilter('_ALL_', 'trim');
	$form->applyFilter('country_name', 'myReplace');
	$form->addRule('country_name', $lang['ErrName'], 'required');
	$form->addRule('country_alias', $lang['ErrAlias'], 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('country_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);

	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
		
	$tpl->assign("urlText", $lang['views_ct_citiesCmt1']);
	$tpl->assign("url", $lang['views_ct_citiesCmt2']);
	
	# Just watch an information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&country_id=".$country_id."'"));
	    $form->setDefaults($country);
		$form->freeze();
	}
	# Modify an information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($country);
	}
	# Add an information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	
	$valid = false;
	if ($form->validate())	{
		$countryObj =& $form->getElement('country_id');
		if ($form->getSubmitValue("submitA"))
			$countryObj->setValue(insertCountryInDB());
		else if ($form->getSubmitValue("submitC"))
			updateCountryInDB($countryObj->getValue());
		$o = "w";	
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&country_id=".$countryObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once("listCountry.php");
	else	{
		#Apply a template definition	
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formCountry.ihtml");
	}
?>