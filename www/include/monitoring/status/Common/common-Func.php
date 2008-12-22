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
 * SVN : $URL
 * SVN : $Id: common-Func.php 7199 2008-12-03 09:22:07Z jmathis $
 * 
 */
 
	ini_set("Display_error", "On");

	function get_Host_Status($host_name, $pearDBndo, $general_opt){
		global $ndo_base_prefix;
		$rq = "SELECT nhs.current_state FROM `" .$ndo_base_prefix."hoststatus` nhs, `" .$ndo_base_prefix."objects` no " .
			  "WHERE no.object_id = nhs.host_object_id" ;
		$DBRESULT =& $pearDBndo->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$status =& $DBRESULT->fetchRow();
		unset($DBRESULT);
		return $status["current_state"];
	}
	
	function getMyIndexGraph4Service($host_name = NULL, $service_description = NULL, $pearDBO)	{
		if ((!isset($service_description) || !$service_description ) || (!isset($host_name) || !$host_name)) 
			return NULL;
		$DBRESULT =& $pearDBO->query("SELECT id FROM index_data i, metrics m WHERE i.host_name = '".$host_name."' " .
									"AND m.hidden = '0' " .									
									"AND i.service_description = '".$service_description."' " .
									"AND i.id = m.index_id");									
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["id"];
		} else {
			return 0;
		}
		return NULL;
	}
	
	function get_services_status($host_name, $status){
		global $pearDBndo, $ndo_base_prefix, $general_opt, $o, $is_admin, $groupnumber;

		$rq = 	" SELECT count( nss.service_object_id ) AS nb".
				" FROM " .$ndo_base_prefix."servicestatus nss".
				" WHERE nss.current_state = '".$status."'";

		if ($o == "svcSum_ack_0")
			$rq .= " AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0";

		if ($o == "svcSum_ack_1")
			$rq .= " AND nss.problem_has_been_acknowledged = 1 AND nss.current_state != 0";

		$rq .= 	" AND nss.service_object_id".
				" IN (".
				" SELECT nno.object_id".
				" FROM " .$ndo_base_prefix."objects nno";
		if (!$is_admin && $groupnumber)
			$rq	.=	", centreon_acl";
		
		$rq	.=	" WHERE nno.objecttype_id = 2 ";
		
		if (!$is_admin && $groupnumber)
			$rq .= 	" AND nno.name1 = centreon_acl.host_name AND nno.name2 = centreon_acl.service_description AND centreon_acl.group_id IN (5)";

		$rq .=	" AND nno.name1 = '".$host_name."')";		
		
		$DBRESULT =& $pearDBndo->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$tab =& $DBRESULT->fetchRow();
		return ($tab["nb"]);
	}
?>