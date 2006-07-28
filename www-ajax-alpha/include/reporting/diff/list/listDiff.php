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
*/	$pagination = "maxViewConfiguration";
	# set limit
	$res =& $pearDB->query("SELECT maxViewConfiguration FROM general_opt LIMIT 1");
	if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
	$gopt = array_map("myDecode", $res->fetchRow());		
	!isset ($_GET["limit"]) ? $limit = $gopt["maxViewConfiguration"] : $limit = $_GET["limit"];

	isset ($_GET["num"]) ? $num = $_GET["num"] : $num = 0;
	isset ($_GET["search"]) ? $search = $_GET["search"] : $search = NULL;
	if ($search){
		$res = & $pearDB->query("SELECT COUNT(*) FROM reporting_diff_list WHERE name LIKE '%".htmlentities($search, ENT_QUOTES)."%'");
		if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
	}
	else{
		$res = & $pearDB->query("SELECT COUNT(*) FROM reporting_diff_list");
		if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
	}
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
	$tpl->assign("headerMenu_nbrMail", $lang["diffListNbrMail"]);
	$tpl->assign("headerMenu_options", $lang['options']);
	# end header menu
	#Contact list
	if ($search)
		$rq = "SELECT @nbr:=(SELECT DISTINCT COUNT(rtelr.rtde_id) FROM reporting_email_list_relation rtelr WHERE rtelr.rtdl_id = rtdl.rtdl_id) AS nbr, rtdl.rtdl_id, rtdl.name, rtdl.description, rtdl.activate FROM reporting_diff_list rtdl WHERE name LIKE '%".htmlentities($search, ENT_QUOTES)."%' ORDER BY  name LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT @nbr:=(SELECT DISTINCT COUNT(rtelr.rtde_id) FROM reporting_email_list_relation rtelr WHERE rtelr.rtdl_id = rtdl.rtdl_id) AS nbr, rtdl.rtdl_id, rtdl.name, rtdl.description, rtdl.activate FROM reporting_diff_list rtdl ORDER BY name LIMIT ".$num * $limit.", ".$limit;
	$res =& $pearDB->query($rq);
	if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
	
	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();
	for ($i = 0; $res->fetchInto($list); $i++) {		
		$selectedElements =& $form->addElement('checkbox', "select[".$list['rtdl_id']."]");	
		$moptions = "<a href='oreon.php?p=".$p."&rtdl_id=".$list['rtdl_id']."&o=w&search=".$search."'><img src='img/icones/16x16/view.gif' border='0' alt='".$lang['view']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&rtdl_id=".$list['rtdl_id']."&o=c&search=".$search."'><img src='img/icones/16x16/document_edit.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&rtdl_id=".$list['rtdl_id']."&o=d&select[".$list['rtdl_id']."]=1&num=".$num."&limit=".$limit."&search=".$search."' onclick=\"return confirm('".$lang['confirm_removing']."')\"><img src='img/icones/16x16/delete.gif' border='0' alt='".$lang['delete']."'></a>&nbsp;&nbsp;";
		if ($list["activate"])
			$moptions .= "<a href='oreon.php?p=".$p."&rtdl_id=".$list['rtdl_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='".$lang['disable']."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='oreon.php?p=".$p."&rtdl_id=".$list['rtdl_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='".$lang['enable']."'></a>&nbsp;&nbsp;";
		$moptions .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$moptions .= "<select style='width:35; margin-bottom: 3px;' name='dupNbr[".$list['rtdl_id']."]'><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>10</option></select>";
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$list["name"],
						"RowMenu_link"=>"?p=".$p."&o=w&rtdl_id=".$list['rtdl_id'],
						"RowMenu_description"=>$list["description"],
						"RowMenu_nbrMail"=>isset($list["nbr"]) ? $list["nbr"] : "0",
						"RowMenu_status"=>$list["activate"] ? $lang['enable'] : $lang['disable'],
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
	$tpl->display("listDiff.ihtml");

	$tpl = new Smarty();
	$tpl = initSmartyTpl("./", $tpl);
	$tpl->assign('lang', $lang);
	$tpl->display("include/common/legend.ihtml");	
?>