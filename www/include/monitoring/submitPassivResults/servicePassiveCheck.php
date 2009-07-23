<?php
/*
 * Copyright 2005-2009 MERETHIS
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

	$o = "svcd";

	if (!isset ($oreon))
		exit ();

	require_once ($centreon_path . "www/class/centreonHost.class.php");
	require_once ($centreon_path . "www/class/centreonDB.class.php");
	
	isset($_GET["host_name"]) ? $host_name = $_GET["host_name"] : $host_name = NULL;
	isset($_GET["service_description"]) ? $service_description = $_GET["service_description"] : $service_description = NULL;
	isset($_GET["cmd"]) ? $cmd = $_GET["cmd"] : $cmd = NULL;

	$hObj = new CentreonHost($pearDB);
	$path = "./include/monitoring/submitPassivResults/";
	$pearDBndo = new CentreonDB("ndo");
	
	# HOST LCA
	$flag_acl = 0;
	if (!$is_admin){
		$host_id = $hObj->getHostId($host_name);
		$serviceTab = $oreon->user->access->getHostServices($pearDBndo, $host_id);		
		foreach ($serviceTab as $value) {
			if ($value == $service_description)
				$flag_acl = 1;
		}	
	}
	
	if ($is_admin || ($flag_acl && !$is_admin)){

		#Pear library
		require_once "HTML/QuickForm.php";
		require_once 'HTML/QuickForm/advmultiselect.php';
		require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
		$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
		$form->addElement('header', 'title', 'Command Options');

		$hosts = array($host_name=>$host_name);

		$DBRESULT =& $pearDB->query("SELECT host_id FROM `host` WHERE host_name = '".$host_name."' ORDER BY host_name");
		$host =& $DBRESULT->fetchRow();
		$host_id = $host["host_id"];
		
		$services = array();
		if (isset($host_id))
			$services_id = getMyHostServices($host_id);
		
		$services = array();	
		foreach ($services_id as $id => $value){
			$svc_desc = getMyServiceName($id);
			$services[$svc_desc] = $svc_desc;
		}
		
		$form->addElement('select', 'host_name', _("Host Name"), $hosts, array("onChange" =>"this.form.submit();"));
		$form->addElement('select', 'service_description', _("Service"), $services);
	   	
		$form->addRule('host_name', _("Required Field"), 'required');
		$form->addRule('service_description', _("Required Field"), 'required');
	
		$return_code = array("0" => "OK","1" => "WARNING", "3" => "UNKNOWN", "2" => "CRITICAL");
	
		$form->addElement('select', 'return_code', _("Check result"),$return_code);
		$form->addElement('text', 'output', _("Check output"), array("size"=>"100"));
		$form->addElement('text', 'dataPerform', _("Performance data"), array("size"=>"100"));
	
		$form->addElement('hidden', 'author', $oreon->user->get_alias());
		$form->addElement('hidden', 'cmd', $cmd);
		$form->addElement('hidden', 'p', $p);
	
		$form->addElement('submit', 'submit', _("Save"));
		$form->addElement('reset', 'reset', _("Reset"));
		
		# Smarty template Init
		$tpl = new Smarty();
		$tpl = initSmartyTpl($path, $tpl);
			
		#Apply a template definition	
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);			
		
		$tpl->assign('form', $renderer->toArray());	
		$tpl->display("servicePassiveCheck.ihtml");
	}
?>