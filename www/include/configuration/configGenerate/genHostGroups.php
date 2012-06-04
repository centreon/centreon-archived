<?php
/*
 * Copyright 2005-2011 MERETHIS
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

	if (!isset($oreon))
		exit();

	if (!is_dir($nagiosCFGPath.$tab['id']."/")) {
		mkdir($nagiosCFGPath.$tab['id']."/");
	}

	/*
	 * Create a GHG Cache
	 */
	$ghgCache = array();
	$DBRESULT = $pearDB->query("SELECT hg_parent_id, hg_child_id, hg_name FROM `hostgroup`, `hostgroup_hg_relation`, hostgroup_relation WHERE hostgroup_hg_relation.hg_child_id = hostgroup.hg_id AND hostgroup_relation.hostgroup_hg_id = hostgroup_hg_relation.hg_child_id");
	while ($ghg = $DBRESULT->fetchRow()) {
		if (!isset($ghgCache[$ghg["hg_parent_id"]])) {
			$ghgCache[$ghg["hg_parent_id"]] = array();
		}
		$ghgCache[$ghg["hg_parent_id"]][$ghg["hg_child_id"]] = $ghg["hg_name"];
	}
	$DBRESULT->free();
	unset($ghg);

	/*
	 * Create table to liste generated hostGroups
	 */
	$generatedHG = array();
	$handle = create_file($nagiosCFGPath.$tab['id']."/hostgroups.cfg", $oreon->user->get_name());

	$i = 1;
	$str = NULL;
	$hostGroup = array();
	$request =  "SELECT DISTINCT hg_id, hg_name, hg_alias, hg_comment, hg_notes, hg_notes_url, hg_action_url " .
				"FROM hostgroup hg, hostgroup_relation hgr, ns_host_relation nhr, host h " .
				"WHERE hg.hg_activate = '1' " .
					"AND hg.hg_id = hgr.hostgroup_hg_id " .
					"AND hgr.host_host_id = h.host_id " .
					"AND nhr.host_host_id = hgr.host_host_id " .
					"AND nhr.nagios_server_id = '".$tab['id']."' " .
					"AND h.host_activate = '1'" .
		    "UNION SELECT DISTINCT hg_id, hg_name, hg_alias, hg_comment, hg_notes, hg_notes_url, hg_action_url " .
				"FROM hostgroup hg, hostgroup_hg_relation hghgr " .
				"WHERE hg.hg_activate = '1' " .
					"AND hghgr.hg_parent_id = hg.hg_id " .
					"AND hghgr.hg_parent_id IS NOT NULL " .
				"ORDER BY `hg_name`";
	$DBRESULT = $pearDB->query($request);
	while ($hostGroup = $DBRESULT->fetchRow())	{
		$strDef = NULL;
		$HGLinkedToHost = 0;

		/*
		 * Generate a new Hostgroup
		 */
		$ret["comment"] ? ($strDef .= "# '" . $hostGroup["hg_name"] . "' hostgroup definition " . $i . "\n") : NULL;
		if ($ret["comment"] && $hostGroup["hg_comment"])	{
			$comment = array();
			$comment = explode("\n", $hostGroup["hg_comment"]);
			foreach ($comment as $cmt)
				$strDef .= "# ".$cmt."\n";
		}
		$strDef .= "define hostgroup{\n";

		if ($hostGroup["hg_name"]) {
			$strDef .= print_line("hostgroup_name", $hostGroup["hg_name"]);
		}
		if ($hostGroup["hg_alias"]) {
			$strDef .= print_line("alias", $hostGroup["hg_alias"]);
		}

		/*
		 * HostGroups Members
		 */
		$strTMP = "";
		if (isset($ghgCache[$hostGroup["hg_id"]])) {
			foreach ($ghgCache[$hostGroup["hg_id"]] as $key => $name) {
				if ($strTMP != "") {
					$strTMP .= ",";
				}
				$strTMP .= $name;
			}
		}
		if ($strTMP != "") {
			$strDef .= print_line("hostgroup_members", $strTMP);
		}
		unset($strTMP);

		if (isset($hostGroup["hg_notes"]) && $hostGroup["hg_notes"]) {
			$strDef .= print_line("notes", $hostGroup["hg_notes"]);
		}
		if (isset($hostGroup["hg_notes_url"]) && $hostGroup["hg_notes_url"]) {
			$strDef .= print_line("notes_url", $hostGroup["hg_notes_url"]);
		}
		if (isset($hostGroup["hg_action_url"]) && $hostGroup["hg_action_url"]) {
			$strDef .= print_line("action_url", $hostGroup["hg_action_url"]);
		}

		/*
		 * Generate only if this hostgroup had a host generate on this nagios instance
		 */
		$generatedHG[$hostGroup["hg_id"]] = $hostGroup["hg_name"];
		$str .= $strDef;
		$str .= "}\n\n";

		$i++;
		unset($hostGroup);
		unset($strDef);
	}

	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/hostgroups.cfg");
	fclose($handle);
	
	setFileMod($nagiosCFGPath.$tab['id']."/hostgroups.cfg");
	
	$DBRESULT->free();
	unset($str);
	unset($i);
?>