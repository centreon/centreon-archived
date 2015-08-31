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

	if (!isset ($oreon))
		exit ();

	$select = array();
	if (isset($_GET['select'])) {
		foreach ($_GET['select'] as $key => $value) {
			if ($cmd == '75') {
				$tmp = preg_split("/\;/", $key);
				$select[] = $tmp[0];
			}
			else {
				$select[] = $key;
			}
		}
	}

	$path = "$centreon_path/www/include/monitoring/external_cmd/popup/";
	
	/*
	 * Init GMT
	 */
	$centreonGMT = new CentreonGMT($pearDB);
	$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTplForPopup($path, $tpl, './templates/', $centreon_path);

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$form = new HTML_QuickForm('select_form', 'GET', 'main.php');

	$form->addElement('header', 'title', _("Set downtimes"));

	$tpl->assign('authorlabel', _("Alias"));
	$tpl->assign('authoralias', $oreon->user->get_alias());

	$form->addElement('textarea', 'comment', _("Comment"), array("rows"=>"5", "cols"=>"70", "id"=>"popupComment"));
	$form->setDefaults(array("comment" => sprintf(_("Downtime set by %s"), $oreon->user->alias)));

	$form->addElement('text', 'start', _('Start Time'), array('id'=>'start', 'size'=>10, 'class'=>'datepicker'));
	$form->addElement('text', 'end', _('End Time'), array('id'=>'end', 'size'=>10, 'class'=>'datepicker'));
        
        $form->addElement('text', 'start_time', '', array('id'=>'start_time', 'size' => 5, 'class' => 'timepicker'));
        $form->addElement('text', 'end_time', '', array('id'=>'end_time', 'size' => 5, 'class' => 'timepicker'));
        
	$form->setDefaults(
                array(
                    "start" => $centreonGMT->getDate("m/d/Y" , time() + 120), 
                    "end" => $centreonGMT->getDate("m/d/Y", time() + 7320),
                    "start_time" => $centreonGMT->getDate("G:i" , time() + 120),
                    "end_time" => $centreonGMT->getDate("G:i" , time() + 7320)
		)
        );
	$form->addElement('text', 'duration', _('Duration'), array('id'=>'duration', 'width'=>'30', 'disabled'=>'true'));
	$defaultDuration = 3600;
	if (isset($oreon->optGen['monitoring_dwt_duration']) && $oreon->optGen['monitoring_dwt_duration']) {
	    $defaultDuration = $oreon->optGen['monitoring_dwt_duration'];
	}
	$form->setDefaults(array('duration' => $defaultDuration));
    
    $scaleChoices = array("s" => _("Seconds"),
                          "m" => _("Mminutes"),
                          "h" => _("Hours"),
                          "d" => _("Days")
              );
    $form->addElement('select', 'duration_scale', _("Scale of time"), $scaleChoices, array('id'=>'duration_scale'));
    $defaultScale = 's';
    if (isset($oreon->optGen['monitoring_dwt_duration_scale']) && $oreon->optGen['monitoring_dwt_duration_scale']) {
        $defaultScale = $oreon->optGen['monitoring_dwt_duration_scale'];
    }
    $form->setDefaults(array('duration_scale' => $defaultScale));

	$chckbox[] = $form->addElement('checkbox', 'fixed', _("Fixed"), "", array("id"=>"fixed"));
	$chckbox[0]->setChecked(true);

	$chckbox2[] = $form->addElement('checkbox', 'downtimehostservice', _("Set downtimes on services attached to hosts"), "", array("id"=>"downtimehostservice"));
	$chckbox2[0]->setChecked(true);

	$form->addElement('hidden', 'author', $oreon->user->get_alias(), array("id"=>"author"));

	$form->addRule('comment', _("Comment is required"), 'required', '', 'client');
	$form->setJsWarnings(_("Invalid information entered"), _("Please correct these fields"));

	$form->addElement('button', 'submit', _("Set downtime"), array("onClick" => "send_the_command();"));
	$form->addElement('reset', 'reset', _("Reset"));

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');

	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());

	$defaultFixed = "";
	if (isset($oreon->optGen['monitoring_dwt_fixed']) && $oreon->optGen['monitoring_dwt_fixed']) {
        $defaultFixed = "checked";
	}
	$tpl->assign('defaultFixed', $defaultFixed);

	$defaultSetDwtOnSvc = "";
	if (isset($oreon->optGen['monitoring_dwt_svc']) && $oreon->optGen['monitoring_dwt_svc']) {
        $defaultSetDwtOnSvc = "checked";
	}
	$tpl->assign('defaultSetDwtOnSvc', $defaultSetDwtOnSvc);

	$tpl->assign('o', $o);
	$tpl->assign('p', $p);
	$tpl->assign('cmd', $cmd);
	$tpl->assign('select', $select);
	$tpl->display("massive_downtime.ihtml");
?>