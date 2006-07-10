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

	$handle = create_file($nagiosCFGPath."hostgroups.cfg", $oreon->user->get_name());
	$res =& $pearDB->query("SELECT * FROM hostgroup ORDER BY `hg_name`");
	$hostGroup = array();
	$i = 1;
	$str = NULL;
	while($res->fetchInto($hostGroup))	{
		$BP = false;
		if ($ret["level"]["level"] == 1)
			array_key_exists($hostGroup["hg_id"], $gbArr[3]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 2)
			array_key_exists($hostGroup["hg_id"], $gbArr[3]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 3)
			$BP = true;
		if ($BP)	{
			$ret["comment"]["comment"] ? ($str .= "# '" . $hostGroup["hg_name"] . "' hostgroup definition " . $i . "\n") : NULL;
			if ($ret["comment"]["comment"] && $hostGroup["hg_comment"])	{
				$comment = array();
				$comment = explode("\n", $hostGroup["hg_comment"]);
				foreach ($comment as $cmt)
					$str .= "# ".$cmt."\n";
			}
			$str .= "define hostgroup{\n";
			if ($hostGroup["hg_name"]) $str .= print_line("hostgroup_name", $hostGroup["hg_name"]);
			if ($hostGroup["hg_alias"]) $str .= print_line("alias", $hostGroup["hg_alias"]);
			// Host members
			$host = array();
			$strTemp = NULL;
			$res2 =& $pearDB->query("SELECT host.host_id, host.host_name FROM hostgroup_relation hgr, host WHERE hgr.hostgroup_hg_id = '".$hostGroup["hg_id"]."' AND hgr.host_host_id = host.host_id ORDER BY `host_name`");
			while($res2->fetchInto($host))	{
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
			unset($host);
			if ($strTemp) $str .= print_line("members", $strTemp);
			unset($strTemp);
			// Nagios V1 : Contactgroups
			if ($oreon->user->get_version() == 1)	{
				$contactGroup = array();
				$strTemp = NULL;
				$res2 =& $pearDB->query("SELECT cg.cg_name, cg.cg_id FROM contactgroup_hostgroup_relation cghgr, contactgroup cg WHERE cghgr.hostgroup_hg_id = '".$hostGroup["hg_id"]."' AND cghgr.contactgroup_cg_id = cg.cg_id ORDER BY `cg_name`");
				while($res2->fetchInto($contactGroup))	{
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
				$res2->free();
				unset($contactGroup);
				if ($strTemp) $str .= print_line("contact_groups", $strTemp);
				unset($strTemp);
			}
			$str .= "}\n\n";
			$i++;
		}
		unset($hostGroup);
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath."hostgroups.cfg");
	fclose($handle);
	$res->free();
	unset($str);
	unset($i);
	?>