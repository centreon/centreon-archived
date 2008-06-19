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
 

	$calcType = array("AVE"=>_("Average"), "SOM"=>_("Sum"), "MIN"=>_("Min"), "MAX"=>_("Max"));

	if (!isset($oreon))
		exit();
		
	include("./include/common/autoNumLimit.php");
	
	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	require_once("./DBOdsConnect.php");
	
	$DBRESULT = & $pearDB->query("SELECT * FROM meta_service WHERE meta_id = '".$meta_id."'");	
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";

	$meta =& $DBRESULT->fetchRow();
	$tpl->assign("meta", 
			array("meta"=>_("Meta Service"),
				"name"=>$meta["meta_name"],
				"calc_type"=>$calcType[$meta["calcul_type"]]));
	$DBRESULT->free();

	/*
	 * start header menu
	 */
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_host", _("Host"));
	$tpl->assign("headerMenu_service", _("Server"));
	$tpl->assign("headerMenu_metric", _("Metric"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_options", _("Options"));
	
	if ($is_admin)
		$rq = "SELECT * FROM `meta_service_relation` WHERE  meta_id = '".$meta_id."' ORDER BY host_id";
	else
		$rq = "SELECT * FROM `meta_service_relation` WHERE host_id IN (".$lcaHoststr.") AND meta_id = '".$meta_id."' ORDER BY host_id";
	$DBRESULT = & $pearDB->query($rq);
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";

	$form = new HTML_QuickForm('Form', 'POST', "?p=".$p);
	
	/*
	 * Different style between each lines
	 */
	$style = "one";
	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr1 = array();
	for ($i = 0; $DBRESULT->fetchInto($metric); $i++) {
		$moptions = "";
		$selectedElements =& $form->addElement('checkbox', "select[".$metric['msr_id']."]");	
		if ($metric["activate"])
			$moptions .= "<a href='main.php?p=".$p."&msr_id=".$metric['msr_id']."&o=us&meta_id=".$meta_id."&metric_id=".$metric['metric_id']."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='main.php?p=".$p."&msr_id=".$metric['msr_id']."&o=ss&meta_id=".$meta_id."&metric_id=".$metric['metric_id']."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
		$DBRESULTO =& $pearDBO->query("SELECT * FROM metrics m, index_data i WHERE m.metric_id = '".$metric['metric_id']."' and m.index_id=i.id");
		$row =& $DBRESULTO->fetchRow();
		$elemArr1[$i] = array("MenuClass"=>"list_".$style, 
					"RowMenu_select"=>$selectedElements->toHtml(),
					"RowMenu_host"=>htmlentities($row["host_name"], ENT_QUOTES),
					"RowMenu_link"=>"?p=".$p."&o=ws&msr_id=".$metric['msr_id'],
					"RowMenu_service"=>htmlentities($row["service_description"], ENT_QUOTES),
					"RowMenu_metric"=>$row["metric_name"]." (".$row["unit_name"].")",
					"RowMenu_status"=>$metric["activate"] ? _("Enabled") : _("Disabled"),
					"RowMenu_options"=>$moptions);
		$DBRESULTO->free();
		$style != "two" ? $style = "two" : $style = "one";
	}
	$tpl->assign("elemArr1", $elemArr1);	

	/*
	 * Different messages we put in the template
	 */
	$tpl->assign('msg', array ("addL1"=>"?p=".$p."&o=as&meta_id=".$meta_id, "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
		
	/*
	 * Element we need when we reload the page
	 */
	$form->addElement('hidden', 'p');
	$form->addElement('hidden', 'meta_id');
	$tab = array ("p" => $p, "meta_id"=>$meta_id);
	$form->setDefaults($tab);

	/*
	 * Toolbar select 
	 */
	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?php
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} ");
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "ds"=>_("Delete")), $attrs1);
	$form->setDefaults(array('o1' => NULL));

	
	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} ");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "ds"=>_("Delete")), $attrs2);
	$form->setDefaults(array('o2' => NULL));

	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);
	$o1->setSelected(NULL);

	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);
	
	$tpl->assign('limit', $limit);

	/*
	 * Apply a template definition
	 */
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listMetric.ihtml");
?>