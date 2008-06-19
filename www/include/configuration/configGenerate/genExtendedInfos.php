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

	if (!isset($oreon))
		exit();

	if (!is_dir($nagiosCFGPath.$tab['id']."/")) {
		mkdir($nagiosCFGPath.$tab['id']."/");
	}
	
	/*
	 * Host Extended Information
	 */
	$handle = create_file($nagiosCFGPath.$tab['id']."/hostextinfo.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1' ORDER BY `host_name`");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$ehi = array();
	$i = 1;
	$str = NULL;
	while ($ehi =& $DBRESULT->fetchRow())	{
		if (isHostOnThisInstance(getMyHostID($ehi["host_name"]), $tab['id'])) {
			if (isset($ehi["host_id"][$gbArr[2]])) {
				$ret["comment"] ? ($str .= "# '" . $ehi["host_name"] . "' Host Extended Information definition " . $i . "\n") : NULL ;
				$str .= "define hostextinfo{\n";
				if ($ehi["host_name"])
					$str .= print_line("host_name", $ehi["host_name"]);
				if ($field = getMyHostExtendedInfoField($ehi["host_id"], "ehi_notes"))
					$str .= print_line("notes", $field);
				if ($field = getMyHostExtendedInfoField($ehi["host_id"], "ehi_notes_url"))
					$str .= print_line("notes_url", $field);
				if ($field = getMyHostExtendedInfoField($ehi["host_id"], "ehi_action_url"))
					$str .= print_line("action_url", $field);
				if ($field = getMyHostExtendedInfoField($ehi["host_id"], "ehi_icon_image"))
					$str .= print_line("icon_image", $field);
				if ($field = getMyHostExtendedInfoField($ehi["host_id"], "ehi_icon_image_alt"))
					$str .= print_line("icon_image_alt", $field);
				if ($field = getMyHostExtendedInfoField($ehi["host_id"], "ehi_vrml_image"))
					$str .= print_line("vrml_image", $field);
				if ($field = getMyHostExtendedInfoField($ehi["host_id"], "ehi_statusmap_image"))
					$str .= print_line("statusmap_image", $field);
				if ($field = getMyHostExtendedInfoField($ehi["host_id"], "ehi_2d_coords"))
					$str .= print_line("2d_coords", $field);
				if ($field = getMyHostExtendedInfoField($ehi["host_id"], "ehi_3d_coords"))
					$str .= print_line("3d_coords", $field);
				$str .= "}\n\n";
				$i++;
			}
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/hostextinfo.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($ehi);
	unset($str);
	unset($i);

	/*
	 * Service Extended Information
	 */
	$handle = create_file($nagiosCFGPath.$tab['id']."/serviceextinfo.cfg", $oreon->user->get_name());
	$esi = array();
	$i = 1;
	$str = NULL;

	$DBRESULT =& $pearDB->query("SELECT service_id FROM service WHERE service_register = '1'");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	while ($esi =& $DBRESULT->fetchRow())	{
		if (isset($esi["service_id"][$gbArr[4]]))	{
			$hosts = getMyServiceHosts($esi["service_id"]);
			foreach ($hosts as $key=>$value)	{
				$BP = false;
				array_key_exists($value, $gbArr[2]) ? $BP = true : NULL;				
				if ($BP && isAHostTpl($value))	{
					$host_name = getMyHostName($value);
					if (isHostOnThisInstance(getMyHostID($host_name), $tab['id'])) {
						$service_description = getMyServiceName($esi["service_id"]);
						$service_description = str_replace('#S#', "/", $service_description);
						$service_description = str_replace('#BS#', "\\", $service_description);
						$str .= "# '" . $host_name . "'/'" . $service_description . "' Service Extended Information definition " . $i . "\n";
						$str .= "define serviceextinfo{\n";
						if ($host_name)
							$str .= print_line("host_name", $host_name);
						if ($service_description)
							$str .= print_line("service_description", $service_description);									
						if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_notes"))
							$str .= print_line("notes", $field);
						if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_notes_url"))
							$str .= print_line("notes_url", $field);
						if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_action_url"))
							$str .= print_line("action_url", $field);
						if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_icon_image"))
							$str .= print_line("icon_image", $field);
						if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_icon_image_alt"))
							$str .= print_line("icon_image_alt", $field);
						$str .= "}\n\n";
						$i++;
					}
				}
			}
			$hgs = getMyServiceHostGroups($esi["service_id"]);
			foreach ($hgs as $key=>$value)	{
				if (isset($value[$gbArr[3]]))	{
					$hostgroup_name = getMyHostGroupName($value);
					$service_description = getMyServiceName($esi["service_id"]);
					$service_description = str_replace('#S#', "/", $service_description);
					$service_description = str_replace('#BS#', "\\", $service_description);
					$str .= "# '" . $hostgroup_name . "'/'" . $service_description . "' Service Extended Information definition " . $i . "\n";
					$str .= "define serviceextinfo{\n";
					if ($hostgroup_name)
						$str .= print_line("hostgroup_name", $hostgroup_name);
					if ($service_description)
						$str .= print_line("service_description", $service_description);									
					if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_notes"))
						$str .= print_line("notes", $field);
					if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_notes_url"))
						$str .= print_line("notes_url", $field);
					if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_action_url"))
						$str .= print_line("action_url", $field);
					if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_icon_image"))
						$str .= print_line("icon_image", $field);
					if ($field = getMyServiceExtendedInfoField($esi["service_id"], "esi_icon_image_alt"))
						$str .= print_line("icon_image_alt", $field);
					$str .= "}\n\n";
					$i++;
				}
			}
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/serviceextinfo.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($esi);
	unset($str);
	unset($i);
?>