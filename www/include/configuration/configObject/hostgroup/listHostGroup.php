<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

if (!isset($oreon)) {
    exit();
}

include("./include/common/autoNumLimit.php");

/*
 * Object init
 */
$mediaObj = new CentreonMedia($pearDB);

/*
 * start quickSearch form
 */
$advanced_search = 0;
include_once("./include/common/quickSearch.php");

/*
 * Search
 */
$SearchTool = NULL;
if (isset($search) && $search) {
    $SearchTool = " (hg_name LIKE '%".$pearDB->escape($search)."%' OR hg_alias LIKE '%".$pearDB->escape($search)."%') AND ";
}

/*
 *  Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$centreon->cache->initHostGroupCache($pearDB);

/* Access level */
($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';
$tpl->assign('mode_access', $lvl_access);

/*
 * start header menu
 */
$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_desc", _("Description"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_hostAct", _("Enabled Hosts"));
$tpl->assign("headerMenu_hostDeact", _("Disabled Hosts"));
$tpl->assign("headerMenu_hostgroupAct", _("Enabled HostGroups"));
$tpl->assign("headerMenu_hostgroupDeact", _("Disabled HostGroups"));
$tpl->assign("headerMenu_options", _("Options"));

/*
 * Hostgroup list
 */

$rq = "SELECT SQL_CALC_FOUND_ROWS hg_id, hg_name, hg_alias, hg_activate, hg_icon_image
           FROM hostgroup
           WHERE $SearchTool hg_id NOT IN (SELECT hg_child_id FROM hostgroup_hg_relation) ".
    $acl->queryBuilder('AND', 'hg_id', $hgString).
" ORDER BY hg_name LIMIT ".$num * $limit .", $limit";
$DBRESULT = $pearDB->query($rq);

/*
 * Pagination
 */
$rows = $pearDB->numberRows();
include("./include/common/checkPagination.php");

$search = tidySearchKey($search, $advanced_search);

$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
/*
 * Different style between each lines
 */
$style = "one";

/*
 * Fill a tab with a mutlidimensionnal Array we put in $tpl
 */
$elemArr = array();
for ($i = 0; $hg = $DBRESULT->fetchRow(); $i++) {
    $selectedElements = $form->addElement('checkbox', "select[".$hg['hg_id']."]");
    $moptions = "";
    if ($hg["hg_activate"]) {
        $moptions .= "<a href='main.php?p=".$p."&hg_id=".$hg['hg_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
    } else {
        $moptions .= "<a href='main.php?p=".$p."&hg_id=".$hg['hg_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
    }
    $moptions .= "&nbsp;";
    $moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$hg['hg_id']."]'></input>";

    /*
     * Check Nbr of Host / hg
     */
    $nbrhostAct = array();
    $nbrhostDeact = array();
    $nbrhostgroupAct = array();
    $nbrhostgroupDeact = array();

    $aclFrom = "";
    $aclCond = "";
    if (!$oreon->user->admin) {
        $aclFrom = ", $aclDbName.centreon_acl acl ";
        $aclCond = " AND h.host_id = acl.host_id
                         AND acl.group_id IN (".$acl->getAccessGroupsString().") ";
    }
    $rq = "SELECT h.host_id, h.host_activate
               FROM hostgroup_relation hgr, host h $aclFrom
               WHERE hostgroup_hg_id = '".$hg['hg_id']."'
               AND h.host_id = hgr.host_host_id
               AND h.host_register = '1' $aclCond";
    $DBRESULT2 = $pearDB->query($rq);
    $nbrhostActArr = array();
    $nbrhostDeactArr = array();
    while ($row = $DBRESULT2->fetchRow()) {
        if ($row['host_activate']) {
            $nbrhostActArr[$row['host_id']] = true;
        } else {
            $nbrhostDeactArr[$row['host_id']] = true;
        }
    }
    $nbrhostAct = count($nbrhostActArr);
    $nbrhostDeact = count($nbrhostDeactArr);

    $rq = "SELECT COUNT(*) as nbr FROM hostgroup_hg_relation hgr, hostgroup WHERE hg_parent_id = '".$hg['hg_id']."' AND hostgroup.hg_id = hgr.hg_child_id AND hostgroup.hg_activate = '1'";
    $DBRESULT2 = $pearDB->query($rq);
    $nbrhostgroupAct = $DBRESULT2->fetchRow();

    $rq = "SELECT COUNT(*) as nbr FROM hostgroup_hg_relation hgr, hostgroup WHERE hg_parent_id = '".$hg['hg_id']."' AND hostgroup.hg_id = hgr.hg_child_id AND hostgroup.hg_activate = '0'";
    $DBRESULT2 = $pearDB->query($rq);
    $nbrhostgroupDeact = $DBRESULT2->fetchRow();

    if ($hg['hg_icon_image'] != "") {
        $hgIcone = "./img/media/" . $mediaObj->getFilename($hg['hg_icon_image']);
    } else {
        $hgIcone = "./img/icones/16x16/clients.gif";
    }
    $elemArr[$i] = array("MenuClass"=>"list_".$style,
                         "RowMenu_select"=>$selectedElements->toHtml(),
                         "RowMenu_name"=>$hg["hg_name"],
                         "RowMenu_link"=>"?p=".$p."&o=c&hg_id=".$hg['hg_id'],
                         "RowMenu_desc"=>html_entity_decode($hg["hg_alias"]),
                         "RowMenu_status"=>$hg["hg_activate"] ? _("Enabled") : _("Disabled"),
                         "RowMenu_hostAct"=>$nbrhostAct,
                         "RowMenu_icone" => $hgIcone,
                         "RowMenu_hostDeact"=>$nbrhostDeact,
                         "RowMenu_hostgroupAct"=>$nbrhostgroupAct["nbr"],
                         "RowMenu_hostgroupDeact"=>$nbrhostgroupDeact["nbr"],
                         "RowMenu_options"=>$moptions);
    /*
     * Switch color line
     */
    $style != "two" ? $style = "two" : $style = "one";
 }
$tpl->assign("elemArr", $elemArr);

/*
 * Different messages we put in the template
 */
$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));

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
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 4) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs1);
$form->setDefaults(array('o1' => NULL));

$attrs2 = array(
                'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 4) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
$form->addElement('select', 'o2', NULL, array(NULL => _("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs2);
$form->setDefaults(array('o2' => NULL));

$o1 = $form->getElement('o1');
$o1->setValue(NULL);
$o1->setSelected(NULL);

$o2 = $form->getElement('o2');
$o2->setValue(NULL);
$o2->setSelected(NULL);

$tpl->assign('limit', $limit);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listHostGroup.ihtml");

?>
