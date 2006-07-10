<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called « Oreon Status Map » is developped by Merethis company for Lafarge Group, 
under the direction of Jean Baptiste Sarrodie <jean-baptiste@sarrodie.org>

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
	$map = array("map_path"=>NULL);
	if ($o == "c" || $o == "w")	{
		$res =& $pearDB->query("SELECT * FROM view_map WHERE map_id = '".$map_id."' LIMIT 1");
		# Set base value
		$map = array_map("myDecode", $res->fetchRow());
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
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang['views_map_add']);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["views_map_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["views_map_view"]);

	#
	## Basic information
	#
	$form->addElement('header', 'information', $lang['views_map_infos']);
	$form->addElement('text', 'map_name', $lang["views_map_name"], $attrsText);
	$form->addElement('text', 'map_description', $lang["views_map_desc"], $attrsText);	
	$file =& $form->addElement('file', 'filename', $lang["views_map_img"]);
	$form->addElement('text', 'map_path', $map["map_path"], NULL);	
	$form->addElement('textarea', 'map_comment', $lang['views_map_comment'], $attrsTextarea);	

	#
	## Further informations
	#
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');
	$form->setDefaults(array('action'=>'1'));
	
	$form->addElement('hidden', 'map_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	$form->applyFilter('_ALL_', 'trim');
	$form->addRule('map_name', $lang['ErrName'], 'required');
	$form->addRule('map_description', $lang['ErrRequired'], 'required');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('map_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);

	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch an information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&map_id=".$map_id."'"));
	    $form->setDefaults($map);
		$form->freeze();
	}
	# Modify an information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($map);
	}
	# Add an information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}
	
	$valid = false;
	if ($form->validate())	{
		$mapObj =& $form->getElement('map_id');
		if ($form->getSubmitValue("submitA"))
			$mapObj->setValue(insertMapInDB($file));
		else if ($form->getSubmitValue("submitC"))
			updateMapInDB($mapObj->getValue(), $file);
		$o = "w";	
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&map_id=".$mapObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once("listMap.php");
	else	{
		#Apply a template definition	
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formMap.ihtml");
	}
?>