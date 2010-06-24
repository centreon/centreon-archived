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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/monitoring/status/Hosts/xml/hostXML.php $
 * SVN : $Id: hostXML.php 9401 2009-11-20 09:18:02Z shotamchay $
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
	$DBRESULT =& $obj->DBNdo->query($rq_pagination);
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
	$DBRESULT =& $obj->DBNdo->query($rq1);
	while ($ndo =& $DBRESULT->fetchRow()) {
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