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

if (!$oreon->user->admin) {
    if ($hg_id && false === strpos($hgString, "'".$hg_id."'")) {
        $msg = new CentreonMsg();
        $msg->setImage("./img/icons/warning.png");
        $msg->setTextStyle("bold");
        $msg->setText(_('You are not allowed to access this host group'));
        return null;
    }
}

$initialValues = array();

/*
 * Database retrieve information for HostGroup
	 */
$hg = array();
if (($o == "c" || $o == "w") && $hg_id) {
    $DBRESULT = $pearDB->query("SELECT * FROM hostgroup WHERE hg_id = '".$hg_id."' LIMIT 1");
    /*
     * Set base value
     */
    $hg = array_map("myDecode", $DBRESULT->fetchRow());


    /*
     * Get Parent Groups
     */
    $hostGroupParents = array();
    //$hostGroupParents = getHGParents($hg_id, $hostGroupParents, $pearDB);

    /*
     *  Set HostGroup Childs
     */
    $DBRESULT = $pearDB->query("SELECT DISTINCT host.host_id FROM hostgroup_relation, hostgroup, host WHERE hostgroup_relation.host_host_id = host.host_id AND hostgroup_relation.hostgroup_hg_id = hostgroup.hg_id AND hostgroup.hg_id = '".$hg_id."' ORDER BY host.host_name");
    for ($i = 0; $hosts = $DBRESULT->fetchRow();) {
        if (!$oreon->user->admin && false === strpos($hoststring, "'".$hosts['host_id']."'")) {
            $initialValues['hg_hosts'][] = $hosts['host_id'];
        } else {
            $hg["hg_hosts"][$i] = $hosts["host_id"];
            $i++;
        }
    }
    $DBRESULT->free();
    unset($hosts);

    /*
     *  Set HostGroup Childs
     */
    /*
    $DBRESULT = $pearDB->query("SELECT DISTINCT hg_child_id FROM hostgroup_hg_relation hgr, hostgroup hg WHERE hgr.hg_parent_id = '".$hg_id."' AND hgr.hg_child_id = hg.hg_id ORDER BY hg.hg_name");
    for ($i = 0; $hgs = $DBRESULT->fetchRow(); $i++) {
        $hg["hg_hg"][$i] = $hgs["hg_child_id"];
    }
    $DBRESULT->free();
    unset($hgs);
    */
}

/*
 * Hosts comes from DB -> Store in $hosts Array
 */
$aclFrom = "";
$aclCond = "";
if (!$centreon->user->admin) {
    $aclFrom = ", $aclDbName.centreon_acl acl ";
    $aclCond = " AND h.host_id = acl.host_id
                 AND acl.group_id IN (".$acl->getAccessGroupsString().") ";
}
$hosts = array();
$DBRESULT = $pearDB->query("SELECT DISTINCT h.host_id, h.host_name
                                FROM host h $aclFrom
                                WHERE host_register = '1' $aclCond
                                ORDER BY host_name");
while ($host = $DBRESULT->fetchRow()) {
    $hosts[$host["host_id"]] = $host["host_name"];
}
$DBRESULT->free();
unset($host);

/*
 * Hostgroups comes from DB -> Store in $hosts Array
 */

$EDITCOND = "";
if ($o == "w" || $o == "c") {
    $EDITCOND = " WHERE `hg_id` != '".$hg_id."' ";
}

$hostGroups = array();
$DBRESULT = $pearDB->query("SELECT hg_id, hg_name FROM hostgroup $EDITCOND ORDER BY hg_name");
while ($hgs = $DBRESULT->fetchRow()) {
    if (!isset($hostGroupParents[$hgs["hg_id"]])) {
        $hostGroups[$hgs["hg_id"]] = $hgs["hg_name"];
    }
}
$DBRESULT->free();
unset($hgs);

/*
 * Contact Groups comes from DB -> Store in $cgs Array
 */
$cgs = array();
$DBRESULT = $pearDB->query("SELECT cg_id, cg_name FROM contactgroup ORDER BY cg_name");
while ($cg = $DBRESULT->fetchRow()) {
    $cgs[$cg["cg_id"]] = $cg["cg_name"];
}
$DBRESULT->free();
unset($cg);

/*
 * IMG comes from DB -> Store in $extImg Array
 */
$extImg = array();
$extImg = return_image_list(1);
$extImgStatusmap = array();
$extImgStatusmap = return_image_list(2);

/*
 * Define Templatse
 */
$attrsText      = array("size"=>"30");
$attrsTextLong  = array("size"=>"50");
$attrsAdvSelect = array("style" => "width: 300px; height: 220px;");
$attrsTextarea  = array("rows"=>"4", "cols"=>"60");
$eTemplate  = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';
$attrHosts = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_host&action=list',
    'multiple' => true,
    'linkedObject' => 'centreonHost'
);
$attrHostgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup&action=list',
    'multiple' => true,
    'linkedObject' => 'centreonHostgroups'
);

