<?php
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

	global $pearDB;
	$DBRESULT =& $pearDB->query("SELECT debug_path, debug_nagios_import FROM general_opt LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "DB Error : SELECT debug_path, debug_nagios_import FROM general_opt LIMIT 1 : ".$DBRESULT->getMessage()."<br>";

	$debug = $DBRESULT->fetchRow();

	$debug_nagios_import = $debug['debug_nagios_import'];
	$debug_path = $debug['debug_path'];

	if (!isset($debug_nagios_import))
		$debug_nagios_import = 0;

	#
	## Form begin
	#
	$attrSelect = array("style" => "width: 100px;");
	$attrsTextarea 	= array("rows"=>"12", "cols"=>"90");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', $lang["upl_name"]);

	$form->addElement('header', 'infos', $lang["upl_infos"]);
    $form->addElement('select', 'host', $lang["upl_host"], array(0=>"localhost"), $attrSelect);

	$form->addElement('header', 'opt', $lang["upl_opt"]);
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'del', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'del', null, $lang["no"], '0');
	$form->addGroup($tab, 'del', $lang["upl_del"], '&nbsp;');
	$form->setDefaults(array('del' => '0'));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'overwrite', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'overwrite', null, $lang["no"], '0');
	$form->addGroup($tab, 'overwrite', $lang["upl_over"], '&nbsp;');
	$form->setDefaults(array('overwrite' => '1'));
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'comment', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'comment', null, $lang["no"], '0');
	$form->addGroup($tab, 'comment', $lang["upl_comment"], '&nbsp;');
	$form->setDefaults(array('comment' => '0'));

	$form->addElement('header', 'fileType', $lang["upl_type"]);
	$form->addElement('header', 'fileMis1', $lang["upl_mis1"]);
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'Type', null, $lang["upl_typeNag"], 'nagios');
	$tab[] = &HTML_QuickForm::createElement('radio', 'Type', null, $lang["upl_typeCgi"], 'cgi');
	$tab[] = &HTML_QuickForm::createElement('radio', 'Type', null, $lang["upl_typeRes"], 'res');
	$tab[] = &HTML_QuickForm::createElement('radio', 'Type', null, $lang["upl_typeCfg"], 'cfg');
	$form->addGroup($tab, 'Type', $lang["upl_typeName"], '<br>');
	$form->setDefaults(array('Type' => array("Type"=>"cfg")));

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'cmdType', null, $lang["upl_typeCmdCheck"], '2');
	$tab[] = &HTML_QuickForm::createElement('radio', 'cmdType', null, $lang["upl_typeCmdNotif"], '1');
	$form->addGroup($tab, 'cmdType', $lang["upl_typeCmdType"], '&nbsp;');
	$form->setDefaults(array('cmdType' => array("cmdType"=>"2")));
	$form->addElement('header', 'fileCmt1', $lang["upl_typeCmdCmt1"]);
	$form->addElement('header', 'fileCmt2', $lang["upl_typeCmdCmt2"]);

	$file =& $form->addElement('file', 'filename', $lang["upl_file"]);
	$form->addElement('textarea', 'manualDef', $lang["upl_manualDef"], $attrsTextarea);

	$form->addElement('header', 'result', $lang["upl_result"]);
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'debug', null, $lang["yes"], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'debug', null, $lang["no"], '0');
	$form->addGroup($tab, 'debug', $lang["upl_debug"], '&nbsp;');
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

	$sub =& $form->addElement('submit', 'submit', $lang["upl_butOK"]);
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
			case "application/x-zip-compressed" : $msg .= $fDataz["name"]." ".$lang["upl_uplBadType"]."<br>"; break;
			case "application/x-gzip" : $file->moveUploadedFile($nagiosCFGPath); $msg .= $fDataz["name"]." ".$lang["upl_uplOk"]."<br>"; break; // tar.gz
			case "application/x-tar" : $file->moveUploadedFile($nagiosCFGPath); $msg .= $fDataz["name"]." ".$lang["upl_uplOk"]."<br>"; break; // tar
			case "application/octet-stream" : $file->moveUploadedFile($nagiosCFGPath); $msg .= $lang["upl_manualDef"]." ".$lang["upl_uplOk"]."<br>"; break; // Text
			default : $msg .= $lang["upl_uplKo"]."<br>";
		}
		# Buffering Data
		if (is_file($nagiosCFGPath.$fDataz["name"]))	{
			$buf =& gzfile($nagiosCFGPath.$fDataz["name"]);
			$buf ? $msg .= $lang["upl_carrOk"]."<br>" :	$msg .= $lang["upl_carrKo"]."<br>";
		}
		else if ($ret["manualDef"])	{
			if ($debug_nagios_import == 1)
				error_log("[" . date("d/m/Y H:s") ."] Nagios Import : Manual Definition\n", 3, $debug_path."cfgimport.log");

			$msg .= $lang["upl_manualDefOk"]."<br>";
			$msg .= $lang["upl_carrOk"]."<br>";
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
						$msg .= "1 ".$lang["upl_newEntries"]."<br>";
					break;
				case "cgi" :
					if ($ret["del"]["del"])
						deleteCgiCFG();
					if (insertCgiCFG($buf))
						$msg .= "1 ".$lang["upl_newEntries"]."<br>";
					break;
				case "res" :
					if ($ret["del"]["del"])
						deleteResourceCFG();
					$msg .= insertResourceCFG($buf)." ".$lang["upl_newEntries"]."<br>";
					break;
				case "cfg" :
					if ($ret["del"]["del"]) {
						deleteAllConfCFG();
						if ($debug_nagios_import == 1)
							error_log("[" . date("d/m/Y H:s") ."] Nagios Import : Delete All Conf\n", 3, $debug_path."cfgimport.log");
					}
					$nbr =& insertCFG($buf, $ret);
					($nbr["cmd"] ? $msg .= "Command : ".$nbr["cmd"]." ".$lang["upl_newEntries"]."<br>" : 0);
					($nbr["tp"] ? $msg .= "Time Period : ".$nbr["tp"]." ".$lang["upl_newEntries"]."<br>" : 0);
					($nbr["cct"] ? $msg .= "Contact : ".$nbr["cct"]." ".$lang["upl_newEntries"]."<br>" : 0);
					($nbr["cg"] ? $msg .= "Contact Group : ".$nbr["cg"]." ".$lang["upl_newEntries"]."<br>" : 0);
					($nbr["h"] ? $msg .= "Host : ".$nbr["h"]." ".$lang["upl_newEntries"]."<br>" : 0);
					($nbr["hei"] ? $msg .= "Host Extended Infos : ".$nbr["hei"]." ".$lang["upl_newEntries"]."<br>" : 0);
					($nbr["hg"] ? $msg .= "Host Group : ".$nbr["hg"]." ".$lang["upl_newEntries"]."<br>" : 0);
					($nbr["hd"] ? $msg .= "Host Dependency : ".$nbr["hd"]." ".$lang["upl_newEntries"]."<br>" : 0);
					($nbr["sv"] ? $msg .= "Service : ".$nbr["sv"]." ".$lang["upl_newEntries"]."<br>" : 0);
					($nbr["svd"] ? $msg .= "Service Dependency : ".$nbr["svd"]." ".$lang["upl_newEntries"]."<br>" : 0);
					($nbr["sg"] ? $msg .= "Service Group : ".$nbr["sg"]." ".$lang["upl_newEntries"]."<br>" : 0);
					($nbr["sgd"] ? $msg .= "Service Group Dependency : ".$nbr["sgd"]." ".$lang["upl_newEntries"]."<br>" : 0);
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

	$form->addElement('header', 'status', $lang["gen_status"]);
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