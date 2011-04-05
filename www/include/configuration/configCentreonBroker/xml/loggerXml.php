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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

    include_once("@CENTREON_ETC@/centreon.conf.php");

	require_once $centreon_path . "/www/class/centreonDB.class.php";
	require_once $centreon_path . "/www/class/centreonXML.class.php";

    /*
	 * start init db
	 */
	$db = new CentreonDB();
	$xml = new CentreonXML();

	$xml->startElement('root');
	$xml->startElement('main');
	/*
	 * Translate
	 */
	$xml->startElement('lang');
	$xml->writeElement('yes', _('Yes'));
	$xml->writeElement('no', _('No'));
	$xml->writeElement('type', _('Type'));
	$xml->writeElement('file', _('File'));
	$xml->writeElement('standard', _('Standard'));
	$xml->writeElement('syslog', _('Syslog'));
	$xml->writeElement('logging_config', _('Logging Config'));
	$xml->writeElement('logging_debug', _('Logging Debug'));
	$xml->writeElement('logging_info', _('Logging Info'));
	$xml->writeElement('logging_error', _('Logging Error'));
	$xml->writeElement('logging_level', _('Logging level'));
	$xml->writeElement('file_for_logging', _('File for logging'));
	$xml->writeElement('logging_output', _('Logging Output'));
	$xml->writeElement('standard_output', _('Standard Output'));
	$xml->writeElement('standard_error', _('Standard Error'));
	$xml->writeElement('standard_log', _('Standard Log'));
	$xml->endElement(); /* lang */
	$xml->endElement(); /* main */

	if (isset($_GET['config_id']) && $_GET['config_id'] != 0) {
	    $query = "SELECT config_key, config_value, config_group_id FROM cfg_centreonbroker_info WHERE config_id = " . $_GET['config_id'] . " AND config_group = 'logger' ORDER BY config_group_id";
	    $res = $db->query($query);
	    if (!PEAR::isError($res)) {
	        $infos = array();
	        while ($row = $res->fetchRow()) {
	            $infos[$row['config_group_id']][$row['config_key']] = $row['config_value'];
	        }
	        foreach ($infos as $id => $info) {
	            $xml->startElement('logger');
	            $xml->writeElement('id', $id);
	            $type = '';
	            $name = null;
	            foreach ($info as $key => $value) {
	                if ($key == 'type') {
	                    $type = $value;
	                }
	                if ($key == 'config' || $key == 'debug' || $key == 'info' || $key == 'error') {
	                    if ($value == '0') {
	                        $xml->writeElement($key, 'false');
	                    } else {
	                        $xml->writeElement($key, 'true');
	                    }
	                } elseif ($key == 'name') {
	                    $name = $value;
	                } else {
                        $xml->writeElement($key, $value);
	                }
	            }
	            if (!is_null($name) && $type == 'file') {
	                $xml->writeElement('file', $name);
	            } elseif (!is_null($name) && $type == 'standard') {
	                $xml->writeElement('output', $name);
	            }
	            $xml->endElement();
	        }
	    }
	} else {
    	/*
    	 * New Element
    	 */
    	$xml->startElement('logger');
    	$xml->writeElement('id', $_GET['pos_id']);
    	/*
    	 * Default values
    	 */
    	$xml->writeElement('config', 'false');
    	$xml->writeElement('debug', 'false');
    	$xml->writeElement('info', 'false');
    	$xml->writeElement('error', 'false');
    	$xml->endElement();
	}

	/*
	 * Display
	 */
	header('Content-Type: text/xml');
	$xml->output();
?>