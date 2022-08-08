<?php

/*
 * Copyright 2005-2020 Centreon
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

$graph = [];
if (($o == "c" || $o == "w") && $graph_id !== false && $graph_id > 0) {
    $stmt = $pearDB->prepare('SELECT * FROM giv_graphs_template WHERE graph_id = :graphId LIMIT 1');
    $stmt->bindValue(':graphId', $graph_id, \PDO::PARAM_INT);
    $stmt->execute();
    $graph = array_map("myDecode", $stmt->fetch(\PDO::FETCH_ASSOC));
}

/*
 * Retrieve information from database for differents elements list we need on the page
 */
$compos = array();
$stmt = $pearDB->query('SELECT compo_id, name FROM giv_components_template ORDER BY name');
while ($compo = $stmt->fetch(\PDO::FETCH_ASSOC)) {
    $compos[$compo["compo_id"]] = $compo["name"];
}

#
# End of "database-retrieved" information
##########################################################
##########################################################
# Var information to format the element
#

$attrsText = array("size" => "30");
$attrsText2 = array("size" => "6");
$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
$attrsTextarea = array("rows" => "3", "cols" => "30");

#
## Form begin
#
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
if ($o == "a") {
    $form->addElement('header', 'ftitle', _("Add a Graph Template"));
} elseif ($o == "c") {
    $form->addElement('header', 'ftitle', _("Modify a Graph Template"));
} elseif ($o == "w") {
    $form->addElement('header', 'ftitle', _("View a Graph Template"));
}

#
## Basic information
#
$form->addElement('header', 'information', _("General Information"));
$form->addElement('header', 'color', _("Legend"));
$form->addElement('text', 'name', _("Template Name"), $attrsText);

$form->addElement('select', 'img_format', _("Image Type"), array("PNG" => "PNG", "GIF" => "GIF"));
$form->addElement('text', 'vertical_label', _("Vertical Label"), $attrsText);
$form->addElement('text', 'width', _("Width"), $attrsText2);
$form->addElement('text', 'height', _("Height"), $attrsText2);
$form->addElement('text', 'lower_limit', _("Lower Limit"), $attrsText2);
$form->addElement('text', 'upper_limit', _("Upper Limit"), array(
    'id' => 'upperLimitTxt',
    'size' => '6'
));
$form->addElement('checkbox', 'size_to_max', _("Size to max"), '', array(
    'id' => 'sizeToMax',
    'onClick' => 'sizeToMaxx();'
));
$form->addElement('text', 'ds_name', _("Data Source Name"), $attrsText);
$form->addElement('select', 'base', _("Base"), array("1000" => "1000", "1024" => "1024"));

$periods = array(
    "10800" => _("Last 3 Hours"),
    "21600" => _("Last 6 Hours"),
    "43200" => _("Last 12 Hours"),
    "86400" => _("Last 24 Hours"),
    "172800" => _("Last 2 Days"),
    "302400" => _("Last 4 Days"),
    "604800" => _("Last 7 Days"),
    "1209600" => _("Last 14 Days"),
    "2419200" => _("Last 28 Days"),
    "2592000" => _("Last 30 Days"),
    "2678400" => _("Last 31 Days"),
    "5184000" => _("Last 2 Months"),
    "10368000" => _("Last 4 Months"),
    "15552000" => _("Last 6 Months"),
    "31104000" => _("Last Year")
);

$sel = $form->addElement('select', 'period', _("Graph Period"), $periods);
$steps = array(
    "0" => _("No Step"),
    "2" => "2",
    "6" => "6",
    "10" => "10",
    "20" => "20",
    "50" => "50",
    "100" => "100"
);

$sel = $form->addElement('select', 'step', _("Recovery Step"), $steps);

if ($o == "c" || $o == "a") {
    $nameColor = isset($nameColor) ? $nameColor : '';
    $attrsText5 = isset($attrsText5) ? $attrsText5 : '';
    $form->addElement('button', $nameColor . '_modify', _("Modify"), $attrsText5);
}

$form->addElement('checkbox', 'stacked', _("Stacking"));
$form->addElement('checkbox', 'scaled', _("Scale Graph Values"));
$form->addElement('textarea', 'comment', _("Comments"), $attrsTextarea);
$form->addElement('checkbox', 'default_tpl1', _("Default Centreon Graph Template"));

$form->addElement('hidden', 'graph_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/*
 * Form Rules
 */
$form->applyFilter('__ALL__', 'myTrim');
$form->addRule('name', _("Compulsory Name"), 'required');
$form->addRule('vertical_label', _("Required Field"), 'required');
$form->addRule('width', _("Required Field"), 'required');
$form->addRule('height', _("Required Field"), 'required');
// $form->addRule('title', _("Required Field"), 'required'); - Field is not declared so rule is not needed
$form->registerRule('exist', 'callback', 'testExistence');
$form->addRule('name', _("Name is already in use"), 'exist');
$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;" . _("Required fields"));


/*
 * Smarty template Init
 */

$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

# Just watch
if ($o == "w") {
    $form->addElement(
        "button",
        "change",
        _("Modify"),
        array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&graph_id=" . $graph_id . "'")
    );
    $form->setDefaults($graph);
    $form->freeze();
} elseif ($o == "c") {
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Delete"), array("class" => "btc bt_danger"));
    $form->setDefaults($graph);
} elseif ($o == "a") {
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Delete"), array("class" => "btc bt_danger"));
}
$tpl->assign('msg', array("changeL" => "main.php?p=" . $p . "&o=c&graph_id=" . $graph_id, "changeT" => _("Modify")));

$tpl->assign("sort1", _("Properties"));
$tpl->assign("sort2", _("Data Sources"));
// prepare help texts
$helptext = "";
include_once("help.php");

foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);


/*
 * Picker Color JS
 */

$tpl->assign(
    'colorJS',
    "
<script type='text/javascript'>
    function popup_color_picker(t,name)
	{
	    var width = 400;
	    var height = 300;
	    window.open(
	        './include/common/javascript/color_picker.php?n=' + t + '&name=' + name,
	        'cp',
			'resizable=no, location=no, width=' + width + ', height=' + height +
			', menubar=no, status=yes, scrollbars=no, menubar=no'
	    );
	}
</script>"
);

/*
 * End of Picker Color
 */

$valid = false;
if ($form->validate()) {
    $graphObj = $form->getElement('graph_id');
    if ($form->getSubmitValue("submitA")) {
        $graphObj->setValue(insertGraphTemplateInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateGraphTemplateInDB($graphObj->getValue());
    }
    $o = "w";
    $form->addElement(
        "button",
        "change",
        _("Modify"),
        array("onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&graph_id=" . $graphObj->getValue() . "'")
    );
    $form->freeze();
    $valid = true;
}

$action = $form->getSubmitValue("action");
if ($valid) {
    require_once("listGraphTemplates.php");
} else {
    // Apply a template definition
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->display("formGraphTemplate.ihtml");
}
?>
<script type='text/javascript'>
    jQuery(function () {
        sizeToMaxx();
    });

    function sizeToMaxx() {
        var upperLimitTxt = $('#upperLimitTxt');
        var sizeToMax = $('#sizeToMax');

        if (sizeToMax.is(':checked')) {
            upperLimitTxt.prop('disabled', true);
        } else {
            upperLimitTxt.prop('disabled', false);
        }
    }
</script>
