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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configObject/traps/GetXMLTrapsForVendor.php $
 * SVN : $Id: GetXMLTrapsForVendor.php 8144 2009-05-20 21:11:21Z jmathis $
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
    $xml->writeElement('advancedLabel', _('Advanced parameters'));
    $xml->writeElement('addLabel', _('Add a new matching rule'));
    $xml->writeElement('regexpLabel', _('Regexp') . ' : ');
    $xml->writeElement('statusLabel', _('Status') . ' : ');
    $xml->writeElement('orderLabel', _('Order') . ' : ');
    $xml->writeElement('addImg', trim('./img/icones/16x16/navigate_plus.gif'));
    $xml->writeElement('rmImg', trim('./img/icones/16x16/delete.gif'));
    $xml->writeElement('okLabel', _('OK'));
    $xml->writeElement('warningLabel', _('Warning'));
    $xml->writeElement('criticalLabel', _('Critical'));
    $xml->writeElement('confirmDeletion', _('Do you really wish to remove this entry?'));
    $xml->endElement();
    
    if (isset($_GET['trapId']) && $_GET['trapId']) {
        $trapId = htmlentities($_GET['trapId'], ENT_QUOTES);
        $res = $db->query("SELECT * FROM traps_matching_properties WHERE trap_id = '".$trapId."' ORDER BY tmo_order ASC");
        $style = 'list_two';
        while ($row = $res->fetchRow()) {
            $style == 'list_one' ? $style = 'list_two' : $style = 'list_one';
            $xml->startElement('trap');
            $xml->writeElement('regexp', $row['tmo_regexp']);
            $xml->writeElement('status', $row['tmo_status']);
            $xml->writeElement('order', $row['tmo_order']);
            $xml->writeElement('style', $style);
            $xml->endElement();            
        }
    }
	
	$xml->endElement();	
	header('Content-Type: text/xml');
	$xml->output();
?>