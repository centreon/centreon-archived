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
 * Get host icones
 */
$ehiCache = array();
$DBRESULT = $pearDB->query("SELECT ehi_icon_image, host_host_id FROM extended_host_information");
while ($ehi = $DBRESULT->fetchRow()) {
    $ehiCache[$ehi["host_host_id"]] = $ehi["ehi_icon_image"];
}
$DBRESULT->free();

/**
 * Get user list
 */
$contact = array("" => null);
$DBRESULT = $pearDB->query("SELECT contact_id, contact_alias FROM contact ORDER BY contact_alias");
while ($ct = $DBRESULT->fetchRow()) {
    $contact[$ct["contact_id"]] = $ct["contact_alias"];
}
$DBRESULT->free();

/*
 * Object init
 */
$mediaObj       = new CentreonMedia($pearDB);
$host_method    = new CentreonHost($pearDB);
$contactObj     = new CentreonContact($pearDB);

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
    $contact_id = (int)htmlentities($_POST["contact"], ENT_QUOTES, "UTF-8");
} else {
    $contact_id = 0;
    $formData = array('contact' => $contact_id);
}

$formData = array('contact' => $contact_id);

/*
 * Create select form
 */
$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);

$form->addElement('select', 'contact', _("Contact"), $contact, array('id'=>'contact', 'onChange'=>'submit();'));
$form->setDefaults($formData);

/*
 * Host escalations
 */
$elemArrHostEsc = array();
if ($contact_id) {
    $hostEscResources = $contactObj->getNotifications(2, $contact_id);
}
if (isset($hostEscResources)) {
    foreach ($hostEscResources as $hostId => $hostName) {
        if ((isset($ehiCache[$hostId]) && $ehiCache[$hostId])) {
            $host_icone = "./img/media/" . $mediaObj->getFilename($ehiCache[$hostId]);
        } elseif ($icone = $host_method->replaceMacroInString($hostId, getMyHostExtendedInfoImage($hostId, "ehi_icon_image", 1))) {
            $host_icone = "./img/media/" . $icone;
        } else {
            $host_icone = "./img/icons/host.png";
        }
        $moptions = "";
        $elemArrHostEsc[] = array("MenuClass"    => "list_".$style,
                                  "RowMenu_hico" => $host_icone,
                                  "RowMenu_host" => myDecode($hostName));
        $style != "two" ? $style = "two" : $style = "one";
    }
}
$tpl->assign("elemArrHostEsc", $elemArrHostEsc);


/*
 * Service escalations
 */
$elemArrSvcEsc = array();
if ($contact_id) {
    $svcEscResources = $contactObj->getNotifications(3, $contact_id);
}
if (isset($svcEscResources)) {
    foreach ($svcEscResources as $hostId => $hostTab) {
        foreach ($hostTab as $serviceId => $tab) {
            if ((isset($ehiCache[$hostId]) && $ehiCache[$hostId])) {
                $host_icone = "./img/media/" . $mediaObj->getFilename($ehiCache[$hostId]);
            } elseif ($icone = $host_method->replaceMacroInString($hostId, getMyHostExtendedInfoImage($hostId, "ehi_icon_image", 1))) {
                $host_icone = "./img/media/" . $icone;
            } else {
                $host_icone = "./img/icons/host.png";
            }
            $moptions = "";
            $elemArrSvcEsc[] = array("MenuClass"       => "list_".$style,
                                  "RowMenu_hico"    => $host_icone,
                                  "RowMenu_host"    => myDecode($tab['host_name']),
                                  "RowMenu_service" => myDecode($tab['service_description']));
            $style != "two" ? $style = "two" : $style = "one";
        }
    }
}
$tpl->assign("elemArrSvcEsc", $elemArrSvcEsc);

/*
 * Hosts
 */
$elemArrHost = array();
if ($contact_id) {
    $hostResources = $contactObj->getNotifications(0, $contact_id);
}
if (isset($hostResources)) {
    foreach ($hostResources as $hostId => $hostName) {
        if ((isset($ehiCache[$hostId]) && $ehiCache[$hostId])) {
            $host_icone = "./img/media/" . $mediaObj->getFilename($ehiCache[$hostId]);
        } elseif ($icone = $host_method->replaceMacroInString($hostId, getMyHostExtendedInfoImage($hostId, "ehi_icon_image", 1))) {
            $host_icone = "./img/media/" . $icone;
        } else {
            $host_icone = "./img/icons/host.png";
        }
        $moptions = "";
        $elemArrHost[] = array("MenuClass"    => "list_".$style,
                               "RowMenu_hico" => $host_icone,
                               "RowMenu_host" => myDecode($hostName));
        $style != "two" ? $style = "two" : $style = "one";
    }
}
$tpl->assign("elemArrHost", $elemArrHost);

/*
 * Services
 */
$elemArrSvc = array();
if ($contact_id) {
    $svcResources = $contactObj->getNotifications(1, $contact_id);
}
if (isset($svcResources)) {
    foreach ($svcResources as $hostId => $hostTab) {
        foreach ($hostTab as $serviceId => $tab) {
            if ((isset($ehiCache[$hostId]) && $ehiCache[$hostId])) {
                $host_icone = "./img/media/" . $mediaObj->getFilename($ehiCache[$hostId]);
            } elseif ($icone = $host_method->replaceMacroInString($hostId, getMyHostExtendedInfoImage($hostId, "ehi_icon_image", 1))) {
                $host_icone = "./img/media/" . $icone;
            } else {
                $host_icone = "./img/icons/host.png";
            }
            $moptions = "";
            $elemArrSvc[] = array("MenuClass"       => "list_".$style,
                                  "RowMenu_hico"    => $host_icone,
                                  "RowMenu_host"    => myDecode($tab['host_name']),
                                  "RowMenu_service" => myDecode($tab['service_description']));
            $style != "two" ? $style = "two" : $style = "one";
        }
    }
}
$tpl->assign("elemArrSvc", $elemArrSvc);

$labels = array('host_escalation'       => _('Host escalations'),
                'service_escalation'    => _('Service escalations'),
                'host_notifications'    => _('Host notifications'),
                'service_notifications' => _('Service notifications'));

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('msg', _("The selected user didn't see any resources"));
$tpl->assign('msgSelect', _("Please select a user in order to view his notifications"));
$tpl->assign('msgdisable', _("The selected user is not enable."));
$tpl->assign('p', $p);
$tpl->assign('i', $i);
$tpl->assign('contact', $contact_id);
$tpl->assign('labels', $labels);
$tpl->display("displayNotification.ihtml");
