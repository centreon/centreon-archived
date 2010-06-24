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
	include_once "/etc/centreon/centreon.conf.php";
	
	include_once $centreon_path . "www/class/centreonXMLBGRequest.class.php";
	include_once $centreon_path . "www/include/monitoring/status/Common/common-Func.php";	
	include_once $centreon_path . "www/include/common/common-Func.php";

	/*
	 * Create XML Request Objects
	 */
	$obj = new CentreonXMLBGRequest($_GET["sid"], 1, 1, 0, 1);
	
	if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
		;
	} else {
		print "Bad Session ID";
		exit();
	}
	
	/*
	 * Set Default Poller
	 */
	$obj->getDefaultFilters();
		
	/* **************************************************
	 * Check Arguments From GET tab
	 */
	$o 			= $obj->checkArgument("o", $_GET, "h");
	$p 			= $obj->checkArgument("p", $_GET, "2");
	$nc 		= $obj->checkArgument("nc", $_GET, "0");
	$num 		= $obj->checkArgument("num", $_GET, 0);
	$limit 		= $obj->checkArgument("limit", $_GET, 20);
	$instance 	= $obj->checkArgument("instance", $_GET, $obj->defaultPoller);
	$hostgroups = $obj->checkArgument("hostgroups", $_GET, $obj->defaultHostgroups);
	$search 	= $obj->checkArgument("search", $_GET, "");
	$sort_type 	= $obj->checkArgument("sort_type", $_GET, "host_name");
	$order 		= $obj->checkArgument("order", $_GET, "ASC");
	$dateFormat = $obj->checkArgument("date_time_format_status", $_GET, "d/m/Y H:i:s");
	
	/*
	 * Backup poller selection
	 */
	$obj->setInstanceHistory($instance);
	
	$service = array();
	$host_status = array();
	$service_status = array();
	$host_services = array();
	$metaService_status = array();
	$tab_host_service = array();

	/* 
	 * Get Host status 
	 */	 
	$rq1 =	  	" SELECT DISTINCT no.name1 as host_name, nhs.current_state" .
				" FROM " .$obj->ndoPrefix."objects no, " .$obj->ndoPrefix."hoststatus nhs ";
	
	if ($hostgroups) {
		$rq1 .= ", ".$obj->ndoPrefix."hostgroup_members hgm ";
	}	
	
	if (!$obj->is_admin)
		$rq1 	.= ", centreon_acl ";
	
	$rq1 .=		" WHERE no.objecttype_id = 1 AND nhs.host_object_id = no.object_id ".
				" AND no.name1 NOT LIKE '_Module_%'";				
	
	$grouplistStr = $obj->access->getAccessGroupsString();
	$rq1 .= $obj->access->queryBuilder("AND", "no.name1", "centreon_acl.host_name").$obj->access->queryBuilder("AND", "group_id", $grouplistStr);

	if ($o == "svcgrid_pb" || $o == "svcOV_pb" || $o == "svcgrid_ack_0" || $o == "svcOV_ack_0") {
		$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.current_state != 0)";
	}
	if ($o == "svcgrid_ack_1" || $o == "svcOV_ack_1") {
		$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$obj->ndoPrefix."objects nno," .$obj->ndoPrefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1)";
	}
	if ($search != "") {
		$rq1 .= " AND no.name1 like '%" . $search . "%' ";
	}
	if ($instance) {
		$rq1 .= " AND no.instance_id = ".$instance;
	}
	if ($hostgroups) {
	    $rq1 .= " AND nhs.host_object_id = hgm.host_object_id ";
	    $rq1 .= " AND hgm.hostgroup_id IN (SELECT hostgroup_id FROM ".$obj->ndoPrefix."hostgroups WHERE alias LIKE '".$hostgroups."') ";
	}
	
	switch ($sort_type) {
		case 'current_state' : $rq1 .= " order by nhs.current_state ". $order.",no.name1 "; break;
		default : $rq1 .= " order by no.name1 ". $order; break;
	}

	$rq_pagination = $rq1;
	
	/*
	 * Add limit
	 */
	$rq1 .= " LIMIT ".($num * $limit).",".$limit;

	/*
	 * Get Pagination Rows
	 */
	$DBRESULT_PAGINATION =& $obj->DBNdo->query($rq_pagination);
	$numRows = $DBRESULT_PAGINATION->numRows();

	$obj->XML->startElement("reponse");
	$obj->XML->startElement("i");
	$obj->XML->writeElement("numrows", $numRows);
	$obj->XML->writeElement("num", $num);
	$obj->XML->writeElement("limit", $limit);
	$obj->XML->writeElement("p", $p);	

	preg_match("/svcOV/",$_GET["o"], $matches) ? $obj->XML->writeElement("s", "1") : $obj->XML->writeElement("s", "0");
	$obj->XML->endElement();

	$tab_final = array();
	$str = "";
	
	/*
	 * Execute request
	 */
	$DBRESULT =& $obj->DBNdo->query($rq1);
	while ($ndo =& $DBRESULT->fetchRow()) {
		if ($str != "") {
			$str .= ",";
		}
		$str .= "'".$ndo["host_name"]."'";
		$tab_final[$ndo["host_name"]] = array("cs" => $ndo["current_state"]);
	}
	$DBRESULT->free();
	
	/*
	 * Get Service status
	 */
	$tab_svc = $obj->monObj->getServiceStatus($str, $obj, $o, $instance, $hostgroups);
	foreach ($tab_svc as $host_name => $tab) {
		if (count($tab)) {
			$tab_final[$host_name]["tab_svc"] = $tab;
		}
	} 
	
	$ct = 0;
	foreach ($tab_final as $host_name => $tab){
		$obj->XML->startElement("l");
		$obj->XML->writeAttribute("class", $obj->getNextLineClass());
		foreach ($tab["tab_svc"] as $svc => $state) {
			$obj->XML->startElement("svc");
			$obj->XML->writeElement("sn", $svc);
			$obj->XML->writeElement("snl", urlencode($svc));
			$obj->XML->writeElement("sc", $obj->colorService[$state]);			
			$obj->XML->endElement();			
		}
		$obj->XML->writeElement("o", $ct++);
		$obj->XML->writeElement("hn", $host_name);
		$obj->XML->writeElement("hnl", urlencode($host_name));
		$obj->XML->writeElement("hs", $obj->statusHost[$tab["cs"]]);
		$obj->XML->writeElement("hc", $obj->colorHost[$tab["cs"]]);
		$obj->XML->endElement();		
	}

	if (!$ct) {
		$obj->XML->writeElement("infos", "none");	
	}
	$obj->XML->endElement();
	
	/*
	 * Send Header
	 */
	$obj->header();

	/*
	 * Send XML
	 */	
	$obj->XML->output();
?>