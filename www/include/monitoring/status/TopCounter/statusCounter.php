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
 * SVN : $URL$
 * SVN : $Id$
 * 
 */

	$debug = 0;
	
	include_once "/etc/centreon/centreon.conf.php";	
	include_once $centreon_path . "www/class/centreonXMLBGRequest.class.php";
	
	include_once $centreon_path . "www/include/common/common-Func.php";
	
	/*
	 * Create XML Request Objects
	 */
	$obj = new CentreonXMLBGRequest($_POST["sid"], 1, 1, 0, $debug);
	
	if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
		$obj->reloadSession();
	} else {
		print "Bad Session ID";
		exit();
	}
	
	/* *********************************************
	 * Get Host stats
	 */
	$rq1 = 	" SELECT count(DISTINCT ".$obj->ndoPrefix."objects.name1), ".$obj->ndoPrefix."hoststatus.current_state" .
			" FROM ".$obj->ndoPrefix."hoststatus, ".$obj->ndoPrefix."objects";
	
	if (!$obj->is_admin)
		$rq1 .= " , centreon_acl ";
	
	$rq1 .= " WHERE ".$obj->ndoPrefix."objects.object_id = ".$obj->ndoPrefix."hoststatus.host_object_id " .
			" AND ".$obj->ndoPrefix."objects.is_active = 1 " .
			$obj->access->queryBuilder("AND", $obj->ndoPrefix."objects.name1", "centreon_acl.host_name") .				
			$obj->access->queryBuilder("AND", "centreon_acl.group_id", $grouplistStr) .
			" AND " . $obj->ndoPrefix. "objects.name1 NOT LIKE '_Module_%' " .				
			" GROUP BY ".$obj->ndoPrefix."hoststatus.current_state";
	
	
	$hostCounter = 0;
	$host_stat = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
	$DBRESULT =& $obj->DBNdo->query($rq1);
	while ($ndo =& $DBRESULT->fetchRow()) {
		$host_stat[$ndo["current_state"]] = $ndo["count(DISTINCT ".$obj->ndoPrefix."objects.name1)"];
		$hostCounter += $host_stat[$ndo["current_state"]];
	}
	$DBRESULT->free();
	unset($ndo);
	 
	/* *********************************************
	 * Get Service stats
	 */
	if (!$obj->is_admin)
		$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
				" FROM ".$obj->ndoPrefix."servicestatus nss, ".$obj->ndoPrefix."objects no, centreon_acl " .
				" WHERE no.object_id = nss.service_object_id".
				" AND no.name1 NOT LIKE '_Module_%' ".					
				" AND no.name1 = centreon_acl.host_name ".
				" AND no.name2 = centreon_acl.service_description " .
				" AND centreon_acl.group_id IN (".$grouplistStr.") ".
				" AND no.is_active = 1 GROUP BY nss.current_state";
	else
		$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
				" FROM ".$obj->ndoPrefix."servicestatus nss, ".$obj->ndoPrefix."objects no" .
				" WHERE no.object_id = nss.service_object_id".
				" AND no.name1 NOT LIKE '_Module_%' ".
				" AND no.is_active = 1 GROUP BY nss.current_state";			
	
	$serviceCounter = 0;
	$svc_stat = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
	$DBRESULT =& $obj->DBNdo->query($rq2);
	while ($ndo =& $DBRESULT->fetchRow()) {
		$svc_stat[$ndo["current_state"]] = $ndo["count(nss.current_state)"];
		$serviceCounter += $svc_stat[$ndo["current_state"]];
	}
	$DBRESULT->free();
	unset($ndo);

	/* *********************************************
	 * Create Buffer
	 */
	$obj->XML = new CentreonXML();
	$obj->XML->startElement("reponse");
	$obj->XML->startElement("infos");
	$obj->XML->writeElement("filetime", time());
	$obj->XML->endElement();
	$obj->XML->startElement("s");
	$obj->XML->writeElement("th", $hostCounter);
	$obj->XML->writeElement("ts", $serviceCounter);
	$obj->XML->writeElement("o", $svc_stat["0"]);
	$obj->XML->writeElement("w", $svc_stat["1"]);
	$obj->XML->writeElement("c", $svc_stat["2"]);
	$obj->XML->writeElement("un1", $svc_stat["3"]);
	$obj->XML->writeElement("p1", $svc_stat["4"]);
	$obj->XML->writeElement("up", $host_stat["0"]);
	$obj->XML->writeElement("d", $host_stat["1"]);
	$obj->XML->writeElement("un2", $host_stat["2"]);
	$obj->XML->writeElement("p2", $host_stat["3"]);
	$obj->XML->endElement();
	$obj->XML->endElement();
	
	/*
	 * Send headers
	 */	
	$obj->header();
	
	/*
	 * Display XML data
	 */
	$obj->XML->output();

?>