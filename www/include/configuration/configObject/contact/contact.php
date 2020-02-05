<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

use Centreon\ServiceProvider;
use Centreon\Infrastructure\Event\EventDispatcher;
use Centreon\Infrastructure\Event\EventHandler;

if (!isset($centreon)) {
    exit();
}

isset($_GET["contact_id"]) ? $cG = $_GET["contact_id"] : $cG = null;
isset($_POST["contact_id"]) ? $cP = $_POST["contact_id"] : $cP = null;
$cG ? $contactId = $cG : $contactId = $cP;

isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = null;
isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = null;
$cG ? $select = $cG : $select = $cP;

isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = null;
isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = null;
$cG ? $dupNbr = $cG : $dupNbr = $cP;

/*
 * Path to the configuration dir
 */
$path = "./include/configuration/configObject/contact/";

/*
 * PHP functions
 */
require_once $path . "DB-Func.php";
require_once "./include/common/common-Func.php";

/* Set the real page */
if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

$acl = $oreon->user->access;
$allowedAclGroups = $acl->getAccessGroups();

/**
 * @var $eventDispatcher EventDispatcher
 */
$eventDispatcher = $dependencyInjector[ServiceProvider::CENTREON_EVENT_DISPATCHER];

if(! is_null($eventDispatcher->getDispatcherLoader())) {
    $eventDispatcher->getDispatcherLoader()->load();
}

$duplicateEventHandler = new EventHandler();
$duplicateEventHandler->setProcessing(
    function (array $arguments) {
        if (isset($arguments['contact_ids'], $arguments['numbers'])) {
            $newContactIds = multipleContactInDB(
                $arguments['contact_ids'],
                $arguments['numbers']
            );
            // We store the result for possible future use
            return ['new_contact_ids' => $newContactIds];
        }
    }
);
$eventDispatcher->addEventHandler(
    'contact.form',
    EventDispatcher::EVENT_DUPLICATE,
    $duplicateEventHandler
);

/*
 * We define a event to delete a list of contacts
 */
$deleteEventHandler = new EventHandler();
$deleteEventHandler->setProcessing(
    function ($arguments) {
        if (isset($arguments['contact_ids'])) {
            deleteContactInDB($arguments['contact_ids']);
        }
    }
);
/*
 * We add the delete event in the context named 'contact.form' for and event type
 * EventDispatcher::EVENT_DELETE
 */
$eventDispatcher->addEventHandler(
    'contact.form',
    EventDispatcher::EVENT_DELETE,
    $deleteEventHandler
);

/*
 * Defining an event to manually request a LDAP synchronization of an array of contacts
 */
$synchronizeEventHandler = new EventHandler();
$synchronizeEventHandler->setProcessing(
    function ($arguments) {
        if (isset($arguments['contact_ids'])) {
            synchronizeContactWithLdap($arguments['contact_ids']);
        }
    }
);
$eventDispatcher->addEventHandler(
    'contact.form',
    EventDispatcher::EVENT_SYNCHRONIZE,
    $synchronizeEventHandler
);

switch ($o) {
    case "li":
        require_once($path . "ldapImportContact.php");
        break; # LDAP import form	# Wistof
    case "mc":
        require_once($path . "formContact.php");
        break; # Massive Change
    case "a":
        require_once($path . "formContact.php");
        break; #Add a contact
    case "w":
        require_once($path . "formContact.php");
        break; #Watch a contact
    case "c":
        require_once($path . "formContact.php");
        break; #Modify a contact
    case "s":
        enableContactInDB($contactId);
        require_once($path . "listContact.php");
        break; #Activate a contact
    case "ms":
        enableContactInDB(null, isset($select) ? $select : array());
        require_once($path . "listContact.php");
        break;
    case "u":
        disableContactInDB($contactId);
        require_once($path . "listContact.php");
        break; #Desactivate a contact
    case "mu":
        disableContactInDB(null, isset($select) ? $select : array());
        require_once($path . "listContact.php");
        break;
    case "m":
        // We notify that we have made a duplicate
        $eventDispatcher->notify(
            'contact.form',
            EventDispatcher::EVENT_DUPLICATE,
            [
                'contact_ids' => $select,
                'numbers' => $dupNbr
            ]
        );

        require_once($path . "listContact.php");
        break; #Duplicate n contacts
    case "d":
        // We notify that we have made a delete
        $eventDispatcher->notify(
            'contact.form',
            EventDispatcher::EVENT_DELETE,
            ['contact_ids' => $select]
        );
        require_once($path . "listContact.php");
        break; #Delete n contacts
    case "dn":
        require_once $path . 'displayNotification.php';
        break;
    case "sync":
        $eventDispatcher->notify(
            'contact.form',
            EventDispatcher::EVENT_SYNCHRONIZE,
            ['contact_ids' => $select]
        );
        require_once($path . "listContact.php");
        break; #Synchronize selected contacts with the LDAP
    default:
        require_once($path . "listContact.php");
        break;
}
