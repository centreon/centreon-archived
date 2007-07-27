<?
/**
Oreon is developped with GPL Licence 2.0 :
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
	$form->addElement('header', 'title', $lang["genOpt_change"]);

	#
	## Nagios information
	#
	$form->addElement('header', 'nagios', $lang['genOpt_nagios']);
	$form->addElement('text', 'nagios_path', $lang["genOpt_nagPath"], $attrsText);
	$form->addElement('text', 'nagios_path_bin', $lang["genOpt_nagBin"], $attrsText);
	$form->addElement('text', 'nagios_path_img', $lang["genOpt_nagImg"], $attrsText);
	$form->addElement('text', 'nagios_path_plugins', $lang["genOpt_nagPlug"], $attrsText);
	$form->addElement('select', 'nagios_version', $lang["genOpt_nagVersion"], array(1=>"1", 2=>"2"));

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

	$templates = array();
	if ($handle  = @opendir($oreon->optGen["oreon_path"]."www/Themes/"))	{
		while ($file = @readdir($handle))
			if (!is_file($oreon->optGen["oreon_path"]."www/Themes/".$file) && $file != "." && $file != ".." && $file != ".svn")
				$templates[$file] = $file;
		@closedir($handle);
	}
	$form->addElement('select', 'template', $lang["genOpt_template"], $templates);

	$TabColorNameAndLang = array("color_up"=>"genOpt_oHCUP",
                                    	"color_down"=>"genOpt_oHCDW",
                                    	"color_unreachable"=>"genOpt_oHCUN",
                                    	"color_ok"=>"genOpt_oSOK",
                                    	"color_warning"=>"genOpt_oSWN",
                                    	"color_critical"=>"genOpt_oSCT",
                                    	"color_pending"=>"genOpt_oSPD",
                                    	"color_unknown"=>"genOpt_oSUK",
					);

	while (list($nameColor, $val) = each($TabColorNameAndLang))	{
		$nameLang = $lang[$val];
		$codeColor = $gopt[$nameColor];
		$title = $lang["genOpt_colorPicker"];
		$attrsText3 	= array("value"=>$nameColor,"size"=>"8","maxlength"=>"7");
		$form->addElement('text', $nameColor, $nameLang,  $attrsText3);
		if ($form->validate())
			$colorColor = $form->exportValue($nameColor);
		$attrsText4 	= array("style"=>"width:50px; height:18px; background: ".$codeColor." url() left repeat-x 0px; border-color:".$codeColor.";");
		$attrsText5 	= array("onclick"=>"popup_color_picker('$nameColor','$nameLang','$title');");
		$form->addElement('button', $nameColor.'_color', "", $attrsText4);
		if (!$form->validate())
			$form->addElement('button', $nameColor.'_modify', $lang['modify'], $attrsText5);
	}

	#
	## SNMP information
	#
	$form->addElement('header', 'snmp', $lang["genOpt_snmp"]);
	$form->addElement('text', 'snmp_community', $lang["genOpt_snmpCom"], $attrsText);
	$form->addElement('select', 'snmp_version', $lang["genOpt_snmpVer"], array("0"=>"1", "1"=>"2", "2"=>"2c"), $attrsAdvSelect);
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'snmp_trapd_used', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'snmp_trapd_used', null, $lang["no"], '0');
	$form->addGroup($tab, 'snmp_trapd_used', $lang["genOpt_snmp_trapd_used"], '&nbsp;');
	$form->setDefaults(array('snmp_trapd_used' => '0'));
	$form->addElement('text', 'snmp_trapd_path_conf', $lang["genOpt_snmp_trapd_pathConf"], $attrsText);
	$form->addElement('text', 'snmp_trapd_path_daemon', $lang["genOpt_snmp_trapd_pathBin"], $attrsText);

    #
	## LDAP information
	#
	$form->addElement('header', 'ldap', $lang["genOpt_ldap"]);
	$form->addElement('text', 'ldap_host', $lang["genOpt_ldap_host"], $attrsText );
	$form->addElement('text', 'ldap_port', $lang["genOpt_ldap_port"],  $attrsText2);
	$form->addElement('text', 'ldap_base_dn', $lang["genOpt_ldap_base_dn"], $attrsText);
	$form->addElement('text', 'ldap_login_attrib', $lang["genOpt_ldap_login_attrib"], $attrsText);
	$ldapUseSSL[] = &HTML_QuickForm::createElement('radio', 'ldap_ssl', null, $lang["yes"], '1');
	$ldapUseSSL[] = &HTML_QuickForm::createElement('radio', 'ldap_ssl', null, $lang["no"], '0');
	$form->addGroup($ldapUseSSL, 'ldap_ssl', $lang["genOpt_ldap_ssl"], '&nbsp;');
	$ldapEnable[] = &HTML_QuickForm::createElement('radio', 'ldap_auth_enable', null, $lang["yes"], '1');
	$ldapEnable[] = &HTML_QuickForm::createElement('radio', 'ldap_auth_enable', null, $lang["no"], '0');
	$form->addGroup($ldapEnable, 'ldap_auth_enable', $lang["genOpt_ldap_auth_enable"], '&nbsp;');
	$form->addElement('header', 'searchldap', $lang["genOpt_searchldap"]);
	$form->addElement('text', 'ldap_search_user', $lang["genOpt_ldap_search_user"], $attrsText );
	$form->addElement('password', 'ldap_search_user_pwd', $lang["genOpt_ldap_search_user_pwd"],  $attrsText);
	$form->addElement('text', 'ldap_search_filter', $lang["genOpt_ldap_search_filter"], $attrsText);
	$form->addElement('text', 'ldap_search_timeout', $lang["genOpt_ldap_search_timeout"], $attrsText2);
	$form->addElement('text', 'ldap_search_limit', $lang["genOpt_ldap_search_limit"], $attrsText2);

    #
	## Debug information
	#
	$form->addElement('header', 'debug', $lang["genOpt_debug"]);
	$form->addElement('text', 'debug_path', $lang["genOpt_dPath"], $attrsText);

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', $lang["genOpt_debug_clear"]);
	$form->addGroup($Opt, 'debug_auth_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', $lang["genOpt_debug_clear"]);
	$form->addGroup($Opt, 'debug_nagios_import_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', $lang["genOpt_debug_clear"]);
	$form->addGroup($Opt, 'debug_rrdtool_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', $lang["genOpt_debug_clear"]);
	$form->addGroup($Opt, 'debug_ldap_import_clear', '&nbsp;', '&nbsp;&nbsp;');

	$Opt = array();
 	$Opt[] = &HTML_QuickForm::createElement('checkbox', '1', '&nbsp;', $lang["genOpt_debug_clear"]);
	$form->addGroup($Opt, 'debug_inventory_clear', '&nbsp;', '&nbsp;&nbsp;');


	$form->addElement('select', 'debug_auth', $lang["genOpt_debug_auth"], array(0=>$lang['no'], 1=>$lang['yes']));
	$form->addElement('select', 'debug_nagios_import', $lang["genOpt_debug_nagios_import"], array(0=>$lang['no'], 1=>$lang['yes']));
	$form->addElement('select', 'debug_rrdtool', $lang["genOpt_debug_rrdtool"], array(0=>$lang['no'], 1=>$lang['yes']));
	$form->addElement('select', 'debug_ldap_import', $lang["genOpt_debug_ldap_import"], array(0=>$lang['no'], 1=>$lang['yes']));
	$form->addElement('select', 'debug_inventory', $lang["genOpt_debug_inventory"], array(0=>$lang['no'], 1=>$lang['yes']));

	#
	## Various information
	#
	$form->addElement('header', 'various', $lang["genOpt_various"]);
	$form->addElement('text', 'mailer_path_bin', $lang["genOpt_mailer"], $attrsText);
	$form->addElement('text', 'rrdtool_path_bin', $lang["genOpt_rrdtool"], $attrsText);
	$form->addElement('text', 'rrdtool_version', $lang["genOpt_rrdtoolV"], $attrsText2);
	$ppUse[] = &HTML_QuickForm::createElement('radio', 'perfparse_installed', null, $lang["yes"], '1');
	$ppUse[] = &HTML_QuickForm::createElement('radio', 'perfparse_installed', null, $lang["no"], '0');
	$form->addGroup($ppUse, 'perfparse_installed', $lang["genOpt_perfparse"], '&nbsp;');

	$graphPref[] = &HTML_QuickForm::createElement('radio', 'graph_preferencies', null, $lang["m_views_graphPlu"], '1');
	$graphPref[] = &HTML_QuickForm::createElement('radio', 'graph_preferencies', null, $lang["m_views_graphShow"], '0');
	$form->addGroup($graphPref, 'graph_preferencies', $lang["genOpt_graph_preferencies"], '&nbsp;');

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
	$form->addRule('oreon_path', $lang['ErrWrPath'], 'is_valid_path');
	$form->addRule('nagios_path_plugins', $lang['ErrWrPath'], 'is_writable_path');
	$form->addRule('nagios_path_img', $lang['ErrWrPath'], 'is_writable_path');
	$form->addRule('nagios_path', $lang['ErrValidPath'], 'is_valid_path');
	$form->addRule('nagios_path_bin', $lang['ErrExeBin'], 'is_executable_binary');
	$form->addRule('mailer_path_bin', $lang['ErrExeBin'], 'is_executable_binary');
	$form->addRule('rrdtool_path_bin', $lang['ErrExeBin'], 'is_executable_binary');
	$form->addRule('oreon_rrdbase_path', $lang['ErrWrPath'], 'is_writable_path');
	$form->addRule('debug_path', $lang['ErrWrPath'], 'is_writable_path');
	$form->addRule('snmp_trapd_path_conf', $lang['ErrWrFile'], 'is_writable_file_if_exist');

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$form->setDefaults($gopt);

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
	    print("<div class='msg' align='center'>".$lang["quickFormError"]."</div>");

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