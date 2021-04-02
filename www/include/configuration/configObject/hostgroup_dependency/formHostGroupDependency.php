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

#
## Database retrieve information for Dependency
#
$dep = array();
$initialValues = array();
if (($o == "c" || $o == "w") && $dep_id) {
    $DBRESULT = $pearDB->query("SELECT * FROM dependency WHERE dep_id = '" . $dep_id . "' LIMIT 1");

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

    $DBRESULT->closeCursor();
}

/*
 * Var information to format the element
 */
$attrsText = array("size" => "30");
$attrsText2 = array("size" => "10");
$attrsAdvSelect = array("style" => "width: 300px; height: 150px;");
$attrsTextarea = array("rows" => "3", "cols" => "30");
$eTemplate = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br />' .
    '<br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

$route = './include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup&action=list';
$attrHostgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $route,
    'multiple' => true,
    'linkedObject' => 'centreonHostgroups'
);


/*
 * Form begin
 */
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
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
$tab[] = $form->createElement('radio', 'inherits_parent', null, _("Yes"), '1');
$tab[] = $form->createElement('radio', 'inherits_parent', null, _("No"), '0');
$form->addGroup($tab, 'inherits_parent', _("Parent relationship"), '&nbsp;');
$form->setDefaults(array('inherits_parent' => '1'));

$tab = array();
$tab[] = $form->createElement(
    'checkbox',
    'o',
    '&nbsp;',
    _("Ok/Up"),
    array('id' => 'hUp', 'onClick' => 'uncheckAllH(this);')
);
$tab[] = $form->createElement(
    'checkbox',
    'd',
    '&nbsp;',
    _("Down"),
    array('id' => 'hDown', 'onClick' => 'uncheckAllH(this);')
);
$tab[] = $form->createElement(
    'checkbox',
    'u',
    '&nbsp;',
    _("Unreachable"),
    array('id' => 'hUnreachable', 'onClick' => 'uncheckAllH(this);')
);
$tab[] = $form->createElement(
    'checkbox',
    'p',
    '&nbsp;',
    _("Pending"),
    array('id' => 'hPending', 'onClick' => 'uncheckAllH(this);')
);
$tab[] = $form->createElement(
    'checkbox',
    'n',
    '&nbsp;',
    _("None"),
    array('id' => 'hNone', 'onClick' => 'uncheckAllH(this);')
);
$form->addGroup($tab, 'notification_failure_criteria', _("Notification Failure Criteria"), '&nbsp;&nbsp;');

$tab = array();
$tab[] = $form->createElement('checkbox', 'o', '&nbsp;', _("Ok/Up"));
$tab[] = $form->createElement('checkbox', 'd', '&nbsp;', _("Down"));
$tab[] = $form->createElement('checkbox', 'u', '&nbsp;', _("Unreachable"));
$tab[] = $form->createElement('checkbox', 'p', '&nbsp;', _("Pending"));
$tab[] = $form->createElement('checkbox', 'n', '&nbsp;', _("None"));
$form->addGroup($tab, 'execution_failure_criteria', _("Execution Failure Criteria"), '&nbsp;&nbsp;');

$route = './include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup' .
    '&action=defaultValues&target=dependency&field=dep_hgParents&id=' . $dep_id;
$attrHostgroup1 = array_merge(
    $attrHostgroups,
    array('defaultDatasetRoute' => $route)
);
$form->addElement('select2', 'dep_hgParents', _("Host Groups Name"), array(), $attrHostgroup1);

$route = './include/common/webServices/rest/internal.php?object=centreon_configuration_hostgroup' .
    '&action=defaultValues&target=dependency&field=dep_hgChilds&id=' . $dep_id;
$attrHostgroup2 = array_merge(
    $attrHostgroups,
    array('defaultDatasetRoute' => $route)
);
$form->addElement('select2', 'dep_hgChilds', _("Dependent Host Groups Name"), array(), $attrHostgroup2);

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
$form->registerRule('sanitize', 'callback', 'isNotEmptyAfterStringSanitize');
$form->addRule('dep_name', _("Compulsory Name"), 'required');
$form->addRule('dep_name', _("Unauthorized value"), 'sanitize');
$form->addRule('dep_description', _("Required Field"), 'required');
$form->addRule('dep_description', _("Unauthorized value"), 'sanitize');
$form->addRule('dep_hgParents', _("Required Field"), 'required');
$form->addRule('dep_hgChilds', _("Required Field"), 'required');

$form->addRule('notification_failure_criteria', _("Required Field"), 'required');

$form->registerRule('cycle', 'callback', 'testHostGroupDependencyCycle');
$form->addRule('dep_hgChilds', _("Circular Definition"), 'cycle');
$form->registerRule('exist', 'callback', 'testHostGroupDependencyExistence');
$form->addRule('dep_name', _("Name is already in use"), 'exist');
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

# Just watch a Dependency information
if ($o == "w") {
    if ($centreon->user->access->page($p) != 2) {
        $form->addElement(
            "button",
            "change",
            _("Modify"),
            array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&dep_id=" . $dep_id . "'")
        );
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

$tpl->assign(
    "helpattr",
    'TITLE, "' . _("Help") . '", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", ' .
    'TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, ' .
    'SHADOW, true, TEXTALIGN, "justify"'
);
# prepare help texts
$helptext = "";
include_once("include/configuration/configObject/host_dependency/help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

$valid = false;
if ($form->validate()) {
    $depObj = $form->getElement('dep_id');
    if ($form->getSubmitValue("submitA")) {
        $depObj->setValue(insertHostGroupDependencyInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateHostGroupDependencyInDB($depObj->getValue("dep_id"));
    }
    $o = null;
    $valid = true;
}

if ($valid) {
    require_once("listHostGroupDependency.php");
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
    $tpl->display("formHostGroupDependency.ihtml");
}
?>
<script type="text/javascript">
    function uncheckAllH(object) {
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
</script>
