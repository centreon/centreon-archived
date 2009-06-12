<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
 */

	if (!isset($oreon))
		exit();
	
	$path = "./include/options/accessLists/reloadACL/";	
	
	
	require_once "./include/common/common-Func.php";
	require_once "./class/centreonMsg.class.php";
	require_once "HTML/QuickForm.php";	
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
		
	if (isset($_GET["o"]) && $_GET["o"] == "r"){		
		$pearDB->query("UPDATE session SET update_acl = '1' WHERE session_id = '".$_GET["session_id"]."'");
		$msg = new CentreonMsg();
		$msg->setTextStyle("bold");
		$msg->setText(_("ACL reloaded"));
		$msg->setTimeOut("3");		
	}	
	else if (isset($_POST["o"]) && $_POST["o"] == "u") {
		isset($_GET["select"]) ? $sel = $_GET["select"] : $sel = NULL;
		isset($_POST["select"]) ? $sel = $_POST["select"] : $sel;
				

		$query = "UPDATE session SET update_acl = '1' WHERE user_id IN (";
		$i = 0;		
		if (isset($sel))
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
		$msg->setTextStyle("bold");
		$msg->setText(_("ACL reloaded"));
		$msg->setTimeOut("3");
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
	$tpl->assign("wi_user", _("Connected users"));
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
				" 	setO(this.form.elements['o1'].value); submit();} this.form.elements['o1'].selectedIndex = 0");
    $form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "u"=>_("Reload ACL")), $attrs);
	
	
	$attrs = array(
			'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1) {" .
				" 	setO(this.form.elements['o2'].value); submit();} this.form.elements['o1'].selectedIndex = 0");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "u"=>_("Reload ACL")), $attrs);
	
	
	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);	
	
	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('p', $p);
	$tpl->display("reloadACL.ihtml");
?>