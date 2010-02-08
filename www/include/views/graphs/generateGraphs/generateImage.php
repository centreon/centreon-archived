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

	/*
	 * Include config file
	 */
	include "@CENTREON_ETC@/centreon.conf.php";
		
	require_once "./DB-Func.php";
	require_once "$centreon_path/www/class/centreonGraph.class.php";

	/*
	 * Create XML Request Objects
	 */
	$obj = new CentreonGraph($_GET["session_id"], $_GET["index"], 0, 1);
	
	if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
		;
	} else {
		$obj->displayError();
	}
	
	require_once $centreon_path."www/include/common/common-Func.php";

	/*
	 * Set General Options
	 */
	$obj->setGeneralOption();
	
	/*
	 * Set arguments from GET
	 */	
	$obj->start 	= $obj->checkArgument("start", $_GET, time() - (60*60*48));
	$obj->end 		= $obj->checkArgument("end", $_GET, time());
		
 	$obj->GMT->getMyGMTFromSession($obj->session_id, $pearDB);

	/*
	 * Check Graphs size
	 */
	if (isset($graph_width) && $graph_width != "") 
		$obj->setWidth($graph_width);
	if (isset($graph_height) && $graph_height != "")
		$obj->setHeight($graph_height);	

	/*
	 * Template Management
	 */
	$obj->setTemplate($_GET["template_id"]);
	
	/*
	 * Get Activate Metrics
	 */
	$obj->setActivateMetrics();
	
	/*
	 * Begin command line build
	 */
	$obj->initCommandLine();
	$obj->addCommandLineTimeLimit($_GET["flagperiod"]);
	
	/*
	 * Init Curve list
	 */
	$obj->setMetricList($_GET["metric"]);
	$obj->initCurveList();	
	$obj->addCurveInCommandLine();
	
	/*
	 * Comment time
	 */
	$obj->addCommentTime();
	
	/*
	 * Create Legende
	 */
	$obj->createLegend();

	/*
	 * Close command line
	 */
	$obj->endCommandLine();
	/*
	 * Add Timezone for current user.
	 */
	$obj->setTimezone();
	//print $obj->commandLine;
	
	/*
	 * Display Images Binary Data
	 */
	$obj->displayImageFlow();
?>