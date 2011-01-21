<?php
/*
 * Copyright 2005-2010 MERETHIS
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
 * SVN : $URL: $
 * SVN : $Id: $
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
	$xml->writeElement('addImg', trim('./img/icones/16x16/navigate_plus.gif'));
    $xml->writeElement('rmImg', trim('./img/icones/16x16/delete.gif'));
	/* The labels */
	$xml->startElement('labels');
	$xml->writeElement('addHost', _("Add a LDAP server"));
	$xml->writeElement('hostname', _("Hostname"));
	$xml->writeElement('port', _("Port"));
	$xml->writeElement('ssl', _("SSL"));
	$xml->writeElement('tls', _("TLS"));
	$xml->writeElement('order', _("Order"));
	$xml->writeElement('confirmDeletion', _('Do you really wish to remove this entry?'));
	$xml->endElement(); /* End label */
	$xml->endElement(); /* End main */
	
	$ldapHost = array();
	$query = "SELECT ar.ar_id, ar.ar_order, ari.ari_name, ari.ari_value 
		FROM auth_ressource ar, auth_ressource_info ari 
		WHERE ar.ar_type = 'ldap' AND ar.ar_id = ari.ar_id AND ari.ari_name IN ('host', 'port', 'use_ssl', 'use_tls') ORDER BY ar.ar_order";
	$res = $db->query($query);
	if (false === PEAR::isError($res)) {
	    while ($row = $res->fetchRow()) {
	        if (!isset($ldapHost[$row['ar_id']])) {
	            $ldapHost[$row['ar_id']] = array('host' => '', 'port' => 389, 'use_ssl' => 0, 'use_tls' => 0, 'order' => $row['ar_order']);
	        }
	        $ldapHost[$row['ar_id']][$row['ari_name']] = $row['ari_value'];
	    }
	}
	
	/* The hosts ldap */
	if (count($ldapHost)) {
    	
    	foreach ($ldapHost as $id => $hostInfo) {
    	    $xml->startElement('ldap_host');
    	    $xml->writeElement('id', $id);
    	    $xml->writeElement('hostname', $hostInfo['host']);
    	    $xml->writeElement('port', $hostInfo['port']);
    	    if ($hostInfo['use_ssl'] == 1) {
    	        $xml->writeElement('ssl', 'checked');
    	    } else {
    	        $xml->writeElement('ssl', '');
    	    }
    	    if ($hostInfo['use_tls'] == 1) {
    	        $xml->writeElement('tls', 'checked');
    	    } else {
    	        $xml->writeElement('tls', '');
    	    }
    	    $xml->writeElement('order', $hostInfo['order']);
    	    $xml->endElement(); /* End ldap_host */
    	}
    	
	}
	$xml->endElement(); /* End root */
	
	header('Content-Type: text/xml');
	$xml->output();
?>