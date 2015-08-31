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

if (!isset($oreon)) {
		exit();
}

if (!is_dir($nagiosCFGPath.$tab['id']."/")) {
		mkdir($nagiosCFGPath.$tab['id']."/");
}

$handle = create_file($nagiosCFGPath.$tab['id']."/shinkenBroker.cfg", $oreon->user->get_name());

$res = $pearDB->query("SELECT * FROM `cfg_ndo2db` WHERE `activate` = '1' AND `ns_nagios_server` = '".$tab['id']."' LIMIT 1");
$res->numRows() ? $ndodb = $res->fetchRow() : $ndodb = array();

$str = "";
$str .= "define module {";
$str .= print_line("module_name", "ToNdodb_Mysql");
$str .= print_line("module_type", "ndodb_mysql");
$str .= print_line("database", $ndodb['db_name']);
$str .= print_line("user", $ndodb['db_user']);
$str .= print_line("password", $ndodb['db_pass']);
$str .= print_line("host", $ndodb['db_host']);
$str .= print_line("character_set", "utf8");
$str .= "}\n";

write_in_file($handle, $str, $nagiosCFGPath.$tab['id']."/shinkenBroker.cfg");
