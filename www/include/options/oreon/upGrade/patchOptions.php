<?php
/*
 * Copyright 2005-2010 MERETHIS
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

	$path = dirname(__FILE__);
	$valid = 0;
	
	/* Include pour la création du formulaire */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	$attrsText = array("size"=>"35");

	$DBRESULT =& $pearDB->query("SELECT * FROM `options`");
	while ($result =& $DBRESULT->fetchRow()) {
		$gopt[$result["key"]] = myDecode($result["value"]);
	}
	$DBRESULT->free();	
	
	$form = new HTML_QuickForm('patchOption', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Change update options"));
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_stable', null, _("Yes"), 'Y');
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_stable', null, _("No"), 'N');
	$form->addGroup($tab, 'patch_type_stable', _("Check stable versions"), '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_patch', null, _("Yes"), 'Y');
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_patch', null, _("No"), 'N');
	$form->addGroup($tab, 'patch_type_patch', _("Check patches"), '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_secu', null, _("Yes"), 'Y');
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_secu', null, _("No"), 'N');
	$form->addGroup($tab, 'patch_type_secu', _("Check secu-patches"), '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_RC', null, _("Yes"), 'Y');
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_RC', null, _("No"), 'N');
	$form->addGroup($tab, 'patch_type_RC', _("Check Release candidate"), '&nbsp;');
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_beta', null, _("Yes"), 'Y');
	$tab[] = &HTML_QuickForm::createElement('radio', 'patch_type_beta', null, _("No"), 'N');
	$form->addGroup($tab, 'patch_type_beta', _("Check Beta"), '&nbsp;');
	$form->addElement('hidden', 'gopt_id');
	$form->addElement('text', 'patch_path_download', _("Patch Download path"), $attrsText);
	
	$form->setDefaults($gopt);
	
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	$subC =& $form->addElement('submit', 'submitC', _("Save"));
	$res =& $form->addElement('reset', 'reset', _("Reset"));
	
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
		$query = "UPDATE `general_opt` SET `patch_type_stable` = '" . $ret['patch_type_stable']["patch_type_stable"] . "'";
		$query .= ", `patch_type_patch` = '" . $ret['patch_type_patch']['patch_type_patch'] . "'";
		$query .= ", `patch_type_secu` = '" . $ret['patch_type_secu']['patch_type_secu'] . "'";
		$query .= ", `patch_type_RC` = '" . $ret['patch_type_RC']['patch_type_RC'] . "'";
		$query .= ", `patch_type_beta` = '" . $ret['patch_type_beta']['patch_type_beta'] . "'";
		$query .= ", `patch_path_download`= '" . htmlentities($ret['patch_path_download'], ENT_QUOTES) . "'";
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