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
		if ($hostGroup["hg_icon_image"])	
			$strDef .= print_line("icon_image", getImageFilePath($hostGroup["hg_icon_image"]));
		
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