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
include_once _CENTREON_PATH_ . "www/class/centreonService.class.php";
include_once _CENTREON_PATH_ . "www/class/centreonHost.class.php";

/*
 * Init GMT class
 */
$centreonGMT = new CentreonGMT($pearDB);
$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);
$hostStr = $centreon->user->access->getHostsString("ID", $pearDBO);

$hObj = new CentreonHost($pearDB);
$serviceObj = new CentreonService($pearDB);

if (!$centreon->user->access->checkAction("service_comment")) {
    require_once("../errors/alt_error.php");
} else {
    /*
     * Init
     */
    $debug = 0;
    $attrsTextI = array("size" => "3");
    $attrsText = array("size" => "30");
    $attrsTextarea = array("rows" => "7", "cols" => "80");

    /*
     * Form begin
     */
    $form = new HTML_QuickForm('Form', 'POST', "?p=" . $p);

    /*
     * Indicator basic information
     */
    $redirect = $form->addElement('hidden', 'o');
    $redirect->setValue($o);

    if (isset($_GET["host_id"]) && !isset($_GET["service_id"])) {
        $host_name = $hObj->getHostName($_GET['host_id']);
    } elseif (isset($_GET["host_id"]) && isset($_GET["service_id"])) {
        $serviceParameters = $serviceObj->getParameters($_GET['service_id'], array('service_description'));
        $serviceDisplayName = $serviceParameters['service_description'];
        $host_name = $hObj->getHostName($_GET['host_id']);
    }
    if (!isset($_GET['host_id'])) {
        $dtType[] = HTML_QuickForm::createElement(
            'radio',
            'commentType',
            null,
            _("Host"),
            '1',
            array('id' => 'host', 'onclick' => "toggleParams('host');")
        );
        $dtType[] = HTML_QuickForm::createElement(
            'radio',
            'commentType',
            null,
            _("Services"),
            '2',
            array('id' => 'service', 'onclick' => "toggleParams('service');")
        );
        $form->addGroup($dtType, 'commentType', _("Comment type"), '&nbsp;');

        /* ----- Hosts ----- */
        $attrHosts = array(
            'datasourceOrigin' => 'ajax',
            'availableDatasetRoute' => './api/internal.php?object=centreon_configuration_host&action=list',
            'multiple' => true,
            'linkedObject' => 'centreonHost'
        );
        $form->addElement('select2', 'host_id', _("Hosts"), array(), $attrHosts);

        if (!isset($_GET['service_id'])) {
            /* ----- Services ----- */
            $attrServices = array(
                'datasourceOrigin' => 'ajax',
                'availableDatasetRoute' =>
                    './api/internal.php?object=centreon_configuration_service&action=list&e=enable',
                'multiple' => true,
                'linkedObject' => 'centreonService'
            );
            $form->addElement('select2', 'service_id', _("Services"), array($disabled), $attrServices);
        }
    }

    $persistant = $form->addElement('checkbox', 'persistant', _("Persistent"));
    $persistant->setValue('1');

    $form->addElement('textarea', 'comment', _("Comments"), $attrsTextarea);
    $form->addRule('comment', _("Required Field"), 'required');

    $data = array();
    if (isset($_GET["host_id"]) && !isset($_GET["service_id"])) {
        $data["host_id"] = $_GET["host_id"];
        $data["commentType"] = 1;
        $focus = 'host';
        $form->addElement('hidden', 'host_id', $_GET['host_id']);
        $form->addElement('hidden', 'commentType[commentType]', $data["commentType"]);
    } elseif (isset($_GET["host_id"]) && isset($_GET["service_id"])) {
        $data["service_id"] = $_GET["host_id"] . '-' . $_GET["service_id"];
        $data["commentType"] = 2;
        $focus = 'service';
        $form->addElement('hidden', 'service_id', $data["service_id"]);
        $form->addElement('hidden', 'commentType[commentType]', $data["commentType"]);
    } else {
        $data["commentType"] = 1;
        $focus = 'host';
    }

    $form->setDefaults($data);
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));

    /* Push the comment */
    if ((isset($_POST["submitA"]) && $_POST["submitA"]) && $form->validate()) {
        $values = $form->getSubmitValues();

        if (!isset($_POST["persistant"]) || !in_array($_POST["persistant"], array('0', '1'))) {
            $_POST["persistant"] = '0';
        }

        $_POST["comment"] = str_replace("'", " ", $_POST['comment']);

        if ($values['commentType']['commentType'] == 1) {
            /*
             * Set a comment for only host
             */

            //catch fix input host_id
            if (!is_array($_POST["host_id"])) {
                $_POST["host_id"] = array($_POST["host_id"]);
            }

            foreach ($_POST["host_id"] as $host_id) {
                AddHostComment($host_id, $_POST["comment"], $_POST["persistant"]);
            }

            $valid = true;
            require_once($path . "listComment.php");
        } elseif ($values['commentType']['commentType'] == 2) {
            /*
             * Set a comment for a service list
             */

            //catch fix input service_id
            if (!is_array($_POST["service_id"])) {
                $_POST["service_id"] = array($_POST["service_id"]);
            }

            //global services comment
            if (!isset($_POST["host_id"])) {
                foreach ($_POST["service_id"] as $value) {
                    $info = explode('-', $value);
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
        }
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
        $tpl->assign('form', $renderer->toArray());

        if (isset($service_id) && isset($host_id)) {
            $tpl->assign('host_name', $host_name);
            $tpl->assign('service_description', $svc_description);
        } elseif (isset($host_id)) {
            $tpl->assign('host_name', $host_name);
        }

        $tpl->assign('form', $renderer->toArray());
        $tpl->assign('o', $o);
        $tpl->assign('focus', $focus);
        $tpl->display("AddComment.html");
    }
}
