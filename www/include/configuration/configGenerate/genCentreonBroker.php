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
    if (!isset($oreon))
		exit();

	require_once $centreon_path . "/www/class/centreonXML.class.php";

	$dir_conf = $centreonBrokerPath . '/' . $tab['id'];

	if (!is_dir($dir_conf) && is_writable($centreonBrokerPath)) {
	    mkdir($dir_conf);
	}

	$ns_id = $tab['id'];
	
	$files = array();

	$query = "SELECT cs.config_filename, csi.config_key, csi.config_value, csi.config_group, csi.config_group_id, ns.name 
		FROM cfg_centreonbroker_info csi, cfg_centreonbroker cs, nagios_server ns
		WHERE csi.config_id = cs.config_id AND cs.config_activate = 1 AND cs.ns_nagios_server = ns.id AND cs.ns_nagios_server = " . $ns_id;

	$res = $pearDB->query($query);
    if (false === PEAR::isError($res) && $res->numRows()) {
	    $ns_name = null;
	    while ($row = $res->fetchRow()) {
    	    $filename = $row['config_filename'];
            if (!isset($files[$filename])) {
                $files[$filename]['output'] = array();
        	    $files[$filename]['input'] = array();
        	    $files[$filename]['logger'] = array();
            }
	        if (is_null($ns_name)) {
	            $ns_name = $row['name'];
	        }
	        $files[$filename][$row['config_group']][$row['config_group_id']][$row['config_key']] = $row['config_value'];
	    }
	    
	    foreach ($files as $filename => $groups) {
    	    $fileXml = new CentreonXML();
    	    $fileXml->startElement('centreonBroker');
    	    
    	    $fileXml->writeElement('instance', $ns_id);
    	    $fileXml->writeElement('instance_name', $ns_name);
    	    
    	    foreach ($groups as $group => $listInfos) {
    	        if (count($listInfos) > 0) {
        	        $fileXml->startElement($group);
        	        foreach ($listInfos as $infos) {
        	            foreach ($infos as $key => $value) {
        	                if (trim($value) != '') {
        	                    $fileXml->writeElement($key, $value);
        	                }
        	            }
        	        }
        	        $fileXml->endElement();
    	        }
    	    }
    	    $fileXml->endElement();
    
    	    ob_start();
            $fileXml->output();
            file_put_contents($dir_conf . '/' . $filename, ob_get_contents());
            ob_end_clean();
	    }
	}
?>