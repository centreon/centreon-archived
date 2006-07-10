<?
/**
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
	$pagination = "maxViewConfiguration";
	# set limit
	$res =& $pearDB->query("SELECT maxViewConfiguration FROM general_opt LIMIT 1");
	$gopt = array_map("myDecode", $res->fetchRow());		
	!isset ($_GET["limit"]) ? $limit = $gopt["maxViewConfiguration"] : $limit = $_GET["limit"];

	isset ($_GET["num"]) ? $num = $_GET["num"] : $num = 0;
	isset ($_GET["search"]) ? $search = $_GET["search"] : $search = NULL;
	if ($search)
		$res = & $pearDB->query("SELECT COUNT(*) FROM command WHERE command_name LIKE '%".htmlentities($search, ENT_QUOTES)."%'");
	else if ($type)
		$res = & $pearDB->query("SELECT COUNT(*) FROM command WHERE command_type = '".$type."'");
	else
		$res = & $pearDB->query("SELECT COUNT(*) FROM command");
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
	$tpl->assign("headerMenu_type", $lang['cmd_type']);
	$tpl->assign("headerMenu_status", $lang['status']);
	$tpl->assign("headerMenu_options", $lang['options']);
	# end header menu

	#List of elements - Depends on different criteria
	if ($search)
		$rq = "SELECT command_id, command_name, command_line, command_type FROM command WHERE command_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' ORDER BY command_name LIMIT ".$num * $limit.", ".$limit;
	else if ($type)
		$rq = "SELECT command_id, command_name, command_line, command_type FROM command WHERE command_type = '".$type."' ORDER BY command_name LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT command_id, command_name, command_line, command_type FROM command ORDER BY command_name LIMIT ".$num * $limit.", ".$limit;
	$res = & $pearDB->query($rq);
	
	$form = new HTML_QuickForm('form', 'GET', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();
	for ($i = 0; $res->fetchInto($cmd); $i++) {
		$selectedElements =& $form->addElement('checkbox', "select[".$cmd['command_id']."]");	
		$moptions = "<a href='oreon.php?p=".$p."&command_id=".$cmd['command_id']."&o=w&type=".$cmd['command_type']."&search=".$search."'><img src='img/icones/16x16/view.gif' border='0' alt='".$lang['view']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&command_id=".$cmd['command_id']."&o=c&type=".$cmd['command_type']."&search=".$search."'><img src='img/icones/16x16/document_edit.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&command_id=".$cmd['command_id']."&o=d&type=".$cmd['command_type']."&select[".$cmd['command_id']."]=1&num=".$num."&limit=".$limit."&search=".$search."' onclick=\"return confirm('".$lang['confirm_removing']."')\"><img src='img/icones/16x16/delete.gif' border='0' alt='".$lang['delete']."'></a>&nbsp;&nbsp;";
		$moptions .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$moptions .= "<select style='width:35; margin-bottom: 3px;' name='dupNbr[".$cmd['command_id']."]'><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>10</option></select>";
		$cmd["command_line"] = str_replace('#BR#', "\\n", $cmd["command_line"]);
		$cmd["command_line"] = str_replace('#T#', "\\t", $cmd["command_line"]);
		$cmd["command_line"] = str_replace('#R#', "\\r", $cmd["command_line"]);
		$cmd["command_line"] = str_replace('#S#', "/", $cmd["command_line"]);
		$cmd["command_line"] = str_replace('#BS#', "\\", $cmd["command_line"]);
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$cmd["command_name"],
						"RowMenu_link"=>"?p=".$p."&o=w&command_id=".$cmd['command_id']."&type=".$cmd['command_type'],
						"RowMenu_desc"=>substr($cmd["command_line"], 0, 40)."...",
						"RowMenu_type"=>$cmd["command_type"] == 2 ? $lang['cmd_checkShort'] : $lang['cmd_notifShort'],
						"RowMenu_status"=>$lang['enable'],
						"RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";
	}
	$tpl->assign("elemArr", $elemArr);
	#Different messages we put in the template
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));
	

	#
	##Apply a template definition
	#
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listCommand.ihtml");
	
	$tpl = new Smarty();
	$tpl = initSmartyTpl("./", $tpl);
	$tpl->assign('lang', $lang);
	$tpl->display("include/common/legend.ihtml");
?>
