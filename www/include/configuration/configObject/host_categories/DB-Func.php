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

	if (!isset ($oreon))
		exit ();

	function testHostCategorieExistence ($name = NULL)	{
		global $pearDB, $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('hc_id');
		$DBRESULT = $pearDB->query("SELECT hc_name, hc_id FROM hostcategories WHERE hc_name = '".CentreonDB::escape($name)."'");
		$hc = $DBRESULT->fetchRow();
		# Modif case
		if ($DBRESULT->numRows() >= 1 && $hc["hc_id"] == $id)
			return true;
		# Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $hc["hc_id"] != $id)
			return false;
		else
			return true;
	}

	function enableHostCategoriesInDB ($hc_id = NULL, $hc_arr = array())	{
		global $pearDB, $oreon;

		if (!$hc_id && !count($hc_arr))
			return;

		if ($hc_id)
			$hc_arr = array($hc_id=>"1");

		foreach($hc_arr as $key=>$value)	{
			$DBRESULT = $pearDB->query("UPDATE hostcategories SET hc_activate = '1' WHERE hc_id = '".$key."'");
			$DBRESULT2 = $pearDB->query("SELECT hc_name FROM `hostcategories` WHERE `hc_id` = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();
			$oreon->CentreonLogAction->insertLog("hostcategories", $key, $row['hc_name'], "enable");
		}
	}

	function disableHostCategoriesInDB ($hc_id = NULL, $hc_arr = array())	{
		if (!$hc_id && !count($hc_arr)) return;
		global $pearDB, $oreon;
		if ($hc_id)
			$hc_arr = array($hc_id=>"1");
		foreach($hc_arr as $key=>$value)	{
			$DBRESULT = $pearDB->query("UPDATE hostcategories SET hc_activate = '0' WHERE hc_id = '".$key."'");
			$DBRESULT2 = $pearDB->query("SELECT hc_name FROM `hostcategories` WHERE `hc_id` = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();
			$oreon->CentreonLogAction->insertLog("hostcategories", $key, $row['hc_name'], "disable");
		}
	}

	function deleteHostCategoriesInDB ($hostcategoriess = array())	{
		global $pearDB, $centreon;

		foreach ($hostcategoriess as $key=>$value)	{
			$DBRESULT3 = $pearDB->query("SELECT hc_name FROM `hostcategories` WHERE `hc_id` = '".$key."' LIMIT 1");
			$row = $DBRESULT3->fetchRow();
			$DBRESULT = $pearDB->query("DELETE FROM hostcategories WHERE hc_id = '".$key."'");
			$centreon->CentreonLogAction->insertLog("hostcategories", $key, $row['hc_name'], "d");
		}
		$centreon->user->access->updateACL();
	}

	function multipleHostCategoriesInDB ($hostcategories = array(), $nbrDup = array())	{
		global $pearDB, $oreon, $is_admin;

		foreach ($hostcategories as $key => $value)	{
			$DBRESULT = $pearDB->query("SELECT * FROM hostcategories WHERE hc_id = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["hc_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = NULL;
				$rq = NULL;
				foreach ($row as $key2 => $value2)	{
					(isset($key2) && $key2 == "hc_name") ? ($hc_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
					if ($key2 != "hc_id")
						$fields[$key2] = $value2;
				}
				$fields["hc_name"] = $hc_name;
				if (testHostCategorieExistence($hc_name))	{
					$val ? $rq = "INSERT INTO hostcategories VALUES (".$val.")" : $rq = null;
					$DBRESULT = $pearDB->query($rq);
					$DBRESULT = $pearDB->query("SELECT MAX(hc_id) FROM hostcategories");
					$maxId = $DBRESULT->fetchRow();
					if (isset($maxId["MAX(hc_id)"]))	{

						$DBRESULT = $pearDB->query("SELECT DISTINCT hgr.host_host_id FROM hostcategories_relation hgr WHERE hgr.hostcategories_hc_id = '".$key."'");
						$fields["hc_hosts"] = "";
						while($host = $DBRESULT->fetchRow()){
							$DBRESULT2 = $pearDB->query("INSERT INTO hostcategories_relation VALUES ('', '".$maxId["MAX(hc_id)"]."', '".$host["host_host_id"]."')");
							$fields["hc_hosts"] .= $host["host_host_id"] . ",";
						}
						$fields["hc_hosts"] = trim($fields["hc_hosts"], ",");
						$oreon->CentreonLogAction->insertLog("hostcategories", $maxId["MAX(hc_id)"], $hc_name, "a", $fields);
					}
				}
			}
		}
	}

	function insertHostCategoriesInDB ($ret = array())	{
		global $oreon;

		$hc_id = insertHostCategories($ret);
		updateHostCategoriesHosts($hc_id, $ret);
		$oreon->user->access->updateACL();
		return $hc_id;
	}

	function updateHostCategoriesInDB ($hc_id = NULL)	{
		global $oreon;
		if (!$hc_id)
			return;
		updateHostCategories($hc_id);
		updateHostCategoriesHosts($hc_id);
		$oreon->user->access->updateACL();
	}

	function insertHostCategories($ret = array())	{
		global $form, $pearDB, $oreon, $is_admin;

		if (!count($ret))
			$ret = $form->getSubmitValues();

		$rq = "INSERT INTO hostcategories ";
		$rq .= "(hc_name, hc_alias, hc_comment, hc_activate) ";
		$rq .= "VALUES (";
		isset($ret["hc_name"]) && $ret["hc_name"] ? $rq .= "'".CentreonDB::escape($ret["hc_name"])."', " : $rq .= "NULL,";
		isset($ret["hc_alias"]) && $ret["hc_alias"] ? $rq .= "'".CentreonDB::escape($ret["hc_alias"])."', " : $rq .= "NULL,";
		isset($ret["hc_comment"]) && $ret["hc_comment"] ? $rq .= "'".CentreonDB::escape($ret["hc_comment"])."', " : $rq .= "NULL, ";
		isset($ret["hc_activate"]["hc_activate"]) && $ret["hc_activate"]["hc_activate"] ? $rq .= "'".$ret["hc_activate"]["hc_activate"]."'" : $rq .= "'0'";
		$rq .= ")";

		$pearDB->query($rq);
		$DBRESULT = $pearDB->query("SELECT MAX(hc_id) FROM hostcategories");
		$hc_id = $DBRESULT->fetchRow();

		$fields["hc_name"] = CentreonDB::escape($ret["hc_name"]);
		$fields["hc_alias"] = CentreonDB::escape($ret["hc_alias"]);
		$fields["hc_comment"] = CentreonDB::escape($ret["hc_comment"]);
		$fields["hc_activate"] = $ret["hc_activate"]["hc_activate"];
		if (isset($ret["hc_hosts"]))
			$fields["hc_hosts"] = implode(",", $ret["hc_hosts"]);
		if (isset($ret["hc_hg"]))
			$fields["hc_hg"] = implode(",", $ret["hc_hg"]);

		$oreon->CentreonLogAction->insertLog("hostcategories", $hc_id["MAX(hc_id)"], CentreonDB::escape($ret["hc_name"]), "a", $fields);
		return ($hc_id["MAX(hc_id)"]);
	}

	function updateHostCategories($hc_id)	{
		if (!$hc_id) return;
		global $form, $pearDB, $oreon;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE hostcategories SET ";
		$rq .= "hc_name = ";
		isset($ret["hc_name"]) && $ret["hc_name"] != NULL ? $rq .= "'".CentreonDB::escape($ret["hc_name"])."', " : $rq .= "NULL, ";
		$rq .= "hc_alias = ";
		isset($ret["hc_alias"]) && $ret["hc_alias"] != NULL ? $rq .= "'".CentreonDB::escape($ret["hc_alias"])."', " : $rq .= "NULL, ";
		$rq .= "hc_comment = ";
		isset($ret["hc_comment"]) && $ret["hc_comment"] != NULL ? $rq .= "'".CentreonDB::escape($ret["hc_comment"])."', " : $rq .= "NULL, ";
		$rq .= "hc_activate = ";
		isset($ret["hc_activate"]["hc_activate"]) && $ret["hc_activate"]["hc_activate"] != NULL ? $rq .= "'".$ret["hc_activate"]["hc_activate"]."'" : $rq .= "NULL ";
		$rq .= "WHERE hc_id = '".$hc_id."'";
		$DBRESULT = $pearDB->query($rq);

		$fields["hc_name"] = CentreonDB::escape($ret["hc_name"]);
		$fields["hc_alias"] = CentreonDB::escape($ret["hc_alias"]);
		$fields["hc_comment"] = CentreonDB::escape($ret["hc_comment"]);
		$fields["hc_activate"] = $ret["hc_activate"]["hc_activate"];

		if (isset( $ret["hc_hosts"]))
			$fields["hc_hosts"] = implode(",", $ret["hc_hosts"]);
		if (isset( $ret["hc_hostsTemplate"]))
			$fields["hc_hostsTemplate"] = implode(",", $ret["hc_hostsTemplate"]);

		$oreon->CentreonLogAction->insertLog("hostcategories", $hc_id, CentreonDB::escape($ret["hc_name"]), "c", $fields);
	}

	function updateHostCategoriesHosts($hc_id, $ret = array())	{
		global $form, $pearDB;

		if (!$hc_id)
			return;

		/*
		 * Special Case, delete relation between host/service, when service
		 * is linked to hostcategories in escalation, dependencies, osl
		 *
		 * Get initial Host list to make a diff after deletion
		 */
		$hostsOLD = array();
		$DBRESULT = $pearDB->query("SELECT host_host_id FROM hostcategories_relation WHERE hostcategories_hc_id = '".$hc_id."'");
		while ($host = $DBRESULT->fetchRow())
			$hostsOLD[$host["host_host_id"]] = $host["host_host_id"];
		$DBRESULT->free();

		/*
		 * Update Host HG relations
		 */
		$DBRESULT = $pearDB->query("DELETE FROM hostcategories_relation WHERE hostcategories_hc_id = '".$hc_id."'");
		isset($ret["hc_hosts"]) ? $ret = $ret["hc_hosts"] : $ret = $form->getSubmitValue("hc_hosts");
		$hgNEW = array();

		$rq = "INSERT INTO hostcategories_relation (hostcategories_hc_id, host_host_id) VALUES ";
		for ($i = 0; $i < count($ret); $i++)	{
			if ($i != 0)
				$rq .= ", ";
			$rq .= " ('".$hc_id."', '".$ret[$i]."')";

			$hostsNEW[$ret[$i]] = $ret[$i];
		}
		if ($i != 0)
			$DBRESULT = $pearDB->query($rq);

		isset($ret["hc_hostsTemplate"]) ? $ret = $ret["hc_hostsTemplate"] : $ret = $form->getSubmitValue("hc_hostsTemplate");
		$rq = "INSERT INTO hostcategories_relation (hostcategories_hc_id, host_host_id) VALUES ";
		for ($i = 0; $i < count($ret); $i++)	{
			if ($i != 0)
				$rq .= ", ";
			$rq .= " ('".$hc_id."', '".$ret[$i]."')";

			$hostsNEW[$ret[$i]] = $ret[$i];
		}
		if ($i != 0)
			$DBRESULT = $pearDB->query($rq);
	}
?>