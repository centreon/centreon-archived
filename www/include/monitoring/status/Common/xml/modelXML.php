<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	include_once "@CENTREON_ETC@/centreon.conf.php";	
	
	include_once $centreon_path . "www/class/centreonXMLBGRequest.class.php";
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
	
	/*
	 *  Check Arguments from GET
	 */
	$o 			= $obj->checkArgument("o", $_GET, "h");
	$p			= $obj->checkArgument("p", $_GET, "2");
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
	$obj->setHostGroupsHistory($hostgroups);
	
	/* 
	 * Get Host status 
	 */
	$rq1 = "";
	
	/*
	 * Set pagination
	 */
	$rq_pagination = $rq1;
	
	/* 
	 * Get Pagination Rows 
	 */
	$DBRESULT = $obj->DBNdo->query($rq_pagination);
	$numRows = $DBRESULT->numRows();
	$DBRESULT->free();
	
	$rq1 .= " LIMIT ".($num * $limit).",".$limit;

	$obj->XML->startElement("reponse");
	$obj->XML->startElement("i");
	$obj->XML->writeElement("numrows", $numRows);
	$obj->XML->writeElement("num", $num);
	$obj->XML->writeElement("limit", $limit);
	$obj->XML->writeElement("p", $p);
	$obj->XML->writeElement("o", $o);
	$obj->XML->writeElement("hard_state_label", _("Hard State Duration"));
	$obj->XML->endElement();	
	
	$ct = 0;
	$flag = 0;
	$DBRESULT = $obj->DBNdo->query($rq1);
	while ($ndo = $DBRESULT->fetchRow()) {
		$obj->XML->startElement("l");
		$obj->XML->writeAttribute("class", $obj->getNextLineClass());
		/*
		 * All XML data here
		 */
		$obj->XML->endElement();		
	}
	$DBRESULT->free();

	if (!$ct)
		$obj->XML->writeElement("infos", "none");
	$obj->XML->endElement();
	
	$obj->header();
	$obj->XML->output();
?>