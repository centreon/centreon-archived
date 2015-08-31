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

	function get_user_param($user_id, $pearDB){
		$list_param = array('log_filter_host', 'log_filter_svc', 'log_filter_host_down',
			'log_filter_host_up', 'log_filter_host_unreachable', 'log_filter_svc_ok',
			'log_filter_svc_warning', 'log_filter_svc_critical', 'log_filter_svc_unknown',
			'log_filter_notif', 'log_filter_error', 'log_filter_alert', 'log_filter_oh',
			'search_H', 'search_S');
		$tab_row = array();
		foreach ($list_param as $param) {
			if (isset($_SESSION[$param])) {
				$tab_row[$param] = $_SESSION[$param];
			} else {
				if (!isset($cache)) {
					$cache = array();
					$query = "SELECT cp_key, cp_value FROM contact_param
						WHERE cp_key in ('" . join("', '", $list_param) . "') AND cp_contact_id = " . $user_id;
					$DBRESULT = $pearDB->query($query);
					while ($row = $DBRESULT->fetchRow()) {
						$cache[$row['cp_key']] = $row['cp_value'];
						$SESSION[$row['cp_key']] = $row['cp_value'];
					}
				}
				if (isset($cache[$param])) {
					$tab_row[$param] = $cache[$param];
				}
			}
		}
		return $tab_row;
	}

	function set_user_param($user_id, $pearDB, $key, $value){
		$_SESSION[$key] = $value;
		$list_param = array('log_filter_host', 'log_filter_svc', 'log_filter_host_down',
			'log_filter_host_up', 'log_filter_host_unreachable', 'log_filter_svc_ok',
			'log_filter_svc_warning', 'log_filter_svc_critical', 'log_filter_svc_unknown',
			'log_filter_notif', 'log_filter_error', 'log_filter_alert', 'log_filter_oh', 'log_filter_period');
		if (in_array($key, $list_param)) {
			$queryDel = "DELETE FROM contact_param WHERE cp_key = '" .$key . "' AND cp_contact_id = '" . $user_id . "'";
			$queryIns = "INSERT INTO contact_param (cp_key, cp_value, cp_contact_id) " .
				"VALUES ('" . $key . "', '" . $value . "', " . $user_id . ")";
			$pearDB->query($queryDel);
			$pearDB->query($queryIns);
		}
	}

 	function getMyHostIDService($svc_id = NULL)	{
		if (!$svc_id) return;
		global $pearDB;
		$DBRESULT = $pearDB->query("SELECT host_id FROM host h, host_service_relation hs WHERE h.host_id = hs.host_host_id AND hs.service_service_id = '".$svc_id."'");
		if ($DBRESULT->numRows())	{
			$row = $DBRESULT->fetchRow();
			return $row["host_id"];
		}
		return NULL;
	}
?>