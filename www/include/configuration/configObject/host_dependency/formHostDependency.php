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

$dep = array();
$childServices = array();
$initialValues = array();
if (($o == "c" || $o == "w") && $dep_id) {
    $DBRESULT = $pearDB->query("SELECT * FROM dependency WHERE dep_id = '".$dep_id."' LIMIT 1");

    # Set base value
    $dep = array_map("myDecode", $DBRESULT->fetchRow());

    # Set Notification Failure Criteria
    $dep["notification_failure_criteria"] = explode(',', $dep["notification_failure_criteria"]);
    foreach ($dep["notification_failure_criteria"] as $key => $value) {
        $dep["notification_failure_criteria"][trim($value)] = 1;
    }

    # Set Execution Failure Criteria
    $dep["execution_failure_criteria"] = explode(',', $dep["execution_failure_criteria"]);
    foreach ($dep["execution_failure_criteria"] as $key => $value) {
        $dep["execution_failure_criteria"][trim($value)] = 1;
    }
}

# Var information to format the element
$attrsText      = array("size"=>"30");
$attrsText2     = array("size"=>"10");
$attrsAdvSelect = array("style" => "width: 300px; height: 150px;");
$attrsTextarea  = array("rows"=>"3", "cols"=>"30");
$eTemplate  = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';
$attrHosts = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_host&action=list',
    'multiple' => true,
    'linkedObject' => 'centreonHost'
);
$attrServices = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_service&action=list',
    'multiple' => true,
    'linkedObject' => 'centreonService'
);

/*
 * Form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add a Dependency"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify a Dependency"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View a Dependency"));
}

/*
 * Dependency basic information
 */
$form->addElement('header', 'information', _("Information"));
$form->addElement('text', 'dep_name', _("Name"), $attrsText);
$form->addElement('text', 'dep_description', _("Description"), $attrsText);

$tab = array();
$tab[] = HTML_QuickForm::createElement('radio', 'inherits_parent', null, _("Yes"), '1');
$tab[] = HTML_QuickForm::createElement('radio', 'inherits_parent', null, _("No"), '0');
$form->addGroup($tab, 'inherits_parent', _("Parent relationship"), '&nbsp;');
$form->setDefaults(array('inherits_parent'=>'1'));

$tab = array();
$tab[] = HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', _("Ok/Up"), array('id' => 'hUp', 'onClick' => 'uncheckAllH(this);'));
$tab[] = HTML_QuickForm::createElement('checkbox', 'd', '&nbsp;', _("Down"), array('id' => 'hDown', 'onClick' => 'uncheckAllH(this);'));
$tab[] = HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unreachable"), array('id' => 'hUnreachable', 'onClick' => 'uncheckAllH(this);'));
$tab[] = HTML_QuickForm::createElement('checkbox', 'p', '&nbsp;', _("Pending"), array('id' => 'hPending', 'onClick' => 'uncheckAllH(this);'));
$tab[] = HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', _("None"), array('id' => 'hNone', 'onClick' => 'uncheckAllH(this);'));
$form->addGroup($tab, 'notification_failure_criteria', _("Notification Failure Criteria"), '&nbsp;&nbsp;');

$tab = array();
$tab[] = HTML_QuickForm::createElement('checkbox', 'o', '&nbsp;', _("Up"));
$tab[] = HTML_QuickForm::createElement('checkbox', 'd', '&nbsp;', _("Down"));
$tab[] = HTML_QuickForm::createElement('checkbox', 'u', '&nbsp;', _("Unreachable"));
$tab[] = HTML_QuickForm::createElement('checkbox', 'p', '&nbsp;', _("Pending"));
$tab[] = HTML_QuickForm::createElement('checkbox', 'n', '&nbsp;', _("None"));
$form->addGroup($tab, 'execution_failure_criteria', _("Execution Failure Criteria"), '&nbsp;&nbsp;');

