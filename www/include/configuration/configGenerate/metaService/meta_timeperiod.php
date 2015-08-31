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
 	
	$str = NULL;
	$handle = create_file($nagiosCFGPath.$tab['id']."/meta_timeperiod.cfg", $oreon->user->get_name());
	$str .= "define timeperiod{\n";
	$str .= print_line("timeperiod_name", "meta_timeperiod");
	$str .= print_line("alias", "meta_timeperiod");
	$str .= print_line("sunday", "00:00-24:00");
	$str .= print_line("monday", "00:00-24:00");
	$str .= print_line("wednesday", "00:00-24:00");
	$str .= print_line("tuesday", "00:00-24:00");
	$str .= print_line("thursday", "00:00-24:00");
	$str .= print_line("friday", "00:00-24:00");
	$str .= print_line("saturday", "00:00-24:00");
	$str .= "}\n\n";
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, 'UTF-8'), $nagiosCFGPath.$tab['id']."/meta_timeperiod.cfg");
	fclose($handle);
	unset($str);
?>
