<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/configuration/configObject/connector/connector.php $
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
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
    
    $connectorObj = new CentreonConnector($pearDB);
    
    if(isset($_REQUEST['o']))
        $o = $_REQUEST['o'];

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
