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
    if ($sg_id && false === strpos($sgString, "'".$sg_id."'")) {
        $msg = new CentreonMsg();
        $msg->setImage("./img/icons/warning.png");
        $msg->setTextStyle("bold");
        $msg->setText(_('You are not allowed to access this service group'));
        return null;
    }
}

/*
 * Database retrieve information for differents elements list we need on the page
 *
 * Services comes from DB -> Store in $hServices Array and $hgServices
 */
$hgServices = array();

$aclFrom = "";
$aclCond = "";
if (!$centreon->user->admin) {
    $aclFrom = ", hostgroup_relation hgr, $aclDbName.centreon_acl acl ";
    $aclCond = " AND hg.hg_id = hgr.hostgroup_hg_id
        AND hgr.host_host_id = acl.host_id
        AND acl.service_id = sv.service_id
        AND acl.group_id IN (".$acl->getAccessGroupsString().") ";
}

$DBRESULT = $pearDB->query("SELECT DISTINCT hg.hg_name, hg.hg_id, sv.service_description, sv.service_template_model_stm_id, sv.service_id " .
        "FROM host_service_relation hsr, service sv, hostgroup hg $aclFrom" .
        "WHERE sv.service_register = '1' " .
        "AND hsr.service_service_id = sv.service_id " .
        "AND hg.hg_id = hsr.hostgroup_hg_id " . $aclCond .
        "ORDER BY hg.hg_name, sv.service_description");
while ($elem = $DBRESULT->fetchRow()) {
    // If the description of our Service is in the Template definition, we have to catch it, whatever the level of it :-)
    if (!$elem["service_description"]) {
        $elem["service_description"] = getMyServiceName($elem['service_template_model_stm_id']);
    }
    
    $elem["service_description"] = str_replace("#S#", "/", $elem["service_description"]);
    $elem["service_description"] = str_replace("#BS#", "\\", $elem["service_description"]);

    $hgServices[$elem["hg_id"] . '-'.$elem["service_id"]] = $elem["hg_name"]."&nbsp;&nbsp;&nbsp;&nbsp;".$elem["service_description"];
}
$DBRESULT->free();

$initialValues = array('sg_hServices' => array(), 'sg_hgServices' => array());

/*
 * Database retrieve information for ServiceGroup
 */
$sg = array();
$hServices = array();
if (($o == "c" || $o == "w") && $sg_id) {
    $DBRESULT = $pearDB->query("SELECT * FROM servicegroup WHERE sg_id = '".$sg_id."' LIMIT 1");

    // Set base value
    $sg = array_map("myDecode", $DBRESULT->fetchRow());
}

$query = "SELECT host_id, host_name, service_id, service_description
             FROM host, service, host_service_relation
             WHERE host_id = host_host_id
             AND service_id = service_service_id
             AND host_register = '0' ORDER BY host_name";
$res = $pearDB->query($query);
while ($row = $res->fetchRow()) {
    $row['service_description'] = str_replace("#S#", "/", $row['service_description']);
    $tServices[$row["host_id"]."-".$row['service_id']] = $row["host_name"]."&nbsp;-&nbsp;".$row['service_description'];
}

#
# End of "database-retrieved" information
##########################################################
##########################################################
# Var information to format the element
#
$attrsText      = array("size"=>"30");
$attrsAdvSelect = array("style" => "width: 400px; height: 250px;");
$attrsTextarea  = array("rows"=>"5", "cols"=>"40");
$eTemplate  = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

$attrServices = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_service&action=list',
    'multiple' => true,
    'linkedObject' => 'centreonService'
);
$attrServicetemplates = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_servicetemplate&action=list&l=1',
    'multiple' => true,
    'linkedObject' => 'centreonServicetemplates',
    'defaultDatasetOptions' => array('withHosttemplate' => true)
);
$attrHostgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_service&action=list&t=hostgroup',
    'multiple' => true,
    'linkedObject' => 'centreonHostgroups'
);

#
## Form begin
#
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add a Service Group"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify a Service Group"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View a Service Group"));
}

#
## Contact basic information
#
$form->addElement('header', 'information', _("General Information"));
$form->addElement('text', 'sg_name', _("Name"), $attrsText);
$form->addElement('text', 'sg_alias', _("Description"), $attrsText);
$form->addElement('text', 'geo_coords', _("Geo coordinates"), $attrsText);

##
## Services Selection
##
$hostFilter = array(null => null,
        0    => sprintf('__%s__', _('ALL')));
$hostFilter = ($hostFilter + $acl->getHostAclConf(
    null,
    'broker',
    array('fields'  => array('host.host_id', 'host.host_name'),
                                                       'keys'    => array('host_id'),
                                                       'get_row' => 'host_name',
                                                       'order'   => array('host.host_name')),
    false
));

