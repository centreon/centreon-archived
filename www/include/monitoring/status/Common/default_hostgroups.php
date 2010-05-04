<?php
/*
 * Copyright 2005-2009 MERETHIS
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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/monitoring/status/Common/default_poller.php $
 * SVN : $Id: default_poller.php 10348 2010-04-19 10:08:17Z shotamchay $
 * 
 */
 
 
	$DBRESULT = $pearDB->query("SELECT cp_value FROM contact_param WHERE cp_key = 'monitoring_default_hostgroups' AND cp_contact_id = '" .$oreon->user->user_id. "'");
	$row_cp = $DBRESULT->fetchRow();
	if ($DBRESULT->numRows()) {
		$default_hg = $row_cp['cp_value'];
	}
	else {
		$pearDB->query("INSERT INTO contact_param (cp_key, cp_value, cp_contact_id) VALUES ('monitoring_default_hostgroups', '0', '".$oreon->user->user_id."')");
		$default_hg = "0";
	} 

?>