$attrHost1 = array_merge(
    $attrHosts,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_host&action=defaultValues&target=dependency&field=dep_hostParents&id=' . $dep_id)
);
$form->addElement('select2', 'dep_hostParents', _("Host Names"), array(), $attrHost1);

$attrHost2 = array_merge(
    $attrHosts,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_host&action=defaultValues&target=dependency&field=dep_hostChilds&id=' . $dep_id)
);
$form->addElement('select2', 'dep_hostChilds', _("Dependent Host Names"), array(), $attrHost2);

$attrService1 = array_merge(
    $attrServices,
    array('defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_service&action=defaultValues&target=dependency&field=dep_hSvChi&id=' . $dep_id)
);
$form->addElement('select2', 'dep_hSvChi', _("Dependent Services"), array(), $attrService1);

$form->addElement('textarea', 'dep_comment', _("Comments"), $attrsTextarea);

$form->addElement('hidden', 'dep_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

    $init = $form->addElement('hidden', 'initialValues');
    $init->setValue(serialize($initialValues));
    
/*
 * Form Rules
 */
$form->applyFilter('__ALL__', 'myTrim');
$form->addRule('dep_name', _("Compulsory Name"), 'required');
$form->addRule('dep_description', _("Required Field"), 'required');
$form->addRule('dep_hostParents', _("Required Field"), 'required');

$form->registerRule('cycle', 'callback', 'testHostDependencyCycle');
$form->addRule('dep_hostChilds', _("Circular Definition"), 'cycle');
$form->registerRule('exist', 'callback', 'testHostDependencyExistence');
$form->addRule('dep_name', _("Name is already in use"), 'exist');
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"');
# prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
}
$tpl->assign("helptext", $helptext);

# Just watch a Dependency information
if ($o == "w") {
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dep_id=".$dep_id."'"));
    }
    $form->setDefaults($dep);
    $form->freeze();
} # Modify a Dependency information
elseif ($o == "c") {
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($dep);
} # Add a Dependency information
elseif ($o == "a") {
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults(array('inherits_parent', '0'));
}
$tpl->assign("nagios", $oreon->user->get_version());

$valid = false;
if ($form->validate()) {
    $depObj = $form->getElement('dep_id');
    if ($form->getSubmitValue("submitA")) {
        $depObj->setValue(insertHostDependencyInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateHostDependencyInDB($depObj->getValue("dep_id"));
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    require_once("listHostDependency.php");
} else {
    /*
	 * Apply a template definition
	 */
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->display("formHostDependency.ihtml");
}

?>
<script type="text/javascript">
function uncheckAllH(object)
{
    if (object.id == "hNone" && object.checked) {
        document.getElementById('hUp').checked = false;
        document.getElementById('hDown').checked = false;
        document.getElementById('hUnreachable').checked = false;
        document.getElementById('hPending').checked = false;
        if (document.getElementById('hFlapping')) {
            document.getElementById('hFlapping').checked = false;
        }
    }
    else {
        document.getElementById('hNone').checked = false;
    }
}

function hostFilterSelect(elem)
{
    var arg = 'host_id='+elem.value;

    if (window.XMLHttpRequest) {
        var xhr = new XMLHttpRequest();
    } else if(window.ActiveXObject){r
        try {
            var xhr = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            var xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
    } else {
       var xhr = false;
    }

    xhr.open("POST","./include/configuration/configObject/service_dependency/getServiceXml.php", true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.send(arg);

    xhr.onreadystatechange = function()
    {
        if (xhr && xhr.readyState == 4 && xhr.status == 200 && xhr.responseXML){
            var response = xhr.responseXML.documentElement;
            var _services = response.getElementsByTagName("services");
            var _selbox;

            if (document.getElementById("dep_hSvChi-f")) {
                _selbox = document.getElementById("dep_hSvChi-f");
                _selected = document.getElementById("dep_hSvChi-t");
            } else if (document.getElementById("__dep_hSvChi")) {
                _selbox = document.getElementById("__dep_hSvChi");
                _selected = document.getElementById("_dep_hSvChi");
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
