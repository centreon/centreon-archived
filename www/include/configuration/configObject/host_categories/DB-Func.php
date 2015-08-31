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

	if (!isset ($oreon))
		exit ();

        /**
         * Rule that checks whether severity data is set
         */
        function checkSeverity($fields) {
            $arr = array();
            if (isset($fields['hc_type']) && $fields['hc_severity_level'] == "") {
                $arr['hc_severity_level'] = "Severity level is required";
            }
            if (isset($fields['hc_type']) && $fields['hc_severity_icon'] == "") {
                $arr['hc_severity_icon'] = "Severity icon is required";
            }
            if (count($arr)) {
                return $arr;
            }
            return true;
        }
        
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

    function shouldNotBeEqTo0($value) {
        if ($value) {
            return true;
        } else {
            return false;
        }
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

                $hcAcl = array();
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
                                                $hcAcl[$maxId["MAX(hc_id)"]] = $key;
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
                CentreonACL::duplicateHcAcl($hcAcl);
                $oreon->user->access->updateACL();
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
		$rq .= "(hc_name, hc_alias, level, icon_id, hc_comment, hc_activate) ";
		$rq .= "VALUES (";
		isset($ret["hc_name"]) && $ret["hc_name"] ? $rq .= "'".$pearDB->escape($ret["hc_name"])."', " : $rq .= "NULL,";
		isset($ret["hc_alias"]) && $ret["hc_alias"] ? $rq .= "'".$pearDB->escape($ret["hc_alias"])."', " : $rq .= "NULL,";
                isset($ret["hc_severity_level"]) && $ret["hc_severity_level"] && isset($ret['hc_type']) ? $rq .= "'".$pearDB->escape($ret["hc_severity_level"])."', " : $rq .= "NULL,";
                isset($ret["hc_severity_icon"]) && $ret["hc_severity_icon"] ? $rq .= "'".$pearDB->escape($ret["hc_severity_icon"])."', " : $rq .= "NULL,";
		isset($ret["hc_comment"]) && $ret["hc_comment"] ? $rq .= "'".$pearDB->escape($ret["hc_comment"])."', " : $rq .= "NULL, ";
		isset($ret["hc_activate"]["hc_activate"]) && $ret["hc_activate"]["hc_activate"] ? $rq .= "'".$ret["hc_activate"]["hc_activate"]."'" : $rq .= "'0'";
		$rq .= ")";

		$pearDB->query($rq);
		$DBRESULT = $pearDB->query("SELECT MAX(hc_id) FROM hostcategories");
		$hc_id = $DBRESULT->fetchRow();

		$fields["hc_name"] = $pearDB->escape($ret["hc_name"]);
		$fields["hc_alias"] = $pearDB->escape($ret["hc_alias"]);
                $fields["level"] = $pearDB->escape($ret["hc_severity_level"]);
                $fields["icon_id"] = $pearDB->escape($ret["hc_severity_icon"]);
		$fields["hc_comment"] = $pearDB->escape($ret["hc_comment"]);
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
		isset($ret["hc_name"]) && $ret["hc_name"] != NULL ? $rq .= "'".$pearDB->escape($ret["hc_name"])."', " : $rq .= "NULL, ";
		$rq .= "hc_alias = ";
		isset($ret["hc_alias"]) && $ret["hc_alias"] != NULL ? $rq .= "'".$pearDB->escape($ret["hc_alias"])."', " : $rq .= "NULL, ";
                $rq .= "level = ";
		isset($ret["hc_severity_level"]) && $ret["hc_severity_level"] && isset($ret['hc_type']) ? $rq .= "'".$pearDB->escape($ret["hc_severity_level"])."', " : $rq .= "NULL, ";
                $rq .= "icon_id = ";
		isset($ret["hc_severity_icon"]) && $ret["hc_severity_icon"] ? $rq .= "'".$pearDB->escape($ret["hc_severity_icon"])."', " : $rq .= "NULL, ";
		$rq .= "hc_comment = ";
		isset($ret["hc_comment"]) && $ret["hc_comment"] != NULL ? $rq .= "'".$pearDB->escape($ret["hc_comment"])."', " : $rq .= "NULL, ";
		$rq .= "hc_activate = ";
		isset($ret["hc_activate"]["hc_activate"]) && $ret["hc_activate"]["hc_activate"] != NULL ? $rq .= "'".$ret["hc_activate"]["hc_activate"]."'" : $rq .= "NULL ";
		$rq .= "WHERE hc_id = '".$hc_id."'";
		$DBRESULT = $pearDB->query($rq);

		$fields["hc_name"] = $pearDB->escape($ret["hc_name"]);
		$fields["hc_alias"] = $pearDB->escape($ret["hc_alias"]);
                $fields["level"] = $pearDB->escape($ret["hc_severity_level"]);
                $fields["icon_id"] = $pearDB->escape($ret["hc_severity_icon"]);
		$fields["hc_comment"] = $pearDB->escape($ret["hc_comment"]);
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

        
                $ret = isset($ret["hc_hosts"]) ? $ret["hc_hosts"] : CentreonUtils::mergeWithInitialValues($form, 'hc_hosts');
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
