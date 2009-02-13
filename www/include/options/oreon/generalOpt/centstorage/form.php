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
	
	if (isset($_POST["o"]) && $_POST["o"])
		$o = $_POST["o"];

	$DBRESULT =& $pearDBO->query("SELECT * FROM `config` LIMIT 1");
	
			
	/*
	 * Set base value
	 */
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());

	/*
	 * Format of text input
	 */
	$attrsText 		= array("size"=>"40");
	$attrsText2		= array("size"=>"5");
	$attrsAdvSelect = null;

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Modify General Options"));

	$form->addElement('hidden', 'gopt_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	$form->setDefaults($gopt);
	
	/*
	 * Header information
	 */
	$form->addElement('header', 'folder', _("Storage folders"));
	$form->addElement('header', 'retention', _("Retention durations"));
	$form->addElement('header', 'Purge', _("Purge options"));
	$form->addElement('header', 'Input', _("Input treatment options"));
	$form->addElement('header', 'coreOptions', _("Censtorage Core Options"));
	$form->addElement('header', 'Drop', _("Drop possibility after parsing performance data"));
	$form->addElement('header', 'logs', _("Logs Integration Properties"));
	
	/*
	 * inputs declaration
	 */
	$form->addElement('text', 'RRDdatabase_path', _("Path to RRDTool Database For Metrics"), $attrsText);
	$form->addElement('text', 'RRDdatabase_status_path', _("Path to RRDTool Database For Status"), $attrsText);
	$form->addElement('text', 'RRDdatabase_nagios_stats_path', _("Path to RRDTool Database For Nagios Statistics"), $attrsText);
	$form->addElement('text', 'len_storage_rrd', _("RRDTool database size"), $attrsText2);
	$form->addElement('text', 'len_storage_mysql', _("Retention Duration for Data in MySQL"), $attrsText2);
	$form->addElement('checkbox', 'autodelete_rrd_db', _("RRDTool auto delete"));
	$form->addElement('text', 'sleep_time', _("Sleep Time"), $attrsText2);
	$form->addElement('text', 'purge_interval', _("Purge check interval"), $attrsText2);
	$form->addElement('checkbox', 'auto_drop', _("Drop Data in another file"));
	$form->addElement('text', 'drop_file', _("Drop file"), $attrsText);
	$form->addElement('text', 'perfdata_file', _("Perfdata"), $attrsText);
	
	$storage_type = array(0 => "RRDTool", 2 => _("RRDTool & MySQL"));	
	$form->addElement('select', 'storage_type', _("Storage Type"), $storage_type);
	$form->addElement('checkbox', 'archive_log', _("Archive Nagios Logs"));
	$form->addElement('text', 'archive_retention', _("Logs retention duration"), $attrsText2);
	$form->addElement('text', 'nagios_log_file', _("Nagios current log file to parse"), $attrsText);
	
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	/*
	 * Form Rules
	 */
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}
	
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('RRDdatabase_path', 'slash');
	$form->applyFilter('RRDdatabase_status_path', 'slash');
	$form->applyFilter('RRDdatabase_nagios_stats_path', 'slash');
	
	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path.'centstorage/', $tpl);
	$form->setDefaults($gopt);
	
	$subC =& $form->addElement('submit', 'submitC', _("Save"));
	$form->addElement('reset', 'reset', _("Reset"));
    $valid = false;
    
	if ($form->validate())	{
		
		/*
		 * Update in DB
		 */
		updateODSConfigData();
		
		$oreon->optGen = array();
		$DBRESULT2 =& $pearDBO->query("SELECT * FROM `config` LIMIT 1");
		$oreon->optGen =& $DBRESULT2->fetchRow();

		$o = NULL;
   		$valid = true;
		$form->freeze();
	}
	if (!$form->validate() && isset($_POST["gopt_id"]))
	    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=ods'"));

	/*
	 * Apply a template definition
	 */
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	
	$tpl->assign("genOpt_ODS_config", _("Centstorage Configuration"));
	$tpl->assign("ods_log_retention_unit", _("days"));
	$tpl->assign("ods_sleep_time_expl", _("in seconds - Must be higher than 10"));
	$tpl->assign("ods_purge_interval_expl", _("in seconds - Must be higher than 2"));
	
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('valid', $valid);
	$tpl->assign('o', $o);
	$tpl->display("form.ihtml");
?>