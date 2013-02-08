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

	#
	## Database retrieve information for differents elements list we need on the page
	#
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"40");
	$attrsText2		= array("size"=>"5");
	$attrsAdvSelect = null;

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Modify General Options"));

	#
	## SNMP information
	#
	$form->addElement('header', 'snmp', _("SNMP information"));
	$form->addElement('text', 'snmp_community', _("Global Community"), $attrsText);
	$form->addElement('select', 'snmp_version', _("Version"), array("0"=>"1", "1"=>"2", "2"=>"2c", "3"=>"3"), $attrsAdvSelect);
	$form->addElement('text', 'snmp_trapd_path_conf', _("Directory of traps configuration files"), $attrsText);
	$form->addElement('text', 'snmpttconvertmib_path_bin', _("snmpttconvertmib Directory + Binary"), $attrsText);
	$form->addElement('text', 'snmptt_unknowntrap_log_file', _("SNMPTT log file"), $attrsText);
	$form->addElement('text', 'perl_library_path', _("Perl library directory"), $attrsText);

	#
	## Form Rules
	#
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->registerRule('is_writable_path', 'callback', 'is_writable_path');
	$form->registerRule('is_executable_binary', 'callback', 'is_executable_binary');
	$form->registerRule('is_readable_path', 'callback', 'is_readable_path');
	$form->addRule('snmp_trapd_path_conf', _("Can't write in directory"), 'is_writable_path');
	$form->addRule('snmp_trapd_path_conf', _("Required Field"), 'required');
	$form->addRule('snmpttconvertmib_path_bin', _("Can't execute binary"), 'is_executable_binary');
	$form->addRule('perl_library_path', _("Can't read in directory"), 'is_readable_path');
	#
	##End of form definition
	#

	$form->addElement('hidden', 'gopt_id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path."snmp/", $tpl);

	$form->setDefaults($oreon->optGen);

	$subC = $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT = $form->addElement('reset', 'reset', _("Reset"));

    $valid = false;
	if ($form->validate())	{
		# Update in DB
		updateSNMPConfigData($form->getSubmitValue("gopt_id"));

		# Update in Oreon Object
		$oreon->initOptGen($pearDB);

		$o = NULL;
   		$valid = true;
		$form->freeze();
	}
	if (!$form->validate() && isset($_POST["gopt_id"]))
	    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=snmp'"));

        // prepare help texts
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) {
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);

	#
	##Apply a template definition
	#
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('valid', $valid);
	$tpl->display("form.ihtml");
?>
