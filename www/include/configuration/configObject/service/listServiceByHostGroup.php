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

	if (isset($_POST["searchH"])) {
		$searchH = $_POST["searchH"];
		$oreon->svc_host_search = $searchH;
		$search_type_host = 1;
	} else {
		if (isset($oreon->svc_host_search) && $oreon->svc_host_search)
			$searchH = $oreon->svc_host_search;
		else
			$searchH = NULL;
	}
	
	if (isset($_POST["searchS"])) {
		$searchS = $_POST["searchS"];
		$oreon->svc_svc_search = $searchS;
		$search_type_service = 1;
	} else {	
		if (isset($oreon->svc_svc_search) && $oreon->svc_svc_search)
			$searchS = $oreon->svc_svc_search;
		else
			$searchS = NULL;
	}

	include("./include/common/autoNumLimit.php");

	
	if (isset($_GET["search_type_service"])){
		$search_type_service = $_GET["search_type_service"];
		$oreon->search_type_service = $_GET["search_type_service"];
	} else if ($oreon->search_type_service)
		 $search_type_service = $oreon->search_type_service;
	else
		$search_type_service = NULL;

	if (isset($_GET["search_type_host"])){
		$search_type_host = $_GET["search_type_host"];
		$oreon->search_type_host = $_GET["search_type_host"];
	} else if ($oreon->search_type_host)
		 $search_type_host = $oreon->search_type_host;
	else
		$search_type_host = NULL;

	$rows = 0;
	$tmp = NULL;
	/*
	 * Due to Description maybe in the Template definition, we have to search if the description could match for each service with a Template.
	 */
	if (isset($search) && $search != "") {
		$search = str_replace('/', "#S#", $search);
		$search = str_replace('\\', "#BS#", $search);
	
		$DBRESULT =& $pearDB->query("SELECT service_id, service_description, service_template_model_stm_id FROM service sv, host_service_relation hsr WHERE sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND hsr.host_host_id IS NULL AND (sv.service_description LIKE '%$search%')");
		
		while ($service =& $DBRESULT->fetchRow()){
			$rows++;
			$tmp ? $tmp .= ", ".$service["service_id"] : $tmp = $service["service_id"];
		}
		
	} else	{
		$DBRESULT =& $pearDB->query("SELECT service_description FROM service sv, host_service_relation hsr WHERE service_register = '1' AND hsr.service_service_id = sv.service_id AND hsr.host_host_id IS NULL");
		$rows = $DBRESULT->numRows();
	}

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	include("./include/common/checkPagination.php");

	/*
	 * start header menu
	 */
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("HostGroup"));
	$tpl->assign("headerMenu_desc", _("Service"));
	$tpl->assign("headerMenu_retry", _("Scheduling"));
	$tpl->assign("headerMenu_parent", _("Parent Template"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_options", _("Options"));
	
	/*
	 * HostGroup/service list
	 */
	if ($search) {
		$rq = "SELECT @nbr:=(SELECT COUNT(*) FROM host_service_relation WHERE service_service_id = sv.service_id GROUP BY service_id ) AS nbr, sv.service_id, sv.service_description, sv.service_activate, sv.service_template_model_stm_id, hg.hg_id, hg.hg_name FROM service sv, hostgroup hg, host_service_relation hsr WHERE sv.service_id IN (".($tmp ? $tmp : 'NULL').") AND sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND hg.hg_id = hsr.hostgroup_hg_id ORDER BY hg.hg_name, service_description LIMIT ".$num * $limit.", ".$limit;
	} else {
		$rq = "SELECT @nbr:=(SELECT COUNT(*) FROM host_service_relation WHERE service_service_id = sv.service_id GROUP BY service_id ) AS nbr, sv.service_id, sv.service_description, sv.service_activate, sv.service_template_model_stm_id, hg.hg_id, hg.hg_name FROM service sv, hostgroup hg, host_service_relation hsr WHERE sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND hg.hg_id = hsr.hostgroup_hg_id ORDER BY hg.hg_name, service_description LIMIT ".$num * $limit.", ".$limit;
	}
	$DBRESULT =& $pearDB->query($rq);

	$search = tidySearchKey($search, $advanced_search);

	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
	
	/*
	 * Different style between each lines
	 */
	$style = "one";

	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	 
	$interval_length = $oreon->Nagioscfg['interval_length'];
	 
	$elemArr = array();
	$fgHostgroup = array("value"=>NULL, "print"=>NULL);
	
	$search = str_replace('#S#', "/", $search);
	$search = str_replace('#BS#', "\\", $search);
	
	for ($i = 0; $service =& $DBRESULT->fetchRow(); $i++) {
		$moptions = "";
		$fgHostgroup["value"] != $service["hg_name"] ? ($fgHostgroup["print"] = true && $fgHostgroup["value"] = $service["hg_name"]) : $fgHostgroup["print"] = false;
		$selectedElements =& $form->addElement('checkbox', "select[".$service['service_id']."]");
		
		if ($service["service_activate"])
			$moptions .= "<a href='main.php?p=".$p."&service_id=".$service['service_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='main.php?p=".$p."&service_id=".$service['service_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
		
		$moptions .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$service['service_id']."]'></input>";
		
		/*
		 * If the description of our Service is in the Template definition, we have to catch it, whatever the level of it :-)
		 */
		if (!$service["service_description"])
			$service["service_description"] = getMyServiceAlias($service['service_template_model_stm_id']);
		else	{
			$service["service_description"] = str_replace('#S#', "/", $service["service_description"]);
			$service["service_description"] = str_replace('#BS#', "\\", $service["service_description"]);
		}
		
		/* 
		 * TPL List
		 */
		$tplArr = array();
		$tplStr = NULL;
		$tplArr = getMyServiceTemplateModels($service["service_template_model_stm_id"]);
		if (count($tplArr))
			foreach($tplArr as $key =>$value){
				$value = str_replace('#S#', "/", $value);
				$value = str_replace('#BS#', "\\", $value);
				$tplStr .= "&nbsp;->&nbsp;<a href='main.php?p=60206&o=c&service_id=".$key."'>".$value."</a>";				
			}
		
		# Get service intervals in seconds
		$normal_check_interval = getMyServiceField($service['service_id'], "service_normal_check_interval") * $interval_length;
		$retry_check_interval  = getMyServiceField($service['service_id'], "service_retry_check_interval") * $interval_length;

		if ($normal_check_interval % 60 == 0) {
			$normal_units = "min";
			$normal_check_interval = $normal_check_interval / 60;
		} else {
			$normal_units = "sec";
		}

		if ($retry_check_interval % 60 == 0) {
			$retry_units = "min";
			$retry_check_interval = $retry_check_interval / 60;
		} else {
			$retry_units = "sec";
		}
		
		$elemArr[$i] = array(	"MenuClass"=>"list_".($service["nbr"]>1 ? "three" : $style),
								"RowMenu_select"=>$selectedElements->toHtml(),
								"RowMenu_name"=>$service["hg_name"],
								"RowMenu_link"=>"?p=60102&o=c&hg_id=".$service['hg_id'],
								"RowMenu_link2"=>"?p=".$p."&o=c&service_id=".$service['service_id'],
								"RowMenu_parent"=>$tplStr,
								"RowMenu_retry"=> "$normal_check_interval $normal_units / $retry_check_interval $retry_units",
								"RowMenu_attempts"=>getMyServiceField($service['service_id'], "service_max_check_attempts"),
								"RowMenu_desc"=>$service["service_description"],
								"RowMenu_status"=>$service["service_activate"] ? _("Enabled") : _("Disabled"),
								"RowMenu_options"=>$moptions);
								
		$fgHostgroup["print"] ? NULL : $elemArr[$i]["RowMenu_name"] = NULL;
		$style != "two" ? $style = "two" : $style = "one";	}
	$tpl->assign("elemArr", $elemArr);

	/*
	 * Different messages we put in the template
	 */
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));

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
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 6 && confirm('"._("Are you sure you want to detach the service ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3 || this.form.elements['o1'].selectedIndex == 4 ||this.form.elements['o1'].selectedIndex == 5){" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "mc"=>_("Massive Change"), "ms"=>_("Enable"), "mu"=>_("Disable"), "dv"=>_("Detach")), $attrs1);

	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 6 && confirm('"._("Are you sure you want to detach the service ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3 || this.form.elements['o2'].selectedIndex == 4 ||this.form.elements['o2'].selectedIndex == 5){" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "mc"=>_("Massive Change"), "ms"=>_("Enable"), "mu"=>_("Disable"), "dv"=>_("Detach")), $attrs2);

	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);

	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);

	$tpl->assign('limit', $limit);

	/*
	 * Apply a template definition
	 */
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('Hosts', _("Hosts"));
	$tpl->assign('Services', _("Services"));
	$tpl->assign('Search', _("Search"));
	$tpl->display("listService.ihtml");
?>