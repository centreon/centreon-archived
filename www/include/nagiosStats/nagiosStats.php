<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS. OREON makes no representation
and gives no warranty whatsoever, whether express or implied, and without limitation, 
with regard to the quality, safety, contents, performance, merchantability, non-infringement
or suitability for any particular or intended purpose of the Software found on the OREON web
site. In no event will OREON be liable for any direct, indirect, punitive, special, incidental
or consequential damages however they may arise and even if OREON has been previously advised 
of the possibility of such damages.

For information : contact@oreon-project.org
*/

	if (!isset($oreon))
		exit(); 
	
	include_once("./include/monitoring/common-Func.php");
	include_once("./include/monitoring/status/resume.php"); 

	unset($tpl);
	unset($path);					

	# Get Poller List
	$tab_nagios_server = array();
	$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` ORDER BY `name`");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while ($nagios =& $DBRESULT->fetchRow())
		$tab_nagios_server[$nagios['id']] = $nagios['name'];
	
	$host_list = array();
	$tab_server = array();
	$cpt = 0;
	foreach ($tab_nagios_server as $key => $value){
		$host_list[$key] = $value;
		$tab_server[$cpt] = $value;
		$cpt++;
	}
	
	$path = "./include/nagiosStats/";
		
	/*
	 * Smarty template Init
	 */
	 
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./");	
	
	if (isset($host_list) && $host_list)
		$tpl->assign('host_list', $host_list);
		
	if (isset($tab_server) && $tab_server)
		$tpl->assign('tab_server', $tab_server);	
		
	$tpl->assign("session", session_id());
	$tpl->display("nagiosStats.ihtml");
	
?>