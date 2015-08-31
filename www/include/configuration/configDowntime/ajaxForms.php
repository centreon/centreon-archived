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

	if (!isset($centreon)) {
		return;
	}

	if (isset($_GET['period'])) {
		$period_tab = $_GET['period'];
	} else {
		$period_tab = 1;
	}

	if (isset($_GET['period_form'])) {
		$form = $_GET['period_form'];
	} else {
		$form = "general";
	}
    
    require_once "HTML/QuickForm.php";

	$path = "./include/configuration/configDowntime/";

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, 'templates/');

	$tpl->assign("period_tab", $period_tab);

	$tpl->assign("days", _("Days"));
    $tpl->assign("hours", _("Hours"));
    $tpl->assign("minutes", _("Minutes"));
	$tpl->assign("seconds", _("Seconds"));
	$tpl->assign("downtime_type", _("Downtime type"));
	$tpl->assign("fixed", _("Fixed"));
	$tpl->assign("flexible", _("Flexible"));

	switch ($form) {
		case "weekly_basis":
			$tpl->assign("time_period", _("Time period"));
			$tpl->assign("monday", _("Monday"));
			$tpl->assign("tuesday", _("Tuesday"));
			$tpl->assign("wednesday", _("Wednesday"));
			$tpl->assign("thursday", _("Thursday"));
			$tpl->assign("friday", _("Friday"));
			$tpl->assign("saturday", _("Saturday"));
			$tpl->assign("sunday", _("Sunday"));
			$tmpl = "weekly_basis.ihtml";
			break;
		case "monthly_basis":
			$tpl->assign("time_period", _("Time period"));
			$tpl->assign("nbDays", range(1, 31));
			$tmpl = "monthly_basis.ihtml";
			break;
		case "specific_date":
			$tpl->assign("first_of_month", _("First of month"));
			$tpl->assign("last_of_month", _("Last of month"));
			$tpl->assign("time_period", _("Time period"));
			$tpl->assign("monday", _("Monday"));
			$tpl->assign("tuesday", _("Tuesday"));
			$tpl->assign("wednesday", _("Wednesday"));
			$tpl->assign("thursday", _("Thursday"));
			$tpl->assign("friday", _("Friday"));
			$tpl->assign("saturday", _("Saturday"));
			$tpl->assign("sunday", _("Sunday"));
			$tmpl = "specific_date.ihtml";
			break;
		case "general":
		default:
			$tpl->assign("weekly_basis", _("Weekly basis"));
			$tpl->assign("monthly_basis", _("Monthly basis"));
			$tpl->assign("specific_date", _("Specific date"));
			$tmpl = 'general.ihtml';
			break;
	}
	$tpl->assign('o', '');
	$tpl->assign('p', $p);
	$tpl->display($tmpl);
?>