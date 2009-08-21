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

	/*
	 * start quickSearch form
	 */
	$advanced_search = 0;
		
	if (isset($_GET["search_type_service"])){
		$search_type_service = $_GET["search_type_service"];
		$oreon->search_type_service = $_GET["search_type_service"];
	} else if (isset($oreon->search_type_service))
		 $search_type_service = $oreon->search_type_service;
	else
		$search_type_service = NULL;

	if (isset($_GET["search_type_host"])){
		$search_type_host = $_GET["search_type_host"];
		$oreon->search_type_host = $_GET["search_type_host"];
	} else if (isset($oreon->search_type_host))
		 $search_type_host = $oreon->search_type_host;
	else
		$search_type_host = NULL;

	
	if ($search && (!isset($searchH) && !isset($searchS))) {
		$searchH = $search;
		$searchS = $search;
	}
	
	if (!isset($search_type_service) && !isset($search_type_host)){
		$search_type_host = 1;
		$oreon->search_type_host = 1;
		$search_type_service = 1;
		$oreon->search_type_service = 1;
	}
	
	$rows = 0;
	$tmp = "";
	$tab_buffer = array();
	/*
	 * Due to Description maybe in the Template definition, we have to search if the description could match for each service with a Template.
	 */
	if (isset($searchS) || isset($searchH))	{
		$searchH = str_replace('/', "#S#", $searchH);
		$searchH = str_replace('\\', "#BS#", $searchH);
		$searchS = str_replace('/', "#S#", $searchS);
		$searchS = str_replace('\\', "#BS#", $searchS);
		if ($search_type_service && !$search_type_host) {
			/* 
			 * Service only search
			 */
			$locale_query = "SELECT DISTINCT hg.hg_id, sv.service_id, sv.service_description, sv.service_template_model_stm_id FROM service sv, hostgroup hg, hostgroup_relation hgr, host_service_relation hgsr WHERE (sv.service_alias LIKE '%".$searchS."%' OR sv.service_description LIKE '%".$searchS."%') AND hgsr.host_host_id IS NULL AND hgsr.hostgroup_hg_id = hg.hg_id AND hgsr.service_service_id = sv.service_id AND sv.service_register = '1' AND sv.service_activate = '1'";
			$DBRESULT =& $pearDB->query($locale_query);
			while ($service = $DBRESULT->fetchRow()){
				if (!isset($tab_buffer[$service["service_id"]]))
					$tmp ? $tmp .= ", ".$service["service_id"] : $tmp = $service["service_id"];
				$tab_buffer[$service["service_id"]] = $service["service_id"];
				$rows++;
			}
		} else if (!$search_type_service && $search_type_host)	{
			/* 
			 * Host only search
			 */
			$locale_query = "SELECT DISTINCT hg.hg_id, hg.hg_name, hg.hg_alias, sv.service_id, sv.service_description, sv.service_template_model_stm_id FROM service sv, hostgroup hg, hostgroup_relation hgr, host_service_relation hgsr WHERE (hg.hg_name LIKE '%".$searchH."%' OR hg.hg_alias LIKE '%".$searchH."%') AND hgsr.host_host_id IS NULL AND hgsr.hostgroup_hg_id=hg.hg_id AND hgsr.service_service_id = sv.service_id AND sv.service_register = '1' AND sv.service_activate = '1'";
			$DBRESULT =& $pearDB->query($locale_query);
			while ($service = $DBRESULT->fetchRow()) {			         
				$tmp ? $tmp .= ", ".$service["service_id"] : $tmp = $service["service_id"];			          
				$rows++;				
			}
		} else {
			$locale_query = "SELECT hg.hg_id, sv.service_id, sv.service_description, sv. service_template_model_stm_id FROM service sv, host_service_relation hgsr, hostgroup hg WHERE ((hg.hg_name LIKE '%".$searchH."%' OR hg_alias LIKE '%".$searchH."%') AND (sv.service_alias LIKE '%$searchS%' OR sv.service_description LIKE '%$searchS%')) AND hgsr.host_host_id IS NULL AND hgsr.hostgroup_hg_id=hg.hg_id AND sv.service_register = '1' AND hgsr.service_service_id = sv.service_id ";
			$DBRESULT =& $pearDB->query($locale_query);
			while ($service = $DBRESULT->fetchRow()) {			         
				$tmp ? $tmp .= ", ".$service["service_id"] : $tmp = $service["service_id"];			          
				$rows++;
			}
		}
	} else {
		/* show all */
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
	$tpl->assign("headerMenu_name", _("Host Group"));
	$tpl->assign("headerMenu_desc", _("Service"));
	$tpl->assign("headerMenu_retry", _("Scheduling"));
	$tpl->assign("headerMenu_parent", _("Parent Template"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_options", _("Options"));
	
	$tpl->assign("search_type_service", $search_type_service);
	$tpl->assign("search_type_host", $search_type_host);
	
	/*
	 * HostGroup/service list
	 */
	if ($searchH || $searchS) {
		$rq = 	"SELECT @nbr:=(SELECT COUNT(*) FROM host_service_relation " .
				"WHERE service_service_id = sv.service_id GROUP BY service_id) AS nbr, " .
				"sv.service_id, sv.service_description, sv.service_activate, sv.service_template_model_stm_id, " .
				"hg.hg_id, hg.hg_name " .
				"FROM service sv, hostgroup hg, host_service_relation hsr " .
				"WHERE sv.service_id IN (".($tmp ? $tmp : 'NULL').") " .
						"AND sv.service_register = '1' " .
						"AND hsr.service_service_id = sv.service_id " .
						"AND hg.hg_id = hsr.hostgroup_hg_id " .
						"ORDER BY hg.hg_name, service_description LIMIT ".$num * $limit.", ".$limit;
	} else {
		$rq = 	"SELECT @nbr:=(SELECT COUNT(*) FROM host_service_relation " .
				"WHERE service_service_id = sv.service_id GROUP BY service_id) AS nbr, " .
				"sv.service_id, sv.service_description, sv.service_activate, sv.service_template_model_stm_id, hg.hg_id, hg.hg_name " .
				"FROM service sv, hostgroup hg, host_service_relation hsr " .
				"WHERE sv.service_register = '1' " .
						"AND hsr.service_service_id = sv.service_id " .
						"AND hg.hg_id = hsr.hostgroup_hg_id " .
						"ORDER BY hg.hg_name, service_description LIMIT ".$num * $limit.", ".$limit;
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

	$time_min = $oreon->Nagioscfg['interval_length'] / 60;

	$elemArr = array();
	$fgHostgroup = array("value"=>NULL, "print"=>NULL);
	
	
	for ($i = 0; $service = $DBRESULT->fetchRow(); $i++) {
		$fgHostgroup["value"] != $service["hg_name"] ? ($fgHostgroup["print"] = true && $fgHostgroup["value"] = $service["hg_name"]) : $fgHostgroup["print"] = false;
		$selectedElements =& $form->addElement('checkbox', "select[".$service['service_id']."]");
		$moptions = "";
		if ($service["service_activate"])
			$moptions .= "<a href='main.php?p=".$p."&service_id=".$service['service_id']."&o=u&limit=".$limit."&num=".$num."&searchH=".$searchH."&searchS=".$searchS."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>";
		else
			$moptions .= "<a href='main.php?p=".$p."&service_id=".$service['service_id']."&o=s&limit=".$limit."&num=".$num."&searchH=".$searchH."&searchS=".$searchS."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>";

		$moptions .= "&nbsp;&nbsp;&nbsp;";
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
		
		$normal_check_interval = getMyServiceField($service['service_id'], "service_normal_check_interval") * $time_min;
		$retry_check_interval  = getMyServiceField($service['service_id'], "service_retry_check_interval") * $time_min;
		
		$elemArr[$i] = array(	"MenuClass"			=> "list_".($service["nbr"]>1 ? "three" : $style),
								"RowMenu_select"	=> $selectedElements->toHtml(),
								"RowMenu_name"		=> $service["hg_name"],
								"RowMenu_link"		=> "?p=60102&o=c&hg_id=".$service['hg_id'],
								"RowMenu_link2"		=> "?p=".$p."&o=c&service_id=".$service['service_id'],
								"RowMenu_parent"	=> $tplStr,
								"RowMenu_retry"		=> $normal_check_interval . " min / ".$retry_check_interval." min",
								"RowMenu_attempts"	=> getMyServiceField($service['service_id'], "service_max_check_attempts"),
								"RowMenu_desc"		=> $service["service_description"],
								"RowMenu_status"	=> $service["service_activate"] ? _("Enabled") : _("Disabled"),
								"RowMenu_options"	=> $moptions);
		$fgHostgroup["print"] ? NULL : $elemArr[$i]["RowMenu_name"] = NULL;
		$style != "two" ? $style = "two" : $style = "one";
	}
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
	
	$searchH = str_replace("#S#", "/", $searchH);
	$searchH = str_replace("#BS#", "\\", $searchH);
	$searchS = str_replace("#S#", "/", $searchS);
	$searchS = str_replace("#BS#", "\\", $searchS);
	
	$tpl->assign("searchH", (isset($searchH) ? $searchH : NULL));
	$tpl->assign("searchS", (isset($searchS) ? $searchS : NULL));
	 
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('Hosts', _("Host groups"));
	$tpl->assign('Services', _("Services"));
	$tpl->assign('Search', _("Search"));
	$tpl->display("listService.ihtml");
?>
