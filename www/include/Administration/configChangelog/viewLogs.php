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

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	/*
	 * Path to the configuration dir
	 */
	$path = "./include/Administration/configChangelog/";
	
	/*
	 * PHP functions
	 */ 
	require_once "./include/common/common-Func.php";
	require_once("./class/centreonDB.class.php");
	
	$pearDBO = new CentreonDB("centstorage");
	
	if ($cmd2) {
		$listAction = array();
		$listAction = $oreon->CentreonLogAction->listAction($cmd2);
		$listModification = array();
		$listModification = $oreon->CentreonLogAction->listmodification($cmd2);
	}
	
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
	$tpl->assign("before", _("Before"));
	$tpl->assign("after", _("After"));
	$tpl->assign("logs", _("Logs for "));
	$tpl->assign("objTypeLabel", _("Object type : "));
	$tpl->assign("objNameLabel", _("Object name : "));
	$tpl->assign("noModifLabel", _("No modification was made."));
	
	if (isset($listAction))
		$tpl->assign("action", $listAction);
	if (isset($listModification))
		$tpl->assign("modification", $listModification);
	
	isset($cmd2) && $cmd2 ? $display_flag = 1 : $display_flag = 0;
	$tpl->assign("display_flag", $display_flag);

	$objects_type_tab = array();
	$objects_type_tab = $oreon->CentreonLogAction->listObjecttype();
	
	?>
	<script type="text/javascript">
	function setO1(_i) {
		document.forms['form'].elements['cmd'].value = _i;
		document.forms['form'].elements['o1'].selectedIndex = _i;
		document.forms['form'].elements['cmd2'].value = 0;
	}
	
	function setO2(_i) {
		document.forms['form'].elements['cmd2'].value = _i;
	}
	</script>
	<?php
	
	if ($cmd) {
		$DBRESULT = $pearDBO->query("SELECT DISTINCT object_name, object_id FROM log_action WHERE object_type='".$objects_type_tab[$cmd]."' ORDER BY object_id");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$objNameTab[0] = _("Please select an object name");
		while ($res =& $DBRESULT->fetchRow()) {
			if ($res['object_id']) {
				$res['object_name'] = str_replace('#S#', "/", $res["object_name"]);
				$res['object_name'] = str_replace('#BS#', "\\", $res["object_name"]);
				$objNameTab[$res['object_id']] = $res['object_name']." (id:".$res['object_id'].")";
			}
		}
	}
	
	if (isset($cmd2) && $cmd2 && isset($objNameTab))
		$tpl->assign("objName", $objNameTab[$cmd2]);
	
	$attrs = array(	'onchange'=>"javascript: setO1(this.form.elements['o1'].value); submit();");
    $form->addElement('select', 'o1', NULL, $objects_type_tab, $attrs);
	$form->setDefaults(array('o1' => NULL));
	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);

	if (isset($objNameTab)) {
		$attrs = array(	'onchange'=>"javascript: setO2(this.form.elements['o2'].value); submit();");
	    $form->addElement('select', 'o2', NULL, $objNameTab, $attrs);
		$form->setDefaults(array('o2' => NULL));
		$o2 =& $form->getElement('o2');
		$o2->setValue(NULL);
	}
		
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