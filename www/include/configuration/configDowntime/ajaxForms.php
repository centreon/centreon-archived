<?php
/**
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
	
	$path = "./include/configuration/configDowntime/";
	
	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, 'templates/');
	
	$tpl->assign("period_tab", $period_tab);
	
	$tpl->assign("days", _("Days"));
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
	$tpl->display($tmpl);
?>