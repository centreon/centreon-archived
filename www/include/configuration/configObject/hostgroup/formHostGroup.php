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

if (!$centreon->user->admin) {
    if ($hg_id && false === strpos($hgString, "'" . $hg_id . "'")) {
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
    $DBRESULT = $pearDB->query("SELECT * FROM hostgroup WHERE hg_id = '" . $hg_id . "' LIMIT 1");
    /*
     * Set base value
     */
    $hg = array_map("myDecode", $DBRESULT->fetchRow());
}

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
$attrsText = array("size" => "30");
$attrsTextLong = array("size" => "50");
$attrsAdvSelect = array("style" => "width: 300px; height: 220px;");
$attrsTextarea = array("rows" => "4", "cols" => "60");
$eTemplate = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br />'
    . '<br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';
$hostRoute = './api/internal.php?object=centreon_configuration_host&action=list';
$attrHosts = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $hostRoute,
    'multiple' => true,
    'linkedObject' => 'centreonHost'
);
$hostGrRoute = './api/internal.php?object=centreon_configuration_hostgroup&action=list';

/*
 * Create formulary
 */
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
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
$hostRoute = './include/common/webServices/rest/internal.php?object=centreon_configuration_host'
    . '&action=defaultValues&target=hostgroups&field=hg_hosts&id=' . ($hg_id > 0 ? $hg_id : '');
$attrHost1 = array_merge(
    $attrHosts,
    array('defaultDatasetRoute' => $hostRoute)
);
$form->addElement('select2', 'hg_hosts', _("Linked Hosts"), array(), $attrHost1);

/*
 * Extended information
 */
$form->addElement('header', 'extended', _("Extended Information"));
$form->addElement('text', 'hg_notes', _("Notes"), $attrsText);
$form->addElement('text', 'hg_notes_url', _("Notes URL"), $attrsTextLong);
$form->addElement('text', 'hg_action_url', _("Action URL"), $attrsTextLong);
$form->addElement(
    'select',
    'hg_icon_image',
    _("Icon"),
    $extImg,
    array("onChange" => "showLogo('hg_icon_image_img',this.form.elements['hg_icon_image'].value)")
);
$form->addElement(
    'select',
    'hg_map_icon_image',
    _("Map Icon"),
    $extImg,
    array("onChange" => "showLogo('hg_map_icon_image',this.form.elements['hg_map_icon_image'].value)")
);

/*
 * Further informations
 */
$form->addElement('text', 'hg_rrd_retention', _('RRD retention'), array('size' => 5));

$form->registerRule('validate_geo_coords', 'function', 'validateGeoCoords');
$form->addElement('text', 'geo_coords', _("Geo coordinates"), $attrsText);
$form->addRule('geo_coords', _("geo coords are not valid"), 'validate_geo_coords');

$form->addElement('header', 'furtherInfos', _("Additional Information"));
$form->addElement('textarea', 'hg_comment', _("Comments"), $attrsTextarea);

$hgActivation[] = $form->createElement('radio', 'hg_activate', null, _("Enabled"), '1');
$hgActivation[] = $form->createElement('radio', 'hg_activate', null, _("Disabled"), '0');
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

$form->registerRule('exist', 'callback', 'testHostGroupExistence');
$form->addRule('hg_name', _("Name is already in use"), 'exist');
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));

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
        $form->addElement(
            "button",
            "change",
            _("Modify"),
            array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&hg_id=" . $hg_id . "'")
        );
    }
    $form->setDefaults($hg);
    $form->freeze();
} elseif ($o == "c") {
    //Modify a HostGroup information
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($hg);

    //check host resources
    $hostArray = array();
    $host = $acl->getHostAclConf(null, 'broker');
    $accessHost = array_keys($host);
    $rq = "SELECT DISTINCT h.host_id FROM hostgroup_relation hgr, host h  " .
        " WHERE hostgroup_hg_id = '" . $hg_id . "' AND h.host_id = hgr.host_host_id AND h.host_register = '1' ";
    $db = $pearDB->query($rq);
    while ($row = $db->fetch()) {
        $hostArray[] = $row['host_id'];
    }
    $result = array_diff($hostArray, $accessHost);
    if (!empty($result) && (!$centreon->user->admin)) {
        $form->addElement('text', 'msgacl', _("error"), 'error');
        $form->freeze();
    }
} elseif ($o == "a") {
    /*
     * Add a HostGroup information
     */
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$tpl->assign('p', $p);
$tpl->assign('javascript', "<script type='text/javascript' src='./include/common/javascript/showLogo.js'></script>");
$tpl->assign(
    "helpattr",
    'TITLE, "' . _("Help") . '", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR,'
    . ' "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"],'
    . ' WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"'
);

# prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
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
    require_once($path . "listHostGroup.php");
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
