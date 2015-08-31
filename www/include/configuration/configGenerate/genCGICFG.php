<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	if (!isset($oreon))
		exit();

	if (!is_dir($nagiosCFGPath.$tab['id']."/"))
		mkdir($nagiosCFGPath.$tab['id']."/");

	$handle = create_file($nagiosCFGPath.$tab['id']."/cgi.cfg", $oreon->user->get_name());
	$res = $pearDB->query("SELECT cfg_dir
						   FROM `cfg_nagios`
						   WHERE `nagios_activate` = '1'
						   AND nagios_server_id = ".$pearDB->escape($tab['id'])." LIMIT 1");
	$nagios = $res->fetchRow();
	$DBRESULT = $pearDB->query("SELECT *
								FROM `cfg_cgi`
								WHERE `cgi_activate` = '1'
								AND instance_id = ".$pearDB->escape($tab['id'])."
								LIMIT 1");
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
        if ($key == 'escape_html_tags' && $value == 2) continue;
        if ($key == 'lock_author_names' && $value == 2) continue;
		if (($value || $value === '0') && $key != "cgi_id" && $key != "cgi_name" && $key != "cgi_comment" && $key != "cgi_activate" && $key != "instance_id") {
			$value = str_replace("\r\n", ",", $value);
			$str .= $key."=".$value."\n";
		} else if ($key == "use_authentication") {
			$value = str_replace("\r\n", ",", $value);
			$str .= $key."=".$value."\n";
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/cgi.cfg");
	fclose($handle);
	
	setFileMod($nagiosCFGPath.$tab['id']."/cgi.cfg");
	
	$DBRESULT->free();
	unset($str);
?>