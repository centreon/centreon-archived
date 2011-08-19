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
 * SVN : $URL
 * SVN : $Id$
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

	$form->addElement('text', 'start', _('Start Time'), array('id'=>'start', 'width'=>'30'));
	$form->addElement('text', 'end', _('End Time'), array('id'=>'end', 'width'=>'30'));
	$form->setDefaults(array(
						'start' => $centreonGMT->getDate('Y/m/d H:i', time()),
						'end' => $centreonGMT->getDate('Y/m/d H:i', time() + 7200)
					   )
					);
	$form->addElement('text', 'duration', _('Duration'), array('id'=>'duration', 'width'=>'30', 'disabled'=>'true'));
	$defaultDuration = 3600;
	if (isset($oreon->optGen['monitoring_dwt_duration']) && $oreon->optGen['monitoring_dwt_duration']) {
	    $defaultDuration = $oreon->optGen['monitoring_dwt_duration'];
	}
	$form->setDefaults(array('duration' => $defaultDuration));

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
	$tpl->assign('seconds', _('seconds'));
	$tpl->display("massive_downtime.ihtml");
?>