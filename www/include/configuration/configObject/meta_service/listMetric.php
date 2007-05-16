<?
/** 
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
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

	$calcType = array("AVE"=>$lang['ms_selAvr'], "SOM"=>$lang['ms_selSum'], "MIN"=>$lang['ms_selMin'], "MAX"=>$lang['ms_selMax']);

	if (!isset($oreon))
		exit();
		
	include("./include/common/autoNumLimit.php");
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	require_once("./DBOdsConnect.php");
	
	$DBRESULT = & $pearDB->query("SELECT * FROM meta_service WHERE meta_id = '".$meta_id."'");	
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";

	$meta =& $DBRESULT->fetchRow();
	$tpl->assign("meta", 
			array("meta"=>$lang["ms"],
				"name"=>$meta["meta_name"],
				"calc_type"=>$calcType[$meta["calcul_type"]]));
	$DBRESULT->free();

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_host", $lang["h"]);
	$tpl->assign("headerMenu_service", $lang["sv"]);
	$tpl->assign("headerMenu_metric", $lang["ms_metric"]);
	$tpl->assign("headerMenu_status", $lang["status"]);
	$tpl->assign("headerMenu_options", $lang["options"]);
	# end header menu

	if ($oreon->user->admin || !HadUserLca($pearDB))
		$rq = "SELECT * FROM `meta_service_relation` WHERE  meta_id = '".$meta_id."' ORDER BY host_id";
	else
		$rq = "SELECT * FROM `meta_service_relation` WHERE host_id IN (".$lcaHoststr.") AND meta_id = '".$meta_id."' ORDER BY host_id";
	$DBRESULT = & $pearDB->query($rq);
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";

	$form = new HTML_QuickForm('Form', 'GET', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr1 = array();
	for ($i = 0; $DBRESULT->fetchInto($metric); $i++) {
		$selectedElements =& $form->addElement('checkbox', "select[".$metric['msr_id']."]");	
		$moptions = "<a href='oreon.php?p=".$p."&msr_id=".$metric['msr_id']."&metric_id=".$metric['metric_id']."&meta_id=".$meta_id."&o=ws'><img src='img/icones/16x16/view.gif' border='0' alt='".$lang['view']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&msr_id=".$metric['msr_id']."&metric_id=".$metric['metric_id']."&meta_id=".$meta_id."&o=cs'><img src='img/icones/16x16/document_edit.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&msr_id=".$metric['msr_id']."&metric_id=".$metric['metric_id']."&meta_id=".$meta_id."&o=ds&select[".$metric['msr_id']."]=1' onclick=\"return confirm('".$lang['confirm_removing']."')\"><img src='img/icones/16x16/delete.gif' border='0' alt='".$lang['delete']."'></a>&nbsp;&nbsp;";
		if ($metric["activate"])
			$moptions .= "<a href='oreon.php?p=".$p."&msr_id=".$metric['msr_id']."&o=us&meta_id=".$meta_id."&metric_id=".$metric['metric_id']."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='".$lang['disable']."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='oreon.php?p=".$p."&msr_id=".$metric['msr_id']."&o=ss&meta_id=".$meta_id."&metric_id=".$metric['metric_id']."'><img src='img/icones/16x16/element_next.gif' border='0' alt='".$lang['enable']."'></a>&nbsp;&nbsp;";
		$DBRESULTO =& $pearDBO->query("SELECT * FROM metrics m, index_data i WHERE m.metric_id = '".$metric['metric_id']."' and m.index_id=i.id");
		$row =& $DBRESULTO->fetchRow();
		$elemArr1[$i] = array("MenuClass"=>"list_".$style, 
					"RowMenu_select"=>$selectedElements->toHtml(),
					"RowMenu_host"=>htmlentities($row["host_name"], ENT_QUOTES),
					"RowMenu_link"=>"?p=".$p."&o=ws&msr_id=".$metric['msr_id'],
					"RowMenu_service"=>htmlentities($row["service_description"], ENT_QUOTES),
					"RowMenu_metric"=>$row["metric_name"]." (".$row["unit_name"].")",
					"RowMenu_status"=>$metric["activate"] ? $lang['enable'] : $lang['disable'],
					"RowMenu_options"=>$moptions);
		$DBRESULTO->free();
		$style != "two" ? $style = "two" : $style = "one";
	}
	$tpl->assign("elemArr1", $elemArr1);	

	#Different messages we put in the template
	$tpl->assign('msg', array ("addL1"=>"?p=".$p."&o=as&meta_id=".$meta_id, "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));
		
	#Element we need when we reload the page
	$form->addElement('hidden', 'p');
	$form->addElement('hidden', 'meta_id');
	$tab = array ("p" => $p, "meta_id"=>$meta_id);
	$form->setDefaults($tab);

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
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"");
	$form->addElement('select', 'o1', NULL, array(NULL=>$lang["lgd_more_actions"], "ds"=>$lang['delete']/*, "mc"=>$lang['mchange']*/), $attrs1);
	$form->setDefaults(array('o1' => NULL));

	
	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"");
    $form->addElement('select', 'o2', NULL, array(NULL=>$lang["lgd_more_actions"], "ds"=>$lang['delete']/*, "mc"=>$lang['mchange']*/), $attrs2);
	$form->setDefaults(array('o2' => NULL));

	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);
	$o1->setSelected(NULL);

	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);
	
	$tpl->assign('limit', $limit);

	#
	##Apply a template definition
	#	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listMetric.ihtml");
?>