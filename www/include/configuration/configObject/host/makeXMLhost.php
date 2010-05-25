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
 * SVN : $URL$
 * SVN : $Id$
 * 
 */
 
	header('Content-Type: text/xml');
	header('Cache-Control: no-cache');
	
	require_once "@CENTREON_ETC@/centreon.conf.php";
	require_once $centreon_path."/www/class/centreonDB.class.php";	
	require_once $centreon_path."/www/class/centreonXML.class.php";
		
	$pearDB = new CentreonDB();
	
	$DBRESULT =& $pearDB->query("SELECT `host_id`, `host_name` FROM `host` WHERE `host_register` = '0' ORDER BY `host_name`");
	
	/*
	 *  The first element of the select is empty
	 */
	$buffer = new CentreonXML();
	$buffer->startElement("template_data");
	$buffer->startElement("template");
	$buffer->writeElement("tp_id", "0");
	$buffer->writeElement("tp_alias", "empty");
	$buffer->endElement();	
	
	/*
	 *  Now we fill out the select with templates id and names
	 */
	while ($h =& $DBRESULT->fetchRow()){
		if ($h['host_id'] != $_GET['host_id']) {
			$buffer->startElement("template");
			$buffer->writeElement("tp_id", $h['host_id']);
			$buffer->writeElement("tp_alias", $h['host_name']);
			$buffer->endElement();				
		}
	}
	$DBRESULT->free();
	$buffer->endElement();
	$buffer->output();
?>