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

/* 
 * Initiate Objets
 */
$obj = new CentreonForm($path, $p, $o);

/*
 * Database retrieve information for HostGroup
	 */
$hg = array();
if (($o == "c" || $o == "w") && $hg_id) {
    $DBRESULT = $pearDB->query("SELECT * FROM hostgroup WHERE hg_id = '".$hg_id."' LIMIT 1");
    $hg = array_map("myDecode", $DBRESULT->fetchRow());
}

/*
 * Create formulary
 */

$tabLabel = array("a" => _("Add a Host Group"), "c" => _("Modify a Host Group"), "w" => _("View a Host Group"));
$obj->addHeader('title', $tabLabel[$o]);

/*
 * Contact basic information
 */
$obj->addHeader('information', _("General Information"));
$obj->addInputText('hg_name', _("Host Group Name"));
$obj->addInputText('hg_alias', _("Alias"));

/*
 * Hosts Selection
 */
$obj->addSelect2('hg_hosts', _("Linked Hosts"), 'host', array('object' => 'centreon_configuration_host', 'action' => 'defaultValues', 'target' => 'hostgroups', 'field' => 'hg_hosts', 'id' => $hg_id));

/*
 * Extended information
 */
$obj->addHeader('extended', _("Extended Information"));
$obj->addInputText('hg_notes', _("Notes"), 'text-long');
$obj->addInputText('hg_notes_url', _("Notes URL"), 'text-long');
$obj->addInputText('hg_action_url', _("Action URL"), 'text-long');

/*
 * IMG comes from DB -> Store in $extImg Array
 */
$extImg = return_image_list(1);

$obj->addInputSelect('hg_icon_image', _("Icon"), $extImg, array("onChange" => "showLogo('hg_icon_image_img',this.form.elements['hg_icon_image'].value)"));
$obj->addInputSelect('hg_map_icon_image', _("Map Icon"), $extImg, array("onChange" => "showLogo('hg_map_icon_image_img',this.form.elements['hg_map_icon_image'].value)"));

/*
 * Further informations
 */
$obj->addInputText('hg_rrd_retention', _('RRD retention'), 'text-small');
$obj->addInputText('geo_coords', _("Geo coordinates"), 'text-small');
$obj->addHeader('furtherInfos', _("Additional Information"));
$obj->addInputTextarea('hg_comment', _("Comments"));
$obj->addRadioButton('hg_activate', _("Status"), array(0 => _("Disabled"), 1 => _("Enabled")), 1);

$obj->addHidden('hg_id');

/* 
 * define Rules 
 */
$obj->registerRule('exist', 'callback', 'testHostGroupExistence');

$obj->addRule('hg_name', _("Compulsory Name"), 'required');
$obj->addRule('hg_alias', _("Compulsory Alias"), 'required');

if ($o ==  "a") {
    $obj->addRule('hg_name', _("Name is already in use"), 'exist');
}

if ($o == "w") {
    /*
     * Just watch a HostGroup information
     */
    if ($centreon->user->access->page($p) != 2) {
        $obj->addSubmitButton("change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&hg_id=".$hg_id."'"));
    }
    $obj->setDefaults($hg);
    $obj->freeze();
} elseif ($o == "c") {
    /*
     * Modify a HostGroup information
     */
    $obj->addSubmitButton('submitC', _("Save"));
    $obj->addResetButton('reset', _("Reset"));
    $obj->setDefaults($hg);
} elseif ($o == "a") {
    /*
     * Add a HostGroup information
     */
    $obj->addSubmitButton('submitA', _("Save"));
    $obj->addResetButton('reset', _("Reset"));
}

$valid = false;
if ($obj->validate()) {
    $form = $obj->getForm();
    if ($obj->getSubmitValue("submitA")) {
        insertHostGroupInDB();
    } elseif ($obj->getSubmitValue("submitC")) {
        $hg_id = $obj->getElement('hg_id')->getValue();
        updateHostGroupInDB($hg_id, $obj->getSubmitValues());
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    require_once $path."listHostGroup.php";
} else {
    $obj->assign('days', _('days'));
    $obj->assign('topdoc', _("Documentation"));
    $obj->display("formHostGroup.ihtml");
}
