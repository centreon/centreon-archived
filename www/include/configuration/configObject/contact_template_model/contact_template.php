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
$cG ? $contact_id = $cG : $contact_id = $cP;

isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = null;
isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = null;
$cG ? $select = $cG : $select = $cP;

isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = null;
isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = null;
$cG ? $dupNbr = $cG : $dupNbr = $cP;

/*
 * Path to the configuration dir
 */
$path = "./include/configuration/configObject/contact_template_model/";

/*
 * PHP functions
 */
require_once "./include/configuration/configObject/contact/DB-Func.php";
require_once "./include/common/common-Func.php";

/* Set the real page */
if ($ret['topology_page'] != "" && $p != $ret['topology_page']) {
    $p = $ret['topology_page'];
}

$contactObj = new CentreonContact($pearDB);

/**
 * @var $eventDispatcher EventDispatcher
 */
$eventDispatcher = $dependencyInjector[ServiceProvider::CENTREON_EVENT_DISPATCHER];
$eventContext = 'contact.template.form';

if (!is_null($eventDispatcher->getDispatcherLoader())) {
    $eventDispatcher->getDispatcherLoader()->load();
}

$eventDispatcher->addEventHandler(
    $eventContext,
    EventDispatcher::EVENT_DUPLICATE,
    (function (): EventHandler {
        $handler = new EventHandler();
        $handler->setProcessing(function (array $arguments) {
            if (isset($arguments['contact_ids'], $arguments['numbers'])) {
                $newContactIds = multipleContactInDB(
                    $arguments['contact_ids'],
                    $arguments['numbers']
                );

                // We store the result for possible future use
                return ['new_contact_ids' => $newContactIds];
            }
        });

        return $handler;
    })()
);

/*
 * We add the delete event in the context named 'contact.template.form' for and event type
 * EventDispatcher::EVENT_DELETE
 */
$eventDispatcher->addEventHandler(
    $eventContext,
    EventDispatcher::EVENT_DELETE,
    (function () {
        // We define an event to delete a list of contacts
        $handler = new EventHandler();
        $handler->setProcessing(function ($arguments) {
            if (isset($arguments['contact_ids'])) {
                deleteContactInDB($arguments['contact_ids']);
            }
        });

        return $handler;
    })()
);

switch ($o) {
    case "mc":
        require_once($path . "formContactTemplateModel.php");
        break; // Massive Change
    case "a":
        require_once($path . "formContactTemplateModel.php");
        break; // Add a contact template
    case "w":
        require_once($path . "formContactTemplateModel.php");
        break; // Watch a contact template
    case "c":
        require_once($path . "formContactTemplateModel.php");
        break; // Modify a contact template
    case "s":
        if (isCSRFTokenValid()) {
            enableContactInDB($contact_id);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listContactTemplateModel.php");
        break; // Activate a contact template
    case "ms":
        if (isCSRFTokenValid()) {
            enableContactInDB(null, isset($select) ? $select : array());
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listContactTemplateModel.php");
        break;
    case "u":
        if (isCSRFTokenValid()) {
            disableContactInDB($contact_id);
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listContactTemplateModel.php");
        break; // Desactivate a contact
    case "mu":
        if (isCSRFTokenValid()) {
            disableContactInDB(null, isset($select) ? $select : array());
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listContactTemplateModel.php");
        break;
    case "m":
        if (isCSRFTokenValid()) {
            // We notify that we have made a duplicate
            $eventDispatcher->notify(
                $eventContext,
                EventDispatcher::EVENT_DUPLICATE,
                [
                    'contact_ids' => isset($select) ? $select : [],
                    'numbers' => $dupNbr
                ]
            );
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listContactTemplateModel.php");
        break; // Duplicate n contacts
    case "d":
        if (isCSRFTokenValid()) {
            // We notify that we have made a delete
            $eventDispatcher->notify(
                $eventContext,
                EventDispatcher::EVENT_DELETE,
                ['contact_ids' => isset($select) ? $select : []]
            );
        } else {
            unvalidFormMessage();
        }
        require_once($path . "listContactTemplateModel.php");
        break; // Delete n contacts
    default:
        require_once($path . "listContactTemplateModel.php");
        break;
}
