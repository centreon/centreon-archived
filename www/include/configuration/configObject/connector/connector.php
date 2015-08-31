<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

    if (!isset ($oreon))
        exit ();

    require_once $centreon_path . 'www/class/centreonConnector.class.php';
    $path = $centreon_path . 'www/include/configuration/configObject/connector/';
    require_once $path . "DB-Func.php";

    /*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
    require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

    $connectorObj = new CentreonConnector($pearDB);

    if (isset($_REQUEST['select']))
        $select = $_REQUEST['select'];

    if (isset($_REQUEST['id']))
        $connector_id = $_REQUEST['id'];

    if (isset($_REQUEST['options']))
        $options = $_REQUEST['options'];

    switch ($o)
    {
        case "a":
            require_once($path.'formConnector.php');
        break;

        case "w":
            require_once($path.'formConnector.php');
        break;

        case "c":
            require_once($path.'formConnector.php');
        break;

        case "s":
            $myConnector = $connectorObj->read($connector_id);
            $myConnector['enabled'] = '1';
            $connectorObj->update($connector_id, $myConnector);
            require_once($path.'listConnector.php');
        break;

        case "u":
            $myConnector = $connectorObj->read($connector_id);
            $myConnector['enabled'] = '0';
            $connectorObj->update($connector_id, $myConnector);
            require_once($path.'listConnector.php');
        break;

        case "m":
            $selectedConnectors = array_keys($select);
            foreach($selectedConnectors as $connectorId)
                $connectorObj->copy($connectorId, (int)$options[$connectorId]);
            require_once($path.'listConnector.php');
        break;

        case "d":
            $selectedConnectors = array_keys($select);
            foreach($selectedConnectors as $connectorId)
                $connectorObj->delete($connectorId);
            require_once($path.'listConnector.php');
        break;

        default:
            require_once($path.'listConnector.php');
        break;
    }

?>
