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
		exit();
	}
	
	if (($o == 'c' || $o == 'w') && isset($_GET['dt_id'])) {
		$id = $_GET['dt_id'];
	} else {
		$o = 'a';
	}
	
	##########################################################
	# Var information to format the element
	#
	$attrsText 				= array("size"=>"30");
	$attrsText2				= array("size"=>"6");
	$attrsTextLong 			= array("size"=>"50");
	$attrsAdvSelect_small 	= array("style" => "width: 270px; height: 70px;");
	$attrsAdvSelect 		= array("style" => "width: 270px; height: 100px;");
	$attrsAdvSelect_big 	= array("style" => "width: 270px; height: 200px;");
	$attrsTextarea 			= array("rows"=>"5", "cols"=>"40");
	$templateMultiSelect	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';
	
	/* QuickFrom */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a") {
		$form->addElement('header', 'title', _("Add a downtime"));
	} elseif ($o == "c") {
		$form->addElement('header', 'title', _("Modify a downtime"));
	} elseif ($o == "w") {
		$form->addElement('header', 'title', _("View a downtime"));
	}
	
	$form->addElement('header', 'periods', _("Periods"));
	
	# Sort 1
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('header', 'linkManagement', _("Links Management"));
	$form->addElement('text', 'downtime_name', _("Name"), $attrsText);
	$form->addElement('text', 'downtime_description', _("Description"), $attrsTextLong);
	
	$donwtime_activate[] = &HTML_QuickForm::createElement('radio', 'downtime_activate', null, _("Yes"), '1');
	$donwtime_activate[] = &HTML_QuickForm::createElement('radio', 'downtime_activate', null, _("No"), '0');
	$form->addGroup($donwtime_activate, 'downtime_activate', _("Enable"), '&nbsp;');
	$form->setDefaults(array('downtime_activate' => '1'));
	$page = $form->addElement('hidden', 'p');
	$page->setValue($p);
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	/* Tab 2 */
	/*  - Hosts */
	$hosts = array();
	$DBRESULT = $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1' ORDER BY host_name");
	while ($host = $DBRESULT->fetchRow()) {
		$hosts[$host["host_id"]] = $host["host_name"];
	}
	$DBRESULT->free();
	$am_host = $form->addElement('advmultiselect', 'host_relation', array(_("Linked with Hosts"), _("Available"), _("Selected")), $hosts, $attrsAdvSelect_big, SORT_ASC);
	$am_host->setButtonAttributes('add', array('value' =>  _("Add")));
	$am_host->setButtonAttributes('remove', array('value' => _("Remove")));
	$am_host->setElementTemplate($templateMultiSeelect);
	echo $am_host->getElementJs(false);
	/*  - Hostgroups */
	$hgs = array();
	$DBRESULT = $pearDB->query("SELECT hg_id, hg_name FROM hostgroup ORDER BY hg_name");
	while ($hg = $DBRESULT->fetchRow()) {
		$hgs[$hg["hg_id"]] = $hg["hg_name"];
	}
	$DBRESULT->free();
	$am_hostgroup = $form->addElement('advmultiselect', 'hostgroup_relation', array(_("Linked with Host Groups"), _("Available"), _("Selected")), $hgs, $attrsAdvSelect_big, SORT_ASC);
	$am_hostgroup->setButtonAttributes('add', array('value' =>  _("Add")));
	$am_hostgroup->setButtonAttributes('remove', array('value' => _("Remove")));
	$am_hostgroup->setElementTemplate($templateMultiSeelect);
	echo $am_hostgroup->getElementJs(false);
	/*  - Service */
	$host4svc = array(-2 => "_"._("None")."_", -1 => "_"._("ALL")."_");
	foreach ($hosts as $key => $hostname) {
	    $host4svc[$key] = $hostname;
	}
	$form->addElement('select', 'host4svc', _('Host'), $host4svc, array('onchange' => "javascript:getServices(this.form.elements['host4svc'].value); return false;"));
	$svcs = array();
	if (isset($id) && $id != 0) {
	    $query = "SELECT s.service_id, s.service_description, h.host_name, h.host_id
	    	FROM service s, host h, host_service_relation hsr, downtime_service_relation dsr
	    	WHERE h.host_id = hsr.host_host_id AND s.service_id = hsr.service_service_id AND dsr.dt_id = " . $id ." AND dsr.host_host_id = h.host_id AND dsr.service_service_id = s.service_id
	    	ORDER BY h.host_name, s.service_description";
	    $DBRESULT = $pearDB->query($query);
	    while ($svc = $DBRESULT->fetchRow()) {
	        $svc_id = $svc['host_id'] . '-' . $svc['service_id'];
	        $svc_name = $svc['host_name'] . '-' . $svc['service_description'];
	        $svcs[$svc_id] = $svc_name;
	    }
	}
	$am_svc = $form->addElement('advmultiselect', 'svc_relation', array(_("Linked with Services"), _("Available"), _("Selected")), $svcs, $attrsAdvSelect_big, SORT_ASC);
	$am_svc->setButtonAttributes('add', array('value' =>  _("Add")));
	$am_svc->setButtonAttributes('remove', array('value' => _("Remove")));
	$am_svc->setElementTemplate($templateMultiSeelect);
	echo $am_svc->getElementJs(false);
	/*  - Servicegroups */
	$sgs = array();
	$DBRESULT = $pearDB->query("SELECT sg_id, sg_name FROM servicegroup ORDER BY sg_name");
	while ($sg = $DBRESULT->fetchRow()) {
		$sgs[$sg["sg_id"]] = $sg["sg_name"];
	}
	$DBRESULT->free();
	$am_svcgroup = $form->addElement('advmultiselect', 'svcgroup_relation', array(_("Linked with Serfvice Groups"), _("Available"), _("Selected")), $sgs, $attrsAdvSelect_big, SORT_ASC);
	$am_svcgroup->setButtonAttributes('add', array('value' =>  _("Add")));
	$am_svcgroup->setButtonAttributes('remove', array('value' => _("Remove")));
	$am_svcgroup->setElementTemplate($templateMultiSeelect);
	echo $am_svcgroup->getElementJs(false);
	
	$form->addRule('downtime_name', _("Name"), 'required');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));
	
	if ($o == "c" || $o == 'w') {
		$infos = $downtime->getInfos($id);
		$relations = $downtime->getRelations($id);
		$default_dt = array(
			'dt_id' => $id,
			'downtime_name' => $infos['name'],
			'downtime_description' => $infos['description'],
			'downtime_activate' => $infos['activate'],
			'host_relation' => $relations['host'],
			'hostgroup_relation' => $relations['hostgrp'],
		    'svc_relation' => $relations['svc'],
			'svcgroup_relation' => $relations['svcgrp']
		);
	}
	
	// Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	// Just watch a host information
	if ($o == "w") {
		$form->addElement('hidden', 'dt_id');
		if (!$min && $centreon->user->access->page($p) != 2) {
			$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dt_id=".$id."'"));
		}
	    $form->setDefaults($default_dt);
		$form->freeze();
	} elseif ($o == "c") {
		$form->addElement('hidden', 'dt_id');
		// Modify a service information
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('button', 'reset', _("Reset"), array("onClick" => "history.go(0);"));
	    $form->setDefaults($default_dt);
	} elseif ($o == "a") {
		// Add a service information
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	}
	
	$tpl->assign("sort1", _("Downtime Configuration"));
	$tpl->assign("sort2", _("Relations"));
	$tpl->assign("periods", _("Periods"));
	
	# prepare help texts
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) {
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);
	
	
	$valid = false;
	if ($form->validate()) {
		$values = $form->getSubmitValues();
		$valid = true;
		foreach ($values['periods'] as $periods) {
			if (strtotime($periods['start_period']) > strtotime($periods['end_period'])) {
				$valid = false;
				$tpl->assign('period_err', _("The end time must be greater than the start time."));
			}
		} 
		if ($valid) {
			if ($form->getSubmitValue("submitA")) {
				$activate = $values['downtime_activate']['downtime_activate'];
				$id = $downtime->add($values['downtime_name'], $values['downtime_description'], $activate);
				if (false !== $id) {
					foreach ($values['periods'] as $periods) {
						$downtime->addPeriod($id, $periods);
					}
					if (isset($values['host_relation'])) {
						$downtime->addRelations($id, $values['host_relation'], 'host');
					}
					if (isset($values['hostgroup_relation'])) {
						$downtime->addRelations($id, $values['hostgroup_relation'], 'hostgrp');
					}
					if (isset($values['svc_relation'])) {
					    $downtime->addRelations($id, $values['svc_relation'], 'svc');
					}
					if (isset($values['svcgroup_relation'])) {
						$downtime->addRelations($id, $values['svcgroup_relation'], 'svcgrp');
					}
					$o = "w";
					$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dt_id=".$id."'"));
					$form->freeze();
					$valid = true;
				}	
			} elseif ($form->getSubmitValue("submitC")) {
				$id = $values['dt_id'];
				$activate = $values['downtime_activate']['downtime_activate'];
				$downtime->modify($id, $values['downtime_name'], $values['downtime_description'], $activate);
				$downtime->deletePeriods($id);
				foreach ($values['periods'] as $periods) {
					$downtime->addPeriod($id, $periods);
				}
				$downtime->deteleRelations($id);
				if (isset($values['host_relation'])) {
					$downtime->addRelations($id, $values['host_relation'], 'host');
				}
				if (isset($values['hostgroup_relation'])) {
					$downtime->addRelations($id, $values['hostgroup_relation'], 'hostgrp');
				}
			    if (isset($values['svc_relation'])) {
				    file_put_contents('/tmp/query', "Services \n", FILE_APPEND);
				    $downtime->addRelations($id, $values['svc_relation'], 'svc');
				}
				if (isset($values['svcgroup_relation'])) {
					$downtime->addRelations($id, $values['svcgroup_relation'], 'svcgrp');
				}
				$o = "w";
				$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&dt_id=".$id."'"));
				$form->freeze();
				$valid = true;
			}
		}
		
		if ($valid) {
			require_once($path."listDowntime.php");	
		}
	} else {
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		if ($o == 'w') {
		    $tpl->assign("time_period", _("Time period"));
		    $tpl->assign("days", _("Days"));
		    $tpl->assign("seconds", _("Seconds"));
		    $tpl->assign("downtime_type", _("Downtime type"));
		    $tpl->assign("fixed", _("Fixed"));
		    $tpl->assign("flexible", _("Flexible"));
		    $tpl->assign("weekly_basis", _("Weekly basis"));
			$tpl->assign("monthly_basis", _("Monthly basis"));
			$tpl->assign("specific_date", _("Specific date"));
			$tpl->assign("week_days", array(
			    1 => _("Monday"),
				2 => _("Tuesday"),
				3 => _("Wednesday"),
				4 => _("Thursday"),
				5 => _("Friday"),
				6 => _("Saturday"),
				7 => _("Sunday")
			));
		    $tpl->assign('periods_tab', $downtime->getPeriods($id));
		}
		
		$form->accept($renderer);
		$tpl->assign('o', $o);
		$tpl->assign('form', $renderer->toArray());
		
		$tpl->assign('v', $centreon->user->get_version());
		$tpl->display("formDowntime.ihtml");
	}
?>