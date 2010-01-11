<?php
/*
 * Copyright 2005-2009 MERETHIS
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

	global $pearDB;
	
	$DBRESULT =& $pearDB->query("SELECT * FROM `options` WHERE `key` IN ('debug_nagios_import', 'debug_path')");
	while ($res =& $DBRESULT->fetchRow())
		$debug[$res["key"]] = $res["value"];
	$DBRESULT->free(); 

	$debug_nagios_import = $debug['debug_nagios_import'];
	$debug_path = $debug['debug_path'];

	if (!isset($debug_nagios_import))
		$debug_nagios_import = 0;

	# Get Poller List
	$tab_nagios_server = array();
	$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` ORDER BY `localhost` DESC");
	while ($nagios =& $DBRESULT->fetchRow())
		$tab_nagios_server[$nagios['id']] = $nagios['name'];
	

	#
	## Form begin
	#
	$attrSelect = array("style" => "width: 100px;");
	$attrsTextarea 	= array("rows"=>"12", "cols"=>"90");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Nagios Configuration Upload"));

	$form->addElement('header', 'infos', _("Implied Server"));
    $form->addElement('select', 'host', _("Nagios/Centreon Server"), $tab_nagios_server, $attrSelect);

	$form->addElement('header', 'opt', _("Upload Options"));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'del', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'del', null, _("No"), '0');
	$form->addGroup($tab, 'del', _("Delete all configuration for the chosen type of files"), '&nbsp;');
	$form->setDefaults(array('del' => '0'));

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'overwrite', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'overwrite', null, _("No"), '0');
	$form->addGroup($tab, 'overwrite', _("Update definition in case of double definition"), '&nbsp;');
	$form->setDefaults(array('overwrite' => '1'));

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'comment', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'comment', null, _("No"), '0');
	$form->addGroup($tab, 'comment', _("Include comments"), '&nbsp;');
	$form->setDefaults(array('comment' => '0'));

	$form->addElement('header', 'fileType', _("File Type"));
	$form->addElement('header', 'fileMis1', _("For archive upload, be sure that the first line of each file has no importance because it is not handled.<br />Avoid to begin with a definition."));

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'Type', null, _("nagios.cfg"), 'nagios');
	$tab[] = &HTML_QuickForm::createElement('radio', 'Type', null, _("cgi.cfg"), 'cgi');
	$tab[] = &HTML_QuickForm::createElement('radio', 'Type', null, _("resource.cfg"), 'res');
	$tab[] = &HTML_QuickForm::createElement('radio', 'Type', null, _("Template based method file"), 'cfg');
	$form->addGroup($tab, 'Type', _("Type"), '<br />');
	$form->setDefaults(array('Type' => array("Type"=>"cfg")));

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'cmdType', null, _("Check Command"), '2');
	$tab[] = &HTML_QuickForm::createElement('radio', 'cmdType', null, _("Notification Command"), '1');
	$form->addGroup($tab, 'cmdType', _("Command Type"), '&nbsp;');
	$form->setDefaults(array('cmdType' => array("cmdType"=>"2")));
	$form->addElement('header', 'fileCmt1', _("It is recommanded to upload all the Command definitions first by specifying their types."));
	$form->addElement('header', 'fileCmt2', _("Indeed, it's the only way to make a difference between Check and Notification Commands."));

	$file =& $form->addElement('file', 'filename', _("File (zip, tar or cfg)"));
	$form->addElement('textarea', 'manualDef', _("Manual Filling"), $attrsTextarea);

	$form->addElement('header', 'result', _("Result"));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'debug', null, _("Yes"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'debug', null, _("No"), '0');
	$form->addGroup($tab, 'debug', _("Run Nagios debug (-v)"), '&nbsp;');
	$form->setDefaults(array('debug' => '0'));

	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	#
	##End of form definition
	#
	$form->applyFilter('__ALL__', 'myTrim');

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$sub =& $form->addElement('submit', 'submit', _("Load"));
	$msg = NULL;
	if ($form->validate()) {
		$ret = $form->getSubmitValues();
		$fDataz = array();
		$buf = NULL;
		$fDataz =& $file->getValue();

		if ($debug_nagios_import == 1)
			error_log("[" . date("d/m/Y H:s") ."] Nagios Import : ". $fDataz["name"] . " -> ". $fDataz["type"]."\n", 3, $debug_path."cfgimport.log");

		# File Moving
		switch ($fDataz["type"])	{
			case "application/x-zip-compressed" : $msg .= $fDataz["name"]." "._("Not supported extension")."<br />"; break;
			case "application/x-gzip" : $file->moveUploadedFile($nagiosCFGPath); $msg .= $fDataz["name"]." "._("File loading OK")."<br />"; break; // tar.gz
			case "application/x-tar" : $file->moveUploadedFile($nagiosCFGPath); $msg .= $fDataz["name"]." "._("File loading OK")."<br />"; break; // tar
			case "application/octet-stream" : $file->moveUploadedFile($nagiosCFGPath); $msg .= _("Manual filling OK")." "._("File loading OK")."<br />"; break; // Text
			default : $msg .= _("File loading KO")."<br />";
		}

		# Buffering Data
		if (is_file($nagiosCFGPath.$fDataz["name"]))	{
			$buf =& gzfile($nagiosCFGPath.$fDataz["name"]);
			$buf ? $msg .= _("Data recovery OK")."<br />" :	$msg .= _("Data recovery KO")."<br />";
		}
		else if ($ret["manualDef"])	{
			if ($debug_nagios_import == 1)
				error_log("[" . date("d/m/Y H:s") ."] Nagios Import : Manual Definition\n", 3, $debug_path."cfgimport.log");

			$msg .= _("Manual filling OK")."<br />";
			$msg .= _("Data recovery OK")."<br />";
			$buf =& explode("\n", $ret["manualDef"]);
		}
		# Enum Object Types
		if ($buf)	{
			
			if ($debug_nagios_import == 1)
				error_log("[" . date("d/m/Y H:s") ."] Nagios Import : File Type ". $ret["Type"]["Type"] ."\n", 3, $debug_path."cfgimport.log");

			switch ($ret["Type"]["Type"])	{
				case "nagios" :
					if ($ret["del"]["del"])
						deleteNagiosCFG();
					if (insertNagiosCFG($buf))
						$msg .= "1 "._("Entries are registered")."<br />";
					break;
				case "cgi" :
					if ($ret["del"]["del"])
						deleteCgiCFG();
					if (insertCgiCFG($buf))
						$msg .= "1 "._("Entries are registered")."<br />";
					break;
				case "res" :
					if ($ret["del"]["del"])
						deleteResourceCFG();
					$msg .= insertResourceCFG($buf)." "._("Entries are registered")."<br />";
					break;
				case "cfg" :
					if ($ret["del"]["del"]) {
						deleteAllConfCFG();
						if ($debug_nagios_import == 1)
							error_log("[" . date("d/m/Y H:s") ."] Nagios Import : Delete All Conf\n", 3, $debug_path."cfgimport.log");
					}
					$nbr =& insertCFG($buf, $ret);
					($nbr["cmd"] ? $msg .= "Command : ".$nbr["cmd"]." "._("Entries are registered")."<br />" : 0);
					($nbr["tp"] ? $msg .= "Time Period : ".$nbr["tp"]." "._("Entries are registered")."<br />" : 0);
					($nbr["cct"] ? $msg .= "Contact : ".$nbr["cct"]." "._("Entries are registered")."<br />" : 0);
					($nbr["cg"] ? $msg .= "Contact Group : ".$nbr["cg"]." "._("Entries are registered")."<br />" : 0);
					($nbr["h"] ? $msg .= "Host : ".$nbr["h"]." "._("Entries are registered")."<br />" : 0);
					($nbr["hg"] ? $msg .= "Host Group : ".$nbr["hg"]." "._("Entries are registered")."<br />" : 0);
					($nbr["hd"] ? $msg .= "Host Dependency : ".$nbr["hd"]." "._("Entries are registered")."<br />" : 0);
					($nbr["sv"] ? $msg .= "Service : ".$nbr["sv"]." "._("Entries are registered")."<br />" : 0);
					($nbr["svd"] ? $msg .= "Service Dependency : ".$nbr["svd"]." "._("Entries are registered")."<br />" : 0);
					($nbr["sg"] ? $msg .= "Service Group : ".$nbr["sg"]." "._("Entries are registered")."<br />" : 0);
					($nbr["sgd"] ? $msg .= "Service Group Dependency : ".$nbr["sgd"]." "._("Entries are registered")."<br />" : 0);
					break;
			}
		}
		# Delete File Uploaded
		if (is_file($nagiosCFGPath.$fDataz["name"]))  {
			unlink($nagiosCFGPath.$fDataz["name"]);
			if ($debug_nagios_import == 1)
				error_log("[" . date("d/m/Y H:s") ."] Nagios Import : Delete File Uploaded ". $nagiosCFGPath.$fDataz["name"] ."\n", 3, $debug_path."cfgimport.log");
		}
	}

	$form->addElement('header', 'status', _("Status"));
	if ($msg)
		$tpl->assign('msg', $msg);

	#
	##Apply a template definition
	#

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("formLoadFiles.ihtml");
?>