$form->addElement('header', 'relation', _("Relations"));
if (isset($_REQUEST['sg_hServices']) && count($_REQUEST['sg_hServices'])) {
    $sql = "SELECT host_id, service_id, host_name, service_description FROM host h, service s, host_service_relation hsr
           WHERE h.host_id = hsr.host_host_id
           AND hsr.service_service_id = s.service_id
           AND CONCAT_WS('-', h.host_id, s.service_id) IN ('".implode("','", $_REQUEST['sg_hServices'])."')";
    $res = $pearDB->query($sql);
    while ($row = $res->fetchRow()) {
        $k = $row['host_id'] . '-' . $row['service_id'];
        $hServices[$k] = $row['host_name'] . ' - ' . $row['service_description'];
    }
}
$attrService1 = array_merge(
    $attrServices,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_service&action=defaultValues&target=servicegroups&field=sg_hServices&id=' . $sg_id)
);
$form->addElement('select2', 'sg_hServices', _("Linked Host Services"), array(), $attrService1);

$attrHostgroup1 = array_merge(
    $attrHostgroups,
    array(
        'defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_service&action=defaultValues&target=servicegroups&field=sg_hgServices&id=' . $sg_id
    )
);
$form->addElement('select2', 'sg_hgServices', _("Linked Host Group Services"), array(), $attrHostgroup1);


$attrServicetemplate1 = array_merge(
    $attrServicetemplates,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_servicetemplate&action=defaultValues&target=servicegroups&field=sg_tServices&id=' . $sg_id)
);
$form->addElement('select2', 'sg_tServices', _("Linked Service Templates"), array(), $attrServicetemplate1);

/*
 * Further informations
 */
$form->addElement('header', 'furtherInfos', _("Additional Information"));
$sgActivation[] = HTML_QuickForm::createElement('radio', 'sg_activate', null, _("Enabled"), '1');
$sgActivation[] = HTML_QuickForm::createElement('radio', 'sg_activate', null, _("Disabled"), '0');
$form->addGroup($sgActivation, 'sg_activate', _("Status"), '&nbsp;');
$form->setDefaults(array('sg_activate' => '1'));
$form->addElement('textarea', 'sg_comment', _("Comments"), $attrsTextarea);

$form->addElement('hidden', 'sg_id');
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
    return (str_replace(" ", "_", $ret["sg_name"]));
}
$form->applyFilter('__ALL__', 'myTrim');
$form->applyFilter('sg_name', 'myReplace');
$form->addRule('sg_name', _("Compulsory Name"), 'required');
$form->addRule('sg_alias', _("Compulsory Description"), 'required');
$form->registerRule('exist', 'callback', 'testServiceGroupExistence');
$form->addRule('sg_name', _("Name is already in use"), 'exist');
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

#
##End of form definition
#

# Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

# Just watch a Service Group information
if ($o == "w") {
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&sg_id=".$sg_id."'"));
    }
    $form->setDefaults($sg);
    $form->freeze();
} # Modify a Service Group information
elseif ($o == "c") {
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($sg);
} # Add a Service Group information
elseif ($o == "a") {
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$tpl->assign('nagios', $oreon->user->get_version());
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
    $sgObj = $form->getElement('sg_id');
    if ($form->getSubmitValue("submitA")) {
        $sgObj->setValue(insertServiceGroupInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateServiceGroupInDB($sgObj->getValue());
    }
    $o = null;
    $valid = true;
}
$action = $form->getSubmitValue("action");

if ($valid) {
    require_once($path."listServiceGroup.php");
} else {
    // Apply a template definition
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->display("formServiceGroup.ihtml");
}
?>
<script type='text/javascript'>
function hostFilterSelect(elem)
{
    var arg = 'host_id='+elem.value;

    if (window.XMLHttpRequest) {
        var xhr = new XMLHttpRequest();
    } else if(window.ActiveXObject){
        try {
            var xhr = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            var xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
    } else {
        var xhr = false;
    }

    xhr.open("POST","./include/configuration/configObject/servicegroup/getServiceXml.php", true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.send(arg);

    xhr.onreadystatechange = function()
    {
        if (xhr && xhr.readyState == 4 && xhr.status == 200 && xhr.responseXML){
            var response = xhr.responseXML.documentElement;
            var _services = response.getElementsByTagName("services");
            var _selbox;

            if (document.getElementById("sg_hServices-f")) {
                _selbox = document.getElementById("sg_hServices-f");
                _selected = document.getElementById("sg_hServices-t");
            } else if (document.getElementById("__sg_hServices")) {
                _selbox = document.getElementById("__sg_hServices");
                _selected = document.getElementById("_sg_hServices");
            }

            while ( _selbox.options.length > 0 ){
                _selbox.options[0] = null;
            }

            if (_services.length == 0) {
                _selbox.setAttribute('disabled', 'disabled');
            } else {
                _selbox.removeAttribute('disabled');
            }

            for (var i = 0 ; i < _services.length ; i++) {
                var _svc         = _services[i];
                var _id          = _svc.getElementsByTagName("id")[0].firstChild.nodeValue;
                var _description = _svc.getElementsByTagName("description")[0].firstChild.nodeValue;
                var validFlag = true;

                for (var j = 0; j < _selected.length; j++) {
                    if (_id == _selected.options[j].value) {
                        validFlag = false;
                    }
                }

                if (validFlag == true) {
                    new_elem = new Option(_description,_id);
                    _selbox.options[_selbox.length] = new_elem;
                }
            }
        }
    }
}
</script>
