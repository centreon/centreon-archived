<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	$i = 0;
	$res =& $pearDB->query('SELECT snmp_trapd_path_conf FROM `general_opt` LIMIT 1');
	if ($res->numRows())	{
		$trap_conf = $res->fetchRow();
		$handle = create_file($trap_conf["snmp_trapd_path_conf"], $oreon->user->get_name());	
		$res1 =& $pearDB->query('SELECT * FROM `traps` ORDER BY `traps_name`');
		$str = "\n";
		while($res1->fetchInto($trap))	{
			$trap["traps_comments"] ? $str .= "# ".$trap["traps_comments"]."\n" : NULL;
			$str .= "traphandle ".$trap["traps_oid"]." ".$trap["traps_handler"]." ".$trap["traps_id"]." ".$trap["traps_args"];
			$str .= "\n";
			$i++;
		}
		write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $trap_conf);
		fclose($handle);
		unset($str);
		$res1->free();
	}
	$res->free();
?>
