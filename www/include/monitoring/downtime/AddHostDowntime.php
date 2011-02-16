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

	include_once $centreon_path."www/class/centreonGMT.class.php";
	include_once $centreon_path."www/class/centreonDB.class.php";

	$pearDBndo = new CentreonDB("ndo");

	/*
	 * Init GMT class
	 */

	$centreonGMT = new CentreonGMT($pearDB);
	$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);
	$hostStr = $oreon->user->access->getHostsString("ID", $pearDBndo);

	if ($oreon->user->access->checkAction("host_schedule_downtime")) {
		/*
		 * Init
		 */

		if (isset($_GET["host_name"])){
			$host_id = getMyHostID($_GET["host_name"]);
			$host_name = $_GET["host_name"];
		} else
			$host_name = NULL;

			$data = array();
			$data = array("start" => $centreonGMT->getDate("Y/m/d G:i" , time() + 120), "end" => $centreonGMT->getDate("Y/m/d G:i", time() + 7320), "persistant" => 1, "host_or_hg" => '1', "with_services" => '0');
			if (isset($host_id))
				$data["host_id"] = $host_id;
			/*
			 * Database retrieve information for differents elements list we need on the page
			 */

			$hosts = array(""=>"");
			$query = "SELECT host_id, host_name " .
					"FROM `host` " .
					"WHERE host_register = '1' " .
					$oreon->user->access->queryBuilder("AND", "host_id", $hostStr) .
					"ORDER BY host_name";
			$DBRESULT = $pearDB->query($query);
			while ($host = $DBRESULT->fetchRow()){
				$hosts[$host["host_id"]]= $host["host_name"];
			}
			$DBRESULT->free();
			
			/*
			 * Get the list of hostgroup
			 */
			$hgStr = $oreon->user->access->getHostGroupsString("ID", $pearDBndo);
			$hg = array(""=>"");
			$query = "SELECT hg_id, hg_name
				FROM hostgroup
				WHERE hg_activate = '1' " . 
				$oreon->user->access->queryBuilder("AND", "", $hgStr) .
				" ORDER BY hg_name";
			$res = $pearDB->query($query);
			while ($row = $res->fetchRow()) {
			    $hg[$row['hg_id']] = $row['hg_name'];
			}
			$res->free();

			$debug = 0;
			$attrsTextI		= array("size"=>"3");
			$attrsText 		= array("size"=>"30");
			$attrsTextarea 	= array("rows"=>"7", "cols"=>"100");

			/*
			 * Form begin
			 */

			$form = new HTML_QuickForm('Form', 'POST', "?p=".$p);
			if ($o == "ah")
				$form->addElement('header', 'title', _("Add a Host downtime"));

			/*
			 * Indicator basic information
			 */
			$redirect = $form->addElement('hidden', 'o');
			$redirect->setValue($o);
			
	        $host_or_hg[] = &HTML_QuickForm::createElement('radio', 'host_or_hg', null, _("Host"), '1', array('id' => 'host_or_hg_host', 'onclick' => "toggleParams('host');"));
	        $host_or_hg[] = &HTML_QuickForm::createElement('radio', 'host_or_hg', null, _("Hostgroup"), '0', array('id' => 'host_or_hg_hg', 'onclick' => "toggleParams('hostgroup');"));
	        $form->addGroup($host_or_hg, 'host_or_hg', _("Select a downtime type"), '&nbsp;');

		    $selHost = $form->addElement('select', 'host_id', _("Host Name"), $hosts);
		    $selHg = $form->addElement('select', 'hostgroup_id', _("Hostgroup"), $hg);
		    $form->addElement('checkbox', 'persistant', _("Fixed"), null, array('id' => 'fixed', 'onClick' => 'javascript:setDurationField()'));
			$form->addElement('text', 'start', _("Start Time"), $attrsText);
			$form->addElement('text', 'end', _("End Time"), $attrsText);
			$form->addElement('text', 'duration', _("Duration"), array('size' => '15', 'id' => 'duration'));
			
			$with_services[] = &HTML_QuickForm::createElement('radio', 'with_services', null, _("Yes"), '1');
	        $with_services[] = &HTML_QuickForm::createElement('radio', 'with_services', null, _("No"), '0');
	        $form->addGroup($with_services, 'with_services', _("Set downtime for hosts services"), '&nbsp;');
			
			$form->addElement('textarea', 'comment', _("Comments"), $attrsTextarea);

			//$form->addRule('host_id', _("Required Field"), 'required');
			$form->addRule('end', _("Required Field"), 'required');
			$form->addRule('start', _("Required Field"), 'required');
			$form->addRule('comment', _("Required Field"), 'required');

			$form->setDefaults($data);
			$subA = $form->addElement('submit', 'submitA', _("Save"));
			$res = $form->addElement('reset', 'reset', _("Reset"));

		  	if ((isset($_POST["submitA"]) && $_POST["submitA"]) && $form->validate())	{
		  	    $values = $form->getSubmitValues();
				if (!isset($_POST["persistant"]))
					$_POST["persistant"] = 0;
				if (!isset($_POST["comment"]))
					$_POST["comment"] = 0;
				$_POST["comment"] = str_replace("'", " ", $_POST['comment']);
		  	    $duration = null;
				if (isset($_POST['duration'])) {
                    $duration = $_POST['duration'];
			    }
			    $dt_w_services = false;
			    if ($values['with_services']['with_services'] == 1) {
			        $dt_w_services = true;
			    }
			    if ($values['host_or_hg']['host_or_hg'] == 1) {
			        /*
			         * Set a downtime for only host
			         */
				    $ecObj->AddHostDowntime($_POST["host_id"], $_POST["comment"], $_POST["start"], $_POST["end"], $_POST["persistant"], $duration, $dt_w_services);
			    } else {
			        /*
			         * Set a downtime for hostgroup
			         */
			        $hg = new CentreonHostgroups($pearDB);
			        $hostlist = $hg->getHostGroupHosts($_POST['hostgroup_id']);
			        $host_acl_id = preg_split('/,/', $hostStr);
			        foreach ($hostlist as $host_id) {
			            if ($oreon->user->access->admin || in_array($host_id, $host_acl_id)) {
			                $ecObj->AddHostDowntime($host_id, $_POST["comment"], $_POST["start"], $_POST["end"], $_POST["persistant"], $duration, $dt_w_services);
			            }
			        }
			    }
				require_once("viewHostDowntime.php");
		    } else {
				/*
				 * Smarty template Init
				 */
				$tpl = new Smarty();
				$tpl = initSmartyTpl($path, $tpl, "template/");

				/*
				 * Apply a template definition
				 */
				$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
				$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
				$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
				$form->accept($renderer);
				$tpl->assign('form', $renderer->toArray());
				$tpl->assign('seconds', _("seconds"));
				$tpl->assign('o', $o);
				$tpl->display("AddHostDowntime.ihtml");
		    }
		}
		else {
			require_once("../errors/alt_error.php");
		}
?>
<script type='text/javascript'>
document.onLoad = setDurationField();
function setDurationField()
{
	var durationField = document.getElementById('duration');
	var fixedCb = document.getElementById('fixed');

	if (fixedCb.checked == true) {
		durationField.disabled = true;
	} else {
		durationField.disabled = false;
	}
}
</script>