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
 
  	$etc = "@CENTREON_ETC@";
  	$etc = "/etc/centreon/";
  
	if (!file_exists("$etc/centreon.conf.php") && is_dir('./install'))
		header("Location: ./install/setup.php");
	else if (file_exists("$etc/centreon.conf.php") && is_dir('install'))
		header("Location: ./install/upgrade.php");
	else {
		if (file_exists("$etc/centreon.conf.php")){
			require_once ("$etc/centreon.conf.php");
			$freeze = 0;
		} else {
			$freeze = 1;
			require_once ("../centreon.conf.php");
			$msg = _("You have to move centreon configuration file from temporary directory to final directory");
		}
	}

	require_once ("$classdir/Session.class.php");
	require_once ("$classdir/Oreon.class.php");
	require_once ("$classdir/centreonAuth.class.php");
	require_once ("$classdir/centreonLog.class.php");
	require_once ("$classdir/centreonDB.class.php");
	
	/*
	 * Get auth type
	 */
	global $pearDB;
	$pearDB = new CentreonDB();
	$DBRESULT =& $pearDB->query("SELECT * FROM `options`");
    while ($generalOption =& $DBRESULT->fetchRow())
    	$generalOptions[$generalOption["key"]] = $generalOption["value"];
	$DBRESULT->free();

	/*
	 * Set Skin For CSS properties
	 */
	$skin = "./Themes/".$generalOptions["template"]."/";

	/*
	 * detect installation dir
	 */
	$file_install_acces = 0;
	if (file_exists("./install/setup.php")){
		$error_msg = "Installation Directory '". getcwd() ."/install/' is accessible. Delete this directory to prevent security problem.";
		$file_install_acces = 1;
	}

	/*
	 * Set PHP Session Expiration time
	 */
	ini_set("session.gc_maxlifetime", "31536000");
	
	if (!isset($cas) || !isset($cas["auth_cas_enable"])){	
		Session::start();
	}
	
	if (isset($_GET["disconnect"])) {
		
		if (isset($cas) && isset($cas["auth_cas_enable"]) && $cas["auth_cas_enable"]){
			include_once('/var/www/CAS/CAS.php');
			phpCAS::client(CAS_VERSION_2_0, $cas["cas_server"], 443, $cas["cas_url"]);
			phpCAS::logout();
		}
		$oreon = & $_SESSION["oreon"];
		
		/*
		 * Init log class
		 */
		if (is_object($oreon)) {
			$CentreonLog = new CentreonUserLog($oreon->user->get_id(), $pearDB);
			$CentreonLog->insertLog(1, "Contact '".$oreon->user->get_alias()."' logout");
		
			$pearDB->query("DELETE FROM session WHERE session_id = '".session_id()."'");
		
			Session::stop();
			Session::start();
		}
	}
	/*
	 * already connected
	 */
	if (isset($_SESSION["oreon"])) {	
		$oreon = & $_SESSION["oreon"];
		
		/*
		 * Init log class
		 */
		$CentreonLog = new CentreonUserLog($oreon->user->get_id(), $pearDB);
		$CentreonLog->insertLog(1, "Contact '".$oreon->user->get_alias()."' logout");

		$pearDB->query("DELETE FROM session WHERE session_id = '".session_id()."'");
		Session::stop();
		Session::start();
	}
	
	if (isset($_POST["submit"]) || (isset($_GET["autologin"]) && isset($_GET["p"]) && $_GET["autologin"])) {
		/*
		 * Init log class
		 */
		$CentreonLog = new CentreonUserLog(-1, $pearDB);
		
		/*
		 * Get Connexion parameters
		 */
		isset($_GET["autologin"]) ? $autologin = $_GET["autologin"] : $autologin = 0; 
		
		isset($_GET["useralias"]) ? $useraliasG = $_GET["useralias"] : $useraliasG = NULL;
		isset($_POST["useralias"]) ? $useraliasP = $_POST["useralias"] : $useraliasP = NULL;
		$useraliasG ? $useralias = $useraliasG : $useralias = $useraliasP;
		
		isset($_GET["password"]) ? $passwordG = $_GET["password"] : $passwordG = NULL;
		isset($_POST["password"]) ? $passwordP = $_POST["password"] : $passwordP = NULL;
		$passwordG ? $password = $passwordG : $password = $passwordP;
	    
	    $centreonAuth = new CentreonAuth($useralias, $password, $autologin, $pearDB, $CentreonLog);
	     
	    if ($centreonAuth->passwdOk == 1) {
	    	$user =& new User($centreonAuth->userInfos, $generalOptions["nagios_version"]);
	    	$oreon = new Oreon($user);
	    	$_SESSION["oreon"] =& $oreon;
		    $pearDB->query("INSERT INTO `session` (`session_id` , `user_id` , `current_page` , `last_reload`, `ip_address`) VALUES ('".session_id()."', '".$oreon->user->user_id."', '1', '".time()."', '".$_SERVER["REMOTE_ADDR"]."')");
			if (!isset($_POST["submit"]))	{
				$args = NULL;
				foreach ($_GET as $key=>$value)
					$args ? $args .= "&".$key."=".$value : $args = $key."=".$value;
				header("Location: ./main.php?".$args."");
			} else {
				header("Location: ./main.php");
			}
			$connect = true;
	    } else
	    	$connect = false;
	}
	
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Centreon - IT & Network Monitoring</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="Generator" content="Centreon - Copyright (C) 2005 - 2009 Open Source Matters. All rights reserved." />
<meta name="robots" content="index, nofollow" />
<link href="<?php echo $skin; ?>login.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" href="./img/favicon.ico">
</head>
<body OnLoad="document.login.useralias.focus();">
<?php 

	/*
	 * Check PHP version 
	 * 
	 *  Centreon 2.x doesn't support PHP < 5
	 * 
	 */
	if (version_compare(phpversion(), '5.0') < 0){
 		echo "<div class='msg'> PHP version is < 5.0. Please Upgrade PHP</div>";		
 	} else {
		include_once("./login.php"); 
	}
?>
</body>
</html>