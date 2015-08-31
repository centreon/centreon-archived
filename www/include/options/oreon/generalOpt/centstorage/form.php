<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	if (!isset($oreon)) {
		exit();
	}

	if (isset($_POST["o"]) && $_POST["o"]) {
		$o = $_POST["o"];
	}

	/*
	 * Get data into config table of centstorage
	 */
	$DBRESULT = $pearDBO->query("SELECT * FROM `config` LIMIT 1");
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());

	/*
	 * Get centstorage state
	 */
	$DBRESULT2 = $pearDB->query("SELECT * FROM `options` WHERE `key` LIKE 'centstorage%'");
	while ($data = $DBRESULT2->fetchRow()) {
		if (isset($data['value']) && $data['key'] == "centstorage") {
			$gopt["enable_centstorage"] = $data['value'];
		} else {
                    $gopt[$data['key']] = $data['value'];
                }
	}
    
    
    /*
	 * Get insert_data state
	 */
	$DBRESULT2 = $pearDB->query("SELECT * FROM `options` WHERE `key` = 'index_data'");
	while ($data = $DBRESULT2->fetchRow()) {
		if (isset($data['value']) && $data['key'] == "index_data") {
            if ($data['value'] == "1") {
                $gopt["insert_in_index_data"] = "0";
            } elseif ($data['value'] == "0") {
                $gopt["insert_in_index_data"] = "1";
            } else {
                $gopt["insert_in_index_data"] = "1";
            }
		}
	}

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
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	$form->setDefaults($gopt);

	/*
	 * Header information
	 */
	$form->addElement('header', 'enable', _("Engine Status"));
    $form->addElement('header', 'insert', _("Resources storage"));
	$form->addElement('header', 'folder', _("Storage folders"));
	$form->addElement('header', 'retention', _("Retention durations"));
	$form->addElement('header', 'Purge', _("Purge options"));
	$form->addElement('header', 'Input', _("Input treatment options"));
	$form->addElement('header', 'coreOptions', _("Censtorage Core Options"));
	$form->addElement('header', 'Drop', _("Drop possibility after parsing performance data"));
	$form->addElement('header', 'logs', _("Logs Integration Properties"));
	$form->addElement('header', 'reporting', _("Dashboard Integration Properties"));
    $form->addElement('header', 'audit', _("Audit log activation"));

	/*
	 * inputs declaration
	 */
	$form->addElement('checkbox', 'enable_centstorage', _("Enable Centstorage Engine (require restart of centstorage)"));
    $form->addElement('checkbox', 'insert_in_index_data', _("Enable resources's insertion in index_data by Centreon"));
	$form->addElement('text', 'RRDdatabase_path', _("Path to RRDTool Database For Metrics"), $attrsText);
	$form->addElement('text', 'RRDdatabase_status_path', _("Path to RRDTool Database For Status"), $attrsText);
	$form->addElement('text', 'RRDdatabase_nagios_stats_path', _("Path to RRDTool Database For Monitoring Engine Statistics"), $attrsText);
	$form->addElement('text', 'len_storage_rrd', _("RRDTool database size"), $attrsText2);
	$form->addElement('text', 'len_storage_mysql', _("Retention Duration for Data in MySQL"), $attrsText2);
	$form->addElement('checkbox', 'autodelete_rrd_db', _("RRDTool auto delete"));
	$form->addElement('text', 'purge_interval', _("Purge check interval"), $attrsText2);
        $form->addElement('checkbox', 'centstorage_auto_drop', _("Drop Data in another file"));
        $form->addElement('text', 'centstorage_drop_file', _("Drop file"), $attrsText);
        
	$storage_type = array(0 => "RRDTool", 2 => _("RRDTool & MySQL"));
	$form->addElement('select', 'storage_type', _("Storage Type"), $storage_type);
	$form->addElement('checkbox', 'archive_log', _("Archive logs of monitoring engine"));
	$form->addElement('text', 'archive_retention', _("Logs retention duration"), $attrsText2);
	$form->addElement('text', 'reporting_retention', _("Reporting retention duration (dashboard)"), $attrsText2);
    $form->addElement('checkbox', 'audit_log_option', _("Enable/Disable audit logs"));

	$redirect = $form->addElement('hidden', 'o');
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

	$subC = $form->addElement('submit', 'submitC', _("Save"));
	$form->addElement('reset', 'reset', _("Reset"));
    $valid = false;

	if ($form->validate())	{

		/*
		 * Update in DB
		 */
		updateODSConfigData();

		$oreon->optGen = array();
		$DBRESULT2 = $pearDBO->query("SELECT * FROM `config` LIMIT 1");
		$oreon->optGen = $DBRESULT2->fetchRow();

		$o = NULL;
   		$valid = true;
		$form->freeze();
	}
	if (!$form->validate() && isset($_POST["gopt_id"])) {
	    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");
	}

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=ods'"));

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);

	$tpl->assign("genOpt_ODS_config", _("Centstorage Configuration"));
	$tpl->assign("ods_log_retention_unit", _("days"));
	$tpl->assign("ods_sleep_time_expl", _("in seconds - Must be higher than 10"));
	$tpl->assign("ods_purge_interval_expl", _("in seconds - Must be higher than 2"));

        // prepare help texts
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) {
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);

	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('valid', $valid);
	$tpl->assign('o', $o);
	$tpl->display("form.ihtml");
?>