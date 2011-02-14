<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 
	if (!isset ($oreon))
		exit ();
	
	if (isset($_POST["o"]) && $_POST["o"])
		$o = $_POST["o"];
	
	isset($_GET["meta_id"]) ? $cG = $_GET["meta_id"] : $cG = NULL;
	isset($_POST["meta_id"]) ? $cP = $_POST["meta_id"] : $cP = NULL;
	$cG ? $meta_id = $cG : $meta_id = $cP;
	
	isset($_GET["host_name"]) ? $cG = $_GET["host_name"] : $cG = NULL;
	isset($_POST["host_name"]) ? $cP = $_POST["host_name"] : $cP = NULL;
	$cG ? $host_name = $cG : $host_name = $cP;
	
	isset($_GET["host_id"]) ? $cG = $_GET["host_id"] : $cG = NULL;
	isset($_POST["host_id"]) ? $cP = $_POST["host_id"] : $cP = NULL;
	$cG ? $host_id = $cG : $host_id = $cP;

	isset($_GET["metric_id"]) ? $cG = $_GET["metric_id"] : $cG = NULL;
	isset($_POST["metric_id"]) ? $cP = $_POST["metric_id"] : $cP = NULL;
	$cG ? $metric_id = $cG : $metric_id = $cP;

	isset($_GET["msr_id"]) ? $cG = $_GET["msr_id"] : $cG = NULL;
	isset($_POST["msr_id"]) ? $cP = $_POST["msr_id"] : $cP = NULL;
	$cG ? $msr_id = $cG : $msr_id = $cP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;
			
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the configuration dir	
	$path = "./include/configuration/configObject/meta_service/";
	
	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page'])
		$p = $ret['topology_page'];
	
	switch ($o)	{
		case "a" : require_once($path."formMetaService.php"); break; #Add an Meta Service
		case "w" : require_once($path."formMetaService.php"); break; #Watch an Meta Service
		case "c" : require_once($path."formMetaService.php"); break; #Modify an Meta Service		
		case "s" : enableMetaServiceInDB($meta_id); require_once($path."listMetaService.php"); break; #Activate a Meta Service
		case "u" : disableMetaServiceInDB($meta_id); require_once($path."listMetaService.php"); break; #Desactivate a Meta Service
		case "d" : deleteMetaServiceInDB(isset($select) ? $select : array()); require_once($path."listMetaService.php"); break; #Delete n Meta Servive		
		case "m" : multipleMetaServiceInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listMetaService.php"); break; #Duplicate n Meta Service
		case "ci" : require_once($path."listMetric.php"); break; #Manage Service of the MS
		case "as" : require_once($path."metric.php"); break; # Add Service to a MS
		case "cs" : require_once($path."metric.php"); break; # Change Service to a MS
		case "ss" : enableMetricInDB($msr_id); require_once($path."listMetric.php"); break; #Activate a Metric
		case "us" : disableMetricInDB($msr_id); require_once($path."listMetric.php"); break; #Desactivate a Metric
		case "ws" : require_once($path."metric.php"); break; # View Service to a MS
		case "ds" : deleteMetricInDB(isset($select) ? $select : array()); require_once($path."listMetric.php"); break; #Delete n Metric		
		default : require_once($path."listMetaService.php"); break;
	}
?>