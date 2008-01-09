<?php
/**
Centreon is developped with GPL Licence 2.0 :
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
	
	# LCA
	if ($isRestreint){
		$lcaHostByName = getLcaHostByName($pearDB);
		$lcaHostByID = getLcaHostByID($pearDB);
		$lcaHoststr = getLCAHostStr($lcaHostByID["LcaHost"]);
		$lcaHostGroupstr = getLCAHGStr($lcaHostByID["LcaHostGroup"]);
	}
	
	if (isset ($_GET["search"]))
		$search = $_GET["search"];
	else if (isset($oreon->historySearch[$url]))
		$search = $oreon->historySearch[$url];
	else 
		$search = NULL;

	if (isset($search) && $search){
		if ($isRestreint)
			$rq = "SELECT COUNT(*) FROM host h, inventory_index ii WHERE h.host_id = ii.host_id AND ii.type_ressources IS NULL AND" .
				  " h.host_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' AND h.host_id IN (".$lcaHoststr.") " .
				  " AND host_register = '1'";
		else
			$rq = "SELECT COUNT(*) FROM host h, inventory_index ii WHERE h.host_id = ii.host_id AND ii.type_ressources IS NULL AND" .
				  " h.host_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' AND host_register = '1'";
	} else {
		if (!$isRestreint)
				$rq = "SELECT COUNT(*) FROM host h, inventory_index ii WHERE h.host_id = ii.host_id AND ii.type_ressources IS NULL AND host_register = '1'";
		else
				$rq = "SELECT COUNT(*) FROM host h, inventory_index ii WHERE h.host_id = ii.host_id AND ii.type_ressources IS NULL AND h.host_id IN (".$lcaHoststr.") AND host_register = '1'";
	}

	$res =& $pearDB->query($rq);
	if (PEAR::isError($res))
		print "Mysql Error : ".$res->getMessage();
	$tmp = & $res->fetchRow();
	$rows = $tmp["COUNT(*)"];

	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	# end quickSearch form

	include("./include/common/checkPagination.php");

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", $lang['name']);
	$tpl->assign("headerMenu_desc", $lang['description']);
	$tpl->assign("headerMenu_address", $lang['h_address']);
	$tpl->assign("headerMenu_status", $lang['status']);
	$tpl->assign("headerMenu_type", $lang['s_type']);
	# end header menu

	#Host list
	if (isset($search) && $search){
		if ($isRestreint)
			$rq = "SELECT ii.*, h.host_id, h.host_name, h.host_alias, h.host_address, h.host_activate FROM host h, inventory_index ii WHERE h.host_id = ii.host_id AND ii.type_ressources IS NULL AND" .
				  " h.host_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' AND h.host_id IN (".$lcaHoststr.") " .
				  " AND host_register = '1' ORDER BY h.host_name LIMIT ".$num * $limit.", ".$limit;
		else
			$rq = "SELECT ii.*, h.host_id, h.host_name, h.host_alias, h.host_address, h.host_activate FROM host h, inventory_index ii WHERE h.host_id = ii.host_id AND ii.type_ressources IS NULL AND" .
				  " h.host_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' " .
				  " AND host_register = '1' ORDER BY h.host_name LIMIT ".$num * $limit.", ".$limit;
	} else {
		if (!$isRestreint)
			$rq = 	"SELECT ii.*, h.host_id, h.host_name, h.host_alias, h.host_address, h.host_activate FROM host h, inventory_index ii WHERE h.host_id = ii.host_id AND ii.type_ressources IS NULL AND" .
					" host_register = '1' " .
					" ORDER BY h.host_name LIMIT ".$num * $limit.", ".$limit;
		else
			$rq = 	"SELECT ii.*, h.host_id, h.host_name, h.host_alias, h.host_address, h.host_activate FROM host h, inventory_index ii WHERE h.host_id = ii.host_id AND ii.type_ressources IS NULL AND" .
					" h.host_id IN (".$lcaHoststr.") AND host_register = '1' " .
					" ORDER BY h.host_name LIMIT ".$num * $limit.", ".$limit;
	}

	$res = & $pearDB->query($rq);
	if (PEAR::isError($res))
		print "Mysql Error : ".$res->getMessage();
			
	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl

	$elemArr = array();
	for ($i = 0; $res->fetchInto($host); $i++) {		
		$selectedElements =& $form->addElement('checkbox', "select[".$host['host_id']."]");	
		if (!$host["host_name"])
			$host["host_name"] = getMyHostName($host["host_template_model_htm_id"]);
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$host["host_name"],
						"RowMenu_link"=>"?p=".$p."&o=t&host_id=".$host['host_id']."&search=".$search,
						"RowMenu_desc"=>$host["host_alias"],
						"RowMenu_address"=>$host["host_address"],
						"RowMenu_status"=>$host["host_activate"] ? $lang["enable"] : $lang["disable"],
						"RowMenu_type"=>$lang['s_server']);
		$style != "two" ? $style = "two" : $style = "one";
	}
	$tpl->assign("elemArr", $elemArr);
	$tpl->assign("limit", $limit);
	#Different messages we put in the template
	$tpl->assign('msg', array ("addL" => "?p=".$p."&o=a", "addT" => $lang['add'], "delConfirm" => $lang['confirm_removing']));
	
	#form select host
	$req = "SELECT id, alias FROM inventory_manufacturer ";
	$res = & $pearDB->query($req);

	$option = array();
	while ($res->fetchInto($const)) 
    	$option[$const['id']] = $const['alias'];

	#
	##Toolbar select $lang["lgd_more_actions"]
	#
	?>
	<SCRIPT LANGUAGE="JavaScript">
	function setO(_i) {
		document.forms['form'].elements['select_manufacturer'].value = _i;
		document.forms['form'].elements['o'].value = 'c';
	}
	</SCRIPT>
	<?php

	$attrs1 = array(
		'onchange'=>"javascript: " .
				"setO(this.form.elements['o1'].value);  "
				. "this.form.elements['o1'].selectedIndex = 0;submit();");
    $form->addElement('select', 'o1', NULL, $option, $attrs1);

	$attrs2 = array(
		'onchange'=>"javascript: " .
				"setO(this.form.elements['o2'].value);  "
				. "this.form.elements['o2'].selectedIndex = 0;submit();");

    $form->addElement('select', 'o2', NULL, $option, $attrs2);

//    $form->addElement('select', 'select_manufacturer', $lang['s_manufacturer'], $option);



	##Apply a template definition

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('p', $p);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("IDCard_server/listServer.ihtml");
	
?>