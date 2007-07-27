<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	
	if (!isset($oreon))
		exit();

	$handle1 = create_file($nagiosCFGPath."misccommands.cfg", $oreon->user->get_name());
	$handle2 = create_file($nagiosCFGPath."checkcommands.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query('SELECT * FROM `command` ORDER BY `command_type`,`command_name`');
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$command = array();
	$i1 = 1;
	$i2 = 1;
	$str1 = NULL;
	$str2 = NULL;
	while($DBRESULT->fetchInto($command))	{
		$command["command_line"] = str_replace('#BR#', "\\n", $command["command_line"]);
		$command["command_line"] = str_replace('#T#', "\\t", $command["command_line"]);
		$command["command_line"] = str_replace('#R#', "\\r", $command["command_line"]);
		$command["command_line"] = str_replace('#S#', "/", $command["command_line"]);
		$command["command_line"] = str_replace('#BS#', "\\", $command["command_line"]);
		# Notification Command case -> command_type == 1
		if ($command["command_type"] == 1 || $command["command_type"] == 3)	{
			$ret["comment"]["comment"] ? ($str1 .= "# '" . $command["command_name"] . "' command definition " . $i1 . "\n") : NULL;
			$str1 .= "define command{\n";
			$str1 .= print_line("command_name", $command["command_name"]);
			$str1 .= print_line("command_line", str_replace("@MAILER@", $oreon->optGen["mailer_path_bin"], $command["command_line"]));
			$str1 .= "}\n\n";
			$i1++;
		}	
		# Check Command case -> command_type == 2
		else if ($command["command_type"] == 2)	{
			$ret["comment"]["comment"] ? ($str2 .= "# '" . $command["command_name"] . "' command definition " . $i2 . "\n") : NULL;
			$str2 .= "define command{\n";
			if ($command["command_name"]) $str2 .= print_line("command_name", $command["command_name"]);
			if ($command["command_line"]) $str2 .= print_line("command_line", str_replace("@MAILER@", $oreon->optGen["mailer_path_bin"], $command["command_line"]));
			$str2 .= "}\n\n";
			$i2++;
		}
	}
	write_in_file($handle1, html_entity_decode($str1, ENT_QUOTES), $nagiosCFGPath."misccommands.cfg");
	write_in_file($handle2, html_entity_decode($str2, ENT_QUOTES), $nagiosCFGPath."checkcommands.cfg");
	fclose($handle1);
	fclose($handle2);
	$DBRESULT->free();
	unset($str1);
	unset($str2);
?>