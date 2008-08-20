<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
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
 
	if (!isset($oreon))
		exit(); 
	
	include_once "./include/monitoring/common-Func.php";
	include_once "./DBNDOConnect.php";
	
	unset($tpl);
	unset($path);					

	$path = "./include/home/";
	
	if ($err_msg = table_not_exists("centreon_acl")) 
		print "<div class='msg'>"._("Warning: ").$err_msg."</div>";
		
	/*
	 * Smarty template Init
	 */
	 
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./");			
	$tpl->assign("session", session_id());
	$tpl->display("home.ihtml");
	
?>

