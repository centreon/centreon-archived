<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

	if (!isset($oreon))
		exit();

	$DBRESULT =& $pearDB->query("SELECT * FROM `options`");
	while ($opt =& $DBRESULT->fetchRow()) {
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

	$form->addElement('text', 'maxViewMonitoring', _("Limit per page for Monitoring"), $attrsText2);
	$form->addElement('text', 'maxViewConfiguration', _("Limit per page (default)"), $attrsText2);

	$form->addElement('text', 'AjaxTimeReloadStatistic', _("Refresh Interval for statistics"), $attrsText2);
	$form->addElement('text', 'AjaxTimeReloadMonitoring', _("Refresh Interval for monitoring"), $attrsText2);
	$form->addElement('text', 'AjaxFirstTimeReloadStatistic', _("First Refresh delay for statistics"), $attrsText2);
	$form->addElement('text', 'AjaxFirstTimeReloadMonitoring', _("First Refresh delay for monitoring"), $attrsText2);

	$form->addElement('text', 'gmt', _("GMT"), $attrsText2);

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
	
	$options1[] = &HTML_QuickForm::createElement('checkbox', 'yes', '&nbsp;', '');
	$form->addGroup($options1, 'enable_autologin', _("Enable Autologin"), '&nbsp;&nbsp;');
	
	$options2[] = &HTML_QuickForm::createElement('checkbox', 'yes', '&nbsp;', '');
	$form->addGroup($options2, 'display_autologin_shortcut', _("Display Autologin shortcut"), '&nbsp;&nbsp;');
	
	
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
	$form->registerRule('is_valid_path', 'callback', 'is_valid_path');
	$form->registerRule('is_readable_path', 'callback', 'is_readable_path');
	$form->registerRule('is_executable_binary', 'callback', 'is_executable_binary');
	$form->registerRule('is_writable_path', 'callback', 'is_writable_path');
	$form->registerRule('is_writable_file', 'callback', 'is_writable_file');
	$form->registerRule('is_writable_file_if_exist', 'callback', 'is_writable_file_if_exist');
	$form->addRule('oreon_path', _("Can't write in directory"), 'is_valid_path');
	$form->addRule('nagios_path_plugins', _("Can't write in directory"), 'is_writable_path');
	$form->addRule('nagios_path_img', _("Can't write in directory"), 'is_writable_path');
	$form->addRule('nagios_path', _("The directory isn't valid"), 'is_valid_path');


	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path.'general/', $tpl);

	$form->setDefaults($gopt);

	$subC =& $form->addElement('submit', 'submitC', _("Save"));
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
	$tpl->assign("time_min", _(" minutes "));
	$tpl->assign("genOpt_refresh_properties", _("Refresh Properties"));
	$tpl->assign("time_sec", _(" seconds "));
	$tpl->assign("genOpt_display_options", _("Display Options"));
	$tpl->assign("genOpt_problem_display", _("Problem display properties"));
	$tpl->assign("genOpt_time_zone", _("Time Zone"));
	$tpl->assign("genOpt_auth", _("Authentification properties"));
	$tpl->assign('valid', $valid);
	
	/*
	 * Apply a template definition
	 */
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	
	$tpl->display("form.ihtml");
?>