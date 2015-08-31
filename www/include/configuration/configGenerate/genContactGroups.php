<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	if (!isset($oreon))
		exit();

	if (!is_dir($nagiosCFGPath.$tab['id']."/")) {
		mkdir($nagiosCFGPath.$tab['id']."/");
	}

	$handle = create_file($nagiosCFGPath.$tab['id']."/contactgroups.cfg", $oreon->user->get_name());
	$DBRESULT = $pearDB->query("SELECT * FROM contactgroup ORDER BY `cg_name`");
	$contactGroup = array();
	$i = 1;
	$str = NULL;
	while ($contactGroup = $DBRESULT->fetchRow())	{

		if (isset($gbArr[1][$contactGroup["cg_id"]]))	{
			$ret["comment"] ? ($str .= "# '" . $contactGroup["cg_name"] . "' contactgroup definition " . $i . "\n") : NULL ;
			if ($ret["comment"] && $contactGroup["cg_comment"])	{
				$comment = array();
				$comment = explode("\n", $contactGroup["cg_comment"]);
				foreach ($comment as $cmt) {
					$str .= "# ".$cmt."\n";
				}
			}

			/*
			 * Start object
			 */
			$str .= "define contactgroup{\n";
			if ($contactGroup["cg_name"])
				$str .= print_line("contactgroup_name", str_replace(" ", "_", $contactGroup["cg_name"]));
			if ($contactGroup["cg_alias"])
				$str .= print_line("alias", $contactGroup["cg_alias"]);

			$contact = array();
			$strTemp = NULL;
			$DBRESULT2 = $pearDB->query("SELECT cct.contact_id, cct.contact_name FROM contactgroup_contact_relation ccr, contact cct WHERE ccr.contactgroup_cg_id = '".$contactGroup["cg_id"]."' AND ccr.contact_contact_id = cct.contact_id AND cct.contact_register = 1 ORDER BY `contact_name`");
			while ($contact = $DBRESULT2->fetchRow())	{
				if (isset($gbArr[0][$contact["contact_id"]])) {
					$strTemp != NULL ? $strTemp .= ", ".$contact["contact_name"] : $strTemp = $contact["contact_name"];
				}
			}
			$DBRESULT2->free();
			if (strlen($strTemp)) {
				$str .= print_line("members", $strTemp);
			}
			unset($contact);
			unset($strTemp);
			$str .= "}\n\n";
			$i++;
		}
		unset($contactGroup);
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/contactgroups.cfg");
	fclose($handle);
	
	setFileMod($nagiosCFGPath.$tab['id']."/contactgroups.cfg");
	
	$DBRESULT->free();
	unset($str);
	unset($i);
?>