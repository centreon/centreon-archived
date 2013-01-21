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

	if (!isset ($oreon))
		exit ();

	function testServiceGroupExistence ($name = NULL)	{
		global $pearDB, $form, $oreon;

		$id = NULL;

		if (isset($form)) {
			$id = $form->getSubmitValue('sg_id');
		}

		$DBRESULT = $pearDB->query("SELECT sg_name, sg_id FROM servicegroup WHERE sg_name = '".htmlentities($oreon->checkIllegalChar($name), ENT_QUOTES, "UTF-8")."'");
		$sg = $DBRESULT->fetchRow();
		if ($DBRESULT->numRows() >= 1 && $sg["sg_id"] == $id) {
			# Modif
			return true;
		} else if ($DBRESULT->numRows() >= 1 && $sg["sg_id"] != $id) {
			# Duplicate
			return false;
		} else {
			return true;
		}
	}

	function enableServiceGroupInDB ($sg_id = null)	{
		if (!$sg_id) return;
		global $pearDB, $oreon;
		$DBRESULT = $pearDB->query("UPDATE servicegroup SET sg_activate = '1' WHERE sg_id = '".$sg_id."'");
		$DBRESULT2 = $pearDB->query("SELECT sg_name FROM `servicegroup` WHERE `sg_id` = '".$sg_id."' LIMIT 1");
		$row = $DBRESULT2->fetchRow();
		$oreon->CentreonLogAction->insertLog("servicegroup", $sg_id, $row['sg_name'], "enable");
	}

	function disableServiceGroupInDB ($sg_id = null)	{
		if (!$sg_id) return;
		global $pearDB, $oreon;
		$DBRESULT = $pearDB->query("UPDATE servicegroup SET sg_activate = '0' WHERE sg_id = '".$sg_id."'");
		$DBRESULT2 = $pearDB->query("SELECT sg_name FROM `servicegroup` WHERE `sg_id` = '".$sg_id."' LIMIT 1");
		$row = $DBRESULT2->fetchRow();
		$oreon->CentreonLogAction->insertLog("servicegroup", $sg_id, $row['sg_name'], "disable");
	}

	function deleteServiceGroupInDB ($serviceGroups = array())	{
		global $pearDB, $centreon;

		foreach($serviceGroups as $key=>$value) {
			$DBRESULT2 = $pearDB->query("SELECT sg_name FROM `servicegroup` WHERE `sg_id` = '".$key."' LIMIT 1");
			$row = $DBRESULT2->fetchRow();
			$DBRESULT = $pearDB->query("DELETE FROM servicegroup WHERE sg_id = '".$key."'");
			$centreon->CentreonLogAction->insertLog("servicegroup", $key, $row['sg_name'], "d");
		}
		$centreon->user->access->updateACL();
	}

	function multipleServiceGroupInDB ($serviceGroups = array(), $nbrDup = array())	{
		global $pearDB, $oreon, $is_admin;
		foreach($serviceGroups as $key=>$value)	{
			$DBRESULT = $pearDB->query("SELECT * FROM servicegroup WHERE sg_id = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["sg_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = NULL;
				$rq = NULL;
				foreach ($row as $key2=>$value2)	{
					$key2 == "sg_name" ? ($sg_name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
					if ($key2 != "sg_id") {
						$fields[$key2] = $value2;
					}
					if (isset($sg_name)) {
						$fields["sg_name"] = $sg_name;
					}
				}
				if (testServiceGroupExistence($sg_name))	{
					$val ? $rq = "INSERT INTO servicegroup VALUES (".$val.")" : $rq = null;
					$DBRESULT = $pearDB->query($rq);
					$DBRESULT = $pearDB->query("SELECT MAX(sg_id) FROM servicegroup");
					$maxId = $DBRESULT->fetchRow();
					if (isset($maxId["MAX(sg_id)"]))	{

						$DBRESULT->free();
						$DBRESULT = $pearDB->query("SELECT DISTINCT sgr.host_host_id, sgr.hostgroup_hg_id, sgr.service_service_id FROM servicegroup_relation sgr WHERE sgr.servicegroup_sg_id = '".$key."'");
						$fields["sg_hgServices"] = "";
						while($service = $DBRESULT->fetchRow())	{
							$val = null;
							foreach ($service as $key2=>$value2)
								$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
							$DBRESULT2 = $pearDB->query("INSERT INTO servicegroup_relation (host_host_id, hostgroup_hg_id, service_service_id, servicegroup_sg_id) VALUES (".$val.", '".$maxId["MAX(sg_id)"]."')");
							$fields["sg_hgServices"] .= $service["service_service_id"] . ",";
						}
						$fields["sg_hgServices"] = trim($fields["sg_hgServices"], ",");
						$oreon->CentreonLogAction->insertLog("servicegroup", $maxId["MAX(sg_id)"], $sg_name, "a", $fields);
					}
				}
			}
		}
	}

	function insertServiceGroupInDB ($ret = array())	{
            global $centreon;
            
            $sg_id = insertServiceGroup($ret);
            updateServiceGroupServices($sg_id, $ret);
            $centreon->user->access->updateACL();
            return $sg_id;
	}

	function updateServiceGroupInDB ($sg_id = NULL, $ret = array(), $increment = false)	{
            global $centreon;
                
            if (!$sg_id) return;
            updateServiceGroup($sg_id, $ret);
            updateServiceGroupServices($sg_id, $ret, $increment);
            $centreon->user->access->updateACL();
	}

	function insertServiceGroup($ret = array())	{
		global $form, $pearDB, $oreon;
		if (!count($ret))
			$ret = $form->getSubmitValues();

		$ret["sg_name"] = $oreon->checkIllegalChar($ret["sg_name"]);

		$rq = "INSERT INTO servicegroup (sg_name, sg_alias, sg_comment, sg_activate) ";
		$rq .= "VALUES (";
		isset($ret["sg_name"]) && $ret["sg_name"] != NULL ? $rq .= "'".htmlentities($ret["sg_name"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
		isset($ret["sg_alias"]) && $ret["sg_alias"] != NULL ? $rq .= "'".htmlentities($ret["sg_alias"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
		isset($ret["sg_comment"]) && $ret["sg_comment"] != NULL ? $rq .= "'".htmlentities($ret["sg_comment"], ENT_QUOTES, "UTF-8")."', " : $rq .= "NULL, ";
		isset($ret["sg_activate"]["sg_activate"]) && $ret["sg_activate"]["sg_activate"] != NULL ? $rq .= "'".$ret["sg_activate"]["sg_activate"]."'" : $rq .= "'0'";
		$rq .= ")";
		$DBRESULT = $pearDB->query($rq);

		$DBRESULT = $pearDB->query("SELECT MAX(sg_id) FROM servicegroup");
		$sg_id = $DBRESULT->fetchRow();

		$fields["sg_name"] = htmlentities($ret["sg_name"], ENT_QUOTES, "UTF-8");
		$fields["sg_alias"] = htmlentities($ret["sg_alias"], ENT_QUOTES, "UTF-8");
		$fields["sg_comment"] = htmlentities($ret["sg_comment"], ENT_QUOTES, "UTF-8");
		$fields["sg_activate"] = $ret["sg_activate"]["sg_activate"];
		if (isset($ret["sg_hgServices"]))
			$fields["sg_hgServices"] = implode(",", $ret["sg_hgServices"]);
		$oreon->CentreonLogAction->insertLog("servicegroup", $sg_id["MAX(sg_id)"], htmlentities($ret["sg_name"], ENT_QUOTES, "UTF-8"), "a", $fields);
		$DBRESULT->free();
		return ($sg_id["MAX(sg_id)"]);
	}

	function updateServiceGroup($sg_id, $ret = array())	{
		global $form, $pearDB, $oreon;

		if (!$sg_id) {
			return;
		}

	    if (!count($ret)) {
		    $ret = $form->getSubmitValues();
		}

		$ret["sg_name"] = $oreon->checkIllegalChar($ret["sg_name"]);

		$rq = "UPDATE servicegroup SET ";
		isset($ret["sg_name"]) && $ret["sg_name"] != NULL ? $rq .= "sg_name = '".htmlentities($ret["sg_name"], ENT_QUOTES, "UTF-8")."', " : $rq .= "sg_name = NULL,";
		isset($ret["sg_alias"]) && $ret["sg_alias"] != NULL ? $rq.=	"sg_alias = '".htmlentities($ret["sg_alias"], ENT_QUOTES, "UTF-8")."', " : $rq .= "sg_alias = NULL";
		isset($ret["sg_comment"]) && $ret["sg_comment"] != NULL ? $rq .= "sg_comment = '".htmlentities($ret["sg_comment"], ENT_QUOTES, "UTF-8")."', " : $rq .= "sg_comment = NULL,";
		isset($ret["sg_activate"]["sg_activate"]) && $ret["sg_activate"]["sg_activate"] != NULL ? $rq .= "sg_activate = '".$ret["sg_activate"]["sg_activate"]."' " : $rq .= "sg_activate = '0'";
		$rq .= "WHERE sg_id = '".$sg_id."'";
		$DBRESULT = $pearDB->query($rq);

		$fields["sg_name"] = htmlentities($ret["sg_name"], ENT_QUOTES, "UTF-8");
		$fields["sg_alias"] = htmlentities($ret["sg_alias"], ENT_QUOTES, "UTF-8");
		$fields["sg_comment"] = htmlentities($ret["sg_comment"], ENT_QUOTES, "UTF-8");
		$fields["sg_activate"] = $ret["sg_activate"]["sg_activate"];
		if (isset($ret["sg_hgServices"]))
			$fields["sg_hgServices"] = implode(",", $ret["sg_hgServices"]);
		$oreon->CentreonLogAction->insertLog("servicegroup", $sg_id, htmlentities($ret["sg_name"], ENT_QUOTES, "UTF-8"), "c", $fields);
	}

	function updateServiceGroupServices($sg_id, $ret = array(), $increment = false)	{
		if (!$sg_id)
			return;
		global $pearDB, $form;

		if ($increment == false) {
    		$rq  = 	"DELETE FROM servicegroup_relation ";
    		$rq .= 	"WHERE servicegroup_sg_id = '".$sg_id."'";
    		$DBRESULT = $pearDB->query($rq);
		}
		isset($ret["sg_hServices"]) ? $ret = $ret["sg_hServices"] : $ret = $form->getSubmitValue("sg_hServices");
		for ($i = 0; $i < count($ret); $i++)	{
			if (isset($ret[$i]) && $ret[$i]){
				$t = preg_split("/\-/", $ret[$i]);
				$resTest = $pearDB->query("SELECT servicegroup_sg_id service FROM servicegroup_relation WHERE host_host_id = ".$t[0]." AND service_service_id = ".$t[1]." AND servicegroup_sg_id = ".$sg_id);
				if (!$resTest->numRows()) {
				    $rq = "INSERT INTO servicegroup_relation (host_host_id, service_service_id, servicegroup_sg_id) VALUES ('".$t[0]."', '".$t[1]."', '".$sg_id."')";
				    $DBRESULT = $pearDB->query($rq);
				}
			}
		}
		isset($ret["sg_hgServices"]) ? $ret = $ret["sg_hgServices"] : $ret = $form->getSubmitValue("sg_hgServices");
		for ($i = 0; $i < count($ret); $i++)	{
			$t = preg_split("/\-/", $ret[$i]);
			$resTest = $pearDB->query("SELECT servicegroup_sg_id service FROM servicegroup_relation WHERE hostgroup_hg_id = ".$t[0]." AND service_service_id = ".$t[1]." AND servicegroup_sg_id = ".$sg_id);
			if (!$resTest->numRows()) {
			    $rq = "INSERT INTO servicegroup_relation (hostgroup_hg_id, service_service_id, servicegroup_sg_id) VALUES ('".$t[0]."', '".$t[1]."', '".$sg_id."')";
			    $DBRESULT = $pearDB->query($rq);
			}
		}
	}
?>
