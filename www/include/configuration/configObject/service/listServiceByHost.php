<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

if (!isset($centreon)) {
    exit();
}

include_once("./class/centreonUtils.class.php");

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

$template = null;
if (isset($_POST["template"])) {
    $template = $_POST["template"];
} elseif (isset($_GET["template"])) {
    $template = $_GET["template"];
}

if (isset($_POST["hostgroups"])) {
    $hostgroups = $_POST["hostgroups"];
    $_SESSION['configuration_default_hostgroups'] = $hostgroups;
} elseif (isset($_GET["hostgroups"])) {
    $hostgroups = $_GET["hostgroups"];
    $_SESSION['configuration_default_hostgroups'] = $hostgroups;
} elseif (isset($_SESSION['configuration_default_hostgroups'])) {
    $hostgroups = $_SESSION['configuration_default_hostgroups'];
} else {
    $hostgroups = null;
}

$hostStatus = 0;
if (!is_null($template)) {
    if (isset($_POST["statusHostFilter"]) || isset($_GET["statusHostFilter"])) {
        $hostStatus = 1;
    } else {
        $hostStatus = 0;
    }
} elseif (isset($_SESSION["status_host_filter"])) {
    $hostStatus = $_SESSION["status_host_filter"];
}
$_SESSION["status_host_filter"] = $hostStatus;

/*
 * Host Status Filter
 */
$hostStatusChecked = "";
$sqlFilterCase2 = "AND host.host_activate = '1'";
if ($hostStatus == 1) {
    $hostStatusChecked = "checked";
    $sqlFilterCase2 = "";
}

