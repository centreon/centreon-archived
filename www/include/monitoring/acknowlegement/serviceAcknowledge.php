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
		exit ();

	require_once "HTML/QuickForm.php";
	require_once "HTML/QuickForm/Renderer/ArraySmarty.php";	
	require_once "./include/monitoring/common-Func.php";
	require_once "./class/centreonDB.class.php";
	
	$pearDBndo = new CentreonDB("ndo");

	isset($_GET["host_name"]) ? $host_name = $_GET["host_name"] : $host_name = NULL;
	isset($_GET["service_description"]) ? $service_description = $_GET["service_description"] : $service_description = NULL;
	isset($_GET["cmd"]) ? $cmd = $_GET["cmd"] : $cmd = NULL;
	isset($_GET["en"]) ? $en = $_GET["en"] : $en = 1;

	$path = "./include/monitoring/acknowlegement/";

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, './templates/');
	
	if (!$is_admin)
		$lcaHostByName["LcaHost"] = $oreon->user->access->getHostServicesName($pearDBndo);
	
	/*
	 * HOST LCA
	 */
	if ($is_admin || (isset($lcaHostByName["LcaHost"][$host_name]))){
	
		/*
		 * Fetch default values for form
		 */
		$user_params = get_user_param($oreon->user->user_id, $pearDB);
	
		if (!isset($user_params["ack_sticky"]))
			$user_params["ack_sticky"] = 1;

		if (!isset($user_params["ack_notify"]))
			$user_params["ack_notify"] = 0;

		if (!isset($user_params["ack_persistent"]))
			$user_params["ack_persistent"] = 1;

		if (!isset($user_params["force_active"]))
			$user_params["force_active"] = 1;

		$sticky = $user_params["ack_sticky"];
		$notify = $user_params["ack_notify"];
		$persistent = $user_params["ack_persistent"];
		$force_check = $user_params["force_check"];
	
		## Form begin
		$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p."&host_name=$host_name&service_description=$service_description");
		$form->addElement('header', 'title', _("Acknowledge a Service"));

		$tpl->assign('hostlabel', _("Host Name"));
		$tpl->assign('hostname', $host_name);
		$tpl->assign('en', $en);
		
		$tpl->assign('servicelabel', _("Service"));
		$tpl->assign('servicedescription', $service_description);
		$tpl->assign('authorlabel', _("Alias"));
		$tpl->assign('authoralias', $oreon->user->get_alias());
	
		$ckbx[] =& $form->addElement('checkbox', 'notify', _("notify"));
		$ckbx[0]->setChecked($notify);
		
		$ckbx1[] =& $form->addElement('checkbox', 'sticky', _("sticky"));
		$ckbx1[0]->setChecked($sticky);
		
		$ckbx2[] =& $form->addElement('checkbox', 'persistent', _("persistent"));
		$ckbx2[0]->setChecked($persistent);
		
		$ckbx3[] =& $form->addElement('checkbox', 'force_check', _("Force active check"));
		$ckbx3[0]->setChecked($force_check);
		
		$form->addElement('hidden', 'host_name', $host_name);
		$form->addElement('hidden', 'service_description', $service_description);
		$form->addElement('hidden', 'author', $oreon->user->get_alias());
		$form->addElement('hidden', 'cmd', $cmd);
		$form->addElement('hidden', 'p', $p);
		$form->addElement('hidden', 'en', $en);

		$form->applyFilter('__ALL__', 'myTrim');
		
		$textarea =& $form->addElement('textarea', 'comment', _("comment"), array("rows"=>"8", "cols"=>"80"));
		$textarea->setValue(sprintf(_("Acknowledged by %s"), $oreon->user->get_alias()));

		$form->addRule('comment', _("Comment is required"), 'required', '', 'client');
		$form->setJsWarnings(_("Invalid information entered"),_("Please correct these fields"));
		
		$form->addElement('submit', 'submit', ($en == 1) ? _("Add") : _("Delete"));
		$form->addElement('reset', 'reset', _("Reset"));
				
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());

		$tpl->display("serviceAcknowledge.ihtml");
	}
?>
