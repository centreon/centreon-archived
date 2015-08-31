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

	$handle = create_file($nagiosCFGPath.$tab['id']."/ndo2db.cfg", $oreon->user->get_name());

	$DBRESULT = $pearDB->query("SELECT * FROM `cfg_ndo2db` WHERE `activate` = '1' AND `ns_nagios_server` = '".$tab['id']."' LIMIT 1");
	$DBRESULT->numRows() ? $ndomod = $DBRESULT->fetchRow() : $ndomod = array();

	$str = "";
	$icingaCompat = array("ndo2db_user"  => "ido2db_user",
	                      "ndo2db_group" => "ido2db_group");
	foreach ($ndomod as $key => $value)	{
		if ($value && $key != "id" && $key != "description" && $key != "local" && $key != "ns_nagios_server" && $key != "activate")	{
		    if (isset($tab['monitoring_engine']) && $tab['monitoring_engine'] == "ICINGA" && isset($icingaCompat[$key])) {
                $str .= $icingaCompat[$key]."=".$value."\n";
			} else {
		        $str .= $key."=".$value."\n";
			}
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, "UTF-8"), $nagiosCFGPath.$tab['id']."/ndo2db.cfg");
	fclose($handle);
	
	setFileMod($nagiosCFGPath.$tab['id']."/ndo2db.cfg");
	
	$DBRESULT->free();
	unset($str);
?>