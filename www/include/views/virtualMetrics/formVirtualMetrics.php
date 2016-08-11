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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

if (!isset($oreon)) {
    exit;
}

/*
 * Database retrieve information
 */
$vmetric = array();
if ($o == "a" && isset($_POST['vmetric_id']) && $_POST['vmetric_id'] != '') {
    $vmetric_id = $_POST['vmetric_id'];
    $o = "c";
}
if (($o == "c" || $o == "w") && $vmetric_id) {
    $p_qy = $pearDB->query("SELECT *, hidden vhidden FROM virtual_metrics WHERE vmetric_id = '".$vmetric_id."' LIMIT 1");
    // Set base value
    $vmetric = array_map("myDecode", $p_qy->fetchRow());
    $p_qy->free();
}
/*
 * Database retrieve information for differents elements list we need on the page
 *
 * Existing Data Index List comes from DBO -> Store in $indds Array
 */
$indds = array(""=> sprintf("%s%s", _("Host list"), "&nbsp;&nbsp;&nbsp;"));
$mx_l = strlen($indds[""]);

$dbindd = $pearDBO->query("SELECT DISTINCT host_id, host_name FROM index_data;");
if (PEAR::isError($dbindd)) {
    print "DB Error : ".$dbindd->getDebugInfo()."<br />";
}
while ($indd = $dbindd->fetchRow()) {
    $indds[$indd["host_id"]] = $indd["host_name"]."&nbsp;&nbsp;&nbsp;";
    $hn_l = strlen($indd["host_name"]);
    if ($hn_l > $mx_l) {
        $mx_l = $hn_l;
    }
}
$dbindd->free();

/*
 * End of "database-retrieved" information
 */
 
/*
 * Var information to format the element
 */

$attrsText  = array("size"=>"30");
$attrsText2     = array("size"=>"10");
$attrsAdvSelect = array("style" => "width: 200px; height: 100px;");
$attrsTextarea  = array("rows"=>"4", "cols"=>"60");
$attrServices = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_service&action=list',
    'defaultDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_graphvirtualmetric&action=defaultValues&target=graphVirtualMetric&field=host_id&id=' . $vmetric_id,
    'linkedObject' => 'centreonService',
    'multiple' => false
);

/*
 * Form begin
 */
$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
if ($o == "a") {
    $form->addElement('header', 'ftitle', _("Add a Virtual Metric"));
} elseif ($o == "c") {
    $form->addElement('header', 'ftitle', _("Modify a Virtual Metric"));
} elseif ($o == "w") {
    $form->addElement('header', 'ftitle', _("View a Virtual Metric"));
}

/*
 * Basic information 
 * Header
 */
$form->addElement('header', 'information', _("General Information"));
$form->addElement('header', 'function', _("RPN Function"));
$form->addElement('header', 'options', _("Options"));
// General Information
$form->addElement('text', 'vmetric_name', _("Metric Name"), $attrsText);
#$form->addElement('text', 'hs_relation', _("Host / Service Data Source"), $attrsText);
$form->addElement('static', 'hsr_text', _("Choose a service if you want a specific virtual metric for it."));
$form->addElement('select2', 'host_id', _("Linked Host Services"), array(), $attrServices);

$form->addElement('select', 'def_type', _("DEF Type"), array(0=>"CDEF&nbsp;&nbsp;&nbsp;",1=>"VDEF&nbsp;&nbsp;&nbsp;"), "onChange=manageVDEF();");
// RPN Function
$form->addElement('textarea', 'rpn_function', _("RPN (Reverse Polish Notation) Function"), $attrsTextarea);
$form->addElement('static', 'rpn_text', _("<br><i><b><font color=\"#B22222\">Notes </font>:</b></i><br>&nbsp;&nbsp;&nbsp;- Do not mix metrics of different sources.<br>&nbsp;&nbsp;&nbsp;- Only aggregation functions work in VDEF rpn expressions."));
#$form->addElement('select', 'real_metrics', null, $rmetrics);
$form->addElement('text', 'unit_name', _("Metric Unit"), $attrsText2);
$form->addElement('text', 'warn', _("Warning Threshold"), $attrsText2);
$form->addElement('text', 'crit', _("Critical Threshold"), $attrsText2);
// Options
$form->addElement('checkbox', 'vhidden', _("Hidden Graph And Legend"), "", "onChange=manageVDEF();");
$form->addElement('textarea', 'comment', _("Comments"), $attrsTextarea);

