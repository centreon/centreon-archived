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

	// Host Extended Information
	$handle = create_file($nagiosCFGPath."hostextinfo.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query("SELECT host.host_name, ehi.* FROM host, extended_host_information ehi WHERE host.host_id = ehi.host_host_id AND host.host_register = '1' ORDER BY `host_name`");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$ehi = array();
	$i = 1;
	$str = NULL;
	while($DBRESULT->fetchInto($ehi))	{
		$BP = false;
		if ($ret["level"]["level"] == 1)
			array_key_exists($ehi["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 2)
			array_key_exists($ehi["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 3)
			$BP = true;
		if ($BP)	{
			$ret["comment"]["comment"] ? ($str .= "# '" . $ehi["host_name"] . "' Host Extended Information definition " . $i . "\n") : NULL ;
			$str .= "define hostextinfo{\n";
			if ($ehi["host_name"]) $str .= print_line("host_name", $ehi["host_name"]);
			if ($oreon->user->get_version() == 2)
				if ($ehi["ehi_notes"]) $str .= print_line("notes", $ehi["ehi_notes"]);
			if ($ehi["ehi_notes_url"]) $str .= print_line("notes_url", $ehi["ehi_notes_url"]);
			if ($oreon->user->get_version() == 2)
				if ($ehi["ehi_action_url"]) $str .= print_line("action_url", $ehi["ehi_action_url"]);
			if ($ehi["ehi_icon_image"]) $str .= print_line("icon_image", $ehi["ehi_icon_image"]);
			if ($ehi["ehi_icon_image_alt"]) $str .= print_line("icon_image_alt", $ehi["ehi_icon_image_alt"]);
			if ($ehi["ehi_vrml_image"]) $str .= print_line("vrml_image", $ehi["ehi_vrml_image"]);
			if ($ehi["ehi_statusmap_image"]) $str .= print_line("statusmap_image", $ehi["ehi_statusmap_image"]);
			if ($ehi["ehi_2d_coords"]) $str .= print_line("2d_coords", $ehi["ehi_2d_coords"]);
			if ($ehi["ehi_3d_coords"]) $str .= print_line("3d_coords", $ehi["ehi_3d_coords"]);
			$str .= "}\n\n";
			$i++;
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath."hostextinfo.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($ehi);
	unset($str);
	unset($i);

	// Service Extended Information
	$handle = create_file($nagiosCFGPath."serviceextinfo.cfg", $oreon->user->get_name());
	$esi = array();
	$i = 1;
	$str = NULL;

	$DBRESULT =& $pearDB->query("SELECT * FROM extended_service_information");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	while($DBRESULT->fetchInto($esi))	{
		$BP = false;
		if ($ret["level"]["level"] == 1)
			array_key_exists($esi["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 2)
			array_key_exists($esi["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
		else if ($ret["level"]["level"] == 3)
			$BP = true;
		if ($BP)	{
			$hosts = getMyServiceHosts($esi["service_service_id"]);
			foreach ($hosts as $key=>$value)	{
				$BP = false;
				if ($ret["level"]["level"] == 1)
					array_key_exists($value, $gbArr[2]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 2)
					array_key_exists($value, $gbArr[2]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 3)
					$BP = true;
				if ($BP && isAHostTpl($value))	{
					$host_name = getMyHostName($value);
					$service_description = getMyServiceName($esi["service_service_id"]);
					$service_description = str_replace('#S#', "/", $service_description);
					$service_description = str_replace('#BS#', "\\", $service_description);
					$str .= "# '" . $host_name . "'/'" . $service_description . "' Service Extended Information definition " . $i . "\n";
					$str .= "define serviceextinfo{\n";
					if ($host_name) $str .= print_line("host_name", $host_name);
					if ($service_description) $str .= print_line("service_description", $service_description);
					if ($oreon->user->get_version() == 2)
						if ($esi["esi_notes"]) $str .= print_line("notes", $esi["esi_notes"]);
					if ($esi["esi_notes_url"]) $str .= print_line("notes_url", $esi["esi_notes_url"]);
					if ($oreon->user->get_version() == 2)
						if ($esi["esi_action_url"]) $str .= print_line("action_url", $esi["esi_action_url"]);
					if ($esi["esi_icon_image"]) $str .= print_line("icon_image", $esi["esi_icon_image"]);
					if ($esi["esi_icon_image_alt"]) $str .= print_line("icon_image_alt", $esi["esi_icon_image_alt"]);;
					$str .= "}\n\n";
					$i++;
				}
			}
			$hgs = getMyServiceHostGroups($esi["service_service_id"]);
			foreach ($hgs as $key=>$value)	{
				$BP = false;
				if ($ret["level"]["level"] == 1)
					array_key_exists($value, $gbArr[3]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 2)
					array_key_exists($value, $gbArr[3]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 3)
					$BP = true;
				if ($BP)	{
					$hostgroup_name = getMyHostGroupName($value);
					$service_description = getMyServiceName($esi["service_service_id"]);
					$service_description = str_replace('#S#', "/", $service_description);
					$service_description = str_replace('#BS#', "\\", $service_description);
					$str .= "# '" . $hostgroup_name . "'/'" . $service_description . "' Service Extended Information definition " . $i . "\n";
					$str .= "define serviceextinfo{\n";
					if ($hostgroup_name) $str .= print_line("hostgroup_name", $hostgroup_name);
					if ($service_description) $str .= print_line("service_description", $service_description);
					if ($oreon->user->get_version() == 2)
						if ($esi["esi_notes"]) $str .= print_line("notes", $esi["esi_notes"]);
					if ($esi["esi_notes_url"]) $str .= print_line("notes_url", $esi["esi_notes_url"]);
					if ($oreon->user->get_version() == 2)
						if ($esi["esi_action_url"]) $str .= print_line("action_url", $esi["esi_action_url"]);
					if ($esi["esi_icon_image"]) $str .= print_line("icon_image", $esi["esi_icon_image"]);
					if ($esi["esi_icon_image_alt"]) $str .= print_line("icon_image_alt", $esi["esi_icon_image_alt"]);;
					$str .= "}\n\n";
					$i++;
				}
			}
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath."serviceextinfo.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($esi);
	unset($str);
	unset($i);
?>