if (isset($_POST["status"])) {
    $status = $_POST["status"];
} elseif (isset($_GET["status"])) {
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
$statusFilter = "<option value=''".(($status == -1) ? " selected" : "")."> </option>";
$statusFilter .= "<option value='1'".(($status == 1) ? " selected" : "").">"._("Enable")."</option>";
$statusFilter .= "<option value='0'".(($status == 0 && $status != '') ? " selected" : "").">"._("Disable")."</option>";

$sqlFilterCase = "";
if ($status == 1) {
    $sqlFilterCase = " AND sv.service_activate = '1' ";
} elseif ($status == 0 && $status != "") {
    $sqlFilterCase = " AND sv.service_activate = '0' ";
}

require_once "./class/centreonHost.class.php";

/*
 * Init Objects
 */
$host_method = new CentreonHost($pearDB);
$service_method = new CentreonService($pearDB);

$searchH = '';
$searchH_SQL = '';
$searchH_GET = '';
$tmp_search_h = '';

if (isset($_GET['search_h'])) {
    $tmp_search_h = $_GET['search_h'];
    $centreon->svc_host_search = $tmp_search_h;
}
if (isset($_POST["searchH"])) {
    $tmp_search_h = $_POST["searchH"];
    $centreon->svc_host_search = $tmp_search_h;
} elseif (isset($centreon->svc_host_search) && $centreon->svc_host_search != '') {
    $tmp_search_h = $centreon->svc_host_search;
}

if ($tmp_search_h != '') {
    $searchH = $tmp_search_h;
    $searchH_GET = $tmp_search_h;
    $searchH_SQL = "AND (host.host_name LIKE '%". $pearDB->escape($tmp_search_h) ."%' OR host_alias LIKE '%". $pearDB->escape($tmp_search_h) ."%' OR host_address LIKE '%". $pearDB->escape($tmp_search_h) ."%')";
}

$searchS = '';
$searchS_SQL = '';
$searchS_GET = '';
$tmp_search_s = '';

if (isset($_GET['search_s'])) {
    $tmp_search_s = $_GET['search_s'];
    $centreon->svc_svc_search = $tmp_search_s;
} elseif (isset($_POST["searchS"])) {
    $tmp_search_s = $_POST["searchS"];
    $centreon->svc_svc_search = $tmp_search_s;
} elseif (isset($centreon->svc_svc_search)) {
    $tmp_search_s = $centreon->svc_svc_search;
}

if ($tmp_search_s != '') {
    $searchS = $tmp_search_s;
    $searchS_GET = $tmp_search_s;
    $searchS_SQL = "AND (sv.service_alias LIKE '%" . $pearDB->escape($tmp_search_s) . "%' OR sv.service_description LIKE '%" . $pearDB->escape($tmp_search_s) . "%')";
}

include("./include/common/autoNumLimit.php");

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

/* Access level */
($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';
$tpl->assign('mode_access', $lvl_access);

/*
 * start header menu
 */
$tpl->assign("headerMenu_name", _("Host"));
$tpl->assign("headerMenu_desc", _("Service"));
$tpl->assign("headerMenu_retry", _("Scheduling"));
$tpl->assign("headerMenu_parent", _("Template"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_options", _("Options"));

$aclfilter = "";
$distinct = "";
if (!$centreon->user->admin) {
    $aclfilter = " AND host.host_id = acl.host_id
                   AND acl.service_id = sv.service_id
                   AND acl.group_id IN (".$acl->getAccessGroupsString().") ";
    $distinct = " DISTINCT ";
}

/*
 * Host/service list
 */
$rq_body =  "esi.esi_icon_image, sv.service_id, sv.service_description, sv.service_activate, sv.service_template_model_stm_id, " .
        "host.host_id, host.host_name, host.host_template_model_htm_id, sv.service_normal_check_interval, " .
        "sv.service_retry_check_interval, sv.service_max_check_attempts " .
        "FROM service sv, host" .
        ((isset($hostgroups) && $hostgroups) ? ", hostgroup_relation hogr, " : ", ") .
        ($centreon->user->admin ? "" : $acldbname.".centreon_acl acl, ").
        "host_service_relation hsr " .
        "LEFT JOIN extended_service_information esi ON esi.service_service_id = hsr.service_service_id " .
        "WHERE host.host_register = '1' $searchH_SQL $sqlFilterCase2 " . " AND host.host_id = hsr.host_host_id AND hsr.service_service_id = sv.service_id " .
                "AND sv.service_register = '1' $searchS_SQL $sqlFilterCase " .
                ((isset($template) && $template) ? " AND service_template_model_stm_id = '$template' " : "") .
                ((isset($hostgroups) && $hostgroups) ? " AND hogr.hostgroup_hg_id = '$hostgroups' AND hogr.host_host_id = host.host_id " : "") .
                $aclfilter .
                "ORDER BY host.host_name, service_description";

$DBRESULT = $pearDB->query("SELECT SQL_CALC_FOUND_ROWS " . $distinct . $rq_body . " LIMIT " . $num * $limit . ", " . $limit);
$rows = $pearDB->numberRows();

if (!($DBRESULT->numRows())) {
    $DBRESULT = $pearDB->query("SELECT " . $distinct . $rq_body . " LIMIT " . (floor($rows / $limit) * $limit) . ", " . $limit);
}

include("./include/common/checkPagination.php");
$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);

/**
 * Different style between each lines
 */
$style = "one";

/**
 * Fill a tab with a mutlidimensionnal Array we put in $tpl
 */
$elemArr = array();
$fgHost = array("value"=>null, "print"=>null);

$interval_length = $centreon->optGen['interval_length'];

for ($i = 0; $service = $DBRESULT->fetchRow(); $i++) {
    /*
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
        $moptions .= "<a href='main.php?p=".$p."&service_id=".$service['service_id']."&o=u&limit=".$limit."&num=".$num."&hostgroups=".$hostgroups."&template=$template&status=".$status."'><img src='img/icons/disabled.png' class='ico-14 margin_right' border='0' alt='"._("Disabled")."'></a>";
    } else {
        $moptions .= "<a href='main.php?p=".$p."&service_id=".$service['service_id']."&o=s&limit=".$limit."&num=".$num."&hostgroups=".$hostgroups."&template=$template&status=".$status."'><img src='img/icons/enabled.png' class='ico-14 margin_right' border='0' alt='"._("Enabled")."'></a>";
    }
    $moptions .= "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" onKeyUp=\"syncInputField(this.name, this.value);\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$service['service_id']."]'></input>";

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
    if (count($tplArr)) {
        foreach ($tplArr as $key => $value) {
            $tplStr .= "&nbsp;->&nbsp;<a href='main.php?p=60206&o=c&service_id=".$key."'>".$value."</a>";
        }
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
        $host_icone = "./img/icons/host.png";
    }

    if (isset($service['esi_icon_image']) && $service['esi_icon_image']) {
        $svc_icon = "./img/media/" . $mediaObj->getFilename($service['esi_icon_image']);
    } elseif ($icone = $mediaObj->getFilename(getMyServiceExtendedInfoField($service["service_id"], "esi_icon_image"))) {
        $svc_icon = "./img/media/" . $icone;
    } else {
        $svc_icon = "./img/icons/service.png";
    }

    $elemArr[$i] = array(   "MenuClass"             => "list_".($service["nbr"]>1 ? "three" : $style),
                            "RowMenu_select"    => $selectedElements->toHtml(),
                            "RowMenu_name"      => CentreonUtils::escapeSecure($service["host_name"]),
                            "RowMenu_icone"         => $host_icone,
                            "RowMenu_sicon"     => $svc_icon,
                            "RowMenu_link"      => "?p=60101&o=c&host_id=".$service['host_id'],
                            "RowMenu_link2"         => "?p=".$p."&o=c&service_id=".$service['service_id'],
                            "RowMenu_parent"    => CentreonUtils::escapeSecure($tplStr),
                            "RowMenu_retry"         => CentreonUtils::escapeSecure("$normal_check_interval $normal_units / $retry_check_interval $retry_units"),
                            "RowMenu_desc"      => CentreonUtils::escapeSecure($service["service_description"]),
                            "RowMenu_status"    => $service["service_activate"] ? _("Enabled") : _("Disabled"),
                            "RowMenu_badge"     => $service["service_activate"] ? "service_ok" : "service_critical",
                            "RowMenu_options"   => $moptions);
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

foreach (array('o1', 'o2') as $option) {
    $attrs1 = array(
        'onchange'=>"javascript: " .
                " var bChecked = isChecked(); ".
                " if (this.form.elements['".$option."'].selectedIndex != 0 && !bChecked) {".
                " alert('"._("Please select one or more items")."'); return false;} " .
                "if (this.form.elements['".$option."'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
                " 	setO(this.form.elements['".$option."'].value); submit();} " .
                "else if (this.form.elements['".$option."'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
                " 	setO(this.form.elements['".$option."'].value); submit();} " .
                "else if (this.form.elements['".$option."'].selectedIndex == 6 && confirm('"._("Are you sure you want to detach the service ?")."')) {" .
                " 	setO(this.form.elements['".$option."'].value); submit();} " .
                "else if (this.form.elements['".$option."'].selectedIndex == 3 || this.form.elements['".$option."'].selectedIndex == 4 ||this.form.elements['".$option."'].selectedIndex == 5){" .
                " 	setO(this.form.elements['".$option."'].value); submit();} " .
                "this.form.elements['".$option."'].selectedIndex = 0");
    $form->addElement('select', $option, null, array(null=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "mc"=>_("Massive Change"), "ms"=>_("Enable"), "mu"=>_("Disable"), "dv"=>_("Detach")), $attrs1);
    $o1 = $form->getElement($option);
    $o1->setValue(null);
}

$tpl->assign('limit', $limit);

/*
 * Apply a template definition
 */
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
$tpl->assign("statusHostFilter", $statusHostFilter);
$tpl->assign("statusFilter", $statusFilter);
$tpl->assign("hostStatusChecked", $hostStatusChecked);

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('Hosts', _("Hosts"));
$tpl->assign('ServiceTemplates', _("Templates"));
$tpl->assign('ServiceStatus', _("Status"));
$tpl->assign('HostStatus', _("Disabled hosts"));
$tpl->assign('Services', _("Services"));
$tpl->assign('Search', _("Search"));
$tpl->display("listService.ihtml");
