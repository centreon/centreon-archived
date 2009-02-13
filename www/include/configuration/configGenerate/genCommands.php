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