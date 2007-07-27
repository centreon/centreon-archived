<?
/**
Oreon is developped with GPL Licence 2.0 :
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

	$handle = create_file($nagiosCFGPath."contacts.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query("SELECT * FROM contact ORDER BY `contact_name`");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$contact = array();
	$i = 1;
	$str = NULL;
	while ($DBRESULT->fetchInto($contact))	{
		$BP = false;
		if ($ret["level"]["level"] == 1)
			array_key_exists($contact["contact_id"], $gbArr[0]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 2)
			array_key_exists($contact["contact_id"], $gbArr[0]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 3)
			$BP = true;
		if ($BP)	{
			$ret["comment"]["comment"] ? ($str .= "# '".$contact["contact_name"]."' contact definition ".$i."\n") : NULL;
			if ($ret["comment"]["comment"] && $contact["contact_comment"])	{
				$comment = array();
				$comment = explode("\n", $contact["contact_comment"]);
				foreach ($comment as $cmt)
					$str .= "# ".$cmt."\n";
			}
			$str .= "define contact{\n";
			if ($contact["contact_name"]) $str .= print_line("contact_name", $contact["contact_name"]);
			if ($contact["contact_alias"]) $str .= print_line("alias", $contact["contact_alias"]);
			// Nagios 2 : Contact Groups in Contact
			if ($oreon->user->get_version() == 2)	{
				$contactGroup = array();
				$strTemp = NULL;
				$DBRESULT2 =& $pearDB->query("SELECT cg.cg_name, cg.cg_id FROM contactgroup_contact_relation ccr, contactgroup cg WHERE ccr.contact_contact_id = '".$contact["contact_id"]."' AND ccr.contactgroup_cg_id = cg.cg_id ORDER BY `cg_name`");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
				while($DBRESULT2->fetchInto($contactGroup))	{
					$BP = false;
					if ($ret["level"]["level"] == 1)
						array_key_exists($contactGroup["cg_id"], $gbArr[1]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 2)
						array_key_exists($contactGroup["cg_id"], $gbArr[1]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 3)
						$BP = true;
					if ($BP)
						$strTemp != NULL ? $strTemp .= ", ".$contactGroup["cg_name"] : $strTemp = $contactGroup["cg_name"];
				}
				$DBRESULT2->free();
				if ($strTemp) $str .= print_line("contactgroups", $strTemp);
				unset($contactGroup);
				unset($strTemp);
			}
			// Timeperiod for host & service
			$timeperiod = array();
			$DBRESULT2 =& $pearDB->query("SELECT cct.timeperiod_tp_id AS cctTP1, cct.timeperiod_tp_id2 AS cctTP2, tp.tp_id, tp.tp_name FROM contact cct, timeperiod tp WHERE cct.contact_id = '".$contact["contact_id"]."' AND (tp.tp_id = cct.timeperiod_tp_id OR tp.tp_id = cct.timeperiod_tp_id2) ORDER BY `cctTP1`");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
			while($DBRESULT2->fetchInto($timeperiod))	{
				$timeperiod["cctTP1"] == $timeperiod["tp_id"] ? $str .= print_line("host_notification_period", $timeperiod["tp_name"]) : NULL;
				$timeperiod["cctTP2"] == $timeperiod["tp_id"] ? $str .= print_line("service_notification_period", $timeperiod["tp_name"]) : NULL;
			}
			$DBRESULT2->free();
			unset($timeperiod);
			if ($contact["contact_host_notification_options"]) $str .= print_line("host_notification_options", $contact["contact_host_notification_options"]);
			if ($contact["contact_service_notification_options"]) $str .= print_line("service_notification_options", $contact["contact_service_notification_options"]);
			// Host & Service notification command
			$command = array();
			$strTemp = NULL;
			$DBRESULT2 =& $pearDB->query("SELECT cmd.command_name FROM contact_hostcommands_relation chr, command cmd WHERE chr.contact_contact_id = '".$contact["contact_id"]."' AND chr.command_command_id = cmd.command_id ORDER BY `command_name`");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
			while($DBRESULT2->fetchInto($command))
				$strTemp != NULL ? $strTemp .= ", ".$command["command_name"] : $strTemp = $command["command_name"];
			$DBRESULT2->free();
			if ($strTemp) $str .= print_line("host_notification_commands", $strTemp);
			unset($command);
			unset($strTemp);
			$command = array();
			$strTemp = NULL;
			$DBRESULT2 =& $pearDB->query("SELECT cmd.command_name FROM contact_servicecommands_relation csr, command cmd WHERE csr.contact_contact_id = '".$contact["contact_id"]."' AND csr.command_command_id = cmd.command_id ORDER BY `command_name`");
			if (PEAR::isError($DBRESULT2))
				print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
			while($DBRESULT2->fetchInto($command))
				$strTemp != NULL ? $strTemp .= ", ".$command["command_name"] : $strTemp = $command["command_name"];
			$DBRESULT2->free();
			if ($strTemp) $str .= print_line("service_notification_commands", $strTemp);
			unset($command);
			unset($strTemp);
			// Misc
			if ($contact["contact_email"]) $str .= print_line("email", $contact["contact_email"]);
			if ($contact["contact_pager"]) $str .= print_line("pager", $contact["contact_pager"]);
			$str .= "}\n\n";
			$i++;
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath."contacts.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($contact);
	unset($str);
	unset($i);
?>