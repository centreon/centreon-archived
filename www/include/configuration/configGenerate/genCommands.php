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

	if (!is_dir($nagiosCFGPath.$tab['id']."/"))
		mkdir($nagiosCFGPath.$tab['id']."/");

	$handle1 = create_file($nagiosCFGPath.$tab['id']."/misccommands.cfg", $oreon->user->get_name());
	$handle2 = create_file($nagiosCFGPath.$tab['id']."/checkcommands.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query('SELECT * FROM `command` ORDER BY `command_type`,`command_name`');
	$command = array();
	$i1 = 1;
	$i2 = 1;
	$str1 = NULL;
	$str2 = NULL;
	while ($command =& $DBRESULT->fetchRow())	{

		$command["command_line"] = str_replace('#BR#', "\\n", $command["command_line"]);
		$command["command_line"] = str_replace('#T#', "\\t", $command["command_line"]);
		$command["command_line"] = str_replace('#R#', "\\r", $command["command_line"]);
		$command["command_line"] = str_replace('#S#', "/", $command["command_line"]);
		$command["command_line"] = str_replace('#BS#', "\\", $command["command_line"]);

		if ($command["command_type"] == 1 || $command["command_type"] == 3)	{
			/*
			 * Notification Command case -> command_type == 1
			 */
			$ret["comment"] ? ($str1 .= "# '" . $command["command_name"] . "' command definition " . $i1 . "\n") : NULL;
			$str1 .= "define command{\n";
			if ($command["command_name"]) 
				$str1 .= print_line("command_name", $command["command_name"]);
			if ($command["command_line"]) 
				$str1 .= print_line("command_line", str_replace("@MAILER@", $oreon->optGen["mailer_path_bin"], $command["command_line"]));
			if ($command["command_example"]) 
				$str1 .= print_line(";command_example", $command["command_example"]);
			$str1 .= "}\n\n";
			$i1++;
		} else if ($command["command_type"] == 2)	{
			/*
			 * Check Command case -> command_type == 2
			 */
			$ret["comment"] ? ($str2 .= "# '" . $command["command_name"] . "' command definition " . $i2 . "\n") : NULL;
			$str2 .= "define command{\n";
			if ($command["command_name"]) 
				$str2 .= print_line("command_name", $command["command_name"]);
			if ($command["command_line"]) 
				$str2 .= print_line("command_line", str_replace("@MAILER@", $oreon->optGen["mailer_path_bin"], $command["command_line"]));
			if ($command["command_example"]) 
				$str2 .= print_line(";command_example", $command["command_example"]);
			$str2 .= "}\n\n";
			$i2++;
		}
	}
	write_in_file($handle1, html_entity_decode($str1, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/misccommands.cfg");
	write_in_file($handle2, html_entity_decode($str2, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/checkcommands.cfg");
	fclose($handle1);
	fclose($handle2);
	$DBRESULT->free();
	unset($str1);
	unset($str2);
?>