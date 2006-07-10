<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called « Oreon Meta Service » is developped by Merethis company for Lafarge Group, 
under the direction of Jean Baptiste Sarrodie <jean-baptiste@sarrodie.org>

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

	$str = NULL;
	$i = 1;
	$handle = create_file($nagiosCFGPath."meta_escalations.cfg", $oreon->user->get_name());

	$res =& $pearDB->query("SELECT DISTINCT meta_service_meta_id FROM escalation_meta_service_relation");
	while($res->fetchInto($service))	{
		$BP = false;
		if ($ret["level"]["level"] == 1)
			array_key_exists($service["meta_service_meta_id"], $gbArr[7]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 2)
			array_key_exists($service["meta_service_meta_id"], $gbArr[7]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 3)
			$BP = true;
		if ($BP)	{
			$res2 =& $pearDB->query("SELECT esc.* FROM escalation esc, escalation_meta_service_relation emsr WHERE emsr.meta_service_meta_id = '".$service["meta_service_meta_id"]."' AND esc.esc_id = emsr.escalation_esc_id ORDER BY esc.esc_name");
			$escalation = array();
			while($res2->fetchInto($escalation))	{
				$ret["comment"]["comment"] ? ($str .= "# '".$escalation["esc_name"]."' service escalation definition ".$i."\n") : NULL;
				if ($ret["comment"]["comment"] && $escalation["esc_comment"])	{
					$comment = array();
					$comment = explode("\n", $escalation["esc_comment"]);
					foreach ($comment as $cmt)
						$str .= "# ".$cmt."\n";
				}
				$str .= "define serviceescalation{\n";
				$str .= print_line("host_name", "Meta_Module");
				$str .= print_line("service_description", "meta_".$service["meta_service_meta_id"]);
				$cg = array();
				$strTemp = NULL;
				$res3 =& $pearDB->query("SELECT DISTINCT cg.cg_id, cg.cg_name FROM escalation_contactgroup_relation ecgr, contactgroup cg WHERE ecgr.escalation_esc_id = '".$escalation["esc_id"]."' AND ecgr.contactgroup_cg_id = cg.cg_id ORDER BY cg.cg_name");
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
			unset($escalation);
			$res2->free();
		}
	}
	unset($service);
	$res->free();
	write_in_file($handle, $str, $nagiosCFGPath."meta_escalations.cfg");
	fclose($handle);
	unset($str);
?>