/*
 * Create formulary
 */
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add a Host Group"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify a Host Group"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View a Host Group"));
}

/*
 * Contact basic information
 */
$form->addElement('header', 'information', _("General Information"));
$form->addElement('text', 'hg_name', _("Host Group Name"), $attrsText);
$form->addElement('text', 'hg_alias', _("Alias"), $attrsText);

/*
 * Hosts Selection
 */
$attrHost1 = array_merge(
    $attrHosts,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_host&action=defaultValues&target=hostgroups&field=hg_hosts&id=' . $hg_id)
);
$form->addElement('select2', 'hg_hosts', _("Linked Hosts"), array(), $attrHost1);

$attrHostgroup1 = array_merge(
    $attrHostgroups,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup&action=defaultValues&target=hostgroups&field=hg_hg&id=' . $hg_id)
);
$form->addElement('select2', 'hg_hg', _("Linked Host Groups"), array(), $attrHostgroup1);

/*
 * Extended information
 */
$form->addElement('header', 'extended', _("Extended Information"));
$form->addElement('text', 'hg_notes', _("Notes"), $attrsText);
$form->addElement('text', 'hg_notes_url', _("Notes URL"), $attrsTextLong);
$form->addElement('text', 'hg_action_url', _("Action URL"), $attrsTextLong);
$form->addElement('select', 'hg_icon_image', _("Icon"), $extImg, array("onChange"=>"showLogo('hg_icon_image_img',this.form.elements['hg_icon_image'].value)"));
$form->addElement('select', 'hg_map_icon_image', _("Map Icon"), $extImg, array("onChange"=>"showLogo('hg_map_icon_image_img',this.form.elements['hg_map_icon_image'].value)"));

/*
 * Further informations
 */
$form->addElement('text', 'hg_rrd_retention', _('RRD retention'), array('size' => 5));
$form->addElement('text', 'geo_coords', _("Geo coordinates"), $attrsText);
$form->addElement('header', 'furtherInfos', _("Additional Information"));
$form->addElement('textarea', 'hg_comment', _("Comments"), $attrsTextarea);

$hgActivation[] = HTML_QuickForm::createElement('radio', 'hg_activate', null, _("Enabled"), '1');
$hgActivation[] = HTML_QuickForm::createElement('radio', 'hg_activate', null, _("Disabled"), '0');
$form->addGroup($hgActivation, 'hg_activate', _("Status"), '&nbsp;');
$form->setDefaults(array('hg_activate' => '1'));

$form->addElement('hidden', 'hg_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

$init = $form->addElement('hidden', 'initialValues');
$init->setValue(serialize($initialValues));

/*
 * Form Rules
 */
function myReplace()
{
    global $form;
    $ret = $form->getSubmitValues();
    return (str_replace(" ", "_", $ret["hg_name"]));
}
$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('hg_name', 'myReplace');
$form->addRule('hg_name', _("Compulsory Name"), 'required');
$form->addRule('hg_alias', _("Compulsory Alias"), 'required');

if (!$oreon->user->admin) {
    //$form->addRule('hg_hosts', _('Compulsory hosts (due to ACL restrictions that could prevent you from seeing this host group)'), 'required');
}

$form->registerRule('exist', 'callback', 'testHostGroupExistence');
$form->addRule('hg_name', _("Name is already in use"), 'exist');
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

if ($o == "w") {
    /*
     * Just watch a HostGroup information
     */
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&hg_id=".$hg_id."'"));
    }
    $form->setDefaults($hg);
    $form->freeze();
} elseif ($o == "c") {
    /*
     * Modify a HostGroup information
     */
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($hg);
} elseif ($o == "a") {
    /*
     * Add a HostGroup information
     */
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$tpl->assign('p', $p);
$tpl->assign("initJS", "<script type='text/javascript'>
							jQuery(function () {
							initAutoComplete('Form','city_name','sub');
							});</script>");
$tpl->assign('javascript', "<script type='text/javascript' src='./include/common/javascript/showLogo.js'></script>");
$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"');

# prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
}
$tpl->assign("helptext", $helptext);

$valid = false;
if ($form->validate()) {
    $hgObj = $form->getElement('hg_id');
    if ($form->getSubmitValue("submitA")) {
        $hgObj->setValue(insertHostGroupInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateHostGroupInDB($hgObj->getValue());
    }
    $o = null;
    $hgObj = $form->getElement('hg_id');
    $valid = true;
}

if ($valid) {
    require_once($path."listHostGroup.php");
} else {
    /*
     * Apply a template definition
     */
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('days', _('days'));
    $tpl->assign('o', $o);
    $tpl->assign('topdoc', _("Documentation"));
    $tpl->display("formHostGroup.ihtml");
}
