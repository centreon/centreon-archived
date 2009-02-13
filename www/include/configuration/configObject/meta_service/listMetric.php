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
 * SVN : $URL
 * SVN : $Id: listMetric.php 7146 2008-11-25 10:13:21Z jmathis $
 * 
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
	
	require_once("./class/centreonDB.class.php");
	$pearDBO = new CentreonDB("centstorage");	
	
	$DBRESULT = & $pearDB->query("SELECT * FROM meta_service WHERE meta_id = '".$meta_id."'");	

	$meta =& $DBRESULT->fetchRow();
	$tpl->assign("meta", array(	"meta" => _("Meta Service"),
								"name" => $meta["meta_name"],
								"calc_type" => $calcType[$meta["calcul_type"]]));
	$DBRESULT->free();

	/*
	 * start header menu
	 */
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_host", _("Host"));
	$tpl->assign("headerMenu_service", _("Services"));
	$tpl->assign("headerMenu_metric", _("Metrics"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_options", _("Options"));
	
	$rq = "SELECT * FROM `meta_service_relation` WHERE  meta_id = '".$meta_id."' ORDER BY host_id";
	$DBRESULT = & $pearDB->query($rq);

	$form = new HTML_QuickForm('Form', 'POST', "?p=".$p);
	
	/*
	 * Different style between each lines
	 */
	$style = "one";
	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr1 = array();
	for ($i = 0; $metric =& $DBRESULT->fetchRow(); $i++) {
		$moptions = "";
		$selectedElements =& $form->addElement('checkbox', "select[".$metric['msr_id']."]");	
		if ($metric["activate"])
			$moptions .= "<a href='main.php?p=".$p."&msr_id=".$metric['msr_id']."&o=us&meta_id=".$meta_id."&metric_id=".$metric['metric_id']."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='main.php?p=".$p."&msr_id=".$metric['msr_id']."&o=ss&meta_id=".$meta_id."&metric_id=".$metric['metric_id']."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
		$DBRESULTO =& $pearDBO->query("SELECT * FROM metrics m, index_data i WHERE m.metric_id = '".$metric['metric_id']."' and m.index_id=i.id");
		$row =& $DBRESULTO->fetchRow();
		$row["service_description"] = str_replace("#S#", "/", $row["service_description"]);
		$row["service_description"] = str_replace("#BS#", "\\", $row["service_description"]);
		$elemArr1[$i] = array(	"MenuClass"=>"list_".$style, 
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