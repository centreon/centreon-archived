<?
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

	if (!isset($oreon))
		exit();
	
	include("./include/common/autoNumLimit.php");	
	
	require_once './class/other.class.php';
	include_once("./include/monitoring/common-Func.php");
	
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the option dir
	$path = "./include/options/ods/";
	
	#PHP functions
	require_once("./include/options/oreon/generalOpt/DB-Func.php");
	require_once("./include/common/common-Func.php");
	require_once("./DBOdsConnect.php");
	
	if (isset($_GET["o"]) && $_GET["o"] == "d" && isset($_GET["id"])){
		$DBRESULT =& $pearDBO->query("UPDATE index_data SET `trashed` = '1' WHERE id = '".$_GET["id"]."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	}
	
	if (isset($_GET["o"]) && $_GET["o"] == "rb" && isset($_GET["id"])){
		$DBRESULT =& $pearDBO->query("UPDATE index_data SET `must_be_rebuild` = '1' WHERE id = '".$_GET["id"]."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	}
	
	$DBRESULT =& $pearDBO->query("SELECT COUNT(*) FROM index_data ORDER BY host_name, service_description");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$tmp =& $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];
			
	$tab_class = array("0" => "list_one", "1" => "list_two");
	$storage_type = array(0 => "RRDTool", 2 => "RRDTool & MySQL");	
	$yesOrNo = array(0 => "No", 1 => "Yes", 2 => "Rebuilding");	
	
	$DBRESULT =& $pearDBO->query("SELECT * FROM index_data ORDER BY host_name, service_description LIMIT ".$num * $limit.", $limit");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$data = array();
	for ($i = 0;$DBRESULT->fetchInto($index_data);$i++){
		$DBRESULT2 =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$index_data["id"]."'");
		if (PEAR::isError($DBRESULT2))
			print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
		$metric = "";
		for ($im = 0;$DBRESULT2->fetchInto($metrics);$im++){
			if ($im)
				$metric .= " - ";
			$metric .= $metrics["metric_name"];
			if (isset($metrics["unit_name"]) && $metrics["unit_name"])
				$metric .= " (".$metrics["unit_name"].") ";
		}
		$index_data["metrics_name"] = $metric;

		$index_data["service_description"] = str_replace("#S#", "/", $index_data["service_description"]);
		$index_data["service_description"] = str_replace("#BS#", "\\", $index_data["service_description"]);

		$index_data["metrics_name"] = str_replace("#S#", "/", $index_data["metrics_name"]);
		$index_data["metrics_name"] = str_replace("#BS#", "\\", $index_data["metrics_name"]);
		
		$index_data["storage_type"] = $storage_type[$index_data["storage_type"]];
		$index_data["must_be_rebuild"] = $yesOrNo[$index_data["must_be_rebuild"]];
		
		$index_data["class"] = $tab_class[$i % 2];
		$data[$i] = $index_data;
	}

	include("./include/common/checkPagination.php");

	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);	

	#
	##Toolbar select $lang["lgd_more_actions"]
	#
	?>
	<SCRIPT LANGUAGE="JavaScript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3 || this.form.elements['o1'].selectedIndex == 4 ||this.form.elements['o1'].selectedIndex == 5){" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
    $form->addElement('select', 'o1', NULL, array(NULL=>$lang["lgd_more_actions"], "m"=>$lang['dup'], "d"=>$lang['delete'], "mc"=>$lang['mchange'], "ms"=>$lang['m_mon_enable'], "mu"=>$lang['m_mon_disable']), $attrs1);
	
	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3 || this.form.elements['o2'].selectedIndex == 4 ||this.form.elements['o2'].selectedIndex == 5){" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"this.form.elements['o2'].selectedIndex = 0");
    $form->addElement('select', 'o2', NULL, array(NULL=>$lang["lgd_more_actions"], "m"=>$lang['dup'], "d"=>$lang['delete'], "mc"=>$lang['mchange'], "ms"=>$lang['m_mon_enable'], "mu"=>$lang['m_mon_disable']), $attrs2);

	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);

	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	
	$tpl->assign('limit', $limit);

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$tpl->assign("p", $p);
	$tpl->assign('o', $o);
	$tpl->assign("num", $num);
	$tpl->assign("limit", $limit);
	

    $tpl->assign("data", $data);
	$tpl->display("manageData.ihtml");
?>