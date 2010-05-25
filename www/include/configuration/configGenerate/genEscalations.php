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

/*
 * This script generate the file configuration escalations.cfg used by Nagios.
 * This script is shared to 6 parts:
 *  - PART 1 : Collect Escalations for all Hosts
 *  - PART 2 : Collect Escalations for all HostGroups 
 *  - PART 3 : Collect Escalations for all ServiceGroups
 *  - PART 4 : Collect Escalations for all Services
 *  - PART 5 : Generate the configuration with all datas collected
 *
 * PHP version 5
 *
 * @package genEscalations.php
 * @author Damien Duponchelle dduponchelle@merethis.com
 * @version $Id$
 * @copyright (c) 2004-2008 Centreon
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

	if (!isset($oreon))
		exit();
		
	/*
	 * Creating handle to write the file configuration
	 */
	$handle = create_file($nagiosCFGPath.$tab['id']."/escalations.cfg", $oreon->user->get_name());

	/*
	 * PART 1 - Escalations for all Hosts
	 */
	$DBRESULT =& $pearDB->query("SELECT DISTINCT esc.* FROM escalation_host_relation ehr, escalation esc WHERE ehr.escalation_esc_id = esc.esc_id ORDER BY esc.esc_name");
	
	$escalation = array();
	$i = 1;
	$str = NULL;
	
	while ($escalation =& $DBRESULT->fetchRow()) {
		
		$strDef = "";
		$linkedToHost = 0;
				
		$DBRESULT2 =& $pearDB->query("SELECT DISTINCT host.host_id, host.host_name, host.host_location FROM escalation_host_relation ehr, host, ns_host_relation nhr WHERE ehr.escalation_esc_id = '".$escalation["esc_id"]."' AND host.host_id = ehr.host_host_id AND host.host_id = nhr.host_host_id AND nhr.nagios_server_id = '".$tab['id']."' AND host.host_activate = '1'");
		
		$hosts = array();
		$strTemp = NULL;
		while ($host =& $DBRESULT2->fetchRow())	{
			if (isset($gbArr[2][$host["host_id"]]) && isset($hostGenerated[$host["host_id"]])) {
				$linkedToHost++;
				if (!isset($hosts[$host["host_location"]]))
					$hosts[$host["host_location"]] = array();
				$hosts[$host["host_location"]][$host["host_id"]] = $host["host_name"];
			}
		}
		$DBRESULT2->free();			
		
		/*
		 * Generate
		 */
		foreach ($hosts as $gmt => $host) {
			$strDef = "";
			$strTMPHost = "";
			foreach ($host as $host_id => $hostList){
				if ($strTMPHost != "")
					$strTMPHost .= ",";
				$strTMPHost .= $hostList;
			}
			
			if (isset($strTMPHost) && strlen($strTMPHost)) {
				
				$ret["comment"] ? ($strDef .= "# '".$escalation["esc_name"]."' host escalation definition ".$i."\n") : NULL;
				if ($ret["comment"] && $escalation["esc_comment"]) {
					$comment = array();
					$comment = explode("\n", $escalation["esc_comment"]);					
					foreach ($comment as $cmt) 
						$strDef .= "# ".$cmt."\n";
				}
				
				$strDef .= "define hostescalation{\n";
				
				/*
				 * Write Hosts
				 */
				$strDef .= print_line("host_name", $strTMPHost);
				
				/*
				 * ContactGroups
				 */
				$cg = array();
				$strTemp = NULL;				
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT cg.cg_id, cg.cg_name FROM escalation_contactgroup_relation ecgr, contactgroup cg WHERE ecgr.escalation_esc_id = '".$escalation["esc_id"]."' AND ecgr.contactgroup_cg_id = cg.cg_id ORDER BY cg.cg_name");
				while($cg =& $DBRESULT2->fetchRow()) {
					if (isset($gbArr[1][$cg["cg_id"]]))
						$strTemp != NULL ? $strTemp .= ", ".$cg["cg_name"] : $strTemp = $cg["cg_name"];
				}
				$DBRESULT2->free();
				
				if (isset($strTemp)) 
					$strDef .= print_line("contact_groups", $strTemp);			
				if (isset($escalation["first_notification"]))
					$strDef .= print_line("first_notification", $escalation["first_notification"]);
				if (isset($escalation["last_notification"])) 
					$strDef .= print_line("last_notification", $escalation["last_notification"]);
				if (isset($escalation["notification_interval"])) 
					$strDef .= print_line("notification_interval", $escalation["notification_interval"]);
				
				$DBRESULT2 =& $pearDB->query("SELECT tp_name FROM timeperiod WHERE tp_id = '".$escalation["escalation_period"]."'");
				$tp =& $DBRESULT2->fetchRow();				
				$DBRESULT2->free();
				
				if (isset($tp["tp_name"])) {
					if ($oreon->CentreonGMT->used() == 1)
						$strDef .= print_line("escalation_period", $tp["tp_name"]."_GMT".$gmt);
					else
						$strDef .= print_line("escalation_period", $tp["tp_name"]);
				}
				if (isset($escalation["escalation_options1"])) 
					$strDef .= print_line("escalation_options", $escalation["escalation_options1"]);
				
				$strDef .= "}\n\n";
				$i++;
			}
			if (isset($linkedToHost))
				$str .= $strDef;
			unset($strDef);	
		}
		
	}
	unset($escalation);
	$DBRESULT->free();

	/*
	 * PART 2 - Escalations for all HostGroups
	 */
	
	if ($oreon->CentreonGMT->used() == 1) {	
		$DBRESULT =& $pearDB->query("SELECT DISTINCT esc.* FROM escalation_hostgroup_relation ehgr, escalation esc WHERE ehgr.escalation_esc_id = esc.esc_id ORDER BY esc.esc_name");
		
		$escalation = array();
		while ($escalation =& $DBRESULT->fetchRow())	{
			$hosts = array();
			$DBRESULT2 =& $pearDB->query("SELECT DISTINCT hg.hg_id, hg.hg_name FROM escalation_hostgroup_relation ehgr, hostgroup hg WHERE ehgr.escalation_esc_id = '".$escalation["esc_id"]."' AND hg.hg_id = ehgr.hostgroup_hg_id");
			$strTemp = NULL;
			$hg = array();
			while ($hg =& $DBRESULT2->fetchRow()) {
				if (isset($gbArr[3][$hg["hg_id"]]) && isset($hgHostGenerated[$hg["hg_id"]])) {
					$hostList = getMyHostGroupHosts($hg["hg_id"]);
					foreach ($hostList as $host_id) {
						if (isset($hostGenerated[$host_id])) {
							$host_location = getMyHostFieldOnHost($host_id, "host_location");
							if (!isset($hosts[$host_location]))
								$hosts[$host_location] = array();
							$hosts[$host_location][$host_id] = getMyHostName($host_id);
							unset($host_location);
						}
					}
				}
			}
			$DBRESULT2->free();
			
			foreach ($hosts as $gmt => $host) {
				$strDef = "";
				$strTMPHost = "";
				foreach ($host as $host_id => $hostList){
					if ($strTMPHost != "")
						$strTMPHost .= ",";
					$strTMPHost .= $hostList;
				}
			
				if (isset($strTMPHost)) {
					$ret["comment"] ? ($str .= "# '".$escalation["esc_name"]."' host (group) escalation definition ".$i."\n") : NULL;
					
					if (isset($ret["comment"]) && isset($escalation["esc_comment"])) {
						$comment = array();
						$comment = explode("\n", $escalation["esc_comment"]);
						foreach ($comment as $cmt)
							$str .= "# ".$cmt."\n";
					}
					
					$str .= "define hostescalation{\n";
					$str .= print_line("host_name", $strTMPHost);			
					
					$cg = array();
					$strTemp = "";
					$DBRESULT2 =& $pearDB->query("SELECT DISTINCT cg.cg_id, cg.cg_name FROM escalation_contactgroup_relation ecgr, contactgroup cg WHERE ecgr.escalation_esc_id = '".$escalation["esc_id"]."' AND ecgr.contactgroup_cg_id = cg.cg_id ORDER BY cg.cg_name");
					while ($cg =& $DBRESULT2->fetchRow()) {
						if (isset($gbArr[1][$cg["cg_id"]]))
							$strTemp != NULL ? $strTemp .= ", ".$cg["cg_name"] : $strTemp = $cg["cg_name"];
					}
					$DBRESULT2->free();
					
					if (isset($strTemp)) 
						$str .= print_line("contact_groups", $strTemp);			
					if ($escalation["first_notification"] != NULL) 
						$str .= print_line("first_notification", $escalation["first_notification"]);
					if ($escalation["last_notification"] != NULL) 
						$str .= print_line("last_notification", $escalation["last_notification"]);
					if ($escalation["notification_interval"] != NULL) 
						$str .= print_line("notification_interval", $escalation["notification_interval"]);
											
					if (isset($timeperiods[$escalation["escalation_period"]])) 
						if ($oreon->CentreonGMT->used() == 1)
							$str .= print_line("escalation_period", $timeperiods[$escalation["escalation_period"]]."_GMT".getMyHostFieldOnHost($host_id, "host_location"));
						else
							$str .= print_line("escalation_period", $timeperiods[$escalation["escalation_period"]]);
					
					if ($escalation["escalation_options1"]) 
						$str .= print_line("escalation_options", $escalation["escalation_options1"]);
					$str .= "}\n\n";
					$i++;
				}
			}
			unset($escalation);
			$DBRESULT->free();
		}
	} else {
		$DBRESULT =& $pearDB->query("SELECT DISTINCT esc.* FROM escalation_hostgroup_relation ehgr, escalation esc WHERE ehgr.escalation_esc_id = esc.esc_id ORDER BY esc.esc_name");
		
		$escalation = array();
		while ($escalation =& $DBRESULT->fetchRow())	{
		
			$DBRESULT2 =& $pearDB->query("SELECT DISTINCT hg.hg_id, hg.hg_name FROM escalation_hostgroup_relation ehgr, hostgroup hg WHERE ehgr.escalation_esc_id = '".$escalation["esc_id"]."' AND hg.hg_id = ehgr.hostgroup_hg_id");		
			$hg = array();
			$strTemp = NULL;
			while ($hg =& $DBRESULT2->fetchRow()) {
				if ($gbArr[3][$hg["hg_id"]] && isset($hgHostGenerated[$hg["hg_id"]]))
					$strTemp != NULL ? $strTemp .= ", ".$hg["hg_name"] : $strTemp = $hg["hg_name"];
			}
			$DBRESULT2->free();
	
			if (isset($strTemp) && isset($escalation)) {
				$ret["comment"] ? ($str .= "# '".$escalation["esc_name"]."' host (group) escalation definition ".$i."\n") : NULL;	
				if (isset($ret["comment"]) == true && isset($escalation["esc_comment"]) == true) {
					$comment = array();
					$comment = explode("\n", $escalation["esc_comment"]);
					foreach ($comment as $cmt)
						$str .= "# ".$cmt."\n";
				}
				
				$str .= "define hostescalation{\n";
				$str .= print_line("hostgroup_name", $strTemp);			
				
				$cg = array();
				$strTemp = NULL;
				
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT cg.cg_id, cg.cg_name FROM escalation_contactgroup_relation ecgr, contactgroup cg WHERE ecgr.escalation_esc_id = '".$escalation["esc_id"]."' AND ecgr.contactgroup_cg_id = cg.cg_id ORDER BY cg.cg_name");
				
				while ($cg =& $DBRESULT2->fetchRow()) {
					if (isset($gbArr[1][$cg["cg_id"]]))
						$strTemp != NULL ? $strTemp .= ", ".$cg["cg_name"] : $strTemp = $cg["cg_name"];
				}
				
				$DBRESULT2->free();
				
				if (isset($strTemp)) 
					$str .= print_line("contact_groups", $strTemp);			
				if ($escalation["first_notification"] != NULL) 
					$str .= print_line("first_notification", $escalation["first_notification"]);
				if ($escalation["last_notification"] != NULL) 
					$str .= print_line("last_notification", $escalation["last_notification"]);
				if ($escalation["notification_interval"] != NULL) 
					$str .= print_line("notification_interval", $escalation["notification_interval"]);
				
				if (isset($timeperiods[$escalation["escalation_period"]])) 
					$str .= print_line("escalation_period", $timeperiods[$escalation["escalation_period"]]);
				if ($escalation["escalation_options1"]) 
					$str .= print_line("escalation_options", $escalation["escalation_options1"]);
				$str .= "}\n\n";
				$i++;
			}
		}
	}
	
	/*
	 * PART 3 - Escalations for all ServiceGroups	
	 */
	$DBRESULT =& $pearDB->query("SELECT DISTINCT esc.* FROM escalation_servicegroup_relation esgr, escalation esc WHERE esgr.escalation_esc_id = esc.esc_id ORDER BY esc.esc_name");
	$escalation = array();
	$strTemp = NULL;
	if ($oreon->CentreonGMT->used() == 1) {
		/*
		 * Get Service groups elements
		 */
		unset($hosts);
		while ($escalation =& $DBRESULT->fetchRow()) {
			$DBRESULT2 =& $pearDB->query("SELECT DISTINCT sg.sg_id FROM escalation_servicegroup_relation esgr, servicegroup sg WHERE esgr.escalation_esc_id = '".$escalation["esc_id"]."' AND sg.sg_id = esgr.servicegroup_sg_id");
			while ($sg =& $DBRESULT2->fetchRow()) {
				if (isset($gbArr[5][$sg["sg_id"]]) && isset($generatedSG[$sg["sg_id"]])) {
					$services =& getMyServiceGroupActivateServices($sg["sg_id"]);
					foreach ($services as $key => $desc){
						$tmptab = split("_", $key);
						$host_location = getMyHostFieldOnHost($tmptab[0], "host_location");
						if (!isset($hosts[$host_location]))
							$hosts[$host_location] = array();
						if (!isset($hosts[$host_location][$tmptab[0]]))
							$hosts[$host_location][$tmptab[0]] = array();
						$timePeriod = $escalation['escalation_period'];
						$hosts[$host_location][$tmptab[0]][$tmptab[0]."_".$tmptab[1]."_".$timePeriod] = $escalation;
					}
				}
			}
			$DBRESULT2->free();
		}
		$DBRESULT->free();
		
		if (isset($hosts))
			foreach ($hosts as $gmt => $value) {
				foreach ($value as $host){
					if (isset($host) && count($host))
						foreach ($host as $ids => $escalation) {
							$tabHS = split("_", $ids);
							if (isset($host_instance[$tabHS[0]]) && isset($hostGenerated[$host_id])) {
								$host = array();
								$strDef = "";
								$host_id = $tabHS[0];
								$host_name = getMyHostName($tabHS[0]);
								$service_description = getMyServiceName($tabHS[1]);				
								if (isset($gbArr[2][$host_id])) {
			
									$ret["comment"] ? ($strDef .= "# '".$escalation["esc_name"]."' service in Service groups escalation definition ".$i."\n") : NULL;						
									if (isset($ret["comment"]) && isset($escalation["esc_comment"]))	{
										$comment = array();
										$comment = explode("\n", $escalation["esc_comment"]);
										foreach ($comment as $cmt)
											$strDef .= "# ".$cmt."\n";
									}
									
									$strDef .= "define serviceescalation{\n";			
									$strDef .= print_line("host_name", $host_name);										
									if (isset($host_instance[$host_id]))					
										$generated++;
									
									$strDef .= print_line("service_description", $service_description);
									$cg = array();
									$strTemp = NULL;
									
									$DBRESULT3 =& $pearDB->query("SELECT DISTINCT cg.cg_id, cg.cg_name FROM escalation_contactgroup_relation ecgr, contactgroup cg WHERE ecgr.escalation_esc_id = '".$escalation["esc_id"]."' AND ecgr.contactgroup_cg_id = cg.cg_id ORDER BY cg.cg_name");
									while ($cg =& $DBRESULT3->fetchRow()) {
										if (isset($gbArr[1][$cg["cg_id"]]))
											$strTemp != NULL ? $strTemp .= ", ".$cg["cg_name"] : $strTemp = $cg["cg_name"];
									}
									$DBRESULT3->free();
				
									if (isset($strTemp)) 
										$strDef .= print_line("contact_groups", $strTemp);			
									if (isset($escalation["first_notification"])) 
										$strDef .= print_line("first_notification", $escalation["first_notification"]);
									if (isset($escalation["last_notification"])) 
										$strDef .= print_line("last_notification", $escalation["last_notification"]);
									if (isset($escalation["notification_interval"])) 
										$strDef .= print_line("notification_interval", $escalation["notification_interval"]);
				
									if (isset($timeperiods[$escalation["escalation_period"]])) 
										if ($oreon->CentreonGMT->used() == 1)
											$strDef .= print_line("escalation_period", $timeperiods[$escalation["escalation_period"]]."_GMT".getMyHostFieldOnHost($host_id, "host_location"));
										else
											$strDef .= print_line("escalation_period", $timeperiods[$escalation["escalation_period"]]);
									
									if (isset($escalation["escalation_options2"])) 
										$strDef .= print_line("escalation_options", $escalation["escalation_options2"]);
									$strDef .= "}\n\n";					
									$str .= $strDef;
									$i++;
									unset($escalation);
								}		
						}
					}
				}
			}	
		$DBRESULT->free();
	} else {
		while ($escalation =& $DBRESULT->fetchRow()) {			
			$strTemp = "";			
			$sg = array(); 
			$DBRESULT2 =& $pearDB->query("SELECT DISTINCT sg.sg_id, sg.sg_name FROM escalation_servicegroup_relation esgr, servicegroup sg WHERE esgr.escalation_esc_id = '".$escalation["esc_id"]."' AND sg.sg_id = esgr.servicegroup_sg_id");
			while ($sg =& $DBRESULT2->fetchRow()) {
				if (isset($gbArr[5][$sg["sg_id"]]) && isset($generatedSG[$sg["sg_id"]]))
					$strTemp != NULL ? $strTemp .= ", ".$sg["sg_name"] : $strTemp = $sg["sg_name"];
			}
			$DBRESULT2->free();
				
			if (isset($strTemp)) {
				$ret["comment"] ? ($str .= "# '".$escalation["esc_name"]."' service (group) escalation definition ".$i."\n") : NULL;
				
				if ($ret["comment"] && $escalation["esc_comment"]) {
					$comment = array();
					$comment = explode("\n", $escalation["esc_comment"]);
					
					foreach ($comment as $cmt)
						$str .= "# ".$cmt."\n";
				}
				
				$str .= "define serviceescalation{\n";
				$str .= print_line("servicegroup_name", $strTemp);			
				
				$cg = array();
				$strTemp = NULL;
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT cg.cg_id, cg.cg_name FROM escalation_contactgroup_relation ecgr, contactgroup cg WHERE ecgr.escalation_esc_id = '".$escalation["esc_id"]."' AND ecgr.contactgroup_cg_id = cg.cg_id ORDER BY cg.cg_name");
				while ($cg =& $DBRESULT2->fetchRow()) {
					if (isset($gbArr[1][$cg["cg_id"]])) {
						$strTemp != NULL ? $strTemp .= ", ".$cg["cg_name"] : $strTemp = $cg["cg_name"];
					}
				}				
				$DBRESULT2->free();
				
				if (isset($strTemp)) 
					$str .= print_line("contact_groups", $strTemp);			
				if (isset($escalation["first_notification"])) 
					$str .= print_line("first_notification", $escalation["first_notification"]);
				if (isset($escalation["last_notification"])) 
					$str .= print_line("last_notification", $escalation["last_notification"]);
				if (isset($escalation["notification_interval"]))
					$str .= print_line("notification_interval", $escalation["notification_interval"]);
	
				if (isset($timeperiods[$escalation["escalation_period"]])) 
					$str .= print_line("escalation_period", $timeperiods[$escalation["escalation_period"]]);
				if (isset($escalation["escalation_options2"])) 
					$str .= print_line("escalation_options", $escalation["escalation_options2"]);
	
				$str .= "}\n\n";
				$i++;
			}
		}
		unset($escalation);
		$DBRESULT->free();	
	}

	/*
	 * PART 4 -Escalation for all Services	
	 */	 
	$generated = 0;
	$DBRESULT =& $pearDB->query("SELECT DISTINCT service.service_activate, service.service_description, esr.service_service_id FROM service, escalation_service_relation esr WHERE esr.service_service_id = service.service_id ORDER BY esr.service_service_id");
	while ($service =& $DBRESULT->fetchRow()) {
		if (isset($gbArr[4][$service["service_service_id"]])) {
			$DBRESULT2 =& $pearDB->query("SELECT * FROM escalation esc, escalation_service_relation esr WHERE esr.service_service_id = '".$service["service_service_id"]."' AND esc.esc_id = esr.escalation_esc_id ORDER BY esc.esc_name");
			
			$escalation = array();
			while ($escalation =& $DBRESULT2->fetchRow()) {
				if (isset($host_instance[$escalation["host_host_id"]])){
					$host = array();
					$strDef = "";
									
					if (isset($gbArr[2][$escalation["host_host_id"]])) {
						$ret["comment"] ? ($strDef .= "# '".$escalation["esc_name"]."' service escalation definition ".$i."\n") : NULL;						
						if (isset($ret["comment"]) && isset($escalation["esc_comment"]))	{
							$comment = array();
							$comment = explode("\n", $escalation["esc_comment"]);
							foreach ($comment as $cmt)
								$strDef .= "# ".$cmt."\n";
						}
						
						$strDef .= "define serviceescalation{\n";			
						$strDef .= print_line("host_name", getMyHostName($escalation["host_host_id"]));										
						if (isset($host_instance[$escalation["host_host_id"]]))
							$generated++;
						
						$service["service_description"] = str_replace('#S#', "/", $service["service_description"]);
						$service["service_description"] = str_replace('#BS#', "\\", $service["service_description"]);

						$strDef .= print_line("service_description", $service["service_description"]);

						$cg = array();
						$strTemp = NULL;						
						$DBRESULT3 =& $pearDB->query("SELECT DISTINCT cg.cg_id, cg.cg_name FROM escalation_contactgroup_relation ecgr, contactgroup cg WHERE ecgr.escalation_esc_id = '".$escalation["esc_id"]."' AND ecgr.contactgroup_cg_id = cg.cg_id ORDER BY cg.cg_name");
						while ($cg =& $DBRESULT3->fetchRow()) {
							if (isset($gbArr[1][$cg["cg_id"]]))
								$strTemp != NULL ? $strTemp .= ", ".$cg["cg_name"] : $strTemp = $cg["cg_name"];
						}
						$DBRESULT3->free();
	
						if (isset($strTemp)) 
							$strDef .= print_line("contact_groups", $strTemp);			
						if (isset($escalation["first_notification"])) 
							$strDef .= print_line("first_notification", $escalation["first_notification"]);
						if (isset($escalation["last_notification"])) 
							$strDef .= print_line("last_notification", $escalation["last_notification"]);
						if (isset($escalation["notification_interval"])) 
							$strDef .= print_line("notification_interval", $escalation["notification_interval"]);
	
						if (isset($timeperiods[$escalation["escalation_period"]])) 
							if ($oreon->CentreonGMT->used() == 1)
								$strDef .= print_line("escalation_period", $timeperiods[$escalation["escalation_period"]]."_GMT".getMyHostFieldOnHost($escalation["host_host_id"], "host_location"));
							else
								$strDef .= print_line("escalation_period", $timeperiods[$escalation["escalation_period"]]);
						
						if (isset($escalation["escalation_options2"])) 
							$strDef .= print_line("escalation_options", $escalation["escalation_options2"]);
	
						$strDef .= "}\n\n";					
						$i++;
					}					
				}
				if ($generated){	
					$str .= $strDef;
					$strDef = "";
				}				
			}	
			unset($escalation);
			$DBRESULT2->free();		
		}
	}		
	unset($service);
	$DBRESULT->free();
	
	/*
	 * PART 5 - Generating final file configuration escalations.cfg		
	 */	 
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/escalations.cfg");
	fclose($handle);
	unset($str);
	unset($i);
?>