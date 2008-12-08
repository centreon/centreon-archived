<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
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
		
		$rq	.=	" WHERE nno.objecttype_id = 2 ".
				" AND nno.name1 not like 'qos_Module'" .
				" AND nno.name1 not like 'Meta_Module'";
		
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