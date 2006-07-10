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
	$form->applyFilter('_ALL_', 'trim');
	
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
		# File Moving
		switch ($fDataz["type"])	{
			case "application/x-zip-compressed" : $msg .= $fDataz["name"]." ".$lang["upl_uplBadType"]."<br>"; break;
			case "application/x-gzip" : $file->moveUploadedFile($nagiosCFGPath); $msg .= $fDataz["name"]." ".$lang["upl_uplOk"]."<br>"; break; // tar.gz
			case "application/octet-stream" : $file->moveUploadedFile($nagiosCFGPath); $msg .= $lang["upl_manualDef"]." ".$lang["upl_uplOk"]."<br>"; break; // Text
			default : $msg .= $lang["upl_uplKo"]."<br>";
		}
		# Buffering Data
		if (is_file($nagiosCFGPath.$fDataz["name"]))	{
			$buf =& gzfile($nagiosCFGPath.$fDataz["name"]);
			$buf ? $msg .= $lang["upl_carrOk"]."<br>" :	$msg .= $lang["upl_carrKo"]."<br>";
		}
		else if ($ret["manualDef"])	{
			$msg .= $lang["upl_manualDefOk"]."<br>";
			$msg .= $lang["upl_carrOk"]."<br>";
			$buf =& explode("\n", $ret["manualDef"]);
		}
		# Enum Object Types
		if ($buf)	{
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
					if ($ret["del"]["del"])
						deleteAllConfCFG();
					$nbr =& insertCFG($buf, $ret);
					$msg .= "Command :".($nbr["cmd"] ? $nbr["cmd"] : "0")." ".$lang["upl_newEntries"]."<br>";
					$msg .= "Time Period :".($nbr["tp"] ? $nbr["tp"] : "0")." ".$lang["upl_newEntries"]."<br>";
					$msg .= "Contact :".($nbr["cct"] ? $nbr["cct"] : "0")." ".$lang["upl_newEntries"]."<br>";
					$msg .= "Contact Group :".($nbr["cg"] ? $nbr["cg"] : "0")." ".$lang["upl_newEntries"]."<br>";
					$msg .= "Host :".($nbr["h"] ? $nbr["h"] : "0")." ".$lang["upl_newEntries"]."<br>";
					$msg .= "Host Extended Infos :".($nbr["hei"] ? $nbr["hei"] : "0")." ".$lang["upl_newEntries"]."<br>";
					$msg .= "Host Group :".($nbr["hg"] ? $nbr["hg"] : "0")." ".$lang["upl_newEntries"]."<br>";
					$msg .= "Host Dependency :".($nbr["hd"] ? $nbr["hd"] : "0")." ".$lang["upl_newEntries"]."<br>";
					$msg .= "Service :".($nbr["sv"] ? $nbr["sv"] : "0")." ".$lang["upl_newEntries"]."<br>";
					$msg .= "Service Dependency :".($nbr["svd"] ? $nbr["svd"] : "0")." ".$lang["upl_newEntries"]."<br>";
					$msg .= "Service Group :".($nbr["sg"] ? $nbr["sg"] : "0")." ".$lang["upl_newEntries"]."<br>";
					$msg .= "Service Group Dependency :".($nbr["sgd"] ? $nbr["sgd"] : "0")." ".$lang["upl_newEntries"]."<br>";
					break;		
			}
		}
		# Delete File Uploaded
		if (is_file($nagiosCFGPath.$fDataz["name"]))
			unlink($nagiosCFGPath.$fDataz["name"]);
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