$form->addElement('hidden', 'vmetric_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);


/*
 * Form Rules
 */
$form->applyFilter('__ALL__', 'myTrim');
$form->addRule('vmetric_name', _("Compulsory Name"), 'required');
$form->addRule('rpn_function', _("Required Field"), 'required');
$form->addRule('host_id', _("Required service"), 'required');


$form->registerRule('existName', 'callback', 'NameTestExistence');
$form->registerRule('RPNInfinityLoop', 'callback', '_TestRPNInfinityLoop');
$form->addRule('vmetric_name', _("Name already in use for this Host/Service"), 'existName');
$form->addRule('rpn_function', _("Can't Use This Virtual Metric '".(isset($_POST["vmetric_name"]) ? htmlentities($_POST["vmetric_name"], ENT_QUOTES, "UTF-8") : '')."' In This RPN Function"), 'RPNInfinityLoop');

$form->setRequiredNote("<font style='color: red;'>*</font>". _(" Required fields"));

/*
 * End of form definition
 */

// Smarty template Init
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

if ($o == "w") {
    // Just watch
    $form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&vmetric_id=".$vmetric_id."'"));
    $form->setDefaults($vmetric);
    $form->freeze();
} elseif ($o == "c") {
    // Modify
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("onClick"=>"javascript:resetLists(".$vmetric["host_id"].",".$vmetric["index_id"].");", "class" => "btc bt_default"));
    $form->setDefaults($vmetric);
} elseif ($o == "a") {
    // Add
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("onClick"=>"javascript:resetLists(0,0)", "class" => "btc bt_default"));
}

if ($o == "c" || $o == "a") {
?>
    <script type='text/javascript'>
        function insertValueQuery() {
            var e_txtarea = document.Form.rpn_function;
            var e_select = document.getElementById('sl_list_metrics');
            var sd_o = e_select.selectedIndex;
            if (sd_o != 0) {
                var chaineAj = '';
                chaineAj = e_select.options[sd_o].text;
                //chaineAj = chaineAj.substring(0, chaineAj.length - 3);
                chaineAj = chaineAj.replace(/\s(\[[CV]DEF\]|)\s*$/,"");

                if (document.selection) {
                    // IE support
                    e_txtarea.focus();
                    sel = document.selection.createRange();
                    sel.text = chaineAj;
                    document.Form.insert.focus();
                } else if (e_txtarea.selectionStart || e_txtarea.selectionStart == '0') {
                    // MOZILLA/NETSCAPE support
                    var pos_s = e_txtarea.selectionStart;
                    var pos_e = e_txtarea.selectionEnd;
                    var str_rpn = e_txtarea.value;
                    e_txtarea.value = str_rpn.substring(0, pos_s) + chaineAj + str_rpn.substring(pos_e, str_rpn.length);
                } else {
                    e_txtarea.value += chaineAj;
                }
            }
        }

        function manageVDEF() {
            var e_checkbox = document.Form.vhidden;
            var vdef_state = document.Form.def_type.value;
            if ( vdef_state == 1) {
                e_checkbox.checked = true;
            }
        }
    </script>
<?php
}
$tpl->assign('msg', array ("changeL"=>"?p=".$p."&o=c&vmetric_id=".$vmetric_id, "changeT"=>_("Modify")));

$tpl->assign("sort1", _("Properties"));
$tpl->assign("sort2", _("Graphs"));
// prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
}
$tpl->assign("helptext", $helptext);

$valid = false;
if ($form->validate()) {
    $vmetricObj = $form->getElement('vmetric_id');
    if ($o == "a") {
        $vmetric_id = insertVirtualMetricInDB();
        $vmetricObj->setValue($vmetric_id);
        try {
            enableVirtualMetricInDB($vmetric_id);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } elseif ($o == "c") {
        try {
            updateVirtualMetricInDB($vmetricObj->getValue());
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    if (!isset($error)) {
        $o = "w";
        $form->addElement(
            "button",
            "change",
            _("Modify"),
            array("onClick"=>"javascript:window.location.href='?p=$p&o=c&vmetric_id=".$vmetricObj->getValue()."'")
        );
        $form->freeze();
        $valid = true;
    }
}
$action = $form->getSubmitValue("action");
if ($valid) {
    require_once("listVirtualMetrics.php");
} else {
    if (isset($error)) {
        print "<p style='text-align: center'><span class='msg'>$error</span></p>";
    }
    // Apply a template definition
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->display("formVirtualMetrics.ihtml");
}
$vdef=1; /* Display VDEF too */
include_once("./include/views/graphs/common/makeJS_formMetricsList.php");
if ($o == "c" || $o == "w") {
    isset($_POST["host_id"]) && $_POST["host_id"] != null ? $host_service_id=$_POST["host_id"]: $host_service_id=$vmetric["host_id"];
} elseif ($o == "a") {
    isset($_POST["host_id"]) && $_POST["host_id"] != null ? $host_service_id=$_POST["host_id"]: $host_service_id=0;
}
?>

<script type="text/javascript">
    update_select_list('<?php echo $host_service_id;?>');

    jQuery("#host_id").on('change', function() {
        update_select_list(this.value);
    });
</script>
