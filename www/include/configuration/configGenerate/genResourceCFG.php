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
	
	$handle = create_file($nagiosCFGPath.$tab['id']."/resource.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query("SELECT * FROM `cfg_resource` WHERE `resource_activate` = '1'");
	$str = NULL;
	while ($DBRESULTource =& $DBRESULT->fetchRow())	{
		$ret["comment"] ? ($str .= "# '".$DBRESULTource["resource_name"]."'\n") : NULL;
		if ($ret["comment"] && $DBRESULTource["resource_comment"])	{
			$comment = array();
			$comment = explode("\n", $DBRESULTource["resource_comment"]);
			foreach ($comment as $cmt)
				$str .= "# ".$cmt."\n";
		}
		$str .= $DBRESULTource["resource_name"]."=".$DBRESULTource["resource_line"]."\n";
	}
	$str .= "\n";
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/resource.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
?>