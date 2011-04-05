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

	$DBRESULT = $pearDB->query("SELECT * FROM `options`");
	while ($opt = $DBRESULT->fetchRow()) {
		$gopt[$opt["key"]] = myDecode($opt["value"]);
	}
	
	/*
	 * Style
	 */	
	$attrsText 		= array("size"=>"40");
	$attrsText2		= array("size"=>"5");
	$attrsAdvSelect = null;

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Modify General Options"));

	/*
	 * information
	 */
	$form->addElement('header', 'oreon', _("Centreon information"));
	$form->addElement('text', 'oreon_path', _("Directory"), $attrsText);
	$form->addElement('text', 'oreon_web_path', _("Centreon Web Directory"), $attrsText);

	$form->addElement('text', 'oreon_refresh', _("Refresh Interval"), $attrsText2);
	$form->addElement('text', 'session_expire', _("Sessions Expiration Time"), $attrsText2);

	$limit = array(10 => 10, 20 => 20, 30 => 30, 40 => 40, 50 => 50, 60 => 60, 70 => 70, 80 => 80, 90 => 90, 100 => 100, 200 => 200, 300 => 300, 400 => 400, 500 => 500);
	$form->addElement('select', 'maxViewMonitoring', _("Limit per page for Monitoring"), $limit);
	
	//$form->addElement('text', 'maxViewMonitoring', _("Limit per page for Monitoring"), $attrsText2);
	$form->addElement('text', 'maxViewConfiguration', _("Limit per page (default)"), $attrsText2);

	$form->addElement('text', 'AjaxTimeReloadStatistic', _("Refresh Interval for statistics"), $attrsText2);
	$form->addElement('text', 'AjaxTimeReloadMonitoring', _("Refresh Interval for monitoring"), $attrsText2);
	$form->addElement('text', 'AjaxFirstTimeReloadStatistic', _("First Refresh delay for statistics"), $attrsText2);
	$form->addElement('text', 'AjaxFirstTimeReloadMonitoring', _("First Refresh delay for monitoring"), $attrsText2);

	$form->addElement('text', 'gmt', _("Default timezone"), $attrsText2);
	
	$form->addElement('text', 'batch_cache_path', _("Cache path"), $attrsText);

	$templates = array();
	if ($handle  = @opendir($oreon->optGen["oreon_path"]."www/Themes/"))	{
		while ($file = @readdir($handle))
			if (!is_file($oreon->optGen["oreon_path"]."www/Themes/".$file) && $file != "." && $file != ".." && $file != ".svn")
				$templates[$file] = $file;
		@closedir($handle);
	}
	$form->addElement('select', 'template', _("Display Template"), $templates);
	
	$sort_type = array(	"last_state_change" => _("Duration"),
						"host_name" => _("Hosts"),
						"service_description" => _("Services"),
						"current_state" => _("Status"),
						"last_check" => _("Last check"),
						"plugin_output" => _("Output"));
	
	$form->addElement('select', 'problem_sort_type', _("Sort problems by  "), $sort_type);
	
	$sort_order = array("ASC" => _("Ascending"), "DESC" => _("Descending"));
	$form->addElement('select', 'problem_sort_order', _("Order sort problems "), $sort_order);
	
	//$form->addElement('text', 'enable_autologin', _("Enable Autologin"));
	//$form->addElement('text', 'display_autologin_shortcut', _("Display Autologin shortcut"));
	
	$options1[] = HTML_QuickForm::createElement('checkbox', 'yes', '&nbsp;', '');
	$form->addGroup($options1, 'enable_autologin', _("Enable Autologin"), '&nbsp;&nbsp;');
	
	$options2[] = HTML_QuickForm::createElement('checkbox', 'yes', '&nbsp;', '');
	$form->addGroup($options2, 'display_autologin_shortcut', _("Display Autologin shortcut"), '&nbsp;&nbsp;');
	
	$options3[] = HTML_QuickForm::createElement('checkbox', 'yes', '&nbsp;', '');
	$form->addGroup($options3, 'enable_gmt', _("Enable Timezone management"), '&nbsp;&nbsp;');
	
	/*
	 * Form Rules
	 */
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('nagios_path', 'slash');
	$form->applyFilter('nagios_path_img', 'slash');
	$form->applyFilter('nagios_path_plugins', 'slash');
	$form->applyFilter('oreon_path', 'slash');
	$form->applyFilter('oreon_web_path', 'slash');
	$form->applyFilter('debug_path', 'slash');
	$form->applyFilter('batch_cache_path', 'slash');
	$form->registerRule('is_valid_path', 'callback', 'is_valid_path');
	$form->registerRule('is_readable_path', 'callback', 'is_readable_path');
	$form->registerRule('is_executable_binary', 'callback', 'is_executable_binary');
	$form->registerRule('is_writable_path', 'callback', 'is_writable_path');
	$form->registerRule('is_writable_file', 'callback', 'is_writable_file');
	$form->registerRule('is_writable_file_if_exist', 'callback', 'is_writable_file_if_exist');
	$form->addRule('oreon_path', _("Can't write in directory"), 'is_valid_path');
	$form->addRule('batch_cache_path', _("Can't write in directory"), 'is_writable_path');
	$form->addRule('nagios_path_plugins', _("Can't write in directory"), 'is_writable_path');
	$form->addRule('nagios_path_img', _("Can't write in directory"), 'is_writable_path');
	$form->addRule('nagios_path', _("The directory isn't valid"), 'is_valid_path');
	


	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path.'general/', $tpl);

	$form->setDefaults($gopt);

	$subC = $form->addElement('submit', 'submitC', _("Save"));
	$form->addElement('reset', 'reset', _("Reset"));

    $valid = false;
	if ($form->validate())	{
		/*
		 * Update in DB
		 */
		updateGeneralConfigData(1);
		
		/*
		 * Update in Oreon Object
		 */
		$oreon->initOptGen($pearDB);
		
		$o = NULL;
   		$valid = true;
		$form->freeze();
	}
	if (!$form->validate() && isset($_POST["gopt_id"]))	{
	    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");
	}

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."'"));

	/*
	 * Send variable to template
	 */
	
	$tpl->assign('o', $o);
	$tpl->assign("genOpt_max_page_size", _("Maximum page size"));
	$tpl->assign("genOpt_expiration_properties", _("Sessions Properties"));
	$tpl->assign("time_min", _("minutes"));
	$tpl->assign("genOpt_refresh_properties", _("Refresh Properties"));
	$tpl->assign("time_sec", _("seconds"));
	$tpl->assign("genOpt_display_options", _("Display Options"));
	$tpl->assign("genOpt_problem_display", _("Problem display properties"));
	$tpl->assign("genOpt_time_zone", _("Time Zone"));
	$tpl->assign("genOpt_auth", _("Authentification properties"));
	$tpl->assign("genOpt_batch", _("Batch configuration"));
	$tpl->assign('valid', $valid);
	
	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	
	$tpl->display("form.ihtml");
?>