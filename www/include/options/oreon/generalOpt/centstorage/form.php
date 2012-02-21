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
	$DBRESULT2 = $pearDB->query("SELECT * FROM `options` WHERE `key` = 'centstorage'");
	while ($data = $DBRESULT2->fetchRow()) {
		if (isset($data['value']) && $data['key'] == "centstorage") {
			$gopt["enable_centstorage"] = $data['value'];
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
	$form->addElement('checkbox', 'enable_centstorage', _("Enable Centstorage Engine (require restart of centstorage)"));
	$form->addElement('text', 'RRDdatabase_path', _("Path to RRDTool Database For Metrics"), $attrsText);
	$form->addElement('text', 'RRDdatabase_status_path', _("Path to RRDTool Database For Status"), $attrsText);
	$form->addElement('text', 'RRDdatabase_nagios_stats_path', _("Path to RRDTool Database For Nagios Statistics"), $attrsText);
	$form->addElement('text', 'len_storage_rrd', _("RRDTool database size"), $attrsText2);
	$form->addElement('text', 'len_storage_mysql', _("Retention Duration for Data in MySQL"), $attrsText2);
	$form->addElement('checkbox', 'autodelete_rrd_db', _("RRDTool auto delete"));
	$form->addElement('text', 'purge_interval', _("Purge check interval"), $attrsText2);
	$form->addElement('checkbox', 'auto_drop', _("Drop Data in another file"));
	$form->addElement('text', 'drop_file', _("Drop file"), $attrsText);

	$storage_type = array(0 => "RRDTool", 2 => _("RRDTool & MySQL"));
	$form->addElement('select', 'storage_type', _("Storage Type"), $storage_type);
	$form->addElement('checkbox', 'archive_log', _("Archive Nagios Logs"));
	$form->addElement('text', 'archive_retention', _("Logs retention duration"), $attrsText2);

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
	if (!$form->validate() && isset($_POST["gopt_id"]))
	    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");

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

	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('valid', $valid);
	$tpl->assign('o', $o);
	$tpl->display("form.ihtml");
?>