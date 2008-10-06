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

	if (!isset ($oreon))
		exit ();
		
	isset($_POST["cmd"]) ? $cmd = $_POST["cmd"] : $cmd = 0;
	isset($_POST["cmd2"]) ? $cmd2 = $_POST["cmd2"] : $cmd2 = 0;

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir
	$path = "./include/options/configurationChangelog/";
	
	#PHP functions 
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	require_once("./DBOdsConnect.php");
	
	$listAction = array();
	$listAction = listAction(NULL);
	$listModification = array();
	$listModification = listmodification(NULL);
	
	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	$tpl->assign("date", _("Date"));
	$tpl->assign("type", _("Type"));
	$tpl->assign("object_id", _("Object ID"));
	$tpl->assign("object_name", _("Object Name"));
	$tpl->assign("action", _("Action"));
	$tpl->assign("contact_name", _("Contact Name"));
	$tpl->assign("field_name", _("Field Name"));
	$tpl->assign("field_value", _("Field Value"));
	$tpl->assign("field_value", _("Field Value"));
	$tpl->assign("before", _("Before"));
	$tpl->assign("after", _("After"));
	$tpl->assign("logs", _("Logs"));
		
	$tpl->assign("action", $listAction);
	$tpl->assign("modification", $listModification);

	$objects_list = array();
	$objects_List = listObjecttype();

	$object_type_tab[0] = _("Please select an object");
	$object_type_tab[1] = "command";
	$object_type_tab[2] = "timeperiod";
	$object_type_tab[3] = "contact";
	$object_type_tab[4] = "contactgroup";
	$object_type_tab[5] = "host";
	$object_type_tab[6] = "hostgroup";
	$object_type_tab[7] = "service";
	$object_type_tab[8] = "servicegroup";
	$object_type_tab[9] = "snmp traps";
	$object_type_tab[10] = "escalations";
	$object_type_tab[11] = "host dependency";
	$object_type_tab[12] = "hostgroup dependency";
	$object_type_tab[13] = "service dependency";
	$object_type_tab[14] = "servicegroup dependency";

	//$objects_list[] = _("Hosts : Disable Check");
	
	?>
	<script type="text/javascript">
	function setO1(_i) {
		document.forms['form'].elements['cmd'].value = _i;
		document.forms['form'].elements['o1'].selectedIndex = _i;
	}
	
	function setO2(_i) {
		document.forms['form'].elements['cmd2'].value = _i;
		document.forms['form'].elements['o2'].selectedIndex = _i;
	}
	
	</script>
	<?php
	
	if ($cmd) {
		$DBRESULT = $pearDB->query("SELECT DISTINCT object_name, object_id FROM log_action WHERE object_type='".$object_type_tab[$cmd]."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$res =& $DBRESULT->fetchRow();	
	}
	
	$attrs = array(	'onchange'=>"javascript: setO1(this.form.elements['o1'].value); submit();");
    $form->addElement('select', 'o1', NULL, $object_type_tab, $attrs);
	$form->setDefaults(array('o1' => NULL));
	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);

	$objects = array();
	$objects = listObjectname(NULL);
	$attrs = array(	'onchange'=>"javascript: setO2(this.form.elements['o2'].value); submit();");
    $form->addElement('select', 'o2', NULL, $res, $attrs);
	$form->setDefaults(array('o2' => NULL));
	$o1 =& $form->getElement('o2');
	$o1->setValue(NULL);
	
	$form->addElement('hidden', 'cmd', $cmd);
	$form->addElement('hidden', 'cmd2', $cmd2);	
	/*
	 * Apply a template definition
	 */
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("viewLogs.ihtml");
?>