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
 		
	/*
	 * Debug Flag
	 */
	$debug = 0;
	
	/*
	 * Database retrieve information for Manufacturer
	 */
	
	function myDecodeMib($arg)	{
		$arg = html_entity_decode($arg, ENT_QUOTES);
		return($arg);
	}

	/*
	 * Set base value
	 */
	$mnftr = array(NULL => NULL);
	$DBRESULT =& $pearDB->query("SELECT `id`, `alias` FROM `traps_vendor` ORDER BY `alias`");
	while ($rmnftr =& $DBRESULT->fetchRow())
		$mnftr[$rmnftr["id"]] = $rmnftr["alias"];
	$DBRESULT->free();

	/*
	 * Init Formulary
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Load a MIB"));

	/*
	 * Manufacturer information
	 */
	$form->addElement('select', 'mnftr', _("Vendor Name"), $mnftr);
	$form->addElement('file', 'filename', _("File (.mib)"));

	/*
	 * Formulary Rules
	 */
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('mnftr', _("Compulsory Name"), 'required');
	$form->addRule('filename', _("Compulsory Name"), 'required');
	$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/*
	 * Just watch a Command information
	 */
	$subA =& $form->addElement('submit', 'submit', _("Export"));
	$form->addElement('header', 'status',_("Status"));
	$valid = false;
	$msg = NULL;
	$stdout = NULL;
	if ($form->validate())	{
	
		$ret = $form->getSubmitValues();
	
		$fileObj =& $form->getElement('filename');
	
		if ($fileObj->isUploadedFile()) {
			/*
			 * Upload File
			 */
			$fileObj->moveUploadedFile("/tmp/");
			$values = $fileObj->getValue();
			$stdout = shell_exec("export LD_LIBRARY_PATH=".$oreon->optGen["perl_library_path"]." && export MIBS=ALL && ".$oreon->optGen["snmpttconvertmib_path_bin"]." --in=/tmp/".$values["name"]." --out=/tmp/".$values["name"].".conf 2>&1");
			if ($debug) 
				print ("export LD_LIBRARY_PATH=".$oreon->optGen["perl_library_path"]." && export MIBS=ALL && ".$oreon->optGen["snmpttconvertmib_path_bin"]." --in=/tmp/".$values["name"]." --out=/tmp/".$values["name"].".conf 2>&1");
			
			$msg .= "<br />".str_replace ("\n", "<br />", $stdout);
			$msg .= "<br />Moving traps in DataBase...";	
			
			if ($debug) 
				print("@CENTPLUGINSTRAPS_BINDIR@/centFillTrapDB -f /tmp/".$values["name"].".conf -m ".htmlentities($ret["mnftr"], ENT_QUOTES)." 2>&1");
			
			$stdout = shell_exec("@CENTPLUGINSTRAPS_BINDIR@/centFillTrapDB -f /tmp/".$values["name"].".conf -m ".htmlentities($ret["mnftr"], ENT_QUOTES)." 2>&1");
			shell_exec("rm /tmp/".$values["name"].".conf /tmp/".$values["name"]);
			$msg .= "<br />".str_replace ("\n", "<br />", $stdout);
			$msg .= "<br />Generate Traps configuration files from Nagios configuration form!";
			if ($msg)
				$tpl->assign('msg', $msg);
		}	
		$valid = true;
	}
	
	/*
	 * Apply a template definition
	 */
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("formMibs.ihtml");
?>