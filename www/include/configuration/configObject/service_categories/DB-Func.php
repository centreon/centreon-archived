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
            if (isset($fields['sc_type']) && $fields['sc_severity_level'] == "") {
                $arr['sc_severity_level'] = "Severity level is required";
            }
            if (isset($fields['sc_type']) && $fields['sc_severity_icon'] == "") {
                $arr['sc_severity_icon'] = "Severity icon is required";
            }
            if (count($arr)) {
                return $arr;
            }
            return true;
        }
        
	function testServiceCategorieExistence($name = NULL) {
		global $pearDB, $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('sc_id');
		$DBRESULT = $pearDB->query("SELECT `sc_name`, `sc_id` FROM `service_categories` WHERE `sc_name` = '".htmlentities($name, ENT_QUOTES, "UTF-8")."'");
		$sc = $DBRESULT->fetchRow();
		if ($DBRESULT->numRows() >= 1 && $sc["sc_id"] == $id)
			return true;
		else if ($DBRESULT->numRows() >= 1 && $sc["sc_id"] != $id)
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

	function multipleServiceCategorieInDB ($sc = array(), $nbrDup = array())	{
                global $pearDB, $centreon;
                
                $scAcl = array();
                foreach($sc as $key => $value)	{	
			$DBRESULT = $pearDB->query("SELECT * FROM `service_categories` WHERE `sc_id` = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["sc_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "sc_name" ? ($sc_name = $value2 = $value2."_".$i) : null;
					$key2 == "sc_description" ? ($sc_alias = $value2 = $value2) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2 != NULL?("'".$value2."'"):"NULL");
				}
				if (testServiceCategorieExistence($sc_name))	{
					$val ? $rq = "INSERT INTO `service_categories` VALUES (".$val.")" : $rq = null;
					$DBRESULT = $pearDB->query($rq);
					$DBRESULT = $pearDB->query("SELECT MAX(sc_id) as maxid FROM `service_categories`");
					$maxId = $DBRESULT->fetchRow();
                                        $scAcl[$maxId['MAX(sc_id)']] = $key;
					$query = "INSERT INTO service_categories_relation (service_service_id, sc_id) (SELECT service_service_id, ".$maxId['maxid']." FROM service_categories_relation WHERE sc_id = ".$pearDB->escape($key).")";
					$pearDB->query($query);
				}
			}
		}
                CentreonACL::duplicateScAcl($scAcl);
                $centreon->user->access->updateACL();
	}

	function enableServiceCategorieInDB($sc_id = null, $sc_arr = array())	{
		if (!$sc_id && !count($sc_arr)) return;
		global $pearDB;
		if ($sc_id)
			$sc_arr = array($sc_id=>"1");
		foreach($sc_arr as $key=>$value)	{
			$DBRESULT = $pearDB->query("UPDATE service_categories SET sc_activate = '1' WHERE sc_id = '".$key."'");
		}
	}

	function disableServiceCategorieInDB($sc_id = null, $sc_arr = array())	{
		if (!$sc_id && !count($sc_arr)) return;
		global $pearDB;
		if ($sc_id)
			$sc_arr = array($sc_id=>"1");
		foreach($sc_arr as $key=>$value)	{
			$DBRESULT = $pearDB->query("UPDATE service_categories SET sc_activate = '0' WHERE sc_id = '".$key."'");
		}
	}

	function insertServiceCategorieInDB(){
		global $pearDB, $centreon;

		if (testServiceCategorieExistence($_POST["sc_name"])){
                $DBRESULT = $pearDB->query("INSERT INTO `service_categories` (`sc_name`, `sc_description`, `level`, `icon_id`, `sc_activate` ) 
                    VALUES ('".$pearDB->escape($_POST["sc_name"])."', '".$pearDB->escape($_POST["sc_description"])."', ".
                        (isset($_POST['sc_severity_level']) && $_POST['sc_type'] ? $pearDB->escape($_POST['sc_severity_level']):"NULL").", ".
                        (isset($_POST['sc_severity_icon']) && $_POST['sc_type'] ? $pearDB->escape($_POST['sc_severity_icon']) : "NULL").", ".
                        "'".$_POST["sc_activate"]["sc_activate"]."')");
                $DBRESULT = $pearDB->query("SELECT MAX(sc_id) FROM `service_categories` WHERE sc_name LIKE '".$pearDB->escape($_POST["sc_name"])."'");
                $data = $DBRESULT->fetchRow();
        }
        updateServiceCategoriesServices($data["MAX(sc_id)"]);
        $centreon->user->access->updateACL();
	}

	function updateServiceCategorieInDB(){
		global $pearDB, $centreon;

		$DBRESULT = $pearDB->query("UPDATE `service_categories` SET 
                    `sc_name` = '".$_POST["sc_name"]."' , 
                    `sc_description` = '".$_POST["sc_description"]."' , 
                    `level` = ".(isset($_POST['sc_severity_level']) && $_POST['sc_type'] ? $pearDB->escape($_POST['sc_severity_level']):"NULL").", 
                    `icon_id` = ".(isset($_POST['sc_severity_icon']) && $_POST['sc_type'] ? $pearDB->escape($_POST['sc_severity_icon']) : "NULL").",
                    `sc_activate` = '".$_POST["sc_activate"]["sc_activate"]."' 
                    WHERE `sc_id` = '".$_POST["sc_id"]."'");
		updateServiceCategoriesServices(htmlentities($_POST["sc_id"], ENT_QUOTES, "UTF-8"));
		$centreon->user->access->updateACL();
	}

	function deleteServiceCategorieInDB($sc_id = NULL){
		global $pearDB, $centreon;

		$select = $_POST["select"];
		foreach ($select as $key => $value){
			$DBRESULT = $pearDB->query("DELETE FROM `service_categories` WHERE `sc_id` = '".$key."'");
		}
		$centreon->user->access->updateACL();
	}

	function updateServiceCategoriesServices($sc_id)	{
		global $pearDB, $form;

		if (!$sc_id)
			return;

		$DBRESULT = $pearDB->query("DELETE FROM service_categories_relation WHERE sc_id = '".$sc_id."' AND service_service_id IN (SELECT service_id FROM service WHERE service_register = '0')");
		if (isset($_POST["sc_svcTpl"]))
			foreach ($_POST["sc_svcTpl"] as $key)	{
				$rq = "INSERT INTO service_categories_relation (service_service_id, sc_id) VALUES ('".$key."', '".$sc_id."')";
				$DBRESULT = $pearDB->query($rq);
			}
	}

?>