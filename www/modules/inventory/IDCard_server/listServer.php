<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called  Oreon Inventory  is developped by Merethis company for Lafarge Group,
under the direction of Jean Baptiste Sarrodie <jean-baptiste@sarrodie.org>

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
	if (!isset ($oreon))
		exit ();
	$pagination = "maxViewConfiguration";

	# LCA
	$lcaHostByName = getLcaHostByName($pearDB);
	$lcaHostByID = getLcaHostByID($pearDB);
	$lcaHoststr = getLCAHostStr($lcaHostByID["LcaHost"]);
	$lcaHostGroupstr = getLCAHGStr($lcaHostByID["LcaHostGroup"]);
	$isRestreint = HadUserLca($pearDB);

	# set limit & num
	$res =& $pearDB->query("SELECT maxViewConfiguration FROM general_opt LIMIT 1");
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	$gopt = array_map("myDecode", $res->fetchRow());

	!isset ($_GET["limit"]) ? $limit = $gopt["maxViewConfiguration"] : $limit = $_GET["limit"];
	!isset($_GET["num"]) ? $num = 0 : $num = $_GET["num"];
	isset ($_GET["search"]) ? $search = $_GET["search"] : $search = NULL;

	if ($search)
		$rq = "SELECT COUNT(*) FROM host h, inventory_index ii WHERE h.host_id = ii.host_id AND ii.type_ressources IS NULL AND" .
			  " h.host_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' AND h.host_id IN (".$oreon->user->lcaHStr.") " .
			  " AND host_register = '1'";
	else {
		if ($oreon->user->admin || !$isRestreint)
				$rq = "SELECT COUNT(*) FROM host h, inventory_index ii WHERE h.host_id = ii.host_id AND ii.type_ressources IS NULL AND host_register = '1'";
		else
				$rq = "SELECT COUNT(*) FROM host h, inventory_index ii WHERE h.host_id = ii.host_id AND ii.type_ressources IS NULL AND h.host_id IN (".$lcaHostByID["LcaHost"].") AND host_register = '1'";
	}

	$res =& $pearDB->query($rq);
	if (PEAR::isError($res))
		print "Mysql Error : ".$res->getMessage();
	$tmp = & $res->fetchRow();
	$rows = $tmp["COUNT(*)"];

	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	# end quickSearch form

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
	if ($search)
		$rq = "SELECT ii.*, h.host_id, h.host_name, h.host_alias, h.host_address, h.host_activate FROM host h, inventory_index ii WHERE h.host_id = ii.host_id AND ii.type_ressources IS NULL AND" .
			  " h.host_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' AND h.host_id IN (".$oreon->user->lcaHStr.") " .
			  " AND host_register = '1' ORDER BY h.host_name LIMIT ".$num * $limit.", ".$limit;
	else {
		if ($oreon->user->admin || !$isRestreint)
			$rq = 	"SELECT ii.*, h.host_id, h.host_name, h.host_alias, h.host_address, h.host_activate FROM host h, inventory_index ii WHERE h.host_id = ii.host_id AND ii.type_ressources IS NULL AND" .
					" host_register = '1' " .
					" ORDER BY h.host_name LIMIT ".$num * $limit.", ".$limit;
		else
			$rq = 	"SELECT ii.*, h.host_id, h.host_name, h.host_alias, h.host_address, h.host_activate FROM host h, inventory_index ii WHERE h.host_id = ii.host_id AND ii.type_ressources IS NULL AND" .
					" h.host_id IN (".$lcaHoststr.") AND host_register = '1' " .
					" ORDER BY h.host_name LIMIT ".$num * $limit.", ".$limit;
	}
	$res = & $pearDB->query($rq);
	
	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
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
	#Different messages we put in the template
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));
	
	#form select host
	$req = "SELECT id, alias FROM inventory_manufacturer ";
	$res = & $pearDB->query($req);

	$option = array();
	while ($res->fetchInto($const)) 
    	$option[$const['id']] = $const['alias'];
    $form->addElement('select', 'select_manufacturer', $lang['s_manufacturer'], $option);
	
	#
	##Apply a template definition
	#
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('p', $p);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("IDCard_server/listServer.ihtml");
	
?>