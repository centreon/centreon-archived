<?
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


	$res =& $pearDB->query("SELECT * FROM general_opt LIMIT 1");
	# Set base value
	$gopt = array_map("myDecode", $res->fetchRow());
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
	$form->addElement('header', 'title', $lang["genOpt_change"]);

	#
	## Oreon information
	#
	$form->addElement('header', 'oreon', $lang['genOpt_oreon']);
	$form->addElement('text', 'oreon_path', $lang["genOpt_oPath"], $attrsText);
	$form->addElement('text', 'oreon_web_path', $lang["genOpt_webPath"], $attrsText);
	$form->addElement('text', 'oreon_rrdbase_path', $lang["genOpt_oRrdbPath"], $attrsText);
	$form->addElement('text', 'oreon_refresh', $lang["genOpt_oRefresh"], $attrsText2);
	$form->addElement('text', 'session_expire', $lang["genOpt_oExpire"], $attrsText2);

	$form->addElement('text', 'maxViewMonitoring', $lang["genOpt_maxViewMonitoring"], $attrsText2);
	$form->addElement('text', 'maxViewConfiguration', $lang["genOpt_maxViewConfiguration"], $attrsText2);

	$form->addElement('text', 'AjaxTimeReloadStatistic', $lang["genOpt_AjaxTimeReloadStatistic"], $attrsText2);
	$form->addElement('text', 'AjaxTimeReloadMonitoring', $lang["genOpt_AjaxTimeReloadMonitoring"], $attrsText2);
	$form->addElement('text', 'AjaxFirstTimeReloadStatistic', $lang["genOpt_AjaxFirstTimeReloadStatistic"], $attrsText2);
	$form->addElement('text', 'AjaxFirstTimeReloadMonitoring', $lang["genOpt_AjaxFirstTimeReloadMonitoring"], $attrsText2);

	$form->addElement('text', 'gmt', $lang["genOpt_gmt"], $attrsText2);


	$templates = array();
	if ($handle  = @opendir($oreon->optGen["oreon_path"]."www/Themes/"))	{
		while ($file = @readdir($handle))
			if (!is_file($oreon->optGen["oreon_path"]."www/Themes/".$file) && $file != "." && $file != ".." && $file != ".svn")
				$templates[$file] = $file;
		@closedir($handle);
	}
	$form->addElement('select', 'template', $lang["genOpt_template"], $templates);
	
	$sort_type = array(	"last_state_change" => $lang["genOpt_problem_duration"],
						"host_name" => $lang["genOpt_problem_host"],
						"service_description" => $lang["genOpt_problem_service"],
						"current_state" => $lang["genOpt_problem_status"],
						"last_check" => $lang["genOpt_problem_last_check"],
						"plugin_output" => $lang["genOpt_problem_output"]);
	
	$form->addElement('select', 'problem_sort_type', $lang["genOpt_problem_sort_type"], $sort_type);
	
	$sort_order = array("ASC" => $lang["genOpt_problem_order_asc"], "DESC" => $lang["genOpt_problem_order_desc"]);
	$form->addElement('select', 'problem_sort_order', $lang["genOpt_problem_sort_order"], $sort_order);
	
	#
	## Form Rules
	#
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}
	$form->applyFilter('_ALL_', 'trim');
	$form->applyFilter('nagios_path', 'slash');
	$form->applyFilter('nagios_path_img', 'slash');
	$form->applyFilter('nagios_path_plugins', 'slash');
	$form->applyFilter('oreon_path', 'slash');
	$form->applyFilter('oreon_web_path', 'slash');
	$form->applyFilter('oreon_rrdbase_path', 'slash');
	$form->applyFilter('debug_path', 'slash');
	$form->registerRule('is_valid_path', 'callback', 'is_valid_path');
	$form->registerRule('is_readable_path', 'callback', 'is_readable_path');
	$form->registerRule('is_executable_binary', 'callback', 'is_executable_binary');
	$form->registerRule('is_writable_path', 'callback', 'is_writable_path');
	$form->registerRule('is_writable_file', 'callback', 'is_writable_file');
	$form->registerRule('is_writable_file_if_exist', 'callback', 'is_writable_file_if_exist');
	$form->addRule('oreon_path', $lang['ErrWrPath'], 'is_valid_path');
	$form->addRule('nagios_path_plugins', $lang['ErrWrPath'], 'is_writable_path');
	$form->addRule('nagios_path_img', $lang['ErrWrPath'], 'is_writable_path');
	$form->addRule('nagios_path', $lang['ErrValidPath'], 'is_valid_path');

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path.'general/', $tpl);

	$form->setDefaults($gopt);

	$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
	$res =& $form->addElement('reset', 'reset', $lang["reset"]);

    $valid = false;
	if ($form->validate())	{
		# Update in DB
		updateGeneralConfigData(1);
		# Update in Oreon Object
		$oreon->optGen = array();
		$res2 =& $pearDB->query("SELECT * FROM `general_opt` LIMIT 1");
		$oreon->optGen = $res2->fetchRow();
		$o = "w";
   		$valid = true;
		$form->freeze();
	}
	if (!$form->validate() && isset($_POST["gopt_id"]))	{
	    print("<div class='msg' align='center'>".$lang["quickFormError"]."</div>");
	}

	$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."'"));

	#
	##Apply a template definition
	#

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('valid', $valid);
	$tpl->display("formGeneralOpt.ihtml");
?>
