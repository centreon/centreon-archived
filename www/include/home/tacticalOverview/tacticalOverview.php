<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@oreon-project.org
 */
 
	if (!isset($oreon))
		exit;

	require_once './class/other.class.php';
	require_once './include/common/common-Func.php';
	require_once './include/common/common-Func-ACL.php';
	
	/*
	 * DB connexion
	 */
	 
	require_once '/etc/centreon/centreon.conf.php';
	require_once './DBconnect.php';
	require_once './DBNDOConnect.php';
	
	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	/*
	 * Ndo table prefix
	 */
	
	$ndo_base_prefix = getNDOPrefix();
	
	/*
	 * Get Color of different status
	 */
	 
	$general_opt = getStatusColor($pearDB);
	
	/*
	 * Check ACL and generate ACL restrictions
	 */
	if (!$is_admin){
		$lca = getLcaHostByName($pearDB);
		$lcaSTR = getLCAHostStr($lca["LcaHost"]);
	}
	/*
	 * Get Status Globals for hosts
	 */
	if (!$is_admin)
		$rq1 = 	" SELECT count(".$ndo_base_prefix."hoststatus.current_state), ".$ndo_base_prefix."hoststatus.current_state" .
				" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects" .
				" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id AND ".$ndo_base_prefix."objects.is_active = 1 " .
				" AND ".$ndo_base_prefix."objects.name1 IN ($lcaSTR)" .
				" GROUP BY ".$ndo_base_prefix."hoststatus.current_state " .
				" ORDER by ".$ndo_base_prefix."hoststatus.current_state";
	else
		$rq1 = 	" SELECT count(".$ndo_base_prefix."hoststatus.current_state) , ".$ndo_base_prefix."hoststatus.current_state" .
				" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects " .
				" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id AND ".$ndo_base_prefix."objects.is_active = 1 " .
				" GROUP BY ".$ndo_base_prefix."hoststatus.current_state " .
				" ORDER by ".$ndo_base_prefix."hoststatus.current_state";
				
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
	
	$hostStatus = array(0=>0, 1=>0, 2=>0, 3=>0);
	while ($ndo = $DBRESULT_NDO1->fetchRow())
		$hostStatus[$ndo["current_state"]] = $ndo["count(nagios_hoststatus.current_state)"];
		
	/*
	 * Get Host Down Ack
	 */

	/*
	 * Get Host Down Not Actif
	 */


	/*
	 * Get Host Unrea Ack
	 */

	/*
	 * Get Host Unrea Not Actif
	 */

	/*
	 * Get Host Unrea Not Unhandled
	 */
	
	/*
	 * Get Status global for Services
	 */
		
	if (!$is_admin)
		$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
				" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, centreon_acl " .
				" WHERE no.object_id = nss.service_object_id".
				" AND no.name1 not like 'OSL_Module' ".
				" AND no.name1 not like 'Meta_Module' ".
				" AND no.name1 = centreon_acl.host_name ".
				" AND no.name2 = centreon_acl.service_description " .
				" AND centreon_acl.group_id IN (".groupsListStr(getGroupListofUser($pearDB)).") ".
				" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";
	else
		$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
				" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
				" WHERE no.object_id = nss.service_object_id".
				" AND no.name1 not like 'OSL_Module' ".
				" AND no.name1 not like 'Meta_Module' ".
				" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";			
	$DBRESULT_NDO2 =& $pearDBndo->query($rq2);
	if (PEAR::isError($DBRESULT_NDO2))
		print "DB Error : ".$DBRESULT_NDO2->getDebugInfo()."<br />";

	$SvcStat = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
	while($DBRESULT_NDO2->fetchInto($ndo))
		$SvcStat[$ndo["current_state"]] = $ndo["count(nss.current_state)"];

	/*
	 * Get Services Critical Ack
	 */
	
	/*
	 * Get Services Critical Disabled
	 */

	/*
	 * Get Services Critical On host PB
	 */
	 
	/*
	 * Get Services Critical Un handled
	 */
	
	/*
	 * Get Services Warning Ack
	 */
	
	/*
	 * Get Services Warning Disabled
	 */

	/*
	 * Get Services Warning On host PB
	 */
	 
	/*
	 * Get Services Warning Un handled
	 */


	/*
	 * Get Services Unknown Ack
	 */
	
	/*
	 * Get Services Unknown Disabled
	 */

	/*
	 * Get Services Unknown On host PB
	 */
	 
	/*
	 * Get Services Unknown Un handled
	 */

			
	$path = "./include/home/tacticalOverview/";

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	$tpl->assign("color", $general_opt);
	$tpl->assign("HostStatus", $hostStatus);
	$tpl->assign("ServiceStatus", $SvcStat);
	$tpl->display("tacticalOverview.ihtml");
 ?>