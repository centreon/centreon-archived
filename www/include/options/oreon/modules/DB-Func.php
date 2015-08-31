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

	function getModuleInfoInDB($name = NULL, $id = NULL) {
		if (!$name && !$id) return;
		global $pearDB;
		if ($id)
			$rq = "SELECT * FROM modules_informations WHERE id='".$id."'  LIMIT 1";
		else if ($name)
			$rq = "SELECT * FROM modules_informations WHERE name='".$name."' LIMIT 1";
		$DBRESULT = $pearDB->query($rq);
		if ($DBRESULT->numRows())
			return ($DBRESULT->fetchRow());
		else
			return array();
	}

	function testModuleExistence($id = NULL, $name = NULL)	{
		if (!$id && !$name) return false;
		global $pearDB;
		if ($id)
			$rq = "SELECT id FROM modules_informations WHERE id = '".$id."'  LIMIT 1";
		else if ($name)
			$rq = "SELECT id FROM modules_informations WHERE name = '".$name."'  LIMIT 1";
		$DBRESULT = $pearDB->query($rq);
		if ($DBRESULT->numRows())
			return true;
		else
			return false;
	}

	function testUpgradeExistence($id = NULL, $release = NULL)	{
		if (!$id || !$release) return true;
		global $pearDB;
		$DBRESULT = $pearDB->query("SELECT mod_release FROM modules_informations WHERE id = '".$id."' LIMIT 1");
		$module = $DBRESULT->fetchRow();
		if ($module["mod_release"] == $release)
			return true;
		else
			return false;
	}

	function insertModuleInDB($name = NULL, $module_conf = array())	{
		if (!$name) return NULL;
		if (testModuleExistence(NULL, $name))	return NULL;
		global $pearDB;
		$rq = "INSERT INTO `modules_informations` " .
				"(`name` , `rname` , `mod_release` , `is_removeable` , `infos` , `author` , `lang_files`, `sql_files`, `php_files`, `svc_tools`, `host_tools` ) " .
				"VALUES ( ";
		isset($name) && $name != NULL ? $rq .= "'".htmlentities($name , ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($module_conf["rname"]) && $module_conf["rname"] != NULL ? $rq .= "'".htmlentities($module_conf["rname"] , ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($module_conf["mod_release"]) && $module_conf["mod_release"] != NULL ? $rq .= "'".htmlentities($module_conf["mod_release"] , ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($module_conf["is_removeable"]) && $module_conf["is_removeable"] != NULL ? $rq .= "'".htmlentities($module_conf["is_removeable"] , ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($module_conf["infos"]) && $module_conf["infos"] != NULL ? $rq .= "'".htmlentities($module_conf["infos"] , ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($module_conf["author"]) && $module_conf["author"] != NULL ? $rq .= "'".htmlentities($module_conf["author"] , ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($module_conf["lang_files"]) && $module_conf["lang_files"] != NULL ? $rq .= "'".htmlentities($module_conf["lang_files"] , ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($module_conf["sql_files"]) && $module_conf["sql_files"] != NULL ? $rq .= "'".htmlentities($module_conf["sql_files"] , ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($module_conf["php_files"]) && $module_conf["php_files"] != NULL ? $rq .= "'".htmlentities($module_conf["php_files"] , ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL,";
		isset($module_conf["svc_tools"]) && $module_conf["svc_tools"] != NULL ? $rq .= "'".htmlentities($module_conf["svc_tools"] , ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL,";
		isset($module_conf["host_tools"]) && $module_conf["host_tools"] != NULL ? $rq .= "'".htmlentities($module_conf["host_tools"] , ENT_QUOTES, "UTF-8")."'": $rq .= "NULL";
		$rq .= ")";
		$DBRESULT = $pearDB->query($rq);
		$DBRESULT = $pearDB->query("SELECT MAX(id) FROM modules_informations");
		$id = $DBRESULT->fetchRow();
		return ($id["MAX(id)"]);
	}

	function upgradeModuleInDB($id = NULL, $upgrade_conf = array())	{
		if (!$id) return NULL;
		if (testUpgradeExistence($id, $upgrade_conf["release_to"]))	return NULL;
		global $pearDB;
		$rq = "UPDATE `modules_informations` SET ";
		if (isset($upgrade_conf["rname"]) && $upgrade_conf["rname"]) $rq .= "rname = '".htmlentities($upgrade_conf["rname"] , ENT_QUOTES, "UTF-8")."', ";
		if (isset($upgrade_conf["release_to"]) && $upgrade_conf["release_to"]) $rq .= "mod_release = '".htmlentities($upgrade_conf["release_to"] , ENT_QUOTES, "UTF-8")."', ";
		if (isset($upgrade_conf["is_removeable"]) && $upgrade_conf["is_removeable"]) $rq .= "is_removeable = '".htmlentities($upgrade_conf["is_removeable"] , ENT_QUOTES, "UTF-8")."', ";
		if (isset($upgrade_conf["infos"]) && $upgrade_conf["infos"]) $rq .= "infos = '".htmlentities($upgrade_conf["infos"] , ENT_QUOTES, "UTF-8")."', ";
		if (isset($upgrade_conf["author"]) && $upgrade_conf["author"]) $rq .= "author = '".htmlentities($upgrade_conf["author"] , ENT_QUOTES, "UTF-8")."', ";
		if (isset($upgrade_conf["lang_files"]) && $upgrade_conf["lang_files"]) $rq .= "lang_files = '".htmlentities($upgrade_conf["lang_files"] , ENT_QUOTES, "UTF-8")."', ";
		if (isset($upgrade_conf["sql_files"]) && $upgrade_conf["sql_files"]) $rq .= "sql_files = '".htmlentities($upgrade_conf["sql_files"] , ENT_QUOTES, "UTF-8")."', ";
		if (isset($upgrade_conf["php_files"]) && $upgrade_conf["php_files"]) $rq .= "php_files = '".htmlentities($upgrade_conf["php_files"] , ENT_QUOTES, "UTF-8")."', ";
		if (isset($upgrade_conf["svc_tools"]) && $upgrade_conf["svc_tools"]) $rq .= "svc_tools = '" . htmlentities($upgrade_conf["svc_tools"] , ENT_QUOTES, "UTF-8")."', ";
		if (isset($upgrade_conf["host_tools"]) && $upgrade_conf["host_tools"]) $rq .= "svc_tools = '" . htmlentities($upgrade_conf["host_tools"] , ENT_QUOTES, "UTF-8")."', ";
		if (strcmp("UPDATE `modules_informations` SET ", $rq))	{
			# Delete last ',' in request
			$rq[strlen($rq)-2] = " ";
			$rq .= "WHERE id = '".$id."'";
			$DBRESULT = $pearDB->query($rq);
			return true;
		}
		return NULL;
	}

	function deleteModuleInDB($id = NULL)	{
		if (!$id) return NULL;
		global $pearDB;
		$rq = "DELETE FROM `modules_informations` WHERE id = '".$id."'";
		$DBRESULT = $pearDB->query($rq);
		return true;
	}

	function execute_sql_file($name = NULL, $sql_file_path = NULL)	{
		if (!$sql_file_path || !$name)	return;
		global $pearDB, $conf_centreon;
		$sql_stream = file($sql_file_path.$name);
        $str = NULL;
        for ($i = 0; $i <= count($sql_stream) - 1; $i++)	{
            $line = $sql_stream[$i];
            if ($line[0] != '#')    {
                $pos = strrpos($line, ";");
                if ($pos != false)      {
                    $str .= $line;
                    $str = chop ($str);
                    $str = str_replace("@DB_CENTSTORAGE@", $conf_centreon['dbcstg'], $str);
                    $DBRESULT = $pearDB->query($str);
                    $str = NULL;
                }
                else
                	$str .= $line;
            }
        }
	}
?>
