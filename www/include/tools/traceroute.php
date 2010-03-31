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
 
 	ini_set("Display_errors", "Off");

	 include("@CENTREON_ETC@/centreon.conf.php");
	 require_once ("../../$classdir/Session.class.php");
	 require_once ("../../$classdir/Oreon.class.php");

	 Session::start();

	 if (!isset($_SESSION["oreon"])) {
	 	// Quick dirty protection
	 	header("Location: ../../index.php");
		exit;
	 } else
	 	$oreon =& $_SESSION["oreon"];

	if (isset($_GET["host"]))
		$host = htmlentities($_GET["host"], ENT_QUOTES);
	else if (isset($_POST["host"]))
		$host = htmlentities($_POST["host"], ENT_QUOTES);
	else
		exit;

	include("Net/Traceroute.php");

	$tr = Net_Traceroute::factory();

	$msg = "";
	if (!PEAR::isError($tr))	{
		$tr->setArgs(array('timeout' => 5));

		 # patch for user that have PEAR Traceroute 0.21.1, remote exec possible Julien Cayssol
	    $response = $tr->traceroute(escapeshellcmd($host));
		foreach ($response->getRawData() as $key => $data)
   			$msg .= $data ."<br />";
		print $msg;
	}

?>
