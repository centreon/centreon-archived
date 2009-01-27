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
	
	$path = "./include/options/accessLists/reloadACL/";	
	
	require_once "./include/common/common-Func.php";
	require_once "HTML/QuickForm.php";	
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
		
	if (isset($_GET["o"]) && $_GET["o"] == "r"){		
		$pearDB->query("UPDATE session SET update_acl = '1' WHERE session_id = '".$_GET["session_id"]."'");
		$msg = _("User's ACL is reloaded.");
	}	
	else if (isset($_POST["o"]) && $_POST["o"] == "u") {
		isset($_GET["select"]) ? $sel = $_GET["select"] : $sel = NULL;
		isset($_POST["select"]) ? $sel = $_POST["select"] : $sel;
				

		$query = "UPDATE session SET update_acl = '1' WHERE user_id IN (";
		$i = 0;		
		foreach ($sel as $key => $val) {
			if ($i) 
				$query .= ", ";
			$query .= "'".$key."'";
			$i++;
		}
		if (!$i)
			$query .= "'')";
		else
			$query .= ")";
		
		$pearDB->query($query);
		$msg = _("User's ACL reloaded.");
	}

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$res =& $pearDB->query("SELECT DISTINCT * FROM session");
	$session_data = array();
	$cpt = 0;
	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
	
	while ($r =& $res->fetchRow()){		
		$resUser =& $pearDB->query("SELECT contact_name, contact_admin FROM contact WHERE contact_id = '".$r["user_id"]."'");
		$rU =& $resUser->fetchRow();
		if ($rU["contact_admin"] != "1") {
			$session_data[$cpt] = array();
			if ($cpt % 2)
				$session_data[$cpt]["class"] = "list_one";
			else
				$session_data[$cpt]["class"] = "list_two";			
			$session_data[$cpt]["user_id"] = $r["user_id"];
			$session_data[$cpt]["user_alias"] = $rU["contact_name"];
			$session_data[$cpt]["admin"] = $rU["contact_admin"];
			$resCP =& $pearDB->query("SELECT topology_name, topology_icone, topology_page, topology_url_opt FROM topology WHERE topology_page = '".$r["current_page"]."'");
			$rCP =& $resCP->fetchRow();
			$session_data[$cpt]["ip_address"] = $r["ip_address"];
			$session_data[$cpt]["current_page"] = $r["current_page"].$rCP["topology_url_opt"];
			$session_data[$cpt]["topology_name"] = _($rCP["topology_name"]);
			$session_data[$cpt]["actions"] = "<a href='./main.php?p=$p&o=r&session_id=".$r["session_id"]."'><img src='./img/icones/16x16/refresh.gif' border='0' alt='"._("Reload ACL")."' title='"._("Reload ACL")."'></a>";
			$selectedElements =& $form->addElement('checkbox', "select[".$r['user_id']."]");
			$session_data[$cpt]["checkbox"] = $selectedElements->toHtml();
			$cpt++;
		}
	}
	if (isset($msg))
		$tpl->assign("msg", $msg);
		
	$tpl->assign("session_data", $session_data);
	$tpl->assign("wi_user", _("Connectd users"));
	$tpl->assign("wi_where", _("Position"));	
	$tpl->assign("actions", _("Reload ACL"));
	$tpl->assign("distant_location", _("IP Address"));
	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</script>
	<?php
	$attrs = array(
			'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1) {" .
				" 	setO(this.form.elements['o1'].value); submit();}");
    $form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "u"=>_("Reload ACL")), $attrs);
	$form->setDefaults(array('o1' => NULL));
	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);

	$attrs = array(
			'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1) {" .
				" 	setO(this.form.elements['o2'].value); submit();}");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "u"=>_("Reload ACL")), $attrs);
	$form->setDefaults(array('o2' => NULL));
	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('p', $p);
	$tpl->display("reloadACL.ihtml");
?>