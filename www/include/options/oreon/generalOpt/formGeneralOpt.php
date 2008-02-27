<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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

	if (!isset($oreon))
		exit();

	#
	## Database retrieve information
	#
	$DBRESULT =& $pearDB->query("SELECT * FROM general_opt LIMIT 1");
	# Set base value
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());
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
	## Nagios information
	#
	$form->addElement('header', 'nagios', _("Nagios information"));
	$form->addElement('text', 'nagios_path', _("Directory"), $attrsText);
	$form->addElement('text', 'nagios_path_bin', _("Directory + Binary"), $attrsText);
	$form->addElement('text', 'nagios_path_img', _("Images Directory"), $attrsText);
	$form->addElement('text', 'nagios_path_plugins', _("Plugins Directory"), $attrsText);
	$form->addElement('select', 'nagios_version', _("Nagios Release"), array(1=>"1", 2=>"2"));

	#
	## Oreon information
	#
	$form->addElement('header', 'oreon', _("Centreon informations"));
	$form->addElement('text', 'oreon_path', _("Directory"), $attrsText);
	$form->addElement('text', 'oreon_web_path', _("Centreon Web Directory"), $attrsText);
	$form->addElement('text', 'oreon_rrdbase_path', _("RRD Directory"), $attrsText);
	$form->addElement('text', 'oreon_refresh', _("Refresh Interval"), $attrsText2);
	$form->addElement('text', 'session_expire', _("Sessions Expiration Time"), $attrsText2);

	$form->addElement('text', 'maxViewMonitoring', _("Limit per page for Monitoring"), $attrsText2);
	$form->addElement('text', 'maxViewConfiguration', _("Limit per page (default)"), $attrsText2);

	$form->addElement('text', 'AjaxTimeReloadStatistic', _("Refresh Interval for statistics"), $attrsText2);
	$form->addElement('text', 'AjaxTimeReloadMonitoring', _("Refresh Interval for monitoring"), $attrsText2);
	$form->addElement('text', 'AjaxFirstTimeReloadStatistic', _("First Refresh delay for statistics"), $attrsText2);
	$form->addElement('text', 'AjaxFirstTimeReloadMonitoring', _("First Refresh delay for monitoring"), $attrsText2);

	$templates = array();
	if ($handle  = @opendir($oreon->optGen["oreon_path"]."www/Themes/"))	{
		while ($file = @readdir($handle))
			if (!is_file($oreon->optGen["oreon_path"]."www/Themes/".$file) && $file != "." && $file != ".." && $file != ".svn")
				$templates[$file] = $file;
		@closedir($handle);
	}
	$form->addElement('select', 'template', _("Template"), $templates);

	$TabColorNameAndLang = array("color_up"=>_("Host UP Color"),
                                    	"color_down"=>_("Host DOWN Color"),
                                    	"color_unreachable"=>_("Host UNREACHABLE Color"),
                                    	"color_ok"=>_("Service OK Color"),
                                    	"color_warning"=>_("Service WARNING Color"),
                                    	"color_critical"=>_("Service CRITICAL Color"),
                                    	"color_pending"=>_("Service PENDING Color"),
                                    	"color_unknown"=>_("Service UNKNOWN Color"),
					);

	while (list($nameColor, $val) = each($TabColorNameAndLang))	{
		$nameLang = $val;
		$codeColor = $gopt[$nameColor];
		$title = _("Pick a color");
		$attrsText3 	= array("value"=>$nameColor,"size"=>"8","maxlength"=>"7");
		$form->addElement('text', $nameColor, $nameLang,  $attrsText3);
		if ($form->validate())
			$colorColor = $form->exportValue($nameColor);
		$attrsText4 	= array("style"=>"width:50px; height:18px; background: ".$codeColor." url() left repeat-x 0px; border-color:".$codeColor.";");
		$attrsText5 	= array("onclick"=>"popup_color_picker('$nameColor','$nameLang','$title');");
		$form->addElement('button', $nameColor.'_color', "", $attrsText4);
		if (!$form->validate())
			$form->addElement('button', $nameColor.'_modify', _("Modify"), $attrsText5);
	}

	#
	## SNMP information
	#
	$form->addElement('header', 'snmp', _("SNMP information"));
	$form->addElement('text', 'snmp_community', _("Global Community"), $attrsText);
	$form->addElement('select', 'snmp_version', _("Version"), array("0"=>"1", "1"=>"2", "2"=>"2c"), $attrsAdvSelect);
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'snmp_trapd_used', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'snmp_trapd_used', null, _("No"), '0');
	$form->addGroup($tab, 'snmp_trapd_used', $lang["genOpt_snmp_trapd_used"], '&nbsp;');
	$form->setDefaults(array('snmp_trapd_used' => '0'));
	$form->addElement('text', 'snmp_trapd_path_conf', _("Directory of traps configuration files"), $attrsText);
	$form->addElement('text', 'snmp_trapd_path_daemon', $lang["genOpt_snmp_trapd_pathBin"], $attrsText);

    #
	## LDAP information
	#
	$form->addElement('header', 'ldap', _("LDAP information"));
	$form->addElement('text', 'ldap_host', _("LDAP Server"), $attrsText );
	$form->addElement('text', 'ldap_port', _("LDAP Port"),  $attrsText2);
	$form->addElement('text', 'ldap_base_dn', _("LDAP Base DN"), $attrsText);
	$form->addElement('text', 'ldap_login_attrib', _("LDAP Login Attribute"), $attrsText);
	$ldapUseSSL[] = &HTML_QuickForm::createElement('radio', 'ldap_ssl', null, _("Yes"), '1');
	$ldapUseSSL[] = &HTML_QuickForm::createElement('radio', 'ldap_ssl', null, _("No"), '0');
	$form->addGroup($ldapUseSSL, 'ldap_ssl', _("Enable LDAP over SSL"), '&nbsp;');
	$ldapEnable[] = &HTML_QuickForm::createElement('radio', 'ldap_auth_enable', null, _("Yes"), '1');
	$ldapEnable[] = &HTML_QuickForm::createElement('radio', 'ldap_auth_enable', null, _("No"), '0');
	$form->addGroup($ldapEnable, 'ldap_auth_enable', _("Enable LDAP authentification"), '&nbsp;');
	$form->addElement('header', 'searchldap', _("LDAP Search Information"));
	$form->addElement('text', 'ldap_search_user', _("User for search (anonymous if empty)"), $attrsText );
	$form->addElement('password', 'ldap_search_user_pwd', _("Password"),  $attrsText);
	$form->addElement('text', 'ldap_search_filter', _("Default LDAP filter"), $attrsText);
	$form->addElement('text', 'ldap_search_timeout', _("LDAP search timeout"), $attrsText2);
	$form->addElement('text', 'ldap_search_limit', _("LDAP Search Size Limit"), $attrsText2);

    #
	## Debug information
	#
	$form->addElement('header', 'debug', _("Debug"));
	$form->addElement('text', 'debug_path', _("Logs Directory"), $attrsText);

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', _("&nbsp;Clear debug file"));
	$form->addGroup($Opt, 'debug_auth_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', _("&nbsp;Clear debug file"));
	$form->addGroup($Opt, 'debug_nagios_import_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', _("&nbsp;Clear debug file"));
	$form->addGroup($Opt, 'debug_rrdtool_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', _("&nbsp;Clear debug file"));
	$form->addGroup($Opt, 'debug_ldap_import_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', _("&nbsp;Clear debug file"));
	$form->addGroup($Opt, 'debug_inventory_clear', '&nbsp;', '&nbsp;&nbsp;');


	$form->addElement('select', 'debug_auth', _("Authentification Debug"), array(0=>_("No"), 1=>_("Yes")));
	$form->addElement('select', 'debug_nagios_import', _("Nagios Import Debug"), array(0=>_("No"), 1=>_("Yes")));
	$form->addElement('select', 'debug_rrdtool', _("RRDTool Debug"), array(0=>_("No"), 1=>_("Yes")));
	$form->addElement('select', 'debug_ldap_import', _("LDAP Import Users Debug"), array(0=>_("No"), 1=>_("Yes")));
	$form->addElement('select', 'debug_inventory', _("Inventory Debug"), array(0=>_("No"), 1=>_("Yes")));

	#
	## Various information
	#
	$form->addElement('header', 'various', _("Various Information"));
	$form->addElement('text', 'mailer_path_bin', _("Directory + Mailer Binary"), $attrsText);
	$form->addElement('text', 'rrdtool_path_bin', _("Directory + RRDTool Binary"), $attrsText);
	$form->addElement('text', 'rrdtool_version', _("RRDTool Version"), $attrsText2);
	$ppUse[] = &HTML_QuickForm::createElement('radio', 'perfparse_installed', null, _("Yes"), '1');
	$ppUse[] = &HTML_QuickForm::createElement('radio', 'perfparse_installed', null, _("No"), '0');
	$form->addGroup($ppUse, 'perfparse_installed', _("Using PerfParse"), '&nbsp;');

	$graphPref[] = &HTML_QuickForm::createElement('radio', 'graph_preferencies', null, _("Graphs Plugins"), '1');
	$graphPref[] = &HTML_QuickForm::createElement('radio', 'graph_preferencies', null, _("Simple Graphs Renderer"), '0');
	$form->addGroup($graphPref, 'graph_preferencies', _("Favorite Graphs Engine"), '&nbsp;');

	$form->addElement('hidden', 'gopt_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	## Form Rules
	#
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('nagios_path', 'slash');
	//$form->applyFilter('nagios_path_bin', 'slash');
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
	$form->addRule('oreon_path', _("Can't write in directory"), 'is_valid_path');
	$form->addRule('nagios_path_plugins', _("Can't write in directory"), 'is_writable_path');
	$form->addRule('nagios_path_img', _("Can't write in directory"), 'is_writable_path');
	$form->addRule('nagios_path', _("The directory isn't valid"), 'is_valid_path');
	$form->addRule('nagios_path_bin', _("Can't execute binary"), 'is_executable_binary');
	$form->addRule('mailer_path_bin', _("Can't execute binary"), 'is_executable_binary');
	$form->addRule('rrdtool_path_bin', _("Can't execute binary"), 'is_executable_binary');
	$form->addRule('oreon_rrdbase_path', _("Can't write in directory"), 'is_writable_path');
	$form->addRule('debug_path', _("Can't write in directory"), 'is_writable_path');
	$form->addRule('snmp_trapd_path_conf', _("Can't write in file"), 'is_writable_file_if_exist');

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$form->setDefaults($gopt);

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
    "
    );
	#
	##End of Picker Color
	#

    $valid = false;
	if ($form->validate())	{
		# Update in DB
		updateGeneralConfigData(1);
		# Update in Oreon Object
		$oreon->optGen = array();
		$DBRESULT2 =& $pearDB->query("SELECT * FROM `general_opt` LIMIT 1");
		$oreon->optGen = $DBRESULT2->fetchRow();
		$o = NULL;
   		$valid = true;
		$form->freeze();
	}
	if (!$form->validate() && isset($_POST["gopt_id"]))
	    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."'"));

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