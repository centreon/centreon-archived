<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	$handle = create_file($nagiosCFGPath."escalations.cfg", $oreon->user->get_name());
	$res =& $pearDB->query("SELECT DISTINCT esc.* FROM escalation_host_relation ehr, escalation esc WHERE ehr.escalation_esc_id = esc.esc_id ORDER BY esc.esc_name");
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	$escalation = array();
	$i = 1;
	$str = NULL;
	while($res->fetchInto($escalation))	{
		$BP = false;
		$res2 =& $pearDB->query("SELECT DISTINCT host.host_id, host.host_name FROM escalation_host_relation ehr, host WHERE ehr.escalation_esc_id = '".$escalation["esc_id"]."' AND host.host_id = ehr.host_host_id");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$host = array();
		$strTemp = NULL;
		while ($res2->fetchInto($host))	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($host["host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)	
				$strTemp != NULL ? $strTemp .= ", ".$host["host_name"] : $strTemp = $host["host_name"];
		}
		$res2->free();			
		if ($strTemp)	{
			$ret["comment"]["comment"] ? ($str .= "# '".$escalation["esc_name"]."' host escalation definition ".$i."\n") : NULL;
			if ($ret["comment"]["comment"] && $escalation["esc_comment"])	{
				$comment = array();
				$comment = explode("\n", $escalation["esc_comment"]);
				foreach ($comment as $cmt)
					$str .= "# ".$cmt."\n";
			}
			$str .= "define hostescalation{\n";
			$str .= print_line("host_name", $strTemp);			
			$cg = array();
			$strTemp = NULL;
			$res2 =& $pearDB->query("SELECT DISTINCT cg.cg_id, cg.cg_name FROM escalation_contactgroup_relation ecgr, contactgroup cg WHERE ecgr.escalation_esc_id = '".$escalation["esc_id"]."' AND ecgr.contactgroup_cg_id = cg.cg_id ORDER BY cg.cg_name");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			while($res2->fetchInto($cg))	{
				$BP = false;				
				if ($ret["level"]["level"] == 1)
					array_key_exists($cg["cg_id"], $gbArr[1]) ? $BP = true : $BP = false;
				else if ($ret["level"]["level"] == 2)
					array_key_exists($cg["cg_id"], $gbArr[1]) ? $BP = true : $BP = false;
				else if ($ret["level"]["level"] == 3)
					$BP = true;
				if ($BP)
					$strTemp != NULL ? $strTemp .= ", ".$cg["cg_name"] : $strTemp = $cg["cg_name"];
			}
			$res2->free();
			if ($strTemp) $str .= print_line("contact_groups", $strTemp);			
			if ($escalation["first_notification"] != NULL) $str .= print_line("first_notification", $escalation["first_notification"]);
			if ($escalation["last_notification"] != NULL) $str .= print_line("last_notification", $escalation["last_notification"]);
			if ($escalation["notification_interval"]!= NULL) $str .= print_line("notification_interval", $escalation["notification_interval"]);
			// Nagios 2
			if ($oreon->user->get_version() == 2)	{
				$res2 =& $pearDB->query("SELECT tp_name FROM timeperiod WHERE tp_id = '".$escalation["escalation_period"]."'");
				$tp =& $res2->fetchRow();				
				if ($tp["tp_name"]) $str .= print_line("escalation_period", $tp["tp_name"]);
				if ($escalation["escalation_options1"]) $str .= print_line("escalation_options", $escalation["escalation_options1"]);
			}
			$str .= "}\n\n";
			$i++;
		}
	}
	unset($escalation);
	$res->free();
	
	$res =& $pearDB->query("SELECT DISTINCT esc.* FROM escalation_hostgroup_relation ehgr, escalation esc WHERE ehgr.escalation_esc_id = esc.esc_id ORDER BY esc.esc_name");
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	$escalation = array();
	while($res->fetchInto($escalation))	{
		$BP = false;
		$res2 =& $pearDB->query("SELECT DISTINCT hg.hg_id, hg.hg_name FROM escalation_hostgroup_relation ehgr, hostgroup hg WHERE ehgr.escalation_esc_id = '".$escalation["esc_id"]."' AND hg.hg_id = ehgr.hostgroup_hg_id");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		$hg = array();
		$strTemp = NULL;
		while ($res2->fetchInto($hg))	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($hg["hg_id"], $gbArr[3]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($hg["hg_id"], $gbArr[3]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)	
				$strTemp != NULL ? $strTemp .= ", ".$hg["hg_name"] : $strTemp = $hg["hg_name"];
		}
		$res2->free();			
		if ($strTemp)	{
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
			$res2 =& $pearDB->query("SELECT DISTINCT cg.cg_id, cg.cg_name FROM escalation_contactgroup_relation ecgr, contactgroup cg WHERE ecgr.escalation_esc_id = '".$escalation["esc_id"]."' AND ecgr.contactgroup_cg_id = cg.cg_id ORDER BY cg.cg_name");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			while($res2->fetchInto($cg))	{
				$BP = false;				
				if ($ret["level"]["level"] == 1)
					array_key_exists($cg["cg_id"], $gbArr[1]) ? $BP = true : $BP = false;
				else if ($ret["level"]["level"] == 2)
					array_key_exists($cg["cg_id"], $gbArr[1]) ? $BP = true : $BP = false;
				else if ($ret["level"]["level"] == 3)
					$BP = true;
				if ($BP)
					$strTemp != NULL ? $strTemp .= ", ".$cg["cg_name"] : $strTemp = $cg["cg_name"];
			}
			$res2->free();
			if ($strTemp) $str .= print_line("contact_groups", $strTemp);			
			if ($escalation["first_notification"] != NULL) $str .= print_line("first_notification", $escalation["first_notification"]);
			if ($escalation["last_notification"] != NULL) $str .= print_line("last_notification", $escalation["last_notification"]);
			if ($escalation["notification_interval"] != NULL) $str .= print_line("notification_interval", $escalation["notification_interval"]);
			// Nagios 2
			if ($oreon->user->get_version() == 2)	{
				$res2 =& $pearDB->query("SELECT tp_name FROM timeperiod WHERE tp_id = '".$escalation["escalation_period"]."'");
				if (PEAR::isError($pearDB)) {
					print "Mysql Error : ".$pearDB->getMessage();
				}
				$tp =& $res2->fetchRow();				
				if ($tp["tp_name"]) $str .= print_line("escalation_period", $tp["tp_name"]);
				if ($escalation["escalation_options1"]) $str .= print_line("escalation_options", $escalation["escalation_options1"]);
			}
			$str .= "}\n\n";
			$i++;
		}
	}
	unset($escalation);
	$res->free();	
	
	$res =& $pearDB->query("SELECT DISTINCT service.service_activate, service.service_description, esr.service_service_id FROM service, escalation_service_relation esr WHERE esr.service_service_id = service.service_id ORDER BY service.service_description");
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	while($res->fetchInto($service))	{
		$BP = false;
		if ($ret["level"]["level"] == 1)
			array_key_exists($service["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 2)
			array_key_exists($service["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 3)
			$BP = true;
		if ($BP)	{
			$res2 =& $pearDB->query("SELECT * FROM escalation esc, escalation_service_relation esr WHERE esr.service_service_id = '".$service["service_service_id"]."' AND esc.esc_id = esr.escalation_esc_id ORDER BY esc.esc_name");
			if (PEAR::isError($pearDB)) {
				print "Mysql Error : ".$pearDB->getMessage();
			}
			$escalation = array();
			while($res2->fetchInto($escalation))	{
				$host = array();
				$BP = false;
				if ($ret["level"]["level"] == 1)
					array_key_exists($escalation["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 2)
					array_key_exists($escalation["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 3)
					$BP = true;
				if ($BP)	{
					$ret["comment"]["comment"] ? ($str .= "# '".$escalation["esc_name"]."' service escalation definition ".$i."\n") : NULL;
					if ($ret["comment"]["comment"] && $escalation["esc_comment"])	{
						$comment = array();
						$comment = explode("\n", $escalation["esc_comment"]);
						foreach ($comment as $cmt)
							$str .= "# ".$cmt."\n";
					}
					$str .= "define serviceescalation{\n";			
					$str .= print_line("host_name", getMyHostName($escalation["host_host_id"]));
					$str .= print_line("service_description", $service["service_description"]);
					$cg = array();
					$strTemp = NULL;
					$res3 =& $pearDB->query("SELECT DISTINCT cg.cg_id, cg.cg_name FROM escalation_contactgroup_relation ecgr, contactgroup cg WHERE ecgr.escalation_esc_id = '".$escalation["esc_id"]."' AND ecgr.contactgroup_cg_id = cg.cg_id ORDER BY cg.cg_name");
					if (PEAR::isError($pearDB)) {
						print "Mysql Error : ".$pearDB->getMessage();
					}
					while($res3->fetchInto($cg))	{
						$BP = false;				
						if ($ret["level"]["level"] == 1)
							array_key_exists($cg["cg_id"], $gbArr[1]) ? $BP = true : $BP = false;
						else if ($ret["level"]["level"] == 2)
							array_key_exists($cg["cg_id"], $gbArr[1]) ? $BP = true : $BP = false;
						else if ($ret["level"]["level"] == 3)
							$BP = true;
						if ($BP)
							$strTemp != NULL ? $strTemp .= ", ".$cg["cg_name"] : $strTemp = $cg["cg_name"];
					}
					$res3->free();
					if ($strTemp) $str .= print_line("contact_groups", $strTemp);			
					if ($escalation["first_notification"] != NULL) $str .= print_line("first_notification", $escalation["first_notification"]);
					if ($escalation["last_notification"] != NULL) $str .= print_line("last_notification", $escalation["last_notification"]);
					if ($escalation["notification_interval"] != NULL) $str .= print_line("notification_interval", $escalation["notification_interval"]);
					// Nagios 2
					if ($oreon->user->get_version() == 2)	{
						$res4 =& $pearDB->query("SELECT tp_name FROM timeperiod WHERE tp_id = '".$escalation["escalation_period"]."'");
						$tp =& $res4->fetchRow();
						$res4->free();		
						if ($tp["tp_name"]) $str .= print_line("escalation_period", $tp["tp_name"]);
						if ($escalation["escalation_options2"]) $str .= print_line("escalation_options", $escalation["escalation_options2"]);
					}
					$str .= "}\n\n";
					$i++;
				}
			}
			unset($escalation);
			$res2->free();
		}
	}
	unset($service);
	$res->free();
	
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath."escalations.cfg");
	fclose($handle);
	unset($str);
	unset($i);
?>