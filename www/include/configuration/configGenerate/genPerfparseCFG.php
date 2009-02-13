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