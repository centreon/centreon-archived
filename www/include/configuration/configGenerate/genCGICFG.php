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

	if (!is_dir($nagiosCFGPath.$tab['id']."/")) {
		mkdir($nagiosCFGPath.$tab['id']."/");
	}

	$handle = create_file($nagiosCFGPath.$tab['id']."/cgi.cfg", $oreon->user->get_name());
	$res =& $pearDB->query("SELECT cfg_dir FROM `cfg_nagios` WHERE `nagios_activate` = '1' LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$nagios = $res->fetchRow();	
	$DBRESULT =& $pearDB->query("SELECT * FROM `cfg_cgi` WHERE `cgi_activate` = '1' LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	if ($DBRESULT->numRows())
		$cgi = $DBRESULT->fetchRow();
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
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/cgi.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
?>