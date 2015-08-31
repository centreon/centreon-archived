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

 	if (!isset($oreon))
 		exit();
        
	/*
	 * Debug Flag
	 */
	$debug = 0;
        $max_characters = 20000;

	/*
	 * Database retrieve information for Manufacturer
	 */

	function myDecodeMib($arg)	{
		$arg = html_entity_decode($arg, ENT_QUOTES, "UTF-8");
		return($arg);
	}

	/*
	 * Set base value
	 */
	$mnftr = array(NULL => NULL);
	$DBRESULT = $pearDB->query("SELECT `id`, `alias` FROM `traps_vendor` ORDER BY `alias`");
	while ($rmnftr = $DBRESULT->fetchRow())
		$mnftr[$rmnftr["id"]] = $rmnftr["alias"];
	$DBRESULT->free();

	/*
	 * Init Formulary
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Import SNMP traps from MIB file"));

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
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"' );
	# prepare help texts
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) {
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);

	/*
	 * Just watch a Command information
	 */
	$subA = $form->addElement('submit', 'submit', _("Import"));
	$form->addElement('header', 'status',_("Status"));
	$valid = false;
	$msg = NULL;
	$stdout = NULL;
	if ($form->validate())	{

		$ret = $form->getSubmitValues();

		$fileObj = $form->getElement('filename');

		if ($fileObj->isUploadedFile()) {
			/*
			 * Upload File
			 */
			$values = $fileObj->getValue();
			$msg .= str_replace ("\n", "<br />", $stdout);
			$msg .= "<br />Moving traps in DataBase...";

			if ($debug)
				print("@CENTREONTRAPD_BINDIR@/centFillTrapDB -f '".$values["tmp_name"]."' -m ".htmlentities($ret["mnftr"], ENT_QUOTES, "UTF-8")." --severity=info 2>&1");

			$stdout = shell_exec("@CENTREONTRAPD_BINDIR@/centFillTrapDB -f '".$values["tmp_name"]."' -m ".htmlentities($ret["mnftr"], ENT_QUOTES, "UTF-8")." --severity=info 2>&1");
			unlink($values['tmp_name']);
			$msg .= "<br />".str_replace ("\n", "<br />", $stdout);
			$msg .= "<br />Generate Traps configuration files from Monitoring Engine configuration form!";
			if ($msg) {
                            if (strlen($msg) > $max_characters) {
                                $msg = substr($msg, 0, $max_characters)."...".
                                        sprintf(_("Message truncated (exceeded %s characters)"), $max_characters);
                            }
                            $tpl->assign('msg', $msg);
                        }
		}
		$valid = true;
	}

	/*
	 * Apply a template definition
	 */
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("formMibs.ihtml");
?>
