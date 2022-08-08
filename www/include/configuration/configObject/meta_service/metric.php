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

/*
 * Database retrieve information
 */
require_once("./class/centreonDB.class.php");

$pearDBO = new CentreonDB("centstorage");

$metric = array();
if (($o == 'cs') && $msr_id) {
    # Set base value
    $DBRESULT = $pearDB->prepare("SELECT * FROM meta_service_relation WHERE msr_id = :msr_id");
    $DBRESULT->bindValue(':msr_id', $msr_id, \PDO::PARAM_INT);
    $DBRESULT->execute();

    # Set base value
    $metric1 = array_map("myDecode", $DBRESULT->fetchRow());
    if ($host_id === false || $metric1['host_id'] == $host_id) {
        $DBRESULT = $pearDBO->prepare(
            'SELECT * FROM metrics, index_data
            WHERE metric_id = :metric_id and metrics.index_id = index_data.id'
        );
        $DBRESULT->bindValue(':metric_id', $metric1["metric_id"], PDO::PARAM_INT);
        $DBRESULT->execute();
        $metric2 = array_map("myDecode", $DBRESULT->fetchRow());
        $metric = array_merge($metric1, $metric2);
        $host_id = (int) $metric1["host_id"];
        $metric["metric_sel"][0] = getMyServiceID($metric["service_description"], $metric["host_id"]);
        $metric["metric_sel"][1] = $metric["metric_id"];
    }
}

#
## Database retrieve information for differents elements list we need on the page
#

/*
 * Host comes from DB -> Store in $hosts Array
 */
$hosts =
    array(null => null) + $acl->getHostAclConf(
        null,
        'broker',
        array(
            'fields' => array('host.host_id', 'host.host_name'),
            'keys' => array('host_id'),
            'get_row' => 'host_name',
            'order' => array('host.host_name')
        )
    );

$services1 = array(null => null);
$services2 = array(null => null);
if ($host_id !== false) {
    $services =
        array(null => null) + $acl->getHostServiceAclConf(
            $host_id,
            'broker',
            array(
                'fields' => array('s.service_id', 's.service_description'),
                'keys' => array('service_id'),
                'get_row' => 'service_description',
                'order' => array('service_description')
            )
        );

    foreach ($services as $key => $value) {
        $DBRESULT = $pearDBO->query("SELECT DISTINCT metric_name, metric_id, unit_name
									 FROM metrics m, index_data i
									 WHERE i.host_name = '" . $pearDBO->escape(getMyHostName($host_id)) . "'
									 AND i.service_description = '" . $pearDBO->escape($value) . "'
									 AND i.id = m.index_id
									 ORDER BY metric_name, unit_name");

        while ($metricSV = $DBRESULT->fetchRow()) {
            $services1[$key] = $value;
            $metricSV["metric_name"] = str_replace("#S#", "/", $metricSV["metric_name"]);
            $metricSV["metric_name"] = str_replace("#BS#", "\\", $metricSV["metric_name"]);
            $services2[$key][$metricSV["metric_id"]] = $metricSV["metric_name"] . "  (" . $metricSV["unit_name"] . ")";
        }
    }
    $DBRESULT->closeCursor();
}

$debug = 0;
$attrsTextI = array("size" => "3");
$attrsText = array("size" => "30");
$attrsTextarea = array("rows" => "5", "cols" => "40");

/*
 * Form begin
 */
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
if ($o == "as") {
    $form->addElement('header', 'title', _("Add a Meta Service indicator"));
} elseif ($o == "cs") {
    $form->addElement('header', 'title', _("Modify a Meta Service indicator"));
}

/*
 * Indicator basic information
 */
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);
$formMsrId = $form->addElement('hidden', 'msr_id');
$formMsrId->setValue($msr_id);
$formMetaId = $form->addElement('hidden', 'meta_id');
$formMetaId->setValue($meta_id);
$formMetricId = $form->addElement('hidden', 'metric_id');
$formMetricId->setValue($metric_id);

$hn = $form->addElement('select', 'host_id', _("Host"), $hosts, array("onChange" => "this.form.submit()"));
$sel = $form->addElement('hierselect', 'metric_sel', _("Service"));
$sel->setOptions(array($services1, $services2));

$tab = array();
$tab[] = $form->createElement('radio', 'activate', null, _("Enabled"), '1');
$tab[] = $form->createElement('radio', 'activate', null, _("Disabled"), '0');
$form->addGroup($tab, 'activate', _("Status"), '&nbsp;');
$form->setDefaults(array('activate' => '1'));
$form->addElement('textarea', 'msr_comment', _("Comments"), $attrsTextarea);

$form->addRule('host_id', _("Compulsory Field"), 'required');

function checkMetric()
{
    global $form;

    $tab = $form->getSubmitValue("metric_sel");
    if (isset($tab[0]) & isset($tab[1])) {
        return 1;
    }
    return 0;
}

$form->registerRule('checkMetric', 'callback', 'checkMetric');
$form->addRule('metric_sel', _("Compulsory Field"), 'checkMetric');

/*
 * Just watch
 */

if ($o == "cs") {
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $form->setDefaults($metric);
} elseif ($o == "as") {
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
}

$valid = false;
if (
    ((isset($_POST["submitA"]) && $_POST["submitA"]) || (isset($_POST["submitC"]) && $_POST["submitC"]))
    && $form->validate()
) {
    $msrObj = $form->getElement('msr_id');
    if ($form->getSubmitValue("submitA")) {
        $msrObj->setValue(insertMetric($meta_id));
    } elseif ($form->getSubmitValue("submitC")) {
        updateMetric($msrObj->getValue());
    }
    $valid = true;
}

if ($valid) {
    require_once($path . "listMetric.php");
} else {
    /*
     * Smarty template Init
     */
    $tpl = new Smarty();
    $tpl = initSmartyTpl($path, $tpl);

    /*
     * Apply a template definition
     */
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $form->accept($renderer);

    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->assign('valid', $valid);
    $tpl->display("metric.ihtml");
}
