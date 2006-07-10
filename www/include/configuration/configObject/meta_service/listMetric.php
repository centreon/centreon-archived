<?php
/** 
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called « Oreon Meta Service » is developped by Merethis company for Lafarge Group, 
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
	$calcType = array("AVE"=>$lang['ms_selAvr'], "SOM"=>$lang['ms_selSum'], "MIN"=>$lang['ms_selMin'], "MAX"=>$lang['ms_selMax']);

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	require_once("./DBPerfparseConnect.php");
	
	$res = & $pearDB->query("SELECT * FROM meta_service WHERE meta_id = '".$meta_id."'");	
	$meta =& $res->fetchRow();
	$tpl->assign("meta", 
			array("meta"=>$lang["ms"],
				"name"=>$meta["meta_name"],
				"calc_type"=>$calcType[$meta["calcul_type"]]));
	$res->free();

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_host", $lang["h"]);
	$tpl->assign("headerMenu_service", $lang["sv"]);
	$tpl->assign("headerMenu_metric", $lang["ms_metric"]);
	$tpl->assign("headerMenu_status", $lang["status"]);
	$tpl->assign("headerMenu_options", $lang["options"]);
	# end header menu

	$rq = "SELECT * FROM `meta_service_relation` WHERE host_id IN (".$oreon->user->lcaHStr.") AND meta_id = '".$meta_id."' ORDER BY host_id";
	$res = & $pearDB->query($rq);
	$form1 = new HTML_QuickForm('Form', 'GET', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr1 = array();
	for ($i = 0; $res->fetchInto($metric); $i++) {
		$selectedElements =& $form1->addElement('checkbox', "select[".$metric['msr_id']."]");	
		$moptions = "<a href='oreon.php?p=".$p."&msr_id=".$metric['msr_id']."&metric_id=".$metric['metric_id']."&meta_id=".$meta_id."&o=ws'><img src='img/icones/16x16/view.gif' border='0' alt='".$lang['view']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&msr_id=".$metric['msr_id']."&metric_id=".$metric['metric_id']."&meta_id=".$meta_id."&o=cs'><img src='img/icones/16x16/document_edit.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&msr_id=".$metric['msr_id']."&metric_id=".$metric['metric_id']."&meta_id=".$meta_id."&o=ds&select[".$metric['msr_id']."]=1' onclick=\"return confirm('".$lang['confirm_removing']."')\"><img src='img/icones/16x16/delete.gif' border='0' alt='".$lang['delete']."'></a>&nbsp;&nbsp;";
		if ($metric["activate"])
			$moptions .= "<a href='oreon.php?p=".$p."&msr_id=".$metric['msr_id']."&o=us&meta_id=".$meta_id."&metric_id=".$metric['metric_id']."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='".$lang['disable']."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='oreon.php?p=".$p."&msr_id=".$metric['msr_id']."&o=ss&meta_id=".$meta_id."&metric_id=".$metric['metric_id']."'><img src='img/icones/16x16/element_next.gif' border='0' alt='".$lang['enable']."'></a>&nbsp;&nbsp;";
		$resPp =& $pearDBpp->query("SELECT * FROM perfdata_service_metric WHERE metric_id = '".$metric['metric_id']."'");
		$row =& $resPp->fetchRow();
		$elemArr1[$i] = array("MenuClass"=>"list_".$style, 
					"RowMenu_select"=>$selectedElements->toHtml(),
					"RowMenu_host"=>htmlentities($row["host_name"], ENT_QUOTES),
					"RowMenu_link"=>"?p=".$p."&o=ws&msr_id=".$metric['msr_id'],
					"RowMenu_service"=>htmlentities($row["service_description"], ENT_QUOTES),
					"RowMenu_metric"=>$row["metric"]." (".$row["unit"].")",
					"RowMenu_status"=>$metric["activate"] ? $lang['enable'] : $lang['disable'],
					"RowMenu_options"=>$moptions);
		$resPp->free();
		$style != "two" ? $style = "two" : $style = "one";
	}
	$tpl->assign("elemArr1", $elemArr1);	

	#Different messages we put in the template
	$tpl->assign('msg', array ("addL1"=>"?p=".$p."&o=as&meta_id=".$meta_id, "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));
		
	#Element we need when we reload the page
	$form1->addElement('hidden', 'p');
	$form1->addElement('hidden', 'meta_id');
	$tab = array ("p" => $p, "meta_id"=>$meta_id);
	$form1->setDefaults($tab);

	#
	##Apply a template definition
	#
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form1->accept($renderer);
	$tpl->assign('form1', $renderer->toArray());
	$tpl->display("listMetric.ihtml");
?>