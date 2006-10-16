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

/*
	$attrs = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	submit();} " .
				"else if (this.form.elements['o'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				"	submit();}" .
				"else if (this.form.elements['o'].selectedIndex == 3) {" .
				"	submit();}");
	  
    $form->addElement('select', 'o', NULL, array(NULL=>NULL, "m"=>$lang['dup'], "d"=>$lang['delete']/*, "mc"=>$lang['mchange']*/), $attrs);
*/

/*
	if (!isset($oreon))
		exit();

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./include/common/");

	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p."&search_type_service=" . $search_type_service."&search_type_host=" . $search_type_host);


	$attrs = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	submit();} " .
				"else if (this.form.elements['o'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				"	submit();}" .
				"else if (this.form.elements['o'].selectedIndex == 3) {" .
				"	submit();}");
	  
    $form->addElement('select', 'o', NULL, array(NULL=>NULL, "m"=>$lang['dup'], "d"=>$lang['delete']/*, "mc"=>$lang['mchange']*/), $attrs);
    

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		*/

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./include/common/");



	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p."&search_type_service=" . $search_type_service."&search_type_host=" . $search_type_host);

?>

<SCRIPT LANGUAGE="JavaScript">
function setO(_i)
{
document.forms['form'].elements['o'].value = _i;
}

</SCRIPT>

<?

	$attrs = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"");	  
        $form->addElement('select', 'o1', NULL, array(NULL=>$lang["lgd_more_actions"], "m"=>$lang['dup'], "d"=>$lang['delete']/*, "mc"=>$lang['mchange']*/), $attrs);
	$form->setDefaults(array('o1' => NULL));
			$o1 =& $form->getElement('o1');
		$o1->setValue(NULL);
	
	$attrs = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"");
    $form->addElement('select', 'o2', NULL, array(NULL=>$lang["lgd_more_actions"], "m"=>$lang['dup'], "d"=>$lang['delete']/*, "mc"=>$lang['mchange']*/), $attrs);
	$form->setDefaults(array('o2' => NULL));
	if ($form->validate())	{
		$o2 =& $form->getElement('o2');
		$o2->setValue(NULL);
	
	$tpl->assign('limit', $limit);



	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);


	$tpl->assign('form', $renderer->toArray());



	$tpl->display("checkListAction.ihtml");




?>