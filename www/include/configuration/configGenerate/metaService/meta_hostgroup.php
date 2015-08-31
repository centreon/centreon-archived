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

	$handle = create_file($nagiosCFGPath.$tab['id']."/meta_hostgroup.cfg", $oreon->user->get_name());
	$str = NULL;

	# Host Creation
	$DBRESULT = $pearDB->query("SELECT * FROM meta_service WHERE meta_activate = '1'");
	$nb = $DBRESULT->numRows();
	
	if ($nb){
		$str .= "define hostgroup{\n";
		$str .= print_line("hostgroup_name", "meta_hostgroup");
		$str .= print_line("alias", "meta_hostgroup");
		$str .= print_line("members", "_Module_Meta");
		$str .= "\t}\n\n";
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, 'UTF-8'), $nagiosCFGPath.$tab['id']."/meta_hostgroup.cfg");
	fclose($handle);
	unset($str);
?>
