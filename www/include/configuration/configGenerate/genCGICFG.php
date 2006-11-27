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

	$handle = create_file($nagiosCFGPath."cgi.cfg", $oreon->user->get_name());
	$res =& $pearDB->query("SELECT cfg_dir FROM `cfg_nagios` WHERE `nagios_activate` = '1' LIMIT 1");
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	$nagios = $res->fetchRow();	
	$res =& $pearDB->query("SELECT * FROM `cfg_cgi` WHERE `cgi_activate` = '1' LIMIT 1");
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	if ($res->numRows())
		$cgi = $res->fetchRow();
	else
		$cgi = array();
	$str = NULL;
	$ret["comment"]["comment"] ? ($str .= "# '".$cgi["cgi_name"]."'\n") : NULL;
	if ($ret["comment"]["comment"] && $cgi["cgi_comment"])	{
		$comment = array();
		$comment = explode("\n", $cgi["cgi_comment"]);
		foreach ($comment as $cmt)
			$str .= "# ".$cmt."\n";
	}
	foreach ($cgi as $key=>$value)	{
		if ($value && $key != "cgi_id" && $key != "cgi_name" && $key != "cgi_comment" && $key != "cgi_activate")	{	
			$str .= $key."=".$value."\n";
		}
	}
	if ($oreon->user->get_version() == 1)	{
		$str .= "xedtemplate_config_file=".$nagios["cfg_dir"]."hostextinfo.cfg\n";
		$str .= "xedtemplate_config_file=".$nagios["cfg_dir"]."serviceextinfo.cfg\n";
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath."cgi.cfg");
	fclose($handle);
	$res->free();
	unset($str);
?>