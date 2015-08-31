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

	$handle = create_file($nagiosCFGPath.$tab['id']."/resource.cfg", $oreon->user->get_name());
	$DBRESULT = $pearDB->query("SELECT *
								FROM `cfg_resource` cr, `cfg_resource_instance_relations` crir
								WHERE cr.resource_id = crir.resource_id
								AND crir.instance_id = ".$pearDB->escape($tab['id'])."
								AND cr.`resource_activate` = '1'");
	$str = NULL;
	while ($DBRESULTource = $DBRESULT->fetchRow())	{
		if (isset($DBRESULTource["resource_line"]) && $DBRESULTource["resource_line"] != "") {
                    $ret["comment"] ? ($str .= "# '".$DBRESULTource["resource_name"]."'\n") : NULL;
                    if ($ret["comment"] && $DBRESULTource["resource_comment"])	{
                            $comment = array();
                            $comment = explode("\n", $DBRESULTource["resource_comment"]);
                            foreach ($comment as $cmt)
                                    $str .= "# ".$cmt."\n";
                    }
                    $str .= $DBRESULTource["resource_name"]."=".$DBRESULTource["resource_line"]."\n";
                }
	}
	$str .= "\n";
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/resource.cfg");
	fclose($handle);
	
	setFileMod($nagiosCFGPath.$tab['id']."/resource.cfg");
	
	$DBRESULT->free();
	unset($str);
?>