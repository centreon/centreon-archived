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

	function display_copying_file($filename = NULL, $status){
		if (!isset($filename))
			return ;
		$str = "<tr><td>- ".$filename."</td>";
		$str .= "<td>".$status."</td></tr>";
		return $str;
	}

	/*
	 * Form begin
	 */
	$attrSelect = array("style" => "width: 220px;");

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	/*
	 * Init Header for tables in template
	 */
	$form->addElement('header', 'title', _("Snmptrapd Configuration"));
	$form->addElement('header', 'opt', _("Export Options"));
	$form->addElement('header', 'result', _("Actions"));	    
	
	/*
	 * Add checkbox for enable restart
	 */
	$form->addElement('checkbox', 'restart', _("Generate configuration files for SNMPTT"));

	/*
	 * Set checkbox checked.
	 */
	$form->setDefaults(array('restart' => '1'));
	
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$sub =& $form->addElement('submit', 'submit', _("Export"));
	$msg = NULL;
	$stdout = NULL;
	if ($form->validate())	{
		$ret = $form->getSubmitValues();
		if (isset($ret["restart"]["restart"]) && $ret["restart"]["restart"])	{
			$stdout = shell_exec("$centreon_path/bin/centGenSnmpttConfFile 2>&1");
			$msg .= "<br>".str_replace ("\n", "<br>", $stdout)."<br>";
		}
	}

	$form->addElement('header', 'status', _("Status"));
	if (isset($msg) && $msg)
		$tpl->assign('msg', $msg);
	
	/*
	 * Apply a template definition
	 */
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("formGenerateTraps.ihtml");
?>