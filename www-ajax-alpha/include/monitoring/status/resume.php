<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	if (!isset($oreon))
		exit(); 
	
	$path = "./include/monitoring/status/";
	$pgr_nagios_stat = array();

	include("./include/monitoring/load_status_log.php");
	
	# Smarty template Init
	$tpl_resume = new Smarty();
	$tpl_resume = initSmartyTpl($path, $tpl_resume, "/templates/");
	
	
	$statistic_host = array("UP" => 0, "DOWN" => 0, "UNREACHABLE" => 0, "PENDING" => 0);
	$statistic_service = array("OK" => 0, "WARNING" => 0, "CRITICAL" => 0, "UNKNOWN" => 0, "PENDING" => 0);
	
	
	if (isset($host_status))
		foreach ($host_status as $hs)
			$statistic_host[$hs["status"]]++;
	if (isset($service_status))		
		foreach ($service_status as $s)
			$statistic_service[$s["status"]]++;
	
	$statistic_service_color = array();
	if (isset($statistic_service))
		foreach ($statistic_service as $key => $stts)
			$statistic_service_color[$key] = " style='background:" . $oreon->optGen["color_".strtolower($key)] . "'";

	$statistic_host_color = array();
	if (isset($statistic_host))
		foreach ($statistic_host as $key => $stth)
			$statistic_host_color[$key] = " style='background:" . $oreon->optGen["color_".strtolower($key)] . "'";


			
	$tpl_resume->assign("statistic_service", $statistic_service);
	$tpl_resume->assign("statistic_host", $statistic_host);
	$tpl_resume->assign("statistic_service_color", $statistic_service_color);
	$tpl_resume->assign("statistic_host_color", $statistic_host_color);
	$tpl_resume->assign("lang", $lang);
	$tpl_resume->assign("refresh", $oreon->optGen["oreon_refresh"]);
	$tpl_resume->assign("pgr_nagios_stat", $pgr_nagios_stat);
	$tpl_resume->display("resume.ihtml");
?>