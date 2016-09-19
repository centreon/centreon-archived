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

include_once _CENTREON_PATH_ . "www/class/centreonGMT.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonService.class.php";

/*
 * Init GMT class
 */
$centreonGMT = new CentreonGMT($pearDB);
$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);

$hostStr = $centreon->user->access->getHostsString("ID", $pearDBO);


if ($centreon->user->access->checkAction("service_comment")) {
    $LCA_error = 0;

    isset($_GET["host_id"]) ? $cG = $_GET["host_id"] : $cG = null;
    isset($_POST["host_id"]) ? $cP = $_POST["host_id"] : $cP = null;
    $cG ? $host_id = $cG : $host_id = $cP;

    $host_name = null;
    $svc_description = null;
    if (isset($_GET["host_name"]) && isset($_GET["service_description"])) {
        $host_id = getMyHostID($_GET["host_name"]);
        $service_id = getMyServiceID($_GET["service_description"], $host_id);
        $host_name = $_GET["host_name"];
        $svc_description = $_GET["service_description"];
        if ($host_name == '_Module_Meta' && preg_match('/^meta_(\d+)/', $svc_description, $matches)) {
            $host_name = 'Meta';
            $serviceObj = new CentreonService($pearDB);
            $serviceParameters = $serviceObj->getParameters($service_id, array('display_name'));
            $svc_description = $serviceParameters['display_name'];
        }
    }

    /*
	 * Database retrieve information for differents
	 * elements list we need on the page
	 */
    $query = "SELECT host_id, host_name FROM `host` WHERE (host_register = '1'  OR host_register = '2' )" .
        $centreon->user->access->queryBuilder("AND", "host_id", $hostStr) . "ORDER BY host_name";
    $DBRESULT = $pearDB->query($query);
    $hosts = array(null => null);
    while ($row = $DBRESULT->fetchRow()) {
        $hosts[$row['host_id']] = $row['host_name'];
    }
    $DBRESULT->free();

    $services = array();
    if (isset($host_id)) {
        $services = $centreon->user->access->getHostServices($pearDBO, $host_id);
    }

    $debug = 0;
    $attrsTextI = array("size" => "3");
    $attrsText = array("size" => "30");
    $attrsTextarea = array("rows" => "7", "cols" => "100");

    /*
	 * Form begin
	 */
    $form = new HTML_QuickForm('Form', 'post', "?p=" . $p);
    $form->addElement('header', 'title', _("Add a comment for Service"));

    /*
	 * Indicator basic information
	 */
    $redirect = $form->addElement('hidden', 'o');
    $redirect->setValue($o);

    if (isset($host_id) && isset($service_id)) {
        $form->addElement('hidden', 'host_id', $host_id);
        $form->addElement('hidden', 'service_id', $service_id);
    } else {
        $disabled = " ";
        $attrServices = array(
            'datasourceOrigin' => 'ajax',
            'availableDatasetRoute' => './api/internal.php?object=centreon_configuration_service&action=list&e=enable',
            'multiple' => true,
            'linkedObject' => 'centreonService'
        );
        $form->addElement('select2', 'service_id', _("Services"), array($disabled), $attrServices);
    }

    $persistant = $form->addElement('checkbox', 'persistant', _("Persistent"));
    $persistant->setValue('1');

    $form->addElement('textarea', 'comment', _("Comments"), $attrsTextarea);
    $form->addRule('comment', _("Required Field"), 'required');

    $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));

    $valid = false;
    if ((isset($_POST["submitA"]) && $_POST["submitA"]) && $form->validate()) {
        if (!isset($_POST["persistant"]) || !in_array($_POST["persistant"], array('0', '1'))) {
            $_POST["persistant"] = '0';
        }
        if (!isset($_POST["comment"])) {
            $_POST["comment"] = 0;
        }

        //global services comment
        if (!isset($_POST["host_id"])) {
            foreach ($_POST["service_id"] as $value) {
                $info = split('-', $value);
                AddSvcComment(
                    $info[0],
                    $info[1],
                    $_POST["comment"],
                    $_POST["persistant"]
                );
            }
        } else {
            //specific service comment
            AddSvcComment($_POST["host_id"], $_POST["service_id"], $_POST["comment"], $_POST["persistant"]);
        }

        $valid = true;
        require_once($path . "listComment.php");
    } else {
        /*
         * Smarty template Init
         */
        $tpl = new Smarty();
        $tpl = initSmartyTpl($path, $tpl, "template/");

        /*
         * Apply a template definition
         */
        $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
        $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
        $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
        $form->accept($renderer);

        if (isset($host_id) && isset($service_id)) {
            $tpl->assign('host_name', $host_name);
            $tpl->assign('service_description', $svc_description);
        }

        $tpl->assign('form', $renderer->toArray());
        $tpl->assign('o', $o);
        $tpl->display("AddSvcComment.ihtml");
    }
}
