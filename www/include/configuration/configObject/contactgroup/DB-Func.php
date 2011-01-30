<?php
/*
 * Copyright 2005-2010 MERETHIS
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

	if (!isset ($oreon))
		exit ();

	function testContactGroupExistence ($name = NULL)	{
		global $pearDB, $form, $oreon;
		$id = NULL;

		if (isset($form))
			$id = $form->getSubmitValue('cg_id');

		$DBRESULT = $pearDB->query("SELECT `cg_name`, `cg_id` FROM `contactgroup` WHERE `cg_name` = '".htmlentities($oreon->checkIllegalChar($name), ENT_QUOTES, "UTF-8")."'");
		$cg = $DBRESULT->fetchRow();

		if ($DBRESULT->numRows() >= 1 && $cg["cg_id"] == $id){
			/*
			 * Modif case
			 */
			return true;
		} else if ($DBRESULT->numRows() >= 1 && $cg["cg_id"] != $id){
			/*
			 * Duplicate entry
			 */
			return false;
		} else {
			return true;
		}
	}

	function enableContactGroupInDB ($cg_id = null)	{
		global $pearDB, $oreon;
		if (!$cg_id)
			return;
		$DBRESULT = $pearDB->query("UPDATE `contactgroup` SET `cg_activate` = '1' WHERE `cg_id` = '".$cg_id."'");
		$DBRESULT2 = $pearDB->query("SELECT cg_name FROM `contactgroup` WHERE `cg_id` = '".$cg_id."' LIMIT 1");
		$row = $DBRESULT2->fetchRow();
		$oreon->CentreonLogAction->insertLog("contactgroup", $cg_id, $row['cg_name'], "enable");
	}

	function disableContactGroupInDB ($cg_id = null)	{
		global $pearDB, $oreon;
		if (!$cg_id)
			return;
		$DBRESULT = $pearDB->query("UPDATE `contactgroup` SET `cg_activate` = '0' WHERE `cg_id` = '".$cg_id."'");
		$DBRESULT2 = $pearDB->query("SELECT cg_name FROM `contactgroup` WHERE `cg_id` = '".$cg_id."' LIMIT 1");
		$row = $DBRESULT2->fetchRow();
		$oreon->CentreonLogAction->insertLog("contactgroup", $cg_id, $row['cg_name'], "disable");
	}

	function deleteContactGroupInDB ($contactGroups = array())	{
		global $pearDB, $oreon;

		foreach($contactGroups as $key => $value)	{
			$DBRESULT2 = $pearDB->query("SELECT cg_name FROM `contactgroup` WHERE `cg_id` = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();

			$DBRESULT = $pearDB->query("DELETE FROM `contactgroup` WHERE `cg_id` = '".$key."'");
			$oreon->CentreonLogAction->insertLog("contactgroup", $key, $row['cg_name'], "d");
		}
	}

	function multipleContactGroupInDB ($contactGroups = array(), $nbrDup = array())	{
		global $pearDB, $oreon;

		foreach ($contactGroups as $key=>$value)	{

			$DBRESULT = $pearDB->query("SELECT * FROM `contactgroup` WHERE `cg_id` = '".$key."' LIMIT 1");

			$row = $DBRESULT->fetchRow();
			$row["cg_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2 => $value2) {
					$key2 == "cg_name" ? ($cg_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ", '".$value2."'" : $val .= "'".$value2."'";
					if ($key2 != "cg_id") {
						$fields[$key2] = $value2;
					}
					if (isset($cg_name)) {
					    $fields["cg_name"] = $cg_name;
					}
				}
				if (isset($cg_name) && testContactGroupExistence($cg_name)) {
					$val ? $rq = "INSERT INTO `contactgroup` VALUES (".$val.")" : $rq = null;
					$DBRESULT = $pearDB->query($rq);

					$DBRESULT = $pearDB->query("SELECT MAX(cg_id) FROM `contactgroup`");
					$maxId = $DBRESULT->fetchRow();

					if (isset($maxId["MAX(cg_id)"])) {
						$DBRESULT = $pearDB->query("SELECT DISTINCT `cgcr`.`contact_contact_id` FROM `contactgroup_contact_relation` `cgcr` WHERE `cgcr`.`contactgroup_cg_id` = '".$key."'");
						$fields["cg_contacts"] = "";
						while($cct = $DBRESULT->fetchRow())	{
							$DBRESULT2 = $pearDB->query("INSERT INTO `contactgroup_contact_relation` VALUES ('', '".$cct["contact_contact_id"]."', '".$maxId["MAX(cg_id)"]."')");
							$fields["cg_contacts"] .= $cct["contact_contact_id"] . ",";
						}
						$fields["cg_contacts"] = trim($fields["cg_contacts"], ",");
						$oreon->CentreonLogAction->insertLog("contactgroup", $maxId["MAX(cg_id)"], $cg_name, "a", $fields);
					}
				}
			}
		}
	}

	function insertContactGroupInDB ($ret = array())	{
		$cg_id = insertContactGroup($ret);
		updateContactGroupContacts($cg_id, $ret);
		return $cg_id;
	}

	function insertContactGroup($ret)	{
		global $form, $pearDB, $oreon;

		if (!count($ret))
			$ret = $form->getSubmitValues();

		$ret["cg_name"] = $oreon->checkIllegalChar($ret["cg_name"]);

		$rq = "INSERT INTO `contactgroup` (`cg_name`, `cg_alias`, `cg_comment`, `cg_activate`) ";
		$rq .= "VALUES ('".htmlentities($ret["cg_name"], ENT_QUOTES, "UTF-8")."', '".htmlentities($ret["cg_alias"], ENT_QUOTES, "UTF-8")."', '".htmlentities($ret["cg_comment"], ENT_QUOTES, "UTF-8")."', '".$ret["cg_activate"]["cg_activate"]."')";
		$DBRESULT = $pearDB->query($rq);

		$DBRESULT = $pearDB->query("SELECT MAX(cg_id) FROM `contactgroup`");
		$cg_id = $DBRESULT->fetchRow();
		$fields["cg_name"] = htmlentities($ret["cg_name"], ENT_QUOTES, "UTF-8");
		$fields["cg_alias"] = htmlentities($ret["cg_alias"], ENT_QUOTES, "UTF-8");
		$fields["cg_comment"] = htmlentities($ret["cg_comment"], ENT_QUOTES, "UTF-8");
		$fields["cg_activate"] = $ret["cg_activate"]["cg_activate"];
		if (isset($ret["cg_contacts"]))
			$fields["cg_contacts"] = implode(",", $ret["cg_contacts"]);
		$oreon->CentreonLogAction->insertLog("contactgroup", $cg_id["MAX(cg_id)"], htmlentities($ret["cg_name"], ENT_QUOTES, "UTF-8"), "a", $fields);
		return ($cg_id["MAX(cg_id)"]);
	}

	function updateContactGroupInDB ($cg_id = NULL)	{
		if (!$cg_id)
			return;
		updateContactGroup($cg_id);
		updateContactGroupContacts($cg_id);
	}

	function updateContactGroup($cg_id = null)	{
		global $form, $pearDB, $oreon;
		if (!$cg_id)
			return;
		$ret = array();
		$ret = $form->getSubmitValues();

		$ret["cg_name"] = $oreon->checkIllegalChar($ret["cg_name"]);

		$rq = "UPDATE `contactgroup` ";
		$rq .= "SET `cg_name` = '".htmlentities($ret["cg_name"], ENT_QUOTES, "UTF-8")."', " .
				"`cg_alias` = '".htmlentities($ret["cg_alias"], ENT_QUOTES, "UTF-8")."', " .
				"`cg_comment` = '".htmlentities($ret["cg_comment"], ENT_QUOTES, "UTF-8")."', " .
				"`cg_activate` = '".$ret["cg_activate"]["cg_activate"]."' " .
				"WHERE `cg_id` = '".$cg_id."'";
		$DBRESULT = $pearDB->query($rq);
		$fields["cg_name"] = htmlentities($ret["cg_name"], ENT_QUOTES, "UTF-8");
		$fields["cg_alias"] = htmlentities($ret["cg_alias"], ENT_QUOTES, "UTF-8");
		$fields["cg_comment"] = htmlentities($ret["cg_comment"], ENT_QUOTES, "UTF-8");
		$fields["cg_activate"] = $ret["cg_activate"]["cg_activate"];
		if (isset($ret["cg_contacts"]))
			$fields["cg_contacts"] = implode(",", $ret["cg_contacts"]);
		$oreon->CentreonLogAction->insertLog("contactgroup", $cg_id, htmlentities($ret["cg_name"], ENT_QUOTES, "UTF-8"), "c", $fields);
	}

	function updateContactGroupContacts($cg_id, $ret = array())	{
		global $form, $pearDB;
		if (!$cg_id)
			return;

		$rq = "DELETE FROM `contactgroup_contact_relation` WHERE `contactgroup_cg_id` = '".$cg_id."'";
		$DBRESULT = $pearDB->query($rq);
		if (isset($ret["cg_contacts"]))
			$ret = $ret["cg_contacts"];
		else
			$ret = $form->getSubmitValue("cg_contacts");

		for ($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO `contactgroup_contact_relation` (`contact_contact_id`, `contactgroup_cg_id`) ";
			$rq .= "VALUES ('".$ret[$i]."', '".$cg_id."')";
			$DBRESULT = $pearDB->query($rq);
		}
	}
?>