<?php
/*
 * Copyright 2005-2009 MERETHIS
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
	$DBRESULT =& $pearDB->query("SELECT hg_parent_id, hg_child_id, hg_name FROM `hostgroup`, `hostgroup_hg_relation` WHERE hostgroup_hg_relation.hg_child_id = hostgroup.hg_id");
	while ($ghg =& $DBRESULT->fetchRow()) {
		if (!isset($ghgCache[$ghg["hg_parent_id"]]))
			$ghgCache[$ghg["hg_parent_id"]] = array();
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
	$DBRESULT =& $pearDB->query("SELECT * FROM hostgroup WHERE hg_activate = '1' ORDER BY `hg_name`");
	while ($hostGroup =& $DBRESULT->fetchRow())	{
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
		
		if ($hostGroup["hg_name"])	
			$strDef .= print_line("hostgroup_name", $hostGroup["hg_name"]);
		if ($hostGroup["hg_alias"]) 
			$strDef .= print_line("alias", $hostGroup["hg_alias"]);
		
		/*
		 * Hostgroup Members
		 */
		
		$strTemp = "";
		if (isset($ghgCache[$hostGroup["hg_id"]])) {
			foreach ($ghgCache[$hostGroup["hg_id"]] as $child_name) {
				if ($strTemp != "")
					$strTemp .= ",";
				$strTemp .= $child_name;
				$HGLinkedToHost++;
			}
			if ($strTemp) 
				$strDef .= print_line("hostgroup_members", $strTemp);
		}
		
		if ($hostGroup["hg_notes"])	
			$strDef .= print_line("notes", $hostGroup["hg_notes"]);
		if ($hostGroup["hg_notes_url"])	
			$strDef .= print_line("notes_url", $hostGroup["hg_notes_url"]);
		if ($hostGroup["hg_action_url"])	
			$strDef .= print_line("action_url", $hostGroup["hg_action_url"]);
		
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

	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/hostgroups.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
	unset($i);
?>