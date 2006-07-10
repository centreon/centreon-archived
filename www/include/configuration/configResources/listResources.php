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
	$pagination = "maxViewConfiguration";	# set limit
	$res =& $pearDB->query("SELECT maxViewConfiguration FROM general_opt LIMIT 1");
	$gopt = array_map("myDecode", $res->fetchRow());		
	!isset ($_GET["limit"]) ? $limit = $gopt["maxViewConfiguration"] : $limit = $_GET["limit"];


	isset ($_GET["num"]) ? $num = $_GET["num"] : $num = 0;
	isset ($_GET["search"]) ? $search = $_GET["search"] : $search = NULL;
	if ($search)
		$res = & $pearDB->query("SELECT COUNT(*) FROM cfg_resource WHERE resource_name LIKE '%".htmlentities($search, ENT_QUOTES)."%'");
	else
		$res = & $pearDB->query("SELECT COUNT(*) FROM cfg_resource");
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
	$tpl->assign("headerMenu_status", $lang['status']);
	$tpl->assign("headerMenu_options", $lang['options']);
	# end header menu
	# Timeperiod list
	if ($search)
		$rq = "SELECT resource_id, resource_name, resource_line, resource_activate FROM cfg_resource WHERE resource_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' ORDER BY resource_name LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT resource_id, resource_name, resource_line, resource_activate FROM cfg_resource ORDER BY resource_name LIMIT ".$num * $limit.", ".$limit;
	$res = & $pearDB->query($rq);
	
	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();	for ($i = 0; $res->fetchInto($resource); $i++) {
		$selectedElements =& $form->addElement('checkbox', "select[".$resource['resource_id']."]");	
		$moptions = "<a href='oreon.php?p=".$p."&resource_id=".$resource['resource_id']."&o=w&&search=".$search."'><img src='img/icones/16x16/view.gif' border='0' alt='".$lang['view']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&resource_id=".$resource['resource_id']."&o=c&search=".$search."'><img src='img/icones/16x16/document_edit.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&resource_id=".$resource['resource_id']."&o=d&select[".$resource['resource_id']."]=1&num=".$num."&limit=".$limit."&search=".$search."' onclick=\"return confirm('".$lang['confirm_removing']."')\"><img src='img/icones/16x16/delete.gif' border='0' alt='".$lang['delete']."'></a>&nbsp;&nbsp;";
		if ($resource["resource_activate"])
			$moptions .= "<a href='oreon.php?p=".$p."&resource_id=".$resource['resource_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='".$lang['disable']."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='oreon.php?p=".$p."&resource_id=".$resource['resource_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='".$lang['enable']."'></a>&nbsp;&nbsp;";
		$moptions .= "<select style='width:35; margin-bottom: 3px;' name='dupNbr[".$resource['resource_id']."]'><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>10</option></select>";
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$resource["resource_name"],
						"RowMenu_link"=>"?p=".$p."&o=w&resource_id=".$resource['resource_id'],
						"RowMenu_desc"=>substr($resource["resource_line"], 0, 40),
						"RowMenu_status"=>$resource["resource_activate"] ? $lang['enable'] :  $lang['disable'],
						"RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";	}
	$tpl->assign("elemArr", $elemArr);
	#Different messages we put in the template
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));
	

	#
	##Apply a template definition
	#
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listResources.ihtml");

	$tpl = new Smarty();
	$tpl = initSmartyTpl("./", $tpl);
	$tpl->assign('lang', $lang);
	$tpl->display("include/common/legend.ihtml");	
?>