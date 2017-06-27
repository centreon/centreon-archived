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

$mediaObj = new CentreonMedia($pearDB);

if (isset($_POST["hostgroups"])) {
    $searchHG = $_POST["hostgroups"];
    $centreon->svc_hostgroup_search = $searchHG;
    if ($_POST["searchH"] != "") {
        $search_type_host = 1;
        $centreon->search_type_host = 1;
    }
} else {
    if (isset($centreon->svc_hostgroup_search) && $centreon->svc_hostgroup_search) {
        $searchHG = $centreon->svc_hostgroup_search;
    } else {
        $searchHG = null;
    }
}

if (isset($_POST["searchS"])) {
    $searchS = $_POST["searchS"];
    $centreon->svc_svc_search = $searchS;
    if ($_POST["searchS"] != "") {
        $search_type_service = 1;
        $centreon->search_type_service = 1;
    }
} else {
    if (isset($centreon->svc_svc_search) && $centreon->svc_svc_search) {
        $searchS = $centreon->svc_svc_search;
    } else {
        $searchS = null;
    }
}

if (isset($_POST["template"])) {
    $template = $_POST["template"];
} elseif (isset($_GET["template"])) {
    $template = $_GET["template"];
} else {
    $template = null;
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
$DBRESULT->closeCursor();

/*
 * Status Filter
 */
$statusFilter = "<option value=''".(($status == -1) ? " selected" : "")."> </option>";
;
$statusFilter .= "<option value='1'".(($status == 1) ? " selected" : "").">"._("Enable")."</option>";
$statusFilter .= "<option value='0'".(($status == 0 && $status != '') ? " selected" : "").">"._("Disable")."</option>";

$sqlFilterCase = "";
if ($status == 1) {
    $sqlFilterCase = " AND sv.service_activate = '1' ";
} elseif ($status == 0 && $status != "") {
    $sqlFilterCase = " AND sv.service_activate = '0' ";
}

include("./include/common/autoNumLimit.php");

$rows = 0;
$tmp = null;
$tmp2 = null;
$searchHG = $pearDB->escape($searchHG);
$searchS = $pearDB->escape($searchS);

$aclfrom = "";
$aclcond = "";
$distinct = "";
if (!$centreon->user->admin) {
    $aclfrom = ", $acldbname.centreon_acl acl ";
    $aclcond = " AND sv.service_id = acl.service_id
                 AND acl.group_id IN (".$acl->getAccessGroupsString().") ";
    $distinct = " DISTINCT ";
}

/*
 * Due to Description maybe in the Template definition, we have to search if the description could match for each service with a Template.
 */
if ($searchS != "" || $searchHG != "") {
    if ($searchS && !$searchHG) {
        $DBRESULT = $pearDB->query("SELECT $distinct hostgroup_hg_id, sv.service_id, sv.service_description, service_template_model_stm_id " .
                                    "FROM service sv, host_service_relation hsr $aclfrom" .
                                    "WHERE sv.service_register = '1' $sqlFilterCase " .
                                    "	AND hsr.service_service_id = sv.service_id " . $aclcond .
                                    "	AND hsr.host_host_id IS NULL " .
                                    "	AND (sv.service_description LIKE '%$searchS%')".((isset($template) && $template) ? " AND service_template_model_stm_id = '$template' " : ""));
        while ($service = $DBRESULT->fetchRow()) {
            if (!isset($tab_buffer[$service["service_id"]])) {
                $tmp ? $tmp .= ", ".$service["service_id"] : $tmp = $service["service_id"];
            }
            $tmp2 ? $tmp2 .= ", ".$service["hostgroup_hg_id"] : $tmp2 = $service["hostgroup_hg_id"];
            $tab_buffer[$service["service_id"]] = $service["service_id"];
            $rows++;
        }
    } elseif (!$searchS && $searchHG) {
        $DBRESULT = $pearDB->query("SELECT $distinct hostgroup_hg_id, sv.service_id, sv.service_description, service_template_model_stm_id " .
                                    "FROM service sv, host_service_relation hsr, hostgroup hg $aclfrom" .
                                    "WHERE sv.service_register = '1' $sqlFilterCase " .
                                    "	AND hsr.service_service_id = sv.service_id " . $aclcond .
                                    "	AND hsr.host_host_id IS NULL " .
                                    "	AND (hg.hg_name LIKE '%$searchHG%')" .
                                    "	AND hsr.hostgroup_hg_id = hg.hg_id".((isset($template) && $template) ? " AND service_template_model_stm_id = '$template' " : ""));
        while ($service = $DBRESULT->fetchRow()) {
            $tmp ? $tmp .= ", ".$service["service_id"] : $tmp = $service["service_id"];
            $tmp2 ? $tmp2 .= ", ".$service["hostgroup_hg_id"] : $tmp2 = $service["hostgroup_hg_id"];
            $rows++;
        }
    } else {
        $DBRESULT = $pearDB->query("SELECT $distinct hostgroup_hg_id, sv.service_id, sv.service_description, service_template_model_stm_id " .
                                    "FROM service sv, host_service_relation hsr, hostgroup hg $aclfrom" .
                                    "WHERE sv.service_register = '1' $sqlFilterCase " .
                                    "	AND hsr.service_service_id = sv.service_id " . $aclcond .
                                    "	AND hsr.host_host_id IS NULL " .
                                    "	AND hg.hg_name LIKE '%$searchHG%'" .
                                    "	AND sv.service_description LIKE '%$searchS%'" .
                                    "	AND hsr.hostgroup_hg_id = hg.hg_id".((isset($template) && $template) ? " AND service_template_model_stm_id = '$template' " : ""));
        while ($service = $DBRESULT->fetchRow()) {
            $tmp ? $tmp .= ", ".$service["service_id"] : $tmp = $service["service_id"];
            $tmp2 ? $tmp2 .= ", ".$service["hostgroup_hg_id"] : $tmp2 = $service["hostgroup_hg_id"];
            $rows++;
        }
    }
} else {
    $DBRESULT = $pearDB->query("SELECT $distinct sv.service_description
                                FROM service sv, host_service_relation hsr $aclfrom
                                WHERE service_register = '1' $sqlFilterCase ". ((isset($template) && $template) ? " AND service_template_model_stm_id = '$template' " : "") . " AND hsr.service_service_id = sv.service_id AND hsr.host_host_id IS NULL $aclcond");
    $rows = $DBRESULT->rowCount();
}

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
$tpl->assign("headerMenu_name", _("HostGroup"));
$tpl->assign("headerMenu_desc", _("Service"));
$tpl->assign("headerMenu_retry", _("Scheduling"));
$tpl->assign("headerMenu_parent", _("Template"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_options", _("Options"));

/*
 * HostGroup/service list
 */
if ($searchS || $searchHG) {
    $rq = "SELECT $distinct @nbr:=(SELECT COUNT(*) FROM host_service_relation WHERE service_service_id = sv.service_id GROUP BY sv.service_id ) AS nbr, sv.service_id, sv.service_description, sv.service_activate, sv.service_template_model_stm_id, hg.hg_id, hg.hg_name FROM service sv, hostgroup hg, host_service_relation hsr $aclfrom WHERE sv.service_register = '1' $sqlFilterCase AND sv.service_id IN (".($tmp ? $tmp : 'NULL').") AND hsr.hostgroup_hg_id IN (".($tmp2 ? $tmp2 : 'NULL').") ". ((isset($template) && $template) ? " AND service_template_model_stm_id = '$template' " : "") . " AND hsr.service_service_id = sv.service_id AND hg.hg_id = hsr.hostgroup_hg_id $aclcond ORDER BY hg.hg_name, sv.service_description LIMIT ".$num * $limit.", ".$limit;
} else {
    $rq = "SELECT $distinct @nbr:=(SELECT COUNT(*) FROM host_service_relation WHERE service_service_id = sv.service_id GROUP BY sv.service_id ) AS nbr, sv.service_id, sv.service_description, sv.service_activate, sv.service_template_model_stm_id, hg.hg_id, hg.hg_name FROM service sv, hostgroup hg, host_service_relation hsr $aclfrom WHERE sv.service_register = '1' $sqlFilterCase ". ((isset($template) && $template) ? " AND service_template_model_stm_id = '$template' " : "") . " AND hsr.service_service_id = sv.service_id AND hg.hg_id = hsr.hostgroup_hg_id $aclcond ORDER BY hg.hg_name, sv.service_description LIMIT ".$num * $limit.", ".$limit;
}
$DBRESULT = $pearDB->query($rq);

$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);

/*
 * Different style between each lines
 */
$style = "one";

/*
 * Fill a tab with a mutlidimensionnal Array we put in $tpl
 */

$interval_length = $centreon->optGen['interval_length'];

$elemArr = array();
$fgHostgroup = array("value" => null, "print" => null);

for ($i = 0; $service = $DBRESULT->fetchRow(); $i++) {
    $moptions = "";
    $fgHostgroup["value"] != $service["hg_name"] ? ($fgHostgroup["print"] = true && $fgHostgroup["value"] = $service["hg_name"]) : $fgHostgroup["print"] = false;
    $selectedElements = $form->addElement('checkbox', "select[".$service['service_id']."]");

    if ($service["service_activate"]) {
        $moptions .= "<a href='main.php?p=".$p."&service_id=".$service['service_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."&template=$template&status=".$status."'><img src='img/icons/disabled.png' class='ico-14 margin_right' border='0' alt='"._("Disabled")."'>";
    } else {
        $moptions .= "<a href='main.php?p=".$p."&service_id=".$service['service_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."&template=$template&status=".$status."'><img src='img/icons/enabled.png' class='ico-14 margin_right' border='0' alt='"._("Enabled")."'></a>";
    }
    $moptions .= "&nbsp;";
    $moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" onKeyUp=\"syncInputField(this.name, this.value);\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$service['service_id']."]'></input>";

    /*
	 * If the description of our Service is in the Template definition, we have to catch it, whatever the level of it :-)
	 */
    if (!$service["service_description"]) {
        $service["service_description"] = getMyServiceAlias($service['service_template_model_stm_id']);
    }

    /*
	 * TPL List
	 */
    $tplArr = array();
    $tplStr = null;
    $tplArr = getMyServiceTemplateModels($service["service_template_model_stm_id"]);
    if (count($tplArr)) {
        foreach ($tplArr as $key => $value) {
            $value = str_replace('#S#', "/", $value);
            $value = str_replace('#BS#', "\\", $value);
            $tplStr .= "&nbsp;>&nbsp;<a href='main.php?p=60206&o=c&service_id=".$key."'>".$value."</a>";
        }
    }

    if (isset($service['esi_icon_image']) && $service['esi_icon_image']) {
        $svc_icon = "./img/media/" . $mediaObj->getFilename($service['esi_icon_image']);
    } elseif ($icone = $mediaObj->getFilename(getMyServiceExtendedInfoField($service["service_id"], "esi_icon_image"))) {
        $svc_icon = "./img/media/" . $icone;
    } else {
        $svc_icon = "./img/icons/service.png ";
    }

    /*
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

    $elemArr[$i] = array(   "MenuClass" => "list_".($service["nbr"] > 1 ? "three" : $style),
                            "RowMenu_select" => $selectedElements->toHtml(),
                            "RowMenu_name" => CentreonUtils::escapeSecure($service["hg_name"]),
                            "RowMenu_link" => "?p=60102&o=c&hg_id=".$service['hg_id'],
                            "RowMenu_link2" => "?p=".$p."&o=c&service_id=".$service['service_id'],
                            "RowMenu_parent" => CentreonUtils::escapeSecure($tplStr),
                            "RowMenu_sicon" => $svc_icon,
                            "RowMenu_retry" =>  CentreonUtils::escapeSecure("$normal_check_interval $normal_units / $retry_check_interval $retry_units"),
                            "RowMenu_attempts" => CentreonUtils::escapeSecure(getMyServiceField($service['service_id'], "service_max_check_attempts")),
                            "RowMenu_desc" => CentreonUtils::escapeSecure($service["service_description"]),
                            "RowMenu_status" => $service["service_activate"] ? _("Enabled") : _("Disabled"),
                            "RowMenu_badge"     => $service["service_activate"] ? "service_ok" : "service_critical",
                            "RowMenu_options" => $moptions);

    $fgHostgroup["print"] ? null : $elemArr[$i]["RowMenu_name"] = null;
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
                        " var bChecked = isChecked(); ".
                        " if (this.form.elements['o1'].selectedIndex != 0 && !bChecked) {".
                        " alert('"._("Please select one or more items")."'); return false;} " .
            "if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "else if (this.form.elements['o1'].selectedIndex == 6 && confirm('"._("Are you sure you want to detach the service ?")."')) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "else if (this.form.elements['o1'].selectedIndex == 7 && confirm('"._("Are you sure you want to detach the service ?")."')) {" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "else if (this.form.elements['o1'].selectedIndex == 3 || this.form.elements['o1'].selectedIndex == 4 ||this.form.elements['o1'].selectedIndex == 5){" .
            " 	setO(this.form.elements['o1'].value); submit();} " .
            "this.form.elements['o1'].selectedIndex = 0");
$form->addElement('select', 'o1', null, array(null=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "mc"=>_("Massive Change"), "ms"=>_("Enable"), "mu"=>_("Disable"), "dv"=>_("Detach host group services"), "mvH"=>_("Move host group's services to hosts")), $attrs1);

$attrs2 = array(
    'onchange'=>"javascript: " .
                        " var bChecked = isChecked(); ".
                        " if (this.form.elements['o2'].selectedIndex != 0 && !bChecked) {".
                        " alert('"._("Please select one or more items")."'); return false;} " .
            "if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "else if (this.form.elements['o2'].selectedIndex == 6 && confirm('"._("Are you sure you want to detach the service ?")."')) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "else if (this.form.elements['o2'].selectedIndex == 7 && confirm('"._("Are you sure you want to detach the service ?")."')) {" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "else if (this.form.elements['o2'].selectedIndex == 3 || this.form.elements['o2'].selectedIndex == 4 ||this.form.elements['o2'].selectedIndex == 5){" .
            " 	setO(this.form.elements['o2'].value); submit();} " .
            "this.form.elements['o2'].selectedIndex = 0");
$form->addElement('select', 'o2', null, array(null=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "mc"=>_("Massive Change"), "ms"=>_("Enable"), "mu"=>_("Disable"), "dv"=>_("Detach host group services"), "mvH"=>_("Move host group's services to hosts")), $attrs2);

$o1 = $form->getElement('o1');
$o1->setValue(null);

$o2 = $form->getElement('o2');
$o2->setValue(null);

$tpl->assign('limit', $limit);

if (isset($searchHG) && $searchHG) {
    $searchHG = html_entity_decode($searchHG);
    $searchHG = stripslashes(str_replace('"', "&quot;", $searchHG));
}
if (isset($searchS) && $searchS) {
    $searchS = html_entity_decode($searchS);
    $searchS = stripslashes(str_replace('"', "&quot;", $searchS));
}
$tpl->assign("hostgroupsFilter", $searchHG);
$tpl->assign("searchS", $searchS);
$tpl->assign("templateFilter", $templateFilter);
$tpl->assign("statusFilter", $statusFilter);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('HostGroups', _("HostGroups"));
$tpl->assign('Services', _("Services"));
$tpl->assign('ServiceTemplates', _("Templates"));
$tpl->assign('ServiceStatus', _("Status"));
$tpl->assign('Search', _("Search"));
$tpl->display("listService.ihtml");
