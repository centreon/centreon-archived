<?php
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

	$path = dirname(__FILE__);
	$valid = 0;
	
	/* Include pour la création du formulaire */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	$attrsText = array("size"=>"35");
	
	$query = "SELECT `gopt_id`, `patch_type_stable`, `patch_type_RC`, `patch_type_patch`, `patch_type_secu`, `patch_type_beta`, `patch_path_download` FROM `general_opt` LIMIT 1";
	$DBRESULT =& $pearDB->query($query);
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());
	
	$form = new HTML_QuickForm('patchOption', 'post', "?p=".$p);
	$form->addElement('header', 'title', $lang["patchOption_change"]);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_stable', null, $lang["yes"], 'Y');
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_stable', null, $lang["no"], 'N');
	$form->addGroup($tab, 'patch_type_stable', $lang["patchOption_check_stable"], '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_patch', null, $lang["yes"], 'Y');
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_patch', null, $lang["no"], 'N');
	$form->addGroup($tab, 'patch_type_patch', $lang["patchOption_check_patch"], '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_secu', null, $lang["yes"], 'Y');
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_secu', null, $lang["no"], 'N');
	$form->addGroup($tab, 'patch_type_secu', $lang["patchOption_check_security"], '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_RC', null, $lang["yes"], 'Y');
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_RC', null, $lang["no"], 'N');
	$form->addGroup($tab, 'patch_type_RC', $lang["patchOption_check_rc"], '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_beta', null, $lang["yes"], 'Y');
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_beta', null, $lang["no"], 'N');
	$form->addGroup($tab, 'patch_type_beta', $lang["patchOption_check_beta"], '&nbsp;');
	$form->addElement('hidden', 'gopt_id');
	$form->addElement('text', 'patch_path_download', $lang["patchOption_path_download"], $attrsText);
	
	$form->setDefaults($gopt);
	
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
	$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	
	#
	##Picker Color JS
	#
	$tpl->assign('colorJS',"
	<script type='text/javascript'>
		function popup_color_picker(t,name,title)
		{
			var width = 400;
			var height = 300;
			window.open('./include/common/javascript/color_picker.php?n='+t+'&name='+name+'&title='+title, 'cp', 'resizable=no, location=no, width='
						+width+', height='+height+', menubar=no, status=yes, scrollbars=no, menubar=no');
		}
	</script>
	");
	#
	##End of Picker Color
	#
	
	if ($form->validate()) {
		$ret = array();
		$ret = $form->getSubmitValues();
		$query = "UPDATE `general_opt` SET `patch_type_stable`='" . $ret['patch_type_stable']["patch_type_stable"] . "'";
		$query .= ", `patch_type_patch`='" . $ret['patch_type_patch']['patch_type_patch'] . "'";
		$query .= ", `patch_type_secu`='" . $ret['patch_type_secu']['patch_type_secu'] . "'";
		$query .= ", `patch_type_RC`='" . $ret['patch_type_RC']['patch_type_RC'] . "'";
		$query .= ", `patch_type_beta`='" . $ret['patch_type_beta']['patch_type_beta'] . "'";
		$query .= " WHERE `gopt_id`=1";
		$pearDB->query($query);
	}
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('valid', $valid);
	$tpl->display("patchOptions.ihtml");
?>