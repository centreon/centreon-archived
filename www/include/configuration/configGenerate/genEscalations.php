<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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

	if (!is_dir($nagiosCFGPath.$tab['id']."/")) {
		mkdir($nagiosCFGPath.$tab['id']."/");
	}

	$handle = create_file($nagiosCFGPath.$tab['id']."/escalations.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query("SELECT DISTINCT esc.* FROM escalation_host_relation ehr, escalation esc WHERE ehr.escalation_esc_id = esc.esc_id ORDER BY esc.esc_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$escalation = array();
	$i = 1;
	$str = NULL;
	while($DBRESULT->fetchInto($escalation)){
		$BP = false;
		$strDef = "";
		$linkedToHost = 0;
		$DBRESULT2 =& $pearDB->query("SELECT DISTINCT host.host_id, host.host_name FROM escalation_host_relation ehr, host , ns_host_relation nhr WHERE ehr.escalation_esc_id = '".$escalation["esc_id"]."' AND host.host_id = ehr.host_host_id AND host.host_id = nhr.host_host_id AND nhr.nagios_server_id = '".$tab['id']."' ");
		if (PEAR::isError($DBRESULT2))
			print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
		$host = array();
		$strTemp = NULL;
		while ($DBRESULT2->fetchInto($host))	{
			$BP = false;
			array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
			
			if ($BP && isHostOnThisInstance($host["host_id"], $tab['id'])){
				$linkedToHost++;	
				$strTemp != NULL ? $strTemp .= ", ".$host["host_name"] : $strTemp = $host["host_name"];
			}
		}
		$DBRESULT2->free();			
		if ($strTemp)	{
			print "-".$host["host_id"]."<br>";
			$ret["comment"]["comment"] ? ($strDef .= "# '".$escalation["esc_name"]."' host escalation definition ".$i."\n") : NULL;
			if ($ret["comment"]["comment"] && $escalation["esc_comment"])	{
				$comment = array();
				$comment = explode("\n", $escalation["esc_comment"]);
				foreach ($comment as $cmt)
					$strDef .= "# ".$cmt."\n";
			}
			$strDef .= "define hostescalation{\n";
			$strDef .= print_line("host_name", $strTemp);
			
			$cg = array();
			$strTemp = NULL;
			$DBRESULT2 =& $pearDB->query("SELECT DISTINCT cg.cg_id, cg.cg_name FROM escalation_contactgroup_relation ecgr, contactgroup cg WHERE ecgr.escalation_esc_id = '".$escalation["esc_id"]."' AND ecgr.contactgroup_cg_id = cg.cg_id ORDER BY cg.cg_name");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
			while($DBRESULT2->fetchInto($cg))	{
				$BP = false;				
				array_key_exists($cg["cg_id"], $gbArr[1]) ? $BP = true : $BP = false;
				
				if ($BP)
					$strTemp != NULL ? $strTemp .= ", ".$cg["cg_name"] : $strTemp = $cg["cg_name"];
			}
			
			$DBRESULT2->free();
			if ($strTemp) $strDef .= print_line("contact_groups", $strTemp);			
			if ($escalation["first_notification"] != NULL) $strDef .= print_line("first_notification", $escalation["first_notification"]);
			if ($escalation["last_notification"] != NULL) $strDef .= print_line("last_notification", $escalation["last_notification"]);
			if ($escalation["notification_interval"]!= NULL) $strDef .= print_line("notification_interval", $escalation["notification_interval"]);
			
			// Nagios 2
			
			if ($oreon->user->get_version() == 2)	{
				$DBRESULT2 =& $pearDB->query("SELECT tp_name FROM timeperiod WHERE tp_id = '".$escalation["escalation_period"]."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
				$tp =& $DBRESULT2->fetchRow();				
				if ($tp["tp_name"]) $strDef .= print_line("escalation_period", $tp["tp_name"]);
				if ($escalation["escalation_options1"]) $strDef .= print_line("escalation_options", $escalation["escalation_options1"]);
				$DBRESULT2->free();
			}
			$strDef .= "}\n\n";
			$i++;
		}
		if ($linkedToHost)
			$str .= $strDef;
		unset($strDef);
	}
	unset($escalation);
	$DBRESULT->free();
	
	$DBRESULT =& $pearDB->query("SELECT DISTINCT esc.* FROM escalation_hostgroup_relation ehgr, escalation esc WHERE ehgr.escalation_esc_id = esc.esc_id ORDER BY esc.esc_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$escalation = array();
	while($DBRESULT->fetchInto($escalation))	{
		$BP = false;
		$DBRESULT2 =& $pearDB->query("SELECT DISTINCT hg.hg_id, hg.hg_name FROM escalation_hostgroup_relation ehgr, hostgroup hg WHERE ehgr.escalation_esc_id = '".$escalation["esc_id"]."' AND hg.hg_id = ehgr.hostgroup_hg_id");
		if (PEAR::isError($DBRESULT2))
			print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
		$hg = array();
		$strTemp = NULL;
		while ($DBRESULT2->fetchInto($hg))	{
			$BP = false;
			array_key_exists($hg["hg_id"], $gbArr[3]) ? $BP = true : NULL;
			
			if ($BP && $generatedHG[$hg["hg_name"]])	
				$strTemp != NULL ? $strTemp .= ", ".$hg["hg_name"] : $strTemp = $hg["hg_name"];
		}
		$DBRESULT2->free();
			
		if ($strTemp && $generatedHG[$hg["hg_id"]])	{
			$ret["comment"]["comment"] ? ($str .= "# '".$escalation["esc_name"]."' host (group) escalation definition ".$i."\n") : NULL;
			if ($ret["comment"]["comment"] && $escalation["esc_comment"])	{
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
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
			while($DBRESULT2->fetchInto($cg))	{
				$BP = false;				
				array_key_exists($cg["cg_id"], $gbArr[1]) ? $BP = true : $BP = false;
				
				if ($BP)
					$strTemp != NULL ? $strTemp .= ", ".$cg["cg_name"] : $strTemp = $cg["cg_name"];
			}
			$DBRESULT2->free();
			if ($strTemp) $str .= print_line("contact_groups", $strTemp);			
			if ($escalation["first_notification"] != NULL) $str .= print_line("first_notification", $escalation["first_notification"]);
			if ($escalation["last_notification"] != NULL) $str .= print_line("last_notification", $escalation["last_notification"]);
			if ($escalation["notification_interval"] != NULL) $str .= print_line("notification_interval", $escalation["notification_interval"]);
			
			/*
			 *  Nagios 2
			 */
			
			if ($oreon->user->get_version() == 2)	{
				$DBRESULT2 =& $pearDB->query("SELECT tp_name FROM timeperiod WHERE tp_id = '".$escalation["escalation_period"]."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
				$tp =& $DBRESULT2->fetchRow();				
				if ($tp["tp_name"]) $str .= print_line("escalation_period", $tp["tp_name"]);
				if ($escalation["escalation_options1"]) $str .= print_line("escalation_options", $escalation["escalation_options1"]);
			}
			$str .= "}\n\n";
			$i++;
		}
	}
	unset($escalation);
	$DBRESULT->free();	
	
	$DBRESULT =& $pearDB->query("SELECT DISTINCT esc.* FROM escalation_servicegroup_relation esgr, escalation esc WHERE esgr.escalation_esc_id = esc.esc_id ORDER BY esc.esc_name");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$escalation = array();
	while($DBRESULT->fetchInto($escalation))	{
		$BP = false;
		$DBRESULT2 =& $pearDB->query("SELECT DISTINCT sg.sg_id, sg.sg_name FROM escalation_servicegroup_relation esgr, servicegroup sg WHERE esgr.escalation_esc_id = '".$escalation["esc_id"]."' AND sg.sg_id = esgr.servicegroup_sg_id");
		if (PEAR::isError($DBRESULT2))
			print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
		$sg = array();
		$strTemp = NULL;
		while ($DBRESULT2->fetchInto($sg))	{
			$BP = false;
			array_key_exists($sg["sg_id"], $gbArr[5]) ? $BP = true : NULL;
			
			if ($BP)	
				$strTemp != NULL ? $strTemp .= ", ".$sg["sg_name"] : $strTemp = $sg["sg_name"];
		}
		$DBRESULT2->free();			
		if ($strTemp)	{
			$ret["comment"]["comment"] ? ($str .= "# '".$escalation["esc_name"]."' service (group) escalation definition ".$i."\n") : NULL;
			if ($ret["comment"]["comment"] && $escalation["esc_comment"])	{
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
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
			while($DBRESULT2->fetchInto($cg))	{
				$BP = false;				
				array_key_exists($cg["cg_id"], $gbArr[1]) ? $BP = true : $BP = false;
				
				if ($BP)
					$strTemp != NULL ? $strTemp .= ", ".$cg["cg_name"] : $strTemp = $cg["cg_name"];
			}
			$DBRESULT2->free();
			if ($strTemp) $str .= print_line("contact_groups", $strTemp);			
			if ($escalation["first_notification"] != NULL) $str .= print_line("first_notification", $escalation["first_notification"]);
			if ($escalation["last_notification"] != NULL) $str .= print_line("last_notification", $escalation["last_notification"]);
			if ($escalation["notification_interval"] != NULL) $str .= print_line("notification_interval", $escalation["notification_interval"]);
			// Nagios 2
			if ($oreon->user->get_version() == 2)	{
				$DBRESULT2 =& $pearDB->query("SELECT tp_name FROM timeperiod WHERE tp_id = '".$escalation["escalation_period"]."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
				$tp =& $DBRESULT2->fetchRow();				
				if ($tp["tp_name"]) $str .= print_line("escalation_period", $tp["tp_name"]);
				if ($escalation["escalation_options2"]) $str .= print_line("escalation_options", $escalation["escalation_options2"]);
			}
			$str .= "}\n\n";
			$i++;
		}
	}
	unset($escalation);
	$DBRESULT->free();	
	
	$DBRESULT =& $pearDB->query("SELECT DISTINCT service.service_activate, service.service_description, esr.service_service_id FROM service, escalation_service_relation esr WHERE esr.service_service_id = service.service_id ORDER BY service.service_description");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($service))	{
		$BP = false;
		$generated = 0;
		array_key_exists($service["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
		
		if ($BP)	{
			$DBRESULT2 =& $pearDB->query("SELECT * FROM escalation esc, escalation_service_relation esr WHERE esr.service_service_id = '".$service["service_service_id"]."' AND esc.esc_id = esr.escalation_esc_id ORDER BY esc.esc_name");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
			$escalation = array();
			while($DBRESULT2->fetchInto($escalation))	{
				$host = array();
				$BP = false;
				$strDef = "";
				array_key_exists($escalation["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
				
				$service["service_description"] = str_replace('#S#', "/", $service["service_description"]);
				$service["service_description"] = str_replace('#BS#', "\\", $service["service_description"]);
				if ($BP)	{
					$ret["comment"]["comment"] ? ($strDef .= "# '".$escalation["esc_name"]."' service escalation definition ".$i."\n") : NULL;
					if ($ret["comment"]["comment"] && $escalation["esc_comment"])	{
						$comment = array();
						$comment = explode("\n", $escalation["esc_comment"]);
						foreach ($comment as $cmt)
							$strDef .= "# ".$cmt."\n";
					}
					$strDef .= "define serviceescalation{\n";			
					$strDef .= print_line("host_name", getMyHostName($escalation["host_host_id"]));
					if (isHostOnThisInstance($escalation["host_host_id"], $tab['id'])){
						$generated++;
					}
					$strDef .= print_line("service_description", $service["service_description"]);
					$cg = array();
					$strTemp = NULL;
					$DBRESULT3 =& $pearDB->query("SELECT DISTINCT cg.cg_id, cg.cg_name FROM escalation_contactgroup_relation ecgr, contactgroup cg WHERE ecgr.escalation_esc_id = '".$escalation["esc_id"]."' AND ecgr.contactgroup_cg_id = cg.cg_id ORDER BY cg.cg_name");
					if (PEAR::isError($DBRESULT3))
						print "DB Error : ".$DBRESULT3->getDebugInfo()."<br>";
					while($DBRESULT3->fetchInto($cg))	{
						$BP = false;				
						array_key_exists($cg["cg_id"], $gbArr[1]) ? $BP = true : $BP = false;
						
						if ($BP)
							$strTemp != NULL ? $strTemp .= ", ".$cg["cg_name"] : $strTemp = $cg["cg_name"];
					}
					$DBRESULT3->free();
					if ($strTemp) $strDef .= print_line("contact_groups", $strTemp);			
					if ($escalation["first_notification"] != NULL) $strDef .= print_line("first_notification", $escalation["first_notification"]);
					if ($escalation["last_notification"] != NULL) $strDef .= print_line("last_notification", $escalation["last_notification"]);
					if ($escalation["notification_interval"] != NULL) $strDef .= print_line("notification_interval", $escalation["notification_interval"]);
					// Nagios 2
					if ($oreon->user->get_version() == 2)	{
						$DBRESULT4 =& $pearDB->query("SELECT tp_name FROM timeperiod WHERE tp_id = '".$escalation["escalation_period"]."'");
						if (PEAR::isError($DBRESULT4))
							print "DB Error : ".$DBRESULT4->getDebugInfo()."<br>";
						$tp =& $DBRESULT4->fetchRow();
						$DBRESULT4->free();		
						if ($tp["tp_name"]) $strDef .= print_line("escalation_period", $tp["tp_name"]);
						if ($escalation["escalation_options2"]) $strDef .= print_line("escalation_options", $escalation["escalation_options2"]);
					}
					$strDef .= "}\n\n";
					$i++;
				}
			}
			if ($generated){
				$str .= $strDef;
				$strDef = "";
			}
			unset($escalation);
			$DBRESULT2->free();
		}
	}
	unset($service);
	$DBRESULT->free();
	
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/escalations.cfg");
	fclose($handle);
	unset($str);
	unset($i);
?>