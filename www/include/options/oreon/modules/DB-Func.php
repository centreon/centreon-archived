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

	if (!isset($oreon))
		exit();

	function getModuleInfoInDB($name = NULL, $id = NULL) {
		if (!$name && !$id) return;
		global $pearDB;
		if ($id)
			$rq = "SELECT * FROM modules_informations WHERE id='".$id."'  LIMIT 1";
		else if ($name)
			$rq = "SELECT * FROM modules_informations WHERE name='".$name."' LIMIT 1";		
		$DBRESULT =& $pearDB->query($rq);
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
		$DBRESULT =& $pearDB->query($rq);
		if ($DBRESULT->numRows())
			return true;
		else
			return false;
	}
	
	function testUpgradeExistence($id = NULL, $release = NULL)	{
		if (!$id || !$release) return true;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT mod_release FROM modules_informations WHERE id = '".$id."' LIMIT 1");
		$module =& $DBRESULT->fetchRow();
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
		isset($name) && $name != NULL ? $rq .= "'".htmlentities($name , ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($module_conf["rname"]) && $module_conf["rname"] != NULL ? $rq .= "'".htmlentities($module_conf["rname"] , ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($module_conf["mod_release"]) && $module_conf["mod_release"] != NULL ? $rq .= "'".htmlentities($module_conf["mod_release"] , ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($module_conf["is_removeable"]) && $module_conf["is_removeable"] != NULL ? $rq .= "'".htmlentities($module_conf["is_removeable"] , ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($module_conf["infos"]) && $module_conf["infos"] != NULL ? $rq .= "'".htmlentities($module_conf["infos"] , ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($module_conf["author"]) && $module_conf["author"] != NULL ? $rq .= "'".htmlentities($module_conf["author"] , ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($module_conf["lang_files"]) && $module_conf["lang_files"] != NULL ? $rq .= "'".htmlentities($module_conf["lang_files"] , ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($module_conf["sql_files"]) && $module_conf["sql_files"] != NULL ? $rq .= "'".htmlentities($module_conf["sql_files"] , ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($module_conf["php_files"]) && $module_conf["php_files"] != NULL ? $rq .= "'".htmlentities($module_conf["php_files"] , ENT_QUOTES)."', ": $rq .= "NULL,";
		isset($module_conf["svc_tools"]) && $module_conf["svc_tools"] != NULL ? $rq .= "'".htmlentities($module_conf["svc_tools"] , ENT_QUOTES)."', ": $rq .= "NULL,";
		isset($module_conf["host_tools"]) && $module_conf["host_tools"] != NULL ? $rq .= "'".htmlentities($module_conf["host_tools"] , ENT_QUOTES)."'": $rq .= "NULL";
		$rq .= ")";
		$DBRESULT =& $pearDB->query($rq);
		$DBRESULT =& $pearDB->query("SELECT MAX(id) FROM modules_informations");
		$id =& $DBRESULT->fetchRow();
		return ($id["MAX(id)"]);
	}
	
	function upgradeModuleInDB($id = NULL, $upgrade_conf = array())	{
		if (!$id) return NULL;
		if (testUpgradeExistence($id, $upgrade_conf["release_to"]))	return NULL;
		global $pearDB;
		$rq = "UPDATE `modules_informations` SET ";
		if (isset($upgrade_conf["rname"]) && $upgrade_conf["rname"]) $rq .= "rname = '".htmlentities($upgrade_conf["rname"] , ENT_QUOTES)."', ";
		if (isset($upgrade_conf["release_to"]) && $upgrade_conf["release_to"]) $rq .= "mod_release = '".htmlentities($upgrade_conf["release_to"] , ENT_QUOTES)."', ";
		if (isset($upgrade_conf["is_removeable"]) && $upgrade_conf["is_removeable"]) $rq .= "is_removeable = '".htmlentities($upgrade_conf["is_removeable"] , ENT_QUOTES)."', ";
		if (isset($upgrade_conf["infos"]) && $upgrade_conf["infos"]) $rq .= "infos = '".htmlentities($upgrade_conf["infos"] , ENT_QUOTES)."', ";
		if (isset($upgrade_conf["author"]) && $upgrade_conf["author"]) $rq .= "author = '".htmlentities($upgrade_conf["author"] , ENT_QUOTES)."', ";
		if (isset($upgrade_conf["lang_files"]) && $upgrade_conf["lang_files"]) $rq .= "lang_files = '".htmlentities($upgrade_conf["lang_files"] , ENT_QUOTES)."', ";
		if (isset($upgrade_conf["sql_files"]) && $upgrade_conf["sql_files"]) $rq .= "sql_files = '".htmlentities($upgrade_conf["sql_files"] , ENT_QUOTES)."', ";
		if (isset($upgrade_conf["php_files"]) && $upgrade_conf["php_files"]) $rq .= "php_files = '".htmlentities($upgrade_conf["php_files"] , ENT_QUOTES)."', ";
		if (strcmp("UPDATE `modules_informations` SET ", $rq))	{
			# Delete last ',' in request
			$rq[strlen($rq)-2] = " ";
			$rq .= "WHERE id = '".$id."'";
			$DBRESULT =& $pearDB->query($rq);
			return true;
		}
		return NULL;
	}
	
	function deleteModuleInDB($id = NULL)	{
		if (!$id) return NULL;
		global $pearDB;
		$rq = "DELETE FROM `modules_informations` WHERE id = '".$id."'";
		$DBRESULT =& $pearDB->query($rq);
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
                    $DBRESULT =& $pearDB->query($str);
                    $str = NULL;
                }
                else
                	$str .= $line;
            }
        }		
	}
?>
