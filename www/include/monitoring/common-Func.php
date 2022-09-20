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
 * @param int $hostId
 * @param \Pimple\Container $dependencyInjector
 * @return array
 */
function getNotifiedInfosForHost(int $hostId, \Pimple\Container $dependencyInjector) : array
{
    $results = array('contacts' => array(), 'contactGroups' => array());
    $hostInstance = Host::getInstance($dependencyInjector);
    $notifications = $hostInstance->getCgAndContacts($hostId);

    if (isset($notifications['cg']) && count($notifications['cg']) > 0) {
        $results['contactGroups'] = getContactgroups($notifications['cg']);
    }
    if (isset($notifications['contact']) && count($notifications['contact']) > 0) {
        $results['contacts'] = getContacts($notifications['contact']);
    }

    natcasesort($results['contacts']);
    natcasesort($results['contactGroups']);
    return $results;
}

/**
 * Get the list of enable contact groups (id/name)
 *
 * @param int[] $cg list contact group id
 * @return array
 */
function getContactgroups(array $cg): array
{
    global $pearDB;

    $contactGroups = array();
    $dbResult = $pearDB->query(
        'SELECT cg_id, cg_name 
        FROM contactgroup
        WHERE cg_id IN (' . implode(', ', $cg) . ')'
    );
    while (($row = $dbResult->fetchRow())) {
        $contactGroups[$row['cg_id']] = $row['cg_name'];
    }
    return $contactGroups;
}

/**
 * Get the list of enable contact (id/name)
 *
 * @param int[] $contacts list contact id
 * @return array
 */
function getContacts(array $contacts) : array
{
    global $pearDB;

    $contactsResult = array();
    $dbResult = $pearDB->query(
        'SELECT contact_id, contact_name 
        FROM contact
        WHERE contact_id IN (' . implode(', ', $contacts) . ')'
    );
    while (($row = $dbResult->fetchRow())) {
        $contactsResult[$row['contact_id']] = $row['contact_name'];
    }

    return $contactsResult;
}

/**
 * Get the notified contact/contact group of service tree inheritance
 *
 * @param int $serviceId
 * @param int $hostId
 * @param \Pimple\Container $dependencyInjector
 * @return array
 */
function getNotifiedInfosForService(int $serviceId, int $hostId, \Pimple\Container $dependencyInjector) : array
{
    $results = array('contacts' => array(), 'contactGroups' => array());

    $serviceInstance = Service::getInstance($dependencyInjector);
    $notifications = $serviceInstance->getCgAndContacts($serviceId);

    if (((!isset($notifications['cg']) || count($notifications['cg']) == 0) &&
        (!isset($notifications['contact']) || count($notifications['contact']) == 0)) ||
        $serviceInstance->getString($serviceId, 'service_use_only_contacts_from_host')
    ) {
        $results = getNotifiedInfosForHost($hostId, $dependencyInjector);
    } else {
        if (isset($notifications['cg']) && count($notifications['cg']) > 0) {
            $results['contactGroups'] = getContactgroups($notifications['cg']);
        }
        if (isset($notifications['contact']) && count($notifications['contact']) > 0) {
            $results['contacts'] = getContacts($notifications['contact']);
        }
    }

    natcasesort($results['contacts']);
    natcasesort($results['contactGroups']);
    return $results;
}
