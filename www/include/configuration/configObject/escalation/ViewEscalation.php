<?/**
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

if (!isset($oreon))
		exit();
	#
	## Database retrieve information for Host
	#
	
	global $path;
	$path = "./include/configuration/configObject/escalation/";
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$hostgroup_ary = array(NULL=>NULL);
	$cmd = "SELECT hg_id, hg_name ".
			"FROM hostgroup";
	$res_host = $pearDB->query($cmd);
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	while($res_host->fetchInto($hostgroup))
		$hostgroup_ary[$hostgroup["hg_id"]] = $hostgroup["hg_name"];
	$res_host->free();
	
	$hgs = array(NULL=>NULL);
	if (isset($_POST["hostgroup_escalation"]) && $_POST["hostgroup_escalation"] != NULL){
		$cmd = "SELECT h.host_id, h.host_name ".
				"FROM host h, hostgroup_relation hr ".
				"WHERE h.host_id = hr.host_host_id ".
				"AND hr.hostgroup_hg_id = '".$_POST["hostgroup_escalation"]."' ";
		$res = $pearDB->query($cmd);
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		while($res->fetchInto($hg))
			$hgs[$hg["host_id"]] = $hg["host_name"];
		$res->free();
	}
	else {
		$res = $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1' ORDER by host_name");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		while($res->fetchInto($hg))
			$hgs[$hg["host_id"]] = $hg["host_name"];
		$res->free();
	}

	$svcs = array();
	if (isset($_POST["hostgroup_escalation"])){
		$tpl->assign('hostgroup_id', $_POST["hostgroup_escalation"]);
	}
	if (isset($_POST["host_escalation"])){
		$svcs = getMyHostServices($_POST["host_escalation"]);
		$svcs[NULL]= NULL;
		$tpl->assign('host_id', $_POST["host_escalation"]);
		if (isset($_POST["service_escalation"]))
			$tpl->assign('service_id', $_POST["service_escalation"]);
	}

	$attrsText 		= array("size"=>"30");
	$attrsText2		= array("size"=>"6");
	$attrsAdvSelect = array("style" => "width: 200px; height: 200px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	$form->addElement('header', 'title', $lang["m_header_gantt"]);
	$form->addElement('select', 'hostgroup_escalation', $lang['esc_sort4'], $hostgroup_ary, array("onChange" =>"this.form.submit();"));
	$form->addElement('select', 'host_escalation', $lang['esc_sort2'], $hgs, array("onChange" =>"this.form.submit();"));
	$form->addElement('select', 'service_escalation', $lang['esc_sort3'], $svcs, array("onChange" =>"this.form.submit();"));
	$valid = false;
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->display("ViewEscalation.ihtml");
?>


