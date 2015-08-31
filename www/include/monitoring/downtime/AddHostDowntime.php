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

	include_once $centreon_path."www/class/centreonGMT.class.php";
	include_once $centreon_path."www/class/centreonDB.class.php";

	if ($oreon->broker->getBroker() == "ndo") {
		$pearDBndo = new CentreonDB("ndo");
	}

	/*
	 * Init GMT class
	 */
	$centreonGMT = new CentreonGMT($pearDB);
	$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);
	$hostStr = $oreon->user->access->getHostsString("ID", ($oreon->broker->getBroker() == "ndo" ? $pearDBndo : $pearDBO));

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
			$data = array(
                            "start" => $centreonGMT->getDate("m/d/Y" , time() + 120), 
                            "end" => $centreonGMT->getDate("m/d/Y", time() + 7320),
                            "start_time" => $centreonGMT->getDate("G:i" , time() + 120),
                            "end_time" => $centreonGMT->getDate("G:i" , time() + 7320),
                            "host_or_hg" => '1',
                            "with_services" => '0'
                        );
			if (isset($host_id))
				$data["host_id"] = $host_id;
			/*
			 * Database retrieve information for differents elements list we need on the page
			 */
			$hosts = array();
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
			$acldb = $oreon->broker->getBroker() == "ndo" ? $pearDBndo : $pearDBO;
			$hg = array();
                        if ($oreon->user->access->admin) {
                            $query = "SELECT hg_id, hg_name
                                      FROM hostgroup
                                      WHERE hg_activate = '1' 
                                      ORDER BY hg_name";
                        } else {
                            $query = "SELECT DISTINCT hg.hg_id, hg.hg_name " .
                                     "FROM hostgroup hg, acl_resources_hg_relations arhr " .
                                     "WHERE hg.hg_id = arhr.hg_hg_id " .
                                     "AND arhr.acl_res_id IN (".$oreon->user->access->getResourceGroupsString().") " .
                                     "AND hg.hg_activate = '1' ".
                                     "AND hg.hg_id in (SELECT hostgroup_hg_id
                                                       FROM hostgroup_relation
                                                       WHERE host_host_id IN (".$oreon->user->access->getHostsString("ID", $acldb).")) " .
                                     "ORDER BY hg.hg_name";
                        }
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

	        $host_or_hg[] = HTML_QuickForm::createElement('radio', 'host_or_hg', null, _("Host"), '1', array('id' => 'host_or_hg_host', 'onclick' => "toggleParams('host');"));
	        $host_or_hg[] = HTML_QuickForm::createElement('radio', 'host_or_hg', null, _("Hostgroup"), '0', array('id' => 'host_or_hg_hg', 'onclick' => "toggleParams('hostgroup');"));
	        $form->addGroup($host_or_hg, 'host_or_hg', _("Select a downtime type"), '&nbsp;');

		    $selHost = $form->addElement('select', 'host_id', _("Host Name"), $hosts);
		    $selHg = $form->addElement('select', 'hostgroup_id', _("Hostgroup"), $hg);
		    $chbx = $form->addElement('checkbox', 'persistant', _("Fixed"), null, array('id' => 'fixed', 'onClick' => 'javascript:setDurationField()'));
		    if (isset($oreon->optGen['monitoring_dwt_fixed']) && $oreon->optGen['monitoring_dwt_fixed']) {
		        $chbx->setChecked(true);
		    }
			$form->addElement('text', 'start', _("Start Time"), array('size' => 10, 'class' => 'datepicker'));
			$form->addElement('text', 'end', _("End Time"), array('size' => 10, 'class' => 'datepicker'));
                        
            $form->addElement('text', 'start_time', '', array('size' => 5, 'class' => 'timepicker'));
			$form->addElement('text', 'end_time', '', array('size' => 5, 'class' => 'timepicker'));
                        
			$form->addElement('text', 'duration', _("Duration"), array('size' => '15', 'id' => 'duration'));
	        $defaultDuration = 3600;
            
	        if (isset($oreon->optGen['monitoring_dwt_duration']) && $oreon->optGen['monitoring_dwt_duration']) {
	            $defaultDuration = $oreon->optGen['monitoring_dwt_duration'];
	        }
	        $form->setDefaults(array('duration' => $defaultDuration));
            
            $scaleChoices = array("s" => _("seconds"),
                          "m" => _("minutes"),
                          "h" => _("hours"),
                          "d" => _("days")
                        );
            $form->addElement('select', 'duration_scale', _("Scale of time"), $scaleChoices);
            $defaultScale = 's';
            if (isset($oreon->optGen['monitoring_dwt_duration_scale']) && $oreon->optGen['monitoring_dwt_duration_scale']) {
	            $defaultScale = $oreon->optGen['monitoring_dwt_duration_scale'];
	        }
            $form->setDefaults(array('duration_scale' => $defaultScale));

			$with_services[] = HTML_QuickForm::createElement('radio', 'with_services', null, _("Yes"), '1');
	        $with_services[] = HTML_QuickForm::createElement('radio', 'with_services', null, _("No"), '0');
	        $form->addGroup($with_services, 'with_services', _("Set downtime for hosts services"), '&nbsp;');

			$form->addElement('textarea', 'comment', _("Comments"), $attrsTextarea);

			$form->addRule('end', _("Required Field"), 'required');
			$form->addRule('start', _("Required Field"), 'required');
            $form->addRule('end_time', _("Required Field"), 'required');
			$form->addRule('start_time', _("Required Field"), 'required');
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
                    
                    if (isset($_POST['duration_scale'])) {
                        $duration_scale = $_POST['duration_scale'];
                    } else {
                        $duration_scale = 's';
                    }
                    
                    switch ($duration_scale)
                    {
                        default:
                        case 's':
                            $duration = $_POST['duration'];
                            break;
                        
                        case 'm':
                            $duration = $_POST['duration'] * 60;
                            break;
                        
                        case 'h':
                            $duration = $_POST['duration'] * 60 * 60;
                            break;
                        
                        case 'd':
                            $duration = $_POST['duration'] * 60 * 60 * 24;
                            break;
                    }
			    }
                
			    $dt_w_services = false;
			    if ($values['with_services']['with_services'] == 1) {
			        $dt_w_services = true;
			    }
			    if ($values['host_or_hg']['host_or_hg'] == 1) {
			        /*
			         * Set a downtime for only host
			         */
                    $ecObj->AddHostDowntime(
                            $_POST["host_id"], 
                            $_POST["comment"], 
                            $_POST["start"].' '.$_POST['start_time'], 
                            $_POST["end"].' '.$_POST['end_time'], 
                            $_POST["persistant"], 
                            $duration, 
                            $dt_w_services
                    );
			    } else {
			        /*
			         * Set a downtime for hostgroup
			         */
			        $hg = new CentreonHostgroups($pearDB);
			        $hostlist = $hg->getHostGroupHosts($_POST['hostgroup_id']);
			        $host_acl_id = preg_split('/,/', str_replace("'", "", $hostStr));
			        foreach ($hostlist as $host_id) {
			            if ($oreon->user->access->admin || in_array($host_id, $host_acl_id)) {
							$ecObj->AddHostDowntime(
								$host_id, 
								$_POST["comment"], 
								$_POST["start"] . ' '. $_POST["start_time"], 
								$_POST["end"] . ' ' . $_POST["end_time"], 
								$_POST["persistant"], 
								$duration, 
								$dt_w_services
							);
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
		} else {
			require_once("../errors/alt_error.php");
		}
?>
<script type='text/javascript'>
jQuery(function() {
    setDurationField();
});

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
