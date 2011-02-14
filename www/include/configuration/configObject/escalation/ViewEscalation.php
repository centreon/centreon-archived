<?php
/*
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

	if (!isset($oreon))
		exit();
	#
	## Database retrieve information for Host
	#
	
	global $path;
	$path = "./include/configuration/configObject/escalation/";
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$hostgroup_ary = array(NULL=>NULL);
	$cmd = "SELECT hg_id, hg_name FROM hostgroup";
	$DBRESULT = $pearDB->query($cmd);
	while($hostgroup = $DBRESULT->fetchRow())
		$hostgroup_ary[$hostgroup["hg_id"]] = $hostgroup["hg_name"];
	$DBRESULT->free();
	
	$hgs = array(NULL=>NULL);
	if (isset($_POST["hostgroup_escalation"]) && $_POST["hostgroup_escalation"] != NULL){
		$cmd = "SELECT h.host_id, h.host_name ".
				"FROM host h, hostgroup_relation hr ".
				"WHERE h.host_id = hr.host_host_id ".
				"AND hr.hostgroup_hg_id = '".$_POST["hostgroup_escalation"]."' ";
		$res = $pearDB->query($cmd);
		while($hg = $res->fetchRow())
			$hgs[$hg["host_id"]] = $hg["host_name"];
		$res->free();
	}
	else {
		$res = $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '1' ORDER by host_name");
		while($hg = $res->fetchRow())
			$hgs[$hg["host_id"]] = $hg["host_name"];
		$res->free();
	}

	$svcs = array();
	if (isset($_POST["hostgroup_escalation"])){
		$tpl->assign('hostgroup_id', $_POST["hostgroup_escalation"]);
	}
	if (isset($_POST["host_escalation"])){
		$svcs = getMyHostServices($_POST["host_escalation"]);
		$svcs[NULL]= NULL;
		$tpl->assign('host_id', $_POST["host_escalation"]);
		if (isset($_POST["service_escalation"]))
			$tpl->assign('service_id', $_POST["service_escalation"]);
	}

	$attrsText 		= array("size"=>"30");
	$attrsText2		= array("size"=>"6");
	$attrsAdvSelect = array("style" => "width: 200px; height: 200px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	$form->addElement('header', 'title', _("Escalations View"));
	$form->addElement('select', 'hostgroup_escalation', _("Hostgroups Escalation"), $hostgroup_ary, array("onChange" =>"this.form.submit();"));
	$form->addElement('select', 'host_escalation', _("Hosts Escalation"), $hgs, array("onChange" =>"this.form.submit();"));
	$form->addElement('select', 'service_escalation', _("Services Escalation"), $svcs, array("onChange" =>"this.form.submit();"));
	$valid = false;
		#Apply a template definition
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->display("ViewEscalation.ihtml");
?>


