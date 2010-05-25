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

	$DBRESULT =& $pearDB->query("SELECT * FROM `options`");
	
	while ($opt =& $DBRESULT->fetchRow()) {
		$gopt[$opt["key"]] = myDecode($opt["value"]);
	}
	$DBRESULT->free();
	
	$attrsText 		= array("size"=>"40");
	$attrsText2		= array("size"=>"5");
	$attrsAdvSelect = null;

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Modify General Options"));

   	$form->addElement('header', 'debug', _("Debug"));
	
	$form->addElement('text', 'debug_path', _("Logs Directory"), $attrsText);

	$form->addElement('select', 'debug_auth', _("Authentification debug"), array(0=>_("No"), 1=>_("Yes")));
	$form->addElement('select', 'debug_sql', _("SQL debug"), array(0=>_("No"), 1=>_("Yes")));
	$form->addElement('select', 'debug_nagios_import', _("Nagios Import debug"), array(0=>_("No"), 1=>_("Yes")));
	$form->addElement('select', 'debug_rrdtool', _("RRDTool debug"), array(0=>_("No"), 1=>_("Yes")));
	$form->addElement('select', 'debug_ldap_import', _("LDAP Import Users debug"), array(0=>_("No"), 1=>_("Yes")));

	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('debug_path', 'slash');

	$form->registerRule('is_valid_path', 'callback', 'is_valid_path');
	$form->registerRule('is_readable_path', 'callback', 'is_readable_path');
	$form->registerRule('is_executable_binary', 'callback', 'is_executable_binary');
	$form->registerRule('is_writable_path', 'callback', 'is_writable_path');
	$form->registerRule('is_writable_file', 'callback', 'is_writable_file');
	$form->registerRule('is_writable_file_if_exist', 'callback', 'is_writable_file_if_exist');
	
	$form->addRule('debug_path', _("Can't write in directory"), 'is_writable_path');

	$form->addElement('hidden', 'gopt_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);


	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path.'debug/', $tpl);

	$form->setDefaults($gopt);

	$subC =& $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT =& $form->addElement('reset', 'reset', _("Reset"));

    $valid = false;
	if ($form->validate())	{

		/*
		 * Update in DB
		 */
		updateDebugConfigData($form->getSubmitValue("gopt_id"));
		
		/*
		 * Update in Oreon Object
		 */
		$oreon->initOptGen($pearDB);

		$o = NULL;
   		$valid = true;
		$form->freeze();

		if (isset($_POST["debug_auth_clear"]))
			@unlink($oreon->optGen["debug_path"]."auth.log");

		if (isset($_POST["debug_nagios_import_clear"]))
			@unlink($oreon->optGen["debug_path"]."cfgimport.log");

		if (isset($_POST["debug_rrdtool_clear"]))
			@unlink($oreon->optGen["debug_path"]."rrdtool.log");

		if (isset($_POST["debug_ldap_import_clear"]))
			@unlink($oreon->optGen["debug_path"]."ldapsearch.log");

		if (isset($_POST["debug_inventory_clear"]))
			@unlink($oreon->optGen["debug_path"]."inventory.log");
	}
	
	if (!$form->validate() && isset($_POST["gopt_id"])) {
	    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");
	}
	
	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=debug'"));
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign("genOpt_debug_options", _("Debug Properties"));
	$tpl->assign('valid', $valid);
	$tpl->display("form.ihtml");
?>