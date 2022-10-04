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

if (!isset($oreon)) {
    exit();
}

require_once _CENTREON_PATH_ . 'www/class/centreonNotification.class.php';

/*
 * Connect to Database
 */
$pearDBO = new CentreonDB("centstorage");

/**
 * Get user list
 */
$contact = array("" => null);
$DBRESULT = $pearDB->query("SELECT contact_id, contact_alias FROM contact ORDER BY contact_alias");
while ($ct = $DBRESULT->fetchRow()) {
    $contact[$ct["contact_id"]] = $ct["contact_alias"];
}
$DBRESULT->closeCursor();

/*
 * Object init
 */
$mediaObj       = new CentreonMedia($pearDB);
$host_method    = new CentreonHost($pearDB);
$oNotification     = new CentreonNotification($pearDB);

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

/*
 * start header menu
 */
$tpl->assign("headerMenu_host", _("Hosts"));
$tpl->assign("headerMenu_service", _("Services"));
$tpl->assign("headerMenu_host_esc", _("Escalated Hosts"));
$tpl->assign("headerMenu_service_esc", _("Escalated Services"));

/*
 * Different style between each lines
 */
$style = "one";

$groups = "''";
if (isset($_POST["contact"])) {
    $contactId = (int)htmlentities($_POST["contact"], ENT_QUOTES, "UTF-8");
} else {
    $contactId = 0;
}

$formData = array('contact' => $contactId);

/*
 * Create select form
 */
$form = new HTML_QuickFormCustom('select_form', 'GET', "?p=" . $p);

$form->addElement('select', 'contact', _("Contact"), $contact, array('id' => 'contact', 'onChange' => 'submit();'));
$form->setDefaults($formData);

/*
 * Host escalations
 */
$elemArrHostEsc = array();
if ($contactId) {
    $hostEscResources = $oNotification->getNotifications(2, $contactId);
}
if (isset($hostEscResources)) {
    foreach ($hostEscResources as $hostId => $hostName) {
        $elemArrHostEsc[] = array(
            "MenuClass" => "list_" . $style,
            "RowMenu_hico" => "./img/icons/host.png",
            "RowMenu_host" => myDecode($hostName)
        );
        $style != "two" ? $style = "two" : $style = "one";
    }
}
$tpl->assign("elemArrHostEsc", $elemArrHostEsc);


/*
 * Service escalations
 */
$elemArrSvcEsc = array();
if ($contactId) {
    $svcEscResources = $oNotification->getNotifications(3, $contactId);
}
if (isset($svcEscResources)) {
    foreach ($svcEscResources as $hostId => $hostTab) {
        foreach ($hostTab as $serviceId => $tab) {
            $elemArrSvcEsc[] = array(
                "MenuClass" => "list_" . $style,
                "RowMenu_hico" => "./img/icons/host.png",
                "RowMenu_host" => myDecode($tab['host_name']),
                "RowMenu_service" => myDecode($tab['service_description'])
            );
            $style != "two" ? $style = "two" : $style = "one";
        }
    }
}
$tpl->assign("elemArrSvcEsc", $elemArrSvcEsc);

/*
 * Hosts
 */
$elemArrHost = array();
if ($contactId) {
    $hostResources = $oNotification->getNotifications(0, $contactId);
}
if (isset($hostResources)) {
    foreach ($hostResources as $hostId => $hostName) {
        $elemArrHost[] = array(
            "MenuClass" => "list_" . $style,
            "RowMenu_hico" => "./img/icons/host.png",
            "RowMenu_host" => myDecode($hostName)
        );
        $style != "two" ? $style = "two" : $style = "one";
    }
}
$tpl->assign("elemArrHost", $elemArrHost);

/*
 * Services
 */
$elemArrSvc = array();
if ($contactId) {
    $svcResources = $oNotification->getNotifications(1, $contactId);
}
if (isset($svcResources)) {
    foreach ($svcResources as $hostId => $hostTab) {
        foreach ($hostTab as $serviceId => $tab) {
            $elemArrSvc[] = array(
                "MenuClass" => "list_" . $style,
                "RowMenu_hico" => "./img/icons/host.png",
                "RowMenu_host" => myDecode($tab['host_name']),
                "RowMenu_service" => myDecode($tab['service_description'])
            );
            $style != "two" ? $style = "two" : $style = "one";
        }
    }
}
$tpl->assign("elemArrSvc", $elemArrSvc);

$labels = array(
    'host_escalation' => _('Host escalations'),
    'service_escalation' => _('Service escalations'),
    'host_notifications' => _('Host notifications'),
    'service_notifications' => _('Service notifications')
);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('msgSelect', _("Please select a user in order to view his notifications"));
$tpl->assign('p', $p);
$tpl->assign('contact', $contactId);
$tpl->assign('labels', $labels);
$tpl->display("displayNotification.ihtml");
