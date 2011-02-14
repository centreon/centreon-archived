<?php
/*
 * Copyright 2005-2011 MERETHIS
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

	$handle = create_file($nagiosCFGPath.$tab['id']."/cgi.cfg", $oreon->user->get_name());
	$res = $pearDB->query("SELECT cfg_dir FROM `cfg_nagios` WHERE `nagios_activate` = '1' LIMIT 1");
	$nagios = $res->fetchRow();	
	$DBRESULT = $pearDB->query("SELECT * FROM `cfg_cgi` WHERE `cgi_activate` = '1' LIMIT 1");
	if ($DBRESULT->numRows())
		$cgi = $DBRESULT->fetchRow();
	else
		$cgi = array();
	
	$str = NULL;
	$ret["comment"] ? ($str .= "# '".$cgi["cgi_name"]."'\n") : NULL;
	if ($ret["comment"] && $cgi["cgi_comment"])	{
		$comment = array();
		$comment = explode("\n", $cgi["cgi_comment"]);
		foreach ($comment as $cmt)
			$str .= "# ".$cmt."\n";
	}
	foreach ($cgi as $key=>$value)	{
		if ($value && $key != "cgi_id" && $key != "cgi_name" && $key != "cgi_comment" && $key != "cgi_activate") {
			$value = str_replace("\r\n", ",", $value);
			$str .= $key."=".$value."\n";
		} else if ($key == "use_authentication") {
			$value = str_replace("\r\n", ",", $value);
			$str .= $key."=".$value."\n";
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/cgi.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
?>