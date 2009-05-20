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

	$handle = create_file($nagiosCFGPath.$tab['id']."/perfparse.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query("SELECT * FROM `cfg_perfparse` WHERE `perfparse_activate` = '1' LIMIT 1");
	if ($DBRESULT->numRows())
		$perfparse = $DBRESULT->fetchRow();
	else
		$perfparse = array();
	$str = NULL;
	$ret["comment"] ? ($str .= "# '".$perfparse["perfparse_name"]."'\n") : NULL;
	if ($ret["comment"] && $perfparse["perfparse_comment"])	{
		$comment = array();
		$comment = explode("\n", $perfparse["perfparse_comment"]);
		foreach ($comment as $cmt)
			$str .= "# ".$cmt."\n";
	}
	foreach ($perfparse as $key=>$value)	{
		if ($key != "perfparse_id" && $key != "perfparse_name" && $key != "perfparse_comment" && $key != "perfparse_activate")	{	
			if ($key == "Error_Log_Rotate")
				switch ($value)	{
					case "0" : $str .= $key." = \"No\"\n"; break;
					case "1" : $str .= $key." = \"Yes\"\n"; break;
					default : break;
				}
			else if ($key == "Drop_File_Rotate")
				switch ($value)	{
					case "0" : $str .= $key." = \"No\"\n"; break;
					case "1" : $str .= $key." = \"Yes\"\n"; break;
					default : break;
				}
			else if ($key == "Show_Status_Bar")
				switch ($value)	{
					case "0" : $str .= $key." = \"No\"\n"; break;
					case "1" : $str .= $key." = \"Yes\"\n"; break;
					default : break;
				}
			else if ($key == "Do_Report")
				switch ($value)	{
					case "0" : $str .= $key." = \"No\"\n"; break;
					case "1" : $str .= $key." = \"Yes\"\n"; break;
					default : break;
				}
			else if ($key == "Output_Log_File")
				switch ($value)	{
					case "0" : $str .= $key." = \"No\"\n"; break;
					case "1" : $str .= $key." = \"Yes\"\n"; break;
					default : break;
				}
			else if ($key == "Output_Log_Rotate")
				switch ($value)	{
					case "0" : $str .= $key." = \"No\"\n"; break;
					case "1" : $str .= $key." = \"Yes\"\n"; break;
					default : break;
				}
			else if ($key == "Use_Storage_Socket_Output")
				switch ($value)	{
					case "0" : $str .= $key." = \"No\"\n"; break;
					case "1" : $str .= $key." = \"Yes\"\n"; break;
					default : break;
				}
			else if ($key == "Use_Storage_Mysql")
				switch ($value)	{
					case "0" : $str .= $key." = \"No\"\n"; break;
					case "1" : $str .= $key." = \"Yes\"\n"; break;
					default : break;
				}
			else if ($key == "No_Raw_Data")
				switch ($value)	{
					case "0" : $str .= $key." = \"No\"\n"; break;
					case "1" : $str .= $key." = \"Yes\"\n"; break;
					default : break;
				}
			else if ($key == "No_Bin_Data")
				switch ($value)	{
					case "0" : $str .= $key." = \"No\"\n"; break;
					case "1" : $str .= $key." = \"Yes\"\n"; break;
					default : break;
				}
			else if ($key == "Default_user_permissions_Policy")
				switch ($value)	{
					case "1" : $str .= $key." = \"ro\"\n"; break;
					case "2" : $str .= $key." = \"rw\"\n"; break;
					case "3" : $str .= $key." = \"hide\"\n"; break;
					default : break;
				}
			else if ($key == "Default_user_permissions_Host_groups")
				switch ($value)	{
					case "1" : $str .= $key." = \"ro\"\n"; break;
					case "2" : $str .= $key." = \"rw\"\n"; break;
					case "3" : $str .= $key." = \"hide\"\n"; break;
					default : break;
				}
			else if ($key == "Default_user_permissions_Summary")
				switch ($value)	{
					case "1" : $str .= $key." = \"ro\"\n"; break;
					case "2" : $str .= $key." = \"rw\"\n"; break;
					case "3" : $str .= $key." = \"hide\"\n"; break;
					default : break;
				}
			else
				$str .= $key." = \"".$value."\"\n";
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/perfparse.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
?>