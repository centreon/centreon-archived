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

/*
 * This file drawing the tactical overview on Home pages. 
 *
 * PHP version 5
 *
 * @package tacticalOverview.php
 * @author Julien Mathis jmathis@merethis.com
 * @author Damien Duponchelle dduponchelle@merethis.com
 * @version $Id: $
 * @copyright (c) 2007-2008 Centreon
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
 
	// Variables $oreon must exist. it contains all personnals datas (Id, Name etc.) using by user to navigate on the interface.
	if (!isset($oreon)) {
		exit();
	}

	// Including files and dependences 
	require_once './class/other.class.php';
	require_once './include/common/common-Func.php';
	require_once './include/common/common-Func-ACL.php';
	require_once './DBconnect.php';
	require_once './DBNDOConnect.php';
	
	// Testing the NDO database connexion. If "error" or "failed" is matching in the output message, the script print a error message and exit
	if (preg_match("/error/", $pearDBndo->toString(), $str) || preg_match("/failed/", $pearDBndo->toString(), $str)) {
		print "<div class='msg'>"._("Connection Error to NDO DataBase ! \n")."</div>";
	} else {
		
			// The user must install the ndo table with the 'centreon_acl'
			if ($err_msg = table_not_exists("centreon_acl")) { 
				print "<div class='msg'>"._("Warning: ").$err_msg."</div>";
			}

			// Check ACL and generate ACL restrictions
			if (!$is_admin)	{
				$lca = getLcaHostByName($pearDB);
				$lcaSTR = getLCAHostStr($lca["LcaHost"]);
		    	$grouplist = getGroupListofUser($pearDB);
		    	$grouplistStr = groupsListStr($grouplist);
		    }

			// Including Pear files
			require_once 'HTML/QuickForm.php';
			require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
			
			// Declaring variables
			$ndo_base_prefix = getNDOPrefix(); // Getting ndo database prefix
			$general_opt = getStatusColor($pearDB); // Getting colors of each status like : "[color_ok] => #13EB3A [color_warning] => #F8C706 ..." 

		    // Getting Group list
			$grouplist = getGroupListofUser($pearDB); // Getting group of user
			$groupnumber = count($grouplist); // Getting group id of the previous group

			// Get Status Globals for hosts
			if (!$is_admin) {
				$rq1 = 	" SELECT count(".$ndo_base_prefix."hoststatus.current_state), ".$ndo_base_prefix."hoststatus.current_state" .
						" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects" .
						" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id".
						" AND ".$ndo_base_prefix."objects.is_active = 1 " .
						" AND ".$ndo_base_prefix."objects.name1 IN ($lcaSTR)" .
						" AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'Meta_Module' AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'qos_Module' " .
						" GROUP BY ".$ndo_base_prefix."hoststatus.current_state " .
						" ORDER by ".$ndo_base_prefix."hoststatus.current_state";
			} else {
				$rq1 = 	" SELECT count(".$ndo_base_prefix."hoststatus.current_state) , ".$ndo_base_prefix."hoststatus.current_state" .
						" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects " .
						" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id".
						" AND ".$ndo_base_prefix."objects.is_active = 1 " .
						" AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'Meta_Module' AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'qos_Module' " .
						" GROUP BY ".$ndo_base_prefix."hoststatus.current_state " .
						" ORDER by ".$ndo_base_prefix."hoststatus.current_state";
			}
						
			$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
			if (PEAR::isError($DBRESULT_NDO1))
				print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
			
			$hostStatus = array(0=>0, 1=>0, 2=>0, 3=>0);
			while ($ndo =& $DBRESULT_NDO1->fetchRow()) {
				$hostStatus[$ndo["current_state"]] = $ndo["count(".$ndo_base_prefix."hoststatus.current_state)"];
			}
			
			$hostUnhand = array(0=>$hostStatus[0], 1=>$hostStatus[1], 2=>$hostStatus[2]);
			
			/*
			 * Get the id's of problem hosts
			*/
			if (!$is_admin) {
				$rq1 = 	" SELECT ".$ndo_base_prefix."hoststatus.host_object_id, " .$ndo_base_prefix. "hoststatus.current_state ".
						" FROM ".$ndo_base_prefix."servicestatus, ".$ndo_base_prefix."hoststatus, " . $ndo_base_prefix."services, " . $ndo_base_prefix. "objects" .
						" WHERE ".$ndo_base_prefix."servicestatus.service_object_id = ".$ndo_base_prefix."services.service_object_id" . 
						" AND ".$ndo_base_prefix."services.host_object_id = " . $ndo_base_prefix . "hoststatus.host_object_id" .
						" AND ".$ndo_base_prefix."hoststatus.host_object_id = " . $ndo_base_prefix . "objects.object_id" .
						" AND ".$ndo_base_prefix."objects.is_active = 1 " .
						" AND ".$ndo_base_prefix."objects.name1 IN ($lcaSTR) ".
						" AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'Meta_Module' AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'qos_Module' " .
						" GROUP BY ".$ndo_base_prefix."services.host_object_id";
			} else {
				$rq1 = 	" SELECT ".$ndo_base_prefix."services.host_object_id, " .$ndo_base_prefix. "hoststatus.current_state" . 
						" FROM ".$ndo_base_prefix."servicestatus, ".$ndo_base_prefix."hoststatus, " . $ndo_base_prefix."services, " . $ndo_base_prefix. "objects" .
						" WHERE ".$ndo_base_prefix."servicestatus.service_object_id = ".$ndo_base_prefix."services.service_object_id" . 
						" AND ".$ndo_base_prefix."services.host_object_id = " . $ndo_base_prefix . "hoststatus.host_object_id" .
						" AND ".$ndo_base_prefix."hoststatus.host_object_id = " . $ndo_base_prefix . "objects.object_id" .
						" AND ".$ndo_base_prefix."objects.is_active = 1 " .
						" AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'Meta_Module' AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'qos_Module' " .
						" GROUP BY ".$ndo_base_prefix."services.host_object_id";
			}
			$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
			if (PEAR::isError($DBRESULT_NDO1))
				print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
			
			$pbCount = 0;
			while ($ndo =& $DBRESULT_NDO1->fetchRow())
				if ($ndo["current_state"] != 0){
					$hostPb[$pbCount] = $ndo["host_object_id"];			
					$pbCount++;
				}
			
			/*
			 * Get Host Ack  UP(0), DOWN(1),  UNREACHABLE(2)
			 */
			if (!$is_admin)
				$rq1 = 	" SELECT count(DISTINCT ".$ndo_base_prefix."objects.name1), ".$ndo_base_prefix."hoststatus.current_state" .
						" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects, centreon_acl " .
						" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id " .
								" AND ".$ndo_base_prefix."objects.is_active = 1 " .
								" AND ".$ndo_base_prefix."hoststatus.problem_has_been_acknowledged = 1 " .
								" AND ".$ndo_base_prefix."objects.name1 = centreon_acl.host_name " .
								" AND centreon_acl.group_id IN (".$grouplistStr.")".
						" GROUP BY ".$ndo_base_prefix."hoststatus.current_state " .
						" ORDER by ".$ndo_base_prefix."hoststatus.current_state";
			else
				$rq1 = 	" SELECT count(DISTINCT ".$ndo_base_prefix."objects.name1), ".$ndo_base_prefix."hoststatus.current_state" .
						" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects " .
						" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id" .
								" AND ".$ndo_base_prefix."objects.is_active = 1 " .
								" AND ".$ndo_base_prefix."hoststatus.problem_has_been_acknowledged = 1 " .
						" GROUP BY ".$ndo_base_prefix."hoststatus.current_state " .
						" ORDER by ".$ndo_base_prefix."hoststatus.current_state";						
			
			$hostAck = array(0=>0, 1=>0, 2=>0);
			$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
			if (PEAR::isError($DBRESULT_NDO1))
				print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
			while ($ndo =& $DBRESULT_NDO1->fetchRow())
				$hostAck[$ndo["current_state"]] = $ndo["count(DISTINCT ".$ndo_base_prefix."objects.name1)"];

			/*
			 * Get Host inactive objects
			 */
			if (!$is_admin)
				$rq1 = 	" SELECT count(".$ndo_base_prefix."hoststatus.current_state), ".$ndo_base_prefix."hoststatus.current_state" .
						" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects" .
						" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id AND ".$ndo_base_prefix."objects.is_active = 0 " .
						" AND ".$ndo_base_prefix."objects.name1 IN ($lcaSTR)" .
						" AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'Meta_Module' AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'qos_Module' " .
						" GROUP BY ".$ndo_base_prefix."hoststatus.current_state " .
						" ORDER by ".$ndo_base_prefix."hoststatus.current_state";
			else
				$rq1 = 	" SELECT count(".$ndo_base_prefix."hoststatus.current_state) , ".$ndo_base_prefix."hoststatus.current_state" .
						" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects " .
						" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id AND ".$ndo_base_prefix."objects.is_active = 0 " .
						" AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'Meta_Module' AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'qos_Module' " .
						" GROUP BY ".$ndo_base_prefix."hoststatus.current_state " .
						" ORDER by ".$ndo_base_prefix."hoststatus.current_state";
						
			$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
			if (PEAR::isError($DBRESULT_NDO1))
				print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
			
			$hostInactive = array(0=>0, 1=>0, 2=>0, 3=>0);
			while ($ndo =& $DBRESULT_NDO1->fetchRow())	{
				$hostInactive[$ndo["current_state"]] = $ndo["count(".$ndo_base_prefix."hoststatus.current_state)"];
				$hostUnhand[$ndo["current_state"]] -= $hostInactive[$ndo["current_state"]];
			}
			 
			 
			/*
			 * Get Host Unrea Not Unhandled
			 */
			
			/*
			 * Get Status global for Services
			 */		
			if (!$is_admin && $groupnumber)
				$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
						" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, centreon_acl" .
						" WHERE no.object_id = nss.service_object_id".
						" AND no.name1 not like 'qos_Module' ".
						" AND no.name1 not like 'Meta_Module' ".
						" AND no.name1 = centreon_acl.host_name ".
						" AND no.name2 = centreon_acl.service_description " .
						" AND no.name1 NOT LIKE 'Meta_Module' AND no.name1 NOT LIKE 'qos_Module' " .
						" AND centreon_acl.group_id IN (".groupsListStr($grouplist).") " .
						" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";
			else
				$rq2 = 	" SELECT count(nss.current_state), nss.current_state". 
						" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
						" WHERE no.object_id = nss.service_object_id".
						" AND no.name1 not like 'qos_Module' ".
						" AND no.name1 not like 'Meta_Module' ".
						" AND no.name1 NOT LIKE 'Meta_Module' AND no.name1 NOT LIKE 'qos_Module' " .
						" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";					
		
			$DBRESULT_NDO2 =& $pearDBndo->query($rq2);
			if (PEAR::isError($DBRESULT_NDO2))
				print "DB Error : ".$DBRESULT_NDO2->getDebugInfo()."<br />";
		
			$SvcStat = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
		
			while ($ndo =& $DBRESULT_NDO2->fetchRow())
				$SvcStat[$ndo["current_state"]] = $ndo["count(nss.current_state)"];
	
			/*
			 * Get on pb host
			*/
			if (!$is_admin && $groupnumber)
				$rq2 = 	" SELECT nss.current_state, " . $ndo_base_prefix ."services.host_object_id".
						" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, centreon_acl, " . $ndo_base_prefix."services" .
						" WHERE no.object_id = nss.service_object_id".
						" AND nss.service_object_id = ".$ndo_base_prefix."services.service_object_id".
						" AND no.name1 not like 'qos_Module' ".
						" AND no.name1 not like 'Meta_Module' ".
						" AND no.name1 = centreon_acl.host_name ".
						" AND no.name2 = centreon_acl.service_description " .
						" AND centreon_acl.group_id IN (".groupsListStr($grouplist).") " .
						" AND no.is_active = 1" .
						" AND nss.problem_has_been_acknowledged = 0" .
						" AND nss.current_state > 0 GROUP BY nss.service_object_id";
			else
				$rq2 = 	" SELECT nss.current_state, ". $ndo_base_prefix ."services.host_object_id".
						" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, " . $ndo_base_prefix."services" .
						" WHERE no.object_id = nss.service_object_id".
						" AND nss.service_object_id = ".$ndo_base_prefix."services.service_object_id".
						" AND no.name1 not like 'qos_Module' ".
						" AND no.name1 not like 'Meta_Module' ".
						" AND no.is_active = 1" .
						" AND nss.problem_has_been_acknowledged = 0" .
						" AND nss.current_state > 0 GROUP BY nss.service_object_id";
			
			$onPbHost = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
			
			$DBRESULT_NDO1 =& $pearDBndo->query($rq2);
			if (PEAR::isError($DBRESULT_NDO1))
				print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
			while($ndo =& $DBRESULT_NDO1->fetchRow())	{			
				if ($ndo["current_state"] != 0)
					for ($i = 0; $i < $pbCount; $i++)
						if (isset($hostPb[$i]) && ($hostPb[$i] == $ndo["host_object_id"]))
							$onPbHost[$ndo["current_state"]]++;
			}
		
			
			/*
			 * Get ServiceAck  OK(0), WARNING(1),  CRITICAL(2), UNKNOWN(3)
			 */
			if (!$is_admin)
				$rq1 = 	" SELECT count(DISTINCT ".$ndo_base_prefix."objects.object_id), " . $ndo_base_prefix."servicestatus.current_state" .
						" FROM ".$ndo_base_prefix."objects, ".$ndo_base_prefix."servicestatus, centreon_acl" .
						" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."servicestatus.service_object_id" .					
						" AND ".$ndo_base_prefix."servicestatus.problem_has_been_acknowledged = 1 " .
						" AND ".$ndo_base_prefix."objects.is_active = 1 " .
						" AND ".$ndo_base_prefix."objects.name1 = centreon_acl.host_name ".
						" AND ".$ndo_base_prefix."objects.name2 = centreon_acl.service_description " .
						" AND centreon_acl.group_id IN (".groupsListStr($grouplist).") " .
						" AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'Meta_Module' AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'qos_Module'" .
						" GROUP BY ".$ndo_base_prefix."servicestatus.current_state";								
			else
				$rq1 = 	" SELECT count(DISTINCT ".$ndo_base_prefix."objects.object_id), " . $ndo_base_prefix."servicestatus.current_state" .
						" FROM ".$ndo_base_prefix."objects, ".$ndo_base_prefix."servicestatus" .
						" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."servicestatus.service_object_id" .
						" AND ".$ndo_base_prefix."servicestatus.problem_has_been_acknowledged = 1 " .
						" AND ".$ndo_base_prefix."objects.is_active = 1 " .
						" AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'Meta_Module' AND ".$ndo_base_prefix."objects.name1 NOT LIKE 'qos_Module'" .
						" GROUP BY ".$ndo_base_prefix."servicestatus.current_state";									
			
			$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
			if (PEAR::isError($DBRESULT_NDO1))
				print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
			
			$svcAck = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
			while ($ndo =& $DBRESULT_NDO1->fetchRow())
				$svcAck[$ndo["current_state"]] = $ndo["count(DISTINCT ".$ndo_base_prefix."objects.object_id)"];
			
			/*
			 * Get Services Inactive objects
			 */
			if (!$is_admin && $groupnumber)
				$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
						" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, centreon_acl " .
						" WHERE no.object_id = nss.service_object_id".
						" AND no.name1 not like 'qos_Module' ".
						" AND no.name1 not like 'Meta_Module' ".
						" AND no.name1 = centreon_acl.host_name ".
						" AND no.name2 = centreon_acl.service_description " .
						" AND centreon_acl.group_id IN (".groupsListStr($grouplist).") ".
						" AND no.is_active = 0 GROUP BY nss.current_state ORDER by nss.current_state";
			else
				$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
						" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
						" WHERE no.object_id = nss.service_object_id".
						" AND no.name1 not like 'qos_Module' ".
						" AND no.name1 not like 'Meta_Module' ".
						" AND no.is_active = 0 GROUP BY nss.current_state ORDER by nss.current_state";			
	
			$DBRESULT_NDO2 =& $pearDBndo->query($rq2);
			if (PEAR::isError($DBRESULT_NDO2))
				print "DB Error : ".$DBRESULT_NDO2->getDebugInfo()."<br />";
		
			$svcInactive = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
			while($ndo =& $DBRESULT_NDO2->fetchRow())
				$svcInactive[$ndo["current_state"]] = $ndo["count(nss.current_state)"];
	
			/*
			 * Get Undandled Services
			 */
			
			$svcUnhandled = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
			for ($i=0; $i<=4; $i++)
				$svcUnhandled[$i] = $SvcStat[$i] - $svcAck[$i] - $svcInactive[$i] - $onPbHost[$i];			
			 
			/*
			 * Get problem table
			*/
			if (!$is_admin && $groupnumber)
				$rq1 = 	" SELECT distinct obj.name1, obj.name2, stat.current_state, unix_timestamp(stat.last_check) as last_check, stat.output, unix_timestamp(stat.last_state_change) as last_state_change, svc.host_object_id, " . "ht.address" .
						" FROM ".$ndo_base_prefix."objects obj, ".$ndo_base_prefix."servicestatus stat, " . $ndo_base_prefix . "services svc, centreon_acl," . $ndo_base_prefix . "hosts ht" .
						" WHERE obj.object_id = stat.service_object_id" .
						" AND stat.service_object_id = svc.service_object_id" .
						" AND obj.name1 = ht.display_name" .
						" AND stat.current_state > 0" .
						" AND stat.current_state <> 3" .
						" AND stat.problem_has_been_acknowledged = 0" .
						" AND obj.is_active = 1" .
						" AND obj.name1 NOT LIKE 'Meta_Module' AND obj.name1 NOT LIKE 'qos_Module' " .
						" AND obj.name1 = centreon_acl.host_name ".
						" AND obj.name2 = centreon_acl.service_description " .
						" AND centreon_acl.group_id IN (".groupsListStr($grouplist).") " .
						" ORDER by stat.current_state DESC, obj.name1";
			else
				$rq1 = 	" SELECT distinct obj.name1, obj.name2, stat.current_state, unix_timestamp(stat.last_check) as last_check, stat.output, unix_timestamp(stat.last_state_change) as last_state_change, svc.host_object_id, " . "ht.address" .
						" FROM ".$ndo_base_prefix."objects obj, ".$ndo_base_prefix."servicestatus stat, " . $ndo_base_prefix . "services svc, " . $ndo_base_prefix . "hosts ht" .
						" WHERE obj.object_id = stat.service_object_id" .
						" AND stat.service_object_id = svc.service_object_id" .
						" AND obj.name1 = ht.display_name" .
						" AND stat.current_state > 0" .
						" AND stat.current_state <> 3" .
						" AND stat.problem_has_been_acknowledged = 0" .
						" AND obj.is_active = 1" .				
						" AND obj.name1 NOT LIKE 'Meta_Module' AND obj.name1 NOT LIKE 'qos_Module' " .
						" ORDER by stat.current_state DESC, obj.name1";
			
			$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
			if (PEAR::isError($DBRESULT_NDO1))
				print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
			
			$j = 0;	
			$tab_hostname[$j] = "";
			$tab_svcname[$j] = "";
			$tab_state[$j] = "";
			$tab_last[$j] = "";
			$tab_duration[$j] = "";
			$tab_output[$j] = "";
			$tab_ip[$j] = "";
			
			while ($ndo =& $DBRESULT_NDO1->fetchRow()){
				$is_unhandled = 1;	
	
				for ($i = 0; $i < $pbCount && $is_unhandled; $i++){
					if (isset($hostPb[$i]) && ($hostPb[$i] == $ndo["host_object_id"]))
						$is_unhandled = 0;
				}
	
				if ($is_unhandled){
					$tab_hostname[$j] = $ndo["name1"];
					$tab_svcname[$j] = $ndo["name2"];
					$tab_state[$j] = $ndo["current_state"];
					$tab_last[$j] = $oreon->CentreonGMT->getDate(_("Y/m/d G:i"), $ndo["last_check"], $oreon->user->getMyGMT());
					$tab_ip[$j] = $ndo["address"];
		
					if ($ndo["last_state_change"] > 0 && time() > $ndo["last_state_change"])
						$tab_duration[$j] = Duration::toString(time() - $ndo["last_state_change"]);
					else if ($ndo["last_state_change"] > 0)
						$tab_duration[$j] = " - ";
					$tab_output[$j] = $ndo["output"];
					$j++;
				}
			}
			$nb_pb = $j;
			 
			$path = "./include/home/tacticalOverview/";
		
			/*
			 * Smaty template Init
			 */
			$tpl = new Smarty();
			$tpl = initSmartyTpl($path, $tpl);
			
			$tpl->assign("color", $general_opt);
			$tpl->assign("HostStatus", $hostStatus);
			$tpl->assign("HostAck", $hostAck);
			$tpl->assign("HostInact", $hostInactive);
			$tpl->assign("HostUnhand", $hostUnhand);
			$tpl->assign("ServiceStatus", $SvcStat);
			$tpl->assign("SvcAck", $svcAck);
			$tpl->assign("SvcInact", $svcInactive);
			$tpl->assign("SvcOnPbHost", $onPbHost);
			$tpl->assign("SvcUnhandled", $svcUnhandled);
			$tpl->assign("nb_pb", $nb_pb);
			$tpl->assign("tb_hostname", $tab_hostname);
			$tpl->assign("tb_svcname", $tab_svcname);
			$tpl->assign("tb_state", $tab_state);
			$tpl->assign("tb_last", $tab_last);
			$tpl->assign("tb_output", $tab_output);
			$tpl->assign("tb_duration", $tab_duration);
			$tpl->assign("tb_ip", $tab_ip);
			
			$tpl->assign("refresh_interval", $oreon->optGen["oreon_refresh"]);
			
			/*
			 * URL
			 */
			$tpl->assign("url_hostPb", "main.php?p=20103&o=hpb");
			$tpl->assign("url_ok", "main.php?p=2020101&o=svc_ok");
			$tpl->assign("url_critical", "main.php?p=2020202&o=svc_critical");
			$tpl->assign("url_warning", "main.php?p=2020201&o=svc_warning");
			$tpl->assign("url_unknown", "main.php?p=2020203&o=svc_unknown");
			$tpl->assign("url_hostdetail", "main.php?p=201&o=hd&host_name=");
			$tpl->assign("url_svcdetail", "main.php?p=202&o=svcd&host_name=");
			$tpl->assign("url_svcdetail2", "&service_description=");
			
			/*
			 *  Strings for the host part
			 */
			$tpl->assign("str_hosts", _("Hosts"));
			$tpl->assign("str_up", _("Up"));
			$tpl->assign("str_down", _("Down"));
			$tpl->assign("str_unreachable", _("Unreachable"));
			
			/*
			 *  Strings for the service part
			 */
			$tpl->assign("str_services", _("Services"));
			$tpl->assign("str_ok", _("OK"));
			$tpl->assign("str_warning", _("Warning"));
			$tpl->assign("str_critical", _("Critical"));
			$tpl->assign("str_unknown", _("Unknown"));
			$tpl->assign("str_pbhost", _("On Problem Host"));
			$tpl->assign("str_unhandledpb", _("Unhandled"));
			
			/*
			 *  Common Strings for both the host and service parts
			 */
		 	$tpl->assign("str_pending", _("Pending"));
			$tpl->assign("str_disabled", _("Disabled"));
			$tpl->assign("str_acknowledged", _("Acknowledged"));
			
			/*
			 *  Strings for service problems
			 */
			$tpl->assign("str_unhandled", _("Unhandled Service problems"));
			$tpl->assign("str_no_unhandled", _("No unhandled service problem"));
			$tpl->assign("str_hostname", _("Host Name"));
			$tpl->assign("str_servicename", _("Service Name"));
			$tpl->assign("str_status", _("Status"));
			$tpl->assign("str_lastcheck", _("Last Check"));
			$tpl->assign("str_duration", _("Duration"));
			$tpl->assign("str_output", _("Status Output"));
			$tpl->assign("str_actions", _("Actions"));
			$tpl->assign("str_ip", _("IP Address"));
			
			/*
			 * Display tactical
			 */
			$tpl->display("tacticalOverview.ihtml");	
	}
 ?>