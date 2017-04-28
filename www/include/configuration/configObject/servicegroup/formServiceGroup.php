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
 * Initiate Objets
 */
$obj = new CentreonForm($path, $p, $o);

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

/*
 * Create formulary
 */
$tabLabel = array("a" => _("Add a Host Group"), "c" => _("Modify a Host Group"), "w" => _("View a Host Group"));
$obj->addHeader('title', $tabLabel[$o]);

$obj->addHeader('information', _("General Information"));

$obj->addInputText('sg_name', _("Service Group Name"));
$obj->addInputText('sg_alias', _("Description"));
$obj->addInputText('geo_coords', _("Geo coordinates"));

$obj->addHeader('relation', _("Relations"));

/*
 * Hosts Selection
 */
$obj->addSelect2('sg_hServices', _("Linked to Services by Hosts"), 'service', array('object' => 'centreon_configuration_service', 'action' => 'defaultValues', 'target' => 'servicegroups', 'field' => 'sg_hServices', 'id' => $sg_id));
$obj->addSelect2('sg_hgServices', _("Linked to Service by Hostgroups"), 'service', array('object' => 'centreon_configuration_service', 'action' => 'defaultValues', 'target' => 'servicegroups', 'field' => 'sg_hgServices', 'id' => $sg_id));
$obj->addSelect2('sg_tServices', _("Linked Service Templates"), 'servicetemplate', array('object' => 'centreon_configuration_servicetemplate', 'action' => 'defaultValues', 'target' => 'servicegroups', 'field' => 'sg_tServices', 'id' => $sg_id));

/*
 * Further informations
 */
$obj->addHeader('furtherInfos', _("Additional Information"));
$obj->addRadioButton('sg_activate', _("Status"), array(0 => _("Disabled"), 1 => _("Enabled")), 1);
$obj->addInputTextarea('sg_comment', _("Comments"));
$obj->addHidden('hg_id');

/*
$init = $form->addElement('hidden', 'initialValues');
$init->setValue(serialize($initialValues));
*/

/*
 * Form Rules
 */
$obj->registerRule('exist', 'callback', 'testServiceGroupExistence');

$obj->addRule('sg_name', _("Compulsory Name"), 'required');
$obj->addRule('sg_alias', _("Compulsory Alias"), 'required');

if ($o ==  "a") {
    $obj->addRule('sg_name', _("Name is already in use"), 'exist');
}

if ($o == "w") {
    /*
     * Just watch information
     */
    if ($centreon->user->access->page($p) != 2) {
        $obj->addSubmitButton("change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&sg_id=".$sg_id."'"));
    }
    $obj->setDefaults($sg);
    $obj->freeze();
} elseif ($o == "c") {
    /*
     * Modify  information
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
        insertServiceGroupInDB();
    } elseif ($obj->getSubmitValue("submitC")) {
        $sg_id = $obj->getElement('sg_id')->getValue();
        updateHostGroupInDB($sg_id, $obj->getSubmitValues());
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    require_once $path."listServiceGroup.php";
} else {
    $obj->display("formServiceGroup.ihtml");
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
