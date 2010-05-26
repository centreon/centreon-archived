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

	if (!isset($oreon))
		exit();

	require_once './class/centreonDuration.class.php';
	include_once("./include/monitoring/common-Func.php");
	include_once("./include/monitoring/external_cmd/cmd.php");

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$path = "./include/monitoring/status/Hosts/";
	$path_hg = "./include/monitoring/status/HostGroups/";
	
	$pathRoot = "./include/monitoring/";
	$pathDetails = "./include/monitoring/objectDetails/";
	$pathTools = "./include/tools/";

	if (!isset($_GET["cmd"]) && isset($_POST["cmd"])) {
		$param = $_POST;
	} else {
		$param = $_GET;
	}

	if (isset($param["cmd"]) && $param["cmd"] == 14 && isset($param["author"]) && isset($param["en"]) && $param["en"] == 1){
		if (!isset($param["notify"]))
			$param["notify"] = 0;
		if (!isset($param["persistent"]))
			$param["persistent"] = 0;
		acknowledgeHost($param);
	} else if (isset($param["cmd"]) && $param["cmd"] == 14 && isset($param["author"]) && isset($param["en"]) && $param["en"] == 0){
		acknowledgeHostDisable();
	}

	if (isset($param["cmd"]) && $param["cmd"] == 16 && isset($param["output"])) {
		submitHostPassiveCheck();
	}
	
	if ($min){
		switch ($o)	{
			default : require_once($pathTools."tools.php"); break;
		}
	} else {		
		include_once("./class/centreonDB.class.php");
		
		$pearDBndo = new CentreonDB("ndo");
		
		if (preg_match("/error/", $pearDBndo->toString(), $str) || preg_match("/failed/", $pearDBndo->toString(), $str)) {
			print "<div class='msg'>"._("Connection Error to NDO DataBase ! \n")."</div>";
		} else {
	
			if (preg_match("/connect\ failed/", $pearDBndo->toString(), $str)) 
				print "<div class='msg'>"._("Connection Error to NDO DataBase ! \n")."</div>";			
			else {
				/*
				 * Check table ACL exists
				 */
				if ($err_msg = table_not_exists("centreon_acl")) 
					print "<div class='msg'>"._("Warning: ").$err_msg."</div>";
				
				switch ($o)	{
					case "h" 	: 
						require_once($path."host.php");
						break;
					case "hpb" 	: 
						require_once($path."host.php");
						break;
					case "h_unhandled" 	: 
						require_once($path."host.php");
						break;
					case "hd" 	: 
						require_once($pathDetails."hostDetails.php");
						break;
					case "hpc" 		: 
						require_once("./include/monitoring/submitPassivResults/hostPassiveCheck.php");
						break;
					case "hak" 	: 
						require_once($pathRoot."acknowlegement/hostAcknowledge.php");
						break;
					default 	: 
						require_once($path."host.php");
						break;
				}
			}
		}
	}
?>