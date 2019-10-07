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

$configFile = realpath(dirname(__FILE__) . "/../../../config/centreon.config.php");
require_once __DIR__ . '/../../class/config-generate/host.class.php';
require_once __DIR__ . '/../../class/config-generate/service.class.php';


if (!isset($centreon)) {
    exit();
}

function getMyHostRow($host_id = null, $rowdata)
{
    global $pearDB;
    if (!$host_id) {
        exit();
    }
    while (1) {
        $DBRESULT = $pearDB->query("SELECT host_" . $rowdata .
            ", host_template_model_htm_id FROM host WHERE host_id = '" . CentreonDB::escape($host_id) . "' LIMIT 1");
        $row = $DBRESULT->fetchRow();
        if ($row["host_" . $rowdata]) {
            return $row["host_$rowdata"];
        } elseif ($row["host_template_model_htm_id"]) {
            $host_id = $row["host_template_model_htm_id"];
        } else {
            break;
        }
    }
}

function get_user_param($user_id, $pearDB)
{
    $list_param = array(
        'ack_sticky',
        'ack_notify',
        'ack_persistent',
        'ack_services',
        'force_active',
        'force_check'
    );
    $tab_row = array();
    foreach ($list_param as $param) {
        if (isset($_SESSION[$param])) {
            $tab_row[$param] = $_SESSION[$param];
        }
    }
    return $tab_row;
}

function set_user_param($user_id, $pearDB, $key, $value)
{
    $_SESSION[$key] = $value;
}

/**
 * Get the notified contact/contact group of host tree inheritance
 *
 * @param $hostId
 * @param $dependencyInjector
 * @return array
 */
function getNotifiedInfosForHost($hostId, $dependencyInjector)
{
    $hostInfo = array();
    $hostInstance = Host::getInstance($dependencyInjector);
    $results = array('contacts' => array(), 'contactGroups' => array());

    $hostInfo['host_id'] = (int)$hostId;
    $listHostsContact = array_unique($hostInstance->listHostsWithContacts($hostInfo));
    $listHostsContactGroup = array_unique($hostInstance->listHostsWithContactGroups($hostInfo));

    foreach ($listHostsContact as $host) {
        $contacts = getContactsForHost($host);
        $results['contacts'] = $results['contacts'] + $contacts;
    }
    foreach ($listHostsContactGroup as $host) {
        $contactGroups = getContactGroupsForHost($host);
        $results['contactGroups'] = $results['contactGroups'] + $contactGroups;
    }
    natcasesort($results['contacts']);
    natcasesort($results['contactGroups']);
    return $results;
}

/**
 * Get the list of enable contact groups (id/name) for a host
 *
 * @param $hostId
 * @return array
 */
function getContactgroupsForHost($hostId)
{
    global $pearDB;

    $contactGroups = array();
    $dbResult = $pearDB->query(
        'SELECT contactgroup.cg_id, contactgroup.cg_name 
        FROM contactgroup, contactgroup_host_relation 
        WHERE contactgroup_host_relation.host_host_id = ' . (int)$hostId . '
        AND contactgroup_host_relation.contactgroup_cg_id = contactgroup.cg_id
        AND contactgroup.cg_activate = "1"'
    );
    while (($row = $dbResult->fetchRow())) {
        $contactGroups[$row['cg_id']] = $row['cg_name'];
    }
    return $contactGroups;
}

/**
 * Get the list of enable contact (id/name) for a host
 *
 * @param $hostId
 * @return array
 */
function getContactsForHost($hostId)
{
    global $pearDB;

    $contacts = array();
    $dbResult = $pearDB->query(
        'SELECT contact.contact_id, contact.contact_name 
        FROM contact, contact_host_relation 
        WHERE contact_host_relation.host_host_id = ' . (int)$hostId . '
        AND contact_host_relation.contact_id = contact.contact_id
        AND contact.contact_activate = "1" 
        AND contact.contact_enable_notifications != "0"'
    );
    while (($row = $dbResult->fetch())) {
        $contacts[$row['contact_id']] = $row['contact_name'];
    }
    return $contacts;
}

/**
 * Get the notified contact/contact group of service tree inheritance
 *
 * @param $serviceId
 * @param $hostId
 * @param $dependencyInjector
 * @return array
 */
function getNotifiedInfosForService($serviceId, $hostId, $dependencyInjector)
{
    $serviceInfo = array();
    $results = array('contacts' => array(), 'contactGroups' => array());
    $serviceInstance = Service::getInstance($dependencyInjector);
    $serviceInfo['service_id'] = (int)$serviceId;

    $listServicesContact = $serviceInstance->listServicesWithContacts($serviceInfo);
    $listServicesContactGroup = $serviceInstance->listServicesWithContactGroups($serviceInfo);

    if ((empty($listServicesContact) && empty($listServicesContactGroup))
        || $serviceInfo['service_use_only_contacts_from_host']
    ) {
        $results = getNotifiedInfosForHost($hostId, $dependencyInjector);
    } else {
        foreach ($listServicesContact as $service) {
            $contacts = getContactsForService($service);
            $results['contacts'] = $results['contacts'] + $contacts;
        }
        foreach ($listServicesContactGroup as $service) {
            $contactGroups = getContactgroupsForService($service);
            $results['contactGroups'] = $results['contactGroups'] + $contactGroups;
        }
    }
    natcasesort($results['contacts']);
    natcasesort($results['contactGroups']);
    return $results;
}

/**
 * Get the list of enable contact groups (id/name) for a service
 *
 * @param $serviceId
 * @return array
 */
function getContactgroupsForService($serviceId)
{
    global $pearDB;
    $contactGroups = array();
    $dbResult = $pearDB->query(
        'SELECT contactgroup.cg_id, contactgroup.cg_name 
        FROM contactgroup, contactgroup_service_relation 
        WHERE contactgroup_service_relation.service_service_id = ' . (int)$serviceId . '
        AND contactgroup_service_relation.contactgroup_cg_id = contactgroup.cg_id
        AND contactgroup.cg_activate = "1"'
    );
    while (($row = $dbResult->fetch())) {
        $contactGroups[$row['cg_id']] = $row['cg_name'];
    }
    return $contactGroups;
}

/**
 * Get the list of enable contact (id/name) for a host
 *
 * @param $serviceId
 * @return array
 */
function getContactsForService($serviceId)
{
    global $pearDB;
    $contacts = array();
    $dbResult = $pearDB->query(
        'SELECT contact.contact_id, contact.contact_name 
        FROM contact, contact_service_relation 
        WHERE contact_service_relation.service_service_id = ' . (int)$serviceId . '
        AND contact_service_relation.contact_id = contact.contact_id
        AND contact.contact_activate = "1" 
        AND contact.contact_enable_notifications != "0"'
    );
    while (($row = $dbResult->fetch())) {
        $contacts[$row['contact_id']] = $row['contact_name'];
    }

    return $contacts;
}
