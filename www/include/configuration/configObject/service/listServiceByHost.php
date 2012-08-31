<?php
/*
 * Copyright 2005-2011 MERETHIS
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

	if (!isset($oreon)) {
		exit();
	}

	/*
	 * Object init
	 */
    $mediaObj = new CentreonMedia($pearDB);

	/*
	 * Get Extended informations
	 */
	$ehiCache = array();
	$DBRESULT = $pearDB->query("SELECT ehi_icon_image, host_host_id FROM extended_host_information");
	while ($ehi = $DBRESULT->fetchRow()) {
		$ehiCache[$ehi["host_host_id"]] = $ehi["ehi_icon_image"];
	}
	$DBRESULT->free();

	if (isset($_POST["template"])) {
		$template = $_POST["template"];
	} else if (isset($_GET["template"])) {
		$template = $_GET["template"];
	} else {
		$template = NULL;
	}

    if (isset($_POST["hostgroups"])) {
		$hostgroups = $_POST["hostgroups"];
	} else if (isset($_GET["hostgroups"])) {
		$hostgroups = $_GET["hostgroups"];
	} else {
		$hostgroups = NULL;
	}

	if (isset($_POST["host_id"])) {
		$host_id = $_POST["host_id"];
	} else if (isset($_GET["host_id"])) {
		$host_id = $_GET["host_id"];
	} else {
		$host_id = NULL;
	}

	if (isset($_POST["status"])) {
		$status = $_POST["status"];
	} else if (isset($_GET["status"])) {
		$status = $_GET["status"];
	} else {
		$status = -1;
	}

	/*
	 * Get Service Template List
	 */
	$tplService = array();
	$templateFilter = "<option value='0'></option>";
	$DBRESULT = $pearDB->query("SELECT service_id, service_description, service_alias FROM service WHERE service_register = '0' AND service_activate = '1' ORDER BY service_description");
	while ($tpl = $DBRESULT->fetchRow()) {
		$tplService[$tpl["service_id"]] = $tpl["service_alias"];
		$templateFilter .= "<option value='".$tpl["service_id"]."'".(($tpl["service_id"] == $template) ? " selected" : "").">".$tpl["service_description"]."</option>";
	}
	$DBRESULT->free();

	/*
	 * Status Filter
	 */
	$statusFilter = "<option value=''".(($status == -1) ? " selected" : "")."> </option>";;
	$statusFilter .= "<option value='1'".(($status == 1) ? " selected" : "").">"._("Enable")."</option>";
	$statusFilter .= "<option value='0'".(($status == 0 && $status != '') ? " selected" : "").">"._("Disable")."</option>";

	$sqlFilterCase = "";
	if ($status == 1) {
		$sqlFilterCase = " AND sv.service_activate = '1' ";
	} else if ($status == 0 && $status != "") {
		$sqlFilterCase = " AND sv.service_activate = '0' ";
	}

	require_once "./class/centreonHost.class.php";

	/*
	 * Init Objects
	 */
	$host_method = new CentreonHost($pearDB);
	$service_method = new CentreonService($pearDB);
    /*$hostGroup_handler = new CentreonHostgroups($pearDB);
    echo '<pre>'; var_dump($hostGroup_handler->getHostgroupsList($pearDB)); echo '</pre>';*/

    /**
     * Get
     */
    $hostgroupsTab = array();
	$hostgroupsFilter = "<option value='0'></option>";
	$DBRESULT = $pearDB->query("SELECT hg_id, hg_name, hg_alias, hg_activate FROM hostgroup WHERE hg_id NOT IN (SELECT hg_child_id FROM hostgroup_hg_relation) AND hg_activate='1' ORDER BY hg_name");
	while ($hgrp = $DBRESULT->fetchRow())
    {
		$hostgroupsTab[$hgrp["hg_id"]] = $hgrp["hg_name"];
		$hostgroupsFilter .= "<option value='".$hgrp["hg_id"]."'".(($hgrp["hg_id"] == $hostgroups) ? " selected" : "").">".$hgrp["hg_name"]."</option>";
	}
	$DBRESULT->free();


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
		if (isset($oreon->svc_svc_search) && $oreon->svc_svc_search) {
			$searchS = $oreon->svc_svc_search;
		} else {
			$searchS = null;
		}
	}

	include("./include/common/autoNumLimit.php");

	/*
	 * start quickSearch form
	 */
	$advanced_search = 0;

	if (isset($_GET["search_type_service"])){
		$search_type_service = $_GET["search_type_service"];
		$oreon->search_type_service = $_GET["search_type_service"];
	} elseif (isset($oreon->search_type_service)) {
		 $search_type_service = $oreon->search_type_service;
	} else {
		$search_type_service = null;
	}

	if (isset($_GET["search_type_host"])){
		$search_type_host = $_GET["search_type_host"];
		$oreon->search_type_host = $_GET["search_type_host"];
	} elseif (isset($oreon->search_type_host)) {
		 $search_type_host = $oreon->search_type_host;
	} else {
		$search_type_host = null;
	}

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

	$searchHostallone = "";
	if (isset($host_id) && $host_id) {
		$searchH = "";
		$searchS = "";
		$searchHostallone = " hsr.host_host_id = '$host_id' AND ";
	}

	$rows = 0;
	$tmp = array();
	$tmp2 = array();
	$tab_buffer = array();
	$searchH = CentreonDB::escape($searchH);
	$searchS = CentreonDB::escape($searchS);

	/*
	 * Search case
	 */
	if (isset($searchS) && $searchS != "" || isset($searchH) && $searchH != "")	{
		if ($search_type_service && !$search_type_host) {
			$DBRESULT = $pearDB->query("SELECT SQL_CALC_FOUND_ROWS host.host_id, service_id, service_description, service_template_model_stm_id FROM service sv, host_service_relation hsr, host WHERE $searchHostallone sv.service_register = '1' $sqlFilterCase AND hsr.service_service_id = sv.service_id AND hsr.hostgroup_hg_id IS NULL AND hsr.host_host_id = host.host_id AND (sv.service_alias LIKE '%$searchS%' OR sv.service_description LIKE '%$searchS%')".((isset($template) && $template) ? " AND service_template_model_stm_id = '$template' " : " LIMIT ".$num * $limit.", ".$limit));
			while ($service = $DBRESULT->fetchRow()){
				if (!isset($tab_buffer[$service["service_id"]])) {
					$tmp[] = $service["service_id"];
				}
				$tmp2[] = $service["host_id"];
				$tab_buffer[$service["service_id"]] = $service["service_id"];
			}
		} elseif (!$search_type_service && $search_type_host)	{
			$locale_query = "SELECT SQL_CALC_FOUND_ROWS host.host_id, service_id, service_description, service_template_model_stm_id FROM service sv, host_service_relation hsr, host WHERE sv.service_register = '1' $sqlFilterCase AND $searchHostallone (host_name LIKE '%".$searchH."%' OR host_alias LIKE '%".$searchH."%' OR host_address LIKE '%".$searchH."%') AND hsr.host_host_id=host.host_id AND hsr.service_service_id = sv.service_id AND hsr.hostgroup_hg_id IS NULL".((isset($template) && $template) ? " AND service_template_model_stm_id = '$template' " : " LIMIT ".$num * $limit.", ".$limit);
			$DBRESULT = $pearDB->query($locale_query);
			while ($service = $DBRESULT->fetchRow()) {
				$tmp[] = $service["service_id"];
				$tmp2[] = $service["host_id"];
			}
		} else {
			$locale_query = "SELECT SQL_CALC_FOUND_ROWS host.host_id, service_id, service_description, service_template_model_stm_id FROM service sv, host_service_relation hsr, host WHERE sv.service_register = '1' $sqlFilterCase AND $searchHostallone ((host_name LIKE '%".$searchH."%' OR host_alias LIKE '%".$searchH."%' OR host_address LIKE '%".$searchH."%') AND (sv.service_alias LIKE '%$searchS%' OR sv.service_description LIKE '%$searchS%')) AND hsr.host_host_id=host.host_id AND hsr.service_service_id = sv.service_id AND hsr.hostgroup_hg_id IS NULL".((isset($template) && $template) ? " AND service_template_model_stm_id = '$template' " : " LIMIT ".$num * $limit.", ".$limit);
			$DBRESULT = $pearDB->query($locale_query);
			while ($service = $DBRESULT->fetchRow()) {
				$tmp[] = $service["service_id"];
				$tmp2[] = $service["host_id"];
			}
		}
    } else {
    	$DBRESULT = $pearDB->query("SELECT SQL_CALC_FOUND_ROWS service_description FROM service sv, host_service_relation hsr WHERE $searchHostallone service_register = '1' $sqlFilterCase AND hsr.service_service_id = sv.service_id AND hsr.hostgroup_hg_id IS NULL".((isset($template) && $template) ? " AND service_template_model_stm_id = '$template' " : " LIMIT ".$num * $limit.", ".$limit));
	}
	$rows = $pearDB->numberRows();

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/* Access level */
	($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';
	$tpl->assign('mode_access', $lvl_access);

	include("./include/common/checkPagination.php");

	/*
	 * start header menu
	 */
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Host"));
	$tpl->assign("headerMenu_desc", _("Service"));
	$tpl->assign("headerMenu_retry", _("Scheduling"));
	$tpl->assign("headerMenu_parent", _("Parent Template"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_options", _("Options"));

	$tpl->assign("search_type_service", $search_type_service);
	$tpl->assign("search_type_host", $search_type_host);

	/*
	 * Host/service list
	 */
	if ($searchH && $searchH != "" || $searchS && $searchS != "") {
	    $listServiceId = 'IN (NULL)';
	    $listHostId = 'IN (NULL)';
	    $tmp = array_unique($tmp);
	    $tmp2 = array_unique($tmp2);
	    if (count($tmp) == 1) {
	        $listServiceId = '= ' . $tmp[0];
	    } elseif (count($tmp) > 1) {
	        $listServiceId = 'IN (' . join(', ', $tmp) . ')';
	    }
	    if (count($tmp2) == 1) {
	        $listHostId = '= ' . $tmp2[0];
	    } elseif (count($tmp2) > 1) {
	        $listHostId = 'IN (' . join(', ', $tmp2) . ')';
	    }
		$rq = 	"SELECT esi.esi_icon_image, sv.service_id, sv.service_description, sv.service_activate, sv.service_template_model_stm_id, " .
				"host.host_id, host.host_name, host.host_template_model_htm_id, sv.service_normal_check_interval, " .
				"sv.service_retry_check_interval, sv.service_max_check_attempts " .
				"FROM service sv, host".
                ((isset($hostgroups) && $hostgroups) ? ", hostgroup_relation hogr, " : ", ") .
                "host_service_relation hsr " .
		        "LEFT JOIN extended_service_information esi ON esi.service_service_id = hsr.service_service_id " .
				"WHERE $searchHostallone sv.service_id " . $listServiceId . " " .
						($searchHostallone == "" ? "AND host.host_id " . $listHostId . " " : "") .
						"AND sv.service_register = '1' $sqlFilterCase " .
						"AND hsr.service_service_id = sv.service_id " .
						"AND host.host_id = hsr.host_host_id " .
						"AND host.host_register = '1' " .
						((isset($template) && $template) ? " AND service_template_model_stm_id = '$template' " : "") .
                        ((isset($hostgroups) && $hostgroups) ? " AND hogr.hostgroup_hg_id = '$hostgroups' AND hogr.host_host_id = host.host_id " : "") .
						"ORDER BY host.host_name, service_description";
	} else {
		$rq = 	"SELECT esi.esi_icon_image, sv.service_id, sv.service_description, sv.service_activate, sv.service_template_model_stm_id, host.host_id, " .
				"host.host_name, host.host_template_model_htm_id, sv.service_normal_check_interval, sv.service_retry_check_interval, " .
				"sv.service_max_check_attempts " .
				"FROM service sv, host".
                ((isset($hostgroups) && $hostgroups) ? ", hostgroup_relation hogr, " : ", ") .
                "host_service_relation hsr " .
		        "LEFT JOIN extended_service_information esi ON esi.service_service_id = hsr.service_service_id ".
				"WHERE $searchHostallone sv.service_register = '1' $sqlFilterCase " .
						"AND hsr.service_service_id = sv.service_id " .
						"AND host.host_id = hsr.host_host_id " .
						"AND host.host_register = '1' " .
						((isset($template) && $template) ? " AND service_template_model_stm_id = '$template' " : "") .
                        ((isset($hostgroups) && $hostgroups) ? " AND hogr.hostgroup_hg_id = '$hostgroups' AND hogr.host_host_id = host.host_id " : "") .
						"ORDER BY host.host_name, service_description LIMIT ".$num * $limit.", ".$limit;
	}
	$DBRESULT = $pearDB->query($rq);
	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);

	/**
	 * Different style between each lines
	 */
	$style = "one";

	/**
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr = array();
	$fgHost = array("value"=>NULL, "print"=>NULL);

	$interval_length = $oreon->Nagioscfg['interval_length'];

	for ($i = 0; $service = $DBRESULT->fetchRow(); $i++) {
		/**
		 * Get Number of Hosts linked to this one.
		 */
		$request = "SELECT COUNT(*) FROM host_service_relation WHERE service_service_id = '".$service["service_id"]."'";
		$BDRESULT2 = $pearDB->query($request);
		$data = $BDRESULT2->fetchRow();
		$service["nbr"] = $data["COUNT(*)"];
		$BDRESULT2->free();
		unset($data);

		/**
		 * If the name of our Host is in the Template definition, we have to catch it, whatever the level of it :-)
		 */
		$fgHost["value"] != $service["host_name"] ? ($fgHost["print"] = true && $fgHost["value"] = $service["host_name"]) : $fgHost["print"] = false;
		$selectedElements = $form->addElement('checkbox', "select[".$service['service_id']."]");
		$moptions = "";
		if ($service["service_activate"]) {
			$moptions .= "<a href='main.php?p=".$p."&service_id=".$service['service_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."&hostgroups=".$hostgroups."&template=$template&status=".$status."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		} else {
			$moptions .= "<a href='main.php?p=".$p."&service_id=".$service['service_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."&hostgroups=".$hostgroups."&template=$template&status=".$status."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
		}

		$moptions .= "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$service['service_id']."]'></input>";

		/*
		 * If the description of our Service is in the Template definition, we have to catch it, whatever the level of it :-)
		 */

		if (!$service["service_description"]) {
			$service["service_description"] = getMyServiceAlias($service['service_template_model_stm_id']);
		} else {
			$service["service_description"] = str_replace('#S#', "/", $service["service_description"]);
			$service["service_description"] = str_replace('#BS#', "\\", $service["service_description"]);
		}

		/**
		 * TPL List
		 */
		$tplArr = array();
		$tplStr = null;
		$tplArr = getMyServiceTemplateModels($service["service_template_model_stm_id"]);
		if (count($tplArr))
			foreach($tplArr as $key => $value){
				$value = str_replace('#S#', "/", $value);
				$value = str_replace('#BS#', "\\", $value);
				$tplStr .= "&nbsp;->&nbsp;<a href='main.php?p=60206&o=c&service_id=".$key."'>".$value."</a>";
			}

		/**
		 * Get service intervals in seconds
		 */
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

		if ((isset($ehiCache[$service["host_id"]]) && $ehiCache[$service["host_id"]])) {
		    $host_icone = "./img/media/" . $mediaObj->getFilename($ehiCache[$service["host_id"]]);
		} elseif ($icone = $host_method->replaceMacroInString($service["host_id"], getMyHostExtendedInfoImage($service["host_id"], "ehi_icon_image", 1))) {
			$host_icone = "./img/media/" . $icone;
		} else {
			$host_icone = "./img/icones/16x16/server_network.gif";
		}

	    if (isset($service['esi_icon_image']) && $service['esi_icon_image']) {
			$svc_icon = "./img/media/" . $mediaObj->getFilename($service['esi_icon_image']);
		} elseif ($icone = $mediaObj->getFilename(getMyServiceExtendedInfoField($service["service_id"], "esi_icon_image"))) {
			$svc_icon = "./img/media/" . $icone;
		} else {
			$svc_icon = "./img/icones/16x16/gear.gif";
		}

        $elemArr[$i] = array(	"MenuClass"			=> "list_".($service["nbr"]>1 ? "three" : $style),
                                "RowMenu_select"	=> $selectedElements->toHtml(),
                                "RowMenu_name"		=> $service["host_name"],
                                "RowMenu_icone"		=> $host_icone,
                                "RowMenu_sicon"     => $svc_icon,
                                "RowMenu_link"		=> "?p=60101&o=c&host_id=".$service['host_id'],
                                "RowMenu_link2"		=> "?p=".$p."&o=c&service_id=".$service['service_id'],
                                "RowMenu_parent"	=> $tplStr,
                                "RowMenu_retry"		=> "$normal_check_interval $normal_units / $retry_check_interval $retry_units",
                                "RowMenu_desc"		=> $service["service_description"],
                                "RowMenu_status"	=> $service["service_activate"] ? _("Enabled") : _("Disabled"),
                                "RowMenu_options"	=> $moptions);
		$fgHost["print"] ? null : $elemArr[$i]["RowMenu_name"] = null;
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

	$o1 = $form->getElement('o1');
	$o1->setValue(NULL);

	$o2 = $form->getElement('o2');
	$o2->setValue(NULL);

	$tpl->assign('limit', $limit);

	/*
	 * Apply a template definition
	 */

	$searchH = str_replace("#S#", "/", $searchH);
	$searchH = str_replace("#BS#", "\\", $searchH);
	$searchS = str_replace("#S#", "/", $searchS);
	$searchS = str_replace("#BS#", "\\", $searchS);

	if (isset($searchH) && $searchH) {
        $searchH = html_entity_decode($searchH);
        $searchH = stripslashes(str_replace('"', "&quot;", $searchH));
	}
	if (isset($searchS) && $searchS) {
	    $searchS = html_entity_decode($searchS);
        $searchS = stripslashes(str_replace('"', "&quot;", $searchS));
	}
	$tpl->assign("searchH", $searchH);
	$tpl->assign("searchS", $searchS);
    $tpl->assign("hostgroupsFilter", $hostgroupsFilter);
	$tpl->assign("templateFilter", $templateFilter);
	$tpl->assign("statusFilter", $statusFilter);

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('Hosts', _("Hosts"));
    $tpl->assign('Hostgroups', _("HostGroups"));
	$tpl->assign('ServiceTemplates', _("Templates"));
	$tpl->assign('ServiceStatus', _("Status"));
	$tpl->assign('Services', _("Services"));
	$tpl->assign('Search', _("Search"));
	$tpl->display("listService.ihtml");
?>
