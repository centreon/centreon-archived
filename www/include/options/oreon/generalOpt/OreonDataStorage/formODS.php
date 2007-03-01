<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
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

	if (!isset($oreon))
		exit();

	require_once("./DBOdsConnect.php");
	
	if (isset($_POST["o"]) && $_POST["o"])
		$o = $_POST["o"];

	$DBRESULT =& $pearDBO->query("SELECT * FROM config LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			
	# Set base value
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());

	## Database retrieve information for differents elements list we need on the page
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element

	$attrsText 		= array("size"=>"40");
	$attrsText2		= array("size"=>"5");
	$attrsAdvSelect = null;

	## Form begin
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', $lang["genOpt_change"]);
	
	$gopt["purge_interval"] *= $gopt["sleep_time"];
	
	## Oreon information
	$form->addElement('header', 'oreon', $lang['genOpt_oreon']);
	$form->addElement('text', 'RRDdatabase_path', $lang["ods_rrd_path"], $attrsText);
	$form->addElement('text', 'len_storage_rrd', $lang["ods_len_storage_rrd"], $attrsText);
	$form->addElement('checkbox', 'autodelete_rrd_db', $lang['ods_autodelete_rrd_db']);
	$form->addElement('text', 'sleep_time', $lang["ods_sleep_time"], $attrsText2);
	$form->addElement('text', 'purge_interval', $lang["ods_purge_interval"], $attrsText2);
	
	$storage_type = array(0 => "RRDTool", 2 => "RRDTool & MySQL");	
	$form->addElement('select', 'storage_type', $lang['ods_storage_type'], $storage_type);
	
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	## Form Rules
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}
	$form->applyFilter('_ALL_', 'trim');
	$form->applyFilter('RRDdatabase_path', 'slash');
	
	//$form->registerRule('is_valid_path', 'callback', 'is_valid_path');
	//form->addRule('RRDdatabase_path', $lang['ErrWrPath'], 'is_valid_path');
	##End of form definition

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path.'OreonDataStorage/', $tpl);
	$form->setDefaults($gopt);
	
	$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
	$form->addElement('reset', 'reset', $lang["reset"]);
    $valid = false;
    
	if ($form->validate())	{
		
		# Update in DB
		updateODSConfigData();
		# Update in Oreon Object
		
		$oreon->optGen = array();
		$DBRESULT2 =& $pearDBO->query("SELECT * FROM `config` LIMIT 1");
		if (PEAR::isError($DBRESULT2))
			print ("DB error : ".$DBRESULT2->getDebugInfo());
		$DBRESULT2->fetchInto($oreon->optGen);
		
		$o = "ods";
	}
	if (!$form->validate() && isset($_POST["gopt_id"]))
	    print("<div class='msg' align='center'>".$lang["quickFormError"]."</div>");

	$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."'"));

	## Apply a template definition
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('lang', $lang);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->display("formODS.ihtml");
?>